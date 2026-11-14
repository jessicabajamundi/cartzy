<?php

namespace App\Http\Controllers;

use App\Models\{LogisticsProvider, ReturnCase, Rider};
use App\Services\ReturnWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReturnController extends Controller
{
    private function active(Request $request): void
    {
        abort_unless($request->user()->status === 'active' && !$request->user()->is_suspended, 403);
    }

    public function index(Request $request)
    {
        $this->active($request);
        $request->validate(['case' => ['nullable', 'integer', 'min:1']]);
        $cases = ReturnCase::visibleTo($request->user())
            ->when($request->filled('case'), fn ($q) => $q->whereKey($request->integer('case')))
            ->with(['dispute.sellerOrder.seller', 'dispute.order', 'events', 'refund', 'logisticsProvider.user', 'rider.user'])
            ->latest('updated_at')->paginate(12)->withQueryString();
        if ($request->expectsJson()) {
            return response()->json(['data' => $cases->map(fn ($case) => [
                'id' => $case->id, 'dispute_id' => $case->admin_dispute_id, 'status' => $case->status,
                'order_reference' => $case->dispute->order->reference, 'amount_minor' => $case->amount_minor,
                'return_address' => $case->return_address, 'handover_method' => $case->handover_method,
                'pickup_address' => $case->pickup_address, 'scheduled_date' => $case->scheduled_date?->toDateString(),
                'tracking_code' => $case->tracking_code, 'logistics_provider_id' => $case->logistics_provider_id,
                'rider_id' => $case->rider_id, 'events' => $case->events->map->only(['status', 'notes', 'created_at']),
                'refund' => $case->refund,
            ]), 'current_page' => $cases->currentPage(), 'last_page' => $cases->lastPage()]);
        }
        $layout = match ($request->user()->role) {
            'seller' => 'layouts.seller', 'admin' => 'layouts.admin', 'logistics' => 'layouts.logistics', default => 'layouts.app',
        };
        $providers = LogisticsProvider::where('status', 'approved')->with('user')->orderBy('name')->get();
        $riders = Rider::whereIn('logistics_provider_id', $cases->pluck('logistics_provider_id')->filter())
            ->where('status', 'approved')->where('is_active', true)->where('online_until', '>', now())
            ->whereHas('user', fn ($q) => $q->where('role', 'courier')->where('status', 'active')->where('is_suspended', false))->with('user')->get();
        return view('returns.index', ['cases' => $cases, 'layout' => $layout, 'providers' => $providers,
            'riders' => $riders, 'shop' => $request->user()->seller]);
    }

    public function action(Request $request, int $id, string $action, ReturnWorkflow $workflow)
    {
        $this->active($request);
        ReturnCase::visibleTo($request->user())->findOrFail($id);
        $rules = ['notes' => ['required', 'string', 'min:3', 'max:2000']];
        $rules += match ($action) {
            'approve' => ['amount' => ['required', 'string', 'regex:/^\d{1,9}(\.\d{1,2})?$/'], 'return_address' => ['nullable', 'string', 'max:2000']],
            'schedule' => ['handover_method' => ['required', Rule::in(['pickup', 'dropoff'])],
                'scheduled_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
                'pickup_address' => ['required_if:handover_method,pickup', 'nullable', 'string', 'max:2000'],
                'logistics_provider_id' => ['required', 'integer', Rule::exists('logistics_providers', 'id')->where('status', 'approved')]],
            'dispatch' => ['rider_id' => ['required', 'integer']],
            'inspect' => ['result' => ['required', Rule::in(['accepted', 'rejected'])]],
            'refund' => ['method' => ['required', Rule::in(['gcash', 'bank', 'cash'])],
                'reference' => ['required', 'string', 'max:150', Rule::unique('refund_transactions')->where('method', $request->input('method'))],
                'paid_at' => ['required', 'date', 'before_or_equal:now'], 'paid_confirmation' => ['accepted'],
                'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120']],
            'reject', 'appeal', 'collect', 'receive' => [],
            default => abort(404),
        };
        if ($action === 'refund') abort_unless($request->user()->isAdmin() || $request->user()->role === 'logistics', 403);
        $data = $request->validate($rules);
        $path = null;
        try {
            if ($action === 'refund') {
                $path = $request->file('proof')->store('refund-proofs/'.$id, 'local');
                abort_unless($path, 422, 'Payment receipt could not be saved. Please retry.');
                $data['proof_path'] = $path;
            }
            $case = $workflow->act($request->user(), $id, $action, $data);
        } catch (\Throwable $error) {
            if ($path) Storage::disk('local')->delete($path);
            if ($error instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                throw ValidationException::withMessages(['reference' => 'This refund or payment reference has already been recorded.']);
            }
            throw $error;
        }
        return $request->expectsJson()
            ? response()->json(['id' => $case->id, 'status' => $case->status, 'amount_minor' => $case->amount_minor])
            : back()->with('success', 'Case updated: '.str_replace('_', ' ', $case->status).'.');
    }

    public function proof(Request $request, int $id)
    {
        $this->active($request);
        $case = ReturnCase::visibleTo($request->user())->with('refund')->findOrFail($id);
        $path = $case->refund?->proof_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->download($path);
    }
}

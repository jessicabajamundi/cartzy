<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Voucher, VoucherRedemption};
use App\Services\{NotificationService, VoucherService};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    /** How many buyers the last give/announce reached, and emails that matched no buyer. */
    private ?int $lastDelivered = null;

    private array $missingEmails = [];

    private function audit(string $action, string $description): void
    {
        DB::table('admin_audit_logs')->insert([
            'user_id' => Auth::id(), 'action' => $action, 'description' => $description,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function index(Request $request)
    {
        $vouchers = Voucher::whereNull('seller_id')->withCount('users')
            ->withSum(['redemptions as discount_given_minor' => fn ($q) => $q->where('status', 'applied')], 'discount_minor')
            ->when($request->query('search'), fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")))
            ->latest()->paginate(20)->withQueryString();
        $applied = VoucherRedemption::where('status', 'applied')->whereHas('voucher', fn ($q) => $q->whereNull('seller_id'));
        $stats = [
            'active' => Voucher::whereNull('seller_id')->get()->filter(fn ($v) => $v->status() === 'active')->count(),
            'uses' => (clone $applied)->count(),
            'given' => (clone $applied)->sum('discount_minor') / 100,
        ];
        // Seller-funded shop vouchers: the admin can only watch and pause them (sellers manage their own).
        $shopVouchers = Voucher::whereNotNull('seller_id')->with('seller:id,name,slug')
            ->withSum(['redemptions as discount_given_minor' => fn ($q) => $q->where('status', 'applied')], 'discount_minor')
            ->when($request->query('shop_search'), fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%")
                ->orWhereHas('seller', fn ($store) => $store->where('name', 'like', "%{$s}%"))))
            ->latest()->paginate(20, ['*'], 'shops_page')->withQueryString();

        return view('admin.vouchers', compact('vouchers', 'stats', 'shopVouchers'));
    }

    /** Sellers own their shop vouchers; the admin can only pause or resume them. */
    private function platformOnly(Voucher $voucher): void
    {
        abort_if($voucher->isShopVoucher(), 403, 'This is a seller\'s shop voucher. You can pause or resume it from the Seller vouchers tab.');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $voucher = DB::transaction(function () use ($data, $request) {
            $voucher = Voucher::create($data + ['created_by' => Auth::id()]);
            $this->audit('Voucher created', "{$voucher->name} — {$voucher->headline()}");
            $this->deliver($voucher, $request);

            return $voucher;
        });

        return redirect()->route('admin.vouchers.show', $voucher)->with('success', "Voucher \"{$voucher->name}\" created. ".$this->deliveryNote($voucher));
    }

    public function show(Voucher $voucher)
    {
        if ($voucher->isShopVoucher()) {
            return redirect()->route('admin.vouchers', ['tab' => 'shops', 'shop_search' => $voucher->code ?? $voucher->name]);
        }
        $voucher->loadCount('users');
        $redemptions = $voucher->redemptions()->with(['user:id,name,email', 'order:id,reference'])->latest()->paginate(20);
        $given = $voucher->redemptions()->where('status', 'applied')->sum('discount_minor') / 100;
        $holders = $voucher->users()->select('users.id', 'users.name', 'users.email')->latest('voucher_user.created_at')->limit(50)->get();

        return view('admin.voucher-show', compact('voucher', 'redemptions', 'given', 'holders'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $this->platformOnly($voucher);
        $data = $this->validated($request, $voucher);
        DB::transaction(function () use ($voucher, $data) {
            $voucher->update($data);
            $this->audit('Voucher updated', "{$voucher->name} — {$voucher->headline()}");
        });

        return back()->with('success', 'Voucher saved.');
    }

    public function toggle(Voucher $voucher)
    {
        $voucher->update(['is_active' => !$voucher->is_active]);
        $this->audit($voucher->is_active ? 'Voucher resumed' : 'Voucher paused', $voucher->name.($voucher->isShopVoucher() ? ' (shop voucher of '.$voucher->seller?->name.')' : ''));
        if ($voucher->isShopVoucher() && $voucher->seller) {
            NotificationService::notify($voucher->seller->user_id, 'product_update',
                $voucher->is_active ? 'Voucher resumed by cartzy' : 'Voucher paused by cartzy',
                $voucher->is_active ? "Your voucher \"{$voucher->name}\" is live again." : "Your voucher \"{$voucher->name}\" was paused by the cartzy team. Contact support if you have questions.",
                route('seller.vouchers'));
        }

        return back()->with('success', $voucher->is_active ? "\"{$voucher->name}\" is live again." : "\"{$voucher->name}\" is paused. Buyers can no longer use it.");
    }

    public function assign(Request $request, Voucher $voucher)
    {
        $this->platformOnly($voucher);
        DB::transaction(fn () => $this->deliver($voucher, $request, true));

        return back()->with('success', $this->deliveryNote($voucher));
    }

    public function destroy(Voucher $voucher)
    {
        $this->platformOnly($voucher);
        if ($voucher->redemptions()->exists()) {
            return back()->withErrors(['voucher' => 'This voucher has already been used on orders, so it is kept for your records. Pause it instead.']);
        }
        $name = $voucher->name;
        $voucher->delete();
        $this->audit('Voucher deleted', $name);

        return redirect()->route('admin.vouchers')->with('success', "Voucher \"{$name}\" deleted.");
    }

    /** Gives the voucher to the chosen buyers, or announces a public voucher to everyone. */
    private function deliver(Voucher $voucher, Request $request, bool $required = false): void
    {
        $input = $request->validate([
            'recipients' => [$required ? 'required' : 'nullable', Rule::in(['all_buyers', 'emails'])],
            'emails' => ['nullable', 'required_if:recipients,emails', 'string', 'max:20000'],
            'announce' => ['nullable', 'boolean'],
        ]);

        if (in_array($voucher->audience, ['assigned', 'code'], true) && !empty($input['recipients'])) {
            $buyers = User::where('role', User::ROLE_BUYER);
            if ($input['recipients'] === 'emails') {
                $emails = collect(preg_split('/[\s,;]+/', strtolower($input['emails']), -1, PREG_SPLIT_NO_EMPTY))->unique();
                $buyers->whereIn(DB::raw('LOWER(email)'), $emails);
                $found = (clone $buyers)->pluck('email')->map(fn ($email) => strtolower($email))->all();
                $this->missingEmails = $emails->diff($found)->values()->all();
            }
            $this->lastDelivered = app(VoucherService::class)->assign($voucher, $buyers->pluck('id'));
            $this->audit('Voucher given to buyers', "{$voucher->name} — {$this->lastDelivered} buyer(s)");
        } elseif (in_array($voucher->audience, ['public', 'new_buyers'], true) && !empty($input['announce'])) {
            $count = 0;
            User::where('role', User::ROLE_BUYER)
                ->when($voucher->audience === 'new_buyers', fn ($q) => $q->whereNotIn('id', DB::table('orders')->select('buyer_id')))
                ->select('id')->chunkById(200, function ($users) use ($voucher, &$count) {
                    foreach ($users as $user) {
                        NotificationService::notify($user->id, 'voucher', 'New voucher for you!',
                            "{$voucher->name}: {$voucher->headline()} ({$voucher->terms()}). It will be applied for you at checkout.",
                            route('buyer.dashboard', ['tab' => 'vouchers']));
                        $count++;
                    }
                });
            $this->lastDelivered = $count;
            $this->audit('Voucher announced', "{$voucher->name} — {$count} buyer(s) notified");
        }
    }

    private function deliveryNote(Voucher $voucher): string
    {
        $note = match (true) {
            $this->lastDelivered === null => '',
            in_array($voucher->audience, ['assigned', 'code'], true) => "Given to {$this->lastDelivered} new buyer(s) and they were notified.",
            default => "{$this->lastDelivered} buyer(s) were notified.",
        };
        if ($this->missingEmails) {
            $note .= ' No buyer account found for: '.implode(', ', array_slice($this->missingEmails, 0, 10)).(count($this->missingEmails) > 10 ? '…' : '');
        }

        return trim($note);
    }

    private function validated(Request $request, ?Voucher $voucher = null): array
    {
        $request->merge(['code' => $request->filled('code') ? strtoupper(trim($request->input('code'))) : null]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'discount_type' => ['required', Rule::in(array_keys(Voucher::TYPES))],
            'amount' => ['nullable', 'required_if:discount_type,fixed', 'numeric', 'min:1', 'max:1000000'],
            'percent' => ['nullable', 'required_if:discount_type,percent', 'integer', 'min:1', 'max:100'],
            'max_discount' => ['nullable', 'numeric', 'min:1', 'max:1000000'],
            'min_spend' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'audience' => ['required', Rule::in(array_keys(Voucher::AUDIENCES))],
            'code' => ['nullable', 'required_if:audience,code', 'regex:/^[A-Z0-9_-]{3,40}$/', Rule::unique('vouchers', 'code')->ignore($voucher?->id)],
            'total_limit' => ['nullable', 'integer', 'min:1', 'max:10000000'],
            'per_user_limit' => ['required', 'integer', 'min:0', 'max:1000'],
            'starts_at' => ['nullable', 'date_format:Y-m-d'],
            'ends_at' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_at', 'after_or_equal:today'],
        ], [
            'code.regex' => 'The code may only use letters, numbers, dashes and underscores (3 to 40 characters).',
            'amount.required_if' => 'Enter how many pesos the voucher takes off.',
            'percent.required_if' => 'Enter the percent the voucher takes off.',
            'ends_at.after_or_equal' => 'The end date must be today or later, and not before the start date.',
        ]);
        $minor = fn ($pesos) => $pesos === null || $pesos === '' ? null : (int) round($pesos * 100);
        $isPercent = $data['discount_type'] === 'percent';

        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'discount_type' => $data['discount_type'],
            'amount_minor' => $isPercent ? null : $minor($data['amount'] ?? null),
            'percent' => $isPercent ? $data['percent'] : null,
            'max_discount_minor' => $isPercent ? $minor($data['max_discount'] ?? null) : null,
            'min_spend_minor' => $minor($data['min_spend'] ?? 0) ?? 0,
            'audience' => $data['audience'],
            'code' => $data['code'] ?? null,
            'total_limit' => $data['total_limit'] ?? null,
            'per_user_limit' => $data['per_user_limit'],
            'starts_at' => !empty($data['starts_at']) ? Carbon::parse($data['starts_at'], 'Asia/Manila')->startOfDay()->utc() : null,
            'ends_at' => !empty($data['ends_at']) ? Carbon::parse($data['ends_at'], 'Asia/Manila')->endOfDay()->utc() : null,
        ];
    }
}

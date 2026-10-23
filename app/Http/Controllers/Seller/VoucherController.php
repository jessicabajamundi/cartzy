<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\{Seller, Voucher, VoucherRedemption};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Shop vouchers, Shopee/Lazada style: the store creates and pays for them, they only discount
 * that store's items, and buyers can stack one per store with one platform voucher.
 */
class VoucherController extends Controller
{
    private const TABS = ['all' => 'All', 'active' => 'Ongoing', 'scheduled' => 'Upcoming', 'paused' => 'Paused', 'ended' => 'Ended'];

    private function shop(): ?Seller
    {
        return Auth::user()->seller;
    }

    private function canManage(): bool
    {
        return Auth::user()->status === 'active' && (bool) $this->shop()?->isApproved();
    }

    private function owned(Voucher $voucher): Voucher
    {
        abort_unless($this->shop() && $voucher->seller_id === $this->shop()->id, 404);

        return $voucher;
    }

    public function index(Request $request)
    {
        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'all';
        $shop = $this->shop();
        $all = $shop
            ? $shop->vouchers()->withSum(['redemptions as discount_given_minor' => fn ($q) => $q->where('status', 'applied')], 'discount_minor')->latest()->get()
            : collect();
        $group = fn (Voucher $v) => in_array($v->status(), ['expired', 'used up'], true) ? 'ended' : $v->status();
        $counts = collect(self::TABS)->map(fn ($label, $key) => $key === 'all' ? $all->count() : $all->filter(fn ($v) => $group($v) === $key)->count());
        $applied = VoucherRedemption::where('status', 'applied')->whereIn('voucher_id', $all->pluck('id'));

        return view('seller.vouchers', [
            'shop' => $shop,
            'canManage' => $this->canManage(),
            'tab' => $tab,
            'tabs' => self::TABS,
            'counts' => $counts,
            'vouchers' => $tab === 'all' ? $all : $all->filter(fn ($v) => $group($v) === $tab)->values(),
            'stats' => [
                'live' => $counts['active'],
                'uses' => (clone $applied)->count(),
                'given' => (int) (clone $applied)->sum('discount_minor'),
            ],
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($this->canManage(), 403, 'An approved, active seller account is required to create vouchers.');
        $template = in_array($request->query('template'), ['amount', 'percent', 'shipping', 'code'], true) ? $request->query('template') : 'amount';

        return view('seller.voucher-form', ['shop' => $this->shop(), 'voucher' => null, 'locked' => false, 'template' => $template]);
    }

    public function store(Request $request)
    {
        abort_unless($this->canManage(), 403, 'An approved, active seller account is required to create vouchers.');
        $voucher = $this->shop()->vouchers()->create($this->validated($request) + [
            'funded_by' => 'seller', 'is_active' => true, 'created_by' => Auth::id(),
        ]);

        return redirect()->route('seller.vouchers')->with('success', "Voucher \"{$voucher->name}\" is ready. ".($voucher->starts_at?->isFuture()
            ? 'It starts on '.$voucher->starts_at->timezone('Asia/Manila')->format('M j').'.'
            : 'Buyers can use it on your items now.'));
    }

    public function edit(Voucher $voucher)
    {
        $this->owned($voucher);
        abort_unless($this->canManage(), 403);

        return view('seller.voucher-form', ['shop' => $this->shop(), 'voucher' => $voucher, 'locked' => $this->isLocked($voucher), 'template' => null]);
    }

    public function update(Request $request, Voucher $voucher)
    {
        $this->owned($voucher);
        abort_unless($this->canManage(), 403);
        $voucher->update($this->isLocked($voucher) ? $this->validatedLocked($request, $voucher) : $this->validated($request, $voucher));

        return redirect()->route('seller.vouchers')->with('success', "Voucher \"{$voucher->name}\" saved.");
    }

    public function toggle(Voucher $voucher)
    {
        $this->owned($voucher);
        abort_unless($this->canManage(), 403);
        $voucher->update(['is_active' => !$voucher->is_active]);

        return back()->with('success', $voucher->is_active ? "\"{$voucher->name}\" is live again." : "\"{$voucher->name}\" is paused. Buyers can't use it until you resume it.");
    }

    public function destroy(Voucher $voucher)
    {
        $this->owned($voucher);
        abort_unless($this->canManage(), 403);
        if ($voucher->redemptions()->exists()) {
            return back()->withErrors(['voucher' => "\"{$voucher->name}\" has already been used on orders, so it is kept for your records. Pause it instead."]);
        }
        $name = $voucher->name;
        $voucher->delete();

        return redirect()->route('seller.vouchers')->with('success', "Voucher \"{$name}\" deleted.");
    }

    /** Once buyers have used a voucher, its deal is fixed; only the name, quantity and end date can change. */
    private function isLocked(Voucher $voucher): bool
    {
        return $voucher->used_count > 0 || $voucher->redemptions()->exists();
    }

    private function validated(Request $request, ?Voucher $voucher = null): array
    {
        $request->merge(['code' => $request->filled('code') ? strtoupper(trim($request->input('code'))) : null]);
        $today = now('Asia/Manila')->format('Y-m-d');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'discount_type' => ['required', Rule::in(['fixed', 'percent', 'free_shipping'])],
            'amount' => ['nullable', 'required_if:discount_type,fixed', 'numeric', 'min:1', 'max:100000'],
            'percent' => ['nullable', 'required_if:discount_type,percent', 'integer', 'min:1', 'max:'.Voucher::SHOP_MAX_PERCENT],
            'max_discount' => ['nullable', 'numeric', 'min:1', 'max:100000'],
            'shipping_cap' => ['nullable', 'numeric', 'min:1', 'max:100000'],
            'min_spend' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'audience' => ['required', Rule::in(['public', 'code'])],
            'code' => ['nullable', 'required_if:audience,code', 'regex:/^[A-Z0-9_-]{3,20}$/', Rule::unique('vouchers', 'code')->ignore($voucher?->id)],
            'total_limit' => ['required', 'integer', 'min:1', 'max:100000'],
            'per_user_limit' => ['required', 'integer', 'min:1', 'max:10'],
            'starts_at' => ['required', 'date_format:Y-m-d', ...($voucher ? [] : ['after_or_equal:'.$today])],
            'ends_at' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_at', 'after_or_equal:'.$today],
        ], $this->messages());
        validator($data, [])->after(function (Validator $validator) use ($data) {
            if ($data['discount_type'] === 'fixed' && $data['amount'] * 2 > $data['min_spend']) {
                $validator->errors()->add('min_spend', 'Set a minimum spend of at least ₱'.number_format($data['amount'] * 2, 0).' (twice the discount), so each order stays profitable.');
            }
            if (Carbon::parse($data['starts_at'])->diffInDays(Carbon::parse($data['ends_at'])) > Voucher::SHOP_MAX_DAYS) {
                $validator->errors()->add('ends_at', 'A voucher can run for up to '.Voucher::SHOP_MAX_DAYS.' days. Pick an earlier end date.');
            }
        })->validate();
        $minor = fn ($pesos) => $pesos === null || $pesos === '' ? null : (int) round($pesos * 100);
        $isPercent = $data['discount_type'] === 'percent';

        return [
            'name' => $data['name'],
            'discount_type' => $data['discount_type'],
            // Free shipping: amount_minor is the most taken off this store's parcel fee (null = fully free).
            'amount_minor' => match ($data['discount_type']) {
                'fixed' => $minor($data['amount']),
                'free_shipping' => $minor($data['shipping_cap'] ?? null),
                default => null,
            },
            'percent' => $isPercent ? $data['percent'] : null,
            'max_discount_minor' => $isPercent ? $minor($data['max_discount'] ?? null) : null,
            'min_spend_minor' => $minor($data['min_spend']) ?? 0,
            'audience' => $data['audience'],
            'code' => $data['audience'] === 'code' ? $data['code'] : null,
            'total_limit' => $data['total_limit'],
            'per_user_limit' => $data['per_user_limit'],
            'starts_at' => Carbon::parse($data['starts_at'], 'Asia/Manila')->startOfDay()->utc(),
            'ends_at' => Carbon::parse($data['ends_at'], 'Asia/Manila')->endOfDay()->utc(),
        ];
    }

    private function validatedLocked(Request $request, Voucher $voucher): array
    {
        $start = $voucher->starts_at?->timezone('Asia/Manila') ?? now('Asia/Manila');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'total_limit' => ['required', 'integer', 'min:'.max(1, $voucher->used_count), 'max:100000'],
            'ends_at' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Manila')->format('Y-m-d'), 'before_or_equal:'.$start->copy()->addDays(Voucher::SHOP_MAX_DAYS)->format('Y-m-d')],
        ], $this->messages() + [
            'total_limit.min' => 'The quantity can\'t be lower than the '.$voucher->used_count.' already used.',
            'ends_at.before_or_equal' => 'A voucher can run for up to '.Voucher::SHOP_MAX_DAYS.' days from its start date.',
        ]);

        return [
            'name' => $data['name'],
            'total_limit' => $data['total_limit'],
            'ends_at' => Carbon::parse($data['ends_at'], 'Asia/Manila')->endOfDay()->utc(),
        ];
    }

    private function messages(): array
    {
        return [
            'amount.required_if' => 'Enter how many pesos the voucher takes off.',
            'percent.required_if' => 'Enter the percent the voucher takes off.',
            'percent.max' => 'A shop voucher can take off up to '.Voucher::SHOP_MAX_PERCENT.'%.',
            'code.required_if' => 'Enter the code buyers will type at checkout.',
            'code.regex' => 'Use 3 to 20 letters, numbers, dashes or underscores (no spaces).',
            'code.unique' => 'That code is already taken. Try another one.',
            'starts_at.after_or_equal' => 'The start date can\'t be in the past.',
            'ends_at.after_or_equal' => 'The end date must be today or later, and not before the start date.',
            'total_limit.required' => 'Enter how many vouchers buyers can use in total.',
        ];
    }
}

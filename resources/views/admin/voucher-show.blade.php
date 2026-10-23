@extends('layouts.admin')
@section('page_title', $voucher->name)
@section('page_description', $voucher->headline().' · '.$voucher->terms().' · '.\App\Models\Voucher::AUDIENCES[$voucher->audience])
@section('content')
@php $status = $voucher->status(); @endphp
<p><a class="text-link" href="{{ route('admin.vouchers') }}">&larr; All vouchers</a></p>

<div class="stats-grid three spaced">
    <article class="stat-card"><span>Status</span><strong>{{ ucfirst($status) }}</strong><small>{{ $voucher->code ? 'Code '.$voucher->code : 'No code needed' }}</small></article>
    <article class="stat-card"><span>Times used</span><strong>{{ number_format($voucher->used_count) }}{{ $voucher->total_limit ? ' / '.number_format($voucher->total_limit) : '' }}</strong><small>{{ $voucher->per_user_limit ? $voucher->per_user_limit.' per buyer' : 'Unlimited per buyer' }}</small></article>
    <article class="stat-card"><span>Discounts given</span><strong>₱{{ number_format($given, 2) }}</strong><small>Paid by the platform</small></article>
</div>

<section class="panel padded">
    <div class="voucher-actions">
        <form method="POST" action="{{ route('admin.vouchers.toggle', $voucher) }}">@csrf<button class="button {{ $voucher->is_active ? 'subtle' : '' }}">{{ $voucher->is_active ? 'Pause voucher' : 'Resume voucher' }}</button></form>
        @if($voucher->used_count === 0 && $redemptions->isEmpty())
            <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}" data-confirm="Delete this voucher? This cannot be undone.">@csrf @method('DELETE')<button class="button danger">Delete</button></form>
        @endif
    </div>
</section>

@if(in_array($voucher->audience, ['assigned', 'code']))
<section class="panel">
    <header class="panel-header"><div><h2>Give to buyers</h2><p>{{ number_format($voucher->users_count) }} buyer(s) have this voucher in their wallet. New recipients get a notification, and it is applied for them at checkout.</p></div></header>
    <form class="padded" method="POST" action="{{ route('admin.vouchers.assign', $voucher) }}">
        @csrf
        <div class="form-grid voucher-form">
            <fieldset class="full">
                <legend>Recipients</legend>
                <label class="voucher-choice"><input type="radio" name="recipients" value="emails" @checked(old('recipients', 'emails') === 'emails')> Specific buyers</label>
                <label class="voucher-choice"><input type="radio" name="recipients" value="all_buyers" @checked(old('recipients') === 'all_buyers')> All buyers</label>
                <label class="full">Buyer emails (one per line or separated by commas)
                    <textarea name="emails" rows="3" placeholder="juan@example.com, maria@example.com">{{ old('emails') }}</textarea>
                </label>
            </fieldset>
        </div>
        <div class="spaced"><button class="button">Give voucher</button></div>
    </form>
    @if($holders->isNotEmpty())
        <div class="table-scroll"><table>
            <thead><tr><th>Buyer</th><th>Received</th><th>How</th></tr></thead>
            <tbody>
            @foreach($holders as $holder)
                <tr><td><strong>{{ $holder->name }}</strong><small>{{ $holder->email }}</small></td><td class="nowrap">{{ \Carbon\Carbon::parse($holder->pivot->created_at)->format('M j, Y') }}</td><td>{{ $holder->pivot->source === 'code' ? 'Entered the code' : 'Given by admin' }}</td></tr>
            @endforeach
            </tbody>
        </table></div>
    @endif
</section>
@endif

<section class="panel padded">
    <details @if($errors->any()) open @endif>
        <summary class="text-link">Edit voucher details</summary>
        <form class="spaced" method="POST" action="{{ route('admin.vouchers.update', $voucher) }}">
            @csrf
            @include('admin.partials.voucher-form', ['voucher' => $voucher])
            <div class="spaced"><button class="button">Save changes</button></div>
        </form>
    </details>
</section>

<section class="panel">
    <header class="panel-header"><div><h2>Usage history</h2><p>Every order this voucher was used on. Reversed means the order was cancelled and the voucher returned to the buyer.</p></div></header>
    <div class="table-scroll"><table>
        <thead><tr><th>Order</th><th>Buyer</th><th>Date</th><th>Status</th><th class="numeric">Discount</th></tr></thead>
        <tbody>
        @forelse($redemptions as $r)
            <tr><td><strong>{{ $r->order?->reference ?? 'Removed order' }}</strong></td><td>{{ $r->user?->name ?? 'Unavailable buyer' }}<small>{{ $r->user?->email }}</small></td><td class="nowrap">{{ $r->created_at->format('M j, Y') }}</td><td><span class="badge {{ $r->status === 'applied' ? 'good' : '' }}">{{ ucfirst($r->status) }}</span></td><td class="numeric">₱{{ number_format($r->discount_minor / 100, 2) }}</td></tr>
        @empty
            <tr><td colspan="5"><div class="empty"><strong>Not used yet</strong><p>Orders that use this voucher will appear here.</p></div></td></tr>
        @endforelse
        </tbody>
    </table></div>
    @include('admin.partials.pagination', ['paginator' => $redemptions])
</section>
@endsection

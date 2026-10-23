@extends('layouts.admin')
@section('page_title', 'Shipping rates')
@section('page_description', 'Each store ships its own parcel, like Shopee and Lazada. The fee depends on how far it travels: from the store\'s pickup province to the buyer\'s province. Changes apply to new orders right away.')
@section('content')
<section class="panel">
    <header class="panel-header"><div><h2>Fee per zone</h2><p>Free shipping vouchers (yours and sellers') are taken off these fees. The courier is always paid the full fee.</p></div></header>
    <form method="POST" action="{{ route('admin.shipping.update') }}" class="padded">
        @csrf
        <div class="table-scroll"><table>
            <thead><tr><th>Zone</th><th>When it applies</th><th style="width:200px">Fee (₱)</th></tr></thead>
            <tbody>
            @foreach($zones as $key => $zone)
                <tr>
                    <td><strong>{{ $zone['label'] }}</strong></td>
                    <td><small>{{ $zone['description'] }}</small></td>
                    <td>
                        <input type="number" name="fees[{{ $key }}]" min="1" max="10000" step="0.01" required value="{{ old('fees.'.$key, number_format($zone['fee_minor'] / 100, 2, '.', '')) }}" aria-label="Fee for {{ $zone['label'] }}">
                        @error('fees.'.$key)<small style="color:#b42318;font-weight:600">{{ $message }}</small>@enderror
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        <div class="spaced" style="margin-top:16px"><button class="button">Save shipping rates</button></div>
    </form>
</section>

<section class="panel">
    <header class="panel-header"><div><h2>Example routes</h2><p>What a buyer pays for one store's parcel with the current rates.</p></div></header>
    <div class="table-scroll"><table>
        <thead><tr><th>Store ships from</th><th>Buyer lives in</th><th>Zone</th><th class="numeric">Fee</th></tr></thead>
        <tbody>
        @foreach($examples as [$from, $to])
            @php $quote = \App\Services\ShippingCalculator::quote($from, $to); @endphp
            <tr><td>{{ $from }}</td><td>{{ $to }}</td><td>{{ $quote['label'] }}</td><td class="numeric">₱{{ number_format($quote['fee_minor'] / 100, 2) }}</td></tr>
        @endforeach
        </tbody>
    </table></div>
    <p class="padded" style="padding-top:0"><small>Stores without a pickup address ship from {{ config('shipping.default_origin') }}. The Standard rate is used when an address has no recognised province.</small></p>
</section>
@endsection

@extends('layouts.admin')
@section('page_title', 'Product & Seller Compliance')
@section('page_description', 'Verify product categories against sellers’ registered lines of business, flag violations, and enforce actions.')
@section('content')
<section class="panel">
    <form method="GET" class="filters">
        <label class="search-field">Product
            <input name="search" value="{{ request('search') }}" placeholder="Search product name or keyword">
        </label>
        <label>Visibility
            <select name="status">
                <option value="">All products</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </label>
        <button class="button">Apply filters</button>
        <a class="button subtle" href="{{ route('admin.compliance') }}">Reset</a>
    </form>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Product & Category</th>
                    <th>Store & Registered Line</th>
                    <th>Category Verification</th>
                    <th>Listing Status</th>
                    <th>Enforcement Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                @php
                    $lob = strtolower($product->seller?->user?->line_of_business ?? '');
                    $cat = strtolower($product->category?->name ?? '');
                    $isGeneral = str_contains($lob, 'general') || str_contains($lob, 'other');
                    $isMatch = $isGeneral || (!empty($lob) && !empty($cat) && (
                        str_contains($lob, $cat) || str_contains($cat, $lob) ||
                        (str_contains($lob, 'electronic') && (str_contains($cat, 'electronic') || str_contains($cat, 'phone') || str_contains($cat, 'appliance'))) ||
                        (str_contains($lob, 'fashion') && (str_contains($cat, 'clothing') || str_contains($cat, 'shoe') || str_contains($cat, 'wear') || str_contains($cat, 'jewelry'))) ||
                        (str_contains($lob, 'beauty') && (str_contains($cat, 'beauty') || str_contains($cat, 'health'))) ||
                        (str_contains($lob, 'home') && (str_contains($cat, 'home') || str_contains($cat, 'living') || str_contains($cat, 'textile'))) ||
                        (str_contains($lob, 'sports') && str_contains($cat, 'sport')) ||
                        (str_contains($lob, 'toy') && str_contains($cat, 'toy'))
                    ));
                    $mismatch = !empty($lob) && !empty($cat) && !$isMatch;
                    $sellerUser = $product->seller?->user;
                    $isSellerSuspended = $sellerUser && ($sellerUser->is_suspended || $sellerUser->status === 'suspended');
                @endphp
                <tr>
                    <td>
                        <strong>{{ $product->name }}</strong>
                        <small>Product #{{ $product->id }} · {{ $product->category?->name ?? 'Uncategorized' }}</small>
                    </td>
                    <td>
                        <strong>{{ $product->seller?->name ?? 'No store assigned' }}</strong>
                        <small>Registered: {{ $product->seller?->user?->line_of_business ?? 'Unspecified' }}</small>
                        <small class="muted">{{ $product->seller?->user?->email }}</small>
                    </td>
                    <td>
                        @if($mismatch)
                            <span class="badge warning" title="Product category doesn't match seller's declared line of business">Category Mismatch</span>
                        @elseif($isMatch && !empty($lob))
                            <span class="badge good">Verified</span>
                        @else
                            <span class="badge">Standard</span>
                        @endif
                        @if($isSellerSuspended)
                            <small><span class="badge danger" style="margin-top: 4px;">Seller Suspended</span></small>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $product->is_active ? 'good' : '' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 8px; min-width: 170px;">
                            {{-- Product listing visibility toggle --}}
                            <form action="{{ route('admin.compliance.action', $product->id) }}" method="POST" data-confirm="Change this product’s visibility?">
                                @csrf
                                <input type="hidden" name="action" value="{{ $product->is_active ? 'suspend_product' : 'activate' }}">
                                <button type="submit" class="button subtle" style="width: 100%; justify-content: center;">
                                    {{ $product->is_active ? 'Deactivate Listing' : 'Activate Listing' }}
                                </button>
                            </form>

                            @if($product->seller_id)
                            <details class="review-details" style="width: 100%;">
                                <summary style="font-size: 11px;">Seller Enforcement ▾</summary>
                                <div style="padding-top: 8px; gap: 12px;">
                                    {{-- Issue Warning form --}}
                                    <form method="POST" action="{{ route('admin.compliance.seller.warning', $product->seller_id) }}" class="form-stack">
                                        @csrf
                                        <label style="font-size: 11px;">
                                            Issue Warning to Seller:
                                            <textarea name="reason" rows="2" required placeholder="Explain violation (e.g. prohibited product or category mismatch)" style="font-size: 12px;"></textarea>
                                        </label>
                                        <button type="submit" class="button subtle" style="width: 100%; justify-content: center; font-size: 11px;">Send Warning</button>
                                    </form>

                                    {{-- Suspend Seller Account --}}
                                    @if(!$isSellerSuspended)
                                    <form method="POST" action="{{ route('admin.compliance.seller.suspend', $product->seller_id) }}" data-confirm="Are you sure you want to suspend this seller account and deactivate all their listings?">
                                        @csrf
                                        <input type="hidden" name="reason" value="Policy violation on product #{{ $product->id }} ({{ $product->name }})">
                                        <button type="submit" class="button danger" style="width: 100%; font-size: 11px; justify-content: center;">
                                            Suspend Seller Account
                                        </button>
                                    </form>
                                    @else
                                    <small class="muted" style="text-align: center; display: block;">Seller account is already suspended.</small>
                                    @endif
                                </div>
                            </details>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="empty">
                            <strong>No products found</strong>
                            <p>Products stored in your catalog will appear here.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $products])
</section>
@endsection

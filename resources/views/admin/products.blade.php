@extends('layouts.admin')
@section('page_title', 'Seller Products & Approvals')
@section('page_description', 'Monitor all seller products, inspect variations and pricing, and approve or reject marketplace listings.')

@section('content')
<!-- KPI Metric Cards -->
<div class="stats-grid dashboard-stats">
    <a class="stat-card" href="{{ route('admin.products') }}">
        <div class="metric-heading">
            <span class="metric-icon">@include('admin.partials.icon', ['name' => 'box'])</span>
            <span>All Products</span>
        </div>
        <span>Total catalog listings</span>
        <strong>{{ number_format($statusCounts['all']) }}</strong>
        <small>Total products across all verified sellers</small>
    </a>

    <a class="stat-card" href="{{ route('admin.products', ['status' => 'pending']) }}" style="@if($statusCounts['pending'] > 0) border-top-color: #D97706; background: #FFFDF5; @endif">
        <div class="metric-heading">
            <span class="metric-icon" style="@if($statusCounts['pending'] > 0) background: #FEF3C7; color: #B45309; @endif">@include('admin.partials.icon', ['name' => 'file'])</span>
            <span style="@if($statusCounts['pending'] > 0) color: #B45309; font-weight: 700; @endif">Needs Action</span>
        </div>
        <span>Pending Approval</span>
        <strong style="@if($statusCounts['pending'] > 0) color: #B45309; @endif">{{ number_format($statusCounts['pending']) }}</strong>
        <small>{{ $statusCounts['pending'] > 0 ? 'Products awaiting review before appearing live →' : 'Review queue is currently clear' }}</small>
    </a>

    <a class="stat-card" href="{{ route('admin.products', ['status' => 'approved']) }}">
        <div class="metric-heading">
            <span class="metric-icon" style="background: #E8F5E9; color: #2E7D32;">@include('admin.partials.icon', ['name' => 'shield'])</span>
            <span>Live on Storefront</span>
        </div>
        <span>Approved Products</span>
        <strong>{{ number_format($statusCounts['approved']) }}</strong>
        <small>Active and available for buyers to purchase</small>
    </a>

    <a class="stat-card" href="{{ route('admin.products', ['status' => 'rejected']) }}">
        <div class="metric-heading">
            <span class="metric-icon" style="background: var(--rose-light); color: var(--rose-ink);">@include('admin.partials.icon', ['name' => 'archive'])</span>
            <span>Disapproved</span>
        </div>
        <span>Rejected Listings</span>
        <strong>{{ number_format($statusCounts['rejected']) }}</strong>
        <small>Listings flagged or rejected with feedback</small>
    </a>
</div>

<!-- Main Workspace Panel -->
<section class="panel">
    <!-- Filter Toolbar -->
    <form method="GET" class="filters" action="{{ route('admin.products') }}">
        <label class="search-field">Search
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by product name, SKU, seller name or keyword">
        </label>

        <label>Approval Status
            <select name="status">
                <option value="">All statuses ({{ $statusCounts['all'] }})</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending Approval ({{ $statusCounts['pending'] }})</option>
                <option value="approved" @selected(request('status') === 'approved')>Approved ({{ $statusCounts['approved'] }})</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected ({{ $statusCounts['rejected'] }})</option>
            </select>
        </label>

        <label>Category
            <select name="category">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>Seller / Store
            <select name="seller">
                <option value="">All Stores</option>
                @foreach($sellers as $seller)
                    <option value="{{ $seller->id }}" @selected((string) request('seller') === (string) $seller->id)>
                        {{ $seller->name }} ({{ $seller->user?->name ?? 'User' }})
                    </option>
                @endforeach
            </select>
        </label>

        <div style="display: flex; gap: 8px;">
            <button class="button" type="submit">Apply filters</button>
            <a class="button subtle" href="{{ route('admin.products') }}">Reset</a>
        </div>
    </form>

    <!-- Product Approvals Table -->
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th style="min-width: 280px;">Product & Category</th>
                    <th style="min-width: 200px;">Seller & Store</th>
                    <th style="min-width: 170px;">Pricing & Inventory</th>
                    <th style="min-width: 160px;">Approval Status</th>
                    <th style="min-width: 240px;">Review Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                @php
                    $cover = $product->coverImage?->path;
                    $coverUrl = $cover ? (str_starts_with($cover, 'http') || str_starts_with($cover, '/') ? $cover : Storage::disk('public')->url($cover)) : asset('images/logo-transparent.png');
                    $variants = $product->variants;
                    $totalStock = $variants->sum('stock');
                    $prices = $variants->pluck('price_minor')->filter();
                    $minPrice = $prices->isNotEmpty() ? $prices->min() / 100 : 0;
                    $maxPrice = $prices->isNotEmpty() ? $prices->max() / 100 : 0;
                    $priceDisplay = $minPrice === $maxPrice ? '₱' . number_format($minPrice, 2) : '₱' . number_format($minPrice, 2) . ' – ₱' . number_format($maxPrice, 2);
                @endphp
                <tr>
                    <!-- 1. Product & Category -->
                    <td>
                        <div style="display: flex; gap: 14px; align-items: flex-start;">
                            <img src="{{ $coverUrl }}" alt="{{ $product->name }}" style="width: 54px; height: 54px; object-fit: cover; border-radius: 8px; border: 1px solid var(--line); flex-shrink: 0; background: #fff;">
                            <div style="min-width: 0;">
                                <strong style="font-size: 13px; line-height: 1.35; margin-bottom: 3px;">
                                    {{ $product->name }}
                                </strong>
                                <small style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <span style="font-weight: 600; color: var(--accent);">{{ $product->category?->name ?? 'Uncategorized' }}</span>
                                    @if($product->gender && $product->gender !== 'unisex')
                                        <span class="badge" style="font-size: 9px; padding: 1px 6px;">{{ ucfirst($product->gender) }}</span>
                                    @endif
                                    @if($product->badge)
                                        <span class="badge" style="background: var(--brand-blend); color: var(--rose-ink); font-size: 9px; padding: 1px 6px;">{{ $product->badge }}</span>
                                    @endif
                                </small>
                                <small class="muted" style="margin-top: 4px; display: block;">
                                    Product #{{ $product->id }} &middot; Added {{ $product->created_at?->timezone('Asia/Manila')->format('M j, Y g:i A') }}
                                </small>
                                <div style="margin-top: 5px;">
                                    <a class="text-link" href="{{ route('products.show', $product->id) }}" target="_blank" rel="noopener" style="font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                                        View storefront preview ↗
                                    </a>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- 2. Seller & Store -->
                    <td>
                        <strong>
                            @if($product->seller)
                                <a href="{{ route('store.show', $product->seller->slug) }}" target="_blank" rel="noopener" class="text-link" style="font-size: 13px;">
                                    {{ $product->seller->name }} ↗
                                </a>
                            @else
                                <span class="muted">No Store Assigned</span>
                            @endif
                        </strong>
                        <small>Seller: {{ $product->seller?->user?->name ?? 'N/A' }}</small>
                        <small class="muted">{{ $product->seller?->user?->email }}</small>
                        @if($product->seller?->user?->line_of_business)
                            <small style="margin-top: 4px; color: var(--muted); font-size: 11px;">
                                Business: <em>{{ $product->seller->user->line_of_business }}</em>
                            </small>
                        @endif
                    </td>

                    <!-- 3. Pricing & Inventory -->
                    <td>
                        <strong style="font-size: 13px; color: var(--ink);">{{ $priceDisplay }}</strong>
                        <small style="margin-top: 3px;">
                            Total stock: <strong>{{ number_format($totalStock) }} units</strong>
                        </small>
                        <small class="muted">
                            {{ $variants->count() }} {{ \Illuminate\Support\Str::plural('variation', $variants->count()) }}
                        </small>
                        @if($product->discount_type)
                            <small>
                                <span class="badge" style="background: #FEF3C7; color: #92400E; margin-top: 4px;">
                                    Sale: {{ $product->discount_type === 'percentage' ? number_format($product->discount_value) . '% OFF' : '₱' . number_format($product->discount_value, 2) . ' OFF' }}
                                </span>
                            </small>
                        @endif
                    </td>

                    <!-- 4. Approval Status -->
                    <td>
                        @if($product->isPending())
                            <span class="badge warning" style="background: #FEF3C7; color: #B45309; font-weight: 700; padding: 4px 10px;">
                                ⏳ Pending Review
                            </span>
                            <small class="muted" style="margin-top: 5px;">Awaiting admin decision</small>
                        @elseif($product->isApproved())
                            <span class="badge good" style="background: #E8F5E9; color: #2E7D32; font-weight: 700; padding: 4px 10px;">
                                ✓ Approved & Live
                            </span>
                            @if($product->reviewed_at)
                                <small class="muted" style="margin-top: 5px;">
                                    Approved {{ $product->reviewed_at->timezone('Asia/Manila')->format('M j, Y') }}
                                    @if($product->reviewer) by {{ $product->reviewer->name }} @endif
                                </small>
                            @endif
                        @elseif($product->isRejected())
                            <span class="badge danger" style="background: var(--rose-light); color: var(--rose-ink); font-weight: 700; padding: 4px 10px;">
                                ✕ Disapproved / Rejected
                            </span>
                            @if($product->rejection_reason)
                                <div style="margin-top: 6px; padding: 6px 9px; background: #FFF5F3; border-left: 2px solid var(--rose-border); border-radius: 4px; font-size: 11px; color: var(--rose-ink);">
                                    <strong>Reason:</strong> {{ $product->rejection_reason }}
                                </div>
                            @endif
                            @if($product->reviewed_at)
                                <small class="muted" style="margin-top: 4px;">
                                    Rejected {{ $product->reviewed_at->timezone('Asia/Manila')->format('M j, Y') }}
                                </small>
                            @endif
                        @endif
                    </td>

                    <!-- 5. Review Actions -->
                    @php
                        $galleryUrls = [];
                        foreach ($product->images as $img) {
                            $galleryUrls[] = str_starts_with($img->path, 'http') || str_starts_with($img->path, '/') ? $img->path : Storage::disk('public')->url($img->path);
                        }
                        if (empty($galleryUrls)) {
                            $galleryUrls[] = $coverUrl;
                        }

                        $variantsData = $variants->map(function($v) {
                            return [
                                'name' => $v->name,
                                'sku' => $v->sku ?: '—',
                                'price' => '₱' . number_format($v->price_minor / 100, 2),
                                'stock' => (int) $v->stock,
                            ];
                        })->values()->all();

                        $discountBadge = null;
                        if ($product->discount_type) {
                            $discountBadge = $product->discount_type === 'percentage'
                                ? number_format($product->discount_value) . '% OFF'
                                : '₱' . number_format($product->discount_value, 2) . ' OFF';
                        }

                        $productJson = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'category' => $product->category?->name ?? 'Uncategorized',
                            'gender' => $product->gender && $product->gender !== 'unisex' ? ucfirst($product->gender) : null,
                            'badge' => $product->badge,
                            'cover_url' => $coverUrl,
                            'gallery' => $galleryUrls,
                            'description' => $product->description ?: 'No product description provided.',
                            'price_display' => $priceDisplay,
                            'total_stock' => $totalStock,
                            'variants_count' => $variants->count(),
                            'variants' => $variantsData,
                            'discount' => $discountBadge,
                            'seller_name' => $product->seller?->name ?? 'No Store Assigned',
                            'seller_slug' => $product->seller?->slug,
                            'seller_owner' => $product->seller?->user?->name ?? 'N/A',
                            'seller_email' => $product->seller?->user?->email,
                            'seller_lob' => $product->seller?->user?->line_of_business,
                            'seller_url' => $product->seller ? route('store.show', $product->seller->slug) : null,
                            'status' => $product->approval_status ?? 'pending',
                            'rejection_reason' => $product->rejection_reason,
                            'reviewed_at' => $product->reviewed_at ? $product->reviewed_at->timezone('Asia/Manila')->format('M j, Y g:i A') : null,
                            'reviewer_name' => $product->reviewer?->name,
                            'storefront_url' => route('products.show', $product->id),
                            'approve_url' => route('admin.products.approve', $product->id),
                            'reject_url' => route('admin.products.reject', $product->id),
                            'is_approved' => $product->isApproved(),
                            'is_rejected' => $product->isRejected(),
                            'is_pending' => $product->isPending(),
                        ];
                    @endphp
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 7px; min-width: 170px;">
                            <!-- Review Details Modal Trigger -->
                            <button type="button" class="button subtle js-open-product-details" style="width: 100%; min-height: 35px; padding: 6px 12px; font-size: 11px; justify-content: center; gap: 6px; font-weight: 600;">
                                @include('admin.partials.icon', ['name' => 'box'])
                                <span>Review Details</span>
                            </button>

                            <!-- Decision Action Buttons -->
                            <div style="display: flex; gap: 6px;">
                                @if(!$product->isApproved())
                                    <form method="POST" action="{{ route('admin.products.approve', $product->id) }}" style="flex: 1;" data-confirm="Approve '{{ addslashes($product->name) }}' to make it live for shoppers?">
                                        @csrf
                                        <button type="submit" class="button" style="width: 100%; min-height: 33px; padding: 5px 8px; font-size: 11px; background: #2E7D32; border-color: #2E7D32; color: #fff; justify-content: center;">
                                            ✓ Approve
                                        </button>
                                    </form>
                                @endif

                                @if(!$product->isRejected())
                                    <button type="button" class="button subtle js-open-product-reject" style="flex: 1; min-height: 33px; padding: 5px 8px; font-size: 11px; color: var(--rose-ink); border-color: var(--rose-border); justify-content: center;">
                                        ✕ Reject
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Hidden JSON payload for modals --}}
                        <script type="application/json" class="product-payload">
                            {!! json_encode($productJson, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
                        </script>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="empty">
                            <span class="icon">@include('admin.partials.icon', ['name' => 'box'])</span>
                            <strong>No seller products found</strong>
                            <p>No products match the selected filters or search terms. Try adjusting your query or resetting filters.</p>
                            <a class="button subtle" href="{{ route('admin.products') }}" style="margin-top: 14px;">View all products</a>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @include('admin.partials.pagination', ['paginator' => $products])
</section>

{{-- ======================================================== --}}
{{-- MODAL 1: Product Review Details Pop-up                   --}}
{{-- ======================================================== --}}
<dialog id="product-details-modal" class="admin-modal" aria-labelledby="details-modal-title">
    <div class="admin-modal-panel">
        <header class="admin-modal-header">
            <div>
                <p class="eyebrow" style="margin-bottom: 3px;">PRODUCT REVIEW & SPECIFICATIONS</p>
                <h2 id="details-modal-title" style="font-size: 17px; margin: 0; line-height: 1.3;">Product Details</h2>
            </div>
            <button type="button" class="icon-button js-close-modal" aria-label="Close dialog" style="font-size: 22px; width: 36px; height: 36px;">&times;</button>
        </header>

        <div class="admin-modal-body">
            <div class="modal-product-layout">
                <!-- Left: Media & Description -->
                <div class="modal-left-col">
                    <div class="modal-featured-image-wrap">
                        <img id="details-featured-img" src="" alt="Product cover" class="modal-featured-img">
                    </div>
                    <div id="details-gallery" class="modal-gallery-strip" style="display: none;"></div>

                    <div class="modal-info-card" style="margin-top: 16px;">
                        <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 8px;">Product Description</strong>
                        <div id="details-description" class="modal-description-text"></div>
                    </div>
                </div>

                <!-- Right: Meta, Variations & Decision History -->
                <div class="modal-right-col">
                    <!-- Status Banner -->
                    <div id="details-status-banner" class="modal-status-banner"></div>

                    <!-- Store & Seller info -->
                    <div class="modal-info-card">
                        <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 10px;">Seller & Store Information</strong>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
                            <div>
                                <small class="muted" style="display: block;">Store Name</small>
                                <a id="details-store-name" href="#" target="_blank" rel="noopener" class="text-link" style="font-weight: 600;"></a>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Seller Account</small>
                                <span id="details-seller-owner" style="font-weight: 500;"></span>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Contact Email</small>
                                <span id="details-seller-email" style="font-weight: 500; word-break: break-all;"></span>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Declared Line of Business</small>
                                <span id="details-seller-lob" style="font-weight: 500;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Inventory info -->
                    <div class="modal-info-card" style="margin-top: 14px;">
                        <strong style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); margin-bottom: 10px;">Catalog & Pricing Summary</strong>
                        <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: baseline;">
                            <div>
                                <small class="muted" style="display: block;">Price Range</small>
                                <strong id="details-price" style="font-size: 15px; color: var(--ink);"></strong>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Total Available Units</small>
                                <strong id="details-stock" style="font-size: 15px; color: var(--ink);"></strong>
                            </div>
                            <div id="details-discount-wrap" style="display: none;">
                                <small class="muted" style="display: block;">Discount</small>
                                <span id="details-discount-badge" class="badge" style="background: #FEF3C7; color: #92400E; font-weight: 600;"></span>
                            </div>
                            <div>
                                <small class="muted" style="display: block;">Category</small>
                                <span id="details-category-badge" class="badge"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Variations Breakdown Table -->
                    <div class="modal-info-card" style="margin-top: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted);">Variations Breakdown</strong>
                            <small id="details-variation-count" class="muted"></small>
                        </div>
                        <div class="table-scroll" style="border: 1px solid var(--line); border-radius: 8px;">
                            <table style="font-size: 11px;">
                                <thead>
                                    <tr>
                                        <th style="padding: 7px 10px;">Option Name</th>
                                        <th style="padding: 7px 10px;">SKU</th>
                                        <th style="padding: 7px 10px;">Price</th>
                                        <th style="padding: 7px 10px;">Stock</th>
                                    </tr>
                                </thead>
                                <tbody id="details-variations-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="admin-modal-footer">
            <div>
                <a id="details-storefront-link" href="#" target="_blank" rel="noopener" class="button subtle" style="font-size: 11px; padding: 6px 12px; gap: 6px;">
                    <span>View Storefront Preview ↗</span>
                </a>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" class="button subtle js-close-modal" style="font-size: 11px;">Close</button>
                <button type="button" id="details-btn-reject" class="button subtle" style="font-size: 11px; color: var(--rose-ink); border-color: var(--rose-border);">
                    ✕ Reject Listing
                </button>
                <form id="details-approve-form" method="POST" action="">
                    @csrf
                    <button type="submit" id="details-btn-approve" class="button" style="font-size: 11px; background: #2E7D32; border-color: #2E7D32; color: #fff;">
                        ✓ Approve Product
                    </button>
                </form>
            </div>
        </footer>
    </div>
</dialog>

{{-- ======================================================== --}}
{{-- MODAL 2: Reject Listing Pop-up                           --}}
{{-- ======================================================== --}}
<dialog id="product-reject-modal" class="admin-modal" aria-labelledby="reject-modal-title">
    <div class="admin-modal-panel">
        <header class="admin-modal-header" style="border-bottom-color: var(--rose-border); background: #FFFBFB;">
            <div>
                <p class="eyebrow" style="color: var(--rose-ink); margin-bottom: 3px;">REVIEW DECISION</p>
                <h2 id="reject-modal-title" style="font-size: 17px; margin: 0; line-height: 1.3;">Reject Product Listing</h2>
            </div>
            <button type="button" class="icon-button js-close-modal" aria-label="Close dialog" style="font-size: 22px; width: 36px; height: 36px;">&times;</button>
        </header>

        <form id="reject-form" method="POST" action="" class="form-stack">
            @csrf
            <div class="admin-modal-body">
                <!-- Product Overview Card -->
                <div style="display: flex; gap: 12px; align-items: center; padding: 12px 14px; background: var(--canvas); border: 1px solid var(--line); border-radius: 10px; margin-bottom: 16px;">
                    <img id="reject-preview-img" src="" alt="Product cover" style="width: 46px; height: 46px; object-fit: cover; border-radius: 8px; border: 1px solid var(--line); background: #fff; flex-shrink: 0;">
                    <div style="min-width: 0;">
                        <strong id="reject-preview-name" style="font-size: 13px; line-height: 1.3; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></strong>
                        <small id="reject-preview-meta" class="muted" style="display: block; margin-top: 2px;"></small>
                    </div>
                </div>

                <div class="notice danger" style="margin-bottom: 16px; font-size: 12px; padding: 12px 14px; border-radius: 8px;">
                    This product will be hidden from marketplace shoppers. The seller will receive your feedback in their Seller Portal so they can revise and resubmit.
                </div>

                <label style="font-size: 12px; font-weight: 600; color: var(--ink);">
                    Select Common Reason (Optional autofill):
                    <select id="reject-preset-select" style="margin-top: 6px; font-size: 12px;">
                        <option value="">-- Choose common reason to autofill --</option>
                        <option value="Prohibited or restricted merchandise policy violation.">Prohibited or Restricted Merchandise Policy Violation</option>
                        <option value="Misleading product information or non-compliant product title.">Misleading Product Title or Description</option>
                        <option value="Product category does not match declared line of business.">Category Does Not Match Line of Business</option>
                        <option value="Low resolution, watermarked, or copyrighted product photos.">Inappropriate, Watermarked, or Low Quality Images</option>
                        <option value="Suspicious pricing or unrealistic variation details.">Suspicious Pricing or Unrealistic Variations</option>
                        <option value="Missing critical specifications, ingredients, or safety warnings.">Missing Critical Specifications or Warnings</option>
                        <option value="Counterfeit, replica, or unauthorized branded goods.">Counterfeit or Unauthorized Branded Goods</option>
                    </select>
                </label>

                <label style="font-size: 12px; font-weight: 600; color: var(--ink); margin-top: 12px;">
                    Detailed Rejection Feedback (Required):
                    <textarea id="reject-reason-textarea" name="reason" rows="4" required placeholder="Specify exactly why this product was rejected and what the seller needs to correct..." style="margin-top: 6px; font-size: 12px; line-height: 1.5;"></textarea>
                </label>
            </div>

            <footer class="admin-modal-footer">
                <button type="button" class="button subtle js-close-modal">Cancel</button>
                <button type="submit" class="button danger" style="min-width: 140px;">
                    Confirm Rejection
                </button>
            </footer>
        </form>
    </div>
</dialog>
@endsection

@push('styles')
<style>
/* ========================================================
   Admin Modal Pop-ups Styles
   ======================================================== */
.admin-modal {
    margin: auto;
    padding: 0;
    border: 1px solid var(--line);
    border-radius: 18px;
    background: var(--surface);
    color: var(--ink);
    box-shadow: 0 30px 90px -15px rgba(40, 33, 51, 0.35);
    overflow: hidden;
}

.admin-modal::backdrop {
    background: rgba(40, 33, 51, 0.65);
    backdrop-filter: blur(4px);
}

.admin-modal[open] {
    animation: admin-modal-in 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes admin-modal-in {
    from {
        opacity: 0;
        transform: scale(0.96) translateY(-10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

#product-details-modal {
    width: min(880px, calc(100% - 32px));
    max-height: 90vh;
}

#product-reject-modal {
    width: min(520px, calc(100% - 32px));
    max-height: 90vh;
}

.admin-modal-panel {
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}

.admin-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid var(--line);
    background: var(--surface);
    flex-shrink: 0;
}

.admin-modal-body {
    padding: 22px 24px;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

.admin-modal-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-top: 1px solid var(--line);
    background: var(--canvas);
    flex-shrink: 0;
    gap: 12px;
    flex-wrap: wrap;
}

/* 2-Column Product Layout inside Details Modal */
.modal-product-layout {
    display: grid;
    grid-template-columns: 290px 1fr;
    gap: 22px;
}

@media(max-width: 760px) {
    .modal-product-layout {
        grid-template-columns: 1fr;
    }
}

.modal-featured-image-wrap {
    width: 100%;
    aspect-ratio: 1 / 1;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-featured-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.modal-gallery-strip {
    display: flex;
    gap: 6px;
    margin-top: 10px;
    overflow-x: auto;
    padding-bottom: 4px;
}

.modal-gallery-thumb {
    width: 48px;
    height: 48px;
    border-radius: 6px;
    border: 2px solid transparent;
    object-fit: cover;
    cursor: pointer;
    background: #fff;
    flex-shrink: 0;
    transition: border-color 0.15s, transform 0.15s;
}

.modal-gallery-thumb:hover {
    transform: scale(1.05);
}

.modal-gallery-thumb.active {
    border-color: var(--accent);
}

.modal-info-card {
    background: var(--canvas);
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 14px 16px;
}

.modal-description-text {
    font-size: 12px;
    color: var(--muted);
    line-height: 1.55;
    white-space: pre-line;
    max-height: 160px;
    overflow-y: auto;
}

.modal-status-banner {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 14px;
    font-size: 12px;
}

.modal-status-banner.pending {
    background: #FEF3C7;
    border: 1px solid #FCD34D;
    color: #92400E;
}

.modal-status-banner.approved {
    background: #E8F5E9;
    border: 1px solid #A5D6A7;
    color: #1B5E20;
}

.modal-status-banner.rejected {
    background: var(--rose-soft);
    border: 1px solid var(--rose-border);
    color: var(--rose-ink);
}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const detailsModal = document.getElementById('product-details-modal');
    const rejectModal = document.getElementById('product-reject-modal');
    let currentProductData = null;

    // Helper: close modals on backdrop click or close button
    document.querySelectorAll('.admin-modal').forEach(dialog => {
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) dialog.close();
        });
        dialog.querySelectorAll('.js-close-modal').forEach(btn => {
            btn.addEventListener('click', () => dialog.close());
        });
    });

    // Populate and open Product Details Modal
    document.querySelectorAll('.js-open-product-details').forEach(btn => {
        btn.addEventListener('click', () => {
            const tr = btn.closest('tr');
            const payloadScript = tr ? tr.querySelector('.product-payload') : null;
            if (!payloadScript) return;

            const data = JSON.parse(payloadScript.textContent);
            currentProductData = data;

            // Title & Featured Image
            document.getElementById('details-modal-title').textContent = data.name;
            const featuredImg = document.getElementById('details-featured-img');
            featuredImg.src = data.cover_url;
            featuredImg.alt = data.name;

            // Gallery Thumbnails
            const galleryContainer = document.getElementById('details-gallery');
            galleryContainer.innerHTML = '';
            if (data.gallery && data.gallery.length > 1) {
                galleryContainer.style.display = 'flex';
                data.gallery.forEach((url, idx) => {
                    const thumb = document.createElement('img');
                    thumb.src = url;
                    thumb.alt = 'Thumbnail ' + (idx + 1);
                    thumb.className = 'modal-gallery-thumb' + (idx === 0 ? ' active' : '');
                    thumb.addEventListener('click', () => {
                        featuredImg.src = url;
                        galleryContainer.querySelectorAll('.modal-gallery-thumb').forEach(t => t.classList.remove('active'));
                        thumb.classList.add('active');
                    });
                    galleryContainer.appendChild(thumb);
                });
            } else {
                galleryContainer.style.display = 'none';
            }

            // Description
            document.getElementById('details-description').textContent = data.description;

            // Status Banner
            const statusBanner = document.getElementById('details-status-banner');
            statusBanner.className = 'modal-status-banner ' + data.status;
            if (data.status === 'pending') {
                statusBanner.innerHTML = '<strong>⏳ Pending Admin Review</strong><div style="margin-top:3px;font-size:11px;">This product is awaiting your approval before becoming visible and purchasable by shoppers on the marketplace.</div>';
            } else if (data.status === 'approved') {
                const reviewedDate = data.reviewed_at ? ' on ' + data.reviewed_at : '';
                const reviewer = data.reviewer_name ? ' by ' + data.reviewer_name : '';
                statusBanner.innerHTML = '<strong>✓ Approved & Live on Storefront</strong><div style="margin-top:3px;font-size:11px;">Approved' + reviewedDate + reviewer + '. This product is actively published.</div>';
            } else if (data.status === 'rejected') {
                const reviewedDate = data.reviewed_at ? ' on ' + data.reviewed_at : '';
                const reasonText = data.rejection_reason ? '<div style="margin-top:4px;padding:6px 10px;background:#fff;border-radius:6px;border:1px solid var(--rose-border);"><strong>Reason:</strong> ' + escapeHtml(data.rejection_reason) + '</div>' : '';
                statusBanner.innerHTML = '<strong>✕ Disapproved / Rejected</strong><div style="margin-top:3px;font-size:11px;">Listing rejected' + reviewedDate + '.</div>' + reasonText;
            }

            // Seller info
            const storeNameEl = document.getElementById('details-store-name');
            storeNameEl.textContent = data.seller_name + (data.seller_url ? ' ↗' : '');
            storeNameEl.href = data.seller_url || '#';
            if (!data.seller_url) storeNameEl.removeAttribute('target');
            else storeNameEl.setAttribute('target', '_blank');

            document.getElementById('details-seller-owner').textContent = data.seller_owner;
            document.getElementById('details-seller-email').textContent = data.seller_email || 'No email provided';
            document.getElementById('details-seller-lob').textContent = data.seller_lob || 'Not specified';

            // Pricing & stock
            document.getElementById('details-price').textContent = data.price_display;
            document.getElementById('details-stock').textContent = Number(data.total_stock).toLocaleString() + ' units';
            document.getElementById('details-category-badge').textContent = data.category + (data.gender ? ' · ' + data.gender : '');

            const discountWrap = document.getElementById('details-discount-wrap');
            if (data.discount) {
                discountWrap.style.display = 'block';
                document.getElementById('details-discount-badge').textContent = data.discount;
            } else {
                discountWrap.style.display = 'none';
            }

            // Variations Table
            const tbody = document.getElementById('details-variations-tbody');
            tbody.innerHTML = '';
            document.getElementById('details-variation-count').textContent = data.variants.length + ' variation' + (data.variants.length === 1 ? '' : 's');
            data.variants.forEach(v => {
                const trEl = document.createElement('tr');
                trEl.innerHTML = `
                    <td style="padding: 7px 10px;"><strong>${escapeHtml(v.name)}</strong></td>
                    <td style="padding: 7px 10px;"><code>${escapeHtml(v.sku)}</code></td>
                    <td style="padding: 7px 10px;">${escapeHtml(v.price)}</td>
                    <td style="padding: 7px 10px;">${Number(v.stock).toLocaleString()}</td>
                `;
                tbody.appendChild(trEl);
            });

            // Storefront link
            document.getElementById('details-storefront-link').href = data.storefront_url;

            // Footer action buttons
            const approveForm = document.getElementById('details-approve-form');
            const approveBtn = document.getElementById('details-btn-approve');
            const rejectBtn = document.getElementById('details-btn-reject');

            approveForm.action = data.approve_url;
            approveForm.style.display = data.is_approved ? 'none' : 'block';
            rejectBtn.style.display = data.is_rejected ? 'none' : 'inline-flex';

            detailsModal.showModal();
        });
    });

    // Open Reject Modal directly or from Details Modal
    function openRejectModal(data) {
        if (!data) return;
        currentProductData = data;

        const form = document.getElementById('reject-form');
        form.action = data.reject_url;

        document.getElementById('reject-preview-img').src = data.cover_url;
        document.getElementById('reject-preview-name').textContent = data.name;
        document.getElementById('reject-preview-meta').textContent = `${data.category} · Store: ${data.seller_name}`;

        const presetSelect = document.getElementById('reject-preset-select');
        presetSelect.value = '';

        const reasonTextarea = document.getElementById('reject-reason-textarea');
        reasonTextarea.value = data.rejection_reason || '';

        // If details modal was open, close it first
        if (detailsModal.open) detailsModal.close();

        rejectModal.showModal();
        reasonTextarea.focus();
    }

    // Direct Reject button on table row
    document.querySelectorAll('.js-open-product-reject').forEach(btn => {
        btn.addEventListener('click', () => {
            const tr = btn.closest('tr');
            const payloadScript = tr ? tr.querySelector('.product-payload') : null;
            if (!payloadScript) return;
            const data = JSON.parse(payloadScript.textContent);
            openRejectModal(data);
        });
    });

    // Reject button inside Details Modal footer
    document.getElementById('details-btn-reject').addEventListener('click', () => {
        if (currentProductData) {
            openRejectModal(currentProductData);
        }
    });

    // Preset reason autofill
    document.getElementById('reject-preset-select').addEventListener('change', function() {
        if (this.value) {
            const textarea = document.getElementById('reject-reason-textarea');
            textarea.value = this.value;
            textarea.focus();
        }
    });

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
</script>
@endpush

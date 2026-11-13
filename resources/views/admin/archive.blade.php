@extends('layouts.admin')
@section('page_title', 'Archive & Records')
@section('page_description', 'Central archive for deactivated listings, suspended accounts, rejected applications, and resolved dispute cases.')
@section('content')

{{-- Quick Summary Stats --}}
<div class="stats-grid four">
    <a href="{{ route('admin.archive', ['tab' => 'products']) }}" class="stat-card" style="text-decoration: none; border-top-color: {{ $tab === 'products' ? 'var(--rose-gold)' : 'var(--line)' }};">
        <div class="metric-heading">
            <span>Archived Listings</span>
            <span class="badge {{ $counts['products'] > 0 ? 'warning' : '' }}">{{ $counts['products'] }} items</span>
        </div>
        <strong>{{ number_format($counts['products']) }}</strong>
        <small>Deactivated & soft-deleted products</small>
    </a>

    <a href="{{ route('admin.archive', ['tab' => 'users']) }}" class="stat-card" style="text-decoration: none; border-top-color: {{ $tab === 'users' ? 'var(--rose-gold)' : 'var(--line)' }};">
        <div class="metric-heading">
            <span>Archived Accounts</span>
            <span class="badge {{ $counts['users'] > 0 ? 'danger' : '' }}">{{ $counts['users'] }} users</span>
        </div>
        <strong>{{ number_format($counts['users']) }}</strong>
        <small>Archived & removed user accounts</small>
    </a>

    <a href="{{ route('admin.archive', ['tab' => 'registrations']) }}" class="stat-card" style="text-decoration: none; border-top-color: {{ $tab === 'registrations' ? 'var(--rose-gold)' : 'var(--line)' }};">
        <div class="metric-heading">
            <span>Rejected Applications</span>
            <span class="badge">{{ $counts['registrations'] }} records</span>
        </div>
        <strong>{{ number_format($counts['registrations']) }}</strong>
        <small>Declined KYC & business permits</small>
    </a>

    <a href="{{ route('admin.archive', ['tab' => 'disputes']) }}" class="stat-card" style="text-decoration: none; border-top-color: {{ $tab === 'disputes' ? 'var(--rose-gold)' : 'var(--line)' }};">
        <div class="metric-heading">
            <span>Resolved Disputes</span>
            <span class="badge good">{{ $counts['disputes'] }} cases</span>
        </div>
        <strong>{{ number_format($counts['disputes']) }}</strong>
        <small>Settled buyer & seller complaints</small>
    </a>
</div>

<section class="panel">
    {{-- Archive Tab Navigation --}}
    <div class="archive-tabs">
        <a href="{{ route('admin.archive', ['tab' => 'products']) }}" class="archive-tab {{ $tab === 'products' ? 'active' : '' }}">
            @include('admin.partials.icon', ['name' => 'box'])
            <span>Archived Products</span>
            <span class="tab-badge">{{ $counts['products'] }}</span>
        </a>
        <a href="{{ route('admin.archive', ['tab' => 'users']) }}" class="archive-tab {{ $tab === 'users' ? 'active' : '' }}">
            @include('admin.partials.icon', ['name' => 'users'])
            <span>Archived Accounts</span>
            <span class="tab-badge">{{ $counts['users'] }}</span>
        </a>
        <a href="{{ route('admin.archive', ['tab' => 'registrations']) }}" class="archive-tab {{ $tab === 'registrations' ? 'active' : '' }}">
            @include('admin.partials.icon', ['name' => 'file'])
            <span>Rejected Applications</span>
            <span class="tab-badge">{{ $counts['registrations'] }}</span>
        </a>
        <a href="{{ route('admin.archive', ['tab' => 'disputes']) }}" class="archive-tab {{ $tab === 'disputes' ? 'active' : '' }}">
            @include('admin.partials.icon', ['name' => 'shield'])
            <span>Resolved Disputes</span>
            <span class="tab-badge">{{ $counts['disputes'] }}</span>
        </a>
    </div>

    {{-- TAB 1: PRODUCTS --}}
    @if($tab === 'products')
        <form method="GET" class="filters" action="{{ route('admin.archive') }}">
            <input type="hidden" name="tab" value="products">
            <label class="search-field">Search Products
                <input name="search" value="{{ request('search') }}" placeholder="Search product name, description, SKU, or seller store">
            </label>
            <label>Filter Status
                <select name="status">
                    <option value="">All archived</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive Listings</option>
                    <option value="trashed" @selected(request('status') === 'trashed')>Soft-deleted / Removed</option>
                </select>
            </label>
            <button class="button">Apply filters</button>
            <a class="button subtle" href="{{ route('admin.archive', ['tab' => 'products']) }}">Reset</a>
        </form>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Product & Category</th>
                        <th>Seller / Store</th>
                        <th>Archive Type</th>
                        <th>Stock / Pricing</th>
                        <th>Date Archived</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $product)
                    @php
                        $coverPath = $product->coverImage?->path;
                        $totalStock = $product->variants->sum('stock');
                        $minPrice = $product->variants->min('price');
                        $isSoftDeleted = $product->trashed();
                    @endphp
                    <tr>
                        <td>
                            <div class="archive-product-row">
                                @if($coverPath)
                                    <img class="archive-product-img" src="{{ str_starts_with($coverPath, 'http') || str_starts_with($coverPath, '/') ? $coverPath : Storage::disk('public')->url($coverPath) }}" alt="">
                                @else
                                    <div class="archive-product-img">
                                        @include('admin.partials.icon', ['name' => 'box'])
                                    </div>
                                @endif
                                <div>
                                    <strong style="font-size: 13px;">{{ $product->name }}</strong>
                                    <small>
                                        {{ $product->category?->name ?? 'Uncategorized' }} · Target: {{ ucfirst($product->gender ?? 'unisex') }} · {{ $product->variants->count() }} variation(s)
                                    </small>
                                    @if($product->badge)
                                        <small><span class="badge">{{ $product->badge }}</span></small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $product->seller?->name ?? 'No store assigned' }}</strong>
                            <small>{{ $product->seller?->user?->email ?? 'No email' }}</small>
                            <small class="muted">Line: {{ $product->seller?->user?->line_of_business ?? 'Unspecified' }}</small>
                        </td>
                        <td>
                            @if($isSoftDeleted)
                                <span class="badge danger">Soft-deleted</span>
                                <small>Listing removed by seller</small>
                            @else
                                <span class="badge warning">Inactive Listing</span>
                                <small>Hidden from storefront</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $totalStock }} unit(s)</strong>
                            <small>From ₱{{ number_format($minPrice ?? ($product->price_minor / 100), 2) }}</small>
                        </td>
                        <td class="nowrap">
                            {{ ($product->deleted_at ?? $product->updated_at)?->format('M j, Y') }}
                            <small>{{ ($product->deleted_at ?? $product->updated_at)?->format('g:i A') }}</small>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                                <form method="POST" action="{{ route('admin.archive.products.restore', $product->id) }}" data-archive-action="restore" data-product-name="{{ $product->name }}" data-confirm="Restore '{{ $product->name }}' and make it active on the marketplace?">
                                    @csrf
                                    <button type="submit" class="button" style="min-height: 34px; padding: 6px 12px; font-size: 11px;">
                                        @include('admin.partials.icon', ['name' => 'refresh']) Restore Listing
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.archive.products.force', $product->id) }}" data-archive-action="delete" data-product-name="{{ $product->name }}" data-confirm="Permanently delete '{{ $product->name }}' from the database? This cannot be undone.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button subtle danger" style="min-height: 34px; padding: 6px 12px; font-size: 11px;">
                                        Delete Permanently
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty">
                                <div style="display: flex; justify-content: center; margin-bottom: 12px; color: var(--focus);">
                                    @include('admin.partials.icon', ['name' => 'archive'])
                                </div>
                                <strong>No archived products found</strong>
                                <p>Inactive or soft-deleted products from all seller stores will appear here for review or restoration.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($products)
            @include('admin.partials.pagination', ['paginator' => $products])
        @endif

    {{-- TAB 2: USERS --}}
    @elseif($tab === 'users')
        <form method="GET" class="filters" action="{{ route('admin.archive') }}">
            <input type="hidden" name="tab" value="users">
            <label class="search-field">Search Users
                <input name="search" value="{{ request('search') }}" placeholder="Search user name, email, phone, or business name">
            </label>
            <label>Role
                <select name="role">
                    <option value="">All roles</option>
                    <option value="seller" @selected(request('role') === 'seller')>Seller</option>
                    <option value="buyer" @selected(request('role') === 'buyer')>Buyer</option>
                    <option value="courier" @selected(request('role') === 'courier')>Courier / Rider</option>
                    <option value="logistics" @selected(request('role') === 'logistics')>Logistics Provider</option>
                </select>
            </label>
            <label>Status
                <select name="status">
                    <option value="">All statuses</option>
                    <option value="archived" @selected(request('status') === 'archived')>Archived</option>
                    <option value="removed" @selected(request('status') === 'removed')>Removed</option>
                    <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                    <option value="deactivated" @selected(request('status') === 'deactivated')>Deactivated</option>
                </select>
            </label>
            <button class="button">Apply filters</button>
            <a class="button subtle" href="{{ route('admin.archive', ['tab' => 'users']) }}">Reset</a>
        </form>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>User Account</th>
                        <th>Role & Business</th>
                        <th>Account Status</th>
                        <th>Joined</th>
                        <th>Updated At</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->name }}</strong>
                            <small>{{ $user->email }}</small>
                            <small>{{ $user->phone ?? 'No phone' }}</small>
                        </td>
                        <td>
                            <span class="badge" style="text-transform: capitalize;">{{ $user->role }}</span>
                            @if($user->business_name)
                                <small>Store: <strong>{{ $user->business_name }}</strong></small>
                            @endif
                            @if($user->line_of_business)
                                <small class="muted">Line: {{ $user->line_of_business }}</small>
                            @endif
                        </td>
                        <td>
                            @if($user->status === 'removed')
                                <span class="badge danger" style="background: #fce9e9; color: #b42332;">Removed</span>
                                @if($user->removal_reason)
                                    <small style="display: block; color: var(--rose-ink); margin-top: 3px;" title="{{ $user->removal_reason }}">
                                        <strong>Reason:</strong> {{ \Illuminate\Support\Str::limit($user->removal_reason, 40) }}
                                    </small>
                                @endif
                                @if($user->removed_at)
                                    <small class="muted" style="display: block; margin-top: 2px;">{{ $user->removed_at->format('M j, Y') }}</small>
                                @endif
                            @elseif($user->status === 'archived')
                                <span class="badge" style="background: #e2e8f0; color: #475569;">Archived</span>
                                @if($user->status_reason)
                                    <small class="muted" style="display: block; margin-top: 3px;" title="{{ $user->status_reason }}">
                                        <strong>Note:</strong> {{ \Illuminate\Support\Str::limit($user->status_reason, 40) }}
                                    </small>
                                @endif
                            @else
                                <span class="badge danger">{{ ucfirst($user->status) }}</span>
                                @if($user->status_reason)
                                    <small class="muted" style="display: block; margin-top: 3px;" title="{{ $user->status_reason }}">
                                        <strong>Reason:</strong> {{ \Illuminate\Support\Str::limit($user->status_reason, 40) }}
                                    </small>
                                @endif
                            @endif
                        </td>
                        <td class="nowrap">
                            {{ $user->created_at?->format('M j, Y') }}
                        </td>
                        <td class="nowrap">
                            {{ $user->updated_at?->format('M j, Y') }}
                            <small>{{ $user->updated_at?->format('g:i A') }}</small>
                        </td>
                        <td>
                            <div style="display: flex; justify-content: flex-end; gap: 8px; flex-wrap: wrap;">
                                <form method="POST" action="{{ route('admin.archive.users.restore', $user->id) }}" data-confirm="Reactivate and restore this account to active status?">
                                    @csrf
                                    <button type="submit" class="button" style="min-height: 32px; padding: 5px 12px; font-size: 11px;">
                                        @include('admin.partials.icon', ['name' => 'refresh']) Reactivate
                                    </button>
                                </form>
                                @if($user->status !== 'removed')
                                    <button type="button" class="button" style="min-height: 32px; padding: 5px 12px; font-size: 11px; background: #b42332; border-color: #b42332; color: #fff;"
                                        data-trigger-remove-user
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        data-action-url="{{ route('admin.archive.users.remove', $user->id) }}">
                                        @include('admin.partials.icon', ['name' => 'trash']) Remove
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty">
                                <div style="display: flex; justify-content: center; margin-bottom: 12px; color: var(--focus);">
                                    @include('admin.partials.icon', ['name' => 'users'])
                                </div>
                                <strong>No suspended or deactivated accounts</strong>
                                <p>Accounts that have been suspended due to violations or deactivated will appear here for audit and reactivation.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($users)
            @include('admin.partials.pagination', ['paginator' => $users])
        @endif

    {{-- TAB 3: REGISTRATIONS --}}
    @elseif($tab === 'registrations')
        <form method="GET" class="filters" action="{{ route('admin.archive') }}">
            <input type="hidden" name="tab" value="registrations">
            <label class="search-field">Search Registrations
                <input name="search" value="{{ request('search') }}" placeholder="Search applicant name, email, or business name">
            </label>
            <label>Role
                <select name="role">
                    <option value="">All roles</option>
                    <option value="seller" @selected(request('role') === 'seller')>Seller</option>
                    <option value="courier" @selected(request('role') === 'courier')>Courier / Rider</option>
                    <option value="buyer" @selected(request('role') === 'buyer')>Buyer</option>
                </select>
            </label>
            <button class="button">Apply filters</button>
            <a class="button subtle" href="{{ route('admin.archive', ['tab' => 'registrations']) }}">Reset</a>
        </form>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Role & Business</th>
                        <th>Verification Status</th>
                        <th>Rejection Reason</th>
                        <th>Declined Date</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($registrations as $applicant)
                    <tr>
                        <td>
                            <strong>{{ $applicant->name }}</strong>
                            <small>{{ $applicant->email }}</small>
                            <small>{{ $applicant->phone }}</small>
                        </td>
                        <td>
                            <span class="badge">{{ ucfirst($applicant->role) }}</span>
                            @if($applicant->business_name)
                                <small>Business: {{ $applicant->business_name }}</small>
                            @endif
                            @if($applicant->tin_number)
                                <small class="muted">TIN: {{ $applicant->tin_number }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge danger">Rejected</span>
                            <small>ID Status: {{ ucfirst($applicant->id_status) }}</small>
                            @if($applicant->id_photo)
                                <small><a class="text-link" href="{{ Storage::disk('public')->url($applicant->id_photo) }}" target="_blank" rel="noopener">View Submitted ID ↗</a></small>
                            @endif
                            @if($applicant->dti_permit)
                                <small><a class="text-link" href="{{ Storage::disk('public')->url($applicant->dti_permit) }}" target="_blank" rel="noopener">View DTI Permit ↗</a></small>
                            @endif
                        </td>
                        <td>
                            <p style="font-size: 12px; max-width: 300px; color: var(--rose-ink); overflow-wrap: anywhere;">
                                {{ $applicant->id_rejection_reason ?: 'No specific reason recorded.' }}
                            </p>
                        </td>
                        <td class="nowrap">
                            {{ $applicant->updated_at?->format('M j, Y') }}
                            <small>{{ $applicant->updated_at?->format('g:i A') }}</small>
                        </td>
                        <td>
                            <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                <form method="POST" action="{{ route('admin.archive.registrations.restore', $applicant->id) }}" data-confirm="Reopen this application and return it to pending review?">
                                    @csrf
                                    <button type="submit" class="button" style="min-height: 34px; padding: 6px 14px; font-size: 11px;">
                                        @include('admin.partials.icon', ['name' => 'refresh']) Reopen for Review
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty">
                                <div style="display: flex; justify-content: center; margin-bottom: 12px; color: var(--focus);">
                                    @include('admin.partials.icon', ['name' => 'file'])
                                </div>
                                <strong>No rejected applications</strong>
                                <p>Applications that are rejected during KYC and verification are preserved here.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($registrations)
            @include('admin.partials.pagination', ['paginator' => $registrations])
        @endif

    {{-- TAB 4: DISPUTES --}}
    @elseif($tab === 'disputes')
        <form method="GET" class="filters" action="{{ route('admin.archive') }}">
            <input type="hidden" name="tab" value="disputes">
            <label class="search-field">Search Disputes
                <input name="search" value="{{ request('search') }}" placeholder="Search by order reference, subject, or buyer name">
            </label>
            <button class="button">Apply filters</button>
            <a class="button subtle" href="{{ route('admin.archive', ['tab' => 'disputes']) }}">Reset</a>
        </form>

        <div class="case-list">
        @forelse($disputes as $dispute)
            @php
                $order = isset($ordersMap) ? $ordersMap->get($dispute->order_id) : null;
            @endphp
            <article class="case">
                <div class="case-heading">
                    <div>
                        <span class="eyebrow">CASE #{{ $dispute->id }} · {{ $dispute->reference }}</span>
                        <h2>{{ $dispute->subject }}</h2>
                        <p class="muted">Buyer: {{ $dispute->buyer_name }} · Resolved on {{ \Carbon\Carbon::parse($dispute->resolved_at ?? $dispute->updated_at)->format('M j, Y g:i A') }}</p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="badge good">Resolved Case</span>
                        <form method="POST" action="{{ route('admin.archive.disputes.reopen', $dispute->id) }}" data-confirm="Reopen case #{{ $dispute->id }} and move back to active disputes?">
                            @csrf
                            <button type="submit" class="button subtle" style="min-height: 32px; padding: 5px 12px; font-size: 11px;">
                                @include('admin.partials.icon', ['name' => 'refresh']) Reopen Dispute
                            </button>
                        </form>
                    </div>
                </div>

                <div style="margin: 12px 0;">
                    <strong style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--accent-hover);">Original Claim:</strong>
                    <p class="preserve-lines" style="margin-top: 4px; font-size: 12px;">{{ $dispute->description }}</p>
                </div>

                @if($dispute->admin_notes)
                    <div class="notice" style="background: var(--surface); border-left-color: var(--rose-gold); margin-top: 14px;">
                        <strong style="display: block; font-size: 11px; text-transform: uppercase; color: var(--rose-ink); margin-bottom: 4px;">Resolution Recorded Notes:</strong>
                        <p class="preserve-lines" style="font-size: 12px;">{{ $dispute->admin_notes }}</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="empty">
                <div style="display: flex; justify-content: center; margin-bottom: 12px; color: var(--focus);">
                    @include('admin.partials.icon', ['name' => 'shield'])
                </div>
                <strong>No resolved disputes in archive</strong>
                <p>When customer or seller complaints are settled, their permanent records are archived here.</p>
            </div>
        @endforelse
        </div>
        @if($disputes)
            @include('admin.partials.pagination', ['paginator' => $disputes])
        @endif
    @endif
</section>
@include('admin.partials.archive-confirm')

{{-- Modal dialog for removing an archived user with reason input --}}
<dialog id="remove-user-modal" aria-labelledby="remove-user-title" aria-describedby="remove-user-desc">
    <div class="user-status-modal-content">
        <button type="button" class="user-status-modal-close" data-close-remove-modal aria-label="Close dialog">&times;</button>
        <div class="user-status-modal-badge danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
        </div>
        <h2 id="remove-user-title" class="user-status-modal-title">Remove Account</h2>
        <p id="remove-user-desc" class="user-status-modal-desc">
            Input the reason why this account is being removed. This reason will be displayed to the user if they try to log in.
        </p>

        <form id="remove-user-form" method="POST" action="">
            @csrf
            <label class="user-status-modal-label">
                <span>Reason for removal <strong style="color: #dc2626;">*</strong></span>
                <textarea name="removal_reason" id="remove-reason-input" class="user-status-modal-textarea" rows="4" required placeholder="Type the reason why this account is being removed..."></textarea>
            </label>

            <div class="user-status-modal-actions">
                <button type="button" class="btn-cancel" data-close-remove-modal>Cancel</button>
                <button type="submit" class="btn-confirm" style="background: #dc2626;">Confirm Removal</button>
            </div>
        </form>
    </div>
</dialog>

@push('styles')
<style>
    #remove-user-modal {
        width: min(500px, calc(100vw - 32px));
        max-height: calc(100dvh - 32px);
        margin: auto;
        padding: 0;
        border: 1px solid var(--line, #e2e8f0);
        border-radius: 20px;
        background: var(--surface, #fff);
        color: var(--ink, #1f2937);
        box-shadow: 0 24px 80px rgba(40, 33, 51, 0.28);
        overflow: hidden;
    }
    #remove-user-modal::backdrop {
        background: rgba(40, 33, 51, 0.55);
        backdrop-filter: blur(4px);
    }
    #remove-user-modal[open] {
        animation: user-modal-pop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes user-modal-pop {
        from { opacity: 0; transform: scale(0.96) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .user-status-modal-content {
        position: relative;
        padding: 32px 30px 28px;
        text-align: left;
    }
    .user-status-modal-close {
        position: absolute;
        top: 18px;
        right: 18px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.06);
        background: #f8fafc;
        color: #64748b;
        font-size: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        line-height: 1;
        transition: all 0.15s ease;
    }
    .user-status-modal-close:hover {
        background: #f1f5f9;
        color: #0f172a;
        transform: scale(1.05);
    }
    .user-status-modal-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        margin-bottom: 18px;
        border-radius: 14px;
        background: #fef3c7;
        color: #b45309;
        flex-shrink: 0;
    }
    .user-status-modal-badge.danger {
        background: #fef2f2 !important;
        color: #dc2626 !important;
    }
    .user-status-modal-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 8px;
        line-height: 1.3;
        letter-spacing: -0.01em;
    }
    .user-status-modal-desc {
        font-size: 13.5px;
        color: #64748b;
        margin: 0 0 22px;
        line-height: 1.55;
    }
    .user-status-modal-label {
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        text-align: left;
        margin-bottom: 24px;
    }
    .user-status-modal-textarea {
        width: 100%;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 14px;
        font-family: inherit;
        resize: vertical;
        line-height: 1.5;
        box-sizing: border-box;
        background: #f8fafc;
        color: #1e293b;
        transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
    }
    .user-status-modal-textarea:focus {
        background: #fff;
        border-color: #dc2626;
        outline: none;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }
    .user-status-modal-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        margin-top: 24px;
    }
    .user-status-modal-actions .btn-cancel {
        min-height: 42px;
        padding: 0 18px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: background 0.15s;
    }
    .user-status-modal-actions .btn-cancel:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .user-status-modal-actions .btn-confirm {
        min-height: 42px;
        padding: 0 22px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        color: #fff;
        border: none;
        cursor: pointer;
        transition: opacity 0.15s, transform 0.1s;
    }
    .user-status-modal-actions .btn-confirm:hover {
        opacity: 0.92;
        transform: translateY(-1px);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('remove-user-modal');
        if (!modal) return;

        const form = document.getElementById('remove-user-form');
        const titleEl = document.getElementById('remove-user-title');
        const descEl = document.getElementById('remove-user-desc');
        const reasonInput = document.getElementById('remove-reason-input');

        document.querySelectorAll('[data-trigger-remove-user]').forEach(btn => {
            btn.addEventListener('click', function () {
                const userName = this.dataset.userName || 'User';
                const actionUrl = this.dataset.actionUrl;

                form.action = actionUrl;
                reasonInput.value = '';
                titleEl.textContent = `Remove Account — ${userName}`;
                descEl.textContent = `Enter the reason why ${userName}'s account is being removed. This reason will pop up on their screen if they attempt to log in.`;

                if (typeof modal.showModal === 'function') {
                    modal.showModal();
                } else {
                    modal.setAttribute('open', '');
                }
                setTimeout(() => reasonInput.focus(), 80);
            });
        });

        document.querySelectorAll('[data-close-remove-modal]').forEach(btn => {
            btn.addEventListener('click', function () {
                if (typeof modal.close === 'function') modal.close();
                else modal.removeAttribute('open');
            });
        });

        modal.addEventListener('click', function (e) {
            const rect = modal.getBoundingClientRect();
            if (
                e.clientX < rect.left ||
                e.clientX > rect.right ||
                e.clientY < rect.top ||
                e.clientY > rect.bottom
            ) {
                if (typeof modal.close === 'function') modal.close();
                else modal.removeAttribute('open');
            }
        });
    });
</script>
@endpush
@endsection

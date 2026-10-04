@extends('layouts.seller')

@section('title', 'Manage Inventory & Vouchers | Seller Centre')
@section('page_title', 'Inventory & Voucher Management')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Summary -->
    <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 font-heading">Inventory, Prices & Shop Vouchers</h1>
            <p class="text-xs text-slate-500 mt-1">Add, update, or archive products, manage stock levels, and create discount vouchers.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="document.getElementById('addProductModal').classList.remove('hidden')" class="bg-[#6F6382] hover:bg-[#564B68] text-white text-xs font-black px-4 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add New Product</span>
            </button>
            <button type="button" onclick="document.getElementById('addVoucherModal').classList.remove('hidden')" class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-black px-4 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span>Create Shop Voucher</span>
            </button>
        </div>
    </div>

    <!-- Inventory Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200 text-xs font-bold">
        <a href="{{ route('seller.inventory', ['tab' => 'all']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ $tab === 'all' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            All Products ({{ $counts['all'] }})
        </a>
        <a href="{{ route('seller.inventory', ['tab' => 'active']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap {{ $tab === 'active' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            ✓ Active Products ({{ $counts['active'] }})
        </a>
        <a href="{{ route('seller.inventory', ['tab' => 'low_stock']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap inline-flex items-center gap-1.5 {{ $tab === 'low_stock' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Low Stock Alert ({{ $counts['low_stock'] }})</span>
        </a>
        <a href="{{ route('seller.inventory', ['tab' => 'archived']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap inline-flex items-center gap-1.5 {{ $tab === 'archived' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            <span>Archived Items ({{ $counts['archived'] }})</span>
        </a>
        <a href="{{ route('seller.inventory', ['tab' => 'vouchers']) }}" class="px-4 py-2 rounded-xl transition whitespace-nowrap inline-flex items-center gap-1.5 {{ $tab === 'vouchers' ? 'bg-[#6F6382] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            <span>Shop Vouchers ({{ $counts['vouchers'] }})</span>
        </a>
    </div>

    @if($tab === 'vouchers')
        <!-- Section: Shop Vouchers & Discounts -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Active Shop Vouchers & Promo Codes</h2>
                    <p class="text-xs text-slate-500">Provide exclusive discounts to attract buyers and boost sales volume</p>
                </div>
                <button type="button" onclick="document.getElementById('addVoucherModal').classList.remove('hidden')" class="bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 text-xs font-bold px-3 py-1.5 rounded-xl transition">
                    + New Voucher
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($vouchers as $voucher)
                    <div class="p-5 rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50/40 relative overflow-hidden space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-black text-lg text-amber-900 bg-amber-100 px-3 py-1 rounded-xl border border-amber-200">
                                {{ $voucher['code'] }}
                            </span>
                            <span class="text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">
                                {{ $voucher['status'] }}
                            </span>
                        </div>
                        <div class="text-xs space-y-1">
                            <div class="font-extrabold text-slate-900 text-sm">
                                {{ $voucher['discount_type'] === 'fixed' ? '₱' . number_format($voucher['discount_value'], 2) . ' OFF' : $voucher['discount_value'] . '% DISCOUNT' }}
                            </div>
                            <div class="text-slate-600">Min. Spend: <strong>₱{{ number_format($voucher['min_spend'], 2) }}</strong></div>
                            <div class="text-slate-500 text-[11px]">Valid Until: {{ $voucher['valid_until'] }}</div>
                        </div>
                        <div class="pt-2 border-t border-amber-200/60 flex items-center justify-between text-[11px] text-slate-500">
                            <span>Claimed: <strong>{{ $voucher['used_count'] }}</strong> / {{ $voucher['usage_limit'] }}</span>
                            <span class="text-emerald-700 font-bold">Active in store</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Section: Products Catalog Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Products Catalog & Stock Levels</h2>
                    <p class="text-xs text-slate-500">Monitor on-hand inventory, update prices, and control product availability</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="p-4">Product Details</th>
                            <th class="p-4">Category & SKU</th>
                            <th class="p-4">Price & Discount</th>
                            <th class="p-4">Stock Level</th>
                            <th class="p-4">Sales Performance</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($filteredProducts as $product)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200 shrink-0">
                                        <div class="max-w-xs">
                                            <div class="font-extrabold text-slate-900 text-sm leading-snug truncate">{{ $product['name'] }}</div>
                                            <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">{{ $product['description'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-800">{{ $product['category'] }}</div>
                                    <div class="text-[11px] font-mono text-slate-400">{{ $product['sku'] }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-black text-slate-900 text-sm">₱{{ number_format($product['price'], 2) }}</div>
                                    @if($product['original_price'] > $product['price'])
                                        <div class="text-[11px] text-slate-400 line-through">₱{{ number_format($product['original_price'], 2) }}</div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($product['stock'] <= 5)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                            {{ $product['stock'] }} units (Critical)
                                        </span>
                                    @elseif($product['stock'] <= 10)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-200">
                                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>{{ $product['stock'] }} units (Low)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ✓ {{ $product['stock'] }} in stock
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-800">{{ $product['sales_count'] ?? 0 }} sold</div>
                                    <div class="text-[11px] text-amber-500 font-bold inline-flex items-center gap-1">
                                        <svg class="w-3 h-3 text-amber-400 fill-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <span>{{ $product['rating'] ?? 5.0 }}</span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if(($product['status'] ?? 'active') === 'active')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">
                                            Archived
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-right space-x-1 whitespace-nowrap">
                                    <!-- Edit Trigger Button -->
                                    <button type="button" onclick="openEditModal({{ json_encode($product) }})" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-xl transition">
                                        Edit
                                    </button>

                                    <!-- Archive / Unarchive Action -->
                                    <form action="{{ route('seller.inventory.archive', $product['id']) }}" method="POST" class="inline-block m-0">
                                        @csrf
                                        <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-xl transition">
                                            {{ ($product['status'] ?? 'active') === 'archived' ? 'Restore' : 'Archive' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    No products found under this tab.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

<!-- Modal 1: Add New Product -->
<div id="addProductModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-extrabold text-slate-900">Add New Product to Inventory</h3>
            <button type="button" onclick="document.getElementById('addProductModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-2xl font-bold">&times;</button>
        </div>

        <form action="{{ route('seller.inventory.add') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            
            <div>
                <label class="block font-bold text-slate-700 mb-1">Product Name *</label>
                <input type="text" name="name" required placeholder="e.g. Sony WH-1000XM5 Headphones" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Category *</label>
                    <select name="category" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                        <option value="Electronics & Audio">Electronics & Audio</option>
                        <option value="Computer Accessories">Computer Accessories</option>
                        <option value="Tablets & Mobile">Tablets & Mobile</option>
                        <option value="Keyboards & Mice">Keyboards & Mice</option>
                        <option value="Storage & Drives">Storage & Drives</option>
                        <option value="Home & Office">Home & Office</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">SKU Code</label>
                    <input type="text" name="sku" placeholder="Auto-generated if blank" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Selling Price (₱) *</label>
                    <input type="number" step="0.01" name="price" required placeholder="999.00" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Original Price (₱)</label>
                    <input type="number" step="0.01" name="original_price" placeholder="1299.00" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Initial Stock *</label>
                    <input type="number" name="stock" required value="20" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Product Image URL</label>
                <input type="url" name="image" placeholder="https://images.unsplash.com/..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="3" placeholder="Provide product specifications, features, warranty details..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('addProductModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition">Cancel</button>
                <button type="submit" class="bg-[#6F6382] hover:bg-[#564B68] text-white font-bold px-6 py-2.5 rounded-xl shadow-md transition">Save & Publish Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Product -->
<div id="editProductModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-extrabold text-slate-900">Edit Product & Adjust Stock</h3>
            <button type="button" onclick="document.getElementById('editProductModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-2xl font-bold">&times;</button>
        </div>

        <form id="editProductForm" method="POST" class="space-y-4 text-xs">
            @csrf
            
            <div>
                <label class="block font-bold text-slate-700 mb-1">Product Name</label>
                <input type="text" id="edit_name" name="name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Category</label>
                    <input type="text" id="edit_category" name="category" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Stock Level (Units)</label>
                    <input type="number" id="edit_stock" name="stock" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Sale Price (₱)</label>
                    <input type="number" step="0.01" id="edit_price" name="price" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Original Price (₱)</label>
                    <input type="number" step="0.01" id="edit_original_price" name="original_price" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Description</label>
                <textarea id="edit_description" name="description" rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6F6382]"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('editProductModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition">Cancel</button>
                <button type="submit" class="bg-[#6F6382] hover:bg-[#564B68] text-white font-bold px-6 py-2.5 rounded-xl shadow-md transition">Update Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Add Shop Voucher -->
<div id="addVoucherModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-extrabold text-slate-900">Create Shop Voucher</h3>
            <button type="button" onclick="document.getElementById('addVoucherModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-2xl font-bold">&times;</button>
        </div>

        <form action="{{ route('seller.inventory.vouchers.add') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            
            <div>
                <label class="block font-bold text-slate-700 mb-1">Voucher Code *</label>
                <input type="text" name="code" required placeholder="e.g. PAYDAY200" class="w-full uppercase font-mono font-bold bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Discount Type</label>
                    <select name="discount_type" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="fixed">Fixed Amount (₱)</option>
                        <option value="percent">Percentage (%)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Discount Value</label>
                    <input type="number" step="0.01" name="discount_value" required value="100" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Min. Spend (₱)</label>
                    <input type="number" step="0.01" name="min_spend" required value="1000" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Usage Limit</label>
                    <input type="number" name="usage_limit" required value="50" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Expiry Date</label>
                <input type="date" name="valid_until" required value="{{ now()->addDays(30)->format('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('addVoucherModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition">Cancel</button>
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md transition">Publish Voucher</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(product) {
        document.getElementById('editProductForm').action = '/seller/inventory/' + product.id + '/update';
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_category').value = product.category;
        document.getElementById('edit_stock').value = product.stock;
        document.getElementById('edit_price').value = product.price;
        document.getElementById('edit_original_price').value = product.original_price;
        document.getElementById('edit_description').value = product.description || '';
        document.getElementById('editProductModal').classList.remove('hidden');
    }
</script>
@endsection

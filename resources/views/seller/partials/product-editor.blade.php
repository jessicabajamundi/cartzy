<link rel="stylesheet" href="{{ asset('css/seller-add-product.css') }}?v={{ filemtime(public_path('css/seller-add-product.css')) }}">
@php($hasVariants = (bool) old('has_variants'))
<div id="add-product" class="product-editor">
    <header class="pe-header">
        <a class="pe-back" href="{{ route('seller.products') }}">&larr; Back to products</a>
        <span class="pe-kicker">YOUR NEXT BESTSELLER</span>
        <h1>Add product</h1>
        <p>Start with the essentials, then add photos and optional colors or sizes.</p>
    </header>

    @if(!$shop)
        <div class="pe-blocked">
            <strong>Set up your store first</strong>
            <p>You need a store profile before you can add products.</p>
            <a class="button small" href="{{ route('seller.account') }}">Set up store &rarr;</a>
        </div>
    @elseif(auth()->user()->status !== 'active' || !$shop->isApproved())
        <div class="pe-blocked is-pending">
            <strong>Waiting for approval</strong>
            <p>Your seller account is still being reviewed. You can add products once an administrator approves it.</p>
            <a class="button small" href="{{ route('seller.account') }}">Check verification status &rarr;</a>
        </div>
    @elseif($categories->isEmpty())
        <div class="pe-blocked">
            <strong>No categories yet</strong>
            <p>An administrator needs to add a product category before you can create products.</p>
        </div>
    @else
    <nav class="pe-steps" aria-label="Product form sections">
        <a href="#product-details"><span>1</span> Product details</a>
        <a href="#product-media"><span>2</span> Photos</a>
        <a href="#product-pricing"><span>3</span> Price &amp; stock</a>
        <a href="#product-options"><span>+</span> Colors &amp; sizes (optional)</a>
    </nav>
    <div class="pe-layout">
    @php($toBytes = fn ($value) => (int) trim((string) $value) * (['g' => 1073741824, 'm' => 1048576, 'k' => 1024][strtolower(substr(trim((string) $value), -1))] ?? 1))
    <form id="add-product-form" class="pe-form" action="{{ route('seller.products.add') }}" method="POST" enctype="multipart/form-data"
          data-max-files="{{ (int) ini_get('max_file_uploads') }}" data-max-upload-bytes="{{ $toBytes(ini_get('post_max_size')) }}">
        @csrf
        <input type="hidden" name="primary_variant" value="1">

        @if($errors->any())
            <div class="notice error pe-form-error-banner" role="alert" style="margin-bottom: 20px;">
                <strong>Please check your entries:</strong>
                <ul style="margin: 6px 0 0 18px; padding: 0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Details --}}
        @php($swatches = ['Black' => '#1F1F1F', 'White' => '#F5F5F5', 'Gray' => '#9E9E9E', 'Navy' => '#1F2F55', 'Blue' => '#3B6DB3', 'Red' => '#C0392B', 'Green' => '#4B8B52', 'Pink' => '#F4B6C2', 'Beige' => '#E3D5B8', 'Brown' => '#7A5536', 'Yellow' => '#E6C13B', 'Purple' => '#7D55A8'])
        <section class="pe-card" id="product-details" aria-labelledby="pe-details-title">
            <header class="pe-card-head">
                <h2 id="pe-details-title">Product details</h2>
                <p>Help buyers understand what you sell. Fields marked * are required.</p>
            </header>
            <div class="pe-card-body pe-grid">
                <label class="pe-field">Product name *
                    <input name="name" value="{{ old('name') }}" required maxlength="255" placeholder="e.g. Everyday cotton T-shirt">
                </label>
                <label class="pe-field">SKU (optional)
                    <input name="sku" id="single-sku-input" value="{{ old('sku') }}" maxlength="100" placeholder="Generated when left blank">
                    <small class="pe-hint">Your own stock code. Must be unique.</small>
                </label>
                @include('seller.partials.category-multi-select', ['categories' => $categories])
                <label class="pe-field">For (optional)
                    @php($curGender = (string) old('gender', ''))
                    <select name="gender">
                        <option value="" @selected($curGender === '')>Not specified</option>
                        <optgroup label="Adult">
                            <option value="men" @selected($curGender === 'men')>Men</option>
                            <option value="women" @selected($curGender === 'women')>Women</option>
                            <option value="unisex" @selected($curGender === 'unisex')>Unisex</option>
                        </optgroup>
                        <optgroup label="Kids">
                            <option value="boys" @selected($curGender === 'boys')>Boys</option>
                            <option value="girls" @selected($curGender === 'girls')>Girls</option>
                            <option value="kids" @selected($curGender === 'kids' || $curGender === 'all_kids')>All Kids</option>
                        </optgroup>
                    </select>
                </label>
                <label class="pe-field">Badge (optional)
                    <select name="badge">
                        <option value="" @selected(old('badge') == '')>No badge</option>
                        @foreach(['Hot Deal', 'Best Seller', 'New Arrival', 'Trending', 'Authentic 100%', 'Limited Edition'] as $badge)
                            <option value="{{ $badge }}" @selected(old('badge') === $badge)>{{ $badge }}</option>
                        @endforeach
                    </select>
                    <small class="pe-hint">"Sale" is added automatically during a scheduled sale.</small>
                </label>
                <label class="pe-field pe-full">Description (optional)
                    <textarea rows="3" name="description" maxlength="5000">{{ old('description') }}</textarea>
                    <small class="pe-hint">Materials, sizing, care, and what's in the box. Up to 5,000 characters.</small>
                </label>
            </div>
        </section>

        {{-- Photos --}}
        <section class="pe-card" id="product-media" aria-labelledby="pe-media-title">
            <header class="pe-card-head pe-card-head-row">
                <div>
                    <h2 id="pe-media-title">Photos <span class="pe-optional">optional</span></h2>
                    <p>Add as many photos as you like, up to 10 MB total. The first one is the cover.</p>
                </div>
                <span class="pe-photo-count" id="photos-counter-badge" aria-live="polite"><span id="photos-counter-text">0 photos · 0.00 / 10 MB</span></span>
            </header>
            <div class="pe-card-body pe-photos">
                <input type="file" name="images[]" id="product-images-input" multiple accept="image/*,.jpg,.jpeg,.png,.webp,.jfif,.avif" style="display:none !important;" tabindex="-1">
                <input type="file" id="product-images-picker" multiple accept="image/*,.jpg,.jpeg,.png,.webp,.jfif,.avif" style="display:none !important;" tabindex="-1">

                <label class="pe-dropzone" id="photos-dropzone" for="product-images-picker" role="button" tabindex="0" aria-label="Add product photos">
                    <span class="pe-dropzone-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4 M7 9l5-5 5 5 M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/></svg>
                    </span>
                    <span>
                        <strong>+ Upload photos</strong>
                        <small>Click or drag files here · JPG, PNG, WebP · 10 MB total for all photos</small>
                    </span>
                </label>
                <div id="product-photos-preview-grid" class="media-preview-grid pe-photo-grid" style="display: none;"></div>
            </div>
        </section>

        {{-- Price & stock --}}
        <section class="pe-card" id="product-pricing" aria-labelledby="pe-pricing-title">
            <header class="pe-card-head pe-card-head-row">
                <div>
                    <h2 id="pe-pricing-title">Your first product option</h2>
                    <p>Set your price and available stock. Color and size appear when relevant to the category you choose.</p>
                </div>
            </header>
            <div class="pe-card-body">
                {{-- One version --}}
                <div id="single-variant-fields" class="pe-grid">
                    <label class="pe-field" data-primary-field="color" hidden>Color
                        <input name="primary_color" value="{{ old('primary_color') }}" maxlength="100" placeholder="e.g. Black" disabled>
                        <small class="pe-hint">Leave blank if color does not apply.</small>
                    </label>
                    <label class="pe-field" data-primary-field="size" hidden>Available sizes (US)
                        <input name="primary_size" value="{{ old('primary_size') }}" maxlength="1000" placeholder="e.g. US 7, US 7.5, US 8, US 8.5, US 9, US 10" aria-describedby="primary-size-help" disabled>
                        <div class="pe-size-chips-wrap" data-size-chips-for="primary_size">
                            <div class="pe-size-chips-head">
                                <span class="pe-size-chips-label">Quick US sizes:</span>
                                <div class="pe-size-type-toggle">
                                    <button type="button" class="pe-size-type-btn is-active" data-editor-size-toggle="adult">@include('seller.partials.icon', ['name' => 'user', 'size' => 14]) <span>Adult</span></button>
                                    <button type="button" class="pe-size-type-btn" data-editor-size-toggle="kids">@include('seller.partials.icon', ['name' => 'child', 'size' => 14]) <span>Kids</span></button>
                                </div>
                            </div>
                            <div class="pe-size-chips-list" data-chips-list-container="primary_size">
                                @foreach(['US 6', 'US 6.5', 'US 7', 'US 7.5', 'US 8', 'US 8.5', 'US 9', 'US 9.5', 'US 10', 'US 10.5', 'US 11', 'US 12'] as $s)
                                    <button type="button" class="pe-size-chip" data-size="{{ $s }}">{{ $s }}</button>
                                @endforeach
                            </div>
                        </div>
                        <small class="pe-hint" id="primary-size-help">Separate sizes with commas (e.g. US 7, US 7.5, US 8). A price and stock row appears for each size under this color. Leave blank if not applicable.</small>
                    </label>
                    <label class="pe-field" id="primary-price-field">Price *
                        <span class="pe-money"><span>₱</span><input type="number" name="price" id="single-price-input" min="0.01" max="99999999.99" step="0.01" value="{{ old('price') }}" placeholder="0.00" required></span>
                    </label>
                    <label class="pe-field" id="primary-stock-field">Stock *
                        <input type="number" name="stock" id="single-stock-input" min="0" max="1000000" value="{{ old('stock', 0) }}" required>
                    </label>
                    <div class="pe-full pe-primary-sizes" id="primary-size-stocks" hidden>
                        <h3>Price &amp; stock per size</h3>
                        <div class="pe-size-price-head" aria-hidden="true"><span>Size</span><span>Price</span><span>Stock</span></div>
                        <div id="primary-size-stock-rows" class="pe-size-price-list">
                            @foreach(old('primary_size_stocks', []) as $index => $sizeStock)
                                <div class="pe-size-price-row">
                                    <strong>{{ $sizeStock['size'] ?? '' }}</strong>
                                    <input type="hidden" name="primary_size_stocks[{{ $index }}][size]" value="{{ $sizeStock['size'] ?? '' }}" disabled>
                                    <label class="pe-field"><span class="pe-size-field-label">{{ $sizeStock['size'] ?? '' }} price (₱)</span>
                                        <span class="pe-money"><span aria-hidden="true">&#8369;</span><input type="number" data-size-price name="primary_size_stocks[{{ $index }}][price]" value="{{ $sizeStock['price'] ?? old('price') }}" min="0.01" max="99999999.99" step="0.01" disabled></span>
                                    </label>
                                    <label class="pe-field"><span class="pe-size-field-label">{{ $sizeStock['size'] ?? '' }} stock</span>
                                        <input type="number" data-size-stock name="primary_size_stocks[{{ $index }}][stock]" value="{{ $sizeStock['stock'] ?? 0 }}" min="0" max="1000000" step="1" disabled>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <section class="pe-card pe-optional-section" id="product-options" aria-labelledby="pe-options-title">
            <header class="pe-card-head">
                <span class="pe-kicker">OPTIONAL EXTRAS</span>
                <h2 id="pe-options-title">More product options <span class="pe-optional">Optional</span></h2>
                <p>Skip this section if you sell just one version of your product.</p>
            </header>
            <div class="pe-card-body">
                <label class="pe-switch-row pe-option-toggle" for="enable-variants-toggle">
                    <span id="variants-help"><strong>Add more colors or sizes <span class="pe-optional">Optional</span></strong><small>Your first option stays above. Add only the other options you want to sell here.</small></span>
                    <span class="pe-switch">
                        <input type="checkbox" id="enable-variants-toggle" name="has_variants" value="1" aria-describedby="variants-help" aria-controls="multiple-variants-fields" @checked($hasVariants)>
                        <span aria-hidden="true"></span>
                    </span>
                </label>
                {{-- Several options --}}
                <div id="multiple-variants-fields" class="pe-variants" style="{{ $hasVariants ? '' : 'display:none;' }}">
                    <div class="pe-option-builder">
                        <div class="pe-builder-head"><span class="pe-step-badge" aria-hidden="true">1</span><div><h3>Add another color or style</h3><p>Pick a color, then the sizes available in that color.</p></div></div>
                        <div class="pe-grid">
                            <div class="pe-field pe-color-picker">
                                <span class="pe-color-wheel-title">Color</span>
                                <small class="pe-hint" id="option-color-help">Drag the knob around the wheel, adjust brightness, or type a color code.</small>
                                <div class="pe-color-wheel" data-color-wheel>
                                    <div class="pe-wheel">
                                        <canvas class="pe-wheel-canvas" width="440" height="440" aria-hidden="true"></canvas>
                                        <div class="pe-wheel-shade" aria-hidden="true"></div>
                                        <span class="pe-wheel-knob" tabindex="0" role="slider" aria-label="Color wheel: arrow keys change hue (left, right) and intensity (up, down)" aria-valuemin="0" aria-valuemax="360" aria-valuenow="0" aria-valuetext="#1E293B"></span>
                                    </div>
                                    <div class="pe-wheel-controls">
                                        <div class="pe-wheel-preview"><span class="pe-wheel-preview-swatch" aria-hidden="true"></span><div><small>Selected color</small><strong class="pe-wheel-preview-name">—</strong></div></div>
                                        <label class="pe-wheel-field" for="option-color-brightness">Brightness
                                            <input id="option-color-brightness" class="pe-wheel-brightness" type="range" min="0" max="100" step="1" value="100">
                                        </label>
                                        <label class="pe-wheel-field" for="option-color-hex">Color code
                                            <span class="pe-wheel-hex"><span aria-hidden="true">#</span><input id="option-color-hex" maxlength="7" spellcheck="false" autocomplete="off" inputmode="text" pattern="#?[0-9A-Fa-f]{6}" placeholder="1E293B" aria-describedby="option-color-help"></span>
                                        </label>
                                        <label class="pe-wheel-field" for="option-colors">Color name buyers will see
                                            <input id="option-colors" maxlength="100" placeholder="e.g. Navy, Floral" autocomplete="off">
                                            <small class="pe-hint">Filled in from the wheel. Change it to any name you like.</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <label class="pe-field">Available sizes (US) <span class="pe-optional">If applicable</span>
                                <input id="option-sizes" maxlength="500" placeholder="e.g. US 7, US 7.5, US 8, US 8.5, US 9, US 10" autocomplete="off">
                                <div class="pe-size-chips-wrap" data-size-chips-for="option-sizes">
                                    <div class="pe-size-chips-head">
                                        <span class="pe-size-chips-label">Quick US sizes:</span>
                                        <div class="pe-size-type-toggle">
                                            <button type="button" class="pe-size-type-btn is-active" data-editor-size-toggle="adult">@include('seller.partials.icon', ['name' => 'user', 'size' => 14]) <span>Adult</span></button>
                                            <button type="button" class="pe-size-type-btn" data-editor-size-toggle="kids">@include('seller.partials.icon', ['name' => 'child', 'size' => 14]) <span>Kids</span></button>
                                        </div>
                                    </div>
                                    <div class="pe-size-chips-list" data-chips-list-container="option-sizes">
                                        @foreach(['US 6', 'US 6.5', 'US 7', 'US 7.5', 'US 8', 'US 8.5', 'US 9', 'US 9.5', 'US 10', 'US 10.5', 'US 11', 'US 12'] as $s)
                                            <button type="button" class="pe-size-chip" data-size="{{ $s }}">{{ $s }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <small class="pe-hint">Separate sizes with commas (e.g. US 7, US 7.5, US 8). Leave blank for an option without sizes.</small>
                            </label>
                            <label class="pe-field">Price for these options
                                <span class="pe-money"><span>&#8369;</span><input id="option-price" type="number" min="0.01" max="99999999.99" step="0.01" placeholder="Use the first product price"></span>
                                <small class="pe-hint">Leave blank to use the price above.</small>
                            </label>
                            <label class="pe-field">Starting stock per size
                                <input id="option-stock" type="number" min="0" max="1000000" step="1" value="0">
                                <small class="pe-hint">You can adjust each quantity in the list below.</small>
                            </label>
                        </div>
                        <div class="pe-color-photo is-multi">
                            <div class="pe-field">
                                <span class="pe-color-photo-title">Photos of this color or style</span>
                                <small class="pe-hint" id="option-photo-help">Add as many photos as you like — front, back, close-ups. JPG, PNG or WebP, up to 10 MB each. They're shared by every size you add together, and the first photo is the main one.</small>
                            </div>
                            <div class="pe-color-photo-grid" id="option-photo-grid" aria-live="polite">
                                <label class="pe-color-photo-add" for="option-photo">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                    <span data-add-photo-label>Add photos</span>
                                    <input id="option-photo" type="file" multiple accept="image/jpeg,image/png,image/webp" aria-describedby="option-photo-help">
                                </label>
                            </div>
                            <p class="pe-hint pe-color-photo-count" id="option-photo-count">No photos yet</p>
                        </div>
                        <div class="pe-builder-action"><button type="button" class="button" id="generate-product-options"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Add to options</button><span class="pe-hint">Repeat for another color or style.</span></div>
                    </div>
                    <div class="pe-options-review">
                        <div class="pe-review-heading"><h3>2. Review added options</h3><span id="variant-count" class="pe-variant-count"></span></div>
                        <p class="pe-hint">These are in addition to your first product option. Check the price and stock before saving.</p>
                        <p id="options-empty" class="pe-options-empty">No extra options yet. Add a color or size above to get started.</p>
                        {{-- Each color's photos are uploaded once from here; option rows point to them by photo_group. --}}
                        <div id="option-photo-store" hidden></div>
                        <div id="shades-container" class="pe-variant-list">
                            @foreach(old('variants', []) ?: [] as $idx => $v)
                                @include('seller.partials.product-editor-variant', ['idx' => $idx, 'v' => $v, 'required' => $hasVariants])
                            @endforeach
                        </div>
                    </div>
                    <p id="variant-feedback" class="pe-feedback" role="status" aria-live="polite"></p>
                </div>

            </div>
        </section>

        <footer class="pe-actions pe-create-actions">
            <a class="button secondary" href="{{ route('seller.products') }}">Cancel</a>
            <button type="submit" class="button" data-create-product>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>
                Create product
            </button>
        </footer>
    </form>

    <aside class="pe-aside">
        <section class="pe-card">
            <header class="pe-card-head"><span class="pe-kicker">A LITTLE HELP</span><h2>Ready to sell?</h2><p>A clear listing helps buyers choose confidently.</p></header>
            <ul class="pe-aside-list">
                <li>Use a specific name and choose the right category.</li>
                <li>Add a clear cover photo and useful product details.</li>
                <li>Only one version? Leave colors and sizes switched off.</li>
                <li>Check stock for each option. You can add more colors and sizes later.</li>
                <li>Schedule sales with a start and end time.</li>
                <li>Restock anytime from <a href="{{ route('seller.inventory') }}">Inventory</a>.</li>
                <li>Hide it from buyers without deleting it.</li>
            </ul>
        </section>
    </aside>
    </div>
    @endif
</div>

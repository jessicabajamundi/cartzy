{{-- One short promo video for a product. Its size shares the product's media budget with the photos.
     Params: $product (optional, on the edit page). Behaviour is in seller-product-video.js. --}}
@php
    $videoProduct = $product ?? null;
    $videoUrl = $videoProduct?->video_url;
    $videoDisk = \Illuminate\Support\Facades\Storage::disk('public');
    $videoBytes = $videoProduct?->video_path && $videoDisk->exists($videoProduct->video_path) ? $videoDisk->size($videoProduct->video_path) : 0;
@endphp
<div class="pe-video-field" data-product-video data-video-bytes="{{ $videoBytes }}" data-budget-bytes="{{ \App\Models\Product::MEDIA_BUDGET_MB * 1024 * 1024 }}">
    <input type="file" name="video" id="product-video-input" accept="video/mp4,video/webm" style="display:none !important;" tabindex="-1" aria-describedby="product-video-help">
    @if($videoProduct)<input type="hidden" name="remove_video" value="0" data-remove-video>@endif

    <label class="pe-dropzone" for="product-video-input" role="button" tabindex="0" data-video-dropzone @if($videoUrl) hidden @endif aria-label="Add a product video">
        <span class="pe-dropzone-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m10 9 5 3-5 3z"/></svg>
        </span>
        <span>
            <strong>+ Upload a short video</strong>
            <small id="product-video-help">Click or drag a file here · MP4 or WebM · One video to promote this product</small>
        </span>
    </label>

    <div class="pe-video-preview" data-video-preview @unless($videoUrl) hidden @endunless>
        <video controls preload="metadata" playsinline @if($videoUrl) src="{{ $videoUrl }}" @endif aria-label="Product video preview"></video>
        <button type="button" class="button secondary small" data-video-remove>Remove video</button>
    </div>
    @error('video')<p class="pe-field-error" role="alert">{{ $message }}</p>@enderror
</div>
<script src="{{ asset('js/seller-product-video.js') }}?v={{ filemtime(public_path('js/seller-product-video.js')) }}"></script>

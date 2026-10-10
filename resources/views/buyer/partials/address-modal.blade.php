<dialog id="newAddressModal" class="address-dialog" aria-labelledby="addressModalTitle" aria-describedby="addressModalDescription" data-auto-open="{{ $errors->addressBook->any() && old('form_key') === 'new' ? 'true' : 'false' }}">
    <header class="address-dialog-header">
        <div>
            <h3 id="addressModalTitle">Add New Delivery Address</h3>
            <p id="addressModalDescription">Enter accurate details for fast order deliveries.</p>
        </div>
        <button type="button" data-close-address-modal aria-label="Close address form" class="address-dialog-close">&times;</button>
    </header>
    <div class="address-dialog-body">
        @if($errors->addressBook->any() && old('form_key') === 'new')
            <div class="settings-error" role="alert" tabindex="-1">
                <strong>Please check your address.</strong>
                <ul>@foreach($errors->addressBook->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
            </div>
        @endif
        @include('buyer.partials.address-fields', ['entry' => null, 'legacyEntry' => false, 'isModal' => true])
    </div>
</dialog>
<script src="{{ asset('js/buyer/address-modal.js') }}?v={{ filemtime(public_path('js/buyer/address-modal.js')) }}"></script>

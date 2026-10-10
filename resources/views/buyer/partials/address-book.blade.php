@include('buyer.partials.settings-style')
<div class="settings-card" id="saved-addresses-card">
    <div class="settings-section-heading"><h3>Saved addresses</h3><p>Add delivery locations and choose the one to use by default.</p></div>
    @if($errors->addressBook->any())
        <div class="settings-error" role="alert"><strong>Please check your address.</strong><ul>@foreach($errors->addressBook->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    <div class="space-y-4">
        @forelse($savedAddresses as $entry)
            <div class="settings-address">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <strong>{{ $entry->label }}</strong>
                    @if($entry->is_default)<span class="settings-status">Default address</span>@endif
                </div>
                <p class="mt-2 text-sm font-semibold text-gray-800">{{ $entry->recipient }}</p>
                <p class="text-sm text-gray-500">{{ $entry->phone }}</p>
                <p class="mt-2 text-sm text-gray-600">{{ $entry->formatted_address }}</p>
                <div class="mt-3 flex items-center gap-3">
                    @unless($entry->is_default)
                        <form method="POST" action="{{ route('account.addresses.default', $entry->id) }}">@csrf<button class="settings-text-button" type="submit">Set as default</button></form>
                        <span class="text-gray-300">·</span>
                    @endunless
                    <form method="POST" action="{{ route('account.addresses.delete', $entry->id) }}" onsubmit="return confirm('Are you sure you want to delete this address?');">
                        @csrf
                        @method('DELETE')
                        <button class="text-xs font-semibold text-rose-600 hover:text-rose-800 cursor-pointer" type="submit">Delete address</button>
                    </form>
                </div>
                <details class="settings-edit" @if($errors->addressBook->any() && old('form_key') == $entry->id) open @endif>
                    <summary>Edit address</summary>
                    @include('buyer.partials.address-fields', ['entry' => $entry, 'legacyEntry' => false])
                </details>
            </div>
        @empty
            @if($user->street_address || $user->address)
                @php($legacyAddress = new \App\Models\Address(['label' => 'Home', 'recipient' => $user->name, 'phone' => $user->phone, 'line1' => $user->street_address ?: $user->address, 'barangay' => $user->barangay, 'city' => $user->city, 'province' => $user->province, 'postal_code' => $user->postal_code, 'is_default' => true]))
                <div class="settings-address">
                    <div class="flex flex-wrap items-center justify-between gap-2"><strong>Home</strong><span class="settings-status">Default address</span></div>
                    <p class="mt-2 text-sm text-gray-600">{{ $user->address ?: $legacyAddress->formatted_address }}</p>
                    <details class="settings-edit" @if($errors->addressBook->any() && old('form_key') === 'legacy') open @endif>
                        <summary>Edit address</summary>
                        @include('buyer.partials.address-fields', ['entry' => $legacyAddress, 'legacyEntry' => true])
                    </details>
                </div>
            @else
                <p class="settings-empty">No saved addresses yet. Add your first delivery address below.</p>
            @endif
        @endforelse
    </div>
    <div class="mt-5">
        <button type="button" data-open-address-modal aria-haspopup="dialog" aria-controls="newAddressModal" class="w-full py-3.5 px-4 rounded-xl border border-dashed border-[#C08B7F]/60 bg-[#FAF9FC] hover:bg-[#F3EFF7] text-[#564B68] font-bold text-sm flex items-center justify-center gap-2 transition cursor-pointer shadow-2xs">
            <svg class="w-4 h-4 text-[#564B68]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span>+ Add a new address</span>
        </button>
    </div>

</div>

@once
@push('scripts')
    @include('buyer.partials.address-modal')
@endpush
@endonce

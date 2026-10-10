                        {{-- ID Verification --}}
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-extrabold uppercase tracking-wider text-gray-700">Identity Verification</h3>
                                @if(method_exists($user, 'isIdVerified') && $user->isIdVerified())
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">✓ Verified Buyer</span>
                                @elseif(($user->id_status ?? '') === 'pending')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">⏳ Verification Pending</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600">Unverified</span>
                                @endif
                            </div>

                            @if(method_exists($user, 'isIdVerified') && $user->isIdVerified())
                                <p class="text-xs text-gray-500">Your identity document has been verified. You have full checkout and buyer protection privileges.</p>
                            @elseif(($user->id_status ?? '') === 'pending')
                                <p class="text-xs text-amber-700 bg-amber-50 p-3 rounded-xl border border-amber-200">Your ID document ({{ $user->id_type ?? 'Government ID' }}) was submitted and is currently being reviewed by administrators.</p>
                            @else
                                <p class="text-xs text-gray-500 mb-3">Upload a clear photo or copy of a valid government ID (National ID, Driver's License, Passport, etc.) to verify your account.</p>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <label class="block"><span class="mb-1.5 block text-xs font-bold text-gray-600">ID Type</span>
                                        <select name="id_type" class="settings-input">
                                            <option value="">Select ID Type</option>
                                            @foreach(['Philippine National ID', "Driver's License", 'Passport', 'UMID / SSS', 'Postal ID', 'Voter ID', 'PRC ID'] as $t)
                                                <option value="{{ $t }}" @selected(old('id_type', $user->id_type) === $t)>{{ $t }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="block"><span class="mb-1.5 block text-xs font-bold text-gray-600">ID Number (Optional)</span>
                                        <input name="id_number" value="{{ old('id_number', $user->id_number) }}" placeholder="e.g. 1234-5678-9012" class="settings-input">
                                    </label>
                                    <label class="block sm:col-span-2"><span class="mb-1.5 block text-xs font-bold text-gray-600">Upload ID Document</span>
                                        <input type="file" name="id_photo" accept=".jpg,.jpeg,.png,.pdf" class="settings-input">
                                    </label>
                                </div>
                            @endif
                        </div>


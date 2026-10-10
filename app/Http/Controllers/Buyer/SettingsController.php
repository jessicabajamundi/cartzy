<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function saveAddress(Request $request)
    {
        $data = $request->validateWithBag('addressBook', [
            'address_id' => ['nullable', 'integer'],
            'legacy_address' => ['nullable', 'boolean'],
            'label' => ['required', 'in:Home,Work,School,Other'],
            'recipient' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20'],
            'line1' => ['required', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:100'],
            'barangay' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ], [
            'phone.regex' => 'Contact number must contain numbers only.',
            'postal_code.regex' => 'Postal code must contain numbers only.',
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $address = !empty($data['address_id'])
                ? Address::where('user_id', $user->id)->findOrFail($data['address_id'])
                : new Address(['user_id' => $user->id]);

            // Preserve the existing profile address before adding another destination.
            if (!Address::where('user_id', $user->id)->exists() && ($user->street_address || $user->address)) {
                $legacy = Address::create([
                    'user_id' => $user->id, 'label' => 'Home', 'recipient' => $user->name,
                    'phone' => $user->phone ?? '', 'line1' => $user->street_address ?: $user->address,
                    'barangay' => $user->barangay ?? '', 'city' => $user->city ?? '',
                    'province' => $user->province ?? '', 'postal_code' => $user->postal_code ?? '',
                    'is_default' => true,
                ]);
                if ($request->boolean('legacy_address') || (
                    strtolower(trim((string)$legacy->line1)) === strtolower(trim((string)$data['line1']))
                    && strtolower(trim((string)$legacy->city)) === strtolower(trim((string)$data['city']))
                )) {
                    $address = $legacy;
                }
            }

            $isDefault = $address->is_default || $request->boolean('is_default')
                || !Address::where('user_id', $user->id)->where('is_default', true)->exists();
            $regionInput = $data['region'] ?? null;
            unset($data['address_id'], $data['is_default'], $data['legacy_address'], $data['region']);
            $address->fill($data);
            $address->is_default = $isDefault;
            if ($isDefault) {
                Address::where('user_id', $user->id)->where('id', '!=', $address->id ?? 0)->update(['is_default' => false]);
            }
            $address->save();
            if ($isDefault) $this->syncDefault($user, $address, $regionInput);
            if (class_exists(\App\Console\Commands\ExportDatabaseSql::class)) {
                \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
            }
        });

        return back()->with('success', 'Delivery address saved.');
    }

    public function deleteAddress(Request $request, int $address)
    {
        DB::transaction(function () use ($request, $address) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $toDelete = Address::where('user_id', $user->id)->findOrFail($address);
            $wasDefault = $toDelete->is_default;
            $toDelete->delete();

            if ($wasDefault) {
                $nextDefault = Address::where('user_id', $user->id)->first();
                if ($nextDefault) {
                    $nextDefault->update(['is_default' => true]);
                    $this->syncDefault($user, $nextDefault);
                } else {
                    $user->update([
                        'street_address' => null,
                        'barangay' => null,
                        'city' => null,
                        'province' => null,
                        'postal_code' => null,
                        'address' => null,
                    ]);
                }
            }

            if (class_exists(\App\Console\Commands\ExportDatabaseSql::class)) {
                \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
            }
        });

        return back()->with('success', 'Address deleted successfully.');
    }

    public function defaultAddress(Request $request, int $address)
    {
        DB::transaction(function () use ($request, $address) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $saved = Address::where('user_id', $user->id)->findOrFail($address);
            Address::where('user_id', $user->id)->where('id', '!=', $saved->id)->update(['is_default' => false]);
            $saved->update(['is_default' => true]);
            $this->syncDefault($user, $saved);
            if (class_exists(\App\Console\Commands\ExportDatabaseSql::class)) {
                \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
            }
        });

        return back()->with('success', 'Default delivery address updated.');
    }

    private function syncDefault(User $user, Address $address, ?string $region = null): void
    {
        $user->update([
            'street_address' => $address->line1, 'barangay' => $address->barangay,
            'city' => $address->city, 'province' => $address->province,
            'postal_code' => $address->postal_code,
            'region' => $region ?? ($address->province === 'Metro Manila' ? 'NCR' : $user->region),
            'address' => $address->formatted_address,
        ]);
    }

    public function logoutOtherDevices(Request $request)
    {
        $request->validateWithBag('devices', ['device_password' => ['required', 'current_password']]);
        Auth::logoutOtherDevices($request->input('device_password'));
        $request->user()->setRememberToken(Str::random(60));
        $request->user()->save();

        // Remove older database sessions too, including those predating session authentication.
        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))->table(config('session.table'))
                ->where('user_id', $request->user()->id)
                ->where('id', '!=', $request->session()->getId())->delete();
        }

        return back()->with('success', 'Other devices have been signed out. This session remains active.');
    }
}

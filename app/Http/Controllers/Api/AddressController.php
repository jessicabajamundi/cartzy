<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Buyer addresses for the mobile app, stored in the same table as the website's address book.
 * The app's field names are mapped to the website's columns (name -> recipient, municipality -> city, ...).
 */
class AddressController extends Controller
{
    public static function addressJson(Address $address): array
    {
        return [
            'id' => $address->id,
            'user_id' => $address->user_id,
            'label' => $address->label,
            'name' => $address->recipient,
            'phone' => $address->phone,
            'house_number' => '',
            'street' => $address->line1,
            'barangay' => $address->barangay,
            'municipality' => $address->city,
            'province' => $address->province,
            'region' => $address->region,
            'postal_code' => $address->postal_code,
            'is_default' => (bool) $address->is_default,
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'house_number' => 'nullable|string|max:255',
            'street' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        return [
            'label' => $data['label'] ?? 'Home',
            'recipient' => $data['name'],
            'phone' => $data['phone'],
            'line1' => implode(', ', array_filter([trim($data['house_number'] ?? ''), trim($data['street'])])),
            'barangay' => $data['barangay'],
            'city' => $data['municipality'],
            'province' => $data['province'],
            'region' => $data['region'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'is_default' => (bool) ($data['is_default'] ?? false),
        ];
    }

    private function own(Request $request, int $id): Address
    {
        return Address::where('user_id', $request->user()->id)->findOrFail($id);
    }

    private function makeDefault(Address $address): void
    {
        Address::where('user_id', $address->user_id)->where('id', '!=', $address->id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }

    /** GET /addresses and GET /addresses/{userId} (the app sends its own id; others are refused). */
    public function index(Request $request, ?int $userId = null)
    {
        abort_if($userId !== null && $userId !== $request->user()->id, 403);

        return response()->json(['addresses' => Address::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')->orderBy('id')->get()->map(fn ($address) => self::addressJson($address))]);
    }

    public function store(Request $request)
    {
        $address = DB::transaction(function () use ($request) {
            $fields = $this->validated($request);
            $isFirst = !Address::where('user_id', $request->user()->id)->exists();
            $address = Address::create(['user_id' => $request->user()->id] + $fields);
            if ($fields['is_default'] || $isFirst) $this->makeDefault($address);

            return $address->fresh();
        });

        return response()->json(['address' => self::addressJson($address)], 201);
    }

    public function update(Request $request, int $id)
    {
        $address = $this->own($request, $id);
        DB::transaction(function () use ($request, $address) {
            $fields = $this->validated($request);
            $address->update(collect($fields)->except('is_default')->all());
            if ($fields['is_default']) $this->makeDefault($address);
        });

        return response()->json(['address' => self::addressJson($address->fresh())]);
    }

    public function setDefault(Request $request, int $id)
    {
        $address = $this->own($request, $id);
        DB::transaction(fn () => $this->makeDefault($address));

        return response()->json(['address' => self::addressJson($address->fresh())]);
    }

    public function destroy(Request $request, int $id)
    {
        $address = $this->own($request, $id);
        DB::transaction(function () use ($address) {
            $wasDefault = $address->is_default;
            $address->delete();
            if ($wasDefault && ($next = Address::where('user_id', $address->user_id)->orderBy('id')->first())) {
                $this->makeDefault($next);
            }
        });

        return response()->json(['ok' => true]);
    }
}

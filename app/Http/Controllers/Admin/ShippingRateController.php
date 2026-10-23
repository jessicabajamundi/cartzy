<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ShippingCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\Validator;

/** Admin page for the shipping fee of each zone (store pickup province → buyer province). */
class ShippingRateController extends Controller
{
    public function index()
    {
        return view('admin.shipping', [
            'zones' => ShippingCalculator::zones(),
            'examples' => [
                ['Metro Manila', 'Metro Manila'], ['Laguna', 'Laguna'], ['Metro Manila', 'Cavite'], ['Cebu', 'Iloilo'],
                ['Metro Manila', 'Cebu'], ['Cebu', 'Davao del Sur'], ['Metro Manila', 'Davao del Sur'],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $zones = ShippingCalculator::zones();
        $rules = collect($zones)->mapWithKeys(fn ($zone, $key) => ["fees.$key" => ['required', 'numeric', 'min:1', 'max:10000']])->all();
        $data = validator($request->all(), $rules, [
            'fees.*.required' => 'Enter a fee for every zone.',
            'fees.*.min' => 'Each fee must be at least ₱1.',
            'fees.*.max' => 'Each fee can be at most ₱10,000.',
        ])->after(function (Validator $validator) use ($request) {
            // Farther routes should never be cheaper than nearer ones (catches typos like ₱15 instead of ₱150).
            $order = ['same_area', 'same_island', 'near_island', 'far_island'];
            $fees = (array) $request->input('fees', []);
            foreach (array_slice($order, 1) as $i => $zone) {
                $previous = $order[$i];
                if (is_numeric($fees[$zone] ?? null) && is_numeric($fees[$previous] ?? null) && (float) $fees[$zone] < (float) $fees[$previous]) {
                    $validator->errors()->add("fees.$zone", config("shipping.zones.$zone.label").' can\'t be cheaper than '.config("shipping.zones.$previous.label").'.');
                }
            }
        })->validate();

        $changes = [];
        DB::transaction(function () use ($data, $zones, &$changes) {
            foreach ($data['fees'] as $zone => $pesos) {
                $fee = (int) round($pesos * 100);
                if ($fee !== (int) $zones[$zone]['fee_minor']) {
                    $changes[] = $zones[$zone]['label'].': ₱'.number_format($zones[$zone]['fee_minor'] / 100, 2).' → ₱'.number_format($fee / 100, 2);
                }
                DB::table('shipping_rates')->updateOrInsert(['zone' => $zone], ['fee_minor' => $fee, 'updated_at' => now(), 'created_at' => now()]);
            }
            if ($changes) {
                DB::table('admin_audit_logs')->insert([
                    'user_id' => Auth::id(), 'action' => 'Shipping rates updated', 'description' => implode('; ', $changes),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.shipping')->with('success', $changes ? 'Shipping rates saved. New orders use them right away.' : 'No changes to save.');
    }
}

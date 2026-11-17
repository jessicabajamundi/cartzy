# Capacity-based parcel delivery

Products must have packaged length, width, height (cm) and variant weight (grams) before new checkout. Existing products without complete measurements are blocked until their seller updates them under Edit product. No measurements are invented for existing catalog records.

One seller shipment contains all selected quantities from that store. Its immutable checkout snapshot records total weight, total package volume, the largest package dimensions and bulky classification. Dimensions may be rotated. This is a capacity eligibility check, not an exact three-dimensional loading plan; staff must verify actual packing, doors, restraint and handling before handover.

## Setup

1. Run `php artisan migrate --force` in the deployed web application.
2. Review `config/parcel_delivery.php` against the actual vehicle fleet. The supplied limits are configurable operational defaults, not manufacturer ratings. Vehicles with no known limit are ineligible.
3. Sellers update packaged dimensions and weight. Dimensions apply to the largest packaged variant; weight is stored per variant.
4. A logistics partner enables **Bulky delivery service** in Account Management only after confirming storage, access, loading crew and suitable vehicles. Enter its per-parcel bulky handling surcharge; zero explicitly means no additional charge. Standard route rates still apply.
5. Approve and activate van/truck/cargo driver profiles at both handling hubs and declare the supported service regions. Availability at checkout checks registered fleet capability, not momentary online presence. Actual assignment requires availability; offline drivers leave the parcel waiting.

## Flow

- Fit in a motorcycle, tricycle or e-bike: standard parcel; assignment still checks its exact requirements against the selected vehicle.
- Too large/heavy for all three: bulky parcel; only a fitting van, truck or cargo vehicle is eligible, even if a car could otherwise fit.
- Checkout requires a matching capable hub with declared coverage at the seller and buyer ends. Missing address, measurements, service coverage or capable fleet blocks order placement and payment initiation.
- Buyer sees standard route shipping plus the origin bulky surcharge; a different destination hub adds that hub's surcharge. The server recalculates the quote at placement. Discounts/wallet use the resulting shipping total.
- Seller accepts, packs and schedules pickup/drop-off. Bulky shipments retain the capable origin hub priced at checkout.
- Pickup: logistics assigns a compatible vehicle; the driver accepts/scans and brings the parcel to the origin hub. Seller drop-off skips this leg.
- Hub transfer: if needed, dispatch requires a selected compatible courier vehicle and bulky-enabled hubs. Arrival transfers the parcel to the destination hub for sorting.
- Final delivery: logistics assigns a compatible online van/truck driver for a bulky parcel, or a fitting standard rider for a standard parcel. Drivers use **Bulky pickups & buyer deliveries** on the courier mobile dashboard. Existing scan, delivery confirmation, COD and failure/reschedule flows remain in use.
- Tracking labels bulky service; it continues showing actual recorded milestones. There is no new direct seller-to-buyer bypass of hubs.

Automatic/manual assignments, scan acceptance, hub transfer and delivery confirmation enforce eligibility. Capacity checks include measured parcels already reserved on the vehicle; an unmeasured active load cannot share capacity with a new measured shipment. No automatic cash payout or new earnings formula is added.

Existing shipments without a snapshot retain the previous delivery workflow. New orders always require a measured snapshot. Bulky returned items also require compatible return vehicles.

## Validation

`php vendor/phpunit/phpunit/phpunit tests/Feature/BulkyDeliveryTest.php` covers unavailable checkout, stock rollback, measurements, quantity/volume, coverage, fee consistency, immutable snapshots, assignments, pickup, hub transfer and buyer delivery.

# Rider auto-assignment

Last-mile delivery assignment runs automatically when a parcel is received at its logistics hub. First-mile pickup dispatch remains separate.

## Selection and availability

- A candidate must belong to the parcel's hub, be an approved Last Mile rider, have `is_active = true`, have an active/non-suspended courier account, and have `online_until > now()`.
- Rider time-in sets `online_until` to the end of the maximum 16-hour shift; time-out clears it. The availability API also lets a rider explicitly go online/offline. Online here means declared on-duty availability, not network connectivity. Expiry prevents an abandoned shift from remaining online indefinitely.
- Existing riders start offline after migration. They must time in or use the availability API. Account activation alone does not put them online.
- Match the recipient's structured `barangay` (or `brgy`), city and province against either the rider's registered home or `designated_place`. Legacy orders with only a formatted `address` use the existing place matcher. City/province context avoids treating identically named barangays in different cities as a primary match.
- Prefer a barangay match absolutely. Otherwise fall back to another eligible rider in the same hub, preferring the closest matching locality, then the lowest current shipment load, then rider ID for a stable tie break.
- If no rider qualifies, leave the parcel unassigned. Coming online/time-in retries the hub's waiting parcels; the existing bulk auto-assign action also retries them.
- Automatic requests preserve existing assignments. Manual overrides are permitted only after receipt and before the parcel leaves the hub. Cancelled, completed and delivered orders cannot be assigned.
- Shipment/rider row locks and transactional event creation protect assignment changes. Each change logs its method (`barangay`, `fallback`, `manual`), previous rider and new rider in `delivery_events`. Background automatic events are attributed to the hub owner; manual/API requests record the acting account.

## API

All endpoints require Sanctum authentication (`Authorization: Bearer <token>`) and `Accept: application/json`.

| Method | Endpoint | Body / result |
| --- | --- | --- |
| PUT | `/api/rider/availability` | Courier only: `{"status":"online"}` or `{"status":"offline"}`; returns status and expiry |
| GET | `/api/logistics/shipments/{id}/riders` | Returns available riders, `recommended_rider_id`, assigned rider, match type and active load |
| POST | `/api/logistics/shipments/{id}/auto-assign` | No body; returns `assigned` and shipment; false means not ready or nobody available |
| PUT | `/api/logistics/shipments/{id}/rider` | `{"rider_id":42}`; manually assigns/reassigns an eligible rider |
| PUT | `/api/logistics/riders/{id}/designation` | `{"barangay":"Bubukal","city":"Santa Cruz","province":"Laguna"}` |

Logistics endpoints accept active logistics or admin accounts. Logistics users can access only their own approved hub; administrators can access any hub. Overrides never allow cross-hub riders. Invalid/unavailable selections return 422; inaccessible resources return 404; invalid roles or disabled accounts return 403. A recommendation is a snapshot; availability is checked again at assignment time.

The Hub Desk's Assigned tab includes **Change rider**, using the same rules as the API. Existing rider apps already acquire online availability through attendance scans; an explicit online/offline toggle can call the availability endpoint.

## Database and deployment

Migration `2026_11_12_000001_add_rider_dispatch_availability.php` adds nullable `online_until`, JSON `designated_place`, and an index for candidate filtering. Candidate queries use the hub, courier type, approval, active flag and expiry plus a linked-account status check. No external queue or scheduler is required.

Run `php artisan migrate` locally. The repository's Docker startup command already runs `php artisan migrate --force` for Render deployments. Confirm the migration succeeds in deployment logs. No production migration is run by the implementation task.

Tests: `php vendor/phpunit/phpunit/phpunit --filter="DeliveryAssignmentTest|RiderAttendanceTest|HubAndRiderMatchingTest"`.

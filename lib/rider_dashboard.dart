import 'package:flutter/material.dart';

import 'cartzy_colors.dart';
import 'rider_pickup_process.dart';
import 'rider_deliver_process.dart';

// ============================================================
// RIDER ORDER MODEL
// ============================================================
//
// Shared by RiderDashboard, RiderPickupProcess and RiderDeliverProcess.

class RiderOrder {
  final String id;
  final String itemName;
  final String personName; // Seller (for pickup) or Buyer (for deliver)
  final String address;
  final String status; // 'pending_pickup' or 'pending_delivery'

  const RiderOrder({
    required this.id,
    required this.itemName,
    required this.personName,
    required this.address,
    required this.status,
  });
}

// ============================================================
// RIDER DASHBOARD
// ============================================================

class RiderDashboard extends StatefulWidget {
  final VoidCallback onLogout;

  const RiderDashboard({super.key, required this.onLogout});

  @override
  State<RiderDashboard> createState() => _RiderDashboardState();
}

class _RiderDashboardState extends State<RiderDashboard> {
  // TODO: replace with a real GET /api/riders/:id/orders call once the
  // rider dashboard is wired up to the backend. For now this shows sample
  // orders so pickup/deliver flows can be tested end-to-end.
  final List<RiderOrder> _orders = const [
    RiderOrder(
      id: '1',
      itemName: 'Wireless Earbuds',
      personName: 'Juan Dela Cruz (Seller)',
      address: '123 Rizal St., Brgy. San Isidro, Quezon City',
      status: 'pending_pickup',
    ),
    RiderOrder(
      id: '2',
      itemName: 'Phone Case Bundle',
      personName: 'Maria Santos (Buyer)',
      address: '45 Mabini Ave., Brgy. Poblacion, Makati City',
      status: 'pending_delivery',
    ),
  ];

  void _openPickup(RiderOrder order) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => RiderPickupProcess(order: order),
      ),
    );
  }

  void _openDeliver(RiderOrder order) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => RiderDeliverProcess(order: order),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        automaticallyImplyLeading: false,
        title: const Text(
          'Rider Dashboard',
          style: TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout, color: CartzyColors.navy),
            onPressed: widget.onLogout,
            tooltip: 'Logout',
          ),
        ],
      ),
      body: SafeArea(
        child: _orders.isEmpty
            ? const Center(
                child: Text(
                  'No orders assigned yet.',
                  style: TextStyle(color: CartzyColors.gray),
                ),
              )
            : ListView.separated(
                padding: const EdgeInsets.all(20),
                itemCount: _orders.length,
                separatorBuilder: (context, index) => const SizedBox(height: 14),
                itemBuilder: (context, index) {
                  final order = _orders[index];
                  final isPickup = order.status == 'pending_pickup';

                  return Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: CartzyColors.surface,
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.05),
                          blurRadius: 10,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Icon(
                              isPickup ? Icons.inventory_2_outlined : Icons.local_shipping_outlined,
                              color: CartzyColors.coral,
                              size: 20,
                            ),
                            const SizedBox(width: 8),
                            Text(
                              isPickup ? 'PICKUP' : 'DELIVER',
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: CartzyColors.coral,
                                letterSpacing: 0.6,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        Text(
                          order.itemName,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: CartzyColors.navy,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          order.personName,
                          style: const TextStyle(fontSize: 13, color: CartzyColors.gray),
                        ),
                        Text(
                          order.address,
                          style: const TextStyle(fontSize: 13, color: CartzyColors.gray),
                        ),
                        const SizedBox(height: 14),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: CartzyColors.navy,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 14),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(10),
                              ),
                            ),
                            onPressed: () =>
                                isPickup ? _openPickup(order) : _openDeliver(order),
                            child: Text(
                              isPickup ? 'Start Pickup' : 'Start Delivery',
                              style: const TextStyle(fontWeight: FontWeight.bold),
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
      ),
    );
  }
}
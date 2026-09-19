import 'package:flutter/material.dart';

import 'cartzy_colors.dart';
import 'rider_pickup_process.dart';
import 'rider_deliver_process.dart';

// ============================================================
// MODELS
// ============================================================

class PickupNotification {
  final String sellerName;
  final String itemName;
  final String address;
  const PickupNotification({required this.sellerName, required this.itemName, required this.address});
}

class DeliveryNotification {
  final String buyerName;
  final String itemName;
  final String address;
  const DeliveryNotification({required this.buyerName, required this.itemName, required this.address});
}

class RiderOrder {
  final String id;
  final String itemName;
  final String type; // 'Pickup' or 'Deliver'
  final String personName;
  final String address;
  const RiderOrder({
    required this.id,
    required this.itemName,
    required this.type,
    required this.personName,
    required this.address,
  });
}

class DeliveryHistoryEntry {
  final String itemName;
  final String date;
  final String earnings;
  const DeliveryHistoryEntry({required this.itemName, required this.date, required this.earnings});
}

class ChatConversation {
  final String name;
  final String lastMessage;
  final String time;
  const ChatConversation({required this.name, required this.lastMessage, required this.time});
}

// ============================================================
// MOCK DATA
// ============================================================

const _pickupNotifications = [
  PickupNotification(sellerName: 'Cartzy Store - Alex', itemName: 'Wireless Earbuds', address: 'Calamba, Laguna'),
  PickupNotification(sellerName: 'Cartzy Store - Mika', itemName: 'Backpack', address: 'Los Baños, Laguna'),
];

const _deliveryNotifications = [
  DeliveryNotification(buyerName: 'Juan Dela Cruz', itemName: 'Running Shoes', address: 'Santa Rosa, Laguna'),
];

const _riderOrders = [
  RiderOrder(id: 'ORD-1001', itemName: 'Wireless Earbuds', type: 'Pickup', personName: 'Cartzy Store - Alex', address: 'Calamba, Laguna'),
  RiderOrder(id: 'ORD-1002', itemName: 'Running Shoes', type: 'Deliver', personName: 'Juan Dela Cruz', address: 'Santa Rosa, Laguna'),
  RiderOrder(id: 'ORD-1003', itemName: 'Backpack', type: 'Pickup', personName: 'Cartzy Store - Mika', address: 'Los Baños, Laguna'),
];

const _deliveryHistory = [
  DeliveryHistoryEntry(itemName: 'Smart Watch', date: 'Sep 4, 2026', earnings: '\$4.50'),
  DeliveryHistoryEntry(itemName: 'Desk Lamp', date: 'Sep 3, 2026', earnings: '\$3.20'),
  DeliveryHistoryEntry(itemName: 'Sunglasses', date: 'Sep 2, 2026', earnings: '\$2.80'),
];

const _chatConversations = [
  ChatConversation(name: 'Cartzy Store - Alex', lastMessage: 'Item is ready for pickup', time: '10:32 AM'),
  ChatConversation(name: 'Juan Dela Cruz', lastMessage: 'Please call when you arrive', time: '9:15 AM'),
  ChatConversation(name: 'Cartzy Support', lastMessage: 'Your weekly earnings summary is ready', time: 'Yesterday'),
];

// ============================================================
// RIDER DASHBOARD SHELL
// ============================================================

class RiderDashboard extends StatefulWidget {
  final VoidCallback onLogout;

  const RiderDashboard({super.key, required this.onLogout});

  @override
  State<RiderDashboard> createState() => _RiderDashboardState();
}

class _RiderDashboardState extends State<RiderDashboard> {
  int _currentIndex = 0;

  @override
  Widget build(BuildContext context) {
    final tabs = [
      const _RiderHomeTab(),
      const _RiderOrdersTab(),
      const _RiderEarningsTab(),
      const _RiderChatTab(),
      _RiderProfileTab(onLogout: widget.onLogout),
    ];

    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(child: tabs[_currentIndex]),
      bottomNavigationBar: Container(
        color: CartzyColors.surface,
        padding: const EdgeInsets.symmetric(vertical: 10),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          children: [
            _NavItem(icon: Icons.home, label: 'Home', selected: _currentIndex == 0, onTap: () => setState(() => _currentIndex = 0)),
            _NavItem(icon: Icons.local_shipping, label: 'Orders', selected: _currentIndex == 1, onTap: () => setState(() => _currentIndex = 1)),
            _NavItem(icon: Icons.payments_outlined, label: 'Earnings', selected: _currentIndex == 2, onTap: () => setState(() => _currentIndex = 2)),
            _NavItem(icon: Icons.chat_bubble_outline, label: 'Chat', selected: _currentIndex == 3, onTap: () => setState(() => _currentIndex = 3)),
            _NavItem(icon: Icons.person_outline, label: 'Profile', selected: _currentIndex == 4, onTap: () => setState(() => _currentIndex = 4)),
          ],
        ),
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _NavItem({required this.icon, required this.label, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final color = selected ? CartzyColors.coral : CartzyColors.gray;
    return GestureDetector(
      onTap: onTap,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: color, size: 22),
          const SizedBox(height: 3),
          Text(label, style: TextStyle(fontSize: 10, color: color)),
        ],
      ),
    );
  }
}

// ============================================================
// HOME TAB — pickup notifications + delivery notifications
// ============================================================

class _RiderHomeTab extends StatelessWidget {
  const _RiderHomeTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        Row(
          children: [
            Image.asset('assets/cartzy_splash.png', height: 28, fit: BoxFit.contain),
            const SizedBox(width: 8),
            const Text(
              'Rider Dashboard',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: CartzyColors.navy),
            ),
          ],
        ),
        const SizedBox(height: 20),

        // ITEMS FOR PICKUP
        const Text(
          'Items for Pickup',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
        const SizedBox(height: 4),
        const Text(
          'Notifications from sellers',
          style: TextStyle(fontSize: 12, color: CartzyColors.gray),
        ),
        const SizedBox(height: 12),
        ..._pickupNotifications.map((n) => _NotificationCard(
              icon: Icons.store_outlined,
              title: n.sellerName,
              subtitle: '${n.itemName} • ${n.address}',
            )),

        const SizedBox(height: 26),

        // ITEMS FOR DELIVERY
        const Text(
          'Items for Delivery',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
        const SizedBox(height: 4),
        const Text(
          'Delivery notifications & available pickup requests',
          style: TextStyle(fontSize: 12, color: CartzyColors.gray),
        ),
        const SizedBox(height: 12),
        ..._deliveryNotifications.map((n) => _NotificationCard(
              icon: Icons.local_shipping_outlined,
              title: n.buyerName,
              subtitle: '${n.itemName} • ${n.address}',
            )),
      ],
    );
  }
}

class _NotificationCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;

  const _NotificationCard({required this.icon, required this.title, required this.subtitle});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 10, offset: const Offset(0, 3)),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(color: CartzyColors.background, borderRadius: BorderRadius.circular(10)),
            child: Icon(icon, color: CartzyColors.coral),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
                const SizedBox(height: 2),
                Text(subtitle, style: const TextStyle(fontSize: 12, color: CartzyColors.gray)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ============================================================
// ORDERS TAB — pickup/deliver action list
// ============================================================

class _RiderOrdersTab extends StatelessWidget {
  const _RiderOrdersTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        const Text(
          'Active Orders',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
        const SizedBox(height: 4),
        const Text(
          'Tap an order to start the pickup or delivery process.',
          style: TextStyle(fontSize: 12, color: CartzyColors.gray),
        ),
        const SizedBox(height: 16),
        ..._riderOrders.map((order) => _OrderActionCard(order: order)),
      ],
    );
  }
}

class _OrderActionCard extends StatelessWidget {
  final RiderOrder order;

  const _OrderActionCard({required this.order});

  @override
  Widget build(BuildContext context) {
    final isPickup = order.type == 'Pickup';

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 12, offset: const Offset(0, 4)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: (isPickup ? CartzyColors.gold : CartzyColors.coral).withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  order.type,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: isPickup ? CartzyColors.gold : CartzyColors.coral,
                  ),
                ),
              ),
              const Spacer(),
              Text(order.id, style: const TextStyle(fontSize: 11, color: CartzyColors.gray)),
            ],
          ),
          const SizedBox(height: 10),
          Text(order.itemName, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
          const SizedBox(height: 4),
          Text('${order.personName} • ${order.address}', style: const TextStyle(fontSize: 12, color: CartzyColors.gray)),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 42,
            child: ElevatedButton(
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => isPickup
                        ? RiderPickupProcess(order: order)
                        : RiderDeliverProcess(order: order),
                  ),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: CartzyColors.navy,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              child: Text(
                isPickup ? 'Start Pickup' : 'Start Delivery',
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ============================================================
// EARNINGS TAB — profit dashboard + delivery history
// ============================================================

class _RiderEarningsTab extends StatelessWidget {
  const _RiderEarningsTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        const Text(
          'Earnings',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
        const SizedBox(height: 16),

        // PROFIT SUMMARY CARDS
        const Row(
          children: [
            Expanded(
              child: _EarningsSummaryCard(label: 'Today', value: '\$12.40'),
            ),
            SizedBox(width: 12),
            Expanded(
              child: _EarningsSummaryCard(label: 'This Week', value: '\$86.20'),
            ),
          ],
        ),
        const SizedBox(height: 12),
        const Row(
          children: [
            Expanded(
              child: _EarningsSummaryCard(label: 'This Month', value: '\$342.75'),
            ),
            SizedBox(width: 12),
            Expanded(
              child: _EarningsSummaryCard(label: 'Deliveries', value: '58'),
            ),
          ],
        ),

        const SizedBox(height: 28),

        const Text(
          'Delivery History',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
        const SizedBox(height: 12),
        ..._deliveryHistory.map((entry) => _HistoryCard(entry: entry)),
      ],
    );
  }
}

class _EarningsSummaryCard extends StatelessWidget {
  final String label;
  final String value;

  const _EarningsSummaryCard({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 3)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: CartzyColors.gray)),
          const SizedBox(height: 6),
          Text(value, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: CartzyColors.coral)),
        ],
      ),
    );
  }
}

class _HistoryCard extends StatelessWidget {
  final DeliveryHistoryEntry entry;

  const _HistoryCard({required this.entry});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 8, offset: const Offset(0, 2)),
        ],
      ),
      child: Row(
        children: [
          const Icon(Icons.check_circle, color: CartzyColors.coral, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(entry.itemName, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: CartzyColors.navy)),
                Text(entry.date, style: const TextStyle(fontSize: 11, color: CartzyColors.gray)),
              ],
            ),
          ),
          Text(entry.earnings, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
        ],
      ),
    );
  }
}

// ============================================================
// CHAT TAB
// ============================================================

class _RiderChatTab extends StatelessWidget {
  const _RiderChatTab();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        const Text(
          'Messages',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
        const SizedBox(height: 16),
        ..._chatConversations.map((chat) => Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: CartzyColors.surface,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 8, offset: const Offset(0, 2)),
                ],
              ),
              child: Row(
                children: [
                  const CircleAvatar(
                    radius: 20,
                    backgroundColor: CartzyColors.background,
                    child: Icon(Icons.person, color: CartzyColors.coral),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(chat.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
                        const SizedBox(height: 2),
                        Text(chat.lastMessage, style: const TextStyle(fontSize: 12, color: CartzyColors.gray), maxLines: 1, overflow: TextOverflow.ellipsis),
                      ],
                    ),
                  ),
                  Text(chat.time, style: const TextStyle(fontSize: 11, color: CartzyColors.gray)),
                ],
              ),
            )),
      ],
    );
  }
}

// ============================================================
// PROFILE TAB — account management + logout
// ============================================================

class _RiderProfileTab extends StatelessWidget {
  final VoidCallback onLogout;

  const _RiderProfileTab({required this.onLogout});

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        const Row(
          children: [
            CircleAvatar(
              radius: 34,
              backgroundColor: CartzyColors.background,
              child: Icon(Icons.person, size: 38, color: CartzyColors.coral),
            ),
            SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Rider Account', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
                  SizedBox(height: 4),
                  Text('rider@cartzy.com', style: TextStyle(fontSize: 13, color: CartzyColors.gray)),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 24),

        _ProfileMenuItem(icon: Icons.badge_outlined, label: 'Account Information', onTap: () {}),
        _ProfileMenuItem(icon: Icons.two_wheeler_outlined, label: 'Vehicle & Documents', onTap: () {}),
        _ProfileMenuItem(icon: Icons.notifications_outlined, label: 'Notifications', onTap: () {}),
        _ProfileMenuItem(icon: Icons.help_outline, label: 'Help & Support', onTap: () {}),

        const SizedBox(height: 20),

        SizedBox(
          width: double.infinity,
          height: 48,
          child: OutlinedButton.icon(
            onPressed: onLogout,
            style: OutlinedButton.styleFrom(
              foregroundColor: CartzyColors.error,
              side: const BorderSide(color: CartzyColors.error),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            icon: const Icon(Icons.logout),
            label: const Text('Logout', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ),
      ],
    );
  }
}

class _ProfileMenuItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _ProfileMenuItem({required this.icon, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        decoration: BoxDecoration(
          color: CartzyColors.surface,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 8, offset: const Offset(0, 2)),
          ],
        ),
        child: Row(
          children: [
            Icon(icon, color: CartzyColors.coral, size: 20),
            const SizedBox(width: 12),
            Expanded(child: Text(label, style: const TextStyle(fontSize: 14, color: CartzyColors.navy))),
            const Icon(Icons.chevron_right, color: CartzyColors.gray, size: 20),
          ],
        ),
      ),
    );
  }
}
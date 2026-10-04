import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'rider_pickup_dashboard.dart';
import 'rider_delivery_dashboard.dart';
import 'rider_delivery_history_screen.dart';
import 'rider_earnings_dashboard.dart';
import 'rider_chat_list_screen.dart';
import 'rider_account_screen.dart';

// ============================================================
// RIDER DASHBOARD — hub menu
// ============================================================

class RiderDashboard extends StatelessWidget {
  final String token;
  final int userId;
  final String userName;
  final VoidCallback onLogout;

  const RiderDashboard({
    super.key,
    required this.token,
    required this.userId,
    required this.userName,
    required this.onLogout,
  });

  void _push(BuildContext context, Widget screen) {
    Navigator.of(context).push(MaterialPageRoute(builder: (context) => screen));
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
            onPressed: onLogout,
            tooltip: 'Logout',
          ),
        ],
      ),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: CartzyColors.navy,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                children: [
                  const CircleAvatar(
                    radius: 24,
                    backgroundColor: Colors.white24,
                    child: Icon(Icons.two_wheeler, color: Colors.white),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          userName,
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                        const SizedBox(height: 2),
                        const Text('RIDER', style: TextStyle(color: Colors.white70, fontSize: 12)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            _MenuTile(
              icon: Icons.inventory_2_outlined,
              title: 'Items for Pickup',
              subtitle: 'Available pickup requests from sellers',
              onTap: () => _push(context, RiderPickupDashboard(token: token, onBack: () => Navigator.of(context).pop())),
            ),
            _MenuTile(
              icon: Icons.local_shipping_outlined,
              title: 'Items for Delivery',
              subtitle: 'Orders you picked up, ready to deliver',
              onTap: () => _push(context, RiderDeliveryDashboard(token: token, onBack: () => Navigator.of(context).pop())),
            ),
            _MenuTile(
              icon: Icons.history,
              title: 'Delivery History',
              subtitle: 'Everything you\'ve delivered so far',
              onTap: () => _push(context, RiderDeliveryHistoryScreen(token: token, onBack: () => Navigator.of(context).pop())),
            ),
            _MenuTile(
              icon: Icons.payments_outlined,
              title: 'Earnings',
              subtitle: 'Your delivery profit dashboard',
              onTap: () => _push(context, RiderEarningsScreen(token: token, onBack: () => Navigator.of(context).pop())),
            ),
            _MenuTile(
              icon: Icons.chat_bubble_outline,
              title: 'Messages',
              subtitle: 'Chat with buyers and logistics',
              onTap: () => _push(
                context,
                RiderChatListScreen(token: token, myUserId: userId, onBack: () => Navigator.of(context).pop()),
              ),
            ),
            _MenuTile(
              icon: Icons.person_outline,
              title: 'Account',
              subtitle: 'Manage your profile details',
              onTap: () => _push(context, RiderAccountScreen(userId: userId, onBack: () => Navigator.of(context).pop())),
            ),
          ],
        ),
      ),
    );
  }
}

class _MenuTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  const _MenuTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      child: Material(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: onTap,
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: CartzyColors.border),
            ),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: CartzyColors.coral.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: CartzyColors.coral),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
                      const SizedBox(height: 2),
                      Text(subtitle, style: const TextStyle(fontSize: 12, color: CartzyColors.gray)),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right, color: CartzyColors.gray),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
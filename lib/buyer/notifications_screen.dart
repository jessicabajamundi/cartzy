import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

// ============================================================
// NOTIFICATIONS SCREEN
// ============================================================

class NotificationsScreen extends StatefulWidget {
  final VoidCallback onBack;

  const NotificationsScreen({super.key, required this.onBack});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  int _activeTab = 0; // 0=All 1=Orders 2=Promos 3=System
  final List<String> _tabs = ['All', 'Orders', 'Promos', 'System'];

  // Sample notifications — empty by default to show empty state
  final List<Map<String, dynamic>> _notifications = [];

  List<Map<String, dynamic>> get _filtered {
    if (_activeTab == 0) return _notifications;
    final tag = _tabs[_activeTab].toLowerCase();
    return _notifications.where((n) => n['tag'] == tag).toList();
  }

  int get _unreadCount =>
      _notifications.where((n) => n['read'] != true).length;

  void _markAllRead() {
    setState(() {
      for (final n in _notifications) {
        n['read'] = true;
      }
    });
  }

  void _markRead(Map<String, dynamic> notif) {
    setState(() => notif['read'] = true);
  }

  // ── Notif card ──────────────────────────────────────────────
  Widget _notifCard(Map<String, dynamic> n) {
    final bool isRead = n['read'] == true;
    final IconData icon = _iconFor(n['tag']);
    final Color iconBg = _colorFor(n['tag']);

    return GestureDetector(
      onTap: () => _markRead(n),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: isRead ? Colors.white : const Color(0xFFFBF6F8),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isRead ? CartzyColors.border : CartzyColors.rose,
            width: isRead ? 1 : 1.2,
          ),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: iconBg,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, size: 20, color: CartzyColors.navy),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          n['title'] ?? '',
                          style: GoogleFonts.dmSans(
                            fontSize: 13.5,
                            fontWeight: isRead
                                ? FontWeight.w500
                                : FontWeight.w700,
                            color: CartzyColors.text,
                          ),
                        ),
                      ),
                      if (!isRead)
                        Container(
                          width: 8,
                          height: 8,
                          decoration: const BoxDecoration(
                            color: CartzyColors.burgundy,
                            shape: BoxShape.circle,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 3),
                  Text(
                    n['body'] ?? '',
                    style: GoogleFonts.dmSans(
                      fontSize: 12.5,
                      height: 1.45,
                      color: CartzyColors.gray,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    n['time'] ?? '',
                    style: GoogleFonts.dmSans(
                      fontSize: 11,
                      color: CartzyColors.gray,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  IconData _iconFor(String? tag) {
    switch (tag) {
      case 'orders':  return Icons.receipt_long_outlined;
      case 'promos':  return Icons.local_offer_outlined;
      case 'system':  return Icons.info_outline;
      default:        return Icons.notifications_outlined;
    }
  }

  Color _colorFor(String? tag) {
    switch (tag) {
      case 'orders':  return const Color(0xFFEEEBF6);
      case 'promos':  return const Color(0xFFFFF0E8);
      case 'system':  return const Color(0xFFE8F0FF);
      default:        return CartzyColors.background;
    }
  }

  // ── Empty state ─────────────────────────────────────────────
  Widget _emptyState() {
    return SingleChildScrollView(
      child: Center(
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          padding: const EdgeInsets.symmetric(vertical: 52, horizontal: 32),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: CartzyColors.border),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.03),
                blurRadius: 16,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Stack(
                clipBehavior: Clip.none,
                children: [
                  Container(
                    width: 78,
                    height: 78,
                    decoration: BoxDecoration(
                      color: const Color(0xFFEEEBF6),
                      borderRadius: BorderRadius.circular(22),
                    ),
                    child: const Icon(
                      Icons.notifications_none_rounded,
                      size: 38,
                      color: Color(0xFF7C6B8E),
                    ),
                  ),
                  Positioned(
                    right: -6,
                    bottom: -6,
                    child: Container(
                      width: 22,
                      height: 22,
                      decoration: const BoxDecoration(
                        color: Color(0xFFE07B39),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.add, size: 14, color: Colors.white),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 28),
              Text(
                'YOUR UPDATES',
                style: GoogleFonts.dmSans(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 1.4,
                  color: const Color(0xFF9C7B8A),
                ),
              ),
              const SizedBox(height: 10),
              Text(
                'No notifications yet',
                style: GoogleFonts.dmSans(
                  fontSize: 22,
                  fontWeight: FontWeight.w700,
                  color: CartzyColors.text,
                  height: 1.2,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 10),
              Text(
                'Order status alerts, flash sales, price\ndrops and promotions will appear here.',
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  height: 1.55,
                  color: CartzyColors.gray,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
              Text(
                'You\'ll be notified as soon as there\'s an update.',
                style: GoogleFonts.dmSans(
                  fontSize: 11.5,
                  color: CartzyColors.gray,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── Build ───────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final items = _filtered;

    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Header ───────────────────────────────────────
            Container(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 18),
              decoration: BoxDecoration(
                color: CartzyColors.background,
                border: Border(bottom: BorderSide(color: CartzyColors.border)),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  GestureDetector(
                    onTap: widget.onBack,
                    child: Container(
                      margin: const EdgeInsets.only(top: 2, right: 14),
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: CartzyColors.border),
                      ),
                      child: const Icon(Icons.arrow_back,
                          size: 18, color: CartzyColors.navy),
                    ),
                  ),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'YOUR UPDATES',
                          style: GoogleFonts.dmSans(
                            fontSize: 10.5,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 1.4,
                            color: const Color(0xFF9C7B8A),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Notifications',
                          style: GoogleFonts.dmSans(
                            fontSize: 26,
                            fontWeight: FontWeight.w800,
                            color: CartzyColors.text,
                            height: 1.1,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Order status alerts, sales and promotions.',
                          style: GoogleFonts.dmSans(
                            fontSize: 12.5,
                            color: CartzyColors.gray,
                            height: 1.4,
                          ),
                        ),
                      ],
                    ),
                  ),
                  // Chips
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      // Unread badge chip
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 13, vertical: 7),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: CartzyColors.border),
                        ),
                        child: Text(
                          '$_unreadCount unread',
                          style: GoogleFonts.dmSans(
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                            color: CartzyColors.text,
                          ),
                        ),
                      ),
                      if (_unreadCount > 0) ...[
                        const SizedBox(height: 6),
                        GestureDetector(
                          onTap: _markAllRead,
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 13, vertical: 7),
                            decoration: BoxDecoration(
                              color: CartzyColors.navy,
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              'Mark all read',
                              style: GoogleFonts.dmSans(
                                fontSize: 12,
                                fontWeight: FontWeight.w500,
                                color: Colors.white,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ],
              ),
            ),

            // ── Tab Filter ───────────────────────────────────
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
              child: Row(
                children: List.generate(_tabs.length, (i) {
                  final active = _activeTab == i;
                  return GestureDetector(
                    onTap: () => setState(() => _activeTab = i),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 150),
                      margin: const EdgeInsets.only(right: 8),
                      padding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 8),
                      decoration: BoxDecoration(
                        color: active ? CartzyColors.navy : Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                          color: active
                              ? CartzyColors.navy
                              : CartzyColors.border,
                        ),
                      ),
                      child: Text(
                        _tabs[i],
                        style: GoogleFonts.dmSans(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: active ? Colors.white : CartzyColors.text,
                        ),
                      ),
                    ),
                  );
                }),
              ),
            ),

            const SizedBox(height: 12),

            // ── Content ──────────────────────────────────────
            Expanded(
              child: items.isEmpty
                  ? _emptyState()
                  : ListView.builder(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                      itemCount: items.length,
                      itemBuilder: (context, i) => _notifCard(items[i]),
                    ),
            ),
          ],
        ),
      ),
    );
  }
}

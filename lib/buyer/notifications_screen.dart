import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/notification_service.dart';
import 'package:cartzy/buyer/my_orders_screen.dart';
import 'package:cartzy/buyer/vouchers_screen.dart';

// ============================================================
// NOTIFICATIONS SCREEN (Connected to Cartzy Database API)
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

  bool _isLoading = true;
  String? _error;
  List<AppNotificationItem> _notifications = [];
  Map<String, int> _counts = {'all': 0, 'orders': 0, 'promos': 0, 'system': 0};
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _fetch();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetch({bool silent = false}) async {
    if (!silent) {
      setState(() {
        _isLoading = true;
        _error = null;
      });
    }

    try {
      final data = await NotificationService.fetchNotifications();
      if (!mounted) return;
      setState(() {
        _notifications = data.notifications;
        _counts = data.counts;
        _isLoading = false;
        _error = null;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _error = 'Unable to load notifications. Please check your connection.';
      });
    }
  }

  List<AppNotificationItem> get _filtered {
    List<AppNotificationItem> list = _notifications;
    if (_activeTab != 0) {
      final tag = _tabs[_activeTab].toLowerCase();
      list = list.where((n) => n.tag == tag).toList();
    }
    if (_searchQuery.trim().isNotEmpty) {
      final q = _searchQuery.trim().toLowerCase();
      list = list.where((n) {
        final title = n.title.toLowerCase();
        final msg = n.message.toLowerCase();
        final ref = (n.orderReference ?? '').toLowerCase();
        final type = n.type.toLowerCase();
        return title.contains(q) || msg.contains(q) || ref.contains(q) || type.contains(q);
      }).toList();
    }
    return list;
  }

  int get _unreadCount => _notifications.where((n) => !n.isRead).length;

  Future<void> _markAllRead() async {
    setState(() {
      _notifications = _notifications.map((n) => n.copyWith(isRead: true)).toList();
    });
    await NotificationService.markAllAsRead();
  }

  Future<void> _handleTap(AppNotificationItem n) async {
    if (!n.isRead) {
      setState(() {
        final index = _notifications.indexWhere((item) => item.id == n.id);
        if (index != -1) {
          _notifications[index] = _notifications[index].copyWith(isRead: true);
        }
      });
      NotificationService.markAsRead(n.id);
    }

    // Direct routing if notification references an order
    if (n.orderReference != null && n.orderReference!.isNotEmpty) {
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => MyOrdersScreen(
            onBack: () => Navigator.of(context).pop(),
            initialSearch: n.orderReference,
          ),
        ),
      );
      return;
    }

    // Route if promo / voucher notification
    if (n.tag == 'promos' || n.type == 'voucher') {
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => VouchersScreen(
            onBack: () => Navigator.of(context).pop(),
          ),
        ),
      );
    }
  }

  Future<void> _deleteNotif(AppNotificationItem n) async {
    final deletedId = n.id;
    setState(() {
      _notifications.removeWhere((item) => item.id == deletedId);
      _updateCounts();
    });

    await NotificationService.deleteNotification(deletedId);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Notification removed', style: GoogleFonts.dmSans()),
          duration: const Duration(seconds: 2),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  void _updateCounts() {
    _counts = {
      'all': _notifications.length,
      'orders': _notifications.where((n) => n.tag == 'orders').length,
      'promos': _notifications.where((n) => n.tag == 'promos').length,
      'system': _notifications.where((n) => n.tag == 'system').length,
    };
  }

  // ── Icon helper ─────────────────────────────────────────────
  IconData _iconFor(String? type, String? tag) {
    final t = (type ?? '').toLowerCase();
    if (t.contains('deliver') || t == 'order_delivered') {
      return Icons.check_circle_outline_rounded;
    }
    if (t.contains('ship') || t == 'pickup_accepted') {
      return Icons.local_shipping_outlined;
    }
    if (t.contains('cancel')) {
      return Icons.assignment_return_outlined;
    }
    if (t.contains('payment')) {
      return Icons.account_balance_wallet_outlined;
    }
    if (t.contains('voucher') || tag == 'promos') {
      return Icons.confirmation_number_outlined;
    }
    if (tag == 'system' || t.contains('admin')) {
      return Icons.notifications_active_outlined;
    }
    return Icons.receipt_long_outlined;
  }

  // ── Icon background color helper ─────────────────────────────
  Color _colorFor(String? type, String? tag) {
    final t = (type ?? '').toLowerCase();
    if (t.contains('deliver') || t == 'order_delivered') {
      return const Color(0xFFE8F5E9); // Light green
    }
    if (t.contains('ship') || t == 'pickup_accepted') {
      return const Color(0xFFE3F2FD); // Light blue
    }
    if (t.contains('cancel')) {
      return const Color(0xFFFFEBEE); // Light red/rose
    }
    if (t.contains('payment')) {
      return const Color(0xFFFFF8E1); // Light amber
    }
    if (t.contains('voucher') || tag == 'promos') {
      return const Color(0xFFFFF3E0); // Light orange
    }
    if (tag == 'system' || t.contains('admin')) {
      return const Color(0xFFEDE7F6); // Light purple
    }
    return const Color(0xFFEEEBF6); // Brand lavender tint
  }

  // ── Icon tint color helper ───────────────────────────────────
  Color _iconTintFor(String? type, String? tag) {
    final t = (type ?? '').toLowerCase();
    if (t.contains('deliver') || t == 'order_delivered') {
      return const Color(0xFF2E7D32);
    }
    if (t.contains('ship') || t == 'pickup_accepted') {
      return const Color(0xFF1976D2);
    }
    if (t.contains('cancel')) {
      return const Color(0xFFC62828);
    }
    if (t.contains('payment')) {
      return const Color(0xFFF57F17);
    }
    if (t.contains('voucher') || tag == 'promos') {
      return const Color(0xFFE65100);
    }
    if (tag == 'system' || t.contains('admin')) {
      return const Color(0xFF5E35B1);
    }
    return CartzyColors.brandDark;
  }

  // ── Notification Card ─────────────────────────────────────────
  Widget _notifCard(AppNotificationItem n) {
    final bool isRead = n.isRead;
    final IconData icon = _iconFor(n.type, n.tag);
    final Color iconBg = _colorFor(n.type, n.tag);
    final Color iconColor = _iconTintFor(n.type, n.tag);

    return Dismissible(
      key: Key('notif-${n.id}'),
      direction: DismissDirection.endToStart,
      background: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.only(right: 20),
        decoration: BoxDecoration(
          color: const Color(0xFFDC2626),
          borderRadius: BorderRadius.circular(16),
        ),
        alignment: Alignment.centerRight,
        child: const Icon(Icons.delete_outline_rounded, color: Colors.white, size: 24),
      ),
      onDismissed: (_) => _deleteNotif(n),
      child: GestureDetector(
        onTap: () => _handleTap(n),
        child: Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: isRead ? Colors.white : const Color(0xFFFDF8F9),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: isRead ? CartzyColors.border : const Color(0xFFE8D4D8),
              width: isRead ? 1 : 1.3,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: isRead ? 0.02 : 0.04),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Icon container
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: iconBg,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(icon, size: 22, color: iconColor),
              ),
              const SizedBox(width: 14),
              // Text Content
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            n.title,
                            style: GoogleFonts.dmSans(
                              fontSize: 14,
                              fontWeight: isRead ? FontWeight.w600 : FontWeight.w800,
                              color: isRead ? CartzyColors.text : CartzyColors.brand900,
                            ),
                          ),
                        ),
                        if (!isRead)
                          Container(
                            width: 8,
                            height: 8,
                            margin: const EdgeInsets.only(left: 6),
                            decoration: const BoxDecoration(
                              color: CartzyColors.burgundy,
                              shape: BoxShape.circle,
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 5),
                    Text(
                      n.message,
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        height: 1.45,
                        color: isRead ? CartzyColors.gray : const Color(0xFF4A3E4E),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          n.time,
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            fontWeight: FontWeight.w500,
                            color: CartzyColors.gray,
                          ),
                        ),
                        if (n.orderReference != null)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
                            decoration: BoxDecoration(
                              color: CartzyColors.brand50Lavender,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: const Color(0xFFD6CEE0)),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  n.orderReference!,
                                  style: GoogleFonts.dmSans(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w700,
                                    color: CartzyColors.brandDark,
                                  ),
                                ),
                                const SizedBox(width: 3),
                                const Icon(
                                  Icons.chevron_right_rounded,
                                  size: 13,
                                  color: CartzyColors.brandDark,
                                ),
                              ],
                            ),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── Empty State ──────────────────────────────────────────────
  Widget _emptyState() {
    if (_searchQuery.trim().isNotEmpty) {
      return LayoutBuilder(
        builder: (context, constraints) => SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: ConstrainedBox(
            constraints: BoxConstraints(minHeight: constraints.maxHeight),
            child: Center(
              child: Padding(
                padding: const EdgeInsets.all(28),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      decoration: const BoxDecoration(
                        color: Color(0xFFF3F1F4),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.search_off_rounded,
                        size: 32,
                        color: CartzyColors.grayMid,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'No matching notifications',
                      style: GoogleFonts.dmSans(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: CartzyColors.brand900,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'No alerts found matching "$_searchQuery".',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        color: CartzyColors.gray,
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextButton.icon(
                      onPressed: () {
                        _searchController.clear();
                        setState(() => _searchQuery = '');
                      },
                      icon: const Icon(Icons.close_rounded, size: 16),
                      label: const Text('Clear search'),
                      style: TextButton.styleFrom(
                        foregroundColor: CartzyColors.brandDark,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      );
    }
    final String activeLabel = _tabs[_activeTab];
    final String title = activeLabel == 'All'
        ? 'No notifications yet'
        : 'No $activeLabel notifications yet';
    final String subtitle = activeLabel == 'Orders'
        ? 'Your order tracking, delivery updates, and status changes will appear here.'
        : activeLabel == 'Promos'
            ? 'Exclusive discounts, store vouchers, and seasonal sales will be announced here.'
            : activeLabel == 'System'
                ? 'Account security notices and platform updates will appear here.'
                : 'Order status alerts, flash sales, price drops and promotions will appear here.';

    return LayoutBuilder(
      builder: (context, constraints) => SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: ConstrainedBox(
          constraints: BoxConstraints(minHeight: constraints.maxHeight),
          child: Center(
            child: Container(
              margin: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
              padding: const EdgeInsets.symmetric(vertical: 48, horizontal: 28),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: CartzyColors.border),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.03),
                    blurRadius: 18,
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
                        width: 80,
                        height: 80,
                        decoration: BoxDecoration(
                          color: const Color(0xFFEEEBF6),
                          borderRadius: BorderRadius.circular(24),
                        ),
                        child: const Icon(
                          Icons.notifications_none_rounded,
                          size: 40,
                          color: Color(0xFF7C6B8E),
                        ),
                      ),
                      Positioned(
                        right: -4,
                        bottom: -4,
                        child: Container(
                          width: 24,
                          height: 24,
                          decoration: const BoxDecoration(
                            color: Color(0xFFE07B39),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.check, size: 14, color: Colors.white),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  Text(
                    'YOUR CARTZY UPDATES',
                    style: GoogleFonts.dmSans(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 1.6,
                      color: CartzyColors.rose700,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    title,
                    style: GoogleFonts.playfairDisplay(
                      fontSize: 22,
                      fontWeight: FontWeight.w700,
                      color: CartzyColors.brand900,
                      height: 1.2,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 10),
                  Text(
                    subtitle,
                    style: GoogleFonts.dmSans(
                      fontSize: 13,
                      height: 1.55,
                      color: CartzyColors.gray,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 20),
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: CartzyColors.brand50Lavender,
                      foregroundColor: CartzyColors.brandDark,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 11),
                      shape: const StadiumBorder(),
                    ),
                    onPressed: () => _fetch(),
                    icon: const Icon(Icons.refresh_rounded, size: 16),
                    label: Text(
                      'Refresh Notifications',
                      style: GoogleFonts.dmSans(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  // ── Error State ──────────────────────────────────────────────
  Widget _errorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(28.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: const BoxDecoration(
                color: Color(0xFFFFEBEE),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.cloud_off_rounded, size: 30, color: Color(0xFFC62828)),
            ),
            const SizedBox(height: 16),
            Text(
              'Couldn\'t load notifications',
              style: GoogleFonts.dmSans(
                fontSize: 16,
                fontWeight: FontWeight.w700,
                color: CartzyColors.text,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _error ?? 'An unexpected error occurred.',
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(fontSize: 13, color: CartzyColors.gray),
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: () => _fetch(),
              icon: const Icon(Icons.refresh, size: 16),
              label: const Text('Try Again'),
              style: ElevatedButton.styleFrom(
                backgroundColor: CartzyColors.brandDark,
                foregroundColor: Colors.white,
                shape: const StadiumBorder(),
                padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 12),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Build ────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final items = _filtered;
    final unread = _unreadCount;

    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Top Header ──────────────────────────────────────
            Container(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 14),
              decoration: const BoxDecoration(
                color: CartzyColors.background,
                border: Border(bottom: BorderSide(color: CartzyColors.border)),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  GestureDetector(
                    onTap: widget.onBack,
                    child: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: CartzyColors.border),
                      ),
                      child: const Icon(Icons.arrow_back,
                          size: 18, color: CartzyColors.navy),
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'YOUR UPDATES',
                          style: GoogleFonts.dmSans(
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 1.4,
                            color: CartzyColors.rose700,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Notifications',
                          style: GoogleFonts.playfairDisplay(
                            fontSize: 22,
                            fontWeight: FontWeight.w700,
                            color: CartzyColors.brand900,
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (unread > 0)
                    GestureDetector(
                      onTap: _markAllRead,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 7),
                        decoration: BoxDecoration(
                          color: CartzyColors.brandDark,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.done_all_rounded,
                                size: 14, color: Colors.white),
                            const SizedBox(width: 4),
                            Text(
                              'Mark all read',
                              style: GoogleFonts.dmSans(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: Colors.white,
                              ),
                            ),
                          ],
                        ),
                      ),
                    )
                  else
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: CartzyColors.border),
                      ),
                      child: Text(
                        'All caught up',
                        style: GoogleFonts.dmSans(
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                          color: CartzyColors.gray,
                        ),
                      ),
                    ),
                ],
              ),
            ),

            // ── Search Bar (Single clean border) ─────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 10, 16, 2),
              child: Container(
                height: 40,
                decoration: BoxDecoration(
                  color: const Color(0xFFF3F1F4),
                  borderRadius: BorderRadius.circular(22),
                  border: Border.all(
                    color: const Color(0xFFE5E0E8),
                    width: 1.2,
                  ),
                ),
                child: Row(
                  children: [
                    const SizedBox(width: 12),
                    const Icon(
                      Icons.search_rounded,
                      color: CartzyColors.brandDeep,
                      size: 18,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: _searchController,
                        onChanged: (val) => setState(() => _searchQuery = val),
                        style: GoogleFonts.dmSans(
                          fontSize: 13,
                          fontWeight: FontWeight.w500,
                          color: CartzyColors.navy,
                        ),
                        decoration: InputDecoration(
                          isDense: true,
                          filled: false,
                          fillColor: Colors.transparent,
                          contentPadding: const EdgeInsets.symmetric(vertical: 9),
                          hintText: 'Search notifications by order, title...',
                          hintStyle: GoogleFonts.dmSans(
                            fontSize: 12.5,
                            color: CartzyColors.grayMid,
                          ),
                          border: InputBorder.none,
                          enabledBorder: InputBorder.none,
                          focusedBorder: InputBorder.none,
                          errorBorder: InputBorder.none,
                          disabledBorder: InputBorder.none,
                        ),
                      ),
                    ),
                    if (_searchQuery.isNotEmpty)
                      IconButton(
                        icon: const Icon(
                          Icons.close_rounded,
                          color: CartzyColors.grayMid,
                          size: 16,
                        ),
                        onPressed: () {
                          _searchController.clear();
                          setState(() => _searchQuery = '');
                        },
                        splashRadius: 16,
                      )
                    else
                      const SizedBox(width: 8),
                  ],
                ),
              ),
            ),

            // ── Tab Filters with live database counts ─────────────
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: Row(
                children: List.generate(_tabs.length, (i) {
                  final active = _activeTab == i;
                  final key = _tabs[i].toLowerCase();
                  final count = _counts[key] ?? 0;
                  final label = count > 0 ? '${_tabs[i]} ($count)' : _tabs[i];

                  return GestureDetector(
                    onTap: () => setState(() => _activeTab = i),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 150),
                      margin: const EdgeInsets.only(right: 8),
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 7),
                      decoration: BoxDecoration(
                        color: active ? CartzyColors.brandDark : Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(
                          color: active
                              ? CartzyColors.brandDark
                              : CartzyColors.border,
                        ),
                        boxShadow: active
                            ? [
                                BoxShadow(
                                  color: CartzyColors.brandDark
                                      .withValues(alpha: 0.15),
                                  blurRadius: 6,
                                  offset: const Offset(0, 2),
                                )
                              ]
                            : null,
                      ),
                      child: Text(
                        label,
                        style: GoogleFonts.dmSans(
                          fontSize: 12.5,
                          fontWeight:
                              active ? FontWeight.w700 : FontWeight.w600,
                          color: active ? Colors.white : CartzyColors.text,
                        ),
                      ),
                    ),
                  );
                }),
              ),
            ),

            const SizedBox(height: 4),

            // ── Main Content Area ──────────────────────────────
            Expanded(
              child: _isLoading
                  ? const Center(
                      child: CircularProgressIndicator(
                        color: CartzyColors.brandDark,
                        strokeWidth: 2.5,
                      ),
                    )
                  : _error != null
                      ? _errorState()
                      : items.isEmpty
                          ? _emptyState()
                          : RefreshIndicator(
                              color: CartzyColors.brandDark,
                              backgroundColor: Colors.white,
                              onRefresh: () => _fetch(silent: true),
                              child: ListView.builder(
                                physics: const AlwaysScrollableScrollPhysics(),
                                padding:
                                    const EdgeInsets.fromLTRB(16, 4, 16, 24),
                                itemCount: items.length,
                                itemBuilder: (context, i) =>
                                    _notifCard(items[i]),
                              ),
                            ),
            ),
          ],
        ),
      ),
    );
  }
}

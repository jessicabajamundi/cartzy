import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/buyer/order_tracking_screen.dart';
import 'package:cartzy/buyer/report_problem_screen.dart';
import 'package:cartzy/services/api_service.dart' show ApiException;
import 'package:cartzy/services/order_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';

// ============================================================
// MY ORDERS SCREEN ("Your Shopping Journey" reference design)
// Same orders, filters and actions as the website's Orders tab.
// ============================================================

const _ink = Color(0xFF261E30);
const _muted = Color(0xFF756A7C);
const _brand = Color(0xFF564B68);
const _brandLight = Color(0xFF6F6382);
const _cardBorder = Color(0xFFECE3E5);

String _peso(double amount) {
  final fixed = amount.toStringAsFixed(2);
  final parts = fixed.split('.');
  final whole = parts[0].replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (_) => ',');
  return '₱$whole.${parts[1]}';
}

/// Status pill colors, matching the website's status map.
(Color bg, Color fg) _statusColors(String status) => switch (status) {
      'to_pay' => (const Color(0xFFFFFBEB), const Color(0xFFB45309)),
      'to_ship' => (const Color(0xFFF0F9FF), const Color(0xFF0369A1)),
      'to_receive' => (const Color(0xFFF5F3FF), const Color(0xFF6D28D9)),
      'completed' => (const Color(0xFFECFDF5), const Color(0xFF047857)),
      _ => (const Color(0xFFFFF1F2), const Color(0xFFBE123C)),
    };

/// One icon per order stage, shared by the filter chips and status pills.
IconData _statusIcon(String status) => switch (status) {
      'all' => Icons.receipt_long_outlined,
      'to_pay' => Icons.account_balance_wallet_outlined,
      'to_ship' => Icons.inventory_2_outlined,
      'to_receive' => Icons.local_shipping_outlined,
      'completed' => Icons.task_alt_rounded,
      _ => Icons.cancel_outlined,
    };

String _errorText(Object error) => error is ApiException
    ? error.message
    : "Couldn't reach the Cartzy server. Check your connection and try again.";

class MyOrdersScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onStartShopping;
  final String? initialSearch;
  final String? initialFilter;

  /// Loads the orders; defaults to the Cartzy API.
  final Future<BuyerOrdersData> Function()? loadOrders;

  const MyOrdersScreen({
    super.key,
    required this.onBack,
    this.onStartShopping,
    this.loadOrders,
    this.initialSearch,
    this.initialFilter,
  });

  @override
  State<MyOrdersScreen> createState() => _MyOrdersScreenState();
}

class _MyOrdersScreenState extends State<MyOrdersScreen> {
  BuyerOrdersData? _data;
  bool _loading = true;
  String? _error;
  late String _filter;
  late String _search;
  late final TextEditingController _searchController;

  List<BuyerOrder> get _orders => _data?.orders ?? const [];

  @override
  void initState() {
    super.initState();
    _filter = widget.initialFilter ?? 'all';
    _search = widget.initialSearch ?? '';
    _searchController = TextEditingController(text: _search);
    _load();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = _data == null;
      _error = null;
    });
    try {
      final data = await (widget.loadOrders ?? OrderService.fetchOrders)();
      if (!mounted) return;
      setState(() {
        _data = data;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _error = e is ApiException && e.statusCode == 401
            ? 'Your session has expired. Please log in again to see your orders.'
            : _errorText(e);
      });
    }
  }

  List<BuyerOrder> get _visibleOrders {
    final term = _search.trim().toLowerCase();
    return _orders.where((o) {
      if (_filter != 'all' && o.status != _filter) return false;
      if (term.isEmpty) return true;
      return o.reference.toLowerCase().contains(term) ||
          o.store.toLowerCase().contains(term) ||
          (o.trackingCode ?? '').toLowerCase().contains(term) ||
          o.items.any((i) => i.name.toLowerCase().contains(term));
    }).toList();
  }

  void _startShopping() => (widget.onStartShopping ?? widget.onBack)();

  // ── Actions ─────────────────────────────────────────────────

  Future<void> _runAction(Future<String> Function() action) async {
    try {
      final message = await action();
      if (!mounted) return;
      showCartzyFlash(context, message);
      await _load();
    } catch (e) {
      if (!mounted) return;
      showCartzyFlash(context, _errorText(e), icon: Icons.error_outline);
    }
  }

  Future<void> _payNow(BuyerOrder order) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: Text('Pay for ${order.reference}?',
            style: GoogleFonts.dmSans(fontWeight: FontWeight.w700, color: _ink)),
        content: Text(
          'Confirm payment of ${_peso(order.total)} via ${order.payment}.',
          style: GoogleFonts.dmSans(fontSize: 14, color: _muted, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text('Not now', style: GoogleFonts.dmSans(color: _muted)),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: _brand),
            onPressed: () => Navigator.pop(context, true),
            child: Text('Pay now', style: GoogleFonts.dmSans(fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );
    if (confirmed == true) await _runAction(() => OrderService.pay(order.reference));
  }

  Future<void> _cancel(BuyerOrder order) async {
    final result = await showModalBottomSheet<(String, String?)>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _CancelOrderSheet(
        reference: order.reference,
        reasons: _data?.cancellationReasons ?? const [],
      ),
    );
    if (result == null) return;
    await _runAction(() => OrderService.requestCancellation(
          order.sellerOrderId,
          reason: result.$1,
          details: result.$2,
        ));
  }

  void _track(BuyerOrder order) {
    Navigator.of(context).push(MaterialPageRoute(
      builder: (_) => OrderTrackingScreen(
        sellerOrderId: order.sellerOrderId,
        reference: order.reference,
      ),
    ));
  }

  Future<void> _report(BuyerOrder order) async {
    final data = _data;
    if (data == null) return;
    final sent = await Navigator.of(context).push<bool>(MaterialPageRoute(
      builder: (_) => ReportProblemScreen(
        order: order,
        categories: data.reportCategories,
        resolutions: data.reportResolutions,
        contactPhone: data.contactPhone,
      ),
    ));
    if (sent == true) await _load();
  }

  Future<void> _rate(BuyerOrder order) async {
    final saved = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _RateItemsSheet(order: order),
    );
    if (saved == true) await _load();
  }

  // ── Header ──────────────────────────────────────────────────
  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 18, 20, 16),
      color: CartzyColors.background,
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Back button
          GestureDetector(
            onTap: widget.onBack,
            child: Container(
              margin: const EdgeInsets.only(top: 2, right: 14),
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: CartzyColors.border),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.02),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: const Icon(
                Icons.arrow_back,
                size: 18,
                color: CartzyColors.navy,
              ),
            ),
          ),

          // Title & Eyebrow & Subtitle
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'YOUR SHOPPING JOURNEY',
                  style: GoogleFonts.dmSans(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 1.4,
                    color: const Color(0xFF9E7766),
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  'My Orders',
                  style: GoogleFonts.playfairDisplay(
                    fontSize: 26,
                    fontWeight: FontWeight.w700,
                    color: _ink,
                    letterSpacing: -0.3,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Follow your active orders, track deliveries and look back at past purchases.',
                  style: GoogleFonts.dmSans(
                    fontSize: 12.5,
                    color: _muted,
                  ),
                ),
              ],
            ),
          ),

          // Order count chip
          Container(
            margin: const EdgeInsets.only(top: 4),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFFFAF6F5),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: const Color(0xFFEADFD8)),
            ),
            child: Text(
              '${_orders.length} ${_orders.length == 1 ? 'order' : 'orders'}',
              style: GoogleFonts.dmSans(
                fontSize: 12,
                fontWeight: FontWeight.w500,
                color: const Color(0xFF867570),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Filters & search ────────────────────────────────────────
  Widget _buildFilters() {
    final counts = _data?.counts ?? const {};
    Widget chip(String key, String label, int count) {
      final active = _filter == key;
      return Padding(
        padding: const EdgeInsets.only(right: 8),
        child: Material(
          color: active ? _brand : Colors.white,
          borderRadius: BorderRadius.circular(12),
          child: InkWell(
            borderRadius: BorderRadius.circular(12),
            onTap: () => setState(() => _filter = key),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: active ? _brand : _cardBorder),
              ),
              child: Text.rich(
                TextSpan(children: [
                  WidgetSpan(
                    alignment: PlaceholderAlignment.middle,
                    child: Padding(
                      padding: const EdgeInsets.only(right: 6),
                      child: Icon(
                        _statusIcon(key),
                        size: 16,
                        color: active ? Colors.white : const Color(0xFF8A7E8A),
                      ),
                    ),
                  ),
                  TextSpan(text: label),
                  TextSpan(
                    text: '  $count',
                    style: TextStyle(
                      fontSize: 11.5,
                      color: active ? Colors.white70 : const Color(0xFF9E929E),
                    ),
                  ),
                ]),
                style: GoogleFonts.dmSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: active ? Colors.white : const Color(0xFF6B6270),
                ),
              ),
            ),
          ),
        ),
      );
    }

    Widget group(String label) => Padding(
          padding: const EdgeInsets.only(right: 8, left: 4),
          child: Center(
            child: Text(
              label.toUpperCase(),
              style: GoogleFonts.dmSans(
                fontSize: 9.5,
                fontWeight: FontWeight.w700,
                letterSpacing: 1.1,
                color: const Color(0xFFA99BA6),
              ),
            ),
          ),
        );

    return SizedBox(
      height: 40,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 20),
        children: [
          chip('all', 'All', _orders.length),
          group('In progress'),
          for (final key in ['to_pay', 'to_ship', 'to_receive'])
            chip(key, BuyerOrder.statuses[key]!, counts[key] ?? 0),
          group('Past'),
          for (final key in ['completed', 'cancelled'])
            chip(key, BuyerOrder.statuses[key]!, counts[key] ?? 0),
        ],
      ),
    );
  }

  Widget _buildSearch() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 4),
      child: TextField(
        controller: _searchController,
        onChanged: (value) => setState(() => _search = value),
        style: GoogleFonts.dmSans(fontSize: 14, color: _ink),
        decoration: InputDecoration(
          isDense: true,
          hintText: 'Search by order ID, store or product',
          hintStyle: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFFAAA0A2)),
          prefixIcon: const Icon(Icons.search, size: 20, color: _muted),
          suffixIcon: _search.isEmpty
              ? null
              : IconButton(
                  icon: const Icon(Icons.close, size: 18, color: _muted),
                  onPressed: () {
                    _searchController.clear();
                    setState(() => _search = '');
                  },
                ),
          filled: true,
          fillColor: Colors.white,
          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 13),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: _cardBorder),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: CartzyColors.rose500, width: 1.4),
          ),
        ),
      ),
    );
  }

  // ── Empty State Card ────────────────────────────────────────
  Widget _buildEmptyStateCard() {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: _cardBorder, width: 1.1),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.025),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Main content area
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 44, 24, 38),
            child: Column(
              children: [
                // Organic ring + Package container
                SizedBox(
                  width: 96,
                  height: 96,
                  child: Stack(
                    alignment: Alignment.center,
                    clipBehavior: Clip.none,
                    children: [
                      // Organic hand-drawn outer ring
                      Positioned.fill(
                        child: CustomPaint(
                          painter: OrganicRingPainter(
                            color: const Color(0xFFE2CDC4),
                          ),
                        ),
                      ),

                      // Lavender package box
                      Container(
                        width: 66,
                        height: 66,
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFEBF4),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Icon(
                          Icons.shopping_bag_outlined,
                          size: 32,
                          color: Color(0xFF5E4E6D),
                        ),
                      ),

                      // Coral / terracotta + badge
                      Positioned(
                        right: 12,
                        bottom: 12,
                        child: Container(
                          width: 18,
                          height: 18,
                          decoration: const BoxDecoration(
                            color: Color(0xFFC07357),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.add,
                            size: 12,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 24),

                // Eyebrow
                Text(
                  'YOUR FIRST FIND AWAITS',
                  style: GoogleFonts.dmSans(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 1.4,
                    color: const Color(0xFFA66B56),
                  ),
                ),

                const SizedBox(height: 10),

                // Heading
                Text(
                  'No orders placed yet',
                  style: GoogleFonts.playfairDisplay(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: _ink,
                    letterSpacing: -0.2,
                  ),
                  textAlign: TextAlign.center,
                ),

                const SizedBox(height: 8),

                // Description
                Text(
                  'When you buy something, your order will appear here with live tracking\nand status updates.',
                  style: GoogleFonts.dmSans(
                    fontSize: 13,
                    color: _muted,
                    height: 1.5,
                  ),
                  textAlign: TextAlign.center,
                ),

                const SizedBox(height: 22),

                // Start shopping CTA button
                InkWell(
                  onTap: _startShopping,
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 22,
                      vertical: 11,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFF554B62),
                      borderRadius: BorderRadius.circular(10),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF554B62).withValues(alpha: 0.18),
                          blurRadius: 8,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          'Start shopping',
                          style: GoogleFonts.dmSans(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(width: 8),
                        const Icon(
                          Icons.arrow_forward,
                          size: 14,
                          color: Colors.white,
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Divider
          const Divider(
            height: 1,
            thickness: 1,
            color: Color(0xFFF3ECEE),
          ),

          // Footer message
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 15, horizontal: 20),
            child: Text(
              'Order details and delivery updates will be available right here.',
              style: GoogleFonts.dmSans(
                fontSize: 12,
                color: const Color(0xFF9E929E),
              ),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
    );
  }

  Widget _messageCard({
    required IconData icon,
    required String title,
    required String text,
    String? action,
    VoidCallback? onAction,
  }) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      padding: const EdgeInsets.fromLTRB(24, 32, 24, 28),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: _cardBorder, width: 1.1),
      ),
      child: Column(
        children: [
          Icon(icon, size: 34, color: const Color(0xFF9E7766)),
          const SizedBox(height: 12),
          Text(title,
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(fontSize: 15, fontWeight: FontWeight.w700, color: _ink)),
          const SizedBox(height: 6),
          Text(text,
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(fontSize: 13, height: 1.45, color: _muted)),
          if (action != null) ...[
            const SizedBox(height: 16),
            _OrderButton(label: action, primary: true, onTap: onAction),
          ],
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(
        child: CircularProgressIndicator(color: _brand, strokeWidth: 2.4),
      );
    }

    final List<Widget> children;
    if (_error != null && _data == null) {
      children = [
        _messageCard(
          icon: Icons.cloud_off_outlined,
          title: "Couldn't load your orders",
          text: _error!,
          action: 'Try again',
          onAction: _load,
        ),
      ];
    } else if (_orders.isEmpty) {
      children = [_buildEmptyStateCard()];
    } else {
      final visible = _visibleOrders;
      children = [
        const SizedBox(height: 4),
        _buildFilters(),
        _buildSearch(),
        if (visible.isEmpty)
          _messageCard(
            icon: Icons.search_off_rounded,
            title: 'No matching orders.',
            text: 'Choose another status or clear your search to see your other purchases.',
          )
        else
          for (final order in visible)
            _OrderCard(
              order: order,
              onTrack: () => _track(order),
              onCancel: () => _cancel(order),
              onPay: () => _payNow(order),
              onReport: () => _report(order),
              onRate: () => _rate(order),
              onBuyAgain: _startShopping,
            ),
        const SizedBox(height: 24),
      ];
    }

    return RefreshIndicator(
      color: _brand,
      onRefresh: _load,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: children,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            Expanded(child: _buildBody()),
          ],
        ),
      ),
    );
  }
}

// ============================================================
// ORDER CARD — one store's part of an order
// ============================================================

class _OrderCard extends StatelessWidget {
  final BuyerOrder order;
  final VoidCallback onTrack;
  final VoidCallback onCancel;
  final VoidCallback onPay;
  final VoidCallback onReport;
  final VoidCallback onRate;
  final VoidCallback onBuyAgain;

  const _OrderCard({
    required this.order,
    required this.onTrack,
    required this.onCancel,
    required this.onPay,
    required this.onReport,
    required this.onRate,
    required this.onBuyAgain,
  });

  @override
  Widget build(BuildContext context) {
    final (statusBg, statusFg) = _statusColors(order.status);
    final meta = [
      order.reference,
      order.date,
      if (order.trackingCode != null && order.trackingCode!.isNotEmpty) 'Tracking ${order.trackingCode}',
    ].join(' · ');

    return Container(
      margin: const EdgeInsets.fromLTRB(20, 12, 20, 0),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: _cardBorder, width: 1.1),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.025),
            blurRadius: 12,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Store + status
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 34,
                  height: 34,
                  decoration: BoxDecoration(
                    color: const Color(0xFFF4F1F7),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.storefront_outlined, size: 18, color: _brand),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(order.store,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.dmSans(
                              fontSize: 14, fontWeight: FontWeight.w700, color: _ink)),
                      const SizedBox(height: 2),
                      Text(meta,
                          style: GoogleFonts.dmSans(fontSize: 11.5, color: const Color(0xFF9E929E))),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
                  decoration: BoxDecoration(
                    color: statusBg,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: statusFg.withValues(alpha: 0.2)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(_statusIcon(order.status), size: 13, color: statusFg),
                      const SizedBox(width: 4),
                      Text(order.statusLabel,
                          style: GoogleFonts.dmSans(
                              fontSize: 11, fontWeight: FontWeight.w700, color: statusFg)),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: Color(0xFFF3ECEE)),

          // Items
          for (final item in order.items)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
              child: Row(
                children: [
                  CartzyProductImage(
                    imageUrl: item.image,
                    width: 58,
                    height: 58,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(item.name,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.dmSans(
                                fontSize: 13.5, fontWeight: FontWeight.w700, color: _ink)),
                        const SizedBox(height: 2),
                        Text(
                          [if (item.variation.isNotEmpty) item.variation, 'x${item.qty}'].join(' · '),
                          style: GoogleFonts.dmSans(fontSize: 12, color: _muted),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(_peso(item.price),
                      style: GoogleFonts.dmSans(
                          fontSize: 13, fontWeight: FontWeight.w700, color: const Color(0xFF4B4452))),
                ],
              ),
            ),
          const SizedBox(height: 12),

          // Cancellation & report notices
          if (order.cancellationStatus == 'pending')
            const _Notice(
              text: 'Cancellation requested — awaiting seller approval.',
              background: Color(0xFFFFFBEB),
              color: Color(0xFF92400E),
              bold: true,
            )
          else if (order.cancellationStatus == 'rejected')
            const _Notice(text: 'The seller declined your cancellation request. Your order remains active.')
          else if (order.cancellationStatus == 'approved')
            const _Notice(
                text:
                    'Cancellation approved by seller. If already paid, contact the seller to arrange your refund.'),
          if (order.report != null)
            order.report!.isOpen
                ? _Notice(
                    title: 'Problem reported · Case #${order.report!.id}',
                    text:
                        '${order.report!.subject}. Our team is reviewing it and will update you in Notifications.',
                    background: const Color(0xFFFFF7ED),
                    color: const Color(0xFF9A3412),
                  )
                : _Notice(
                    title: 'Report resolved · Case #${order.report!.id}',
                    text: order.report!.adminNotes ?? '',
                    background: const Color(0xFFF0FDF4),
                    color: const Color(0xFF166534),
                  ),

          // Total + actions
          Container(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 14),
            decoration: const BoxDecoration(
              color: Color(0xFFFBF9FA),
              border: Border(top: BorderSide(color: Color(0xFFF3ECEE))),
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(18)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    Text('Order total ', style: GoogleFonts.dmSans(fontSize: 12.5, color: _muted)),
                    Text(_peso(order.total),
                        style: GoogleFonts.dmSans(
                            fontSize: 15, fontWeight: FontWeight.w800, color: _ink)),
                    const Spacer(),
                    if (order.payment.isNotEmpty)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF1EFF5),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(order.payment,
                            style: GoogleFonts.dmSans(
                                fontSize: 11, fontWeight: FontWeight.w700, color: _brand)),
                      ),
                  ],
                ),
                const SizedBox(height: 12),
                Wrap(
                  alignment: WrapAlignment.end,
                  spacing: 8,
                  runSpacing: 8,
                  children: _actions(),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  /// Same buttons, in the same order, as the website's order card.
  List<Widget> _actions() {
    final status = order.status;
    return [
      if (order.canCancel)
        _OrderButton(label: 'Cancel order', icon: Icons.close_rounded, onTap: onCancel),
      if (order.canReport)
        _OrderButton(label: 'Report problem', icon: Icons.flag_outlined, onTap: onReport),
      if (status != 'cancelled' && status != 'to_receive')
        _OrderButton(label: 'Track order', icon: Icons.location_on_outlined, onTap: onTrack),
      if (status == 'to_pay')
        _OrderButton(label: 'Pay now', icon: Icons.payments_outlined, primary: true, onTap: onPay),
      if (status == 'to_receive')
        _OrderButton(
            label: 'Track order', icon: Icons.local_shipping_outlined, primary: true, onTap: onTrack),
      if (status == 'completed')
        _OrderButton(label: 'Rate', icon: Icons.star_outline_rounded, onTap: onRate),
      if (status == 'completed' || status == 'cancelled')
        _OrderButton(label: 'Buy again', icon: Icons.replay_rounded, primary: true, onTap: onBuyAgain),
    ];
  }
}

class _Notice extends StatelessWidget {
  final String? title;
  final String text;
  final Color background;
  final Color color;
  final bool bold;

  const _Notice({
    this.title,
    required this.text,
    this.background = const Color(0xFFF9F8FA),
    this.color = const Color(0xFF4B5563),
    this.bold = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      color: background,
      child: Text.rich(
        TextSpan(children: [
          if (title != null)
            TextSpan(text: '$title\n', style: const TextStyle(fontWeight: FontWeight.w700)),
          TextSpan(text: text),
        ]),
        style: GoogleFonts.dmSans(
          fontSize: 12.5,
          height: 1.45,
          color: color,
          fontWeight: bold ? FontWeight.w600 : FontWeight.w400,
        ),
      ),
    );
  }
}

class _OrderButton extends StatelessWidget {
  final String label;
  final IconData? icon;
  final bool primary;
  final VoidCallback? onTap;

  const _OrderButton({required this.label, this.icon, this.primary = false, this.onTap});

  @override
  Widget build(BuildContext context) {
    final color = primary ? Colors.white : const Color(0xFF4B4452);
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
        decoration: BoxDecoration(
          gradient: primary ? const LinearGradient(colors: [_brand, _brandLight]) : null,
          color: primary ? null : Colors.white,
          borderRadius: BorderRadius.circular(10),
          border: primary ? null : Border.all(color: const Color(0xFFE1DDE7)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (icon != null) ...[
              Icon(icon, size: 15, color: color),
              const SizedBox(width: 5),
            ],
            Text(
              label,
              style: GoogleFonts.dmSans(
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ============================================================
// SHEETS — cancel request and rating
// ============================================================

class _SheetFrame extends StatelessWidget {
  final String title;
  final String? subtitle;
  final List<Widget> children;

  const _SheetFrame({required this.title, this.subtitle, required this.children});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: Container(
        constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.88),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            mainAxisSize: MainAxisSize.min,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: CartzyColors.border,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              Text(title,
                  style: GoogleFonts.dmSans(fontSize: 19, fontWeight: FontWeight.w800, color: _ink)),
              if (subtitle != null) ...[
                const SizedBox(height: 4),
                Text(subtitle!,
                    style: GoogleFonts.dmSans(
                        fontSize: 13, fontWeight: FontWeight.w600, color: const Color(0xFF9E929E))),
              ],
              const SizedBox(height: 12),
              ...children,
            ],
          ),
        ),
      ),
    );
  }
}

class _CancelOrderSheet extends StatefulWidget {
  final String reference;
  final List<String> reasons;

  const _CancelOrderSheet({required this.reference, required this.reasons});

  @override
  State<_CancelOrderSheet> createState() => _CancelOrderSheetState();
}

class _CancelOrderSheetState extends State<_CancelOrderSheet> {
  static const _otherReason = 'Other reason';
  String? _reason;
  final _detailsController = TextEditingController();

  @override
  void dispose() {
    _detailsController.dispose();
    super.dispose();
  }

  bool get _canSend =>
      _reason != null && (_reason != _otherReason || _detailsController.text.trim().isNotEmpty);

  @override
  Widget build(BuildContext context) {
    return _SheetFrame(
      title: 'Request cancellation',
      subtitle: 'Order ${widget.reference}',
      children: [
        Text(
          'Select a reason to send to the seller. Your order will only be cancelled after the seller approves your request.',
          style: GoogleFonts.dmSans(fontSize: 13, height: 1.45, color: _muted),
        ),
        const SizedBox(height: 12),
        for (final reason in widget.reasons)
          Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: InkWell(
              borderRadius: BorderRadius.circular(12),
              onTap: () => setState(() => _reason = reason),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: _reason == reason ? _brand : const Color(0xFFE5E7EB)),
                  color: _reason == reason ? const Color(0xFFF6F4F8) : Colors.white,
                ),
                child: Row(
                  children: [
                    Icon(
                      _reason == reason ? Icons.radio_button_checked : Icons.radio_button_unchecked,
                      size: 20,
                      color: _reason == reason ? _brand : const Color(0xFFB7AAB5),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(reason, style: GoogleFonts.dmSans(fontSize: 13.5, color: _ink)),
                    ),
                  ],
                ),
              ),
            ),
          ),
        if (_reason == _otherReason) ...[
          const SizedBox(height: 4),
          TextField(
            controller: _detailsController,
            maxLength: 1000,
            maxLines: 3,
            onChanged: (_) => setState(() {}),
            style: GoogleFonts.dmSans(fontSize: 13.5),
            decoration: InputDecoration(
              hintText: 'Please specify your reason.',
              hintStyle: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFFAAA0A2)),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
        ],
        const SizedBox(height: 6),
        Text('Send this cancellation request to the seller?',
            style: GoogleFonts.dmSans(fontSize: 13.5, fontWeight: FontWeight.w700, color: _ink)),
        const SizedBox(height: 4),
        Text(
          'If approved and already paid, contact the seller to arrange your refund. Sending a request does not issue a refund.',
          style: GoogleFonts.dmSans(fontSize: 12, height: 1.4, color: const Color(0xFF9E929E)),
        ),
        const SizedBox(height: 16),
        Row(
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            _OrderButton(label: 'Keep order', onTap: () => Navigator.pop(context)),
            const SizedBox(width: 8),
            Opacity(
              opacity: _canSend ? 1 : 0.45,
              child: _OrderButton(
                label: 'Send cancellation request',
                primary: true,
                onTap: _canSend
                    ? () => Navigator.pop(context, (_reason!, _detailsController.text.trim()))
                    : null,
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class _RateItemsSheet extends StatefulWidget {
  final BuyerOrder order;

  const _RateItemsSheet({required this.order});

  @override
  State<_RateItemsSheet> createState() => _RateItemsSheetState();
}

class _RateItemsSheetState extends State<_RateItemsSheet> {
  late final Map<int, int> _ratings = {
    for (final item in widget.order.items)
      if (item.rating != null) item.id: item.rating!,
  };
  late final Map<int, TextEditingController> _comments = {
    for (final item in widget.order.items) item.id: TextEditingController(text: item.comment ?? ''),
  };
  bool _saving = false;

  @override
  void dispose() {
    for (final c in _comments.values) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _save() async {
    if (_ratings.isEmpty) {
      showCartzyFlash(context, 'Please choose a star rating.', icon: Icons.error_outline);
      return;
    }
    setState(() => _saving = true);
    try {
      String message = '';
      for (final entry in _ratings.entries) {
        message = await OrderService.review(
          entry.key,
          rating: entry.value,
          comment: _comments[entry.key]!.text.trim(),
        );
      }
      if (!mounted) return;
      showCartzyFlash(context, message);
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      setState(() => _saving = false);
      showCartzyFlash(context, _errorText(e), icon: Icons.error_outline);
    }
  }

  @override
  Widget build(BuildContext context) {
    return _SheetFrame(
      title: 'Rate your items',
      subtitle: '${widget.order.store} · ${widget.order.reference}',
      children: [
        for (final item in widget.order.items) ...[
          Row(
            children: [
              CartzyProductImage(
                imageUrl: item.image,
                width: 44,
                height: 44,
                borderRadius: BorderRadius.circular(10),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(item.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.dmSans(fontSize: 13.5, fontWeight: FontWeight.w700, color: _ink)),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Row(
            children: [
              for (var star = 1; star <= 5; star++)
                IconButton(
                  visualDensity: VisualDensity.compact,
                  tooltip: '$star star${star == 1 ? '' : 's'}',
                  onPressed: () => setState(() => _ratings[item.id] = star),
                  icon: Icon(
                    star <= (_ratings[item.id] ?? 0) ? Icons.star_rounded : Icons.star_outline_rounded,
                    color: const Color(0xFFF59E0B),
                    size: 30,
                  ),
                ),
            ],
          ),
          TextField(
            controller: _comments[item.id],
            maxLength: 1000,
            maxLines: 2,
            style: GoogleFonts.dmSans(fontSize: 13.5),
            decoration: InputDecoration(
              hintText: 'Share your thoughts (optional)',
              hintStyle: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFFAAA0A2)),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
          const SizedBox(height: 10),
        ],
        Align(
          alignment: Alignment.centerRight,
          child: _saving
              ? const Padding(
                  padding: EdgeInsets.all(8),
                  child: SizedBox(
                    width: 22,
                    height: 22,
                    child: CircularProgressIndicator(strokeWidth: 2, color: _brand),
                  ),
                )
              : _OrderButton(label: 'Submit review', primary: true, onTap: _save),
        ),
      ],
    );
  }
}

// ============================================================
// ORGANIC HAND-DRAWN RING PAINTER
// ============================================================

class OrganicRingPainter extends CustomPainter {
  final Color color;

  OrganicRingPainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..strokeWidth = 1.3
      ..style = PaintingStyle.stroke;

    final path = Path();
    final w = size.width;
    final h = size.height;

    // Organic pebble-shaped ring around the package icon
    path.moveTo(w * 0.35, h * 0.04);
    path.cubicTo(w * 0.78, -0.04, w * 1.04, h * 0.32, w * 0.98, h * 0.70);
    path.cubicTo(w * 0.92, h * 1.03, w * 0.58, h * 1.02, w * 0.24, h * 0.96);
    path.cubicTo(-0.03, h * 0.88, -0.02, h * 0.44, w * 0.08, h * 0.22);
    path.cubicTo(w * 0.15, h * 0.07, w * 0.25, h * 0.06, w * 0.35, h * 0.04);

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant OrganicRingPainter oldDelegate) =>
      color != oldDelegate.color;
}

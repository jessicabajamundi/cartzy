import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/review_service.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';
import 'package:cartzy/buyer/order_history_screen.dart';

// ============================================================
// TRACK ORDER SCREEN — Delivery Updates & Parcel Tracking
// Reached via the location icon in the dashboard
// ============================================================

class TrackOrderScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onBrowseProducts;

  const TrackOrderScreen({
    super.key,
    required this.onBack,
    this.onBrowseProducts,
  });

  @override
  State<TrackOrderScreen> createState() => _TrackOrderScreenState();
}

class _TrackOrderScreenState extends State<TrackOrderScreen> {
  final TextEditingController _trackingController = TextEditingController();
  Order? _trackedOrder;
  bool _hasSearched = false;

  @override
  void dispose() {
    _trackingController.dispose();
    super.dispose();
  }

  void _searchOrder() {
    final query = _trackingController.text.trim().toLowerCase();
    setState(() {
      _hasSearched = true;
      if (query.isEmpty) {
        _trackedOrder = null;
        return;
      }

      final orders = ReviewService.instance.orders;
      try {
        _trackedOrder = orders.firstWhere((o) {
          final idMatch = o.id.toLowerCase() == query ||
              o.id.toLowerCase().replaceAll('-', '') == query.replaceAll('-', '');
          final nameMatch = o.productName.toLowerCase().contains(query);
          return idMatch || nameMatch;
        });
      } catch (_) {
        _trackedOrder = null;
      }
    });
  }

  List<Order> get _activeParcels {
    // Orders currently in transit / shipping
    return ReviewService.instance.orders
        .where((o) => o.status == 'To Ship' || o.status == 'To Receive')
        .toList();
  }

  @override
  Widget build(BuildContext context) {
    final isMobile = MediaQuery.of(context).size.width < 650;
    final activeOrders = _activeParcels;

    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Column(
          children: [
            // Top App Bar
            _buildAppBar(),

            // Content
            Expanded(
              child: SingleChildScrollView(
                padding: EdgeInsets.symmetric(
                  horizontal: isMobile ? 16 : 32,
                  vertical: 20,
                ),
                child: Center(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 820),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Header title block with badge
                        _buildHeader(isMobile),
                        const SizedBox(height: 24),

                        // Search Card
                        _buildSearchCard(isMobile),
                        const SizedBox(height: 22),

                        // Main Tracking Status Card
                        if (_trackedOrder != null)
                          _buildTrackedOrderCard(_trackedOrder!, isMobile)
                        else if (!_hasSearched && activeOrders.isNotEmpty)
                          _buildActiveParcelsList(activeOrders, isMobile)
                        else
                          _buildEmptyStateCard(isMobile),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAppBar() {
    return Container(
      height: 56,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      decoration: const BoxDecoration(
        color: CartzyColors.surface,
        border: Border(
          bottom: BorderSide(color: CartzyColors.border, width: 1),
        ),
      ),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
            onPressed: widget.onBack,
            tooltip: 'Back to Dashboard',
          ),
          const SizedBox(width: 4),
          Text(
            'Delivery Updates',
            style: GoogleFonts.dmSans(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: CartzyColors.navy,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader(bool isMobile) {
    final titleColumn = Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'DELIVERY UPDATES',
          style: GoogleFonts.dmSans(
            fontSize: 11,
            fontWeight: FontWeight.w800,
            letterSpacing: 1.4,
            color: const Color(0xFF94626D),
          ),
        ),
        const SizedBox(height: 6),
        Text(
          'Track Order',
          style: GoogleFonts.playfairDisplay(
            fontSize: isMobile ? 26 : 34,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF282133),
            letterSpacing: -0.5,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          'Follow your parcel, from order confirmation to your doorstep.',
          style: GoogleFonts.dmSans(
            fontSize: isMobile ? 12.5 : 14,
            color: const Color(0xFF7A6F7B),
          ),
        ),
      ],
    );

    final badge = Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
      decoration: BoxDecoration(
        color: const Color(0xFFF9EFEA),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFF2DED6)),
      ),
      child: Text(
        'Parcel tracking',
        style: GoogleFonts.dmSans(
          fontSize: 12,
          fontWeight: FontWeight.w600,
          color: const Color(0xFF8C5C50),
        ),
      ),
    );

    if (isMobile) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(child: titleColumn),
              const SizedBox(width: 8),
              badge,
            ],
          ),
        ],
      );
    }

    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(child: titleColumn),
        const SizedBox(width: 16),
        badge,
      ],
    );
  }

  Widget _buildSearchCard(bool isMobile) {
    return Container(
      padding: EdgeInsets.all(isMobile ? 16 : 22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFECE4E7)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Order reference or tracking number',
            style: GoogleFonts.dmSans(
              fontSize: 12.5,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF756877),
            ),
          ),
          const SizedBox(height: 10),
          if (isMobile) ...[
            // Stacked for narrow mobile screens
            _buildSearchTextField(),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: _buildSearchButton(),
            ),
          ] else ...[
            // Inline for wide screens
            Row(
              children: [
                Expanded(child: _buildSearchTextField()),
                const SizedBox(width: 12),
                _buildSearchButton(),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildSearchTextField() {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFFCFAFB),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE8DEE3)),
      ),
      child: TextField(
        controller: _trackingController,
        style: GoogleFonts.dmSans(
          fontSize: 13.5,
          color: const Color(0xFF282133),
        ),
        onSubmitted: (_) => _searchOrder(),
        decoration: InputDecoration(
          hintText: 'Enter order reference (e.g. ORD-...) or courier tracking number',
          hintStyle: GoogleFonts.dmSans(
            fontSize: 12.5,
            color: const Color(0xFFA197A3),
          ),
          filled: false,
          fillColor: Colors.transparent,
          border: InputBorder.none,
          enabledBorder: InputBorder.none,
          focusedBorder: InputBorder.none,
          errorBorder: InputBorder.none,
          disabledBorder: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          isDense: true,
        ),
      ),
    );
  }

  Widget _buildSearchButton() {
    return ElevatedButton(
      onPressed: _searchOrder,
      style: ElevatedButton.styleFrom(
        backgroundColor: const Color(0xFF4A3C4D),
        foregroundColor: Colors.white,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 13),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            'Track parcel',
            style: GoogleFonts.dmSans(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: Colors.white,
            ),
          ),
          const SizedBox(width: 6),
          const Icon(Icons.arrow_forward, size: 14, color: Colors.white),
        ],
      ),
    );
  }

  Widget _buildEmptyStateCard(bool isMobile) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFECE4E7)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          Padding(
            padding: EdgeInsets.symmetric(
              horizontal: isMobile ? 20 : 40,
              vertical: isMobile ? 36 : 48,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Stylized Organic Package Icon
                _buildOrganicParcelIcon(),
                const SizedBox(height: 22),

                // Subtitle tag
                Text(
                  'DELIVERY UPDATES',
                  style: GoogleFonts.dmSans(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 1.3,
                    color: const Color(0xFF94626D),
                  ),
                ),
                const SizedBox(height: 8),

                // Heading
                Text(
                  _hasSearched ? 'No order found' : 'No active parcels to track',
                  style: GoogleFonts.playfairDisplay(
                    fontSize: isMobile ? 20 : 24,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF282133),
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 8),

                // Subtitle
                ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 440),
                  child: Text(
                    _hasSearched
                        ? 'We couldn\'t find a parcel with that reference number. Please check your reference or browse your active orders.'
                        : 'When you place an order, live shipping status and courier timeline will appear here.',
                    style: GoogleFonts.dmSans(
                      fontSize: 13,
                      color: const Color(0xFF7A6F7B),
                      height: 1.45,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
                const SizedBox(height: 24),

                // Browse products button
                ElevatedButton(
                  onPressed: () {
                    if (widget.onBrowseProducts != null) {
                      widget.onBrowseProducts!();
                    } else {
                      widget.onBack();
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4A3C4D),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 22,
                      vertical: 12,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Browse products',
                        style: GoogleFonts.dmSans(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(width: 6),
                      const Icon(Icons.arrow_forward, size: 14, color: Colors.white),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Footer strip inside card
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
            decoration: const BoxDecoration(
              color: Color(0xFFFCFAFA),
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(18)),
              border: Border(
                top: BorderSide(color: Color(0xFFF0E8EA)),
              ),
            ),
            child: InkWell(
              onTap: () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => OrderHistoryScreen(
                      onBack: () => Navigator.of(context).pop(),
                      onStartShopping: () => Navigator.of(context).pop(),
                    ),
                  ),
                );
              },
              child: Center(
                child: Text(
                  'Find your order reference in My Orders.',
                  style: GoogleFonts.dmSans(
                    fontSize: 12.5,
                    color: const Color(0xFF7A6F7B),
                    fontWeight: FontWeight.w500,
                    decoration: TextDecoration.underline,
                    decorationColor: const Color(0xFFB5A8B4),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOrganicParcelIcon() {
    return Container(
      width: 86,
      height: 86,
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(28),
        border: Border.all(
          color: const Color(0xFFEADDE0),
          width: 1.2,
        ),
      ),
      child: Container(
        decoration: BoxDecoration(
          color: const Color(0xFFEFE8EF),
          borderRadius: BorderRadius.circular(24),
        ),
        child: Stack(
          alignment: Alignment.center,
          children: [
            const Icon(
              Icons.inventory_2_outlined,
              size: 34,
              color: Color(0xFF4C3E54),
            ),
            Positioned(
              right: 8,
              bottom: 8,
              child: Container(
                padding: const EdgeInsets.all(3),
                decoration: const BoxDecoration(
                  color: Color(0xFF9E6B74),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.add,
                  size: 10,
                  color: Colors.white,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActiveParcelsList(List<Order> orders, bool isMobile) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 12),
          child: Text(
            'Active Parcels (${orders.length})',
            style: GoogleFonts.dmSans(
              fontSize: 15,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF282133),
            ),
          ),
        ),
        ...orders.map((order) => Padding(
              padding: const EdgeInsets.only(bottom: 16),
              child: _buildTrackedOrderCard(order, isMobile),
            )),
      ],
    );
  }

  Widget _buildTrackedOrderCard(Order order, bool isMobile) {
    final isDelivered = order.status == 'To Review';
    final isInTransit = order.status == 'To Receive';

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFECE4E7)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Order Card Header
          Padding(
            padding: EdgeInsets.all(isMobile ? 16 : 20),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    width: 54,
                    height: 54,
                    color: CartzyColors.background,
                    child: CartzyProductImage(
                      imageUrl: order.effectiveImage,
                      width: 54,
                      height: 54,
                      fit: BoxFit.cover,
                      fallbackIcon: Icons.shopping_bag_outlined,
                    ),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Flexible(
                            child: Text(
                              'Ref #${order.id.toUpperCase()}',
                              style: GoogleFonts.dmSans(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF94626D),
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: isInTransit
                                  ? const Color(0xFFE8F5E9)
                                  : const Color(0xFFFFF7E6),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              isInTransit ? 'In Transit' : order.status,
                              style: GoogleFonts.dmSans(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: isInTransit
                                  ? const Color(0xFF2E7D32)
                                  : const Color(0xFFD97706),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        order.productName,
                        style: GoogleFonts.dmSans(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF282133),
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Qty: ${order.quantity} • Total: ${order.price}',
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          color: const Color(0xFF8A7E8C),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const Divider(height: 1, color: Color(0xFFF0E8EA)),

          // Progress Timeline
          Padding(
            padding: EdgeInsets.all(isMobile ? 16 : 20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Courier Live Progress',
                  style: GoogleFonts.dmSans(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF282133),
                  ),
                ),
                const SizedBox(height: 16),
                _buildTimelineStep(
                  title: 'Order Confirmed',
                  subtitle: 'Payment verified and sent to merchant warehouse',
                  isCompleted: true,
                  isCurrent: false,
                ),
                _buildTimelineStep(
                  title: 'Packed & Dispatched',
                  subtitle: 'Security sealed and labeled with waybill barcode',
                  isCompleted: true,
                  isCurrent: false,
                ),
                _buildTimelineStep(
                  title: 'In Transit — Metro Express Rider',
                  subtitle: isInTransit
                      ? 'Rider assigned • Metro South Sector'
                      : 'Pending pickup from local distribution center',
                  isCompleted: isDelivered,
                  isCurrent: isInTransit,
                ),
                _buildTimelineStep(
                  title: 'Delivered to Doorstep',
                  subtitle: isDelivered
                      ? 'Package handed over and verified'
                      : 'Estimated arrival today by 5:30 PM',
                  isCompleted: isDelivered,
                  isCurrent: isDelivered,
                  isLast: true,
                ),
              ],
            ),
          ),

          // Footer
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
            decoration: const BoxDecoration(
              color: Color(0xFFFCFAFA),
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(18)),
              border: Border(
                top: BorderSide(color: Color(0xFFF0E8EA)),
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Flexible(
                  child: Text(
                    'Need help with this order?',
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      color: const Color(0xFF7A6F7B),
                    ),
                  ),
                ),
                TextButton(
                  onPressed: () => setState(() => _trackedOrder = null),
                  style: TextButton.styleFrom(
                    visualDensity: VisualDensity.compact,
                    padding: EdgeInsets.zero,
                  ),
                  child: Text(
                    'Clear Search',
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF94626D),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTimelineStep({
    required String title,
    required String subtitle,
    required bool isCompleted,
    required bool isCurrent,
    bool isLast = false,
  }) {
    final dotColor = isCurrent
        ? const Color(0xFF8B3A3A)
        : isCompleted
            ? const Color(0xFF2E7D32)
            : const Color(0xFFD4C8CD);

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Step Dot & Line
          Column(
            children: [
              Container(
                width: 18,
                height: 18,
                decoration: BoxDecoration(
                  color: dotColor.withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                ),
                child: Center(
                  child: Container(
                    width: 8,
                    height: 8,
                    decoration: BoxDecoration(
                      color: dotColor,
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
              ),
              if (!isLast)
                Expanded(
                  child: Container(
                    width: 2,
                    color: isCompleted
                        ? const Color(0xFF2E7D32).withValues(alpha: 0.3)
                        : const Color(0xFFECE4E7),
                    margin: const EdgeInsets.symmetric(vertical: 3),
                  ),
                ),
            ],
          ),
          const SizedBox(width: 14),

          // Step Text
          Expanded(
            child: Padding(
              padding: EdgeInsets.only(bottom: isLast ? 0 : 18),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: GoogleFonts.dmSans(
                      fontSize: 13,
                      fontWeight: isCurrent || isCompleted
                          ? FontWeight.w700
                          : FontWeight.w500,
                      color: isCurrent
                          ? const Color(0xFF8B3A3A)
                          : const Color(0xFF282133),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: GoogleFonts.dmSans(
                      fontSize: 11.5,
                      color: const Color(0xFF8A7E8C),
                      height: 1.35,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

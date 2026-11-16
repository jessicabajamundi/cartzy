import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'rider_common_app_bar.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/rider_delivery_service.dart';
import 'rider_delivery_process.dart';

class RiderDeliveryDashboard extends StatefulWidget {
  final String token;
  final VoidCallback onBack;

  /// Test hooks; the app uses [RiderDeliveryService].
  final Future<RiderDeliveryQueue> Function()? loadQueue;
  final Future<String> Function(RiderDelivery delivery)? startDelivery;

  const RiderDeliveryDashboard({
    super.key,
    required this.token,
    required this.onBack,
    this.loadQueue,
    this.startDelivery,
  });

  @override
  State<RiderDeliveryDashboard> createState() => _RiderDeliveryDashboardState();
}

class _RiderDeliveryDashboardState extends State<RiderDeliveryDashboard> {
  final TextEditingController _searchController = TextEditingController();
  String _activeFilter = 'All'; // 'All', 'COD', 'Prepaid'
  List<RiderDelivery> _orders = [];
  List<RiderPickup> _pickups = [];
  double _codTotal = 0;
  bool _loading = true;
  String? _error;
  int? _startingId;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final queue = await (widget.loadQueue ?? RiderDeliveryService.fetchQueue)();
      if (!mounted) return;
      setState(() {
        _orders = queue.deliveries;
        _pickups = queue.pickups;
        _codTotal = queue.codToCollect;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _error = e is ApiException ? e.message : "Couldn't load your deliveries. Check your connection.";
      });
    }
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  List<RiderDelivery> get _filteredOrders {
    final query = _searchController.text.trim().toLowerCase();
    return _orders.where((order) {
      final matchesQuery = query.isEmpty ||
          [order.trackingCode, order.orderReference, order.recipientName, order.recipientAddress, order.sellerName, order.itemsSummary]
              .any((v) => v != null && v.toLowerCase().contains(query));

      if (!matchesQuery) return false;

      if (_activeFilter == 'COD') return order.isCod;
      if (_activeFilter == 'Prepaid') return !order.isCod;
      return true;
    }).toList();
  }

  /// Leaving the hub: saves "out for delivery" on the server before anything else.
  Future<void> _start(RiderDelivery order) async {
    if (_startingId != null) return;
    setState(() => _startingId = order.id);
    final messenger = ScaffoldMessenger.of(context);
    try {
      final message = await (widget.startDelivery ?? RiderDeliveryService.startDelivery)(order);
      messenger.showSnackBar(SnackBar(content: Text(message)));
      await _load();
    } catch (e) {
      messenger.showSnackBar(SnackBar(
        content: Text(e is ApiException ? e.message : "Couldn't reach the server. Nothing was saved; try again."),
      ));
    } finally {
      if (mounted) setState(() => _startingId = null);
    }
  }

  Future<void> _deliver(RiderDelivery order) async {
    final delivered = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (context) => RiderDeliverProcess(delivery: order, token: widget.token),
      ),
    );
    if (!mounted) return;
    if (delivered != null) await _load();
    if (delivered == true) {
      if (!mounted) return;
      final messenger = ScaffoldMessenger.of(context);
      messenger
        ..hideCurrentMaterialBanner()
        ..showMaterialBanner(
          MaterialBanner(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            leading: Container(
              width: 24,
              height: 24,
              decoration: const BoxDecoration(color: Color(0xFFD1F5E6), shape: BoxShape.circle),
              child: Icon(Icons.check, size: 15, color: CartzyColors.readableOf(context, light: const Color(0xFF13845B), background: const Color(0xFFD1F5E6))),
            ),
            content: Text(
              'Parcel delivered and saved. The buyer, seller and hub were notified.',
              style: GoogleFonts.dmSans(fontSize: 11, fontWeight: FontWeight.w600, color: CartzyColors.readableOf(context, light: const Color(0xFF176B4E))),
            ),
            backgroundColor: CartzyColors.elevatedOf(context, light: const Color(0xFFF0FFF8)),
            elevation: 0,
            dividerColor: const Color(0xFFA9E5CA),
            actions: [
              IconButton(
                tooltip: 'Dismiss',
                onPressed: messenger.hideCurrentMaterialBanner,
                icon: Icon(Icons.close, size: 17, color: CartzyColors.readableOf(context, light: const Color(0xFF13845B))),
              ),
            ],
          ),
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.elevatedOf(context, light: const Color(0xFFFAF7F5)),
      body: Column(
        children: [
          RiderCommonAppBar(
            title: 'Items for Delivery',
            sector: null,
            onBack: widget.onBack,
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: _load,
              child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeaderStats(),
                  const SizedBox(height: 20),
                  if (_pickups.isNotEmpty) ...[
                    _buildPickupsSection(),
                    const SizedBox(height: 20),
                  ],
                  _buildSearchFilterBar(),
                  const SizedBox(height: 20),
                  _buildQueueContent(),
                ],
              ),
            ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPickupsSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Seller Pickups (${_pickups.length})',
          style: GoogleFonts.dmSans(
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: CartzyColors.textOf(context, light: const Color(0xFF261E30)),
          ),
        ),
        const SizedBox(height: 8),
        ..._pickups.map((p) => Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: CartzyColors.surfaceOf(context, light: Colors.white),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: CartzyColors.borderOf(context, light: const Color(0xFFE5DCDD))),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          p.sellerName,
                          style: GoogleFonts.dmSans(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            color: CartzyColors.textOf(context, light: const Color(0xFF261E30)),
                          ),
                        ),
                      ),
                      if (p.trackingCode != null)
                        Text(
                          p.trackingCode!,
                          style: GoogleFonts.dmSans(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w700,
                            color: CartzyColors.brandDark,
                          ),
                        ),
                    ],
                  ),
                  if (p.address != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      p.address!,
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        color: CartzyColors.mutedOf(context, light: const Color(0xFF6B7280)),
                      ),
                    ),
                  ],
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          '${p.parcelCount} parcel(s)${p.pickupDate != null ? ' · ${p.pickupDate}' : ''}',
                          style: GoogleFonts.dmSans(
                            fontSize: 11.5,
                            color: CartzyColors.mutedOf(context, light: const Color(0xFF6B7280)),
                          ),
                        ),
                      ),
                      if (p.trackingCode != null)
                        ElevatedButton(
                          onPressed: () async {
                            try {
                              final msg = await RiderDeliveryService.pickupFromSeller(p.trackingCode!);
                              if (!mounted) return;
                              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
                              _load();
                            } catch (e) {
                              if (!mounted) return;
                              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Pickup failed: $e')));
                            }
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: CartzyColors.brandDark,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          child: const Text('Pick Up', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                        ),
                    ],
                  ),
                ],
              ),
            )),
      ],
    );
  }

  Widget _buildHeaderStats() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isMobile = constraints.maxWidth < 650;

        final titleCol = Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Active Delivery Queue',
              style: GoogleFonts.cormorantGaramond(
                fontSize: 22,
                fontWeight: FontWeight.w700,
                color: CartzyColors.textOf(context, light: const Color(0xFF261E30)),
                letterSpacing: -0.4,
              ),
            ),
            const SizedBox(height: 3),
            Text(
              'Parcels your hub assigned to you. Start each one when you leave the hub.',
              style: GoogleFonts.dmSans(
                fontSize: 11,
                color: CartzyColors.mutedOf(context, light: const Color(0xFF8A7780)),
              ),
            ),
          ],
        );

        final badges = Wrap(
          spacing: 10,
          runSpacing: 8,
          children: [
            // Total in Route
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
              decoration: BoxDecoration(
                color: CartzyColors.surfaceOf(context, light: Colors.white),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: CartzyColors.borderOf(context, light: const Color(0xFFE5DCDD))),
              ),
              child: Text(
                'Total in Route: ${_orders.length}',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: CartzyColors.textOf(context, light: const Color(0xFF554B62)),
                ),
              ),
            ),
            // COD to Collect
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
              decoration: BoxDecoration(
                color: CartzyColors.elevatedOf(context, light: const Color(0xFFFFF9EE)),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: CartzyColors.borderOf(context, light: const Color(0xFFFDE6C5))),
              ),
              child: Text(
                'COD to Collect: ₱${_codTotal.toStringAsFixed(2)}',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: CartzyColors.readableOf(context, light: const Color(0xFFB45309), background: CartzyColors.elevatedOf(context, light: const Color(0xFFFFF9EE))),
                ),
              ),
            ),
          ],
        );

        if (isMobile) {
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              titleCol,
              const SizedBox(height: 14),
              badges,
            ],
          );
        }

        return Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            titleCol,
            badges,
          ],
        );
      },
    );
  }

  Widget _buildSearchFilterBar() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: CartzyColors.surfaceOf(context, light: Colors.white),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: CartzyColors.borderOf(context, light: const Color(0xFFEFE8EA))),
      ),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final isMobile = constraints.maxWidth < 600;

          final searchInput = Row(
            children: [
              const Icon(Icons.search, size: 18, color: Color(0xFFBAABB0)),
              const SizedBox(width: 8),
              Expanded(
                child: TextField(
                  controller: _searchController,
                  onChanged: (_) => setState(() {}),
                  decoration: InputDecoration(
                    hintText: 'Search tracking #, customer name, or order ref...',
                    hintStyle: GoogleFonts.dmSans(
                      fontSize: 13,
                      color: const Color(0xFFBAABB0),
                    ),
                    border: InputBorder.none,
                    enabledBorder: InputBorder.none,
                    focusedBorder: InputBorder.none,
                    disabledBorder: InputBorder.none,
                    errorBorder: InputBorder.none,
                    focusedErrorBorder: InputBorder.none,
                    isDense: true,
                    contentPadding: EdgeInsets.zero,
                  ),
                  style: GoogleFonts.dmSans(fontSize: 13.5, color: CartzyColors.textOf(context, light: const Color(0xFF261E30))),
                ),
              ),
              ElevatedButton(
                onPressed: () => setState(() {}),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF46333D),
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                ),
                child: Text(
                  'Search',
                  style: GoogleFonts.dmSans(fontSize: 12, fontWeight: FontWeight.w600),
                ),
              ),
            ],
          );

          final filterPills = Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildFilterPill('All'),
              const SizedBox(width: 6),
              _buildFilterPill('COD'),
              const SizedBox(width: 6),
              _buildFilterPill('Prepaid'),
            ],
          );

          if (isMobile) {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                searchInput,
                const SizedBox(height: 10),
                filterPills,
              ],
            );
          }

          return Row(
            children: [
              Expanded(child: searchInput),
              const SizedBox(width: 16),
              filterPills,
            ],
          );
        },
      ),
    );
  }

  Widget _buildFilterPill(String filter) {
    final isSelected = _activeFilter == filter;
    return InkWell(
      onTap: () => setState(() => _activeFilter = filter),
      borderRadius: BorderRadius.circular(16),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF46333D) : Colors.transparent,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Text(
          filter,
          style: GoogleFonts.dmSans(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
            color: isSelected ? Colors.white : CartzyColors.textOf(context, light: const Color(0xFF554B62)),
          ),
        ),
      ),
    );
  }

  Widget _buildQueueContent() {
    final orders = _filteredOrders;

    if (_loading && _orders.isEmpty) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 60),
        child: Center(child: CircularProgressIndicator(color: CartzyColors.readableOf(context, light: const Color(0xFF554B62)))),
      );
    }

    if (_error != null) {
      return Container(
        width: double.infinity,
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: CartzyColors.surfaceOf(context, light: Colors.white),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: CartzyColors.borderOf(context, light: const Color(0xFFEFE8EA))),
        ),
        child: Column(
          children: [
            Text("Can't load your deliveries",
                style: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w700, color: CartzyColors.textOf(context, light: const Color(0xFF261E30)))),
            const SizedBox(height: 6),
            Text(_error!, textAlign: TextAlign.center, style: GoogleFonts.dmSans(fontSize: 12, color: CartzyColors.mutedOf(context, light: const Color(0xFF8A7780)))),
            const SizedBox(height: 12),
            OutlinedButton(onPressed: _load, child: const Text('Try again')),
          ],
        ),
      );
    }

    if (orders.isEmpty) {
      return Container(
        width: double.infinity,
        height: 380,
        decoration: BoxDecoration(
          color: CartzyColors.surfaceOf(context, light: Colors.white),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: CartzyColors.borderOf(context, light: const Color(0xFFEFE8EA))),
        ),
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: CartzyColors.elevatedOf(context, light: const Color(0xFFF3ECEF)),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    Icons.check,
                    color: CartzyColors.mutedOf(context, light: const Color(0xFF865561)),
                    size: 24,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'No active parcels to deliver',
                  style: GoogleFonts.cormorantGaramond(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: CartzyColors.textOf(context, light: const Color(0xFF261E30)),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  _orders.isEmpty
                      ? 'Your hub has not assigned you any parcels. Pull down to refresh.'
                      : 'No parcels match your search.',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.dmSans(
                    fontSize: 13,
                    color: CartzyColors.mutedOf(context, light: const Color(0xFF8A7780)),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: orders.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (context, index) => _buildDeliveryCard(orders[index], index),
    );
  }

  Widget _buildDeliveryCard(RiderDelivery order, int index) {
    final isCod = order.isCod;
    final name = order.recipientName ?? 'Buyer';
    final phone = order.recipientPhone ?? '';
    final isMobile = MediaQuery.sizeOf(context).width < 600;
    final paymentBadge = Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: isCod ? CartzyColors.elevatedOf(context, light: const Color(0xFFFFF6E5)) : CartzyColors.elevatedOf(context, light: const Color(0xFFEAF9F2)),
        border: Border.all(color: isCod ? const Color(0xFFFFD98A) : const Color(0xFFA9E5CA)),
        borderRadius: BorderRadius.circular(5),
      ),
      child: Text(
        isCod ? 'COLLECT COD: ₱${order.codAmount.toStringAsFixed(2)}' : 'ONLINE PAID (₱0.00)',
        style: GoogleFonts.dmSans(
          fontSize: 9,
          fontWeight: FontWeight.w700,
          color: isCod ? CartzyColors.readableOf(context, light: const Color(0xFF9A5A00), background: isCod ? CartzyColors.elevatedOf(context, light: const Color(0xFFFFF6E5)) : CartzyColors.elevatedOf(context, light: const Color(0xFFEAF9F2))) : CartzyColors.readableOf(context, light: const Color(0xFF188257), background: isCod ? CartzyColors.elevatedOf(context, light: const Color(0xFFFFF6E5)) : CartzyColors.elevatedOf(context, light: const Color(0xFFEAF9F2))),
        ),
      ),
    );

    final details = Expanded(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Wrap(
            spacing: 7,
            runSpacing: 5,
            crossAxisAlignment: WrapCrossAlignment.center,
            children: [
              Text(order.trackingCode, style: GoogleFonts.dmSans(fontSize: 11, fontWeight: FontWeight.w700, color: CartzyColors.textOf(context, light: const Color(0xFF261E30)))),
              paymentBadge,
              _statusChip(order),
              if (order.orderReference != null)
                Text('Order: ${order.orderReference}', style: GoogleFonts.dmSans(fontSize: 10, color: CartzyColors.mutedOf(context, light: const Color(0xFF8A7780)))),
            ],
          ),
          const SizedBox(height: 5),
          Text(name, style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w700, color: CartzyColors.textOf(context, light: const Color(0xFF261E30)))),
          const SizedBox(height: 2),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(Icons.location_on_outlined, size: 13, color: CartzyColors.mutedOf(context, light: const Color(0xFF8A7780))),
              const SizedBox(width: 3),
              Expanded(child: Text(order.recipientAddress ?? 'No address on the order', style: GoogleFonts.dmSans(fontSize: 10, color: CartzyColors.textOf(context, light: const Color(0xFF554B62))))),
            ],
          ),
          const SizedBox(height: 3),
          Text(
            'Store: ${order.sellerName ?? '—'}  •  Items: ${order.itemsSummary ?? order.itemsCount}',
            maxLines: isMobile ? 2 : 1,
            overflow: TextOverflow.ellipsis,
            style: GoogleFonts.dmSans(fontSize: 9, color: CartzyColors.mutedOf(context, light: const Color(0xFF8A7780))),
          ),
        ],
      ),
    );

    final actions = Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        IconButton(
          tooltip: phone.isEmpty ? 'Phone number unavailable' : 'Call $name',
          onPressed: phone.isEmpty
              ? null
              : () => ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Contact $name: $phone')),
                  ),
          icon: const Icon(Icons.call_outlined, size: 17),
          style: IconButton.styleFrom(
            foregroundColor: CartzyColors.readableOf(context, light: const Color(0xFF554B62), background: CartzyColors.surfaceOf(context, light: const Color(0xFFFAF8FA))),
            backgroundColor: CartzyColors.surfaceOf(context, light: const Color(0xFFFAF8FA)),
            side: BorderSide(color: CartzyColors.borderOf(context, light: const Color(0xFFE7E2E8))),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          ),
        ),
        const SizedBox(width: 5),
        ElevatedButton.icon(
          onPressed: _startingId != null ? null : () => order.isOutForDelivery ? _deliver(order) : _start(order),
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF554B62),
            foregroundColor: Colors.white,
            elevation: 0,
            padding: EdgeInsets.symmetric(horizontal: isMobile ? 10 : 13, vertical: 9),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          ),
          icon: _startingId == order.id
              ? const SizedBox(width: 13, height: 13, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
              : Icon(order.isOutForDelivery ? Icons.check : Icons.local_shipping_outlined, size: 13),
          label: Text(order.isOutForDelivery ? 'Confirm Delivery' : 'Start Delivery', style: GoogleFonts.dmSans(fontSize: 10, fontWeight: FontWeight.w700)),
        ),
      ],
    );

    return Container(
      padding: EdgeInsets.symmetric(horizontal: 14, vertical: isMobile ? 13 : 12),
      decoration: BoxDecoration(
        color: CartzyColors.surfaceOf(context, light: Colors.white),
        borderRadius: BorderRadius.circular(13),
        border: Border.all(color: CartzyColors.borderOf(context, light: const Color(0xFFEFE8EA))),
        boxShadow: const [BoxShadow(color: Color(0x0A261E30), blurRadius: 8, offset: Offset(0, 2))],
      ),
      child: isMobile
          ? Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(children: [_deliveryIndexBadge(index), const SizedBox(width: 10), details]),
                const SizedBox(height: 10),
                Align(alignment: Alignment.centerRight, child: actions),
              ],
            )
          : Row(children: [_deliveryIndexBadge(index), const SizedBox(width: 10), details, const SizedBox(width: 12), actions]),
    );
  }

  Widget _statusChip(RiderDelivery order) {
    final out = order.isOutForDelivery;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: out ? CartzyColors.elevatedOf(context, light: const Color(0xFFEDE7F6)) : CartzyColors.elevatedOf(context, light: const Color(0xFFF3ECEF)),
        borderRadius: BorderRadius.circular(5),
      ),
      child: Text(
        out ? 'OUT FOR DELIVERY' : 'AT HUB · ASSIGNED',
        style: GoogleFonts.dmSans(fontSize: 9, fontWeight: FontWeight.w700, color: out ? CartzyColors.textOf(context, light: const Color(0xFF5A4A69)) : CartzyColors.mutedOf(context, light: const Color(0xFF865561))),
      ),
    );
  }

  Widget _deliveryIndexBadge(int index) => Container(
        width: 28,
        height: 28,
        alignment: Alignment.center,
        decoration: BoxDecoration(color: CartzyColors.elevatedOf(context, light: const Color(0xFFF3ECEF)), borderRadius: BorderRadius.circular(8)),
        child: Text('#${index + 1}', style: GoogleFonts.dmSans(fontSize: 10, fontWeight: FontWeight.w700, color: CartzyColors.textOf(context, light: const Color(0xFF554B62)))),
      );
}

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'rider_common_app_bar.dart';
import 'package:cartzy/services/mock_data.dart';
import 'rider_order_detail.dart';
import 'rider_delivery_process.dart';

class RiderDeliveryDashboard extends StatefulWidget {
  final String token;
  final VoidCallback onBack;

  const RiderDeliveryDashboard({
    super.key,
    required this.token,
    required this.onBack,
  });

  @override
  State<RiderDeliveryDashboard> createState() => _RiderDeliveryDashboardState();
}

class _RiderDeliveryDashboardState extends State<RiderDeliveryDashboard> {
  final TextEditingController _searchController = TextEditingController();
  String _activeFilter = 'All'; // 'All', 'COD', 'Prepaid'
  List<RiderOrder> _orders = [];

  @override
  void initState() {
    super.initState();
    // Default list can start empty or loaded from MockData
    _orders = List.from(MockData.activeDeliveries);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  List<RiderOrder> get _filteredOrders {
    final query = _searchController.text.trim().toLowerCase();
    return _orders.where((order) {
      final matchesQuery = query.isEmpty ||
          order.id.toString().contains(query) ||
          order.buyerName.toLowerCase().contains(query) ||
          order.deliveryAddress.toLowerCase().contains(query) ||
          order.itemsSummary.toLowerCase().contains(query);

      if (!matchesQuery) return false;

      if (_activeFilter == 'COD') {
        // Assume COD if totalAmount > 0
        return order.totalAmount > 0;
      } else if (_activeFilter == 'Prepaid') {
        return order.totalAmount == 0;
      }
      return true;
    }).toList();
  }

  double get _codTotal => _orders.fold(0.0, (sum, o) => sum + o.totalAmount);

  Future<void> _startDelivery(RiderOrder order) async {
    final delivered = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (context) => RiderDeliverProcess(order: order, token: widget.token),
      ),
    );
    if (delivered == true) {
      setState(() {
        _orders.removeWhere((o) => o.id == order.id);
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F5),
      body: Column(
        children: [
          RiderCommonAppBar(
            title: 'Items for Delivery',
            sector: 'Metro South Sector',
            onBack: widget.onBack,
          ),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeaderStats(),
                  const SizedBox(height: 20),
                  _buildSearchFilterBar(),
                  const SizedBox(height: 20),
                  _buildQueueContent(),
                ],
              ),
            ),
          ),
        ],
      ),
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
                fontSize: 26,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF261E30),
                letterSpacing: -0.4,
              ),
            ),
            const SizedBox(height: 3),
            Text(
              'Parcels out for doorstep delivery across your assigned sector',
              style: GoogleFonts.dmSans(
                fontSize: 13,
                color: const Color(0xFF8A7780),
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
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0xFFE5DCDD)),
              ),
              child: Text(
                'Total in Route: ${_orders.length}',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF554B62),
                ),
              ),
            ),
            // COD to Collect
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
              decoration: BoxDecoration(
                color: const Color(0xFFFFF9EE),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0xFFFDE6C5)),
              ),
              child: Text(
                'COD to Collect: ₱${_codTotal.toStringAsFixed(2)}',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFFB45309),
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
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFEFE8EA)),
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
                    isDense: true,
                    contentPadding: EdgeInsets.zero,
                  ),
                  style: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFF261E30)),
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
            color: isSelected ? Colors.white : const Color(0xFF554B62),
          ),
        ),
      ),
    );
  }

  Widget _buildQueueContent() {
    final orders = _filteredOrders;

    if (orders.isEmpty) {
      return Container(
        width: double.infinity,
        height: 380,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFEFE8EA)),
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
                  decoration: const BoxDecoration(
                    color: Color(0xFFF3ECEF),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.check,
                    color: Color(0xFF865561),
                    size: 24,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'No active parcels to deliver',
                  style: GoogleFonts.cormorantGaramond(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF261E30),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'All assigned packages are complete. Check the pickup section for new collections.',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.dmSans(
                    fontSize: 13,
                    color: const Color(0xFF8A7780),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    // Active parcels list
    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: orders.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final order = orders[index];
        return Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: const Color(0xFFEFE8EA)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF3ECEF),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      'TRACKING: #CRTZ-${order.id}',
                      style: GoogleFonts.dmSans(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF46333D),
                      ),
                    ),
                  ),
                  const Spacer(),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                    decoration: BoxDecoration(
                      color: order.totalAmount > 0
                          ? const Color(0xFFFFF9EE)
                          : const Color(0xFFEAF6ED),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      order.totalAmount > 0
                          ? 'COD: ₱${order.totalAmount.toStringAsFixed(2)}'
                          : 'PREPAID',
                      style: GoogleFonts.dmSans(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: order.totalAmount > 0
                            ? const Color(0xFFB45309)
                            : const Color(0xFF2E7D32),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                order.buyerName,
                style: GoogleFonts.dmSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF261E30),
                ),
              ),
              const SizedBox(height: 3),
              Row(
                children: [
                  const Icon(Icons.location_on_outlined, size: 14, color: Color(0xFF8A7780)),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      order.deliveryAddress,
                      style: GoogleFonts.dmSans(fontSize: 12.5, color: const Color(0xFF554B62)),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                'Items: ${order.itemsSummary}',
                style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF8A7780)),
              ),
              const SizedBox(height: 14),
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  ElevatedButton.icon(
                    onPressed: () => _startDelivery(order),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF46333D),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 9),
                    ),
                    icon: const Icon(Icons.local_shipping_outlined, size: 14),
                    label: Text(
                      'Start Delivery',
                      style: GoogleFonts.dmSans(fontSize: 12, fontWeight: FontWeight.w700),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }
}
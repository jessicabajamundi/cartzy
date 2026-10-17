import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'rider_common_app_bar.dart';
import 'rider_order_detail.dart';

class RiderDeliveryHistoryScreen extends StatefulWidget {
  final String token;
  final VoidCallback onBack;

  const RiderDeliveryHistoryScreen({
    super.key,
    required this.token,
    required this.onBack,
  });

  @override
  State<RiderDeliveryHistoryScreen> createState() => _RiderDeliveryHistoryScreenState();
}

class _RiderDeliveryHistoryScreenState extends State<RiderDeliveryHistoryScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _activeTimeframe = 'All'; // 'All', 'Today', '7 Days', 'This Month'
  List<RiderOrder> _completedOrders = [];

  @override
  void initState() {
    super.initState();
    // In screenshot it shows 0 drops / empty state
    _completedOrders = [];
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  List<RiderOrder> get _filteredOrders {
    final query = _searchController.text.trim().toLowerCase();
    if (query.isEmpty) return _completedOrders;
    return _completedOrders.where((order) {
      return order.id.toString().contains(query) ||
          order.buyerName.toLowerCase().contains(query) ||
          order.deliveryAddress.toLowerCase().contains(query);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F5),
      body: Column(
        children: [
          RiderCommonAppBar(
            title: 'Delivery History',
            sector: 'Metro South Sector',
            onBack: widget.onBack,
          ),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeaderAndSearch(),
                  const SizedBox(height: 20),
                  _buildStatCards(),
                  const SizedBox(height: 22),
                  _buildRecordsSectionHeader(),
                  const SizedBox(height: 12),
                  _buildRecordsContainer(),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeaderAndSearch() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isMobile = constraints.maxWidth < 750;

        final titleCol = Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Completed Deliveries',
              style: GoogleFonts.cormorantGaramond(
                fontSize: 26,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF261E30),
                letterSpacing: -0.4,
              ),
            ),
            const SizedBox(height: 3),
            Text(
              'Official log of your successfully completed drops and recipient signatures',
              style: GoogleFonts.dmSans(
                fontSize: 13,
                color: const Color(0xFF8A7780),
              ),
            ),
          ],
        );

        final searchAndFilters = Row(
          mainAxisSize: isMobile ? MainAxisSize.max : MainAxisSize.min,
          children: [
            // Search Input
            Container(
              width: isMobile ? 180 : 220,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFE5DCDD)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.search, size: 16, color: Color(0xFFBAABB0)),
                  const SizedBox(width: 6),
                  Expanded(
                    child: TextField(
                      controller: _searchController,
                      onChanged: (_) => setState(() {}),
                      decoration: InputDecoration(
                        hintText: 'Search tracking / recipient...',
                        hintStyle: GoogleFonts.dmSans(
                          fontSize: 11.5,
                          color: const Color(0xFFBAABB0),
                        ),
                        border: InputBorder.none,
                        isDense: true,
                        contentPadding: EdgeInsets.zero,
                      ),
                      style: GoogleFonts.dmSans(fontSize: 12),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),

            // Timeframe pills
            _buildTimeframePill('All'),
            const SizedBox(width: 4),
            _buildTimeframePill('Today'),
            const SizedBox(width: 4),
            _buildTimeframePill('7 Days'),
            const SizedBox(width: 4),
            _buildTimeframePill('This Month'),
          ],
        );

        if (isMobile) {
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              titleCol,
              const SizedBox(height: 14),
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: searchAndFilters,
              ),
            ],
          );
        }

        return Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            titleCol,
            searchAndFilters,
          ],
        );
      },
    );
  }

  Widget _buildTimeframePill(String label) {
    final isSelected = _activeTimeframe == label;
    return InkWell(
      onTap: () => setState(() => _activeTimeframe = label),
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF46333D) : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? const Color(0xFF46333D) : const Color(0xFFE5DCDD),
          ),
        ),
        child: Text(
          label,
          style: GoogleFonts.dmSans(
            fontSize: 11,
            fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
            color: isSelected ? Colors.white : const Color(0xFF554B62),
          ),
        ),
      ),
    );
  }

  Widget _buildStatCards() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isWide = constraints.maxWidth > 900;
        final cardWidth = isWide
            ? (constraints.maxWidth - 36) / 4
            : (constraints.maxWidth - 12) / 2;

        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            SizedBox(
              width: cardWidth,
              child: _buildSummaryCard(
                icon: Icons.check_circle_outline,
                iconColor: const Color(0xFF554B62),
                iconBg: const Color(0xFFF3ECEF),
                title: 'TOTAL DELIVERED',
                value: '${_completedOrders.length} drops',
              ),
            ),
            SizedBox(
              width: cardWidth,
              child: _buildSummaryCard(
                icon: Icons.attach_money,
                iconColor: const Color(0xFF2E7D32),
                iconBg: const Color(0xFFEAF6ED),
                title: 'TOTAL COD HANDLED',
                value: '₱0.00',
              ),
            ),
            SizedBox(
              width: cardWidth,
              child: _buildSummaryCard(
                icon: Icons.trending_up,
                iconColor: const Color(0xFFD97850),
                iconBg: const Color(0xFFFDF2EE),
                title: 'RIDER DROP PAYOUT',
                value: '₱0.00',
              ),
            ),
            SizedBox(
              width: cardWidth,
              child: _buildSummaryCard(
                icon: Icons.star_border,
                iconColor: const Color(0xFFE6A23C),
                iconBg: const Color(0xFFFFF7E8),
                title: 'SERVICE RATING',
                value: '5.00 ★',
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildSummaryCard({
    required IconData icon,
    required Color iconColor,
    required Color iconBg,
    required String title,
    required String value,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: iconBg,
              shape: BoxShape.circle,
            ),
            child: Icon(icon, size: 20, color: iconColor),
          ),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: GoogleFonts.dmSans(
                  fontSize: 10,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 0.5,
                  color: const Color(0xFF8A7780),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: GoogleFonts.dmSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF261E30),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildRecordsSectionHeader() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Row(
          children: [
            const Icon(Icons.history, size: 16, color: Color(0xFF8A7780)),
            const SizedBox(width: 6),
            Text(
              'Delivery Records (${_filteredOrders.length})',
              style: GoogleFonts.dmSans(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: const Color(0xFF554B62),
              ),
            ),
          ],
        ),
        Text(
          'Ordered by completion time',
          style: GoogleFonts.dmSans(
            fontSize: 12,
            color: const Color(0xFF8A7780),
          ),
        ),
      ],
    );
  }

  Widget _buildRecordsContainer() {
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
                    color: Color(0xFFFBF8F9),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.description_outlined,
                    color: Color(0xFFBAABB0),
                    size: 26,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'No delivery history records found',
                  style: GoogleFonts.cormorantGaramond(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF261E30),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'You haven\'t completed any deliveries yet for this timeframe. Once you\nmark items as delivered, they will be archived here.',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.dmSans(
                    fontSize: 13,
                    color: const Color(0xFF8A7780),
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    // If records exist
    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: orders.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (context, index) {
        final order = orders[index];
        return Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFEFE8EA)),
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'CRTZ-${order.id}  •  ${order.buyerName}',
                      style: GoogleFonts.dmSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF261E30),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      order.deliveryAddress,
                      style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF8A7780)),
                    ),
                  ],
                ),
              ),
              Text(
                'Delivered',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF2E7D32),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
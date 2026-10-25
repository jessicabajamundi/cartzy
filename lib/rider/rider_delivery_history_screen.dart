import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/services/mock_data.dart';
import 'rider_common_app_bar.dart';
import 'rider_order_detail.dart';

class RiderDeliveryHistoryScreen extends StatefulWidget {
  final String token;
  final VoidCallback? onBack;
  final VoidCallback? onLogout;

  const RiderDeliveryHistoryScreen({
    super.key,
    required this.token,
    this.onBack,
    this.onLogout,
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
    _completedOrders = List<RiderOrder>.from(MockData.completedDeliveries);
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
            sector: 'Metro South (Laguna & NCR)',
            onBack: widget.onBack ?? () => Navigator.of(context).maybePop(),
            onLogout: widget.onLogout,
          ),
          Expanded(
            child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeaderAndSearch(),
                  const SizedBox(height: 14),
                  _buildStatCards(),
                  const SizedBox(height: 16),
                  _buildRecordsSectionHeader(),
                  const SizedBox(height: 8),
                  _buildHistoryRecords(),
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
                fontSize: 20,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF261E30),
                letterSpacing: -0.4,
              ),
            ),
            const SizedBox(height: 3),
            Text(
              'Official log of your successfully completed drops and recipient signatures',
              style: GoogleFonts.dmSans(
                fontSize: 10,
                color: const Color(0xFF8A7780),
              ),
            ),
          ],
        );

        final searchInput = Container(
              width: isMobile ? constraints.maxWidth : 240,
              height: 42,
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
                        hintText: 'Search tracking or recipient',
                        hintStyle: GoogleFonts.dmSans(
                          fontSize: 12,
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
                      style: GoogleFonts.dmSans(fontSize: 13),
                    ),
                  ),
                ],
              ),
            );

        final timeframeFilters = Row(
          mainAxisSize: MainAxisSize.min,
          children: [
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
              searchInput,
              const SizedBox(height: 10),
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: timeframeFilters,
              ),
            ],
          );
        }

        return Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            titleCol,
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                searchInput,
                const SizedBox(width: 8),
                timeframeFilters,
              ],
            ),
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
                value: _formatPeso(_completedOrders
                    .where((order) => order.paymentMethod.toUpperCase() == 'COD')
                    .fold<double>(0, (sum, order) => sum + order.totalAmount)),
              ),
            ),
            SizedBox(
              width: cardWidth,
              child: _buildSummaryCard(
                icon: Icons.trending_up,
                iconColor: const Color(0xFFD97850),
                iconBg: const Color(0xFFFDF2EE),
                title: 'RIDER DROP PAYOUT',
                value: _formatPeso(_completedOrders.fold<double>(0, (sum, order) => sum + order.riderPayout)),
              ),
            ),
            SizedBox(
              width: cardWidth,
              child: _buildSummaryCard(
                icon: Icons.star_border,
                iconColor: const Color(0xFFE6A23C),
                iconBg: const Color(0xFFFFF7E8),
                title: 'SERVICE RATING',
                value: '4.95 ★',
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
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      child: Row(
        children: [
          Container(
            width: 28,
            height: 28,
            decoration: BoxDecoration(
              color: iconBg,
              shape: BoxShape.circle,
            ),
            child: Icon(icon, size: 16, color: iconColor),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: GoogleFonts.dmSans(
                  fontSize: 8,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 0.5,
                  color: const Color(0xFF8A7780),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF261E30),
                ),
              ),
            ],
          ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecordsSectionHeader() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Flexible(
          child: Row(
            children: [
              const Icon(Icons.history, size: 16, color: Color(0xFF8A7780)),
              const SizedBox(width: 6),
              Flexible(
                child: Text(
                  'Delivery Records (${_filteredOrders.length})',
                  style: GoogleFonts.dmSans(
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF554B62),
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(width: 8),
        Flexible(
          child: Text(
            'Ordered by completion time',
            textAlign: TextAlign.end,
            style: GoogleFonts.dmSans(
              fontSize: 9,
              color: const Color(0xFF8A7780),
            ),
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

  String _formatPeso(num amount) => '₱${amount.toStringAsFixed(2)}';

  Widget _buildHistoryRecords() {
    final orders = _filteredOrders;
    return LayoutBuilder(
      builder: (context, constraints) {
        final mobile = constraints.maxWidth < 760;
        return Container(
          width: double.infinity,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: const Color(0xFFEFE8EA)),
          ),
          child: orders.isEmpty
              ? Padding(
                  padding: const EdgeInsets.all(24),
                  child: Center(
                    child: Text(
                      'No completed deliveries found.',
                      style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF8A7780)),
                    ),
                  ),
                )
              : mobile
                  ? Column(
                      children: [
                        for (var i = 0; i < orders.length; i++)
                          _buildMobileHistoryRecord(orders[i], i),
                      ],
                    )
                  : SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: SizedBox(
                        width: constraints.maxWidth,
                        child: Column(
                          children: [
                            _buildHistoryTableHeader(),
                            for (var i = 0; i < orders.length; i++)
                              _buildHistoryTableRecord(orders[i], i),
                          ],
                        ),
                      ),
                    ),
        );
      },
    );
  }

  Widget _buildHistoryTableHeader() {
    const style = TextStyle(
      fontSize: 9,
      fontWeight: FontWeight.w600,
      color: Color(0xFF554B62),
    );
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
      color: const Color(0xFFFAF8FA),
      child: const Row(
        children: [
          Expanded(flex: 3, child: Text('Tracking / Waybill', style: style)),
          Expanded(flex: 6, child: Text('Recipient & Destination', style: style)),
          Expanded(flex: 3, child: Text('Store Partner', style: style)),
          Expanded(flex: 3, child: Text('Date & Time Completed', style: style)),
          Expanded(flex: 3, child: Text('Payment Method', style: style)),
          Expanded(flex: 2, child: Text('Rider Payout', style: style, textAlign: TextAlign.right)),
        ],
      ),
    );
  }

  Widget _buildHistoryTableRecord(RiderOrder order, int index) {
    final completedAt = order.completedAt.isNotEmpty ? order.completedAt : order.createdAt;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: const BoxDecoration(
        border: Border(top: BorderSide(color: Color(0xFFF0EBEF))),
      ),
      child: Row(
        children: [
          Expanded(
            flex: 3,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('CTZY-RD-${order.id}', style: _historyPrimary),
                Text('Ref: ORD-MTR-${order.id}', style: _historySecondary),
              ],
            ),
          ),
          Expanded(
            flex: 6,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(order.buyerName, style: _historyPrimary),
                Text(order.deliveryAddress, maxLines: 1, overflow: TextOverflow.ellipsis, style: _historySecondary),
                const Text('✓ Delivered successfully. Barcode verified by rider.', style: _historySuccess),
              ],
            ),
          ),
          Expanded(
            flex: 3,
            child: Align(
              alignment: Alignment.centerLeft,
              child: _historyStoreBadge(order.storePartner),
            ),
          ),
          Expanded(
            flex: 3,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(_formatHistoryDate(completedAt), style: _historyPrimary),
                Text(_formatHistoryTime(completedAt), style: _historySecondary),
              ],
            ),
          ),
          Expanded(
            flex: 3,
            child: Align(
              alignment: Alignment.centerLeft,
              child: _historyPaymentBadge(order),
            ),
          ),
          Expanded(
            flex: 2,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text('+${_formatPeso(order.riderPayout)}', style: _historyPrimary),
                const Text('Credited', style: _historySuccess),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMobileHistoryRecord(RiderOrder order, int index) {
    final completedAt = order.completedAt.isNotEmpty ? order.completedAt : order.createdAt;
    final mobilePrimary = _historyPrimary.copyWith(fontSize: 13);
    final mobileSecondary = _historySecondary.copyWith(fontSize: 11);
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: const BoxDecoration(
        border: Border(top: BorderSide(color: Color(0xFFF0EBEF))),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(child: Text('CTZY-RD-${order.id}', style: mobilePrimary)),
              _historyPaymentBadge(order),
            ],
          ),
          Text('Ref: ORD-MTR-${order.id}', style: mobileSecondary),
          const SizedBox(height: 8),
          Text(order.buyerName, style: mobilePrimary),
          Text(order.deliveryAddress, style: mobileSecondary),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  '${_formatHistoryDate(completedAt)} · ${_formatHistoryTime(completedAt)}',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: mobileSecondary,
                ),
              ),
              Text(
                '+${_formatPeso(order.riderPayout)} credited',
                style: _historySuccess.copyWith(fontSize: 10),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _historyStoreBadge(String storeName) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
        decoration: BoxDecoration(
          color: const Color(0xFFF7F2F8),
          borderRadius: BorderRadius.circular(5),
        ),
        child: Text(storeName, style: GoogleFonts.dmSans(fontSize: 8, color: const Color(0xFF554B62))),
      );

  Widget _historyPaymentBadge(RiderOrder order) {
    final isCod = order.paymentMethod.toUpperCase() == 'COD';
    return Container(
        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
        decoration: BoxDecoration(
          color: isCod ? const Color(0xFFEFFAF4) : const Color(0xFFEFF4FF),
          border: Border.all(color: isCod ? const Color(0xFFB9E8D0) : const Color(0xFFD4E0FF)),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Text(
          isCod ? '✓ COD ${_formatPeso(order.totalAmount)}' : 'PREPAID',
          style: GoogleFonts.dmSans(
            fontSize: 8,
            fontWeight: FontWeight.w700,
            color: isCod ? const Color(0xFF21845A) : const Color(0xFF4269BD),
          ),
        ),
      );
  }

  String _formatHistoryDate(String raw) {
    if (raw.length < 10) return raw.isEmpty ? 'Completed' : raw;
    final date = DateTime.tryParse(raw.substring(0, 10));
    if (date == null) return raw;
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return '${months[date.month - 1]} ${date.day}, ${date.year}';
  }

  String _formatHistoryTime(String raw) => raw.length > 11 ? raw.substring(11) : 'Completed';

  static const _historyPrimary = TextStyle(
    fontSize: 9,
    fontWeight: FontWeight.w700,
    color: Color(0xFF261E30),
  );
  static const _historySecondary = TextStyle(fontSize: 8, color: Color(0xFF8A7780));
  static const _historySuccess = TextStyle(
    fontSize: 7,
    color: Color(0xFF21845A),
    fontStyle: FontStyle.italic,
  );
}

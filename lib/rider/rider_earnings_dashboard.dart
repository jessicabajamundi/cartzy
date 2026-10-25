import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/services/mock_data.dart';
import 'rider_common_app_bar.dart';
import 'rider_order_detail.dart';

class RiderEarningsScreen extends StatefulWidget {
  final String token;
  final VoidCallback onBack;

  const RiderEarningsScreen({
    super.key,
    required this.token,
    required this.onBack,
  });

  @override
  State<RiderEarningsScreen> createState() => _RiderEarningsScreenState();
}

class _RiderEarningsScreenState extends State<RiderEarningsScreen> {
  Set<int>? _remittedOrderIds;

  Set<int> get _remittedOrderIdsOrCreate => _remittedOrderIds ??= <int>{};

  List<RiderOrder> get _records {
    final records = List<RiderOrder>.from(MockData.completedDeliveries);
    records.sort((first, second) {
      final firstDate = _completedDate(first) ?? DateTime(1970);
      final secondDate = _completedDate(second) ?? DateTime(1970);
      return secondDate.compareTo(firstDate);
    });
    return records;
  }

  double get _codCashOnHand => _records
      .where((order) => order.paymentMethod.toUpperCase() == 'COD' && !_remittedOrderIdsOrCreate.contains(order.id))
      .fold<double>(0, (sum, order) => sum + order.totalAmount);

  DateTime? _completedDate(RiderOrder order) {
    final raw = order.completedAt.isNotEmpty ? order.completedAt : order.createdAt;
    if (raw.length < 10) return null;
    final parsed = DateTime.tryParse(raw);
    if (parsed != null) return parsed;
    final date = DateTime.tryParse(raw.substring(0, 10));
    if (date == null) return null;
    final time = RegExp(r'(\d{1,2}):(\d{2})\s*(AM|PM)', caseSensitive: false)
        .firstMatch(raw.substring(10));
    if (time == null) return date;
    var hour = int.parse(time.group(1)!);
    final minute = int.parse(time.group(2)!);
    final isPm = time.group(3)!.toUpperCase() == 'PM';
    if (isPm && hour < 12) hour += 12;
    if (!isPm && hour == 12) hour = 0;
    return DateTime(date.year, date.month, date.day, hour, minute);
  }

  bool _sameDay(DateTime first, DateTime second) =>
      first.year == second.year && first.month == second.month && first.day == second.day;

  List<RiderOrder> _inRange(DateTime start, DateTime end) => _records.where((order) {
        final date = _completedDate(order);
        return date != null && !date.isBefore(start) && !date.isAfter(end);
      }).toList();

  double _payoutTotal(Iterable<RiderOrder> orders) =>
      orders.fold<double>(0, (sum, order) => sum + order.riderPayout);

  List<RiderOrder> get _lastSevenDays {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    return _inRange(
      today.subtract(const Duration(days: 6)),
      today.add(const Duration(days: 1)).subtract(const Duration(microseconds: 1)),
    );
  }

  List<RiderOrder> get _thisMonth {
    final now = DateTime.now();
    return _inRange(
      DateTime(now.year, now.month),
      DateTime(now.year, now.month + 1).subtract(const Duration(microseconds: 1)),
    );
  }

  List<RiderOrder> get _today {
    final now = DateTime.now();
    return _records.where((order) {
      final date = _completedDate(order);
      return date != null && _sameDay(date, now);
    }).toList();
  }

  List<double> get _dailyPayouts {
    final today = DateTime.now();
    return List.generate(7, (index) {
      final day = DateTime(today.year, today.month, today.day).subtract(Duration(days: 6 - index));
      return _payoutTotal(_records.where((order) {
        final date = _completedDate(order);
        return date != null && _sameDay(date, day);
      }));
    });
  }

  List<int> get _dailyDeliveries {
    final today = DateTime.now();
    return List.generate(7, (index) {
      final day = DateTime(today.year, today.month, today.day).subtract(Duration(days: 6 - index));
      return _records.where((order) {
        final date = _completedDate(order);
        return date != null && _sameDay(date, day);
      }).length;
    });
  }

  String _formatPeso(num amount) => '₱${amount.toStringAsFixed(2)}';

  double get _chartScale {
    final payouts = _dailyPayouts.fold<double>(0, (maxValue, value) => math.max(maxValue, value).toDouble());
    final deliveries = _dailyDeliveries.fold<int>(0, (maxValue, value) => math.max(maxValue, value).toInt()) * 65.0;
    final peak = math.max(payouts, deliveries);
    return math.max(100, (peak / 100).ceil() * 100).toDouble();
  }

  String _chartDayLabel(int index) {
    final today = DateTime.now();
    final day = DateTime(today.year, today.month, today.day)
        .subtract(Duration(days: 6 - index));
    const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    return '${weekdays[day.weekday - 1]} ${day.day}';
  }

  String _chartAxisLabel(int index) =>
      '₱${(_chartScale * (5 - index) / 5).round()}';

  String _formatCompletedDate(RiderOrder order) {
    final date = _completedDate(order);
    if (date == null) return '—';
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var hour = date.hour;
    final minute = date.minute.toString().padLeft(2, '0');
    final period = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12;
    if (hour == 0) hour = 12;
    return '${months[date.month - 1]} ${date.day}, ${date.year} $hour:$minute $period';
  }

  // Remittance dialog
  void _openRemittanceDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.receipt_long_outlined, color: Color(0xFF46333D)),
            const SizedBox(width: 8),
            Text(
              'Log COD Remittance',
              style: GoogleFonts.cormorantGaramond(
                fontWeight: FontWeight.bold,
                fontSize: 20,
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Remit collected cash on hand to Metro South Sorting & Fulfillment Hub cashier.',
              style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF554B62)),
            ),
            const SizedBox(height: 14),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFFAF7F5),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFEFE8EA)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Current COD on Hand:', style: GoogleFonts.dmSans(fontSize: 12.5)),
                  Text(
                    _formatPeso(_codCashOnHand),
                    style: GoogleFonts.dmSans(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFFB45309),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Close'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF46333D),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () {
              final orderIdsToRemit = _records
                  .where((order) => order.paymentMethod.toUpperCase() == 'COD' && !_remittedOrderIdsOrCreate.contains(order.id))
                  .map((order) => order.id)
                  .toList();
              final amount = _codCashOnHand;
              if (amount > 0) {
                setState(() => _remittedOrderIdsOrCreate.addAll(orderIdsToRemit));
              }
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(
                    amount > 0
                        ? 'COD remittance of ${_formatPeso(amount)} logged.'
                        : 'No pending COD cash on hand to remit.',
                  ),
                  backgroundColor: const Color(0xFF46333D),
                ),
              );
            },
            child: const Text('Confirm Remittance'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F5),
      body: Column(
        children: [
          RiderCommonAppBar(
            title: 'Profit & Earnings Dashboard',
            sector: 'Metro South (Laguna & NCR)',
            onBack: widget.onBack,
          ),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeaderActions(),
                  const SizedBox(height: 20),
                  _buildStatCards(),
                  const SizedBox(height: 20),
                  _buildTrendAndCommissionSection(),
                  const SizedBox(height: 20),
                  _buildRecentPayoutTransactions(),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeaderActions() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isMobile = constraints.maxWidth < 650;

        final titleCol = Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Rider Earnings & Commissions',
              style: GoogleFonts.cormorantGaramond(
                fontSize: 22,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF261E30),
                letterSpacing: -0.4,
              ),
            ),
            const SizedBox(height: 3),
            Text(
              'Track delivery payouts, COD cash remittances, and performance metrics',
              style: GoogleFonts.dmSans(
                fontSize: 11,
                color: const Color(0xFF8A7780),
              ),
            ),
          ],
        );

        final btn = ElevatedButton.icon(
          onPressed: _openRemittanceDialog,
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF46333D),
            foregroundColor: Colors.white,
            elevation: 0,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          ),
          icon: const Icon(Icons.receipt_long_outlined, size: 15),
          label: Text(
            'Log COD Remittance',
            style: GoogleFonts.dmSans(
              fontSize: 12.5,
              fontWeight: FontWeight.w600,
            ),
          ),
        );

        if (isMobile) {
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              titleCol,
              const SizedBox(height: 12),
              btn,
            ],
          );
        }

        return Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            titleCol,
            btn,
          ],
        );
      },
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
              child: _buildMetricCard(
                title: "TODAY'S PAYOUT",
                pillLabel: '₱',
                amount: _formatPeso(_payoutTotal(_today)),
                subtitle: '${_today.length} completed deliveries',
              ),
            ),
            SizedBox(
              width: cardWidth,
              child: _buildMetricCard(
                title: 'THIS WEEK (7 DAYS)',
                pillLabel: '7D',
                amount: _formatPeso(_payoutTotal(_lastSevenDays)),
                subtitle: '${_lastSevenDays.length} deliveries',
              ),
            ),
            SizedBox(
              width: cardWidth,
              child: _buildMetricCard(
                title: 'THIS MONTH',
                pillLabel: 'MO',
                amount: _formatPeso(_payoutTotal(_thisMonth)),
                subtitle: '${_thisMonth.length} deliveries',
              ),
            ),
            SizedBox(
              width: cardWidth,
              child: _buildMetricCard(
                title: 'COD CASH ON HAND',
                pillLabel: 'COD',
                pillColor: const Color(0xFFFFF9EE),
                pillTextColor: const Color(0xFFB45309),
                amount: _formatPeso(_codCashOnHand),
                subtitle: 'Pending hub remittance',
                subtitleColor: const Color(0xFFB45309),
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildMetricCard({
    required String title,
    required String pillLabel,
    Color pillColor = const Color(0xFFFBF8F9),
    Color pillTextColor = const Color(0xFF8A7780),
    required String amount,
    required String subtitle,
    Color subtitleColor = const Color(0xFF8A7780),
  }) {
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
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            runSpacing: 8,
            children: [
              Text(
                title,
                style: GoogleFonts.dmSans(
                  fontSize: 10.5,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 0.5,
                  color: const Color(0xFF8A7780),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: pillColor,
                  borderRadius: BorderRadius.circular(4),
                  border: Border.all(color: const Color(0xFFEFE8EA)),
                ),
                child: Text(
                  pillLabel,
                  style: GoogleFonts.dmSans(
                    fontSize: 9.5,
                    fontWeight: FontWeight.w700,
                    color: pillTextColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            amount,
            style: GoogleFonts.cormorantGaramond(
              fontSize: 24,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF261E30),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: GoogleFonts.dmSans(
              fontSize: 11.5,
              color: subtitleColor,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTrendAndCommissionSection() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isMobile = constraints.maxWidth < 850;

        if (isMobile) {
          return Column(
            children: [
              _buildTrendChartCard(),
              const SizedBox(height: 16),
              _buildCommissionStructureCard(),
            ],
          );
        }

        return Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(flex: 7, child: _buildTrendChartCard()),
            const SizedBox(width: 16),
            Expanded(flex: 4, child: _buildCommissionStructureCard()),
          ],
        );
      },
    );
  }

  Widget _buildTrendChartCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Wrap(
            alignment: WrapAlignment.spaceBetween,
            runSpacing: 8,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '7-Day Delivery & Profit Trend',
                    style: GoogleFonts.cormorantGaramond(
                      fontSize: 19,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF261E30),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    'Completed packages per day and earned commissions',
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      color: const Color(0xFF8A7780),
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFFAF7F5),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFEFE8EA)),
                ),
                child: Text(
                  'Standard ₱65/drop',
                  style: GoogleFonts.dmSans(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF554B62),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Legend
          Wrap(
            alignment: WrapAlignment.center,
            crossAxisAlignment: WrapCrossAlignment.center,
            runSpacing: 6,
            children: [
              Container(
                width: 12,
                height: 12,
                decoration: BoxDecoration(
                  color: const Color(0xFF554B62),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 6),
              Text(
                'Profit Earned (₱)',
                style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFF554B62)),
              ),
              const SizedBox(width: 20),
              Container(
                width: 12,
                height: 12,
                decoration: BoxDecoration(
                  color: const Color(0xFFB87364),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(width: 6),
              Text(
                'Deliveries Completed',
                style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFF554B62)),
              ),
            ],
          ),

          const SizedBox(height: 20),

          // Chart Display
          SizedBox(
            height: 180,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Y-axis labels
                Column(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: List.generate(
                    6,
                    (index) => Text(
                      _chartAxisLabel(index),
                      style: const TextStyle(fontSize: 8, color: Color(0xFF8A7780)),
                    ),
                  ),
                ),
                const SizedBox(width: 8),

                // Chart Grid & Bars
                Expanded(
                  child: Column(
                    children: [
                      Expanded(
                        child: LayoutBuilder(
                          builder: (context, constraints) {
                            final chartHeight = constraints.maxHeight;
                            return Stack(
                              fit: StackFit.expand,
                              children: [
                                Column(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: List.generate(
                                    6,
                                    (_) => Container(height: 1, color: const Color(0xFFEFE8EA)),
                                  ),
                                ),
                                Row(
                                  crossAxisAlignment: CrossAxisAlignment.stretch,
                                  children: List.generate(7, (index) {
                                    final payoutHeight = (_dailyPayouts[index] / _chartScale * chartHeight)
                                        .clamp(0.0, chartHeight)
                                        .toDouble();
                                    final deliveryHeight = (_dailyDeliveries[index] * 65 / _chartScale * chartHeight)
                                        .clamp(0.0, chartHeight)
                                        .toDouble();
                                    return Expanded(
                                      child: Align(
                                        alignment: Alignment.bottomCenter,
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          crossAxisAlignment: CrossAxisAlignment.end,
                                          children: [
                                            Container(
                                              width: 9,
                                              height: payoutHeight,
                                              decoration: const BoxDecoration(
                                                color: Color(0xFF756487),
                                                borderRadius: BorderRadius.vertical(top: Radius.circular(3)),
                                              ),
                                            ),
                                            const SizedBox(width: 2),
                                            Container(
                                              width: 9,
                                              height: deliveryHeight,
                                              decoration: const BoxDecoration(
                                                color: Color(0xFFBD8376),
                                                borderRadius: BorderRadius.vertical(top: Radius.circular(3)),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    );
                                  }),
                                ),
                              ],
                            );
                          },
                        ),
                      ),
                      SizedBox(height: 8),
                      // X-axis days
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceAround,
                        children: List.generate(
                          7,
                          (index) => Flexible(
                            child: Text(
                              _chartDayLabel(index),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              textAlign: TextAlign.center,
                              style: GoogleFonts.dmSans(fontSize: 7, color: const Color(0xFF8A7780)),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCommissionStructureCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Commission Structure',
            style: GoogleFonts.cormorantGaramond(
              fontSize: 19,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF261E30),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            'Transparent compensation model',
            style: GoogleFonts.dmSans(
              fontSize: 12,
              color: const Color(0xFF8A7780),
            ),
          ),
          const SizedBox(height: 16),

          _buildCommissionBox(
            title: 'STANDARD PARCEL DROP',
            value: '₱65.00 per completed drop',
          ),
          const SizedBox(height: 10),
          _buildCommissionBox(
            title: 'COD INCENTIVE',
            value: '+100% remitted intact to hub',
          ),
          const SizedBox(height: 10),
          _buildCommissionBox(
            title: 'LOGISTICS HUB PARTNER',
            value: 'Metro South Sorting & Fulfillment Hub',
          ),
          const SizedBox(height: 14),
          Text(
            '* Payouts are credited directly to your registered courier account or remitted via hub cashier weekly.',
            style: GoogleFonts.dmSans(
              fontSize: 11,
              fontStyle: FontStyle.italic,
              color: const Color(0xFF8A7780),
              height: 1.3,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCommissionBox({required String title, required String value}) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
      decoration: BoxDecoration(
        color: const Color(0xFFFBF8F9),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      child: Column(
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
          const SizedBox(height: 3),
          Text(
            value,
            style: GoogleFonts.dmSans(
              fontSize: 12.5,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF46333D),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecentPayoutTransactions() {
    final isMobile = MediaQuery.sizeOf(context).width < 650;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Recent Payout Transactions',
                    style: GoogleFonts.cormorantGaramond(
                      fontSize: 19,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF261E30),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    'Itemized commission per delivered parcel',
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      color: const Color(0xFF8A7780),
                    ),
                  ),
                ],
              ),
              ),
              const SizedBox(width: 8),
              Text(
                '${_records.length} Records',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  color: const Color(0xFF8A7780),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          if (isMobile) ...[
            for (final order in _records.take(5)) _buildMobilePayoutCard(order),
            if (_records.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 28),
                child: Center(child: Text('No completed payouts recorded yet.')),
              ),
          ] else ...[
          // Table Header
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: const BoxDecoration(
              color: Color(0xFFFBF8F9),
              border: Border(
                top: BorderSide(color: Color(0xFFEFE8EA)),
                bottom: BorderSide(color: Color(0xFFEFE8EA)),
              ),
            ),
            child: Row(
              children: [
                Expanded(
                  flex: 3,
                  child: Text(
                    'TRACKING CODE',
                    style: GoogleFonts.dmSans(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF8A7780),
                    ),
                  ),
                ),
                Expanded(
                  flex: 4,
                  child: Text(
                    'RECIPIENT / AREA',
                    style: GoogleFonts.dmSans(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF8A7780),
                    ),
                  ),
                ),
                Expanded(
                  flex: 2,
                  child: Text(
                    'PAYMENT',
                    style: GoogleFonts.dmSans(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF8A7780),
                    ),
                  ),
                ),
                Expanded(
                  flex: 3,
                  child: Text(
                    'DELIVERED TIME',
                    style: GoogleFonts.dmSans(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF8A7780),
                    ),
                  ),
                ),
                Expanded(
                  flex: 2,
                  child: Text(
                    'RIDER COMMISSION',
                    textAlign: TextAlign.right,
                    style: GoogleFonts.dmSans(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF8A7780),
                    ),
                  ),
                ),
              ],
            ),
          ),

          if (_records.isEmpty)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 40),
              child: Center(
                child: Text(
                  'No completed payouts recorded yet.',
                  style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFFBAABB0)),
                ),
              ),
            )
          else
            for (final order in _records.take(5)) _buildPayoutTableRow(order),
          ],
        ],
      ),
    );
  }

  Widget _buildPayoutTableRow(RiderOrder order) {
    final isCod = order.paymentMethod.toUpperCase() == 'COD';
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
      decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: Color(0xFFF0EBEF)))),
      child: Row(
        children: [
          Expanded(flex: 3, child: Text('CTZY-RD-${order.id}', style: GoogleFonts.dmSans(fontSize: 10, fontWeight: FontWeight.w700, color: const Color(0xFF46333D)))),
          Expanded(
            flex: 4,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(order.buyerName, maxLines: 1, overflow: TextOverflow.ellipsis, style: GoogleFonts.dmSans(fontSize: 10, fontWeight: FontWeight.w600)),
                Text(order.deliveryAddress, maxLines: 1, overflow: TextOverflow.ellipsis, style: GoogleFonts.dmSans(fontSize: 8, color: const Color(0xFF8A7780))),
              ],
            ),
          ),
          Expanded(flex: 2, child: _paymentBadge(isCod)),
          Expanded(flex: 3, child: Text(_formatCompletedDate(order), style: GoogleFonts.dmSans(fontSize: 9, color: const Color(0xFF554B62)))),
          Expanded(
            flex: 2,
            child: Text('+${_formatPeso(order.riderPayout)}', textAlign: TextAlign.right, style: GoogleFonts.dmSans(fontSize: 10, fontWeight: FontWeight.w700, color: const Color(0xFF168456))),
          ),
        ],
      ),
    );
  }

  Widget _buildMobilePayoutCard(RiderOrder order) {
    final isCod = order.paymentMethod.toUpperCase() == 'COD';
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 12),
      decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: Color(0xFFF0EBEF)))),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(child: Text('CTZY-RD-${order.id}', style: GoogleFonts.dmSans(fontSize: 11, fontWeight: FontWeight.w700, color: const Color(0xFF46333D)))),
              _paymentBadge(isCod),
            ],
          ),
          const SizedBox(height: 6),
          Text(order.buyerName, style: GoogleFonts.dmSans(fontSize: 12, fontWeight: FontWeight.w600)),
          Text(order.deliveryAddress, style: GoogleFonts.dmSans(fontSize: 10, color: const Color(0xFF8A7780))),
          const SizedBox(height: 5),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(_formatCompletedDate(order), style: GoogleFonts.dmSans(fontSize: 9, color: const Color(0xFF554B62))),
              Text('+${_formatPeso(order.riderPayout)}', style: GoogleFonts.dmSans(fontSize: 11, fontWeight: FontWeight.w700, color: const Color(0xFF168456))),
            ],
          ),
        ],
      ),
    );
  }

  Widget _paymentBadge(bool isCod) => Align(
        alignment: Alignment.centerLeft,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
          decoration: BoxDecoration(
            color: isCod ? const Color(0xFFFFF5E5) : const Color(0xFFE8F8F0),
            borderRadius: BorderRadius.circular(4),
          ),
          child: Text(
            isCod ? 'COD Collected' : 'Prepaid',
            style: GoogleFonts.dmSans(fontSize: 8, fontWeight: FontWeight.w700, color: isCod ? const Color(0xFF9A5A00) : const Color(0xFF168456)),
          ),
        ),
      );
}

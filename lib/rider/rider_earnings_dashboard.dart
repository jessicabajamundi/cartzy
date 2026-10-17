import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'rider_common_app_bar.dart';

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
                    '₱0.00',
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
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('No pending COD cash on hand to remit.'),
                  backgroundColor: Color(0xFF46333D),
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
            title: 'Rider Earnings & Commissions',
            sector: 'Metro South Sector',
            onBack: widget.onBack,
          ),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
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
                fontSize: 26,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF261E30),
                letterSpacing: -0.4,
              ),
            ),
            const SizedBox(height: 3),
            Text(
              'Track delivery payouts, COD cash remittances, and performance metrics',
              style: GoogleFonts.dmSans(
                fontSize: 13,
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
                amount: '₱0.00',
                subtitle: '0 completed deliveries',
              ),
            ),
            SizedBox(
              width: cardWidth,
              child: _buildMetricCard(
                title: 'THIS WEEK (7 DAYS)',
                pillLabel: '7D',
                amount: '₱0.00',
                subtitle: '0 deliveries',
              ),
            ),
            SizedBox(
              width: cardWidth,
              child: _buildMetricCard(
                title: 'THIS MONTH',
                pillLabel: 'MO',
                amount: '₱0.00',
                subtitle: '0 deliveries',
              ),
            ),
            SizedBox(
              width: cardWidth,
              child: _buildMetricCard(
                title: 'COD CASH ON HAND',
                pillLabel: 'COD',
                pillColor: const Color(0xFFFFF9EE),
                pillTextColor: const Color(0xFFB45309),
                amount: '₱0.00',
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
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
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
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
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
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
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
          const SizedBox(
            height: 180,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Y-axis labels
                Column(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text('₱1', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                    Text('₱0.8', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                    Text('₱0.6', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                    Text('₱0.4', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                    Text('₱0.2', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                    Text('₱0', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                  ],
                ),
                SizedBox(width: 8),

                // Chart Grid & Bars
                Expanded(
                  child: Column(
                    children: [
                      Expanded(
                        child: CustomPaint(
                          size: Size.infinite,
                          painter: _EarningsChartPainter(),
                        ),
                      ),
                      SizedBox(height: 8),
                      // X-axis days
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceAround,
                        children: [
                          Text('Sun, Oct 11', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                          Text('Mon, Oct 12', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                          Text('Tue, Oct 13', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                          Text('Wed, Oct 14', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                          Text('Thu, Oct 15', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                          Text('Fri, Oct 16', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                          Text('Sat, Oct 17', style: TextStyle(fontSize: 9.5, color: Color(0xFF8A7780))),
                        ],
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
              Column(
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
              Text(
                '0 Records',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  color: const Color(0xFF8A7780),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

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

          // Table Body: Empty state
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 40),
            child: Center(
              child: Text(
                'No completed payouts recorded yet.',
                style: GoogleFonts.dmSans(
                  fontSize: 13,
                  color: const Color(0xFFBAABB0),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _EarningsChartPainter extends CustomPainter {
  const _EarningsChartPainter();

  @override
  void paint(Canvas canvas, Size size) {
    final gridPaint = Paint()
      ..color = const Color(0xFFEFE8EA)
      ..strokeWidth = 1.0;

    // Draw horizontal grid lines
    const lineCount = 5;
    for (int i = 0; i <= lineCount; i++) {
      final y = size.height * (i / lineCount);
      canvas.drawLine(Offset(0, y), Offset(size.width, y), gridPaint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
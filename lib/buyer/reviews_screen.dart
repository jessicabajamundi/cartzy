import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/buyer/order_history_screen.dart';

// ============================================================
// REVIEWS & RATINGS SCREEN
// Matches the reference design with dashed empty-state card
// ============================================================

class ReviewsScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onViewOrders;

  const ReviewsScreen({
    super.key,
    required this.onBack,
    this.onViewOrders,
  });

  @override
  State<ReviewsScreen> createState() => _ReviewsScreenState();
}

class _ReviewsScreenState extends State<ReviewsScreen> {

  void _navigateToOrders() {
    if (widget.onViewOrders != null) {
      widget.onViewOrders!();
    } else {
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => OrderHistoryScreen(
            onBack: () => Navigator.of(context).pop(),
          ),
        ),
      );
    }
  }

  // ── Dashed Empty State Card ─────────────────────────────────
  Widget _buildEmptyStateCard() {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
      ),
      child: CustomPaint(
        painter: DashedRectPainter(
          color: const Color(0xFFDCD5E0),
          strokeWidth: 1.2,
          radius: 22,
          dashWidth: 5.0,
          dashSpace: 4.5,
        ),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 48),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // Gold 3D star icon
              const Text(
                '⭐',
                style: TextStyle(
                  fontSize: 34,
                  height: 1.0,
                ),
              ),

              const SizedBox(height: 16),

              // Title in elegant serif font
              Text(
                'No products to review yet',
                style: GoogleFonts.playfairDisplay(
                  fontSize: 18.5,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF261E30),
                  letterSpacing: -0.2,
                ),
                textAlign: TextAlign.center,
              ),

              const SizedBox(height: 8),

              // Subtitle
              Text(
                'Once your orders are delivered, you can rate and share your feedback here.',
                style: GoogleFonts.dmSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w400,
                  color: const Color(0xFF756A7C),
                  height: 1.45,
                ),
                textAlign: TextAlign.center,
              ),

              const SizedBox(height: 22),

              // View orders button
              InkWell(
                onTap: _navigateToOrders,
                borderRadius: BorderRadius.circular(10),
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24,
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
                  child: Text(
                    'View orders',
                    style: GoogleFonts.dmSans(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: Colors.white,
                      letterSpacing: 0.2,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
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

          // Title & Subtitle
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Reviews & Ratings',
                  style: GoogleFonts.playfairDisplay(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF261E30),
                    letterSpacing: -0.3,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Share feedback on products you\'ve received.',
                  style: GoogleFonts.dmSans(
                    fontSize: 13,
                    color: const Color(0xFF756A7C),
                    fontWeight: FontWeight.w400,
                  ),
                ),
              ],
            ),
          ),
        ],
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
            Expanded(
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: Column(
                  children: [
                    _buildEmptyStateCard(),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ============================================================
// CUSTOM PAINTER FOR DASHED ROUNDED RECTANGLE
// ============================================================

class DashedRectPainter extends CustomPainter {
  final Color color;
  final double strokeWidth;
  final double radius;
  final double dashWidth;
  final double dashSpace;

  DashedRectPainter({
    required this.color,
    this.strokeWidth = 1.2,
    this.radius = 22.0,
    this.dashWidth = 5.0,
    this.dashSpace = 4.5,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..strokeWidth = strokeWidth
      ..style = PaintingStyle.stroke;

    final rrect = RRect.fromRectAndRadius(
      Rect.fromLTWH(
        strokeWidth / 2,
        strokeWidth / 2,
        size.width - strokeWidth,
        size.height - strokeWidth,
      ),
      Radius.circular(radius),
    );

    final path = Path()..addRRect(rrect);
    final metrics = path.computeMetrics();

    for (final metric in metrics) {
      double distance = 0.0;
      while (distance < metric.length) {
        final double length = (distance + dashWidth < metric.length)
            ? dashWidth
            : metric.length - distance;
        final extractPath = metric.extractPath(distance, distance + length);
        canvas.drawPath(extractPath, paint);
        distance += dashWidth + dashSpace;
      }
    }
  }

  @override
  bool shouldRepaint(covariant DashedRectPainter oldDelegate) =>
      color != oldDelegate.color ||
      strokeWidth != oldDelegate.strokeWidth ||
      radius != oldDelegate.radius ||
      dashWidth != oldDelegate.dashWidth ||
      dashSpace != oldDelegate.dashSpace;
}


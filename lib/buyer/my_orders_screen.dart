import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

// ============================================================
// MY ORDERS SCREEN ("Your Shopping Journey" reference design)
// ============================================================

class MyOrdersScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onStartShopping;

  const MyOrdersScreen({
    super.key,
    required this.onBack,
    this.onStartShopping,
  });

  @override
  State<MyOrdersScreen> createState() => _MyOrdersScreenState();
}

class _MyOrdersScreenState extends State<MyOrdersScreen> {
  final List<Map<String, dynamic>> _orders = [];

  // ── Empty State Card ────────────────────────────────────────
  Widget _buildEmptyStateCard() {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFFECE3E5), width: 1.1),
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
                          Icons.inventory_2_outlined,
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
                    color: const Color(0xFF261E30),
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
                    color: const Color(0xFF756A7C),
                    height: 1.5,
                  ),
                  textAlign: TextAlign.center,
                ),

                const SizedBox(height: 22),

                // Start shopping CTA button
                InkWell(
                  onTap: widget.onStartShopping ?? widget.onBack,
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
                    color: const Color(0xFF261E30),
                    letterSpacing: -0.3,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Every purchase, every update. Keep track of it all here.',
                  style: GoogleFonts.dmSans(
                    fontSize: 12.5,
                    color: const Color(0xFF756A7C),
                  ),
                ),
              ],
            ),
          ),

          // 0 orders chip
          Container(
            margin: const EdgeInsets.only(top: 4),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFFFAF6F5),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: const Color(0xFFEADFD8)),
            ),
            child: Text(
              '${_orders.length} orders',
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


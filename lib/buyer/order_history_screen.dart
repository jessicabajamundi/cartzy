import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

// ============================================================
// ORDER HISTORY SCREEN
// Matches the "Your Purchases / Order History" reference design
// ============================================================

class OrderHistoryScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onStartShopping;

  const OrderHistoryScreen({
    super.key,
    required this.onBack,
    this.onStartShopping,
  });

  @override
  State<OrderHistoryScreen> createState() => _OrderHistoryScreenState();
}

class _OrderHistoryScreenState extends State<OrderHistoryScreen> {
  final List<Map<String, dynamic>> _orders = [];
  final bool _isLoading = false;

  // ── Empty State ─────────────────────────────────────────────
  Widget _emptyState(BuildContext context) {
    return SingleChildScrollView(
      child: Center(
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          padding: const EdgeInsets.symmetric(vertical: 52, horizontal: 32),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: CartzyColors.border),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.03),
                blurRadius: 16,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Icon with badge
              Stack(
                clipBehavior: Clip.none,
                children: [
                  Container(
                    width: 78,
                    height: 78,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0EBF4),
                      borderRadius: BorderRadius.circular(22),
                    ),
                    child: const Icon(
                      Icons.receipt_long_outlined,
                      size: 38,
                      color: Color(0xFF7C6B8E),
                    ),
                  ),
                  Positioned(
                    right: -6,
                    bottom: -6,
                    child: Container(
                      width: 22,
                      height: 22,
                      decoration: const BoxDecoration(
                        color: Color(0xFFE07B39),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.add, size: 14, color: Colors.white),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 28),

              // Eyebrow label
              Text(
                'YOUR PURCHASES',
                style: GoogleFonts.dmSans(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 1.4,
                  color: const Color(0xFF9C7B8A),
                ),
              ),

              const SizedBox(height: 10),

              // Title
              Text(
                'No past purchases yet',
                style: GoogleFonts.dmSans(
                  fontSize: 22,
                  fontWeight: FontWeight.w700,
                  color: CartzyColors.text,
                  height: 1.2,
                ),
                textAlign: TextAlign.center,
              ),

              const SizedBox(height: 10),

              // Subtitle
              Text(
                'Completed and cancelled orders will be\narchived here for your records.',
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  height: 1.55,
                  color: CartzyColors.gray,
                ),
                textAlign: TextAlign.center,
              ),

              const SizedBox(height: 28),

              // CTA button
              GestureDetector(
                onTap: widget.onStartShopping ?? widget.onBack,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 13),
                  decoration: BoxDecoration(
                    color: CartzyColors.navy,
                    borderRadius: BorderRadius.circular(24),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Start shopping',
                        style: GoogleFonts.dmSans(
                          color: Colors.white,
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          letterSpacing: 0.2,
                        ),
                      ),
                      const SizedBox(width: 8),
                      const Text(
                        '→',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 20),

              // Footer hint
              Text(
                'Completed and cancelled purchases will appear here automatically.',
                style: GoogleFonts.dmSans(
                  fontSize: 11.5,
                  color: CartzyColors.gray,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── Build ───────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Page Header ──────────────────────────────────
            Container(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 18),
              decoration: const BoxDecoration(
                color: CartzyColors.background,
                border: const Border(
                  bottom: const BorderSide(color: CartzyColors.border),
                ),
              ),
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
                      ),
                      child: const Icon(Icons.arrow_back, size: 18, color: CartzyColors.navy),
                    ),
                  ),

                  // Title block
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'YOUR PURCHASES',
                          style: GoogleFonts.dmSans(
                            fontSize: 10.5,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 1.4,
                            color: const Color(0xFF9C7B8A),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Order History',
                          style: GoogleFonts.dmSans(
                            fontSize: 26,
                            fontWeight: FontWeight.w800,
                            color: CartzyColors.text,
                            height: 1.1,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'A clear record of your completed and cancelled orders.',
                          style: GoogleFonts.dmSans(
                            fontSize: 12.5,
                            color: CartzyColors.gray,
                            height: 1.4,
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Purchase archive chip
                  Container(
                    margin: const EdgeInsets.only(top: 4),
                    padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 7),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: CartzyColors.border),
                    ),
                    child: Text(
                      'Purchase archive',
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: CartzyColors.text,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // ── Content ──────────────────────────────────────
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _orders.isEmpty
                      ? _emptyState(context)
                      : ListView.builder(
                          padding: const EdgeInsets.all(20),
                          itemCount: _orders.length,
                          itemBuilder: (context, index) {
                            final order = _orders[index];
                            return _orderCard(order);
                          },
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _orderCard(Map<String, dynamic> order) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: CartzyColors.border),
      ),
      child: Row(
        children: [
          const Icon(Icons.receipt_long_outlined, color: CartzyColors.navy),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              order['id'] ?? 'Order',
              style: GoogleFonts.dmSans(fontWeight: FontWeight.w600),
            ),
          ),
          Text(
            order['status'] ?? '',
            style: GoogleFonts.dmSans(color: CartzyColors.gray, fontSize: 12),
          ),
        ],
      ),
    );
  }
}

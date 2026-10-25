import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/services/voucher_service.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';

class VouchersScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onUseAtCheckout;

  const VouchersScreen({
    super.key,
    required this.onBack,
    this.onUseAtCheckout,
  });

  @override
  State<VouchersScreen> createState() => _VouchersScreenState();
}

class _VouchersScreenState extends State<VouchersScreen> {
  final TextEditingController _codeController = TextEditingController();

  @override
  void initState() {
    super.initState();
    VoucherService.instance.addListener(_onVouchersChanged);
  }

  @override
  void dispose() {
    VoucherService.instance.removeListener(_onVouchersChanged);
    _codeController.dispose();
    super.dispose();
  }

  void _onVouchersChanged() {
    if (mounted) setState(() {});
  }

  void _handleAddVoucher() {
    final code = _codeController.text.trim();
    if (code.isEmpty) {
      showCartzyFlash(context, 'Please enter a voucher code');
      return;
    }

    final (success, message) = VoucherService.instance.addVoucherCode(code);
    showCartzyFlash(context, message);
    if (success) {
      _codeController.clear();
      FocusScope.of(context).unfocus();
    }
  }

  @override
  Widget build(BuildContext context) {
    final vouchers = VoucherService.instance.vouchers;

    return Scaffold(
      backgroundColor: const Color(0xFFF7F5F8),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 20),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 1080),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── Back Button ──
                  GestureDetector(
                    onTap: widget.onBack,
                    child: Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.arrow_back, size: 16, color: Color(0xFF865561)),
                          const SizedBox(width: 6),
                          Text(
                            'Back to dashboard',
                            style: GoogleFonts.dmSans(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF865561),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  // ── Pre-header Tag ──
                  Text(
                    'SAVE ON YOUR NEXT ORDER',
                    style: GoogleFonts.dmSans(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 1.5,
                      color: const Color(0xFFA6715E),
                    ),
                  ),
                  const SizedBox(height: 4),

                  // ── Title + Voucher Count Pill ──
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Flexible(
                        child: Text(
                          'Vouchers',
                          style: GoogleFonts.playfairDisplay(
                            fontSize: 34,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF2D2640),
                            letterSpacing: -0.5,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFBF4EE),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: const Color(0xFFEBDCCE), width: 1),
                        ),
                        child: Text(
                          '${vouchers.length} voucher${vouchers.length == 1 ? '' : 's'}',
                          style: GoogleFonts.dmSans(
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                            color: const Color(0xFF5A4B43),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),

                  // ── Subtitle Description ──
                  Text(
                    'Your vouchers are applied for you at checkout. No need to copy anything.',
                    style: GoogleFonts.dmSans(
                      fontSize: 13.5,
                      fontWeight: FontWeight.w400,
                      color: const Color(0xFF7A6E72),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // ── Have a voucher code? Box ──
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFEDE5E7), width: 1.2),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.02),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Have a voucher code?',
                          style: GoogleFonts.dmSans(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF231F20),
                          ),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: SizedBox(
                                height: 46,
                                child: TextField(
                                  controller: _codeController,
                                  textCapitalization: TextCapitalization.characters,
                                  onSubmitted: (_) => _handleAddVoucher(),
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13.5,
                                    fontWeight: FontWeight.w600,
                                    color: const Color(0xFF231F20),
                                  ),
                                  decoration: InputDecoration(
                                    hintText: 'ENTER CODE, E.G. PASKO100',
                                    hintStyle: GoogleFonts.dmSans(
                                      fontSize: 12.5,
                                      color: const Color(0xFFA89CA0),
                                      letterSpacing: 0.5,
                                    ),
                                    isDense: true,
                                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                                    border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(10),
                                      borderSide: const BorderSide(color: Color(0xFFE0D8DA)),
                                    ),
                                    enabledBorder: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(10),
                                      borderSide: const BorderSide(color: Color(0xFFE0D8DA)),
                                    ),
                                    focusedBorder: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(10),
                                      borderSide: const BorderSide(color: Color(0xFF523D4D), width: 1.5),
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            SizedBox(
                              height: 46,
                              child: ElevatedButton(
                                onPressed: _handleAddVoucher,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF523D4D),
                                  foregroundColor: Colors.white,
                                  elevation: 0,
                                  padding: const EdgeInsets.symmetric(horizontal: 22),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                ),
                                child: Text(
                                  'Add voucher',
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13.5,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 28),

                  // ── Voucher Cards List ──
                  Wrap(
                    spacing: 20,
                    runSpacing: 20,
                    children: vouchers.map((voucher) => _buildVoucherCard(voucher)).toList(),
                  ),
                  const SizedBox(height: 28),

                  // ── Bottom Action Button: Use my vouchers at checkout ──
                  ElevatedButton(
                    onPressed: () {
                      if (widget.onUseAtCheckout != null) {
                        widget.onUseAtCheckout!();
                      } else {
                        Navigator.of(context).pop();
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF523D4D),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Flexible(
                          child: Text(
                            'Use my vouchers at checkout',
                            style: GoogleFonts.dmSans(
                              fontSize: 13.5,
                              fontWeight: FontWeight.w600,
                              color: Colors.white,
                            ),
                          ),
                        ),
                        const SizedBox(width: 14),
                        const Icon(
                          Icons.arrow_forward_rounded,
                          size: 16,
                          color: Colors.white,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 40),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildVoucherCard(Voucher voucher) {
    return Container(
      constraints: const BoxConstraints(maxWidth: 370),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE8E0E2), width: 1.1),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: IntrinsicHeight(
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // ── Left Ticket Stub ──
            Container(
              width: 108,
              color: voucher.stubColor,
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 14),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    voucher.tagTop,
                    style: GoogleFonts.dmSans(
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 1.2,
                      color: Colors.white.withValues(alpha: 0.9),
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    voucher.tagMain,
                    style: GoogleFonts.dmSans(
                      fontSize: 17,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                      height: 1.1,
                    ),
                  ),
                  Text(
                    voucher.tagBottom,
                    style: GoogleFonts.dmSans(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                      letterSpacing: 0.5,
                    ),
                  ),
                ],
              ),
            ),

            // ── Perforation divider ──
            CustomPaint(
              size: const Size(1, double.infinity),
              painter: _PerforationPainter(),
            ),

            // ── Right Content Area ──
            Expanded(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 13, 14, 13),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      voucher.title,
                      style: GoogleFonts.playfairDisplay(
                        fontSize: 15.5,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF231F20),
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      voucher.description,
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        color: const Color(0xFF6E6266),
                        height: 1.35,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 7),
                    Text(
                      'Min. spend ₱${voucher.minSpend.toStringAsFixed(0).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (m) => '${m[1]},')}',
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFF4A3E44),
                      ),
                    ),
                    const SizedBox(height: 5),
                    Row(
                      children: [
                        Text(
                          '✓ ',
                          style: GoogleFonts.dmSans(
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                            color: const Color(0xFF2E7D63),
                          ),
                        ),
                        Flexible(
                          child: Text(
                            'Ready to use on your cart',
                            style: GoogleFonts.dmSans(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF2E7D63),
                            ),
                          ),
                        ),
                      ],
                    ),
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

class _PerforationPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFFE8E0E2)
      ..strokeWidth = 1.0;

    double dashHeight = 4, dashSpace = 3, startY = 0;
    while (startY < size.height) {
      canvas.drawLine(Offset(0, startY), Offset(0, startY + dashHeight), paint);
      startY += dashHeight + dashSpace;
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

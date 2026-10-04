import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/review_service.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';

class PayNowDialog extends StatefulWidget {
  final Order order;

  const PayNowDialog({
    super.key,
    required this.order,
  });

  static Future<void> show(BuildContext context, {required Order order}) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
        ),
        child: PayNowDialog(order: order),
      ),
    );
  }

  @override
  State<PayNowDialog> createState() => _PayNowDialogState();
}

class _PayNowDialogState extends State<PayNowDialog>
    with TickerProviderStateMixin {
  // Payment method: 'COD' or 'GCash'
  String _selectedMethod = 'GCash';
  final TextEditingController _gcashController = TextEditingController();
  final TextEditingController _gcashPinController = TextEditingController();

  // Processing state
  // 'select' → 'processing' → 'success'
  String _stage = 'select';

  // Animated step progress (0 → 1 → 2 → 3)
  int _processingStep = 0;

  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;

  late AnimationController _successController;
  late Animation<double> _successScale;

  @override
  void initState() {
    super.initState();

    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..repeat(reverse: true);

    _pulseAnimation = Tween<double>(begin: 0.85, end: 1.0).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );

    _successController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    );

    _successScale = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _successController, curve: Curves.elasticOut),
    );
  }

  @override
  void dispose() {
    _gcashController.dispose();
    _gcashPinController.dispose();
    _pulseController.dispose();
    _successController.dispose();
    super.dispose();
  }

  // --------------------------------------------------------
  // PAYMENT PROCESSING
  // --------------------------------------------------------

  Future<void> _startPayment() async {
    // Validate GCash fields
    if (_selectedMethod == 'GCash') {
      if (_gcashController.text.trim().isEmpty) {
        _showError('Please enter your GCash number.');
        return;
      }
      if (_gcashController.text.trim().length < 11) {
        _showError('GCash number must be 11 digits.');
        return;
      }
      if (_gcashPinController.text.trim().isEmpty) {
        _showError('Please enter your GCash PIN.');
        return;
      }
    }

    setState(() {
      _stage = 'processing';
      _processingStep = 0;
    });

    // Simulate payment processing steps
    await Future.delayed(const Duration(milliseconds: 800));
    if (!mounted) return;
    setState(() => _processingStep = 1);

    await Future.delayed(const Duration(milliseconds: 1200));
    if (!mounted) return;
    setState(() => _processingStep = 2);

    await Future.delayed(const Duration(milliseconds: 1000));
    if (!mounted) return;
    setState(() => _processingStep = 3);

    await Future.delayed(const Duration(milliseconds: 600));
    if (!mounted) return;

    // Update order status
    ReviewService.instance.payOrder(widget.order.id);

    setState(() {
      _stage = 'success';
    });

    _pulseController.stop();
    _successController.forward();
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: CartzyColors.error,
      ),
    );
  }

  // --------------------------------------------------------
  // BUILD
  // --------------------------------------------------------

  @override
  Widget build(BuildContext context) {
    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.9,
      ),
      decoration: const BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Drag handle
          Container(
            margin: const EdgeInsets.only(top: 12, bottom: 8),
            width: 44,
            height: 4,
            decoration: BoxDecoration(
              color: CartzyColors.border,
              borderRadius: BorderRadius.circular(2),
            ),
          ),

          // Content based on stage
          if (_stage == 'select') _buildSelectStage(),
          if (_stage == 'processing') _buildProcessingStage(),
          if (_stage == 'success') _buildSuccessStage(),
        ],
      ),
    );
  }

  // ============================================================
  // STAGE 1: SELECT PAYMENT METHOD
  // ============================================================

  Widget _buildSelectStage() {
    return Flexible(
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Pay Now',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: CartzyColors.gray),
                  onPressed: () => Navigator.of(context).pop(),
                ),
              ],
            ),

            const Divider(color: CartzyColors.border),
            const SizedBox(height: 12),

            // Order Summary Card
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: CartzyColors.background,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: CartzyColors.border),
              ),
              child: Row(
                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      color: CartzyColors.surface,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: CartzyProductImage(
                      imageUrl: widget.order.effectiveImage,
                      width: 52,
                      height: 52,
                      fit: BoxFit.cover,
                      borderRadius: BorderRadius.circular(12),
                      fallbackIcon: Icons.shopping_bag_outlined,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.order.productName,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: CartzyColors.navy,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Text(
                              'Qty: ${widget.order.quantity}',
                              style: const TextStyle(
                                fontSize: 12,
                                color: CartzyColors.gray,
                              ),
                            ),
                            const Spacer(),
                            Text(
                              widget.order.price,
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: CartzyColors.navy,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 22),

            // Payment Method Header
            const Text(
              'Select Payment Method',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),

            const SizedBox(height: 12),

            // GCash Option
            _buildPaymentMethodTile(
              title: 'GCash',
              subtitle: 'Pay instantly via GCash e-wallet',
              icon: Icons.account_balance_wallet_outlined,
              value: 'GCash',
              color: const Color(0xFF007DFE),
            ),

            // COD Option
            _buildPaymentMethodTile(
              title: 'Cash on Delivery',
              subtitle: 'Pay when your order arrives',
              icon: Icons.local_atm_outlined,
              value: 'COD',
              color: const Color(0xFF2AA89C),
            ),

            // GCash input fields
            if (_selectedMethod == 'GCash') ...[
              const SizedBox(height: 8),
              AnimatedContainer(
                duration: const Duration(milliseconds: 250),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFF007DFE).withValues(alpha: 0.04),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: const Color(0xFF007DFE).withValues(alpha: 0.2),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFF007DFE),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Text(
                            'GCash',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        const Text(
                          'Enter Payment Details',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: CartzyColors.navy,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    TextField(
                      controller: _gcashController,
                      keyboardType: TextInputType.phone,
                      maxLength: 11,
                      decoration: InputDecoration(
                        labelText: 'GCash Number',
                        hintText: '09XXXXXXXXX',
                        counterText: '',
                        prefixIcon: const Icon(
                          Icons.phone_android,
                          color: Color(0xFF007DFE),
                        ),
                        filled: true,
                        fillColor: CartzyColors.surface,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: CartzyColors.border),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: CartzyColors.border),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(
                            color: Color(0xFF007DFE),
                            width: 1.5,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _gcashPinController,
                      keyboardType: TextInputType.number,
                      obscureText: true,
                      maxLength: 4,
                      decoration: InputDecoration(
                        labelText: 'GCash PIN',
                        hintText: '••••',
                        counterText: '',
                        prefixIcon: const Icon(
                          Icons.lock_outline,
                          color: Color(0xFF007DFE),
                        ),
                        filled: true,
                        fillColor: CartzyColors.surface,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: CartzyColors.border),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: CartzyColors.border),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: const BorderSide(
                            color: Color(0xFF007DFE),
                            width: 1.5,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Row(
                      children: [
                        Icon(Icons.shield_outlined,
                            size: 14, color: CartzyColors.gray),
                        SizedBox(width: 4),
                        Text(
                          'Your payment information is encrypted and secure.',
                          style: TextStyle(
                            fontSize: 11,
                            color: CartzyColors.gray,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],

            // COD note
            if (_selectedMethod == 'COD') ...[
              const SizedBox(height: 8),
              AnimatedContainer(
                duration: const Duration(milliseconds: 250),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFF2AA89C).withValues(alpha: 0.06),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: const Color(0xFF2AA89C).withValues(alpha: 0.2),
                  ),
                ),
                child: const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(
                          Icons.info_outline,
                          size: 18,
                          color: Color(0xFF2AA89C),
                        ),
                        SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Cash on Delivery',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF2AA89C),
                            ),
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: 8),
                    Text(
                      'Please prepare the exact amount. Your order will be shipped once confirmed. Payment will be collected upon delivery by our rider.',
                      style: TextStyle(
                        fontSize: 12,
                        color: CartzyColors.text,
                        height: 1.4,
                      ),
                    ),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 24),

            // Price Breakdown
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: CartzyColors.background,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  _priceRow('Subtotal', widget.order.price),
                  const SizedBox(height: 8),
                  _priceRow('Delivery Fee', '₱50.00'),
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 10),
                    child: Divider(height: 1, color: CartzyColors.border),
                  ),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Total Amount',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: CartzyColors.navy,
                        ),
                      ),
                      Text(
                        _calculateTotal(),
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFFD32F2F),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Pay Button
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton.icon(
                onPressed: _startPayment,
                icon: Icon(
                  _selectedMethod == 'GCash'
                      ? Icons.account_balance_wallet
                      : Icons.check_circle_outline,
                  size: 22,
                ),
                label: Text(
                  _selectedMethod == 'GCash'
                      ? 'Pay with GCash'
                      : 'Confirm Cash on Delivery',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: _selectedMethod == 'GCash'
                      ? const Color(0xFF007DFE)
                      : const Color(0xFF2AA89C),
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
              ),
            ),

            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  // ============================================================
  // STAGE 2: PROCESSING ANIMATION
  // ============================================================

  Widget _buildProcessingStage() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 16, 24, 40),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const SizedBox(height: 20),

          // Animated pulsing icon
          AnimatedBuilder(
            animation: _pulseAnimation,
            builder: (context, child) {
              return Transform.scale(
                scale: _pulseAnimation.value,
                child: Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: _selectedMethod == 'GCash'
                        ? const Color(0xFF007DFE).withValues(alpha: 0.1)
                        : const Color(0xFF2AA89C).withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    _selectedMethod == 'GCash'
                        ? Icons.account_balance_wallet
                        : Icons.local_atm,
                    size: 40,
                    color: _selectedMethod == 'GCash'
                        ? const Color(0xFF007DFE)
                        : const Color(0xFF2AA89C),
                  ),
                ),
              );
            },
          ),

          const SizedBox(height: 24),

          Text(
            _selectedMethod == 'GCash'
                ? 'Processing GCash Payment...'
                : 'Confirming Cash on Delivery...',
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: CartzyColors.navy,
            ),
          ),

          const SizedBox(height: 6),

          const Text(
            'Please wait, do not close this screen.',
            style: TextStyle(
              fontSize: 13,
              color: CartzyColors.gray,
            ),
          ),

          const SizedBox(height: 32),

          // Processing Steps
          _buildStep(
            0,
            'Verifying payment details',
            Icons.verified_user_outlined,
          ),
          _buildStep(
            1,
            _selectedMethod == 'GCash'
                ? 'Connecting to GCash'
                : 'Verifying order details',
            Icons.sync_outlined,
          ),
          _buildStep(
            2,
            _selectedMethod == 'GCash'
                ? 'Processing transaction'
                : 'Confirming with seller',
            Icons.receipt_long_outlined,
          ),
          _buildStep(
            3,
            'Payment confirmed',
            Icons.check_circle_outline,
          ),

          const SizedBox(height: 16),
        ],
      ),
    );
  }

  // ============================================================
  // STAGE 3: SUCCESS
  // ============================================================

  Widget _buildSuccessStage() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 16, 24, 32),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const SizedBox(height: 28),

          // Success checkmark animation
          AnimatedBuilder(
            animation: _successScale,
            builder: (context, child) {
              return Transform.scale(
                scale: _successScale.value,
                child: Container(
                  width: 90,
                  height: 90,
                  decoration: BoxDecoration(
                    color: const Color(0xFF4CAF50).withValues(alpha: 0.12),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.check_circle,
                    size: 56,
                    color: Color(0xFF4CAF50),
                  ),
                ),
              );
            },
          ),

          const SizedBox(height: 20),

          const Text(
            'Payment Successful! 🎉',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: CartzyColors.navy,
            ),
          ),

          const SizedBox(height: 8),

          Text(
            _selectedMethod == 'GCash'
                ? 'Your GCash payment has been processed successfully.'
                : 'Your COD order has been confirmed and will be shipped soon.',
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 14,
              color: CartzyColors.gray,
              height: 1.4,
            ),
          ),

          const SizedBox(height: 20),

          // Transaction details card
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: CartzyColors.background,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: CartzyColors.border),
            ),
            child: Column(
              children: [
                _detailRow('Order ID', widget.order.id.toUpperCase()),
                const SizedBox(height: 8),
                _detailRow(
                  'Payment Method',
                  _selectedMethod == 'GCash' ? 'GCash' : 'Cash on Delivery',
                ),
                const SizedBox(height: 8),
                _detailRow('Amount', widget.order.price),
                const SizedBox(height: 8),
                _detailRow('Status', 'Paid ✓'),
                const SizedBox(height: 8),
                _detailRow(
                  'Next Step',
                  'Waiting for seller to ship',
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // Done Button
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton.icon(
              onPressed: () {
                Navigator.of(context).pop();
              },
              icon: const Icon(Icons.done_all, size: 20),
              label: const Text(
                'Done',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF4CAF50),
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
            ),
          ),

          const SizedBox(height: 8),
        ],
      ),
    );
  }

  // ============================================================
  // HELPER WIDGETS
  // ============================================================

  Widget _buildPaymentMethodTile({
    required String title,
    required String subtitle,
    required IconData icon,
    required String value,
    required Color color,
  }) {
    final bool selected = _selectedMethod == value;

    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedMethod = value;
        });
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: selected
              ? color.withValues(alpha: 0.06)
              : CartzyColors.surface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: selected ? color : CartzyColors.border,
            width: selected ? 2 : 1,
          ),
        ),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: selected
                    ? color.withValues(alpha: 0.15)
                    : CartzyColors.background,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                icon,
                color: selected ? color : CartzyColors.gray,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: selected ? color : CartzyColors.navy,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: const TextStyle(
                      fontSize: 12,
                      color: CartzyColors.gray,
                    ),
                  ),
                ],
              ),
            ),
            AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              width: 22,
              height: 22,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: selected ? color : CartzyColors.border,
                  width: 2,
                ),
                color: selected ? color : Colors.transparent,
              ),
              child: selected
                  ? const Icon(Icons.check, size: 14, color: Colors.white)
                  : null,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStep(int stepIndex, String label, IconData icon) {
    final bool done = _processingStep > stepIndex;
    final bool active = _processingStep == stepIndex;

    Color stepColor;
    if (done) {
      stepColor = const Color(0xFF4CAF50);
    } else if (active) {
      stepColor = _selectedMethod == 'GCash'
          ? const Color(0xFF007DFE)
          : const Color(0xFF2AA89C);
    } else {
      stepColor = CartzyColors.border;
    }

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            width: 28,
            height: 28,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: done || active
                  ? stepColor.withValues(alpha: 0.15)
                  : CartzyColors.background,
              border: Border.all(color: stepColor, width: 1.5),
            ),
            child: Icon(
              done ? Icons.check : icon,
              size: 15,
              color: stepColor,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              label,
              style: TextStyle(
                fontSize: 13,
                fontWeight: active ? FontWeight.bold : FontWeight.normal,
                color: done || active ? CartzyColors.navy : CartzyColors.gray,
              ),
            ),
          ),
          if (active)
            SizedBox(
              width: 16,
              height: 16,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: stepColor,
              ),
            ),
          if (done)
            const Icon(
              Icons.check_circle,
              size: 16,
              color: Color(0xFF4CAF50),
            ),
        ],
      ),
    );
  }

  Widget _priceRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 13,
            color: CartzyColors.gray,
          ),
        ),
        Text(
          value,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: CartzyColors.navy,
          ),
        ),
      ],
    );
  }

  Widget _detailRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 12,
            color: CartzyColors.gray,
          ),
        ),
        Flexible(
          child: Text(
            value,
            textAlign: TextAlign.end,
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: CartzyColors.navy,
            ),
          ),
        ),
      ],
    );
  }

  String _calculateTotal() {
    // Parse the order price and add delivery fee
    final priceStr = widget.order.price
        .replaceAll('₱', '')
        .replaceAll(',', '')
        .trim();
    final price = double.tryParse(priceStr) ?? 0;
    final total = price + 50.00;
    return '₱${total.toStringAsFixed(2)}';
  }
}

import 'package:flutter/material.dart';
import 'package:cartzy/utils/input_rules.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/services/review_service.dart';
import 'package:cartzy/services/voucher_service.dart';
import 'package:cartzy/buyer/vouchers_screen.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';

class CheckoutScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback onOrderPlaced;

  const CheckoutScreen({
    super.key,
    required this.onBack,
    required this.onOrderPlaced,
  });

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  // ── Address State ── (the buyer adds their own before placing an order)
  Map<String, String>? _deliveryAddress;

  // ── Voucher Code Controller ──
  final TextEditingController _voucherCodeController = TextEditingController();
  bool _useShippingVoucher = false;

  // ── Payment Method ──
  String _selectedPaymentMethod = 'COD';

  final double _deliveryFee = 60.00;

  @override
  void initState() {
    super.initState();
    VoucherService.instance.addListener(_onVouchersChanged);
  }

  @override
  void dispose() {
    VoucherService.instance.removeListener(_onVouchersChanged);
    _voucherCodeController.dispose();
    super.dispose();
  }

  void _onVouchersChanged() {
    if (mounted) setState(() {});
  }

  static double _safeNum(dynamic value, [double defaultValue = 0.0]) {
    if (value == null) return defaultValue;
    if (value is num) {
      if (value.isNaN || value.isInfinite) return defaultValue;
      return value.toDouble();
    }
    return double.tryParse(value.toString().replaceAll(RegExp(r'[^0-9.]'), '')) ?? defaultValue;
  }

  static String _formatCurrency(dynamic value) {
    final numVal = _safeNum(value, 0.0);
    return numVal.toStringAsFixed(2);
  }

  // ── Order items from cart or demo item from screenshot ──
  List<Map<String, dynamic>> get orderItems {
    try {
      final selected = CartService.instance.selectedItems;
      if (selected.isNotEmpty) {
        return selected.map((item) {
          final price = _safeNum(item.price, 899.00);
          final qty = item.quantity <= 0 ? 1 : item.quantity;
          return {
            'name': item.safeName,
            'variant': item.safeVariation,
            'price': price,
            'quantity': qty,
            'image': item.safeImage,
          };
        }).toList();
      }
    } catch (_) {}
    return [];
  }

  int get totalItemCount {
    return orderItems.fold<int>(0, (sum, item) => sum + _safeNum(item['quantity'], 1.0).toInt());
  }

  double get subtotal {
    double sum = 0.0;
    for (final item in orderItems) {
      final p = _safeNum(item['price'], 0.0);
      final q = _safeNum(item['quantity'], 1.0).toInt();
      sum += p * q;
    }
    return (sum.isNaN || sum.isInfinite) ? 0.0 : sum;
  }

  bool get canUseShippingSaver => subtotal >= 1000.0;

  double get shippingDiscount {
    if (_useShippingVoucher && canUseShippingSaver) {
      return 50.00;
    }
    return 0.00;
  }

  double get voucherDiscount {
    try {
      final best = VoucherService.instance.getBestApplicableVoucher(subtotal);
      if (best != null && !best.isShippingDiscount) {
        return _safeNum(best.discountAmount, 0.0);
      }
    } catch (_) {}
    return 0.00;
  }

  double get totalPayment {
    final total = subtotal + _deliveryFee - shippingDiscount - voucherDiscount;
    return (total.isNaN || total < 0) ? 0.0 : total;
  }

  // ── Add Address Modal ──
  void _showAddAddressDialog() {
    final user = AuthService.currentUser;
    final nameCtrl = TextEditingController(text: _deliveryAddress?['name'] ?? user?['name']?.toString() ?? '');
    final phoneCtrl = TextEditingController(text: _deliveryAddress?['phone'] ?? user?['phone']?.toString() ?? '');
    final streetCtrl = TextEditingController(text: _deliveryAddress?['street'] ?? '');
    final cityCtrl = TextEditingController(text: _deliveryAddress?['city'] ?? '');

    showDialog(
      context: context,
      builder: (ctx) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Text(
            'Delivery Address',
            style: GoogleFonts.playfairDisplay(fontSize: 20, fontWeight: FontWeight.w700),
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: nameCtrl,
                  inputFormatters: InputRules.fullNameFormatters,
                  decoration: const InputDecoration(
                    labelText: 'Recipient Name',
                    isDense: true,
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: phoneCtrl,
                  keyboardType: TextInputType.phone,
                  inputFormatters: InputRules.phoneFormatters,
                  decoration: const InputDecoration(
                    labelText: 'Contact Phone',
                    isDense: true,
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: streetCtrl,
                  decoration: const InputDecoration(
                    labelText: 'House / Unit / Street',
                    isDense: true,
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: cityCtrl,
                  decoration: const InputDecoration(
                    labelText: 'City / Municipality, Province',
                    isDense: true,
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text('Cancel', style: GoogleFonts.dmSans(color: const Color(0xFF7A6E72))),
            ),
            ElevatedButton(
              onPressed: () {
                final contactError = InputRules.fullName(nameCtrl.text, label: 'recipient name') ??
                    InputRules.phone(phoneCtrl.text);
                if (contactError != null) {
                  showCartzyFlash(context, contactError);
                  return;
                }
                if (streetCtrl.text.trim().isEmpty || cityCtrl.text.trim().isEmpty) {
                  showCartzyFlash(context, 'Please enter a valid address');
                  return;
                }
                setState(() {
                  _deliveryAddress = {
                    'name': nameCtrl.text.trim(),
                    'phone': phoneCtrl.text.trim(),
                    'street': streetCtrl.text.trim(),
                    'city': cityCtrl.text.trim(),
                  };
                });
                Navigator.pop(ctx);
                showCartzyFlash(context, 'Delivery address updated');
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF5A4452),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              child: const Text('Save Address'),
            ),
          ],
        );
      },
    );
  }

  // ── Apply Voucher Code ──
  void _handleApplyVoucherCode() {
    final code = _voucherCodeController.text.trim();
    if (code.isEmpty) {
      showCartzyFlash(context, 'Please enter a voucher code');
      return;
    }

    final (success, message) = VoucherService.instance.addVoucherCode(code);
    showCartzyFlash(context, message);
    if (success) {
      _voucherCodeController.clear();
      FocusScope.of(context).unfocus();
    }
  }

  // ── Place Order Action ──
  void _handlePlaceOrder() {
    if (_deliveryAddress == null) {
      showCartzyFlash(context, 'Add your delivery address before placing order');
      return;
    }
    if (orderItems.isEmpty) {
      showCartzyFlash(context, 'Select at least one item in your cart to check out');
      return;
    }

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          title: Row(
            children: [
              const Icon(Icons.check_circle, color: Color(0xFF2E7D63), size: 28),
              const SizedBox(width: 10),
              Text(
                'Order Placed!',
                style: GoogleFonts.dmSans(fontWeight: FontWeight.w700, fontSize: 18),
              ),
            ],
          ),
          content: Text(
            'Your order has been placed successfully via Cash on Delivery.\n\n'
            'Prepare ₱${_formatCurrency(totalPayment)} for the rider upon delivery.',
            style: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFF4A3E47), height: 1.4),
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(ctx);
                for (final item in orderItems) {
                  ReviewService.instance.addOrderFromCheckout(
                    productName: (item['name'] as String?) ?? 'Product',
                    price: '₱${_formatCurrency(item['price'])}',
                    quantity: _safeNum(item['quantity'], 1.0).toInt(),
                    paymentMethod: _selectedPaymentMethod,
                    image: (item['image'] as String?) ?? '',
                  );
                }
                CartService.instance.clearSelected();
                widget.onOrderPlaced();
              },
              child: Text(
                'View My Orders',
                style: GoogleFonts.dmSans(
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF5A4452),
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF8F9),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 1120),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── Breadcrumbs ──
                  Row(
                    children: [
                      GestureDetector(
                        onTap: widget.onBack,
                        child: Text(
                          'Home',
                          style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
                        ),
                      ),
                      Text(
                        '  /  ',
                        style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFFB8AEB2)),
                      ),
                      GestureDetector(
                        onTap: widget.onBack,
                        child: Text(
                          'Cart',
                          style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
                        ),
                      ),
                      Text(
                        '  /  ',
                        style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFFB8AEB2)),
                      ),
                      Text(
                        'Checkout',
                        style: GoogleFonts.dmSans(fontSize: 12, fontWeight: FontWeight.w600, color: const Color(0xFF231F20)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // ── Pre-header ──
                  Text(
                    'ONE LAST STEP',
                    style: GoogleFonts.dmSans(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 1.5,
                      color: const Color(0xFFA6715E),
                    ),
                  ),
                  const SizedBox(height: 4),

                  // ── Title & Back to Cart Link ──
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Review your order',
                              style: GoogleFonts.playfairDisplay(
                                fontSize: 32,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF231F20),
                                letterSpacing: -0.4,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Check your items, delivery details, and total before placing your order.',
                              style: GoogleFonts.dmSans(
                                fontSize: 13,
                                color: const Color(0xFF7A6E72),
                              ),
                            ),
                          ],
                        ),
                      ),
                      GestureDetector(
                        onTap: widget.onBack,
                        child: Padding(
                          padding: const EdgeInsets.only(top: 6),
                          child: Text(
                            '← Back to cart',
                            style: GoogleFonts.dmSans(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF231F20),
                              decoration: TextDecoration.underline,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // ── Layout: Two Columns on Wide Screens ──
                  LayoutBuilder(
                    builder: (context, constraints) {
                      final isWide = constraints.maxWidth >= 860;
                      if (isWide) {
                        return Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Left Column (Cards 01, 02, 03, 04)
                            Expanded(
                              flex: 13,
                              child: Column(
                                children: [
                                  _buildDeliveryDetailsCard(),
                                  const SizedBox(height: 18),
                                  _buildOrderItemsCard(),
                                  const SizedBox(height: 18),
                                  _buildShippingVouchersCard(),
                                  const SizedBox(height: 18),
                                  _buildPaymentMethodCard(),
                                ],
                              ),
                            ),
                            const SizedBox(width: 24),
                            // Right Column (Order Summary Sidebar)
                            Expanded(
                              flex: 7,
                              child: _buildOrderSummarySidebar(),
                            ),
                          ],
                        );
                      } else {
                        // Narrow / Mobile screen layout
                        return Column(
                          children: [
                            _buildDeliveryDetailsCard(),
                            const SizedBox(height: 18),
                            _buildOrderItemsCard(),
                            const SizedBox(height: 18),
                            _buildShippingVouchersCard(),
                            const SizedBox(height: 18),
                            _buildPaymentMethodCard(),
                            const SizedBox(height: 24),
                            _buildOrderSummarySidebar(),
                          ],
                        );
                      }
                    },
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

  // ============================================================
  // CARD 01: DELIVERY DETAILS
  // ============================================================

  Widget _buildDeliveryDetailsCard() {
    return _buildSectionCard(
      stepNumber: '01',
      title: 'Delivery details',
      trailingAction: GestureDetector(
        onTap: _showAddAddressDialog,
        child: Text(
          _deliveryAddress == null ? 'Add address' : 'Change address',
          style: GoogleFonts.dmSans(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: const Color(0xFF231F20),
            decoration: TextDecoration.underline,
          ),
        ),
      ),
      child: _deliveryAddress == null
          ? Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
              decoration: BoxDecoration(
                color: const Color(0xFFFDF6F2),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFF2DDD2), width: 1.1),
              ),
              child: Text(
                'Add a delivery address before placing your order.',
                style: GoogleFonts.dmSans(
                  fontSize: 12.5,
                  color: const Color(0xFF865242),
                ),
              ),
            )
          : Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                color: const Color(0xFFFBF8F9),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFEDE5E8)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.location_on_outlined, color: Color(0xFF865561), size: 20),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${_deliveryAddress!['name']} • ${_deliveryAddress!['phone']}',
                          style: GoogleFonts.dmSans(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF231F20),
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '${_deliveryAddress!['street']}, ${_deliveryAddress!['city']}',
                          style: GoogleFonts.dmSans(
                            fontSize: 12.5,
                            color: const Color(0xFF6E6266),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
    );
  }

  // ============================================================
  // CARD 02: ORDER ITEMS
  // ============================================================

  Widget _buildOrderItemsCard() {
    return _buildSectionCard(
      stepNumber: '02',
      title: 'Order items',
      titleBadge: '$totalItemCount',
      trailingAction: GestureDetector(
        onTap: widget.onBack,
        child: Text(
          'Edit items',
          style: GoogleFonts.dmSans(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: const Color(0xFF231F20),
            decoration: TextDecoration.underline,
          ),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            '${orderItems.length} product selection • $totalItemCount item in total',
            style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
          ),
          const SizedBox(height: 12),
          const Divider(height: 1, color: Color(0xFFF3ECEE)),
          const SizedBox(height: 12),

          ...orderItems.map((item) {
            final name = (item['name'] as String?) ?? 'Product';
            final variant = (item['variant'] as String?) ?? 'Standard';
            final price = _safeNum(item['price'], 899.00);
            final qty = _safeNum(item['quantity'], 1.0).toInt();
            final itemTotal = price * qty;
            final image = (item['image'] as String?) ?? '';

            return Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  // Product Thumbnail
                  Container(
                    width: 58,
                    height: 58,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF6F0F2),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFEDE5E8)),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: image.isNotEmpty
                        ? Image.network(
                            image,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => const Icon(
                              Icons.headset,
                              color: Color(0xFF865561),
                              size: 26,
                            ),
                          )
                        : const Icon(
                            Icons.headset,
                            color: Color(0xFF865561),
                            size: 26,
                          ),
                  ),
                  const SizedBox(width: 14),

                  // Title & Variant
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          name,
                          style: GoogleFonts.dmSans(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF231F20),
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          variant,
                          style: GoogleFonts.dmSans(
                            fontSize: 12,
                            color: const Color(0xFF8E8286),
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '₱${_formatCurrency(price)} each',
                          style: GoogleFonts.dmSans(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF5A4452),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Quantity and Item Total
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF3EDF0),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          'Qty: $qty',
                          style: GoogleFonts.dmSans(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF5A4452),
                          ),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '₱${_formatCurrency(itemTotal)}',
                        style: GoogleFonts.dmSans(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF231F20),
                        ),
                      ),
                      Text(
                        'item total',
                        style: GoogleFonts.dmSans(
                          fontSize: 10.5,
                          color: const Color(0xFFA09498),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  // ============================================================
  // CARD 03: SHIPPING & VOUCHERS
  // ============================================================

  Widget _buildShippingVouchersCard() {
    return _buildSectionCard(
      stepNumber: '03',
      title: 'Shipping & vouchers',
      trailingAction: GestureDetector(
        onTap: () {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => VouchersScreen(
                onBack: () => Navigator.of(context).pop(),
                onUseAtCheckout: () => Navigator.of(context).pop(),
              ),
            ),
          );
        },
        child: Text(
          'My vouchers',
          style: GoogleFonts.dmSans(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: const Color(0xFF231F20),
            decoration: TextDecoration.underline,
          ),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Standard delivery row
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Standard delivery',
                    style: GoogleFonts.dmSans(
                      fontSize: 13.5,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF231F20),
                    ),
                  ),
                  Text(
                    'Shipping fee for this order',
                    style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
                  ),
                ],
              ),
              ),
              const SizedBox(width: 8),
              Text(
                '₱${_formatCurrency(_deliveryFee)}',
                style: GoogleFonts.dmSans(
                  fontSize: 14.5,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF231F20),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            'Delivery timing will be confirmed when your order is dispatched. You can track updates in My Orders.',
            style: GoogleFonts.dmSans(fontSize: 11.5, color: const Color(0xFF8E8286), height: 1.35),
          ),
          const SizedBox(height: 14),

          // Vouchers status notification box
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: const Color(0xFFFDFBF7),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: const Color(0xFFF5EEDF)),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(4),
                  decoration: const BoxDecoration(
                    color: Color(0xFFEFE6D8),
                    shape: BoxShape.circle,
                  ),
                  child: const Text(
                    '%',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF7A6242),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: RichText(
                    text: TextSpan(
                      style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF6E5F52)),
                      children: [
                        const TextSpan(
                          text: 'Vouchers: ',
                          style: TextStyle(fontWeight: FontWeight.w700),
                        ),
                        TextSpan(
                          text: canUseShippingSaver
                              ? 'Shipping Saver is ready to use on this order!'
                              : 'No voucher can be used on this order yet. Have a code? Enter it below.',
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Shipping vouchers section
          Text(
            'Shipping vouchers',
            style: GoogleFonts.dmSans(
              fontSize: 12.5,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF231F20),
            ),
          ),
          const SizedBox(height: 10),

          // Option 1: Shipping Saver Voucher Card
          GestureDetector(
            onTap: canUseShippingSaver
                ? () {
                    setState(() => _useShippingVoucher = true);
                  }
                : null,
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: _useShippingVoucher && canUseShippingSaver
                      ? const Color(0xFF5A4452)
                      : const Color(0xFFE8E0E2),
                  width: _useShippingVoucher && canUseShippingSaver ? 1.5 : 1.0,
                ),
              ),
              child: Row(
                children: [
                  // Left mini stub
                  Container(
                    width: 76,
                    height: 52,
                    decoration: BoxDecoration(
                      color: canUseShippingSaver
                          ? const Color(0xFF2E7D63)
                          : const Color(0xFFF3ECEE),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          '₱50 OFF',
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            color: canUseShippingSaver
                                ? Colors.white
                                : const Color(0xFF8E8286),
                          ),
                        ),
                        Text(
                          'SHIPPING',
                          style: GoogleFonts.dmSans(
                            fontSize: 9,
                            fontWeight: FontWeight.w700,
                            color: canUseShippingSaver
                                ? Colors.white70
                                : const Color(0xFFA89CA0),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),

                  // Voucher info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Shipping Saver',
                          style: GoogleFonts.playfairDisplay(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF231F20),
                          ),
                        ),
                        Text(
                          'Min. spend ₱1,000',
                          style: GoogleFonts.dmSans(
                            fontSize: 11.5,
                            color: const Color(0xFF7A6E72),
                          ),
                        ),
                        Text(
                          canUseShippingSaver
                              ? 'Ready to use on this order'
                              : 'Add ₱${_formatCurrency((1000.0 - subtotal).clamp(0.0, 1000.0))} more to use',
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: canUseShippingSaver
                                ? const Color(0xFF2E7D63)
                                : const Color(0xFFA89CA0),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Radio Circle
                  Icon(
                    _useShippingVoucher && canUseShippingSaver
                        ? Icons.radio_button_checked
                        : Icons.radio_button_off,
                    color: _useShippingVoucher && canUseShippingSaver
                        ? const Color(0xFF5A4452)
                        : const Color(0xFFD0C4C8),
                    size: 20,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 8),

          // Option 2: Don't use a shipping voucher
          GestureDetector(
            onTap: () {
              setState(() => _useShippingVoucher = false);
            },
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: !_useShippingVoucher
                      ? const Color(0xFF5A4452)
                      : const Color(0xFFE8E0E2),
                  width: !_useShippingVoucher ? 1.5 : 1.0,
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      'Don\'t use a shipping voucher',
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color: const Color(0xFF231F20),
                      ),
                    ),
                  ),
                  Icon(
                    !_useShippingVoucher
                        ? Icons.radio_button_checked
                        : Icons.radio_button_off,
                    color: !_useShippingVoucher
                        ? const Color(0xFF5A4452)
                        : const Color(0xFFD0C4C8),
                    size: 20,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 18),

          // Have a voucher code? Box
          Text(
            'Have a voucher code?',
            style: GoogleFonts.dmSans(
              fontSize: 12.5,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF231F20),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: SizedBox(
                  height: 42,
                  child: TextField(
                    controller: _voucherCodeController,
                    textCapitalization: TextCapitalization.characters,
                    onSubmitted: (_) => _handleApplyVoucherCode(),
                    style: GoogleFonts.dmSans(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF231F20),
                    ),
                    decoration: InputDecoration(
                      hintText: 'ENTER CODE, E.G. PASKO100',
                      hintStyle: GoogleFonts.dmSans(
                        fontSize: 12,
                        color: const Color(0xFFA89CA0),
                        letterSpacing: 0.5,
                      ),
                      isDense: true,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: Color(0xFFE0D8DA)),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: Color(0xFFE0D8DA)),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: Color(0xFF5A4452), width: 1.5),
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              SizedBox(
                height: 42,
                child: ElevatedButton(
                  onPressed: _handleApplyVoucherCode,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF5A4452),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: Text(
                    'Apply',
                    style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ============================================================
  // CARD 04: PAYMENT METHOD (Screenshot 2)
  // ============================================================

  Widget _buildPaymentMethodCard() {
    return _buildSectionCard(
      stepNumber: '04',
      title: 'Payment method',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Option 1: Cash on Delivery (COD)
          GestureDetector(
            onTap: () => setState(() => _selectedPaymentMethod = 'COD'),
            child: Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: _selectedPaymentMethod == 'COD'
                      ? const Color(0xFF5A4452)
                      : const Color(0xFFE8E0E2),
                  width: _selectedPaymentMethod == 'COD' ? 1.5 : 1.0,
                ),
              ),
              child: Row(
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF3ECEE),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Center(
                      child: Text(
                        '₱',
                        style: GoogleFonts.dmSans(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF5A4452),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Cash on Delivery (COD)',
                          style: GoogleFonts.dmSans(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF231F20),
                          ),
                        ),
                        Text(
                          'Pay the rider in cash when your order arrives.',
                          style: GoogleFonts.dmSans(
                            fontSize: 12,
                            color: const Color(0xFF7A6E72),
                          ),
                        ),
                      ],
                    ),
                  ),
                  Icon(
                    _selectedPaymentMethod == 'COD'
                        ? Icons.radio_button_checked
                        : Icons.radio_button_off,
                    color: _selectedPaymentMethod == 'COD'
                        ? const Color(0xFF5A4452)
                        : const Color(0xFFD0C4C8),
                    size: 20,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 10),

          // Option 2: GCash (Unavailable)
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFEDE5E8)),
            ),
            child: Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: const Color(0xFFEDF3F8),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Center(
                    child: Text(
                      'G',
                      style: GoogleFonts.dmSans(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF2E6B9E),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Flexible(
                            child: Text(
                              'GCash',
                              style: GoogleFonts.dmSans(
                                fontSize: 13.5,
                                fontWeight: FontWeight.w600,
                                color: const Color(0xFF7A6E72),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF3ECEE),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              'Unavailable',
                              style: GoogleFonts.dmSans(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w500,
                                color: const Color(0xFF8E8286),
                              ),
                            ),
                          ),
                        ],
                      ),
                      Text(
                        'Online payments are not available yet. Please use COD.',
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          color: const Color(0xFFA09498),
                        ),
                      ),
                    ],
                  ),
                ),
                const Icon(
                  Icons.radio_button_off,
                  color: Color(0xFFE2D8DB),
                  size: 20,
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          Text(
            'No payment is collected now. Prepare ₱${_formatCurrency(totalPayment)} for delivery.',
            style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // RIGHT COLUMN: ORDER SUMMARY SIDEBAR
  // ============================================================

  Widget _buildOrderSummarySidebar() {
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFEFE8EB), width: 1.2),
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
            'YOUR ORDER AT A GLANCE',
            style: GoogleFonts.dmSans(
              fontSize: 10.5,
              fontWeight: FontWeight.w700,
              letterSpacing: 1.2,
              color: const Color(0xFFA6715E),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Order summary',
            style: GoogleFonts.playfairDisplay(
              fontSize: 22,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF231F20),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            '$totalItemCount item • All amounts in PHP',
            style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
          ),
          const SizedBox(height: 16),
          const Divider(height: 1, color: Color(0xFFF3ECEE)),
          const SizedBox(height: 14),

          _buildSummaryRow('Items before discounts', '₱${_formatCurrency(subtotal)}'),
          const SizedBox(height: 8),
          _buildSummaryRow('Product discounts', '₱0.00', isAccentGreen: true),
          const SizedBox(height: 12),
          const Divider(height: 1, color: Color(0xFFF3ECEE)),
          const SizedBox(height: 12),

          _buildSummaryRow('Merchandise subtotal', '₱${_formatCurrency(subtotal)}'),
          const SizedBox(height: 8),
          _buildSummaryRow('Shipping fee', '₱${_formatCurrency(_deliveryFee)}'),
          const SizedBox(height: 8),
          _buildSummaryRow(
            'Shipping discount',
            shippingDiscount > 0 ? '-₱${_formatCurrency(shippingDiscount)}' : '₱0.00',
            isAccentGreen: true,
          ),
          const SizedBox(height: 8),
          _buildSummaryRow(
            'Voucher discount',
            voucherDiscount > 0 ? '-₱${_formatCurrency(voucherDiscount)}' : '₱0.00',
            isAccentGreen: true,
          ),
          const SizedBox(height: 14),
          const Divider(height: 1, color: Color(0xFFF3ECEE)),
          const SizedBox(height: 14),

          // Total payment Row
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Total payment',
                    style: GoogleFonts.dmSans(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF231F20),
                    ),
                  ),
                  Text(
                    'Pay on delivery',
                    style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFF8E8286)),
                  ),
                ],
              ),
              ),
              const SizedBox(width: 8),
              Text(
                '₱${_formatCurrency(totalPayment)}',
                style: GoogleFonts.dmSans(
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                  color: const Color(0xFF231F20),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Flexible(
                child: Text(
                  'Payment method',
                  style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
                ),
              ),
              const SizedBox(width: 8),
              Text(
                'Cash on Delivery',
                style: GoogleFonts.dmSans(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF231F20),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),

          // Place order button
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: _deliveryAddress == null ? null : _handlePlaceOrder,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF5A4452),
                disabledBackgroundColor: const Color(0xFFA89CA3),
                foregroundColor: Colors.white,
                disabledForegroundColor: Colors.white70,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    'Place order',
                    style: GoogleFonts.dmSans(
                      fontSize: 13.5,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const Spacer(),
                  Text(
                    '₱${_formatCurrency(totalPayment)}',
                    style: GoogleFonts.dmSans(
                      fontSize: 13.5,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(width: 6),
                  const Icon(Icons.arrow_forward_rounded, size: 16),
                ],
              ),
            ),
          ),
          const SizedBox(height: 8),

          if (_deliveryAddress == null) ...[
            Text(
              'Add your delivery address to continue.',
              style: GoogleFonts.dmSans(
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                color: const Color(0xFFC44D4D),
              ),
            ),
            const SizedBox(height: 8),
          ],

          Text(
            'Review your details before confirming. Your order will appear in My Orders after it is placed.',
            style: GoogleFonts.dmSans(
              fontSize: 11.5,
              color: const Color(0xFF8E8286),
              height: 1.35,
            ),
          ),
          const SizedBox(height: 14),

          Center(
            child: GestureDetector(
              onTap: widget.onBack,
              child: Text(
                'Need to change something? Edit your cart',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF5A4452),
                  decoration: TextDecoration.underline,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value, {bool isAccentGreen = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Expanded(
          child: Text(
            label,
            style: GoogleFonts.dmSans(fontSize: 12.5, color: const Color(0xFF6E6266)),
          ),
        ),
        const SizedBox(width: 8),
        Text(
          value,
          style: GoogleFonts.dmSans(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: isAccentGreen ? const Color(0xFF2E7D63) : const Color(0xFF231F20),
          ),
        ),
      ],
    );
  }

  // ============================================================
  // REUSABLE SECTION CARD WRAPPER
  // ============================================================

  Widget _buildSectionCard({
    required String stepNumber,
    required String title,
    String? titleBadge,
    Widget? trailingAction,
    required Widget child,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFEFE8EB), width: 1.2),
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
          Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFF3EDF0),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  stepNumber,
                  style: GoogleFonts.dmSans(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF5A4452),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Flexible(
                child: Text(
                  title,
                  style: GoogleFonts.dmSans(
                    fontSize: 15.5,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF231F20),
                  ),
                ),
              ),
              if (titleBadge != null) ...[
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF3EDF0),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    titleBadge,
                    style: GoogleFonts.dmSans(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF5A4452),
                    ),
                  ),
                ),
              ],
              const Spacer(),
              if (trailingAction != null) trailingAction,
            ],
          ),
          const SizedBox(height: 14),
          child,
        ],
      ),
    );
  }
}
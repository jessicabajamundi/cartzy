import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/services/cart_service.dart';

class CartScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback onCheckout;

  const CartScreen({
    super.key,
    required this.onBack,
    required this.onCheckout,
  });

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  final double _shippingFee = 60.00;

  @override
  void initState() {
    super.initState();
    CartService.instance.addListener(_onCartChanged);
  }

  @override
  void dispose() {
    CartService.instance.removeListener(_onCartChanged);
    super.dispose();
  }

  void _onCartChanged() {
    if (mounted) {
      setState(() {});
    }
  }

  List<CartItem> get _cartItems => CartService.instance.items;
  double get _selectedTotal => CartService.instance.selectedTotal;
  int get _selectedCount => CartService.instance.selectedItemCount;

  static String _formatCurrency(dynamic value) {
    if (value == null) return '0.00';
    double numVal = 0.0;
    if (value is num) {
      numVal = (value.isNaN || value.isInfinite) ? 0.0 : value.toDouble();
    } else {
      numVal = double.tryParse(value.toString().replaceAll(RegExp(r'[^0-9.]'), '')) ?? 0.0;
    }
    final parts = numVal.toStringAsFixed(2).split('.');
    final intPart = parts[0].replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]},',
    );
    return '$intPart.${parts[1]}';
  }

  void _toggleSelectAll(bool? value) {
    if (CartService.instance.isAllSelected) {
      CartService.instance.toggleSelectAll(false);
    } else {
      CartService.instance.toggleSelectAll(true);
    }
  }

  void _increaseQuantity(int index) {
    CartService.instance.increaseQuantity(index);
  }

  void _decreaseQuantity(int index) {
    CartService.instance.decreaseQuantity(index);
  }

  void _removeItem(int index) {
    if (index >= 0 && index < _cartItems.length) {
      final removedName = _cartItems[index].name;
      CartService.instance.removeItem(index);

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('$removedName removed from cart.'),
          backgroundColor: const Color(0xFF231F20),
          duration: const Duration(seconds: 2),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  void _confirmClearCart() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Clear Cart?',
          style: GoogleFonts.playfairDisplay(
            fontWeight: FontWeight.w700,
            fontSize: 20,
            color: const Color(0xFF231F20),
          ),
        ),
        content: Text(
          'Are you sure you want to remove all items from your cart?',
          style: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFF6E6266)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: Text(
              'Cancel',
              style: GoogleFonts.dmSans(
                fontWeight: FontWeight.w600,
                color: const Color(0xFF7A6E72),
              ),
            ),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              CartService.instance.clearAll();
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Cart cleared.'),
                  backgroundColor: Color(0xFF231F20),
                  duration: Duration(seconds: 2),
                  behavior: SnackBarBehavior.floating,
                ),
              );
            },
            child: Text(
              'Clear All',
              style: GoogleFonts.dmSans(
                fontWeight: FontWeight.w700,
                color: const Color(0xFFD63D4A),
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _checkout() {
    final selectedItems = CartService.instance.selectedItems;

    if (selectedItems.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Please select at least one item to checkout.',
            style: GoogleFonts.dmSans(),
          ),
          backgroundColor: const Color(0xFF231F20),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    widget.onCheckout();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF8F9),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 28),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 1140),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── Top Header ──
                  _buildHeader(),
                  const SizedBox(height: 24),

                  if (_cartItems.isEmpty)
                    _buildEmptyCart()
                  else
                    LayoutBuilder(
                      builder: (context, constraints) {
                        final isWide = constraints.maxWidth >= 880;
                        if (isWide) {
                          return Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Left column: Cart table + Continue shopping
                              Expanded(
                                flex: 13,
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    _buildCartTableCard(isWide: true),
                                    const SizedBox(height: 18),
                                    _buildContinueShoppingLink(),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 24),
                              // Right column: Order Summary
                              SizedBox(
                                width: 330,
                                child: _buildOrderSummaryCard(),
                              ),
                            ],
                          );
                        } else {
                          // Stacked narrow/mobile layout
                          return Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildCartTableCard(isWide: false),
                              const SizedBox(height: 20),
                              _buildOrderSummaryCard(),
                              const SizedBox(height: 18),
                              _buildContinueShoppingLink(),
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
  // HEADER
  // ============================================================

  Widget _buildHeader() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'YOUR NEXT FINDS',
          style: GoogleFonts.dmSans(
            fontSize: 10.5,
            fontWeight: FontWeight.w700,
            letterSpacing: 1.4,
            color: const Color(0xFFA6715E),
          ),
        ),
        const SizedBox(height: 6),
        Row(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Text(
              'Shopping Cart',
              style: GoogleFonts.playfairDisplay(
                fontSize: 32,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF231F20),
                letterSpacing: -0.3,
              ),
            ),
            const SizedBox(width: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
              decoration: BoxDecoration(
                color: const Color(0xFFF9EFE9),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0xFFEEDCD4)),
              ),
              child: Text(
                '${_cartItems.length} items',
                style: GoogleFonts.dmSans(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFFA6715E),
                ),
              ),
            ),
            const Spacer(),
            if (_cartItems.isNotEmpty)
              MouseRegion(
                cursor: SystemMouseCursors.click,
                child: GestureDetector(
                  onTap: _confirmClearCart,
                  child: Text(
                    'Clear All Items',
                    style: GoogleFonts.dmSans(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFFD63D4A),
                    ),
                  ),
                ),
              ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          'Review your picks and make them yours.',
          style: GoogleFonts.dmSans(
            fontSize: 13,
            color: const Color(0xFF7A6E72),
          ),
        ),
      ],
    );
  }

  // ============================================================
  // CART TABLE CARD
  // ============================================================

  Widget _buildCartTableCard({required bool isWide}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFECE5E7)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 10,
            offset: Offset(0, 3),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        children: [
          // ── Table Column Header ──
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: const BoxDecoration(
              color: Color(0xFFFDFBFB),
              border: Border(
                bottom: BorderSide(color: Color(0xFFF0E8EA), width: 1),
              ),
            ),
            child: Row(
              children: [
                // Checkbox + Select All
                Transform.scale(
                  scale: 0.9,
                  child: Checkbox(
                    tristate: true,
                    value: CartService.instance.selectAllState,
                    activeColor: const Color(0xFF5A4452),
                    checkColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                    side: const BorderSide(color: Color(0xFFB0A2A6), width: 1.4),
                    onChanged: _toggleSelectAll,
                  ),
                ),
                const SizedBox(width: 6),
                Text(
                  'SELECT ALL',
                  style: GoogleFonts.dmSans(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.8,
                    color: const Color(0xFF4A3E47),
                  ),
                ),
                const Spacer(),
                if (isWide) ...[
                  SizedBox(
                    width: 90,
                    child: Text(
                      'UNIT PRICE',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.dmSans(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 0.5,
                        color: const Color(0xFF8E8286),
                      ),
                    ),
                  ),
                  const SizedBox(width: 14),
                  SizedBox(
                    width: 100,
                    child: Text(
                      'QUANTITY',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.dmSans(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 0.5,
                        color: const Color(0xFF8E8286),
                      ),
                    ),
                  ),
                  const SizedBox(width: 14),
                  SizedBox(
                    width: 90,
                    child: Text(
                      'TOTAL',
                      textAlign: TextAlign.right,
                      style: GoogleFonts.dmSans(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 0.5,
                        color: const Color(0xFF8E8286),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                ],
              ],
            ),
          ),

          // ── Items List ──
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _cartItems.length,
            separatorBuilder: (_, __) => const Divider(
              height: 1,
              thickness: 1,
              color: Color(0xFFF3ECEE),
            ),
            itemBuilder: (context, index) {
              final item = _cartItems[index];
              return _buildCartRow(item, index, isWide: isWide);
            },
          ),
        ],
      ),
    );
  }

  Widget _buildCartRow(CartItem item, int index, {required bool isWide}) {
    final variationText = item.safeVariation;
    final displayName = item.safeName;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      child: isWide
          ? Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Selection Checkbox
                Transform.scale(
                  scale: 0.95,
                  child: Checkbox(
                    value: item.selected,
                    activeColor: const Color(0xFF5A4452),
                    checkColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                    side: const BorderSide(color: Color(0xFFB0A2A6), width: 1.4),
                    onChanged: (val) {
                      CartService.instance.toggleItemSelection(index, val);
                    },
                  ),
                ),
                const SizedBox(width: 10),

                // Product Image
                _buildProductThumbnail(item),
                const SizedBox(width: 16),

                // Name, Variation, Remove
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        displayName,
                        style: GoogleFonts.dmSans(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF231F20),
                          height: 1.25,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'Variation: $variationText',
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          color: const Color(0xFF8E8286),
                        ),
                      ),
                      const SizedBox(height: 4),
                      MouseRegion(
                        cursor: SystemMouseCursors.click,
                        child: GestureDetector(
                          onTap: () => _removeItem(index),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(
                                Icons.delete_outline_rounded,
                                size: 14,
                                color: Color(0xFFE04F5F),
                              ),
                              const SizedBox(width: 3),
                              Text(
                                'Remove',
                                style: GoogleFonts.dmSans(
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w600,
                                  color: const Color(0xFFE04F5F),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // Unit Price
                SizedBox(
                  width: 90,
                  child: Text(
                    '₱${_formatCurrency(item.price)}',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.dmSans(
                      fontSize: 13.5,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF231F20),
                    ),
                  ),
                ),
                const SizedBox(width: 14),

                // Quantity Pill
                SizedBox(
                  width: 100,
                  child: Center(child: _buildQuantityPill(item, index)),
                ),
                const SizedBox(width: 14),

                // Total
                SizedBox(
                  width: 90,
                  child: Text(
                    '₱${_formatCurrency(item.price * item.quantity)}',
                    textAlign: TextAlign.right,
                    style: GoogleFonts.dmSans(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: const Color(0xFF231F20),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
              ],
            )
          : Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Transform.scale(
                      scale: 0.95,
                      child: Checkbox(
                        value: item.selected,
                        activeColor: const Color(0xFF5A4452),
                        checkColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                        side: const BorderSide(color: Color(0xFFB0A2A6), width: 1.4),
                        onChanged: (val) {
                          CartService.instance.toggleItemSelection(index, val);
                        },
                      ),
                    ),
                    const SizedBox(width: 8),
                    _buildProductThumbnail(item),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            displayName,
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
                            'Variation: $variationText',
                            style: GoogleFonts.dmSans(
                              fontSize: 12,
                              color: const Color(0xFF8E8286),
                            ),
                          ),
                          const SizedBox(height: 4),
                          MouseRegion(
                            cursor: SystemMouseCursors.click,
                            child: GestureDetector(
                              onTap: () => _removeItem(index),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.delete_outline_rounded,
                                    size: 14,
                                    color: Color(0xFFE04F5F),
                                  ),
                                  const SizedBox(width: 3),
                                  Text(
                                    'Remove',
                                    style: GoogleFonts.dmSans(
                                      fontSize: 11.5,
                                      fontWeight: FontWeight.w600,
                                      color: const Color(0xFFE04F5F),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    _buildQuantityPill(item, index),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          '₱${_formatCurrency(item.price * item.quantity)}',
                          style: GoogleFonts.dmSans(
                            fontSize: 14.5,
                            fontWeight: FontWeight.w800,
                            color: const Color(0xFF231F20),
                          ),
                        ),
                        Text(
                          '₱${_formatCurrency(item.price)} each',
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            color: const Color(0xFF8E8286),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
    );
  }

  Widget _buildProductThumbnail(CartItem item) {
    final imgUrl = item.safeImage;
    return Container(
      width: 64,
      height: 64,
      decoration: BoxDecoration(
        color: const Color(0xFFF7F2F4),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFEDE5E8)),
      ),
      clipBehavior: Clip.antiAlias,
      child: imgUrl.isNotEmpty
          ? Image.network(
              imgUrl,
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => const Icon(
                Icons.shopping_bag_outlined,
                color: Color(0xFF865561),
                size: 26,
              ),
            )
          : const Icon(
              Icons.shopping_bag_outlined,
              color: Color(0xFF865561),
              size: 26,
            ),
    );
  }

  Widget _buildQuantityPill(CartItem item, int index) {
    return Container(
      height: 30,
      decoration: BoxDecoration(
        color: const Color(0xFFFAF7F8),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2D9DC)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _buildPillButton(
            icon: Icons.remove,
            onTap: () => _decreaseQuantity(index),
          ),
          Container(
            constraints: const BoxConstraints(minWidth: 26),
            alignment: Alignment.center,
            padding: const EdgeInsets.symmetric(horizontal: 4),
            child: Text(
              '${item.quantity}',
              style: GoogleFonts.dmSans(
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF231F20),
              ),
            ),
          ),
          _buildPillButton(
            icon: Icons.add,
            onTap: () => _increaseQuantity(index),
          ),
        ],
      ),
    );
  }

  Widget _buildPillButton({
    required IconData icon,
    required VoidCallback onTap,
  }) {
    return MouseRegion(
      cursor: SystemMouseCursors.click,
      child: GestureDetector(
        onTap: onTap,
        behavior: HitTestBehavior.opaque,
        child: Container(
          width: 26,
          height: 28,
          alignment: Alignment.center,
          child: Icon(
            icon,
            size: 13,
            color: const Color(0xFF4A3E47),
          ),
        ),
      ),
    );
  }

  // ============================================================
  // ORDER SUMMARY CARD (RIGHT COLUMN)
  // ============================================================

  Widget _buildOrderSummaryCard() {
    final hasSelection = _selectedCount > 0;
    final shipping = hasSelection ? _shippingFee : 0.0;
    final totalAmount = hasSelection ? (_selectedTotal + shipping) : 0.0;

    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFEDE5E8)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x08000000),
            blurRadius: 14,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Subtle warm accent indicator line at the very top
          Container(
            height: 3,
            width: 48,
            decoration: BoxDecoration(
              color: const Color(0xFFE8B6A8),
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 14),

          Text(
            'Order Summary',
            style: GoogleFonts.playfairDisplay(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF231F20),
            ),
          ),
          const SizedBox(height: 3),
          Text(
            '$_selectedCount item${_selectedCount == 1 ? '' : 's'} selected',
            style: GoogleFonts.dmSans(
              fontSize: 12,
              color: const Color(0xFF8E8286),
            ),
          ),
          const SizedBox(height: 16),
          const Divider(height: 1, color: Color(0xFFF3ECEE)),
          const SizedBox(height: 16),

          // Subtotal
          _buildSummaryLine(
            title: 'Subtotal',
            amount: '₱${_formatCurrency(_selectedTotal)}',
          ),
          const SizedBox(height: 10),

          // Shipping Fee
          _buildSummaryLine(
            title: 'Shipping Fee',
            amount: '₱${_formatCurrency(shipping)}',
          ),
          const SizedBox(height: 16),
          const Divider(height: 1, color: Color(0xFFF3ECEE)),
          const SizedBox(height: 16),

          // Total Amount
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Text(
                'Total Amount',
                style: GoogleFonts.dmSans(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF231F20),
                ),
              ),
              Text(
                '₱${_formatCurrency(totalAmount)}',
                style: GoogleFonts.dmSans(
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                  color: const Color(0xFF231F20),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),

          // Proceed to Checkout button
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: hasSelection ? _checkout : null,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF5A4452),
                disabledBackgroundColor: const Color(0xFFA89CA3),
                foregroundColor: Colors.white,
                disabledForegroundColor: Colors.white70,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    'Proceed to Checkout',
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
          const SizedBox(height: 12),

          // Subnotes
          Center(
            child: Text(
              'Only selected items will be checked out.',
              style: GoogleFonts.dmSans(
                fontSize: 11.5,
                color: const Color(0xFF8E8286),
              ),
            ),
          ),
          const SizedBox(height: 6),
          Center(
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(
                  Icons.lock_outline_rounded,
                  size: 13,
                  color: Color(0xFF8E8286),
                ),
                const SizedBox(width: 4),
                Text(
                  'Secure checkout with 100% Buyer Protection',
                  style: GoogleFonts.dmSans(
                    fontSize: 11,
                    color: const Color(0xFF8E8286),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryLine({
    required String title,
    required String amount,
  }) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          title,
          style: GoogleFonts.dmSans(
            fontSize: 13,
            color: const Color(0xFF6E6266),
          ),
        ),
        Text(
          amount,
          style: GoogleFonts.dmSans(
            fontSize: 13.5,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
      ],
    );
  }

  // ============================================================
  // CONTINUE SHOPPING LINK
  // ============================================================

  Widget _buildContinueShoppingLink() {
    return MouseRegion(
      cursor: SystemMouseCursors.click,
      child: GestureDetector(
        onTap: widget.onBack,
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 6),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.arrow_back_rounded,
                size: 16,
                color: Color(0xFF231F20),
              ),
              const SizedBox(width: 6),
              Text(
                'Continue Shopping',
                style: GoogleFonts.dmSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF231F20),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ============================================================
  // EMPTY CART
  // ============================================================

  Widget _buildEmptyCart() {
    return Center(
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(vertical: 60, horizontal: 24),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFEDE5E8)),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 88,
              height: 88,
              decoration: BoxDecoration(
                color: const Color(0xFFF9EFE9),
                shape: BoxShape.circle,
                border: Border.all(color: const Color(0xFFEEDCD4)),
              ),
              child: const Icon(
                Icons.shopping_bag_outlined,
                size: 42,
                color: Color(0xFFA6715E),
              ),
            ),
            const SizedBox(height: 20),
            Text(
              'Your Cart is Empty',
              style: GoogleFonts.playfairDisplay(
                fontSize: 22,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF231F20),
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'Explore our collection and add your favorite finds to your cart.',
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(
                fontSize: 13.5,
                color: const Color(0xFF7A6E72),
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: widget.onBack,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF5A4452),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: Text(
                'Continue Shopping',
                style: GoogleFonts.dmSans(
                  fontWeight: FontWeight.w700,
                  fontSize: 13.5,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

import 'package:cartzy/widgets/inline_quantity_field.dart';
import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/buyer/account_settings_screen.dart';
import 'package:cartzy/buyer/inbox_screen.dart';
import 'package:cartzy/buddy/buddy_chat_screen.dart';
import 'package:cartzy/products/product_list_screen.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/warehouse_note.dart';

// ──────────────────────────────────────────────────────────────
// CART PALETTE: lavender and rose gold blended together
// ──────────────────────────────────────────────────────────────
class _CartPalette {
  static const lavender = CartzyColors.brandDeep; // #6F6382
  static const lavenderDark = CartzyColors.brandDark; // #564B68
  static const roseGold = Color(0xFFB76E79);
  static const roseGoldDeep = Color(0xFF9C4A5A);
  static const ink = CartzyColors.brand900;
  static const muted = CartzyColors.grayMid;
  static const line = Color(0xFFEDE6EE);

  /// Main brand blend for buttons, checkboxes and accents.
  static const blend = LinearGradient(
    colors: [lavender, roseGold],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  /// Soft blend for chips, page wash and empty states.
  static const softBlend = LinearGradient(
    colors: [CartzyColors.brand100, CartzyColors.rose50Warm],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const disabledBlend = LinearGradient(
    colors: [CartzyColors.brand200, CartzyColors.rose200],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
}

class CartScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback onCheckout;
  final VoidCallback? onProfileClick;
  final VoidCallback? onSaleClick;
  final VoidCallback? onInboxClick;
  final VoidCallback? onHomeClick;
  final VoidCallback? onLogout;

  const CartScreen({
    super.key,
    required this.onBack,
    required this.onCheckout,
    this.onProfileClick,
    this.onSaleClick,
    this.onInboxClick,
    this.onHomeClick,
    this.onLogout,
  });

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen>
    with SingleTickerProviderStateMixin {
  final double _shippingFee = 60.00;
  bool _isNavVisible = true;
  Timer? _scrollStopTimer;

  /// Fades the item cards in one after another when the screen opens.
  late final AnimationController _entrance = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 900),
  );

  /// The swipe hint plays once per app session, on the first card.
  static bool _swipeHintShown = false;

  bool _onScrollNotification(ScrollNotification notification) {
    // Only the page's own up/down scrolling by the user moves the nav, not
    // the auto-sliding hero banner or other sideways carousels inside it.
    if (notification.depth != 0 ||
        notification.metrics.axis != Axis.vertical) {
      return false;
    }

    if (notification.metrics.pixels <= 20) {
      if (!_isNavVisible) {
        setState(() => _isNavVisible = true);
      }
      return false;
    }

    if (notification is ScrollStartNotification) {
      if (_isNavVisible) {
        setState(() => _isNavVisible = false);
      }
    } else if (notification is ScrollUpdateNotification) {
      final delta = notification.scrollDelta ?? 0;
      if (delta.abs() > 1.0) {
        if (_isNavVisible) {
          setState(() => _isNavVisible = false);
        }
        _scrollStopTimer?.cancel();
        _scrollStopTimer = Timer(const Duration(milliseconds: 400), () {
          if (mounted && !_isNavVisible) {
            setState(() => _isNavVisible = true);
          }
        });
      }
    } else if (notification is ScrollEndNotification) {
      _scrollStopTimer?.cancel();
      if (!_isNavVisible) {
        setState(() => _isNavVisible = true);
      }
    }
    return false;
  }

  @override
  void initState() {
    super.initState();
    CartService.instance.addListener(_onCartChanged);
    _entrance.forward();
    _checkStock();
  }

  /// Gets the live stock of every item from the database and tells the
  /// buyer if anything had to change.
  Future<void> _checkStock() async {
    final notes = await CartService.instance.refreshStock();
    if (!mounted || notes.isEmpty) return;
    _showSnack(
      icon: Icons.inventory_2_outlined,
      message: notes.length == 1
          ? notes.first
          : '${notes.length} items changed because of stock updates.',
    );
  }

  @override
  void dispose() {
    _scrollStopTimer?.cancel();
    _entrance.dispose();
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
      numVal = double.tryParse(
              value.toString().replaceAll(RegExp(r'[^0-9.]'), '')) ??
          0.0;
    }
    final parts = numVal.toStringAsFixed(2).split('.');
    final intPart = parts[0].replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]},',
    );
    return '$intPart.${parts[1]}';
  }

  // ============================================================
  // ACTIONS
  // ============================================================

  void _toggleSelectAll() {
    CartService.instance.toggleSelectAll(!CartService.instance.isAllSelected);
  }

  /// Called once the swipe/collapse animation of a card has finished.
  void _removeItem(CartItem item) {
    final index = _cartItems.indexWhere((i) => i.id == item.id);
    if (index < 0) return;
    CartService.instance.removeItemById(item.id);

    _showSnack(
      icon: Icons.delete_outline_rounded,
      message: '${item.safeName} removed',
      actionLabel: 'UNDO',
      onAction: () => CartService.instance.insertItem(index, item),
    );
  }

  void _showSnack({
    required IconData icon,
    required String message,
    String? actionLabel,
    VoidCallback? onAction,
  }) {
    final messenger = ScaffoldMessenger.of(context);
    messenger.hideCurrentSnackBar();
    messenger.showSnackBar(
      SnackBar(
        behavior: SnackBarBehavior.floating,
        backgroundColor: _CartPalette.ink,
        elevation: 0,
        duration: const Duration(seconds: 4),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        content: Row(
          children: [
            Container(
              width: 28,
              height: 28,
              decoration: const BoxDecoration(
                shape: BoxShape.circle,
                gradient: _CartPalette.blend,
              ),
              child: Icon(icon, size: 15, color: Colors.white),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                message,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: GoogleFonts.dmSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w500,
                  color: Colors.white,
                ),
              ),
            ),
          ],
        ),
        action: actionLabel == null
            ? null
            : SnackBarAction(
                label: actionLabel,
                textColor: CartzyColors.rose200,
                onPressed: onAction ?? () {},
              ),
      ),
    );
  }

  /// Quantity typed straight into the stepper; limited to the stock.
  void _setTypedQuantity(int index, CartItem item, int typed) {
    final maxStock =
        (item.stock != null && item.stock! > 0) ? item.stock! : null;
    final applied = CartService.instance.setQuantity(index, typed);
    if (maxStock != null && typed > maxStock) {
      _showSnack(
        icon: Icons.inventory_2_outlined,
        message: 'Only $maxStock available. Quantity updated to $applied.',
      );
    }
  }

  void _confirmClearCart() {
    showDialog(
      context: context,
      barrierColor: _CartPalette.ink.withValues(alpha: 0.35),
      builder: (ctx) => Dialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        insetPadding: const EdgeInsets.symmetric(horizontal: 32),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(24, 28, 24, 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 60,
                height: 60,
                decoration: const BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: _CartPalette.softBlend,
                ),
                child: const Icon(
                  Icons.remove_shopping_cart_outlined,
                  color: _CartPalette.roseGold,
                  size: 28,
                ),
              ),
              const SizedBox(height: 16),
              Text(
                'Clear your cart?',
                style: GoogleFonts.playfairDisplay(
                  fontWeight: FontWeight.w700,
                  fontSize: 21,
                  color: _CartPalette.ink,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'All ${_cartItems.length} item${_cartItems.length == 1 ? '' : 's'} will be removed from your cart.',
                textAlign: TextAlign.center,
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  height: 1.4,
                  color: _CartPalette.muted,
                ),
              ),
              const SizedBox(height: 22),
              Row(
                children: [
                  Expanded(
                    child: _OutlineBlendButton(
                      label: 'Keep items',
                      onTap: () => Navigator.of(ctx).pop(),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _BlendButton(
                      label: 'Clear all',
                      height: 46,
                      onTap: () {
                        Navigator.of(ctx).pop();
                        final snapshot = List<CartItem>.from(_cartItems);
                        CartService.instance.clearAll();
                        _showSnack(
                          icon: Icons.remove_shopping_cart_outlined,
                          message: 'Cart cleared',
                          actionLabel: 'UNDO',
                          onAction: () {
                            for (var i = 0; i < snapshot.length; i++) {
                              CartService.instance.insertItem(i, snapshot[i]);
                            }
                          },
                        );
                      },
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _checkout() {
    if (CartService.instance.selectedItems.isEmpty) {
      _showSnack(
        icon: Icons.info_outline_rounded,
        message: 'Select at least one item to check out.',
      );
      return;
    }
    widget.onCheckout();
  }

  // ============================================================
  // BUILD
  // ============================================================

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      extendBody: true,
      backgroundColor: CartzyColors.brand50Lavender,
      body: DecoratedBox(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [CartzyColors.brand50Lavender, CartzyColors.rose50Warm],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: SafeArea(
          child: Stack(
            children: [
              NotificationListener<ScrollNotification>(
                onNotification: _onScrollNotification,
                child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(
                parent: BouncingScrollPhysics(),
              ),
              padding: const EdgeInsets.fromLTRB(18, 14, 18, 110),
              child: Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 1140),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildHeader(),
                      const SizedBox(height: 20),
                      AnimatedSwitcher(
                        duration: const Duration(milliseconds: 350),
                        switchInCurve: Curves.easeOutCubic,
                        switchOutCurve: Curves.easeInCubic,
                        child: _cartItems.isEmpty
                            ? _buildEmptyCart()
                            : LayoutBuilder(
                                key: const ValueKey('cart-content'),
                                builder: (context, constraints) {
                                  final isWide = constraints.maxWidth >= 880;
                                  if (isWide) {
                                    return Row(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Expanded(child: _buildItemsSection()),
                                        const SizedBox(width: 24),
                                        SizedBox(
                                          width: 340,
                                          child: _buildOrderSummaryCard(),
                                        ),
                                      ],
                                    );
                                  }
                                  return Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      _buildItemsSection(),
                                      const SizedBox(height: 8),
                                      _buildOrderSummaryCard(),
                                    ],
                                  );
                                },
                              ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
              ),
              // ── Cozy Bear Floating Companion ──
              ValueListenableBuilder<int>(
                valueListenable: BuddyLauncher.summonTrigger,
                builder: (context, _, __) =>
                    const BuddyLauncher(aboveNav: false),
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: _buildBottomNav(),
    );
  }

  // ============================================================
  // HEADER
  // ============================================================

  Widget _buildHeader() {
    final count = _cartItems.length;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            _CircleIconButton(
              icon: Icons.arrow_back_ios_new_rounded,
              onTap: widget.onBack,
              tooltip: 'Continue shopping',
            ),
            const Spacer(),
            AnimatedSwitcher(
              duration: const Duration(milliseconds: 250),
              child: count == 0
                  ? const SizedBox.shrink()
                  : _ClearAllChip(onTap: _confirmClearCart),
            ),
          ],
        ),
        const SizedBox(height: 18),
        ShaderMask(
          shaderCallback: (rect) => _CartPalette.blend.createShader(rect),
          child: Text(
            'YOUR NEXT FINDS',
            style: GoogleFonts.dmSans(
              fontSize: 10.5,
              fontWeight: FontWeight.w800,
              letterSpacing: 1.6,
              color: Colors.white,
            ),
          ),
        ),
        const SizedBox(height: 4),
        Row(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Flexible(
              child: Text(
                'My Cart',
                style: GoogleFonts.playfairDisplay(
                  fontSize: 32,
                  fontWeight: FontWeight.w700,
                  color: _CartPalette.ink,
                  letterSpacing: -0.3,
                ),
              ),
            ),
            const SizedBox(width: 10),
            AnimatedSwitcher(
              duration: const Duration(milliseconds: 250),
              transitionBuilder: (child, anim) =>
                  ScaleTransition(scale: anim, child: child),
              child: Container(
                key: ValueKey(count),
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  gradient: _CartPalette.blend,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '$count item${count == 1 ? '' : 's'}',
                  style: GoogleFonts.dmSans(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          'Review your picks and make them yours.',
          style: GoogleFonts.dmSans(fontSize: 13, color: _CartPalette.muted),
        ),
      ],
    );
  }

  // ============================================================
  // ITEMS
  // ============================================================

  Widget _buildItemsSection() {
    final items = _cartItems;
    final peekFirst = !_swipeHintShown;
    if (peekFirst && items.isNotEmpty) _swipeHintShown = true;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        _buildSelectAllBar(),
        const SizedBox(height: 12),
        for (var i = 0; i < items.length; i++)
          _StaggeredEntrance(
            key: ValueKey('entrance-${items[i].id}'),
            controller: _entrance,
            index: i,
            child: _SwipeToRemove(
              key: ValueKey('swipe-${items[i].id}'),
              peek: peekFirst && i == 0,
              onRemoved: () => _removeItem(items[i]),
              builder: (context, remove) =>
                  _buildItemCard(items[i], i, remove),
            ),
          ),
      ],
    );
  }

  Widget _buildSelectAllBar() {
    final state = CartService.instance.selectAllState;
    return Container(
      padding: const EdgeInsets.fromLTRB(8, 6, 14, 6),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.85),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _CartPalette.line),
      ),
      child: Row(
        children: [
          _BlendCheckbox(value: state, onTap: _toggleSelectAll),
          const SizedBox(width: 4),
          Text(
            'Select all',
            style: GoogleFonts.dmSans(
              fontSize: 13,
              fontWeight: FontWeight.w700,
              color: _CartPalette.ink,
            ),
          ),
          const Spacer(),
          const Icon(Icons.swipe_left_rounded,
              size: 15, color: _CartPalette.muted),
          const SizedBox(width: 5),
          Flexible(
            child: Text(
              'Swipe left to remove',
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: GoogleFonts.dmSans(
                fontSize: 11.5,
                color: _CartPalette.muted,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildItemCard(CartItem item, int index, VoidCallback remove) {
    final original = CartService.parsePrice(item.originalPrice);
    final hasDiscount = original > item.price;

    return AnimatedContainer(
      duration: const Duration(milliseconds: 220),
      padding: const EdgeInsets.fromLTRB(6, 12, 12, 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: item.selected
              ? CartzyColors.rose200
              : _CartPalette.line,
          width: item.selected ? 1.3 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: _CartPalette.lavender
                .withValues(alpha: item.selected ? 0.10 : 0.05),
            blurRadius: 18,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          _BlendCheckbox(
            value: item.selected && !item.isOutOfStock,
            onTap: () {
              if (item.isOutOfStock) {
                _showSnack(
                  icon: Icons.inventory_2_outlined,
                  message: '${item.safeName} is out of stock.',
                );
                return;
              }
              CartService.instance.toggleItemSelection(index, !item.selected);
            },
          ),
          AnimatedOpacity(
            duration: const Duration(milliseconds: 220),
            opacity: item.isOutOfStock ? 0.4 : (item.selected ? 1 : 0.6),
            child: _buildProductThumbnail(item),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Text(
                        item.safeName,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.dmSans(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w700,
                          height: 1.25,
                          color: _CartPalette.ink,
                        ),
                      ),
                    ),
                    const SizedBox(width: 4),
                    _SmallIconButton(
                      icon: Icons.delete_outline_rounded,
                      tooltip: 'Remove',
                      onTap: remove,
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    gradient: _CartPalette.softBlend,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    item.safeVariation,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.dmSans(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: _CartPalette.lavender,
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          FittedBox(
                            fit: BoxFit.scaleDown,
                            alignment: Alignment.centerLeft,
                            child: _AnimatedAmount(
                              value: item.subtotal,
                              style: GoogleFonts.dmSans(
                                fontSize: 15.5,
                                fontWeight: FontWeight.w800,
                                color: _CartPalette.roseGoldDeep,
                              ),
                            ),
                          ),
                          Text(
                            hasDiscount
                                ? '₱${_formatCurrency(item.price)}  ·  ₱${_formatCurrency(original)}'
                                : '₱${_formatCurrency(item.price)} each',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.dmSans(
                              fontSize: 11,
                              color: _CartPalette.muted,
                            ),
                          ),
                          if (!item.isOutOfStock &&
                              item.stock != null &&
                              (item.atStockLimit || item.stock! <= 5))
                            Padding(
                              padding: const EdgeInsets.only(top: 4),
                              child: _StockChip(
                                label: item.atStockLimit
                                    ? 'Max stock reached (${item.stock})'
                                    : 'Only ${item.stock} left',
                              ),
                            ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    if (item.isOutOfStock)
                      const _StockChip(label: 'Out of stock', strong: true)
                    else
                      _QuantityStepper(
                        quantity: item.quantity,
                        canIncrease: !item.atStockLimit,
                        onIncreaseBlocked: () => _showSnack(
                          icon: Icons.inventory_2_outlined,
                          message:
                              'Only ${item.stock} available for ${item.safeName}.',
                        ),
                        onDecrease: () =>
                            CartService.instance.decreaseQuantity(index),
                        onIncrease: () =>
                            CartService.instance.increaseQuantity(index),
                        onQuantityTyped: (typed) =>
                            _setTypedQuantity(index, item, typed),
                      ),
                  ],
                ),
                if (WarehouseShipping.applies(item.quantity))
                  const Padding(
                    padding: EdgeInsets.only(top: 8),
                    child: WarehouseShippingNote(compact: true),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProductThumbnail(CartItem item) {
    final imgUrl = item.safeImage;
    const fallback = Icon(
      Icons.shopping_bag_outlined,
      color: _CartPalette.roseGold,
      size: 28,
    );
    return Container(
      width: 82,
      height: 82,
      decoration: BoxDecoration(
        gradient: _CartPalette.softBlend,
        borderRadius: BorderRadius.circular(16),
      ),
      clipBehavior: Clip.antiAlias,
      child: imgUrl.isNotEmpty
          ? Image.network(
              imgUrl,
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => fallback,
            )
          : fallback,
    );
  }

  // ============================================================
  // ORDER SUMMARY
  // ============================================================

  Widget _buildOrderSummaryCard() {
    final hasSelection = _selectedCount > 0;
    final shipping = hasSelection ? _shippingFee : 0.0;
    final totalAmount = hasSelection ? (_selectedTotal + shipping) : 0.0;

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 14, 14, 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: _CartPalette.line),
        boxShadow: [
          BoxShadow(
            color: _CartPalette.lavender.withValues(alpha: 0.08),
            blurRadius: 20,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildSummaryLine(
            'Subtotal ($_selectedCount item${_selectedCount == 1 ? '' : 's'})',
            _selectedTotal,
          ),
          const SizedBox(height: 6),
          _buildSummaryLine('Shipping', shipping),
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 12),
            child: Divider(height: 1, thickness: 1, color: _CartPalette.line),
          ),
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      'Total',
                      style: GoogleFonts.dmSans(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w600,
                        color: _CartPalette.muted,
                      ),
                    ),
                    FittedBox(
                      fit: BoxFit.scaleDown,
                      alignment: Alignment.centerLeft,
                      child: ShaderMask(
                        shaderCallback: (rect) =>
                            _CartPalette.blend.createShader(rect),
                        child: _AnimatedAmount(
                          value: totalAmount,
                          style: GoogleFonts.dmSans(
                            fontSize: 21,
                            fontWeight: FontWeight.w800,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 12),
              SizedBox(
                width: 150,
                child: _BlendButton(
                  label: 'Checkout',
                  icon: Icons.arrow_forward_rounded,
                  height: 46,
                  enabled: hasSelection,
                  onTap: _checkout,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryLine(String title, double amount) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          title,
          style: GoogleFonts.dmSans(fontSize: 12.5, color: _CartPalette.muted),
        ),
        _AnimatedAmount(
          value: amount,
          style: GoogleFonts.dmSans(
            fontSize: 13,
            fontWeight: FontWeight.w700,
            color: _CartPalette.ink,
          ),
        ),
      ],
    );
  }

  // ============================================================
  // EMPTY CART
  // ============================================================

  Widget _buildEmptyCart() {
    return Container(
      key: const ValueKey('cart-empty'),
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 56, horizontal: 24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: _CartPalette.line),
      ),
      child: Column(
        children: [
          Container(
            width: 104,
            height: 104,
            decoration: const BoxDecoration(
              shape: BoxShape.circle,
              gradient: _CartPalette.softBlend,
            ),
            child: Center(
              child: ShaderMask(
                shaderCallback: (rect) => _CartPalette.blend.createShader(rect),
                child: const Icon(
                  Icons.shopping_bag_outlined,
                  size: 48,
                  color: Colors.white,
                ),
              ),
            ),
          ),
          const SizedBox(height: 22),
          Text(
            'Your cart is empty',
            style: GoogleFonts.playfairDisplay(
              fontSize: 22,
              fontWeight: FontWeight.w700,
              color: _CartPalette.ink,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Explore our collection and add your favorite finds.',
            textAlign: TextAlign.center,
            style: GoogleFonts.dmSans(
              fontSize: 13.5,
              height: 1.4,
              color: _CartPalette.muted,
            ),
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: 220,
            child: _BlendButton(
              label: 'Start Shopping',
              icon: Icons.arrow_forward_rounded,
              onTap: widget.onBack,
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // BOTTOM NAV
  // ============================================================

  Widget _buildBottomNav() {
    return SafeArea(
      top: false,
      child: AnimatedSlide(
        offset: _isNavVisible ? Offset.zero : const Offset(0, 1.8),
        duration: const Duration(milliseconds: 260),
        curve: Curves.easeInOutCubic,
        child: AnimatedOpacity(
          opacity: _isNavVisible ? 1.0 : 0.0,
          duration: const Duration(milliseconds: 200),
          child: Container(
            margin: const EdgeInsets.fromLTRB(16, 8, 16, 12),
            // Same bar style as the Home / Sale / Profile screens.
            decoration: const ShapeDecoration(
              color: CartzyColors.surface,
              shape: StadiumBorder(
                side: BorderSide(color: CartzyColors.border),
              ),
              shadows: [
                BoxShadow(
                  color: Color(0x148B3A3A),
                  blurRadius: 16,
                  offset: Offset(0, 4),
                ),
              ],
            ),
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
            child: Row(
              children: [
                _CartBottomNavItem(
                  icon: Icons.home_rounded,
                  label: 'Home',
                  selected: false,
                  onClick: widget.onHomeClick ?? widget.onBack,
                ),
                _CartBottomNavItem(
                  icon: Icons.shopping_cart_rounded,
                  label: 'Cart',
                  selected: true,
                  onClick: () {},
                ),
                _CartBottomNavItem(
                  icon: Icons.local_offer_outlined,
                  label: 'Sale',
                  selected: false,
                  isSale: true,
                  onClick: () {
                    if (widget.onSaleClick != null) {
                      widget.onSaleClick!();
                    } else {
                      Navigator.of(context).pushReplacement(
                        MaterialPageRoute(
                          builder: (_) => ProductListScreen(
                            initialCategory: 'Flash Deals',
                            title: 'Sale & Flash Deals',
                            onBack: widget.onHomeClick ?? widget.onBack,
                            onHomeClick: widget.onHomeClick ?? widget.onBack,
                            onCartClick: () => Navigator.of(context).pop(),
                            onSaleClick: () {},
                            onProfileClick: widget.onProfileClick,
                          ),
                        ),
                      );
                    }
                  },
                ),
                _CartBottomNavItem(
                  icon: Icons.mail_outline_rounded,
                  label: 'Inbox',
                  selected: false,
                  onClick: () {
                    if (widget.onInboxClick != null) {
                      widget.onInboxClick!();
                    } else {
                      Navigator.of(context).pushReplacement(
                        MaterialPageRoute(
                          builder: (_) => InboxScreen(
                            onBack: widget.onHomeClick ?? widget.onBack,
                            onHomeClick: widget.onHomeClick ?? widget.onBack,
                            onCartClick: () => Navigator.of(context).pop(),
                            onSaleClick: widget.onSaleClick,
                            onProfileClick: widget.onProfileClick,
                          ),
                        ),
                      );
                    }
                  },
                ),
                _CartBottomNavItem(
                  icon: Icons.person_outline_rounded,
                  label: 'Profile',
                  selected: false,
                  onClick: () {
                    if (widget.onProfileClick != null) {
                      widget.onProfileClick!();
                    } else {
                      Navigator.of(context).pushReplacement(
                        MaterialPageRoute(
                          builder: (_) => AccountSettingsScreen(
                            isLoggedIn: true,
                            userName: 'Buyer',
                            userEmail: '',
                            onBack: widget.onHomeClick ?? widget.onBack,
                            onHomeClick: widget.onHomeClick ?? widget.onBack,
                            onCartClick: () => Navigator.of(context).pop(),
                            onSaleClick: widget.onSaleClick,
                            onLogout: () async {
                              if (widget.onLogout != null) {
                                widget.onLogout!();
                              } else {
                                await AuthService.logout();
                                if (mounted) {
                                  Navigator.of(context)
                                      .popUntil((route) => route.isFirst);
                                }
                              }
                            },
                          ),
                        ),
                      );
                    }
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ──────────────────────────────────────────────────────────────
// SWIPE TO REMOVE
// Drag a card left to reveal the remove panel. Past the threshold
// (or with a quick fling) the card slides out, the row collapses,
// and then [onRemoved] runs. The trash button uses the same animation.
// ──────────────────────────────────────────────────────────────
class _SwipeToRemove extends StatefulWidget {
  final Widget Function(BuildContext context, VoidCallback remove) builder;
  final VoidCallback onRemoved;
  final bool peek;

  const _SwipeToRemove({
    super.key,
    required this.builder,
    required this.onRemoved,
    this.peek = false,
  });

  @override
  State<_SwipeToRemove> createState() => _SwipeToRemoveState();
}

class _SwipeToRemoveState extends State<_SwipeToRemove>
    with TickerProviderStateMixin {
  static const double _threshold = 0.38;
  static const double _radius = 20;

  /// How far the card has slid left, as a fraction of its width.
  late final AnimationController _slide = AnimationController(vsync: this);

  /// Row height: 1 = full, 0 = collapsed.
  late final AnimationController _collapse = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 280),
    value: 1,
  );

  double _width = 1;
  bool _armed = false;
  bool _removing = false;

  @override
  void initState() {
    super.initState();
    if (widget.peek) _playPeekHint();
  }

  /// Nudges the card left once so people can see it can be swiped.
  Future<void> _playPeekHint() async {
    await Future<void>.delayed(const Duration(milliseconds: 1100));
    if (!mounted || _removing || _slide.value != 0) return;
    await _slide.animateTo(0.2,
        duration: const Duration(milliseconds: 420),
        curve: Curves.easeOutCubic);
    await Future<void>.delayed(const Duration(milliseconds: 350));
    if (!mounted || _removing) return;
    _slide.animateTo(0,
        duration: const Duration(milliseconds: 520),
        curve: Curves.easeInOutCubic);
  }

  @override
  void dispose() {
    _slide.dispose();
    _collapse.dispose();
    super.dispose();
  }

  void _setArmed(bool value) {
    if (_armed == value) return;
    setState(() => _armed = value);
    if (value) HapticFeedback.selectionClick();
  }

  void _onDragStart(DragStartDetails _) {
    if (_removing) return;
    _slide.stop();
  }

  void _onDragUpdate(DragUpdateDetails details) {
    if (_removing) return;
    final delta = (details.primaryDelta ?? 0) / _width;
    _slide.value = (_slide.value - delta).clamp(0.0, 1.0);
    _setArmed(_slide.value >= _threshold);
  }

  void _onDragEnd(DragEndDetails details) {
    if (_removing) return;
    final velocity = details.primaryVelocity ?? 0;
    final flung = velocity < -900 && _slide.value > 0.08;
    if (_slide.value >= _threshold || flung) {
      _remove();
    } else {
      _setArmed(false);
      _slide.animateTo(0,
          duration: const Duration(milliseconds: 320),
          curve: Curves.easeOutCubic);
    }
  }

  Future<void> _remove() async {
    if (_removing) return;
    setState(() {
      _removing = true;
      _armed = true;
    });
    HapticFeedback.mediumImpact();
    await _slide.animateTo(1,
        duration: Duration(milliseconds: (300 * (1 - _slide.value)).round() + 80),
        curve: Curves.easeInCubic);
    if (!mounted) return;
    await _collapse.animateTo(0, curve: Curves.easeInOutCubic);
    if (!mounted) return;
    widget.onRemoved();
  }

  @override
  Widget build(BuildContext context) {
    return SizeTransition(
      sizeFactor: _collapse,
      axisAlignment: -1,
      child: FadeTransition(
        opacity: _collapse,
        child: Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: LayoutBuilder(
            builder: (context, constraints) {
              _width = math.max(constraints.maxWidth, 1);
              return GestureDetector(
                onHorizontalDragStart: _onDragStart,
                onHorizontalDragUpdate: _onDragUpdate,
                onHorizontalDragEnd: _onDragEnd,
                child: Stack(
                  children: [
                    Positioned.fill(child: _buildRemovePanel()),
                    AnimatedBuilder(
                      animation: _slide,
                      builder: (context, child) => Transform.translate(
                        offset: Offset(-_slide.value * _width, 0),
                        child: child,
                      ),
                      child: widget.builder(context, _remove),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _buildRemovePanel() {
    return AnimatedBuilder(
      animation: _slide,
      builder: (context, _) {
        final t = _slide.value;
        final progress = (t / _threshold).clamp(0.0, 1.0);
        final revealed = math.max(t * _width, 92.0);

        return Opacity(
          // Hidden at rest so no colour leaks around the card's corners.
          opacity: (t * 10).clamp(0.0, 1.0),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 220),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(_radius),
              gradient: _armed
                  ? const LinearGradient(
                      colors: [
                        _CartPalette.lavender,
                        _CartPalette.roseGold,
                        _CartPalette.roseGoldDeep,
                      ],
                      stops: [0.0, 0.55, 1.0],
                    )
                  : const LinearGradient(
                      colors: [CartzyColors.brand100, CartzyColors.rose200],
                    ),
            ),
            child: Align(
              alignment: Alignment.centerRight,
              child: SizedBox(
                width: revealed,
                child: Center(
                  child: Opacity(
                    opacity: progress,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        AnimatedScale(
                          scale: _armed ? 1.12 : 0.75 + 0.25 * progress,
                          duration: const Duration(milliseconds: 220),
                          curve: Curves.easeOutBack,
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 220),
                            width: 42,
                            height: 42,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: _armed
                                  ? Colors.white.withValues(alpha: 0.22)
                                  : Colors.white,
                            ),
                            child: Icon(
                              _armed
                                  ? Icons.delete_rounded
                                  : Icons.delete_outline_rounded,
                              size: 21,
                              color: _armed
                                  ? Colors.white
                                  : _CartPalette.roseGold,
                            ),
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          _armed ? 'Release' : 'Remove',
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 0.3,
                            color: _armed
                                ? Colors.white
                                : _CartPalette.roseGoldDeep,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}

// ──────────────────────────────────────────────────────────────
// STAGGERED ENTRANCE
// ──────────────────────────────────────────────────────────────
class _StaggeredEntrance extends StatelessWidget {
  final AnimationController controller;
  final int index;
  final Widget child;

  const _StaggeredEntrance({
    super.key,
    required this.controller,
    required this.index,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    final start = (math.min(index, 6) * 0.08).clamp(0.0, 0.6);
    final curved = CurvedAnimation(
      parent: controller,
      curve: Interval(start, math.min(start + 0.45, 1.0),
          curve: Curves.easeOutCubic),
    );
    return FadeTransition(
      opacity: curved,
      child: SlideTransition(
        position: Tween(begin: const Offset(0, 0.12), end: Offset.zero)
            .animate(curved),
        child: child,
      ),
    );
  }
}

// ──────────────────────────────────────────────────────────────
// SMALL BUILDING BLOCKS
// ──────────────────────────────────────────────────────────────

/// Round checkbox that fills with the lavender → rose gold blend.
/// `value == null` shows the "some selected" dash.
class _BlendCheckbox extends StatelessWidget {
  final bool? value;
  final VoidCallback onTap;

  const _BlendCheckbox({required this.value, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final on = value != false;
    return Semantics(
      checked: value == true,
      button: true,
      child: GestureDetector(
        onTap: () {
          HapticFeedback.selectionClick();
          onTap();
        },
        behavior: HitTestBehavior.opaque,
        child: Padding(
          padding: const EdgeInsets.all(10),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            curve: Curves.easeOut,
            width: 22,
            height: 22,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: on ? null : Colors.white,
              gradient: on ? _CartPalette.blend : null,
              border: Border.all(
                color: on ? Colors.transparent : CartzyColors.brand400,
                width: 1.6,
              ),
              boxShadow: on
                  ? [
                      BoxShadow(
                        color: _CartPalette.roseGold.withValues(alpha: 0.30),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ]
                  : const [],
            ),
            child: AnimatedScale(
              scale: on ? 1 : 0,
              duration: const Duration(milliseconds: 200),
              curve: Curves.easeOutBack,
              child: Icon(
                value == null ? Icons.remove_rounded : Icons.check_rounded,
                size: 14,
                color: Colors.white,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _StockChip extends StatelessWidget {
  final String label;
  final bool strong;

  const _StockChip({required this.label, this.strong = false});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: strong ? _CartPalette.roseGoldDeep : CartzyColors.rose50Warm,
        borderRadius: BorderRadius.circular(8),
        border: strong ? null : Border.all(color: CartzyColors.rose200),
      ),
      child: Text(
        label,
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
        style: GoogleFonts.dmSans(
          fontSize: 10.5,
          fontWeight: FontWeight.w700,
          color: strong ? Colors.white : _CartPalette.roseGoldDeep,
        ),
      ),
    );
  }
}

class _QuantityStepper extends StatelessWidget {
  final int quantity;
  final VoidCallback onDecrease;
  final VoidCallback onIncrease;
  final bool canIncrease;
  final VoidCallback? onIncreaseBlocked;
  final ValueChanged<int>? onQuantityTyped;

  const _QuantityStepper({
    required this.quantity,
    required this.onDecrease,
    required this.onIncrease,
    this.canIncrease = true,
    this.onIncreaseBlocked,
    this.onQuantityTyped,
  });

  @override
  Widget build(BuildContext context) {
    final canDecrease = quantity > 1;
    return Container(
      padding: const EdgeInsets.all(3),
      decoration: BoxDecoration(
        color: CartzyColors.brand100,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _StepperButton(
            icon: Icons.remove_rounded,
            filled: false,
            enabled: canDecrease,
            onTap: onDecrease,
          ),
          // Tap the number to type it with the keyboard.
          InlineQuantityField(
            quantity: quantity,
            onCommitted: (typed) => onQuantityTyped?.call(typed),
            enabled: onQuantityTyped != null,
            style: GoogleFonts.dmSans(
              fontSize: 13,
              fontWeight: FontWeight.w800,
              color: _CartPalette.ink,
            ),
          ),
          _StepperButton(
            icon: Icons.add_rounded,
            filled: true,
            enabled: canIncrease,
            onTap: onIncrease,
            onDisabledTap: onIncreaseBlocked,
          ),
        ],
      ),
    );
  }
}

class _StepperButton extends StatelessWidget {
  final IconData icon;
  final bool filled;
  final bool enabled;
  final VoidCallback onTap;

  const _StepperButton({
    required this.icon,
    required this.filled,
    required this.enabled,
    required this.onTap,
    this.onDisabledTap,
  });

  final VoidCallback? onDisabledTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: enabled
          ? () {
              HapticFeedback.selectionClick();
              onTap();
            }
          : onDisabledTap,
      behavior: HitTestBehavior.opaque,
      child: AnimatedOpacity(
        duration: const Duration(milliseconds: 180),
        opacity: enabled ? 1 : 0.4,
        child: Container(
          width: 28,
          height: 28,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            gradient: filled ? _CartPalette.blend : null,
            color: filled ? null : Colors.white,
          ),
          child: Icon(
            icon,
            size: 15,
            color: filled ? Colors.white : _CartPalette.lavender,
          ),
        ),
      ),
    );
  }
}

class _SmallIconButton extends StatelessWidget {
  final IconData icon;
  final String tooltip;
  final VoidCallback onTap;

  const _SmallIconButton({
    required this.icon,
    required this.tooltip,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Tooltip(
      message: tooltip,
      child: InkResponse(
        onTap: onTap,
        radius: 20,
        child: Padding(
          padding: const EdgeInsets.all(4),
          child: Icon(icon, size: 19, color: CartzyColors.rose400),
        ),
      ),
    );
  }
}

class _CircleIconButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  final String tooltip;

  const _CircleIconButton({
    required this.icon,
    required this.onTap,
    required this.tooltip,
  });

  @override
  Widget build(BuildContext context) {
    return Tooltip(
      message: tooltip,
      child: Material(
        color: Colors.white,
        shape: const CircleBorder(side: BorderSide(color: _CartPalette.line)),
        child: InkWell(
          customBorder: const CircleBorder(),
          onTap: onTap,
          child: SizedBox(
            width: 42,
            height: 42,
            child: Icon(icon, size: 16, color: _CartPalette.ink),
          ),
        ),
      ),
    );
  }
}

class _ClearAllChip extends StatelessWidget {
  final VoidCallback onTap;

  const _ClearAllChip({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      shape: const StadiumBorder(side: BorderSide(color: CartzyColors.rose200)),
      child: InkWell(
        customBorder: const StadiumBorder(),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.delete_sweep_outlined,
                  size: 16, color: _CartPalette.roseGoldDeep),
              const SizedBox(width: 6),
              Text(
                'Clear all',
                style: GoogleFonts.dmSans(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w700,
                  color: _CartPalette.roseGoldDeep,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Primary button filled with the lavender → rose gold blend.
class _BlendButton extends StatelessWidget {
  final String label;
  final IconData? icon;
  final VoidCallback onTap;
  final bool enabled;
  final double height;

  const _BlendButton({
    required this.label,
    required this.onTap,
    this.icon,
    this.enabled = true,
    this.height = 52,
  });

  @override
  Widget build(BuildContext context) {
    final radius = BorderRadius.circular(16);
    return AnimatedContainer(
      duration: const Duration(milliseconds: 250),
      height: height,
      decoration: BoxDecoration(
        borderRadius: radius,
        gradient: enabled ? _CartPalette.blend : _CartPalette.disabledBlend,
        boxShadow: enabled
            ? [
                BoxShadow(
                  color: _CartPalette.roseGold.withValues(alpha: 0.35),
                  blurRadius: 16,
                  offset: const Offset(0, 6),
                ),
              ]
            : const [],
      ),
      child: Material(
        type: MaterialType.transparency,
        child: InkWell(
          borderRadius: radius,
          onTap: enabled ? onTap : null,
          splashColor: Colors.white24,
          highlightColor: Colors.white10,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Flexible(
                child: Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: GoogleFonts.dmSans(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.2,
                    color: enabled ? Colors.white : _CartPalette.muted,
                  ),
                ),
              ),
              if (icon != null) ...[
                const SizedBox(width: 8),
                Icon(icon,
                    size: 17,
                    color: enabled ? Colors.white : _CartPalette.muted),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _OutlineBlendButton extends StatelessWidget {
  final String label;
  final VoidCallback onTap;

  const _OutlineBlendButton({required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: CartzyColors.brand100,
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: SizedBox(
          height: 46,
          child: Center(
            child: Text(
              label,
              style: GoogleFonts.dmSans(
                fontSize: 14,
                fontWeight: FontWeight.w700,
                color: _CartPalette.lavenderDark,
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Peso amount that counts smoothly to its new value when it changes.
class _AnimatedAmount extends StatelessWidget {
  final double value;
  final TextStyle style;

  const _AnimatedAmount({required this.value, required this.style});

  @override
  Widget build(BuildContext context) {
    return TweenAnimationBuilder<double>(
      tween: Tween<double>(end: value),
      duration: const Duration(milliseconds: 450),
      curve: Curves.easeOutCubic,
      builder: (context, v, _) => Text(
        '₱${_CartScreenState._formatCurrency(v)}',
        style: style,
      ),
    );
  }
}

// ──────────────────────────────────────────────────────────────
// CART BOTTOM NAV ITEM
// ──────────────────────────────────────────────────────────────
class _CartBottomNavItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final bool isSale;
  final VoidCallback onClick;

  const _CartBottomNavItem({
    required this.icon,
    required this.label,
    required this.selected,
    this.isSale = false,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    // Same colors as the nav on every other screen.
    final highlighted = selected || isSale;
    final color = highlighted ? CartzyColors.burgundy : CartzyColors.gray;

    return Expanded(
      child: InkWell(
        onTap: onClick,
        borderRadius: BorderRadius.circular(8),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, color: color, size: 22),
              const SizedBox(height: 3),
              Text(
                label,
                style: GoogleFonts.dmSans(
                  fontSize: 11,
                  color: color,
                  fontWeight: highlighted ? FontWeight.w600 : FontWeight.w400,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 2),
              AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                height: 2,
                width: selected ? 16 : 0,
                decoration: BoxDecoration(
                  color: CartzyColors.burgundy,
                  borderRadius: BorderRadius.circular(1),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

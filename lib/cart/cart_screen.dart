import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';
import 'package:cartzy/utils/product_images.dart';

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

// ============================================================
// CART SCREEN STATE
// ============================================================

class _CartScreenState extends State<CartScreen> {
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
  bool get _allSelected => CartService.instance.isAllSelected;

  void _toggleSelectAll(bool? value) {
    CartService.instance.toggleSelectAll(value);
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
          backgroundColor: CartzyColors.navy,
          duration: const Duration(seconds: 2),
        ),
      );
    }
  }

  void _confirmClearCart() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'Clear Cart?',
          style: TextStyle(
            color: CartzyColors.navy,
            fontWeight: FontWeight.bold,
          ),
        ),
        content: const Text(
          'Are you sure you want to remove all items from your cart?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel', style: TextStyle(color: CartzyColors.gray)),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              CartService.instance.clearAll();
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Cart cleared.'),
                  backgroundColor: CartzyColors.navy,
                  duration: Duration(seconds: 2),
                ),
              );
            },
            child: const Text(
              'Clear All',
              style: TextStyle(
                color: CartzyColors.coral,
                fontWeight: FontWeight.bold,
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
        const SnackBar(
          content: Text('Please select at least one item to checkout.'),
          backgroundColor: CartzyColors.navy,
        ),
      );
      return;
    }

    widget.onCheckout();
  }

  Widget _buildProductImage(CartItem item) {
    final effectiveImage = item.image.isNotEmpty
        ? item.image
        : CartzyProductImages.getImage(item.name, item.category);

    return Container(
      width: 82,
      height: 82,
      decoration: BoxDecoration(
        color: CartzyColors.background,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: CartzyColors.border,
        ),
      ),
      child: CartzyProductImage(
        imageUrl: effectiveImage,
        width: 82,
        height: 82,
        fit: BoxFit.cover,
        borderRadius: BorderRadius.circular(12),
        fallbackIcon: Icons.shopping_bag_outlined,
      ),
    );
  }

  Widget _buildCartItem(CartItem item, int index) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: CartzyColors.border,
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Checkbox(
            value: item.selected,
            activeColor: CartzyColors.coral,
            onChanged: (value) {
              CartService.instance.toggleItemSelection(index, value);
            },
          ),
          _buildProductImage(item),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.name,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
                if (item.category.isNotEmpty) ...[
                  const SizedBox(height: 3),
                  Text(
                    item.category,
                    style: const TextStyle(
                      fontSize: 11,
                      color: CartzyColors.gray,
                    ),
                  ),
                ],
                const SizedBox(height: 6),
                Row(
                  children: [
                    Text(
                      '₱${item.price.toStringAsFixed(2)}',
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: CartzyColors.coral,
                      ),
                    ),
                    if (item.originalPrice != null && item.originalPrice!.isNotEmpty) ...[
                      const SizedBox(width: 8),
                      Text(
                        item.originalPrice!,
                        style: const TextStyle(
                          fontSize: 11,
                          decoration: TextDecoration.lineThrough,
                          color: CartzyColors.gray,
                        ),
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Container(
                      height: 34,
                      decoration: BoxDecoration(
                        border: Border.all(
                          color: CartzyColors.border,
                        ),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          IconButton(
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(
                              minWidth: 32,
                              minHeight: 32,
                            ),
                            icon: const Icon(
                              Icons.remove,
                              size: 16,
                            ),
                            color: CartzyColors.navy,
                            onPressed: () => _decreaseQuantity(index),
                          ),
                          Container(
                            constraints: const BoxConstraints(minWidth: 28),
                            alignment: Alignment.center,
                            child: Text(
                              '${item.quantity}',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: CartzyColors.navy,
                              ),
                            ),
                          ),
                          IconButton(
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(
                              minWidth: 32,
                              minHeight: 32,
                            ),
                            icon: const Icon(
                              Icons.add,
                              size: 16,
                            ),
                            color: CartzyColors.navy,
                            onPressed: () => _increaseQuantity(index),
                          ),
                        ],
                      ),
                    ),
                    const Spacer(),
                    IconButton(
                      onPressed: () => _removeItem(index),
                      icon: const Icon(
                        Icons.delete_outline,
                      ),
                      color: CartzyColors.coral,
                      tooltip: 'Remove',
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyCart() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(30),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 110,
              height: 110,
              decoration: BoxDecoration(
                color: CartzyColors.surface,
                shape: BoxShape.circle,
                border: Border.all(
                  color: CartzyColors.border,
                ),
              ),
              child: const Icon(
                Icons.shopping_cart_outlined,
                size: 55,
                color: CartzyColors.coral,
              ),
            ),
            const SizedBox(height: 20),
            const Text(
              'Your Cart is Empty',
              style: TextStyle(
                fontSize: 21,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Add products to your cart and they will appear here.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 14,
                color: CartzyColors.gray,
              ),
            ),
            const SizedBox(height: 24),
            _buildNeumorphicButton(
              text: 'CONTINUE SHOPPING',
              onPressed: widget.onBack,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNeumorphicButton({
    required String text,
    required VoidCallback onPressed,
  }) {
    return Container(
      width: double.infinity,
      height: 52,
      decoration: BoxDecoration(
        color: CartzyColors.navy,
        borderRadius: BorderRadius.circular(12),
        boxShadow: const [
          BoxShadow(
            color: Colors.black26,
            offset: Offset(4, 5),
            blurRadius: 6,
          ),
        ],
      ),
      child: ElevatedButton(
        onPressed: onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: CartzyColors.navy,
          foregroundColor: Colors.white,
          elevation: 0,
          shadowColor: Colors.transparent,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        child: Text(
          text,
          style: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        leading: IconButton(
          onPressed: widget.onBack,
          icon: const Icon(
            Icons.arrow_back,
            color: CartzyColors.navy,
          ),
        ),
        title: const Text(
          'My Cart',
          style: TextStyle(
            color: CartzyColors.navy,
            fontSize: 19,
            fontWeight: FontWeight.bold,
          ),
        ),
        centerTitle: true,
        actions: [
          if (_cartItems.isNotEmpty)
            IconButton(
              icon: const Icon(
                Icons.delete_sweep_outlined,
                color: CartzyColors.gray,
              ),
              tooltip: 'Clear Cart',
              onPressed: _confirmClearCart,
            ),
        ],
      ),
      body: _cartItems.isEmpty
          ? _buildEmptyCart()
          : Column(
              children: [
                Container(
                  width: double.infinity,
                  color: CartzyColors.surface,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 8,
                  ),
                  child: Row(
                    children: [
                      Checkbox(
                        value: _allSelected,
                        activeColor: CartzyColors.coral,
                        onChanged: _toggleSelectAll,
                      ),
                      const Text(
                        'Select All',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: CartzyColors.navy,
                        ),
                      ),
                      const Spacer(),
                      Text(
                        '${_cartItems.length} item${_cartItems.length == 1 ? '' : 's'}',
                        style: const TextStyle(
                          fontSize: 13,
                          color: CartzyColors.gray,
                        ),
                      ),
                    ],
                  ),
                ),
                const Divider(
                  height: 1,
                  color: CartzyColors.border,
                ),
                Expanded(
                  child: ListView.builder(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 16),
                    itemCount: _cartItems.length,
                    itemBuilder: (context, index) {
                      return _buildCartItem(_cartItems[index], index);
                    },
                  ),
                ),
                Container(
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
                  decoration: BoxDecoration(
                    color: CartzyColors.surface,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.08),
                        offset: const Offset(0, -3),
                        blurRadius: 10,
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Selected Items',
                                style: TextStyle(
                                  fontSize: 13,
                                  color: CartzyColors.gray,
                                ),
                              ),
                              Text(
                                '${CartService.instance.selectedItemCount} items selected',
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: CartzyColors.gray,
                                ),
                              ),
                            ],
                          ),
                          Text(
                            '₱${_selectedTotal.toStringAsFixed(2)}',
                            style: const TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: CartzyColors.navy,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      _buildNeumorphicButton(
                        text: 'CHECKOUT',
                        onPressed: _checkout,
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}

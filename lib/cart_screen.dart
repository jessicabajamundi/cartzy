import 'package:flutter/material.dart';
import 'cartzy_colors.dart';

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
// CART ITEM
// ============================================================

class CartItem {
  final String name;
  final double price;
  final String image;
  int quantity;
  bool selected;

  CartItem({
    required this.name,
    required this.price,
    required this.image,
    this.quantity = 1,
    this.selected = true,
  });
}

// ============================================================
// CART SCREEN STATE
// ============================================================

class _CartScreenState extends State<CartScreen> {
  final List<CartItem> _cartItems = [
    CartItem(
      name: 'Wireless Earbuds',
      price: 29.99,
      image: 'assets/earbuds.png',
      quantity: 1,
    ),
    CartItem(
      name: 'Running Shoes',
      price: 49.99,
      image: 'assets/shoes.png',
      quantity: 1,
    ),
    CartItem(
      name: 'Smart Watch',
      price: 59.99,
      image: 'assets/watch.png',
      quantity: 2,
    ),
  ];

  // ==========================================================
  // TOTAL
  // ==========================================================

  double get _selectedTotal {
    double total = 0;

    for (final item in _cartItems) {
      if (item.selected) {
        total += item.price * item.quantity;
      }
    }

    return total;
  }

  // ==========================================================
  // SELECT ALL
  // ==========================================================

  bool get _allSelected {
    if (_cartItems.isEmpty) {
      return false;
    }

    return _cartItems.every(
      (item) => item.selected,
    );
  }

  void _toggleSelectAll(bool? value) {
    setState(() {
      for (final item in _cartItems) {
        item.selected = value ?? false;
      }
    });
  }

  // ==========================================================
  // CHANGE QUANTITY
  // ==========================================================

  void _increaseQuantity(int index) {
    setState(() {
      _cartItems[index].quantity++;
    });
  }

  void _decreaseQuantity(int index) {
    setState(() {
      if (_cartItems[index].quantity > 1) {
        _cartItems[index].quantity--;
      }
    });
  }

  // ==========================================================
  // REMOVE ITEM
  // ==========================================================

  void _removeItem(int index) {
    final removedItem = _cartItems[index].name;

    setState(() {
      _cartItems.removeAt(index);
    });

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          '$removedItem removed from cart.',
        ),
      ),
    );
  }

  // ==========================================================
  // CHECKOUT
  // ==========================================================

  void _checkout() {
    final selectedItems =
        _cartItems.where(
      (item) => item.selected,
    ).toList();

    // No selected items
    if (selectedItems.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Please select at least one item.',
          ),
        ),
      );

      return;
    }

    // Go to checkout page
    widget.onCheckout();
  }

  // ==========================================================
  // PRODUCT IMAGE
  // ==========================================================

  Widget _buildProductImage(
    CartItem item,
  ) {
    return Container(
      width: 82,
      height: 82,
      decoration: BoxDecoration(
        color: CartzyColors.background,
        borderRadius:
            BorderRadius.circular(12),
        border: Border.all(
          color: CartzyColors.border,
        ),
      ),
      child: ClipRRect(
        borderRadius:
            BorderRadius.circular(12),
        child: Image.asset(
          item.image,
          fit: BoxFit.cover,
          errorBuilder:
              (context, error, stackTrace) {
            return const Icon(
              Icons.shopping_bag_outlined,
              color: CartzyColors.coral,
              size: 38,
            );
          },
        ),
      ),
    );
  }

  // ==========================================================
  // CART ITEM CARD
  // ==========================================================

  Widget _buildCartItem(
    CartItem item,
    int index,
  ) {
    return Container(
      margin:
          const EdgeInsets.only(bottom: 12),
      padding:
          const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        borderRadius:
            BorderRadius.circular(14),
        border: Border.all(
          color: CartzyColors.border,
        ),
      ),
      child: Row(
        crossAxisAlignment:
            CrossAxisAlignment.center,
        children: [

          // ==================================================
          // CHECKBOX
          // ==================================================

          Checkbox(
            value: item.selected,
            activeColor:
                CartzyColors.coral,
            onChanged: (value) {
              setState(() {
                item.selected =
                    value ?? false;
              });
            },
          ),

          // ==================================================
          // PRODUCT IMAGE
          // ==================================================

          _buildProductImage(item),

          const SizedBox(width: 12),

          // ==================================================
          // PRODUCT INFORMATION
          // ==================================================

          Expanded(
            child: Column(
              crossAxisAlignment:
                  CrossAxisAlignment.start,
              children: [

                Text(
                  item.name,
                  maxLines: 2,
                  overflow:
                      TextOverflow.ellipsis,
                  style:
                      const TextStyle(
                    fontSize: 14,
                    fontWeight:
                        FontWeight.bold,
                    color:
                        CartzyColors.navy,
                  ),
                ),

                const SizedBox(height: 6),

                Text(
                  '\$${item.price.toStringAsFixed(2)}',
                  style:
                      const TextStyle(
                    fontSize: 15,
                    fontWeight:
                        FontWeight.bold,
                    color:
                        CartzyColors.coral,
                  ),
                ),

                const SizedBox(height: 10),

                // ==================================================
                // QUANTITY + REMOVE
                // ==================================================

                Row(
                  children: [

                    // QUANTITY CONTROL
                    Container(
                      height: 34,
                      decoration:
                          BoxDecoration(
                        border:
                            Border.all(
                          color:
                              CartzyColors
                                  .border,
                        ),
                        borderRadius:
                            BorderRadius
                                .circular(
                          8,
                        ),
                      ),
                      child: Row(
                        children: [

                          // MINUS
                          IconButton(
                            padding:
                                EdgeInsets.zero,
                            constraints:
                                const BoxConstraints(
                              minWidth: 32,
                              minHeight: 32,
                            ),
                            icon:
                                const Icon(
                              Icons.remove,
                              size: 16,
                            ),
                            color:
                                CartzyColors
                                    .navy,
                            onPressed: () {
                              _decreaseQuantity(
                                index,
                              );
                            },
                          ),

                          Text(
                            '${item.quantity}',
                            style:
                                const TextStyle(
                              fontSize: 13,
                              fontWeight:
                                  FontWeight.bold,
                              color:
                                  CartzyColors
                                      .navy,
                            ),
                          ),

                          // PLUS
                          IconButton(
                            padding:
                                EdgeInsets.zero,
                            constraints:
                                const BoxConstraints(
                              minWidth: 32,
                              minHeight: 32,
                            ),
                            icon:
                                const Icon(
                              Icons.add,
                              size: 16,
                            ),
                            color:
                                CartzyColors
                                    .navy,
                            onPressed: () {
                              _increaseQuantity(
                                index,
                              );
                            },
                          ),
                        ],
                      ),
                    ),

                    const Spacer(),

                    // DELETE
                    IconButton(
                      onPressed: () {
                        _removeItem(index);
                      },
                      icon:
                          const Icon(
                        Icons
                            .delete_outline,
                      ),
                      color:
                          CartzyColors.coral,
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

  // ==========================================================
  // EMPTY CART
  // ==========================================================

  Widget _buildEmptyCart() {
    return Center(
      child: Padding(
        padding:
            const EdgeInsets.all(30),
        child: Column(
          mainAxisAlignment:
              MainAxisAlignment.center,
          children: [

            Container(
              width: 110,
              height: 110,
              decoration:
                  BoxDecoration(
                color:
                    CartzyColors.surface,
                shape: BoxShape.circle,
                border: Border.all(
                  color:
                      CartzyColors.border,
                ),
              ),
              child: const Icon(
                Icons
                    .shopping_cart_outlined,
                size: 55,
                color:
                    CartzyColors.coral,
              ),
            ),

            const SizedBox(height: 20),

            const Text(
              'Your Cart is Empty',
              style:
                  TextStyle(
                fontSize: 21,
                fontWeight:
                    FontWeight.bold,
                color:
                    CartzyColors.navy,
              ),
            ),

            const SizedBox(height: 8),

            const Text(
              'Add products to your cart and they will appear here.',
              textAlign:
                  TextAlign.center,
              style:
                  TextStyle(
                fontSize: 14,
                color:
                    CartzyColors.gray,
              ),
            ),

            const SizedBox(height: 24),

            _buildNeumorphicButton(
              text:
                  'CONTINUE SHOPPING',
              onPressed:
                  widget.onBack,
            ),
          ],
        ),
      ),
    );
  }

  // ==========================================================
  // NEUMORPHIC BUTTON
  // ==========================================================

  Widget _buildNeumorphicButton({
    required String text,
    required VoidCallback onPressed,
  }) {
    return Container(
      width: double.infinity,
      height: 52,
      decoration: BoxDecoration(
        color: CartzyColors.navy,
        borderRadius:
            BorderRadius.circular(12),
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
        style:
            ElevatedButton.styleFrom(
          backgroundColor:
              CartzyColors.navy,
          foregroundColor:
              Colors.white,
          elevation: 0,
          shadowColor:
              Colors.transparent,
          shape:
              RoundedRectangleBorder(
            borderRadius:
                BorderRadius.circular(12),
          ),
        ),
        child: Text(
          text,
          style:
              const TextStyle(
            fontSize: 15,
            fontWeight:
                FontWeight.bold,
          ),
        ),
      ),
    );
  }

  // ==========================================================
  // BUILD
  // ==========================================================

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor:
          CartzyColors.background,

      // ========================================================
      // APP BAR
      // ========================================================

      appBar: AppBar(
        backgroundColor:
            CartzyColors.surface,
        elevation: 0,

        leading: IconButton(
          onPressed: widget.onBack,
          icon: const Icon(
            Icons.arrow_back,
            color:
                CartzyColors.navy,
          ),
        ),

        title: const Text(
          'My Cart',
          style: TextStyle(
            color:
                CartzyColors.navy,
            fontSize: 19,
            fontWeight:
                FontWeight.bold,
          ),
        ),

        centerTitle: true,
      ),

      // ========================================================
      // BODY
      // ========================================================

      body: _cartItems.isEmpty
          ? _buildEmptyCart()
          : Column(
              children: [

                // ==================================================
                // SELECT ALL
                // ==================================================

                Container(
                  width:
                      double.infinity,
                  color:
                      CartzyColors.surface,
                  padding:
                      const EdgeInsets
                          .symmetric(
                    horizontal: 12,
                    vertical: 8,
                  ),
                  child: Row(
                    children: [

                      Checkbox(
                        value:
                            _allSelected,
                        activeColor:
                            CartzyColors
                                .coral,
                        onChanged:
                            _toggleSelectAll,
                      ),

                      const Text(
                        'Select All',
                        style:
                            TextStyle(
                          fontSize: 14,
                          fontWeight:
                              FontWeight.w600,
                          color:
                              CartzyColors
                                  .navy,
                        ),
                      ),

                      const Spacer(),

                      Text(
                        '${_cartItems.length} item${_cartItems.length == 1 ? '' : 's'}',
                        style:
                            const TextStyle(
                          fontSize: 13,
                          color:
                              CartzyColors
                                  .gray,
                        ),
                      ),
                    ],
                  ),
                ),

                const Divider(
                  height: 1,
                  color:
                      CartzyColors.border,
                ),

                // ==================================================
                // CART ITEMS
                // ==================================================

                Expanded(
                  child:
                      ListView.builder(
                    padding:
                        const EdgeInsets
                            .fromLTRB(
                      16,
                      16,
                      16,
                      16,
                    ),
                    itemCount:
                        _cartItems.length,
                    itemBuilder:
                        (context, index) {
                      return _buildCartItem(
                        _cartItems[index],
                        index,
                      );
                    },
                  ),
                ),

                // ==================================================
                // BOTTOM SUMMARY
                // ==================================================

                Container(
                  padding:
                      const EdgeInsets
                          .fromLTRB(
                    20,
                    16,
                    20,
                    20,
                  ),
                  decoration:
                      BoxDecoration(
                    color:
                        CartzyColors
                            .surface,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black
                            .withValues(
                          alpha: 0.08,
                        ),
                        offset:
                            const Offset(
                          0,
                          -3,
                        ),
                        blurRadius: 10,
                      ),
                    ],
                  ),
                  child: Column(
                    children: [

                      // TOTAL
                      Row(
                        mainAxisAlignment:
                            MainAxisAlignment
                                .spaceBetween,
                        children: [

                          const Text(
                            'Selected Items',
                            style:
                                TextStyle(
                              fontSize: 13,
                              color:
                                  CartzyColors
                                      .gray,
                            ),
                          ),

                          Text(
                            '\$${_selectedTotal.toStringAsFixed(2)}',
                            style:
                                const TextStyle(
                              fontSize: 20,
                              fontWeight:
                                  FontWeight
                                      .bold,
                              color:
                                  CartzyColors
                                      .navy,
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(
                        height: 12,
                      ),

                      // ==================================================
                      // CHECKOUT BUTTON
                      // ==================================================

                      _buildNeumorphicButton(
                        text: 'CHECKOUT',
                        onPressed:
                            _checkout,
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}
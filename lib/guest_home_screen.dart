import 'package:flutter/material.dart';

import 'cartzy_colors.dart';
import 'neumorphic_rounded_button.dart';

class GuestProduct {
  final String name;
  final String price;
  final String category;

  const GuestProduct({
    required this.name,
    required this.price,
    required this.category,
  });
}

class GuestHomeScreen extends StatefulWidget {
  final VoidCallback onLogin;
  final VoidCallback onSignUp;

  const GuestHomeScreen({
    super.key,
    required this.onLogin,
    required this.onSignUp,
  });

  @override
  State<GuestHomeScreen> createState() => _GuestHomeScreenState();
}

class _GuestHomeScreenState extends State<GuestHomeScreen> {
  final TextEditingController _searchController = TextEditingController();

  final List<GuestProduct> _products = const [
    GuestProduct(
      name: 'Everyday Sneakers',
      price: '₱1,299',
      category: 'Fashion',
    ),
    GuestProduct(
      name: 'Wireless Earbuds',
      price: '₱899',
      category: 'Electronics',
    ),
    GuestProduct(
      name: 'Canvas Tote Bag',
      price: '₱499',
      category: 'Fashion',
    ),
    GuestProduct(
      name: 'Skincare Set',
      price: '₱799',
      category: 'Beauty',
    ),
  ];

  final List<String> _categories = const [
    'All',
    'Fashion',
    'Beauty',
    'Electronics',
    'Home',
  ];

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  // ============================================================
  // LOGIN REQUIRED DIALOG
  // ============================================================

  void _showLoginRequired() {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text(
            'Login Required',
            style: TextStyle(
              color: CartzyColors.navy,
              fontWeight: FontWeight.bold,
            ),
          ),
          content: const Text(
            "You're currently browsing as a guest. "
            "Please log in or create an account to add "
            "products to your cart.",
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(context);
                widget.onSignUp();
              },
              child: const Text(
                'Create Account',
                style: TextStyle(color: CartzyColors.coral),
              ),
            ),
            NeumorphicRoundedButton(
              text: 'Login',
              borderRadius: 10,
              width: 110,
              height: 42,
              textColor: Colors.white,
              onTap: () {
                Navigator.pop(context);
                widget.onLogin();
              },
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,

      // ==========================================================
      // TOP BAR
      // ==========================================================
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Image.asset(
              'assets/cartzy_splash.png',
              height: 36,
              fit: BoxFit.contain,
            ),
            const SizedBox(width: 8),
            const Text(
              'Shop your way',
              style: TextStyle(
                color: CartzyColors.coral,
                fontSize: 10,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            onPressed: _showLoginRequired,
            icon: const Icon(
              Icons.shopping_cart_outlined,
              color: CartzyColors.navy,
            ),
          ),
          IconButton(
            onPressed: widget.onLogin,
            icon: const Icon(
              Icons.account_circle_outlined,
              color: CartzyColors.navy,
            ),
          ),
          const SizedBox(width: 8),
        ],
      ),

      // ==========================================================
      // BODY
      // ==========================================================
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              "Discover something you'll love.",
              style: TextStyle(
                fontSize: 25,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),
            const SizedBox(height: 6),
            const Text(
              'Browse our products and find your next favorite.',
              style: TextStyle(fontSize: 13, color: CartzyColors.gray),
            ),
            const SizedBox(height: 20),

            // ======================================================
            // SEARCH
            // ======================================================
            TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Search products',
                prefixIcon: const Icon(Icons.search, color: CartzyColors.coral),
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: BorderSide.none,
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: CartzyColors.border),
                ),
              ),
            ),
            const SizedBox(height: 25),

            // ======================================================
            // CATEGORIES
            // ======================================================
            const Text(
              'Categories',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),
            const SizedBox(height: 12),

            SizedBox(
              height: 42,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: _categories.length,
                separatorBuilder: (_, __) => const SizedBox(width: 9),
                itemBuilder: (context, index) {
                  final category = _categories[index];
                  return Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 18,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      color: category == 'All' ? CartzyColors.navy : Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: CartzyColors.border),
                    ),
                    child: Text(
                      category,
                      style: TextStyle(
                        color: category == 'All' ? Colors.white : CartzyColors.navy,
                        fontWeight: FontWeight.w600,
                        fontSize: 13,
                      ),
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 30),

            // ======================================================
            // FEATURED PRODUCTS
            // ======================================================
            const Text(
              'Featured Products',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),
            const SizedBox(height: 15),

            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _products.length,
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                crossAxisSpacing: 14,
                mainAxisSpacing: 14,
                childAspectRatio: 0.75,
              ),
              itemBuilder: (context, index) {
                final product = _products[index];
                return _ProductCard(
                  product: product,
                  onAddToCart: _showLoginRequired,
                );
              },
            ),
            const SizedBox(height: 30),

            // ======================================================
            // GUEST INFORMATION
            // ======================================================
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: CartzyColors.border),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Shopping as a guest?',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.navy,
                    ),
                  ),
                  const SizedBox(height: 5),
                  const Text(
                    'Browse freely. Log in or create an account '
                    'when you’re ready to add items to your cart.',
                    style: TextStyle(fontSize: 12, color: CartzyColors.gray),
                  ),
                  const SizedBox(height: 16),

                  Row(
                    children: [
                      Expanded(
                        child: NeumorphicRoundedButton(
                          text: 'Login',
                          borderRadius: 10,
                          height: 45,
                          width: double.infinity,
                          textColor: Colors.white,
                          onTap: widget.onLogin,
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: NeumorphicRoundedButton(
                          text: 'Create Account',
                          borderRadius: 10,
                          height: 45,
                          width: double.infinity,
                          textColor: Colors.white,
                          onTap: widget.onSignUp,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 30),
          ],
        ),
      ),
    );
  }
}

// ================================================================
// PRODUCT CARD
// ================================================================

class _ProductCard extends StatelessWidget {
  final GuestProduct product;
  final VoidCallback onAddToCart;

  const _ProductCard({
    required this.product,
    required this.onAddToCart,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: CartzyColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Container(
              width: double.infinity,
              decoration: BoxDecoration(
                color: CartzyColors.background,
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.shopping_bag_outlined,
                size: 55,
                color: CartzyColors.coral,
              ),
            ),
          ),
          const SizedBox(height: 10),
          Text(
            product.category,
            style: const TextStyle(
              fontSize: 11,
              color: CartzyColors.coral,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 3),
          Text(
            product.name,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: CartzyColors.navy,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            product.price,
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.bold,
              color: CartzyColors.navy,
            ),
          ),
          const SizedBox(height: 8),
          SizedBox(
            width: double.infinity,
            child: NeumorphicRoundedButton(
              text: 'Add to Cart',
              borderRadius: 9,
              height: 38,
              width: double.infinity,
              textColor: Colors.white,
              onTap: onAddToCart,
            ),
          ),
        ],
      ),
    );
  }
}
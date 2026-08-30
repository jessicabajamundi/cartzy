import 'package:flutter/material.dart';

import 'cartzy_colors.dart';
import 'neumorphic_rounded_button.dart';

// ==========================================
// PRODUCT DATA
// ==========================================

class Product {
  final String name;
  final String price;

  const Product({required this.name, required this.price});
}

const List<String> _categories = [
  'All',
  'Fashion',
  'Electronics',
  'Home',
  'Beauty',
  'Sports',
];

const List<Product> _sampleProducts = [
  Product(name: 'Wireless Earbuds', price: '\$29.99'),
  Product(name: 'Running Shoes', price: '\$49.99'),
  Product(name: 'Smart Watch', price: '\$59.99'),
  Product(name: 'Backpack', price: '\$34.99'),
  Product(name: 'Sunglasses', price: '\$19.99'),
  Product(name: 'Desk Lamp', price: '\$24.99'),
];

// ==========================================
// DASHBOARD SCREEN
// ==========================================

class DashboardScreen extends StatefulWidget {
  final VoidCallback? onCartClick;
  final VoidCallback? onProfileClick;
  final ValueChanged<Product>? onProductClick;

  const DashboardScreen({
    super.key,
    this.onCartClick,
    this.onProfileClick,
    this.onProductClick,
  });

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _selectedCategory = 'All';

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Column(
          children: [
            // --------------------------------
            // TOP BAR
            // --------------------------------
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Image.asset(
                        'assets/cartzy_splash.png',
                        height: 32,
                        fit: BoxFit.contain,
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        "Let's go shopping",
                        style: TextStyle(
                          fontSize: 13,
                          color: CartzyColors.gray,
                        ),
                      ),
                    ],
                  ),
                  Row(
                    children: [
                      IconButton(
                        icon: const Icon(Icons.shopping_cart, color: CartzyColors.navy),
                        onPressed: widget.onCartClick,
                      ),
                      IconButton(
                        icon: const Icon(Icons.person, color: CartzyColors.navy),
                        onPressed: widget.onProfileClick,
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // --------------------------------
            // SEARCH BAR — matches Login field style
            // --------------------------------
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Search products',
                  hintStyle: const TextStyle(fontSize: 13),
                  prefixIcon: const Icon(Icons.search, color: CartzyColors.coral),
                  filled: true,
                  fillColor: CartzyColors.background,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: BorderSide.none,
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: BorderSide.none,
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: const BorderSide(color: CartzyColors.coral, width: 2),
                  ),
                ),
              ),
            ),

            const SizedBox(height: 20),

            // --------------------------------
            // CATEGORIES TITLE
            // --------------------------------
            const Padding(
              padding: EdgeInsets.symmetric(horizontal: 20),
              child: Align(
                alignment: Alignment.centerLeft,
                child: Text(
                  'Categories',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
              ),
            ),

            const SizedBox(height: 12),

            // --------------------------------
            // CATEGORIES
            // --------------------------------
            SizedBox(
              height: 42,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 20),
                itemCount: _categories.length,
                separatorBuilder: (_, __) => const SizedBox(width: 10),
                itemBuilder: (context, index) {
                  final category = _categories[index];
                  return _CategoryItem(
                    name: category,
                    selected: _selectedCategory == category,
                    onClick: () {
                      setState(() => _selectedCategory = category);
                    },
                  );
                },
              ),
            ),

            const SizedBox(height: 22),

            // --------------------------------
            // PRODUCTS TITLE
            // --------------------------------
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  const Text(
                    'Popular Products',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.navy,
                    ),
                  ),
                  const Spacer(),
                  const Text(
                    'See All',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: CartzyColors.coral,
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 12),

            // --------------------------------
            // PRODUCT GRID
            // --------------------------------
            Expanded(
              child: GridView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  crossAxisSpacing: 14,
                  mainAxisSpacing: 14,
                  childAspectRatio: 0.62,
                ),
                itemCount: _sampleProducts.length,
                itemBuilder: (context, index) {
                  final product = _sampleProducts[index];
                  return _ProductCard(
                    product: product,
                    onClick: () => widget.onProductClick?.call(product),
                  );
                },
              ),
            ),

            // --------------------------------
            // BOTTOM NAVIGATION
            // --------------------------------
            Container(
              color: CartzyColors.surface,
              padding: const EdgeInsets.symmetric(horizontal: 30, vertical: 10),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _BottomNavigationItem(
                    icon: Icons.home,
                    label: 'Home',
                    selected: true,
                    onClick: () {},
                  ),
                  _BottomNavigationItem(
                    icon: Icons.search,
                    label: 'Search',
                    selected: false,
                    onClick: () {},
                  ),
                  _BottomNavigationItem(
                    icon: Icons.shopping_cart,
                    label: 'Cart',
                    selected: false,
                    onClick: widget.onCartClick ?? () {},
                  ),
                  _BottomNavigationItem(
                    icon: Icons.person,
                    label: 'Profile',
                    selected: false,
                    onClick: widget.onProfileClick ?? () {},
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// --------------------------------
// CATEGORY ITEM
// --------------------------------

class _CategoryItem extends StatelessWidget {
  final String name;
  final bool selected;
  final VoidCallback onClick;

  const _CategoryItem({
    required this.name,
    required this.selected,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onClick,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: selected ? CartzyColors.navy : CartzyColors.surface,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Text(
          name,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: selected ? Colors.white : CartzyColors.navy,
          ),
        ),
      ),
    );
  }
}

// --------------------------------
// PRODUCT CARD — soft shadow instead of border
// --------------------------------

class _ProductCard extends StatelessWidget {
  final Product product;
  final VoidCallback onClick;

  const _ProductCard({required this.product, required this.onClick});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onClick,
      child: Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: CartzyColors.surface,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Container(
                width: double.infinity,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: CartzyColors.background,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.shopping_bag_outlined,
                  size: 40,
                  color: CartzyColors.coral,
                ),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              product.name,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: CartzyColors.navy,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              product.price,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: CartzyColors.coral,
              ),
            ),
            const SizedBox(height: 8),
            SizedBox(
              width: double.infinity,
              child: NeumorphicRoundedButton(
                text: 'Add to Cart',
                borderRadius: 10,
                height: 34,
                width: double.infinity,
                textColor: Colors.white,
                onTap: onClick,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// --------------------------------
// BOTTOM NAVIGATION ITEM
// --------------------------------

class _BottomNavigationItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final VoidCallback onClick;

  const _BottomNavigationItem({
    required this.icon,
    required this.label,
    required this.selected,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    final color = selected ? CartzyColors.coral : CartzyColors.gray;

    return GestureDetector(
      onTap: onClick,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 3),
          Text(
            label,
            style: TextStyle(fontSize: 11, color: color),
          ),
        ],
      ),
    );
  }
}
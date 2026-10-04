import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/home_sections.dart';
import 'package:cartzy/products/product_list_screen.dart';
import 'package:cartzy/products/product_details_screen.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/utils/product_images.dart';

class DashboardScreen extends StatefulWidget {
  final VoidCallback? onCartClick;
  final VoidCallback? onProfileClick;

  const DashboardScreen({
    super.key,
    this.onCartClick,
    this.onProfileClick,
  });

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _selectedCategory = categoryIcons.first.label;

  void _openCart(BuildContext context) {
    if (widget.onCartClick != null) {
      widget.onCartClick!();
    } else {
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => CartScreen(
            onBack: () => Navigator.of(context).pop(),
            onCheckout: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => CheckoutScreen(
                    onBack: () => Navigator.of(context).pop(),
                    onOrderPlaced: () {
                      Navigator.of(context).popUntil((route) => route.isFirst);
                    },
                  ),
                ),
              );
            },
          ),
        ),
      );
    }
  }

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
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Image.asset('assets/cartzy_splash.png', height: 30, fit: BoxFit.contain),
                  Row(
                    children: [
                      ListenableBuilder(
                        listenable: CartService.instance,
                        builder: (context, _) {
                          final count = CartService.instance.totalItemCount;
                          return Stack(
                            alignment: Alignment.center,
                            children: [
                              IconButton(
                                icon: const Icon(Icons.shopping_cart_outlined, color: CartzyColors.navy),
                                onPressed: () => _openCart(context),
                              ),
                              if (count > 0)
                                Positioned(
                                  right: 6,
                                  top: 6,
                                  child: Container(
                                    padding: const EdgeInsets.all(4),
                                    decoration: const BoxDecoration(
                                      color: CartzyColors.coral,
                                      shape: BoxShape.circle,
                                    ),
                                    constraints: const BoxConstraints(
                                      minWidth: 18,
                                      minHeight: 18,
                                    ),
                                    child: Text(
                                      count > 99 ? '99+' : '$count',
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 10,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                ),
                            ],
                          );
                        },
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
            Expanded(
              child: ListView(
                padding: const EdgeInsets.only(bottom: 20),
                children: [
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    child: TextField(
                      controller: _searchController,
                      decoration: InputDecoration(
                        hintText: 'Search for products, brands and more...',
                        hintStyle: const TextStyle(fontSize: 13),
                        prefixIcon: const Icon(Icons.search, color: CartzyColors.coral),
                        filled: true,
                        fillColor: CartzyColors.surface,
                        contentPadding: const EdgeInsets.symmetric(vertical: 14),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(14),
                          borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(14),
                          borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(14),
                          borderSide: const BorderSide(color: CartzyColors.coral, width: 1.8),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 18),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    child: HeroBanner(onShopNow: () {}),
                  ),
                  const SizedBox(height: 14),
                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 20),
                    child: Column(
                      children: [
                        PromoCard(
                          tag: 'FLEXIBLE PAYMENTS',
                          title: '0% Interest Installments',
                          description: 'Split payments into 3, 6, or 12 convenient monthly terms',
                          linkText: 'Learn More →',
                        ),
                        SizedBox(height: 12),
                        PromoCard(
                          tag: 'EXPRESS DELIVERY',
                          title: 'Next Day Nationwide',
                          description: 'Guaranteed prompt dispatch on verified partner items',
                          linkText: 'Explore Express →',
                          dark: true,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),
                  const SectionTitle(title: 'Categories'),
                  const SizedBox(height: 12),
                  CategoryIconRow(
                    selectedCategory: _selectedCategory,
                    onSelected: (label) {
                      setState(() => _selectedCategory = label);
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => ProductListScreen(
                            initialCategory: label,
                            title: label,
                          ),
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 26),
                  FlashDealsSection(
                    onViewAll: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => const ProductListScreen(
                            initialCategory: 'Flash Deals',
                            title: 'Flash Deals',
                          ),
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 26),
                  SectionTitle(
                    title: 'Daily Discover',
                    actionText: 'See More',
                    onAction: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => const ProductListScreen(
                            initialCategory: 'All',
                            title: 'Daily Discover',
                          ),
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 4),
                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 20),
                    child: Text(
                      'Curated deals tailored for you',
                      style: TextStyle(fontSize: 12, color: CartzyColors.gray),
                    ),
                  ),
                  const SizedBox(height: 14),
                  GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: 14,
                      mainAxisSpacing: 14,
                      childAspectRatio: 0.68,
                    ),
                    itemCount: discoverProducts.length,
                    itemBuilder: (context, index) {
                      final p = discoverProducts[index];
                      return DiscoverProductCard(
                        product: p,
                        onTap: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => ProductDetailsScreen(
                                name: p.name,
                                category: p.badge,
                                price: p.price,
                                imageUrl: (p.imageUrl != null && p.imageUrl!.isNotEmpty)
                                    ? p.imageUrl
                                    : CartzyProductImages.getImage(p.name, p.badge),
                                onBack: () => Navigator.of(context).pop(),
                                onAddToCart: () {
                                  CartService.instance.addItem(
                                    name: p.name,
                                    price: p.price,
                                    category: p.badge,
                                    image: (p.imageUrl != null && p.imageUrl!.isNotEmpty)
                                        ? p.imageUrl!
                                        : CartzyProductImages.getImage(p.name, p.badge),
                                  );
                                  ScaffoldMessenger.of(context).hideCurrentSnackBar();
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text('${p.name} added to cart!'),
                                      backgroundColor: CartzyColors.navy,
                                      duration: const Duration(seconds: 3),
                                      action: SnackBarAction(
                                        label: 'VIEW CART',
                                        textColor: CartzyColors.coral,
                                        onPressed: () => _openCart(context),
                                      ),
                                    ),
                                  );
                                },
                              ),
                            ),
                          );
                        },
                      );
                    },
                  ),
                ],
              ),
            ),
            Container(
              color: CartzyColors.surface,
              padding: const EdgeInsets.symmetric(horizontal: 30, vertical: 10),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _BottomNavigationItem(icon: Icons.home, label: 'Home', selected: true, onClick: () {}),
                  _BottomNavigationItem(icon: Icons.search, label: 'Search', selected: false, onClick: () {}),
                  ListenableBuilder(
                    listenable: CartService.instance,
                    builder: (context, _) {
                      return _BottomNavigationItem(
                        icon: Icons.shopping_cart_outlined,
                        label: 'Cart',
                        selected: false,
                        badgeCount: CartService.instance.totalItemCount,
                        onClick: () => _openCart(context),
                      );
                    },
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

class _BottomNavigationItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final VoidCallback onClick;
  final int badgeCount;

  const _BottomNavigationItem({
    required this.icon,
    required this.label,
    required this.selected,
    required this.onClick,
    this.badgeCount = 0,
  });

  @override
  Widget build(BuildContext context) {
    final color = selected ? CartzyColors.coral : CartzyColors.gray;
    return GestureDetector(
      onTap: onClick,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Stack(
            clipBehavior: Clip.none,
            children: [
              Icon(icon, color: color, size: 24),
              if (badgeCount > 0)
                Positioned(
                  right: -8,
                  top: -4,
                  child: Container(
                    padding: const EdgeInsets.all(3),
                    decoration: const BoxDecoration(
                      color: CartzyColors.coral,
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(
                      minWidth: 16,
                      minHeight: 16,
                    ),
                    child: Text(
                      badgeCount > 99 ? '99+' : '$badgeCount',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 9,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 3),
          Text(label, style: TextStyle(fontSize: 11, color: color)),
        ],
      ),
    );
  }
}
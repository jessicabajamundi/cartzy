import 'package:flutter/material.dart';

import 'cartzy_colors.dart';
import 'home_sections.dart';

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
                    onSelected: (label) => setState(() => _selectedCategory = label),
                  ),
                  const SizedBox(height: 26),
                  FlashDealsSection(onViewAll: () {}),
                  const SizedBox(height: 26),
                  SectionTitle(title: 'Daily Discover', actionText: 'See More', onAction: () {}),
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
                      return DiscoverProductCard(
                        product: discoverProducts[index],
                        onTap: () {},
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
          Text(label, style: TextStyle(fontSize: 11, color: color)),
        ],
      ),
    );
  }
}
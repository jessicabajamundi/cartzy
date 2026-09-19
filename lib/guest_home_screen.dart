import 'package:flutter/material.dart';

import 'cartzy_colors.dart';
import 'home_sections.dart';

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
  String _selectedCategory = categoryIcons.first.label;

  void _showLoginRequiredDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Login Required', style: TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold)),
        content: const Text('Please log in or create an account to continue.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel', style: TextStyle(color: CartzyColors.gray)),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              widget.onLogin();
            },
            child: const Text('Log In', style: TextStyle(color: CartzyColors.coral, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
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
            // --------------------------------
            // TOP BAR
            // --------------------------------
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Image.asset('assets/cartzy_splash.png', height: 30, fit: BoxFit.contain),
                  Row(
                    children: [
                      TextButton(
                        onPressed: widget.onLogin,
                        child: const Text('Login', style: TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.w600)),
                      ),
                      const SizedBox(width: 4),
                      ElevatedButton(
                        onPressed: widget.onSignUp,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: CartzyColors.navy,
                          foregroundColor: Colors.white,
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        child: const Text('Sign up', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
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
                  // SEARCH BAR
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

                  // HERO BANNER
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    child: HeroBanner(onShopNow: () {}),
                  ),
                  const SizedBox(height: 14),

                  // PROMO CARDS
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

                  // CATEGORY ICONS
                  const SectionTitle(title: 'Categories'),
                  const SizedBox(height: 12),
                  CategoryIconRow(
                    selectedCategory: _selectedCategory,
                    onSelected: (label) => setState(() => _selectedCategory = label),
                  ),
                  const SizedBox(height: 26),

                  // FLASH DEALS
                  FlashDealsSection(onViewAll: () {}),
                  const SizedBox(height: 26),

                  // POPULAR PRODUCTS (guest can browse, tapping prompts login)
                  const SectionTitle(title: 'Popular Products'),
                  const SizedBox(height: 12),
                  GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: 14,
                      mainAxisSpacing: 14,
                      childAspectRatio: 0.72,
                    ),
                    itemCount: flashDeals.length,
                    itemBuilder: (context, index) {
                      final deal = flashDeals[index];
                      return GestureDetector(
                        onTap: _showLoginRequiredDialog,
                        child: Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: CartzyColors.surface,
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 12, offset: const Offset(0, 4)),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Expanded(
                                child: Container(
                                  width: double.infinity,
                                  alignment: Alignment.center,
                                  decoration: BoxDecoration(color: CartzyColors.background, borderRadius: BorderRadius.circular(12)),
                                  child: const Icon(Icons.shopping_bag_outlined, size: 36, color: CartzyColors.coral),
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                deal.name,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: CartzyColors.navy),
                              ),
                              const SizedBox(height: 4),
                              Text(deal.price, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: CartzyColors.coral)),
                            ],
                          ),
                        ),
                      );
                    },
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
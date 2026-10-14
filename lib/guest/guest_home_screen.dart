import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/home_sections.dart';
import 'package:cartzy/utils/product_images.dart';
import 'package:cartzy/products/product_list_screen.dart';
import 'package:cartzy/products/product_details_screen.dart';

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
  String _selectedNavTab = 'Home';
  String _selectedCategory = categoryIcons.first.label;

  final _navTabs = ['Home', 'New', 'Sale', 'Shop All', 'Collection'];
  final GlobalKey _collectionTabKey = GlobalKey();
  bool _isCollectionOpen = false;

  static const List<String> _collectionOptions = [
    'General',
    'Women Clothing',
    'Beachwear',
    'Kids',
    'Curve',
    'Men Clothing',
    'Shoes',
    'Jewelry & Accessories',
    'Underwear & Sleepwear',
    'Baby & Maternity',
    'Bags & Luggage',
    'Home & Living',
    'Beauty & Health',
    'Sports & Outdoors',
    'Home Textiles',
    'Cell Phones & Accessories',
    'Electronics',
    'Tools & Home Improvement',
    'Toys & Games',
    'Pet Supplies',
    'Appliances',
    'Office & School Supplies',
    'Automotive',
  ];

  void _showCollectionDropdown(BuildContext context) {
    Offset targetOffset = const Offset(200, 96);
    if (_collectionTabKey.currentContext != null) {
      final renderBox = _collectionTabKey.currentContext!.findRenderObject() as RenderBox?;
      if (renderBox != null) {
        final pos = renderBox.localToGlobal(Offset.zero);
        final size = renderBox.size;
        targetOffset = Offset(pos.dx, pos.dy + size.height);
      }
    }

    setState(() {
      _isCollectionOpen = true;
      _selectedNavTab = 'Collection';
    });

    final screenWidth = MediaQuery.of(context).size.width;
    final screenHeight = MediaQuery.of(context).size.height;
    final double leftPos = targetOffset.dx.clamp(8.0, screenWidth - 230.0);
    final double topPos = targetOffset.dy.clamp(60.0, screenHeight - 200.0);
    final double maxHeight = (screenHeight - topPos - 24.0).clamp(260.0, 480.0);

    showGeneralDialog(
      context: context,
      barrierDismissible: true,
      barrierLabel: 'Dismiss Collection Menu',
      barrierColor: Colors.black.withOpacity(0.04),
      transitionDuration: const Duration(milliseconds: 160),
      pageBuilder: (ctx, anim1, anim2) {
        return Stack(
          children: [
            Positioned(
              top: topPos,
              left: leftPos,
              child: Material(
                color: Colors.transparent,
                child: Container(
                  width: 220,
                  height: maxHeight,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: const BorderRadius.only(
                      bottomLeft: Radius.circular(8),
                      bottomRight: Radius.circular(8),
                      topRight: Radius.circular(8),
                      topLeft: Radius.circular(2),
                    ),
                    border: Border.all(color: const Color(0xFFDCC8CA), width: 1.2),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.09),
                        blurRadius: 20,
                        spreadRadius: 1,
                        offset: const Offset(0, 8),
                      ),
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: const BorderRadius.only(
                      bottomLeft: Radius.circular(8),
                      bottomRight: Radius.circular(8),
                      topRight: Radius.circular(8),
                    ),
                    child: Theme(
                      data: Theme.of(context).copyWith(
                        scrollbarTheme: ScrollbarThemeData(
                          thumbColor: WidgetStateProperty.all(const Color(0xFFBCAAA4)),
                          thickness: WidgetStateProperty.all(4.0),
                          radius: const Radius.circular(4),
                        ),
                      ),
                      child: Scrollbar(
                        thumbVisibility: true,
                        child: ListView.builder(
                          padding: const EdgeInsets.symmetric(vertical: 6),
                          itemCount: _collectionOptions.length,
                          itemBuilder: (ctx, index) {
                            final category = _collectionOptions[index];
                            return InkWell(
                              hoverColor: const Color(0xFFFAF2F4),
                              splashColor: const Color(0xFFF3E5E7),
                              onTap: () {
                                Navigator.of(ctx).pop();
                                Navigator.of(context).push(
                                  MaterialPageRoute(
                                    builder: (_) => ProductListScreen(
                                      initialCategory: category == 'General' ? 'All' : category,
                                      title: category,
                                    ),
                                  ),
                                );
                              },
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 7.5),
                                child: Text(
                                  category,
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w400,
                                    color: const Color(0xFF2C2829),
                                  ),
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        );
      },
      transitionBuilder: (ctx, anim1, anim2, child) {
        return FadeTransition(
          opacity: anim1,
          child: child,
        );
      },
    ).then((_) {
      if (mounted) {
        setState(() => _isCollectionOpen = false);
      }
    });
  }

  void _showLoginRequiredDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        backgroundColor: CartzyColors.surface,
        title: Text(
          'Login Required',
          style: GoogleFonts.playfairDisplay(color: CartzyColors.navy, fontWeight: FontWeight.w700),
        ),
        content: Text(
          'Please log in or create an account to continue.',
          style: GoogleFonts.dmSans(color: CartzyColors.gray),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text('Cancel', style: GoogleFonts.dmSans(color: CartzyColors.gray)),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              widget.onLogin();
            },
            child: Text('Log In', style: GoogleFonts.dmSans(color: CartzyColors.burgundy, fontWeight: FontWeight.w700)),
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
            // ─────────────────────────────────────────────────
            // HEADER: Logo + Nav Tabs + Search + Login/Signup
            // ─────────────────────────────────────────────────
            Container(
              color: CartzyColors.surface,
              child: Column(
                children: [
                  // Row 1: Logo + Login/Sign Up
                  Padding(
                    padding: const EdgeInsets.fromLTRB(20, 14, 20, 0),
                    child: Row(
                      children: [
                        // Logo
                        Image.asset('assets/cartzy_splash.png', height: 28, fit: BoxFit.contain),
                        const Spacer(),
                        // Login
                        GestureDetector(
                          onTap: widget.onLogin,
                          child: Row(
                            children: [
                              const Icon(Icons.person_outline, size: 16, color: CartzyColors.navy),
                              const SizedBox(width: 4),
                              Text(
                                'Login',
                                style: GoogleFonts.dmSans(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  color: CartzyColors.navy,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 14),
                        // Sign Up pill
                        GestureDetector(
                          onTap: widget.onSignUp,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                            decoration: BoxDecoration(
                              color: CartzyColors.navy,
                              borderRadius: BorderRadius.circular(50),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.add, size: 13, color: Colors.white),
                                const SizedBox(width: 4),
                                Text(
                                  'Sign up',
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                    color: Colors.white,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Row 2: Nav tabs
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 10, 16, 0),
                    child: Row(
                      children: _navTabs.map((tab) {
                        final isActive = tab == _selectedNavTab;
                        final isSale   = tab == 'Sale';
                        final isCollection = tab == 'Collection';

                        Widget tabWidget;
                        if (isCollection) {
                          tabWidget = Container(
                            key: _collectionTabKey,
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: _isCollectionOpen ? Colors.white : Colors.transparent,
                              borderRadius: const BorderRadius.vertical(top: Radius.circular(6)),
                              border: Border(
                                top: BorderSide(
                                  color: _isCollectionOpen ? const Color(0xFF9E404E) : Colors.transparent,
                                  width: 1.2,
                                ),
                                left: BorderSide(
                                  color: _isCollectionOpen ? const Color(0xFF9E404E) : Colors.transparent,
                                  width: 1.2,
                                ),
                                right: BorderSide(
                                  color: _isCollectionOpen ? const Color(0xFF9E404E) : Colors.transparent,
                                  width: 1.2,
                                ),
                                bottom: const BorderSide(color: Colors.transparent, width: 1.2),
                              ),
                            ),
                            child: Text(
                              tab,
                              style: GoogleFonts.dmSans(
                                fontSize: 14,
                                fontWeight: isActive || _isCollectionOpen ? FontWeight.w600 : FontWeight.w400,
                                color: _isCollectionOpen || isActive
                                    ? CartzyColors.burgundy
                                    : CartzyColors.gray,
                              ),
                            ),
                          );
                        } else {
                          tabWidget = Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 3),
                            child: Text(
                              tab,
                              style: GoogleFonts.dmSans(
                                fontSize: 14,
                                fontWeight: isActive || isSale ? FontWeight.w600 : FontWeight.w400,
                                color: isSale
                                    ? CartzyColors.burgundy
                                    : isActive
                                        ? CartzyColors.navy
                                        : CartzyColors.gray,
                              ),
                            ),
                          );
                        }

                        return GestureDetector(
                          onTap: () {
                            if (tab == 'Collection') {
                              _showCollectionDropdown(context);
                            } else if (tab == 'Home') {
                              setState(() => _selectedNavTab = 'Home');
                            } else if (tab == 'New') {
                              setState(() => _selectedNavTab = 'New');
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (_) => const ProductListScreen(
                                    title: 'New Arrivals',
                                    initialCategory: 'All',
                                  ),
                                ),
                              );
                            } else if (tab == 'Sale') {
                              setState(() => _selectedNavTab = 'Sale');
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (_) => const ProductListScreen(
                                    initialCategory: 'Flash Deals',
                                    title: 'Sale & Flash Deals',
                                  ),
                                ),
                              );
                            } else if (tab == 'Shop All') {
                              setState(() => _selectedNavTab = 'Shop All');
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (_) => const ProductListScreen(
                                    initialCategory: 'All',
                                    title: 'Shop All Products',
                                    randomize: true,
                                  ),
                                ),
                              );
                            }
                          },
                          child: Padding(
                            padding: const EdgeInsets.only(right: 12),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                tabWidget,
                                const SizedBox(height: 4),
                                AnimatedContainer(
                                  duration: const Duration(milliseconds: 200),
                                  height: 2,
                                  width: isActive && !_isCollectionOpen ? 20 : 0,
                                  decoration: BoxDecoration(
                                    color: CartzyColors.burgundy,
                                    borderRadius: BorderRadius.circular(1),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                  ),

                  // Row 3: Search bar
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 10, 16, 12),
                    child: Container(
                      height: 44,
                      decoration: BoxDecoration(
                        color: CartzyColors.background,
                        borderRadius: BorderRadius.circular(50),
                        border: Border.all(color: CartzyColors.border, width: 1.2),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: _searchController,
                              style: GoogleFonts.dmSans(fontSize: 13, color: CartzyColors.navy),
                              onSubmitted: (query) {
                                if (query.trim().isNotEmpty) {
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) => ProductListScreen(
                                        title: 'Search: $query',
                                      ),
                                    ),
                                  );
                                }
                              },
                              decoration: InputDecoration(
                                hintText: 'Search products, brands, categories...',
                                hintStyle: GoogleFonts.dmSans(
                                  fontSize: 13,
                                  color: CartzyColors.grayLight,
                                ),
                                border: InputBorder.none,
                                enabledBorder: InputBorder.none,
                                focusedBorder: InputBorder.none,
                                errorBorder: InputBorder.none,
                                disabledBorder: InputBorder.none,
                                filled: false,
                                fillColor: Colors.transparent,
                                isDense: true,
                                contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 11),
                              ),
                            ),
                          ),
                          // Search button
                          GestureDetector(
                            onTap: () {
                              final query = _searchController.text.trim();
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (_) => ProductListScreen(
                                    title: query.isNotEmpty ? 'Search: $query' : 'All Products',
                                  ),
                                ),
                              );
                            },
                            child: Container(
                              width: 36,
                              height: 36,
                              margin: const EdgeInsets.only(right: 4),
                              decoration: const BoxDecoration(
                                gradient: LinearGradient(
                                  colors: [Color(0xFF53414E), Color(0xFF865561)],
                                  begin: Alignment.topLeft,
                                  end: Alignment.bottomRight,
                                ),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.search, size: 16, color: Colors.white),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // ─────────────────────────────────────────────────
            // ICON STRIP  (quick-access icons)
            // ─────────────────────────────────────────────────
            Container(
              color: CartzyColors.surface,
              padding: const EdgeInsets.symmetric(vertical: 10),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  _IconStrip(icon: Icons.view_in_ar_outlined,   onTap: () {}),
                  _IconStrip(icon: Icons.chat_bubble_outline,    onTap: _showLoginRequiredDialog),
                  _IconStrip(icon: Icons.shopping_cart_outlined, onTap: _showLoginRequiredDialog),
                  _IconStrip(icon: Icons.favorite_border,        onTap: _showLoginRequiredDialog),
                  _IconStrip(icon: Icons.history_outlined,       onTap: _showLoginRequiredDialog),
                  _IconStrip(icon: Icons.location_on_outlined,   onTap: _showLoginRequiredDialog),
                  _IconStrip(icon: Icons.notifications_outlined, onTap: _showLoginRequiredDialog),
                ],
              ),
            ),

            // thin divider
            const Divider(height: 1, color: CartzyColors.border),

            // ─────────────────────────────────────────────────
            // SCROLLABLE BODY
            // ─────────────────────────────────────────────────
            Expanded(
              child: ListView(
                padding: const EdgeInsets.only(bottom: 30),
                children: [
                  const SizedBox(height: 20),

                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16),
                    child: HeroBanner(),
                  ),
                  const SizedBox(height: 24),

                  // CATEGORY CARDS GRID
                  CategoryCardGrid(
                    onCategoryTap: (label) {
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
                  const SizedBox(height: 24),

                  // CATEGORY ICON ROW
                  const SectionTitle(title: 'Shop by Category'),
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

                  // FLASH DEALS
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

                  // DAILY DISCOVER
                  DailyDiscoverSection(
                    onBrowseCategories: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => const ProductListScreen(
                            initialCategory: 'All',
                            title: 'Browse Categories',
                          ),
                        ),
                      );
                    },
                    onProductTap: (p) {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => ProductDetailsScreen(
                            name: p.name,
                            category: p.badge,
                            price: p.price,
                            isGuest: true,
                            imageUrl: (p.imageUrl != null && p.imageUrl!.isNotEmpty)
                                ? p.imageUrl
                                : CartzyProductImages.getImage(p.name, p.badge),
                            onBack: () => Navigator.of(context).pop(),
                            onAddToCart: _showLoginRequiredDialog,
                          ),
                        ),
                      );
                    },
                    onAddToCart: (_) => _showLoginRequiredDialog(),
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

// ──────────────────────────────────────────────────────────────
// ICON STRIP BUTTON
// ──────────────────────────────────────────────────────────────
class _IconStrip extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;

  const _IconStrip({required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Icon(icon, size: 22, color: CartzyColors.navy),
    );
  }
}
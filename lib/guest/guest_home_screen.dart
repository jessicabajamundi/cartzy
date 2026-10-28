import 'package:flutter/material.dart';
import 'package:cartzy/services/catalog_service.dart';
import 'package:cartzy/widgets/banner_action_icon.dart';
import 'package:cartzy/guest/guest_wishlist_screen.dart';
import 'package:cartzy/guest/guest_cart_screen.dart';
import 'package:cartzy/guest/guest_account_screen.dart';

import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/home_sections.dart';
import 'package:cartzy/products/product_list_screen.dart';
import 'package:cartzy/products/product_details_screen.dart';
import 'package:cartzy/products/all_categories_screen.dart';

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
  final GlobalKey<HeroBannerState> _heroKey = GlobalKey<HeroBannerState>();
  final GlobalKey<DailyDiscoverSectionState> _discoverKey =
      GlobalKey<DailyDiscoverSectionState>();

  void _showLoginRequiredDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        backgroundColor: CartzyColors.surface,
        title: Text(
          'Login Required',
          style: GoogleFonts.playfairDisplay(
              color: CartzyColors.navy, fontWeight: FontWeight.w700),
        ),
        content: Text(
          'Please log in or create an account to continue.',
          style: GoogleFonts.dmSans(color: CartzyColors.gray),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text('Cancel',
                style: GoogleFonts.dmSans(color: CartzyColors.gray)),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              widget.onLogin();
            },
            child: Text('Log In',
                style: GoogleFonts.dmSans(
                    color: CartzyColors.burgundy, fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );
  }

  void _openWishlist() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (routeContext) => GuestWishlistScreen(
          onLogin: () {
            Navigator.of(routeContext).pop();
            widget.onLogin();
          },
          onSignUp: () {
            Navigator.of(routeContext).pop();
            widget.onSignUp();
          },
        ),
      ),
    );
  }

  void _openCart() {
    final navigator = Navigator.of(context);
    final homeRoute = ModalRoute.of(context);
    navigator.push(
      MaterialPageRoute(
        builder: (routeContext) => GuestCartScreen(
          onLogin: () {
            navigator.popUntil((route) => route == homeRoute || route.isFirst);
            widget.onLogin();
          },
          onSignUp: () {
            navigator.popUntil((route) => route == homeRoute || route.isFirst);
            widget.onSignUp();
          },
          onHome: () => navigator
              .popUntil((route) => route == homeRoute || route.isFirst),
          onWishlist: () {
            Navigator.of(routeContext).pop();
            _openWishlist();
          },
        ),
      ),
    );
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _openSearch() {
    final query = _searchController.text.trim();
    Navigator.of(context).push(MaterialPageRoute(
      builder: (_) => ProductListScreen(
        title: query.isEmpty ? 'All Products' : 'Search: $query',
        initialQuery: query,
      ),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Stack(
          children: [
            // ─────────────────────────────────────────────────
            // Hero banner with an overlaid search, wishlist, and notifications
            // ─────────────────────────────────────────────────
            Positioned.fill(
              child: RefreshIndicator(
                color: CartzyColors.burgundy,
                onRefresh: () async {
                  CatalogService.clearCache();
                  await Future.wait([
                    _heroKey.currentState?.reload() ?? Future.value(),
                    _discoverKey.currentState?.reload() ?? Future.value(),
                  ]);
                },
                child: ListView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.only(bottom: 110),
                  children: [
                    // FULL-WIDTH HERO BANNER CAROUSEL (EDGE-TO-EDGE, NO GAPS)
                    Stack(
                      children: [
                        HeroBanner(key: _heroKey, contentTopInset: 72),
                        Positioned(
                            top: 0,
                            left: 0,
                            right: 0,
                            child: Padding(
                              padding: const EdgeInsets.fromLTRB(12, 10, 8, 12),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: TextField(
                                      controller: _searchController,
                                      textInputAction: TextInputAction.search,
                                      onSubmitted: (_) => _openSearch(),
                                      style: GoogleFonts.dmSans(
                                          fontSize: 13,
                                          color: CartzyColors.navy),
                                      decoration: InputDecoration(
                                        hintText: 'Search...',
                                        hintStyle: GoogleFonts.dmSans(
                                            fontSize: 13,
                                            color: CartzyColors.gray),
                                        filled: true,
                                        fillColor: CartzyColors.creamSection,
                                        isDense: true,
                                        contentPadding:
                                            const EdgeInsets.symmetric(
                                                horizontal: 16, vertical: 8),
                                        suffixIconConstraints:
                                            const BoxConstraints(
                                                minWidth: 40, minHeight: 40),
                                        suffixIcon: IconButton(
                                          tooltip: 'Search products',
                                          style: IconButton.styleFrom(
                                              minimumSize: const Size(40, 40),
                                              tapTargetSize:
                                                  MaterialTapTargetSize
                                                      .shrinkWrap),
                                          onPressed: _openSearch,
                                          icon: const Icon(Icons.search_rounded,
                                              size: 19,
                                              color: CartzyColors.burgundy),
                                        ),
                                        border: OutlineInputBorder(
                                          borderRadius:
                                              BorderRadius.circular(30),
                                          borderSide: const BorderSide(
                                              color: CartzyColors.pinkBorder),
                                        ),
                                        enabledBorder: OutlineInputBorder(
                                          borderRadius:
                                              BorderRadius.circular(30),
                                          borderSide: const BorderSide(
                                              color: CartzyColors.pinkBorder),
                                        ),
                                        focusedBorder: OutlineInputBorder(
                                          borderRadius:
                                              BorderRadius.circular(30),
                                          borderSide: const BorderSide(
                                              color: CartzyColors.burgundy),
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                  IconButton(
                                    tooltip: 'Wishlist',
                                    onPressed: _openWishlist,
                                    style: IconButton.styleFrom(
                                      backgroundColor: Colors.transparent,
                                      foregroundColor: CartzyColors.burgundy,
                                      minimumSize: const Size(48, 48),
                                    ),
                                    icon: const BannerActionIcon(
                                        Icons.favorite_border_rounded),
                                  ),
                                  IconButton(
                                    tooltip: 'Notifications',
                                    onPressed: _showLoginRequiredDialog,
                                    style: IconButton.styleFrom(
                                      backgroundColor: Colors.transparent,
                                      foregroundColor: CartzyColors.burgundy,
                                      minimumSize: const Size(48, 48),
                                    ),
                                    icon: const BannerActionIcon(
                                        Icons.notifications_none_rounded),
                                  ),
                                ],
                              ),
                            )),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // CATEGORY CARDS GRID
                    CategoryCardGrid(
                      onBrowseAllCategories: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => const AllCategoriesScreen(),
                          ),
                        );
                      },
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
                      onProductTap: (p) {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => ProductDetailsScreen(
                              productId: p.id,
                              name: p.name,
                              category: p.category,
                              price: p.price,
                              originalPrice: p.originalPrice,
                              isGuest: true,
                              imageUrl: p.effectiveImageUrl,
                              sellerName: p.sellerName,
                              badge: p.badge,
                              rating: p.rating,
                              soldCount: p.soldCount,
                              onBack: () => Navigator.of(context).pop(),
                              onAddToCart: _openCart,
                            ),
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 26),

                    // DAILY DISCOVER
                    DailyDiscoverSection(
                      key: _discoverKey,
                      onBrowseCategories: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => const AllCategoriesScreen(),
                          ),
                        );
                      },
                      onProductTap: (p) {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => ProductDetailsScreen(
                              productId: p.id,
                              name: p.name,
                              category: p.category,
                              price: p.price,
                              originalPrice: p.originalPrice,
                              isGuest: true,
                              imageUrl: p.effectiveImageUrl,
                              sellerName: p.sellerName,
                              badge: p.badge,
                              rating: p.rating,
                              soldCount: p.soldCount,
                              onBack: () => Navigator.of(context).pop(),
                              onAddToCart: _openCart,
                            ),
                          ),
                        );
                      },
                      onAddToCart: (_) => _openCart(),
                    ),
                  ],
                ),
              ),
            ),

            // ─────────────────────────────────────────────────
            // BOTTOM NAV BAR (Home, Cart, Sale, Profile)
            // ─────────────────────────────────────────────────
            Positioned(
              left: 0,
              right: 0,
              bottom: 0,
              child: Container(
                margin: const EdgeInsets.fromLTRB(16, 8, 16, 12),
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
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                child: Row(
                  children: [
                    _BottomNavItem(
                      icon: Icons.home_rounded,
                      label: 'Home',
                      selected: true,
                      onClick: () {},
                    ),
                    _BottomNavItem(
                      icon: Icons.shopping_cart_outlined,
                      label: 'Cart',
                      selected: false,
                      onClick: _openCart,
                    ),
                    _BottomNavItem(
                      icon: Icons.local_offer_outlined,
                      label: 'Sale',
                      selected: false,
                      isSale: true,
                      onClick: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => const ProductListScreen(
                              initialCategory: 'Flash Deals',
                              title: 'Sale & Flash Deals',
                            ),
                          ),
                        );
                      },
                    ),
                    _BottomNavItem(
                      icon: Icons.person_outline_rounded,
                      label: 'Profile',
                      selected: false,
                      onClick: () => GuestAccountScreen.open(
                        context,
                        onLogin: widget.onLogin,
                        onSignUp: widget.onSignUp,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ──────────────────────────────────────────────────────────────
// BOTTOM NAV ITEM (Guest)
// ──────────────────────────────────────────────────────────────
class _BottomNavItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final bool isSale;
  final VoidCallback onClick;

  const _BottomNavItem({
    required this.icon,
    required this.label,
    required this.selected,
    this.isSale = false,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    final color = isSale
        ? CartzyColors.burgundy
        : selected
            ? CartzyColors.burgundy
            : CartzyColors.gray;

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
                  fontWeight:
                      selected || isSale ? FontWeight.w600 : FontWeight.w400,
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

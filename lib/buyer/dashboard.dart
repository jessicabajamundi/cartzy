import 'dart:async';
import 'package:flutter/material.dart';
import 'package:cartzy/services/catalog_service.dart';
import 'package:cartzy/widgets/banner_action_icon.dart';
import 'package:cartzy/buyer/wishlist_screen.dart';
import 'package:cartzy/buyer/notifications_screen.dart';
import 'package:cartzy/services/notification_service.dart';
import 'package:cartzy/services/wishlist_service.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/home_sections.dart';
import 'package:cartzy/products/product_list_screen.dart';
import 'package:cartzy/products/product_details_screen.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/products/all_categories_screen.dart';

import 'package:cartzy/buyer/account_settings_screen.dart';
import 'package:cartzy/search/search_screen.dart';
import 'package:cartzy/buyer/messages_screen.dart';
import 'package:cartzy/services/message_service.dart';

class DashboardScreen extends StatefulWidget {
  final VoidCallback? onCartClick;
  final VoidCallback? onProfileClick;
  final VoidCallback? onVouchersClick;
  final VoidCallback? onLogout;
  final String? userName;

  const DashboardScreen({
    super.key,
    this.onCartClick,
    this.onProfileClick,
    this.onVouchersClick,
    this.onLogout,
    this.userName,
  });

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _selectedCategory = categoryIcons.first.label;

  final GlobalKey<HeroBannerState> _heroKey = GlobalKey<HeroBannerState>();
  final GlobalKey<DailyDiscoverSectionState> _discoverKey =
      GlobalKey<DailyDiscoverSectionState>();

  bool _isNavVisible = true;
  Timer? _scrollStopTimer;

  @override
  void initState() {
    super.initState();
    NotificationService.fetchUnreadCount();
    WishlistService.fetchWishlistRefs();
    MessageService.fetchUnreadCount();
  }

  bool _onScrollNotification(ScrollNotification notification) {
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

  void _openCart(BuildContext context) {
    if (widget.onCartClick != null) {
      widget.onCartClick!();
    } else {
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => CartScreen(
            onBack: () => Navigator.of(context).pop(),
            onProfileClick: widget.onProfileClick,
            onLogout: widget.onLogout,
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
    _scrollStopTimer?.cancel();
    _searchController.dispose();
    super.dispose();
  }

  void _openSearch([String? query]) {
    final q = query ?? _searchController.text.trim();
    Navigator.of(context).push(MaterialPageRoute(
      builder: (_) => SearchScreen(
        initialQuery: q.isNotEmpty ? q : null,
        autoFocus: q.isEmpty,
        isLoggedIn: true,
        userName: widget.userName,
        userEmail: '',
        onLogout: widget.onLogout,
      ),
    ));
  }

  void _openMessages() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MessagesScreen(
          onBack: () {
            Navigator.of(context).pop();
            MessageService.fetchUnreadCount();
          },
        ),
      ),
    ).then((_) => MessageService.fetchUnreadCount());
  }

  void _openWishlist() {
    Navigator.of(context).push(MaterialPageRoute(
      builder: (_) => WishlistScreen(
        onBack: () => Navigator.of(context).pop(),
        onDiscoverProducts: () => Navigator.of(context).pop(),
      ),
    )).then((_) => WishlistService.fetchWishlistRefs(force: true));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Stack(
          children: [
            // ─────────────────────────────────────────────────
            // HEADER
            // ─────────────────────────────────────────────────
            Positioned.fill(
              child: NotificationListener<ScrollNotification>(
                onNotification: _onScrollNotification,
                child: RefreshIndicator(
                color: CartzyColors.burgundy,
                onRefresh: () async {
                  CatalogService.clearCache();
                  MessageService.fetchUnreadCount();
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
                              padding: const EdgeInsets.fromLTRB(10, 10, 6, 12),
                              child: Row(
                                children: [
                                  // MESSAGE ICON BUTTON (Live Database unread counter)
                                  ValueListenableBuilder<int>(
                                    valueListenable: MessageService.unreadCountNotifier,
                                    builder: (context, msgCount, _) {
                                      return IconButton(
                                        tooltip: 'Messages',
                                        onPressed: _openMessages,
                                        style: IconButton.styleFrom(
                                          backgroundColor: Colors.transparent,
                                          foregroundColor: CartzyColors.burgundy,
                                          minimumSize: const Size(44, 44),
                                        ),
                                        icon: Stack(
                                          clipBehavior: Clip.none,
                                          children: [
                                            const BannerActionIcon(
                                                Icons.chat_bubble_outline_rounded),
                                            if (msgCount > 0)
                                              Positioned(
                                                right: 4,
                                                top: 4,
                                                child: Container(
                                                  padding: const EdgeInsets.symmetric(
                                                      horizontal: 4, vertical: 2),
                                                  decoration: const BoxDecoration(
                                                    color: Color(0xFFEF4444),
                                                    shape: BoxShape.circle,
                                                  ),
                                                  constraints: const BoxConstraints(
                                                    minWidth: 14,
                                                    minHeight: 14,
                                                  ),
                                                  child: Center(
                                                    child: Text(
                                                      msgCount > 99
                                                          ? '99+'
                                                          : '$msgCount',
                                                      style: const TextStyle(
                                                        color: Colors.white,
                                                        fontSize: 8.5,
                                                        fontWeight: FontWeight.bold,
                                                        height: 1,
                                                      ),
                                                    ),
                                                  ),
                                                ),
                                              ),
                                          ],
                                        ),
                                      );
                                    },
                                  ),
                                  const SizedBox(width: 4),

                                  // SEARCH BAR
                                  Expanded(
                                    child: TextField(
                                      controller: _searchController,
                                      readOnly: true,
                                      onTap: _openSearch,
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
                                                horizontal: 14, vertical: 8),
                                        suffixIconConstraints:
                                            const BoxConstraints(
                                                minWidth: 38, minHeight: 38),
                                        suffixIcon: IconButton(
                                          tooltip: 'Search products',
                                          style: IconButton.styleFrom(
                                              minimumSize: const Size(38, 38),
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
                                  const SizedBox(width: 2),

                                  // WISHLIST BUTTON
                                  ValueListenableBuilder<int>(
                                    valueListenable: WishlistService.countNotifier,
                                    builder: (context, wishCount, _) {
                                      return IconButton(
                                        tooltip: 'Wishlist',
                                        onPressed: _openWishlist,
                                        style: IconButton.styleFrom(
                                          backgroundColor: Colors.transparent,
                                          foregroundColor: CartzyColors.burgundy,
                                          minimumSize: const Size(44, 44),
                                        ),
                                        icon: Stack(
                                          clipBehavior: Clip.none,
                                          children: [
                                            const BannerActionIcon(
                                                Icons.favorite_border_rounded),
                                            if (wishCount > 0)
                                              Positioned(
                                                right: 4,
                                                top: 4,
                                                child: Container(
                                                  padding: const EdgeInsets.all(4),
                                                  decoration: const BoxDecoration(
                                                    color: Color(0xFFD1495B),
                                                    shape: BoxShape.circle,
                                                  ),
                                                  constraints: const BoxConstraints(
                                                    minWidth: 14,
                                                    minHeight: 14,
                                                  ),
                                                  child: Center(
                                                    child: Text(
                                                      wishCount > 99 ? '99+' : '$wishCount',
                                                      style: const TextStyle(
                                                        color: Colors.white,
                                                        fontSize: 8.5,
                                                        fontWeight: FontWeight.bold,
                                                        height: 1,
                                                      ),
                                                    ),
                                                  ),
                                                ),
                                              ),
                                          ],
                                        ),
                                      );
                                    },
                                  ),

                                  // NOTIFICATIONS BUTTON
                                  ValueListenableBuilder<int>(
                                    valueListenable: NotificationService.unreadCountNotifier,
                                    builder: (context, unreadCount, _) {
                                      return IconButton(
                                        tooltip: 'Notifications',
                                        onPressed: () {
                                          Navigator.of(context).push(
                                            MaterialPageRoute(
                                              builder: (_) => NotificationsScreen(
                                                onBack: () =>
                                                    Navigator.of(context).pop(),
                                              ),
                                            ),
                                          ).then((_) => NotificationService.fetchUnreadCount());
                                        },
                                        style: IconButton.styleFrom(
                                          backgroundColor: Colors.transparent,
                                          foregroundColor: CartzyColors.burgundy,
                                          minimumSize: const Size(44, 44),
                                        ),
                                        icon: Stack(
                                          clipBehavior: Clip.none,
                                          children: [
                                            const BannerActionIcon(
                                                Icons.notifications_none_rounded),
                                            if (unreadCount > 0)
                                              Positioned(
                                                right: 4,
                                                top: 4,
                                                child: Container(
                                                  padding: const EdgeInsets.all(4),
                                                  decoration: const BoxDecoration(
                                                    color: Color(0xFFEF4444),
                                                    shape: BoxShape.circle,
                                                  ),
                                                  constraints: const BoxConstraints(
                                                    minWidth: 8,
                                                    minHeight: 8,
                                                  ),
                                                ),
                                              ),
                                          ],
                                        ),
                                      );
                                    },
                                  ),
                                ],
                              ),
                            )),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // CATEGORY CARD GRID
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

                    // PROMO CARDS
                    const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 16),
                      child: Column(
                        children: [
                          PromoCard(
                            tag: 'FLEXIBLE PAYMENTS',
                            title: '0% Interest Installments',
                            description:
                                'Split payments into 3, 6, or 12 convenient monthly terms',
                            linkText: 'Learn More →',
                          ),
                          SizedBox(height: 12),
                          PromoCard(
                            tag: 'EXPRESS DELIVERY',
                            title: 'Next Day Nationwide',
                            description:
                                'Guaranteed prompt dispatch on verified partner items',
                            linkText: 'Explore Express →',
                            dark: true,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 26),

                    // CATEGORIES
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
                              imageUrl: p.effectiveImageUrl,
                              sellerName: p.sellerName,
                              badge: p.badge,
                              rating: p.rating,
                              soldCount: p.soldCount,
                              onBack: () => Navigator.of(context).pop(),
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
                              imageUrl: p.effectiveImageUrl,
                              sellerName: p.sellerName,
                              badge: p.badge,
                              rating: p.rating,
                              soldCount: p.soldCount,
                              onBack: () => Navigator.of(context).pop(),
                            ),
                          ),
                        );
                      },
                      onAddToCart: (p) {
                        CartService.instance.addItem(
                          name: p.name,
                          price: p.price,
                          category: p.category,
                          image: p.effectiveImageUrl,
                        );
                        ScaffoldMessenger.of(context).hideCurrentSnackBar();
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('${p.name} added to cart!'),
                            backgroundColor: CartzyColors.navy,
                            duration: const Duration(seconds: 3),
                            action: SnackBarAction(
                              label: 'VIEW CART',
                              textColor: CartzyColors.rose,
                              onPressed: () => _openCart(context),
                            ),
                          ),
                        );
                      },
                    ),
                  ],
                ),
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
              child: AnimatedSlide(
                offset: _isNavVisible ? Offset.zero : const Offset(0, 1.8),
                duration: const Duration(milliseconds: 260),
                curve: Curves.easeInOutCubic,
                child: AnimatedOpacity(
                  opacity: _isNavVisible ? 1.0 : 0.0,
                  duration: const Duration(milliseconds: 200),
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
                    _BottomNavigationItem(
                      icon: Icons.home_rounded,
                      label: 'Home',
                      selected: true,
                      onClick: () {},
                    ),
                    _BottomNavigationItem(
                      icon: Icons.shopping_cart_outlined,
                      label: 'Cart',
                      selected: false,
                      onClick: () => _openCart(context),
                    ),
                    _BottomNavigationItem(
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
                    _BottomNavigationItem(
                      icon: Icons.person_outline_rounded,
                      label: 'Profile',
                      selected: false,
                      onClick: () {
                        if (widget.onProfileClick != null) {
                          widget.onProfileClick!();
                        } else {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => AccountSettingsScreen(
                                isLoggedIn: true,
                                userName: widget.userName ?? 'Buyer',
                                userEmail: '',
                                onBack: () => Navigator.of(context).pop(),
                                onLogout: () {
                                  Navigator.of(context).pop();
                                  widget.onLogout?.call();
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
        ),
      ],
        ),
      ),
    );
  }
}

// ──────────────────────────────────────────────────────────────
// BOTTOM NAV ITEM
// ──────────────────────────────────────────────────────────────
class _BottomNavigationItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final bool isSale;
  final VoidCallback onClick;

  const _BottomNavigationItem({
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

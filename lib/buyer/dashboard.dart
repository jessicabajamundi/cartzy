import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/home_sections.dart';
import 'package:cartzy/products/product_list_screen.dart';
import 'package:cartzy/products/product_details_screen.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/utils/product_images.dart';

import 'package:cartzy/buyer/account_settings_screen.dart';
import 'package:cartzy/buyer/order_history_screen.dart';
import 'package:cartzy/buyer/wishlist_screen.dart';
import 'package:cartzy/buyer/messages_screen.dart';
import 'package:cartzy/buyer/notifications_screen.dart';
import 'package:cartzy/buyer/reviews_screen.dart';
import 'package:cartzy/buyer/my_orders_screen.dart';
import 'package:cartzy/buyer/track_order_screen.dart';
import 'package:cartzy/buyer/vouchers_screen.dart';

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
  String _selectedNavTab   = 'Home';

  final _navTabs = ['Home', 'New', 'Sale', 'Shop All', 'Collection'];

  final GlobalKey _headerAvatarKey = GlobalKey();
  final GlobalKey _stripAvatarKey = GlobalKey();
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

  final String _avatarUrl = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200';

  void _showProfilePopover(BuildContext context, {GlobalKey? anchorKey}) {
    Offset targetOffset = const Offset(0, 60);
    if (anchorKey != null && anchorKey.currentContext != null) {
      final renderBox = anchorKey.currentContext!.findRenderObject() as RenderBox?;
      if (renderBox != null) {
        final pos = renderBox.localToGlobal(Offset.zero);
        final size = renderBox.size;
        targetOffset = Offset(pos.dx, pos.dy + size.height + 8);
      }
    }

    final displayName = widget.userName != null &&
            widget.userName!.isNotEmpty &&
            widget.userName != 'Buyer'
        ? widget.userName!
        : 'Leonardo, Tiffany Joy O.';

    showGeneralDialog(
      context: context,
      barrierDismissible: true,
      barrierLabel: 'Dismiss Profile Menu',
      barrierColor: Colors.black.withValues(alpha: 0.08),
      transitionDuration: const Duration(milliseconds: 180),
      pageBuilder: (ctx, anim1, anim2) {
        final screenWidth = MediaQuery.of(context).size.width;
        double? leftPos;
        double? rightPos;

        if (targetOffset.dx > 0 && targetOffset.dx + 285 <= screenWidth) {
          leftPos = (targetOffset.dx - 80).clamp(12.0, screenWidth - 295.0);
        } else {
          rightPos = 16.0;
        }

        return Stack(
          children: [
            Positioned(
              top: targetOffset.dy.clamp(50.0, 320.0),
              left: leftPos,
              right: rightPos,
              child: Material(
                color: Colors.transparent,
                child: Container(
                  width: 280,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(color: const Color(0xFFF0E5E7), width: 1.2),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.09),
                        blurRadius: 26,
                        spreadRadius: 2,
                        offset: const Offset(0, 10),
                      ),
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.03),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header: Avatar + Name + "Buyer account"
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 16, 16, 14),
                        child: Row(
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(12),
                              child: Image.network(
                                _avatarUrl,
                                width: 46,
                                height: 46,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => Container(
                                  width: 46,
                                  height: 46,
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF865561),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: const Icon(Icons.person, color: Colors.white, size: 24),
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    displayName,
                                    style: GoogleFonts.dmSans(
                                      fontSize: 14.5,
                                      fontWeight: FontWeight.w700,
                                      color: const Color(0xFF231F20),
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    'Buyer account',
                                    style: GoogleFonts.dmSans(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w400,
                                      color: const Color(0xFF8E8286),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),

                      const Divider(height: 1, thickness: 1, color: Color(0xFFF3ECEE)),

                      // Item 1: Account Settings
                      InkWell(
                        onTap: () {
                          Navigator.pop(ctx);
                          if (widget.onProfileClick != null) {
                            widget.onProfileClick!();
                          } else {
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => AccountSettingsScreen(
                                  userName: displayName,
                                  userEmail: 'tiffany.leonardolspu@gmail.com',
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
                        hoverColor: const Color(0xFFF7F2F4),
                        borderRadius: BorderRadius.circular(8),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
                          child: Row(
                            children: [
                              const Icon(Icons.person_outline, size: 20, color: Color(0xFF7A6E72)),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Text(
                                  'Account Settings',
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13.5,
                                    fontWeight: FontWeight.w500,
                                    color: const Color(0xFF231F20),
                                  ),
                                ),
                              ),
                              const Icon(Icons.chevron_right, size: 18, color: Color(0xFFC4B8BA)),
                            ],
                          ),
                        ),
                      ),

                      // Item 2: Reviews & Ratings
                      InkWell(
                        onTap: () {
                          Navigator.pop(ctx);
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => ReviewsScreen(
                                onBack: () => Navigator.of(context).pop(),
                                onViewOrders: () {
                                  Navigator.of(context).pop();
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) => OrderHistoryScreen(
                                        onBack: () => Navigator.of(context).pop(),
                                      ),
                                    ),
                                  );
                                },
                              ),
                            ),
                          );
                        },
                        hoverColor: const Color(0xFFF7F2F4),
                        borderRadius: BorderRadius.circular(8),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
                          child: Row(
                            children: [
                              const Icon(Icons.shopping_bag_outlined, size: 20, color: Color(0xFF7A6E72)),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Text(
                                  'Reviews & Ratings',
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13.5,
                                    fontWeight: FontWeight.w500,
                                    color: const Color(0xFF231F20),
                                  ),
                                ),
                              ),
                              const Icon(Icons.chevron_right, size: 18, color: Color(0xFFC4B8BA)),
                            ],
                          ),
                        ),
                      ),

                      // Item 3: My Vouchers
                      InkWell(
                        onTap: () {
                          Navigator.pop(ctx);
                          if (widget.onVouchersClick != null) {
                            widget.onVouchersClick!();
                          } else {
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => VouchersScreen(
                                  onBack: () => Navigator.of(context).pop(),
                                  onUseAtCheckout: () {
                                    Navigator.of(context).pop();
                                    widget.onCartClick?.call();
                                  },
                                ),
                              ),
                            );
                          }
                        },
                        hoverColor: const Color(0xFFF7F2F4),
                        borderRadius: BorderRadius.circular(8),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
                          child: Row(
                            children: [
                              const Icon(Icons.confirmation_number_outlined, size: 20, color: Color(0xFF7A6E72)),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Text(
                                  'My Vouchers',
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13.5,
                                    fontWeight: FontWeight.w500,
                                    color: const Color(0xFF231F20),
                                  ),
                                ),
                              ),
                              const Icon(Icons.chevron_right, size: 18, color: Color(0xFFC4B8BA)),
                            ],
                          ),
                        ),
                      ),

                      const Divider(height: 1, thickness: 1, color: Color(0xFFF3ECEE)),

                      // Item 4: Sign out
                      InkWell(
                        onTap: () {
                          Navigator.pop(ctx);
                          widget.onLogout?.call();
                        },
                        borderRadius: const BorderRadius.vertical(bottom: Radius.circular(18)),
                        child: Padding(
                          padding: const EdgeInsets.fromLTRB(16, 13, 16, 15),
                          child: Row(
                            children: [
                              const Icon(Icons.logout_rounded, size: 20, color: Color(0xFF9E404E)),
                              const SizedBox(width: 14),
                              Text(
                                'Sign out',
                                style: GoogleFonts.dmSans(
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.w600,
                                  color: const Color(0xFF9E404E),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
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
          child: ScaleTransition(
            scale: CurvedAnimation(parent: anim1, curve: Curves.easeOutCubic),
            alignment: Alignment.topRight,
            child: child,
          ),
        );
      },
    );
  }

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
      barrierColor: Colors.black.withValues(alpha: 0.04),
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
                        color: Colors.black.withValues(alpha: 0.09),
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
            // ─────────────────────────────────────────────────
            // HEADER
            // ─────────────────────────────────────────────────
            Container(
              color: CartzyColors.surface,
              child: Column(
                children: [
                  // Row 1: Logo + Hi name + Cart + Profile
                  Padding(
                    padding: const EdgeInsets.fromLTRB(20, 14, 16, 0),
                    child: Row(
                      children: [
                        Image.asset('assets/cartzy_splash.png', height: 28, fit: BoxFit.contain),
                        if (widget.userName != null && widget.userName!.isNotEmpty) ...[
                          const SizedBox(width: 10),
                          Flexible(
                            child: Text(
                              'Hi, ${widget.userName}! 👋',
                              style: GoogleFonts.dmSans(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: CartzyColors.navy,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                        const Spacer(),
                        // Cart icon with badge
                        ListenableBuilder(
                          listenable: CartService.instance,
                          builder: (context, _) {
                            final count = CartService.instance.totalItemCount;
                            return Stack(
                              alignment: Alignment.center,
                              children: [
                                IconButton(
                                  icon: const Icon(Icons.shopping_cart_outlined, color: CartzyColors.navy, size: 22),
                                  onPressed: () => _openCart(context),
                                ),
                                if (count > 0)
                                  Positioned(
                                    right: 6,
                                    top: 6,
                                    child: Container(
                                      padding: const EdgeInsets.all(3),
                                      decoration: const BoxDecoration(
                                        color: CartzyColors.burgundy,
                                        shape: BoxShape.circle,
                                      ),
                                      constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                                      child: Text(
                                        count > 99 ? '99+' : '$count',
                                        textAlign: TextAlign.center,
                                        style: GoogleFonts.dmSans(color: Colors.white, fontSize: 9, fontWeight: FontWeight.w700),
                                      ),
                                    ),
                                  ),
                              ],
                            );
                          },
                        ),
                        GestureDetector(
                          key: _headerAvatarKey,
                          onTap: () => _showProfilePopover(context, anchorKey: _headerAvatarKey),
                          child: Container(
                            width: 32,
                            height: 32,
                            margin: const EdgeInsets.only(left: 4),
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              border: Border.all(color: const Color(0xFFDCC8CA), width: 1.5),
                            ),
                            child: ClipOval(
                              child: Image.network(
                                _avatarUrl,
                                width: 32,
                                height: 32,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => const CircleAvatar(
                                  backgroundColor: Color(0xFF865561),
                                  child: Icon(Icons.person, color: Colors.white, size: 16),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Row 2: Nav tabs
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 10, 16, 0),
                    child: SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      physics: const BouncingScrollPhysics(),
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
              padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 8),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                physics: const BouncingScrollPhysics(),
                child: ConstrainedBox(
                  constraints: BoxConstraints(
                    minWidth: MediaQuery.of(context).size.width - 16,
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      // Profile Avatar in icon strip (matching Screenshot 1)
                      GestureDetector(
                        key: _stripAvatarKey,
                        onTap: () => _showProfilePopover(context, anchorKey: _stripAvatarKey),
                        child: Container(
                          width: 34,
                          height: 34,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(color: const Color(0xFFDCC8CA), width: 1.5),
                          ),
                          child: ClipOval(
                            child: Image.network(
                              _avatarUrl,
                              width: 34,
                              height: 34,
                              fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) => const CircleAvatar(
                                backgroundColor: Color(0xFF865561),
                                child: Icon(Icons.person, color: Colors.white, size: 16),
                              ),
                            ),
                          ),
                        ),
                      ),
                      _IconStrip(
                        icon: Icons.view_in_ar_outlined,
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => MyOrdersScreen(
                              onBack: () => Navigator.of(context).pop(),
                              onStartShopping: () => Navigator.of(context).pop(),
                            ),
                          ),
                        ),
                      ),
                      _IconStrip(
                        icon: Icons.chat_bubble_outline,
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => MessagesScreen(
                              onBack: () => Navigator.of(context).pop(),
                            ),
                          ),
                        ),
                      ),
                      ListenableBuilder(
                        listenable: CartService.instance,
                        builder: (context, _) => _IconStrip(
                          icon: Icons.shopping_cart_outlined,
                          badgeCount: CartService.instance.totalItemCount,
                          onTap: () => _openCart(context),
                        ),
                      ),
                      _IconStrip(
                        icon: Icons.favorite_border,
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => WishlistScreen(
                              onBack: () => Navigator.of(context).pop(),
                              onDiscoverProducts: () => Navigator.of(context).pop(),
                            ),
                          ),
                        ),
                      ),
                      _IconStrip(
                        icon: Icons.history_outlined,
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => OrderHistoryScreen(
                              onBack: () => Navigator.of(context).pop(),
                              onStartShopping: () => Navigator.of(context).pop(),
                            ),
                          ),
                        ),
                      ),
                      _IconStrip(
                        icon: Icons.location_on_outlined,
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => TrackOrderScreen(
                              onBack: () => Navigator.of(context).pop(),
                              onBrowseProducts: () => Navigator.of(context).pop(),
                            ),
                          ),
                        ),
                      ),
                      _IconStrip(
                        icon: Icons.notifications_outlined,
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => NotificationsScreen(
                              onBack: () => Navigator.of(context).pop(),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            const Divider(height: 1, color: CartzyColors.border),

            // ─────────────────────────────────────────────────
            // BODY
            // ─────────────────────────────────────────────────
            Expanded(
              child: ListView(
                padding: const EdgeInsets.only(bottom: 20),
                children: [
                  const SizedBox(height: 20),

                  // HERO BANNER CAROUSEL
                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16),
                    child: HeroBanner(),
                  ),
                  const SizedBox(height: 24),

                  // CATEGORY CARD GRID
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

                  // PROMO CARDS
                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16),
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
                                    textColor: CartzyColors.rose,
                                    onPressed: () => _openCart(context),
                                  ),
                                ),
                              );
                            },
                          ),
                        ),
                      );
                    },
                    onAddToCart: (p) {
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

            // ─────────────────────────────────────────────────
            // BOTTOM NAV BAR
            // ─────────────────────────────────────────────────
            Container(
              decoration: const BoxDecoration(
                color: CartzyColors.surface,
                border: const Border(top: const BorderSide(color: CartzyColors.border)),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 30, vertical: 10),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _BottomNavigationItem(
                    icon: Icons.home_outlined,
                    label: 'Home',
                    selected: _selectedNavTab == 'Home',
                    onClick: () => setState(() => _selectedNavTab = 'Home'),
                  ),
                  _BottomNavigationItem(
                    icon: Icons.search_outlined,
                    label: 'Search',
                    selected: false,
                    onClick: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => const ProductListScreen(
                            initialCategory: 'All',
                            title: 'Search Products',
                          ),
                        ),
                      );
                    },
                  ),
                  ListenableBuilder(
                    listenable: CartService.instance,
                    builder: (context, _) => _BottomNavigationItem(
                      icon: Icons.shopping_cart_outlined,
                      label: 'Cart',
                      selected: false,
                      badgeCount: CartService.instance.totalItemCount,
                      onClick: () => _openCart(context),
                    ),
                  ),
                  _BottomNavigationItem(
                    icon: Icons.person_outline,
                    label: 'Profile',
                    selected: false,
                    onClick: () => _showProfilePopover(context),
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
// BOTTOM NAV ITEM
// ──────────────────────────────────────────────────────────────
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
    final color = selected ? CartzyColors.burgundy : CartzyColors.gray;
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
                      color: CartzyColors.burgundy,
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                    child: Text(
                      badgeCount > 99 ? '99+' : '$badgeCount',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.dmSans(color: Colors.white, fontSize: 9, fontWeight: FontWeight.w700),
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 3),
          Text(
            label,
            style: GoogleFonts.dmSans(
              fontSize: 11,
              color: color,
              fontWeight: selected ? FontWeight.w600 : FontWeight.w400,
            ),
          ),
        ],
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
  final int badgeCount;

  const _IconStrip({
    required this.icon,
    required this.onTap,
    this.badgeCount = 0,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
        child: Stack(
          clipBehavior: Clip.none,
          alignment: Alignment.center,
          children: [
            Icon(icon, size: 22, color: CartzyColors.navy),
            if (badgeCount > 0)
              Positioned(
                right: -6,
                top: -6,
                child: Container(
                  padding: const EdgeInsets.all(3),
                  decoration: const BoxDecoration(
                    color: CartzyColors.burgundy,
                    shape: BoxShape.circle,
                  ),
                  constraints: const BoxConstraints(minWidth: 15, minHeight: 15),
                  child: Text(
                    badgeCount > 99 ? '99+' : '$badgeCount',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.dmSans(
                      color: Colors.white,
                      fontSize: 8.5,
                      fontWeight: FontWeight.w700,
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
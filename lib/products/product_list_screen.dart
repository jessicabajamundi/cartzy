import 'package:flutter/material.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/guest/guest_cart_screen.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/product_card.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/utils/product_images.dart';
import 'package:cartzy/services/catalog_service.dart';
import 'dart:async';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/buyer/account_settings_screen.dart';
import 'product_details_screen.dart';

class ProductItem {
  /// Database id when the product came from the API (null for built-in samples).
  final int? id;
  final String name;
  final String category;
  final String price;
  final String? originalPrice;
  final int discountPercent;
  final String badge;
  final double rating;
  final String soldCount;
  final String? imageUrl;
  final String? sellerName;
  final bool inStock;
  final int? stock;
  final bool hasFreeShipping;

  const ProductItem({
    this.id,
    required this.name,
    required this.category,
    required this.price,
    this.originalPrice,
    required this.discountPercent,
    required this.badge,
    required this.rating,
    required this.soldCount,
    this.imageUrl,
    this.sellerName,
    this.inStock = true,
    this.stock,
    this.hasFreeShipping = true,
  });

  String get effectiveImageUrl => (imageUrl != null && imageUrl!.isNotEmpty)
      ? imageUrl!
      : CartzyProductImages.getImage(name, category);
}

class ProductListScreen extends StatefulWidget {
  final String initialQuery;
  final String? initialCategory;
  final String? title;
  final VoidCallback? onBack;
  final bool randomize;

  const ProductListScreen({
    super.key,
    this.initialQuery = '',
    this.initialCategory,
    this.title,
    this.onBack,
    this.randomize = false,
  });

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  final TextEditingController _searchController = TextEditingController();
  bool _isNavVisible = true;
  Timer? _scrollStopTimer;

  bool _onScrollNotification(ScrollNotification notification) {
    if (notification.metrics.axis != Axis.vertical) return false;
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
  late String _selectedCategory;
  List<ProductItem> _baseProducts = [];
  bool _loading = true;
  String? _loadError;

  final List<String> _categories = [
    'All',
    'Flash Deals',
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

  @override
  void initState() {
    super.initState();
    _searchController.text = widget.initialQuery;
    _selectedCategory = widget.initialCategory ?? 'All';
    _loadCategories();
    _loadProducts();
  }

  Future<void> _loadCategories() async {
    try {
      final names = await CatalogService.fetchCategoryNames();
      if (mounted && names.isNotEmpty) {
        setState(() {
          _categories.clear();
          _categories.addAll(['All', 'Flash Deals', ...names]);
        });
      }
    } catch (_) {}
  }

  /// Loads the website's products for the selected category from the Laravel API.
  Future<void> _loadProducts() async {
    setState(() {
      _loading = true;
      _loadError = null;
    });
    try {
      final products =
          await CatalogService.fetchProducts(category: _selectedCategory);
      if (!mounted) return;
      setState(() {
        _baseProducts = products;
        if (widget.randomize) _baseProducts.shuffle();
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _baseProducts = [];
        _loading = false;
        _loadError = e.toString();
      });
    }
  }

  void _shuffleProducts() {
    setState(() {
      _baseProducts.shuffle();
    });
  }

  @override
  void dispose() {
    _scrollStopTimer?.cancel();
    _searchController.dispose();
    super.dispose();
  }

  List<ProductItem> get _filteredProducts {
    final query = _searchController.text.trim().toLowerCase();
    return _baseProducts
        .where((p) => query.isEmpty || p.name.toLowerCase().contains(query))
        .toList();
  }

  void _navigateToCart(BuildContext context) {
    if (!AuthService.isAuthenticated) {
      GuestCartScreen.open(context);
      return;
    }
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

  void _openDetails(ProductItem product) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => ProductDetailsScreen(
          productId: product.id,
          name: product.name,
          category: product.category,
          price: product.price,
          originalPrice: product.originalPrice,
          imageUrl: product.effectiveImageUrl,
          sellerName: product.sellerName,
          badge: product.badge,
          rating: product.rating,
          soldCount: product.soldCount,
          onBack: () => Navigator.of(context).pop(),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredProducts;
    return Scaffold(
      extendBody: true,
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
          onPressed: widget.onBack ?? () => Navigator.of(context).maybePop(),
        ),
        title: Text(
          widget.title ?? 'Products',
          style: const TextStyle(
              color: CartzyColors.navy, fontWeight: FontWeight.bold),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.shuffle, color: CartzyColors.navy, size: 20),
            tooltip: 'Shuffle Random Products',
            onPressed: _shuffleProducts,
          ),
          Center(
            child: Text(
              '${filtered.length} items',
              style: const TextStyle(color: CartzyColors.gray, fontSize: 13),
            ),
          ),
          const SizedBox(width: 4),
          ListenableBuilder(
            listenable: CartService.instance,
            builder: (context, _) {
              final count = CartService.instance.totalItemCount;
              return Stack(
                alignment: Alignment.center,
                children: [
                  IconButton(
                    icon: const Icon(Icons.shopping_cart_outlined,
                        color: CartzyColors.navy),
                    tooltip: 'View Cart',
                    onPressed: () => _navigateToCart(context),
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
                        constraints:
                            const BoxConstraints(minWidth: 18, minHeight: 18),
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
          const SizedBox(width: 8),
        ],
      ),
      body: NotificationListener<ScrollNotification>(
        onNotification: _onScrollNotification,
        child: Column(
          children: [
          // Search bar
          Container(
            color: CartzyColors.surface,
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
            child: TextField(
              controller: _searchController,
              onChanged: (_) => setState(() {}),
              decoration: InputDecoration(
                hintText: 'Search products...',
                hintStyle:
                    const TextStyle(color: CartzyColors.gray, fontSize: 14),
                prefixIcon: const Icon(Icons.search,
                    color: CartzyColors.gray, size: 20),
                filled: true,
                fillColor: CartzyColors.background,
                contentPadding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
          ),
          // Category chips
          SizedBox(
            height: 48,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
              itemCount: _categories.length,
              itemBuilder: (context, index) {
                final cat = _categories[index];
                final isSelected = cat == _selectedCategory;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: ChoiceChip(
                    label: Text(
                      cat,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: isSelected ? Colors.white : CartzyColors.navy,
                      ),
                    ),
                    selected: isSelected,
                    selectedColor: CartzyColors.coral,
                    backgroundColor: CartzyColors.surface,
                    showCheckmark: false,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(20),
                      side: BorderSide(
                          color: isSelected
                              ? CartzyColors.coral
                              : CartzyColors.border),
                    ),
                    onSelected: (val) {
                      if (!val || cat == _selectedCategory) return;
                      setState(() => _selectedCategory = cat);
                      _loadProducts();
                    },
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 4),
          // Product grid
          Expanded(
            child: _loading
                ? const Center(
                    child: CircularProgressIndicator(color: CartzyColors.coral))
                : _loadError != null
                    ? Center(
                        child: Padding(
                          padding: const EdgeInsets.all(24),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.cloud_off,
                                  size: 48, color: CartzyColors.gray),
                              const SizedBox(height: 12),
                              const Text("Couldn't load products",
                                  style: TextStyle(
                                      color: CartzyColors.navy,
                                      fontSize: 15,
                                      fontWeight: FontWeight.w600)),
                              const SizedBox(height: 6),
                              Text(_loadError!,
                                  textAlign: TextAlign.center,
                                  style: const TextStyle(
                                      color: CartzyColors.gray, fontSize: 13)),
                              const SizedBox(height: 14),
                              OutlinedButton(
                                  onPressed: _loadProducts,
                                  child: const Text('Try again')),
                            ],
                          ),
                        ),
                      )
                    : filtered.isEmpty
                        ? const Center(
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.search_off,
                                    size: 48, color: CartzyColors.gray),
                                SizedBox(height: 12),
                                Text('No products found',
                                    style: TextStyle(
                                        color: CartzyColors.gray,
                                        fontSize: 15)),
                              ],
                            ),
                          )
                        : LayoutBuilder(builder: (context, constraints) {
                          // ProductCard is a square photo as wide as the card plus
                          // a details section that grows with the text size.
                          final cardWidth = (constraints.maxWidth - 32 - 10) / 2;
                          final cardHeight =
                              cardWidth + ProductCard.bodyHeight(context);
                          return GridView.builder(
                            physics: const AlwaysScrollableScrollPhysics(),
                            padding: const EdgeInsets.fromLTRB(16, 12, 16, 96),
                            gridDelegate:
                                SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              mainAxisSpacing: 10,
                              crossAxisSpacing: 10,
                              mainAxisExtent: cardHeight,
                            ),
                            itemCount: filtered.length,
                            itemBuilder: (context, index) {
                              final product = filtered[index];
                              return ProductCard(
                                productId: product.id,
                                name: product.name,
                                price: product.price,
                                originalPrice: product.originalPrice,
                                discountPercent: product.discountPercent,
                                badge: product.badge,
                                rating: product.rating,
                                soldCount: product.soldCount,
                                imageUrl: product.effectiveImageUrl,
                                sellerName: product.sellerName,
                                inStock: product.inStock,
                                stock: product.stock,
                                hasFreeShipping: product.hasFreeShipping,
                                onTap: () => _openDetails(product),
                                onAddToCart: () {
                                  if (!AuthService.isAuthenticated) {
                                    GuestCartScreen.open(context);
                                    return;
                                  }
                                  CartService.instance.addItem(
                                    name: product.name,
                                    price: product.price,
                                    originalPrice: product.originalPrice,
                                    category: product.category,
                                    image: product.effectiveImageUrl,
                                  );
                                  ScaffoldMessenger.of(context)
                                      .hideCurrentSnackBar();
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(
                                          '${product.name} added to cart!'),
                                      backgroundColor: CartzyColors.navy,
                                      duration: const Duration(seconds: 3),
                                      action: SnackBarAction(
                                        label: 'VIEW CART',
                                        textColor: CartzyColors.rose,
                                        onPressed: () =>
                                            _navigateToCart(context),
                                      ),
                                    ),
                                  );
                                },
                              );
                            },
                          );
                        }),
          ),
        ],
      ),
      ),
      bottomNavigationBar: SafeArea(
        top: false,
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
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              child: Row(
                children: [
                  _ProductBottomNavItem(
                    icon: Icons.home_rounded,
                    label: 'Home',
                    selected: false,
                    onClick: () {
                      if (widget.onBack != null) {
                        widget.onBack!();
                      } else {
                        Navigator.of(context).popUntil((route) => route.isFirst);
                      }
                    },
                  ),
                  _ProductBottomNavItem(
                    icon: Icons.shopping_cart_outlined,
                    label: 'Cart',
                    selected: false,
                    onClick: () => _navigateToCart(context),
                  ),
                  _ProductBottomNavItem(
                    icon: Icons.local_offer_rounded,
                    label: 'Sale',
                    selected: _selectedCategory == 'Flash Deals' || (widget.title != null && widget.title!.toLowerCase().contains('sale')),
                    isSale: true,
                    onClick: () {
                      if (_selectedCategory != 'Flash Deals') {
                        setState(() => _selectedCategory = 'Flash Deals');
                        _loadProducts();
                      }
                    },
                  ),
                  _ProductBottomNavItem(
                    icon: Icons.person_outline_rounded,
                    label: 'Profile',
                    selected: false,
                    onClick: () {
                      if (!AuthService.isAuthenticated) {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => AccountSettingsScreen(
                              isLoggedIn: false,
                              userName: 'Guest',
                              userEmail: '',
                              onBack: () => Navigator.of(context).pop(),
                              onLogout: () {},
                            ),
                          ),
                        );
                      } else {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => AccountSettingsScreen(
                              isLoggedIn: true,
                              userName: 'Buyer',
                              userEmail: '',
                              onBack: () => Navigator.of(context).pop(),
                              onLogout: () async {
                                await AuthService.logout();
                                if (context.mounted) {
                                  Navigator.of(context).popUntil((route) => route.isFirst);
                                }
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
    );
  }
}

// ──────────────────────────────────────────────────────────────
// PRODUCT LIST BOTTOM NAV ITEM
// ──────────────────────────────────────────────────────────────
class _ProductBottomNavItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final bool isSale;
  final VoidCallback onClick;

  const _ProductBottomNavItem({
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


import 'package:flutter/material.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/guest/guest_cart_screen.dart';
import 'package:cartzy/guest/guest_account_screen.dart';
import 'package:cartzy/buyer/messages_screen.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/services/catalog_service.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/widgets/write_review_dialog.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';
import 'package:cartzy/utils/product_images.dart';

class ProductDetailsScreen extends StatefulWidget {
  final int? productId;
  final String name;
  final String category;
  final String price;
  final String? originalPrice;
  final String? imageUrl;
  final String? sellerName;
  final String? badge;
  final double? rating;
  final String? soldCount;
  final VoidCallback onBack;
  final VoidCallback? onAddToCart;
  final bool isGuest;

  const ProductDetailsScreen({
    super.key,
    this.productId,
    required this.name,
    required this.category,
    required this.price,
    this.originalPrice,
    this.imageUrl,
    this.sellerName,
    this.badge,
    this.rating,
    this.soldCount,
    required this.onBack,
    this.onAddToCart,
    this.isGuest = false,
  });

  @override
  State<ProductDetailsScreen> createState() => _ProductDetailsScreenState();
}

class _ProductDetailsScreenState extends State<ProductDetailsScreen> {
  late PageController _pageController;
  int _currentImageIndex = 0;

  // Gallery images list
  List<String> _images = [];

  // Live state values
  late String _name;
  late String _category;
  late String _price;
  String? _originalPrice;
  late String _sellerName;
  String? _sellerLogoUrl;
  double? _sellerFreeShippingMin;
  late String _badge;
  late double _rating;
  late String _soldCount;
  int _reviewsCount = 0;
  String? _description;
  String? _gender;

  // Variants
  List<Map<String, dynamic>> _variants = [];
  Map<String, dynamic>? _selectedVariant;
  List<String> _colors = [];
  List<String> _sizes = [];
  String? _selectedColor;
  String? _selectedSize;

  // Quantity & Stock
  int _quantity = 1;
  int _stock = 99;
  bool _inStock = true;

  // Reviews & Rating breakdown
  Map<int, int> _starCounts = {5: 0, 4: 0, 3: 0, 2: 0, 1: 0};
  List<Map<String, dynamic>> _reviews = [];

  bool _isLoading = false;
  bool _isWishlisted = false;
  bool _isDescriptionExpanded = false;

  @override
  void initState() {
    super.initState();
    _pageController = PageController();

    _name = widget.name;
    _category = widget.category;
    _price = widget.price;
    _originalPrice = widget.originalPrice;
    _sellerName = widget.sellerName ?? 'Cartzy Store';
    _badge = widget.badge ?? '';
    _rating = widget.rating ?? 4.8;
    _soldCount = widget.soldCount ?? '0 sold';

    final initialImg = (widget.imageUrl != null && widget.imageUrl!.isNotEmpty)
        ? widget.imageUrl!
        : CartzyProductImages.getImage(widget.name, widget.category);
    _images = [initialImg];

    if (widget.productId != null) {
      _loadProductDetails(widget.productId!);
    }
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _loadProductDetails(int id) async {
    setState(() => _isLoading = true);
    try {
      final data = await CatalogService.fetchProductDetails(id);
      if (data != null && mounted) {
        setState(() {
          _name = data['name']?.toString() ?? _name;
          if (data['category'] is Map) {
            _category = data['category']['name']?.toString() ?? _category;
          }

          if (data['seller'] is Map) {
            _sellerName = data['seller']['name']?.toString() ?? _sellerName;
            _sellerLogoUrl = data['seller']['logo_url']?.toString();
            if (data['seller']['free_shipping_min'] != null) {
              _sellerFreeShippingMin = (data['seller']['free_shipping_min'] as num).toDouble();
            }
          }

          final rawBadge = data['badge']?.toString().trim();
          if (rawBadge != null && rawBadge.isNotEmpty) {
            _badge = rawBadge;
          }

          if (data['rating'] != null) {
            _rating = (data['rating'] as num).toDouble();
          }
          if (data['reviews_count'] != null) {
            _reviewsCount = (data['reviews_count'] as num).toInt();
          }
          if (data['sold'] != null) {
            final s = (data['sold'] as num).toInt();
            _soldCount = s >= 1000 ? '${(s / 1000).toStringAsFixed(1)}k sold' : '$s sold';
          }
          if (data['in_stock'] != null) {
            _inStock = data['in_stock'] as bool;
          }
          _gender = data['gender']?.toString();
          _description = data['description']?.toString();

          // Full gallery images
          if (data['images'] is List) {
            final imgs = (data['images'] as List)
                .map((e) => e.toString().trim())
                .where((e) => e.isNotEmpty)
                .toList();
            if (imgs.isNotEmpty) {
              _images = imgs;
            }
          }

          // Variants
          if (data['variants'] is List) {
            _variants = (data['variants'] as List)
                .map((v) => Map<String, dynamic>.from(v as Map))
                .toList();

            final colorSet = <String>{};
            final sizeSet = <String>{};
            for (final v in _variants) {
              final c = v['color']?.toString().trim();
              if (c != null && c.isNotEmpty) colorSet.add(c);
              final s = v['size']?.toString().trim();
              if (s != null && s.isNotEmpty) sizeSet.add(s);
            }
            _colors = colorSet.toList();
            _sizes = sizeSet.toList();

            // Default variant selection
            if (_variants.isNotEmpty) {
              _selectVariant(_variants.first, updatePage: false);
            }
          }

          // Star counts breakdown
          if (data['star_counts'] is Map) {
            final sc = data['star_counts'] as Map;
            _starCounts = {
              5: (sc['5'] as num?)?.toInt() ?? 0,
              4: (sc['4'] as num?)?.toInt() ?? 0,
              3: (sc['3'] as num?)?.toInt() ?? 0,
              2: (sc['2'] as num?)?.toInt() ?? 0,
              1: (sc['1'] as num?)?.toInt() ?? 0,
            };
          }

          // Real reviews
          if (data['reviews'] is List) {
            _reviews = (data['reviews'] as List)
                .map((r) => Map<String, dynamic>.from(r as Map))
                .toList();
          }

          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _selectVariant(Map<String, dynamic> variant, {bool updatePage = true}) {
    _selectedVariant = variant;
    _selectedColor = variant['color']?.toString();
    _selectedSize = variant['size']?.toString();

    final p = (variant['price'] as num?) ?? 0;
    final reg = (variant['regular_price'] as num?) ?? p;
    _price = CatalogService.peso(p);
    _originalPrice = reg > p && reg > 0 ? CatalogService.peso(reg) : null;

    final st = (variant['stock'] as num?)?.toInt() ?? 0;
    _stock = st;
    _inStock = st > 0;
    if (_quantity > _stock && _stock > 0) {
      _quantity = _stock;
    } else if (_stock == 0) {
      _quantity = 1;
    }

    final variantImg = variant['image']?.toString();
    if (variantImg != null && variantImg.isNotEmpty && updatePage) {
      final idx = _images.indexOf(variantImg);
      if (idx >= 0 && _pageController.hasClients) {
        _pageController.animateToPage(
          idx,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeInOut,
        );
      }
    }
  }

  void _onColorSelected(String color) {
    setState(() {
      _selectedColor = color;
      // Find variant with this color and current size, or just first with this color
      final match = _variants.firstWhere(
        (v) =>
            v['color'] == color &&
            (_selectedSize == null || v['size'] == _selectedSize),
        orElse: () => _variants.firstWhere(
          (v) => v['color'] == color,
          orElse: () => _variants.first,
        ),
      );
      _selectVariant(match);
    });
  }

  void _onSizeSelected(String size) {
    setState(() {
      _selectedSize = size;
      // Find variant with this size and current color, or just first with this size
      final match = _variants.firstWhere(
        (v) =>
            v['size'] == size &&
            (_selectedColor == null || v['color'] == _selectedColor),
        orElse: () => _variants.firstWhere(
          (v) => v['size'] == size,
          orElse: () => _variants.first,
        ),
      );
      _selectVariant(match);
    });
  }

  double _parsePrice(String priceStr) {
    final cleaned = priceStr.replaceAll(RegExp(r'[^0-9.]'), '');
    return double.tryParse(cleaned) ?? 0.0;
  }

  int get _discountPercent {
    if (_originalPrice == null || _originalPrice!.isEmpty) return 0;
    final current = _parsePrice(_price);
    final reg = _parsePrice(_originalPrice!);
    if (reg > current && reg > 0) {
      return (((reg - current) / reg) * 100).round();
    }
    return 0;
  }

  double get _savingsAmount {
    if (_originalPrice == null || _originalPrice!.isEmpty) return 0.0;
    final current = _parsePrice(_price);
    final reg = _parsePrice(_originalPrice!);
    return reg > current ? (reg - current) : 0.0;
  }

  String get _variationDescription {
    if (_selectedVariant != null) {
      final vName = _selectedVariant!['name']?.toString();
      if (vName != null && vName.isNotEmpty) return vName;
    }
    final parts = <String>[];
    if (_selectedColor != null && _selectedColor!.isNotEmpty) {
      parts.add(_selectedColor!);
    }
    if (_selectedSize != null && _selectedSize!.isNotEmpty) {
      parts.add(_selectedSize!);
    }
    return parts.isNotEmpty ? parts.join(' / ') : 'Standard';
  }

  void _handleAddToCart() {
    if (widget.isGuest || !AuthService.isAuthenticated) {
      if (widget.onAddToCart != null) {
        widget.onAddToCart!();
      } else {
        GuestCartScreen.open(context);
      }
      return;
    }
    if (!_inStock) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Sorry, this variant is currently out of stock.'),
          backgroundColor: Colors.redAccent,
        ),
      );
      return;
    }

    final effectiveImage = _images.isNotEmpty
        ? _images[_currentImageIndex.clamp(0, _images.length - 1)]
        : CartzyProductImages.getImage(_name, _category);

    CartService.instance.addItem(
      name: _name,
      price: _price,
      originalPrice: _originalPrice,
      image: effectiveImage,
      category: _category,
      variation: _variationDescription,
      quantity: _quantity,
    );

    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('$_name ($_variationDescription) added to cart!'),
        backgroundColor: CartzyColors.navy,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        action: SnackBarAction(
          label: 'VIEW CART',
          textColor: CartzyColors.coral,
          onPressed: () => _navigateToCart(),
        ),
      ),
    );
  }

  void _openChatWithSeller() {
    if (!AuthService.isAuthenticated) {
      GuestAccountScreen.open(
        context,
        onLogin: () => Navigator.of(context).pushNamed('/login'),
        onSignUp: () => Navigator.of(context).pushNamed('/signup'),
      );
      return;
    }
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MessagesScreen(
          onBack: () => Navigator.of(context).pop(),
          initialProductId: widget.productId,
          initialStoreName: _sellerName,
        ),
      ),
    );
  }

  void _navigateToCart() {
    if (widget.isGuest || !AuthService.isAuthenticated) {
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

  // ==========================================
  // FULLSCREEN ZOOM VIEWER (Matching Web #pd-viewer)
  // ==========================================
  void _openFullscreenViewer(int initialIndex) {
    showDialog(
      context: context,
      barrierColor: Colors.black.withValues(alpha: 0.95),
      builder: (ctx) {
        int viewerIndex = initialIndex;
        final TransformationController transformController = TransformationController();

        return StatefulBuilder(
          builder: (context, setViewerState) {
            return Scaffold(
              backgroundColor: Colors.transparent,
              body: SafeArea(
                child: Column(
                  children: [
                    // Top Bar
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              '${viewerIndex + 1} / ${_images.length}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                              ),
                            ),
                          ),
                          const Spacer(),
                          IconButton(
                            icon: const Icon(Icons.zoom_out, color: Colors.white),
                            tooltip: 'Reset Zoom',
                            onPressed: () {
                              transformController.value = Matrix4.identity();
                            },
                          ),
                          IconButton(
                            icon: const Icon(Icons.close, color: Colors.white, size: 28),
                            tooltip: 'Close',
                            onPressed: () => Navigator.of(ctx).pop(),
                          ),
                        ],
                      ),
                    ),

                    // Stage
                    Expanded(
                      child: Stack(
                        alignment: Alignment.center,
                        children: [
                          InteractiveViewer(
                            transformationController: transformController,
                            minScale: 0.8,
                            maxScale: 4.0,
                            clipBehavior: Clip.none,
                            child: Center(
                              child: CartzyProductImage(
                                imageUrl: _images[viewerIndex],
                                width: double.infinity,
                                height: double.infinity,
                                fit: BoxFit.contain,
                                fallbackIcon: Icons.shopping_bag_outlined,
                              ),
                            ),
                          ),

                          // Left Arrow
                          if (_images.length > 1 && viewerIndex > 0)
                            Positioned(
                              left: 8,
                              child: IconButton(
                                style: IconButton.styleFrom(
                                  backgroundColor: Colors.black.withValues(alpha: 0.5),
                                ),
                                icon: const Icon(Icons.chevron_left, color: Colors.white, size: 32),
                                onPressed: () {
                                  transformController.value = Matrix4.identity();
                                  setViewerState(() => viewerIndex--);
                                  _pageController.jumpToPage(viewerIndex);
                                },
                              ),
                            ),

                          // Right Arrow
                          if (_images.length > 1 && viewerIndex < _images.length - 1)
                            Positioned(
                              right: 8,
                              child: IconButton(
                                style: IconButton.styleFrom(
                                  backgroundColor: Colors.black.withValues(alpha: 0.5),
                                ),
                                icon: const Icon(Icons.chevron_right, color: Colors.white, size: 32),
                                onPressed: () {
                                  transformController.value = Matrix4.identity();
                                  setViewerState(() => viewerIndex++);
                                  _pageController.jumpToPage(viewerIndex);
                                },
                              ),
                            ),
                        ],
                      ),
                    ),

                    // Hint
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      child: Text(
                        'Pinch to zoom · Double tap to fit · Swipe or tap arrow to change',
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.6),
                          fontSize: 11,
                        ),
                      ),
                    ),

                    // Bottom Thumbnails
                    if (_images.length > 1)
                      Container(
                        height: 64,
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Center(
                          child: SingleChildScrollView(
                            scrollDirection: Axis.horizontal,
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            child: Row(
                              children: List.generate(_images.length, (idx) {
                                final isSelected = idx == viewerIndex;
                                return GestureDetector(
                                  onTap: () {
                                    transformController.value = Matrix4.identity();
                                    setViewerState(() => viewerIndex = idx);
                                    _pageController.jumpToPage(idx);
                                  },
                                  child: Container(
                                    margin: const EdgeInsets.symmetric(horizontal: 4),
                                    width: 48,
                                    height: 48,
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(
                                        color: isSelected ? CartzyColors.coral : Colors.white24,
                                        width: isSelected ? 2.5 : 1,
                                      ),
                                    ),
                                    clipBehavior: Clip.antiAlias,
                                    child: CartzyProductImage(
                                      imageUrl: _images[idx],
                                      fit: BoxFit.cover,
                                    ),
                                  ),
                                );
                              }),
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, size: 20, color: CartzyColors.navy),
          onPressed: widget.onBack,
        ),
        title: Text(
          _name,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(
            color: CartzyColors.navy,
            fontSize: 17,
            fontWeight: FontWeight.bold,
          ),
        ),
        actions: [
          IconButton(
            icon: Icon(
              _isWishlisted ? Icons.favorite : Icons.favorite_border,
              color: _isWishlisted ? Colors.redAccent : CartzyColors.navy,
            ),
            tooltip: 'Wishlist',
            onPressed: () {
              setState(() => _isWishlisted = !_isWishlisted);
              ScaffoldMessenger.of(context).hideCurrentSnackBar();
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(_isWishlisted ? 'Added to your Wishlist!' : 'Removed from Wishlist'),
                  duration: const Duration(seconds: 1),
                ),
              );
            },
          ),
          ListenableBuilder(
            listenable: CartService.instance,
            builder: (context, _) {
              final count = CartService.instance.totalItemCount;
              return Stack(
                alignment: Alignment.center,
                children: [
                  IconButton(
                    icon: const Icon(Icons.shopping_cart_outlined, color: CartzyColors.navy),
                    tooltip: 'Cart',
                    onPressed: _navigateToCart,
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
                        constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
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
          const SizedBox(width: 6),
        ],
      ),
      body: Stack(
        children: [
          SingleChildScrollView(
            padding: const EdgeInsets.only(bottom: 110),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // ==========================================
                // 1. INTERACTIVE IMAGE GALLERY (ASPECT 1:1)
                // ==========================================
                _buildGallerySection(),

                // ==========================================
                // 2. HEADER: EYEBROW, TITLE, STATS
                // ==========================================
                _buildHeaderSection(),

                const Divider(height: 1, color: Color(0xFFEEEEEE)),

                // ==========================================
                // 3. PRICE & DISCOUNT BOX
                // ==========================================
                _buildPriceSection(),

                const SizedBox(height: 12),

                // ==========================================
                // 4. SHIPPING ESTIMATOR
                // ==========================================
                _buildShippingSection(),

                const SizedBox(height: 12),

                // ==========================================
                // 5. INTERACTIVE VARIANT SELECTORS
                // ==========================================
                if (_colors.isNotEmpty || _sizes.isNotEmpty)
                  _buildVariantSection(),

                // ==========================================
                // 6. QUANTITY STEPPER
                // ==========================================
                _buildQuantitySection(),

                const SizedBox(height: 14),

                // ==========================================
                // 7. SELLER STORE CARD
                // ==========================================
                _buildSellerCard(),

                const SizedBox(height: 14),

                // ==========================================
                // 8. AUTHENTIC GUARANTEE CALLOUT
                // ==========================================
                _buildAuthenticCallout(),

                const SizedBox(height: 14),

                // ==========================================
                // 9. SPECIFICATIONS
                // ==========================================
                _buildSpecSection(),

                const SizedBox(height: 14),

                // ==========================================
                // 10. DESCRIPTION (RICH FORMATTED)
                // ==========================================
                _buildDescriptionSection(),

                const SizedBox(height: 14),

                // ==========================================
                // 11. RATINGS & REVIEWS
                // ==========================================
                _buildReviewsSection(),

                const SizedBox(height: 24),
              ],
            ),
          ),

          // Loading overlay indicator
          if (_isLoading)
            const Positioned(
              top: 0,
              left: 0,
              right: 0,
              child: LinearProgressIndicator(
                backgroundColor: Colors.transparent,
                color: CartzyColors.coral,
                minHeight: 3,
              ),
            ),

          // ==========================================
          // 12. STICKY BOTTOM ACTION BAR
          // ==========================================
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: _buildBottomActionBar(),
          ),
        ],
      ),
    );
  }

  // ==========================================
  // GALLERY SECTION
  // ==========================================
  Widget _buildGallerySection() {
    return Container(
      color: Colors.white,
      child: Column(
        children: [
          // Main 1:1 Aspect ratio photo stage
          AspectRatio(
            aspectRatio: 1.0,
            child: Stack(
              children: [
                GestureDetector(
                  onTap: () => _openFullscreenViewer(_currentImageIndex),
                  child: PageView.builder(
                    controller: _pageController,
                    itemCount: _images.length,
                    onPageChanged: (idx) {
                      setState(() => _currentImageIndex = idx);
                    },
                    itemBuilder: (context, index) {
                      return CartzyProductImage(
                        imageUrl: _images[index],
                        width: double.infinity,
                        height: double.infinity,
                        fit: BoxFit.cover,
                        fallbackIcon: Icons.shopping_bag_outlined,
                      );
                    },
                  ),
                ),

                // Top Seller Badge (e.g. New Arrival, Limited Edition)
                if (_badge.isNotEmpty)
                  Positioned(
                    top: 14,
                    left: 14,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [CartzyColors.navy, Color(0xFF6B5B7B)],
                        ),
                        borderRadius: BorderRadius.circular(6),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.15),
                            blurRadius: 6,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Text(
                        _badge.toUpperCase(),
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 0.6,
                        ),
                      ),
                    ),
                  ),

                // Zoom tap hint button
                Positioned(
                  top: 14,
                  right: 14,
                  child: GestureDetector(
                    onTap: () => _openFullscreenViewer(_currentImageIndex),
                    child: Container(
                      padding: const EdgeInsets.all(7),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.45),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.fullscreen,
                        color: Colors.white,
                        size: 20,
                      ),
                    ),
                  ),
                ),

                // Photo index counter pill (e.g. 1 / 4)
                if (_images.length > 1)
                  Positioned(
                    bottom: 14,
                    right: 14,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.6),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        '${_currentImageIndex + 1} / ${_images.length}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          ),

          // Horizontal Thumbnail Strip (like the website!)
          if (_images.length > 1)
            Container(
              padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 14),
              color: const Color(0xFFFBFBFB),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: List.generate(_images.length, (idx) {
                    final isSelected = idx == _currentImageIndex;
                    return GestureDetector(
                      onTap: () {
                        setState(() => _currentImageIndex = idx);
                        _pageController.animateToPage(
                          idx,
                          duration: const Duration(milliseconds: 250),
                          curve: Curves.easeInOut,
                        );
                      },
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        margin: const EdgeInsets.only(right: 8),
                        width: 54,
                        height: 54,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: isSelected ? CartzyColors.coral : const Color(0xFFE2E8F0),
                            width: isSelected ? 2.5 : 1.0,
                          ),
                          boxShadow: isSelected
                              ? [
                                  BoxShadow(
                                    color: CartzyColors.coral.withValues(alpha: 0.25),
                                    blurRadius: 4,
                                    offset: const Offset(0, 2),
                                  ),
                                ]
                              : null,
                        ),
                        clipBehavior: Clip.antiAlias,
                        child: CartzyProductImage(
                          imageUrl: _images[idx],
                          fit: BoxFit.cover,
                        ),
                      ),
                    );
                  }),
                ),
              ),
            ),
        ],
      ),
    );
  }

  // ==========================================
  // HEADER SECTION
  // ==========================================
  Widget _buildHeaderSection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Eyebrow / Category
          Text(
            _category.toUpperCase(),
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.bold,
              letterSpacing: 1.0,
              color: CartzyColors.coral,
            ),
          ),
          const SizedBox(height: 6),

          // Product Name
          Text(
            _name,
            style: const TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: CartzyColors.navy,
              height: 1.3,
            ),
          ),
          const SizedBox(height: 10),

          // Rating and Sold stats (like web's pd-stats)
          Row(
            children: [
              // Rating stars
              const Icon(Icons.star_rounded, color: Color(0xFFFFA41C), size: 18),
              const SizedBox(width: 4),
              Text(
                _rating.toStringAsFixed(1),
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                  color: CartzyColors.navy,
                ),
              ),
              const SizedBox(width: 4),
              Text(
                '($_reviewsCount ${_reviewsCount == 1 ? "review" : "reviews"})',
                style: const TextStyle(fontSize: 13, color: CartzyColors.gray),
              ),

              const SizedBox(width: 10),
              const Text('·', style: TextStyle(color: Colors.grey, fontSize: 16)),
              const SizedBox(width: 10),

              // Sold count
              Text(
                _soldCount,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: CartzyColors.navy,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ==========================================
  // PRICE & DISCOUNT SECTION
  // ==========================================
  Widget _buildPriceSection() {
    final discount = _discountPercent;
    final savings = _savingsAmount;

    return Container(
      width: double.infinity,
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.baseline,
            textBaseline: TextBaseline.alphabetic,
            children: [
              Text(
                _price,
                style: const TextStyle(
                  fontSize: 26,
                  fontWeight: FontWeight.w800,
                  color: CartzyColors.coral,
                ),
              ),
              if (_originalPrice != null && _originalPrice!.isNotEmpty) ...[
                const SizedBox(width: 10),
                Text(
                  _originalPrice!,
                  style: const TextStyle(
                    fontSize: 15,
                    color: Color(0xFF94A3B8),
                    decoration: TextDecoration.lineThrough,
                  ),
                ),
                if (discount > 0) ...[
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEE2E2),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      '-$discount%',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFFDC2626),
                      ),
                    ),
                  ),
                ],
              ],
            ],
          ),

          if (savings > 0) ...[
            const SizedBox(height: 4),
            Text(
              'You save ${CatalogService.peso(savings)}',
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: Color(0xFF16A34A),
              ),
            ),
          ],

          const SizedBox(height: 8),

          // Stock state indicator
          Row(
            children: [
              Container(
                width: 8,
                height: 8,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: _inStock ? const Color(0xFF16A34A) : Colors.redAccent,
                ),
              ),
              const SizedBox(width: 6),
              Text(
                _inStock ? 'In Stock ($_stock available)' : 'Currently Out of Stock',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: _inStock ? const Color(0xFF16A34A) : Colors.redAccent,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ==========================================
  // SHIPPING SECTION (Matching Web .pd-shipping)
  // ==========================================
  Widget _buildShippingSection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(
              Icons.local_shipping_outlined,
              color: CartzyColors.navy,
              size: 20,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text.rich(
                  TextSpan(
                    children: [
                      TextSpan(
                        text: 'Ships to ',
                        style: TextStyle(fontSize: 12, color: CartzyColors.gray),
                      ),
                      TextSpan(
                        text: 'Metro Manila, Philippines',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: CartzyColors.navy,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 3),
                const Text(
                  'Shipping fee ₱50.00 · Standard Delivery (2-4 business days)',
                  style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFFDCFCE7),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    _sellerFreeShippingMin != null && _sellerFreeShippingMin! > 0
                        ? '🚚 Free shipping on orders ₱${_sellerFreeShippingMin!.toStringAsFixed(0)}+'
                        : '🚚 Free shipping on orders ₱500+',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF15803D),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ==========================================
  // VARIANT SECTION (Color & Size selectors)
  // ==========================================
  Widget _buildVariantSection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Color selector
          if (_colors.isNotEmpty) ...[
            Row(
              children: [
                Text(
                  _sizes.isEmpty ? 'Variation: ' : 'Color: ',
                  style: const TextStyle(
                    fontSize: 13,
                    color: CartzyColors.gray,
                  ),
                ),
                Text(
                  _selectedColor ?? _colors.first,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _colors.map((color) {
                final isSelected = color == _selectedColor;
                return ChoiceChip(
                  label: Text(color),
                  selected: isSelected,
                  selectedColor: CartzyColors.navy,
                  backgroundColor: const Color(0xFFF8FAFC),
                  labelStyle: TextStyle(
                    fontSize: 12,
                    fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                    color: isSelected ? Colors.white : CartzyColors.navy,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                    side: BorderSide(
                      color: isSelected ? CartzyColors.navy : const Color(0xFFCBD5E1),
                    ),
                  ),
                  showCheckmark: false,
                  onSelected: (_) => _onColorSelected(color),
                );
              }).toList(),
            ),
            const SizedBox(height: 14),
          ],

          // Size selector
          if (_sizes.isNotEmpty) ...[
            Row(
              children: [
                const Text(
                  'Size: ',
                  style: TextStyle(fontSize: 13, color: CartzyColors.gray),
                ),
                Text(
                  _selectedSize ?? _sizes.first,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _sizes.map((size) {
                final isSelected = size == _selectedSize;
                return ChoiceChip(
                  label: Text(size),
                  selected: isSelected,
                  selectedColor: CartzyColors.navy,
                  backgroundColor: const Color(0xFFF8FAFC),
                  labelStyle: TextStyle(
                    fontSize: 12,
                    fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                    color: isSelected ? Colors.white : CartzyColors.navy,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                    side: BorderSide(
                      color: isSelected ? CartzyColors.navy : const Color(0xFFCBD5E1),
                    ),
                  ),
                  showCheckmark: false,
                  onSelected: (_) => _onSizeSelected(size),
                );
              }).toList(),
            ),
          ],
        ],
      ),
    );
  }

  // ==========================================
  // QUANTITY STEPPER
  // ==========================================
  Widget _buildQuantitySection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          const Text(
            'Quantity',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: CartzyColors.navy,
            ),
          ),
          Row(
            children: [
              IconButton.outlined(
                iconSize: 18,
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
                icon: const Icon(Icons.remove),
                color: CartzyColors.navy,
                onPressed: _quantity > 1 ? () => setState(() => _quantity--) : null,
              ),
              Container(
                margin: const EdgeInsets.symmetric(horizontal: 12),
                constraints: const BoxConstraints(minWidth: 28),
                child: Text(
                  '$_quantity',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
              ),
              IconButton.outlined(
                iconSize: 18,
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(minWidth: 32, minHeight: 32),
                icon: const Icon(Icons.add),
                color: CartzyColors.navy,
                onPressed: (_inStock && _quantity < _stock)
                    ? () => setState(() => _quantity++)
                    : null,
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ==========================================
  // SELLER STORE CARD (Matching Web .pd-seller-row)
  // ==========================================
  Widget _buildSellerCard() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          // Store avatar
          Container(
            width: 46,
            height: 46,
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              shape: BoxShape.circle,
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            clipBehavior: Clip.antiAlias,
            child: _sellerLogoUrl != null && _sellerLogoUrl!.isNotEmpty
                ? Image.network(
                    _sellerLogoUrl!,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Center(
                      child: Text(
                        _sellerName.isNotEmpty ? _sellerName[0].toUpperCase() : 'S',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          color: CartzyColors.navy,
                          fontSize: 18,
                        ),
                      ),
                    ),
                  )
                : Center(
                    child: Text(
                      _sellerName.isNotEmpty ? _sellerName[0].toUpperCase() : 'S',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        color: CartzyColors.navy,
                        fontSize: 18,
                      ),
                    ),
                  ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Sold by',
                  style: TextStyle(fontSize: 11, color: CartzyColors.gray),
                ),
                Text(
                  _sellerName,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
                const SizedBox(height: 2),
                const Text(
                  'Visit store →',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: CartzyColors.coral,
                  ),
                ),
              ],
            ),
          ),
          OutlinedButton.icon(
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: CartzyColors.navy),
              foregroundColor: CartzyColors.navy,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            icon: const Icon(Icons.chat_outlined, size: 16),
            label: const Text('Chat', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
            onPressed: _openChatWithSeller,
          ),
        ],
      ),
    );
  }

  // ==========================================
  // AUTHENTIC CALLOUT
  // ==========================================
  Widget _buildAuthenticCallout() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: const Row(
        children: [
          Icon(Icons.verified_user_outlined, color: Color(0xFF16A34A), size: 26),
          SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '100% Authentic Guarantee',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  'Free 7-day returns & fast nationwide shipping',
                  style: TextStyle(fontSize: 11, color: CartzyColors.gray),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ==========================================
  // SPECIFICATIONS SECTION (Matching Web #details)
  // ==========================================
  Widget _buildSpecSection() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Specifications',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: CartzyColors.navy,
            ),
          ),
          const SizedBox(height: 12),

          _buildSpecRow('Category', _category),
          _buildSpecRow('Store', _sellerName),
          if (_gender != null && _gender!.isNotEmpty)
            _buildSpecRow('For', _gender![0].toUpperCase() + _gender!.substring(1)),
          if (_colors.isNotEmpty)
            _buildSpecRow('Available Colors', _colors.join(', ')),
          if (_sizes.isNotEmpty)
            _buildSpecRow('Available Sizes', _sizes.join(', ')),
          _buildSpecRow('Stock Status', _inStock ? '$_stock items available' : 'Out of stock'),
        ],
      ),
    );
  }

  Widget _buildSpecRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: const TextStyle(fontSize: 13, color: Color(0xFF64748B)),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: CartzyColors.navy,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ==========================================
  // DESCRIPTION SECTION (RICH EXPANDABLE)
  // ==========================================
  Widget _buildDescriptionSection() {
    final text = (_description != null && _description!.trim().isNotEmpty)
        ? _description!.trim()
        : 'Experience premium quality and exceptional reliability with this item. '
            'Features durable materials, modern design aesthetics, and tested performance '
            'for daily use.';

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Product Description',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: CartzyColors.navy,
            ),
          ),
          const SizedBox(height: 10),

          Text(
            text,
            maxLines: _isDescriptionExpanded ? null : 5,
            overflow: _isDescriptionExpanded ? TextOverflow.visible : TextOverflow.ellipsis,
            style: const TextStyle(
              fontSize: 14,
              height: 1.6,
              color: Color(0xFF334155),
            ),
          ),

          if (text.length > 200) ...[
            const SizedBox(height: 8),
            GestureDetector(
              onTap: () => setState(() => _isDescriptionExpanded = !_isDescriptionExpanded),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    _isDescriptionExpanded ? 'Show less' : 'Show more',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.coral,
                    ),
                  ),
                  Icon(
                    _isDescriptionExpanded ? Icons.keyboard_arrow_up : Icons.keyboard_arrow_down,
                    color: CartzyColors.coral,
                    size: 18,
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  // ==========================================
  // REVIEWS & RATINGS SECTION
  // ==========================================
  Widget _buildReviewsSection() {
    final total = _reviewsCount > 0 ? _reviewsCount : _reviews.length;

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  'Ratings & Reviews ($total)',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
              ),
              TextButton(
                onPressed: () {
                  WriteReviewDialog.show(
                    context,
                    productName: _name,
                    initialRating: 5.0,
                    userName: 'Buyer',
                  );
                },
                child: const Text(
                  'Write Review',
                  style: TextStyle(
                    color: CartzyColors.coral,
                    fontWeight: FontWeight.bold,
                    fontSize: 13,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Rating summary & distribution bars
          Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // Score
              Column(
                children: [
                  Text(
                    _rating.toStringAsFixed(1),
                    style: const TextStyle(
                      fontSize: 34,
                      fontWeight: FontWeight.w900,
                      color: CartzyColors.navy,
                    ),
                  ),
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: List.generate(
                      5,
                      (i) => Icon(
                        i < _rating.round() ? Icons.star_rounded : Icons.star_outline_rounded,
                        color: const Color(0xFFFFA41C),
                        size: 16,
                      ),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '$total ${total == 1 ? "rating" : "ratings"}',
                    style: const TextStyle(fontSize: 11, color: CartzyColors.gray),
                  ),
                ],
              ),
              const SizedBox(width: 24),

              // Bars
              Expanded(
                child: Column(
                  children: [5, 4, 3, 2, 1].map((star) {
                    final count = _starCounts[star] ?? 0;
                    final pct = total > 0 ? (count / total).clamp(0.0, 1.0) : 0.0;
                    return Padding(
                      padding: const EdgeInsets.symmetric(vertical: 2),
                      child: Row(
                        children: [
                          Text('$star★', style: const TextStyle(fontSize: 11, color: CartzyColors.gray)),
                          const SizedBox(width: 6),
                          Expanded(
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(4),
                              child: LinearProgressIndicator(
                                value: pct,
                                backgroundColor: const Color(0xFFF1F5F9),
                                color: const Color(0xFFFFA41C),
                                minHeight: 6,
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          SizedBox(
                            width: 20,
                            child: Text(
                              '$count',
                              textAlign: TextAlign.right,
                              style: const TextStyle(fontSize: 11, color: CartzyColors.gray),
                            ),
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),
          const Divider(height: 1, color: Color(0xFFEEEEEE)),
          const SizedBox(height: 14),

          // Real reviews from DB
          if (_reviews.isNotEmpty)
            ..._reviews.map((r) {
              final userName = r['user_name']?.toString() ?? 'Buyer';
              final userRating = (r['rating'] as num?)?.toInt() ?? 5;
              final comment = r['comment']?.toString() ?? '';
              final date = r['created_at']?.toString() ?? '';
              final reply = r['seller_reply']?.toString();

              return Padding(
                padding: const EdgeInsets.only(bottom: 16),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    CircleAvatar(
                      radius: 18,
                      backgroundColor: CartzyColors.navy.withValues(alpha: 0.1),
                      child: Text(
                        userName.isNotEmpty ? userName[0].toUpperCase() : 'B',
                        style: const TextStyle(
                          color: CartzyColors.navy,
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                userName,
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                  color: CartzyColors.navy,
                                ),
                              ),
                              Text(
                                date,
                                style: const TextStyle(fontSize: 11, color: CartzyColors.gray),
                              ),
                            ],
                          ),
                          const SizedBox(height: 2),
                          Row(
                            children: List.generate(
                              5,
                              (i) => Icon(
                                i < userRating ? Icons.star_rounded : Icons.star_outline_rounded,
                                color: const Color(0xFFFFA41C),
                                size: 14,
                              ),
                            ),
                          ),
                          if (comment.isNotEmpty) ...[
                            const SizedBox(height: 6),
                            Text(
                              comment,
                              style: const TextStyle(fontSize: 13, color: Color(0xFF334155), height: 1.4),
                            ),
                          ],
                          if (reply != null && reply.isNotEmpty) ...[
                            const SizedBox(height: 8),
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF8FAFC),
                                borderRadius: BorderRadius.circular(8),
                                border: const Border(
                                  left: BorderSide(color: CartzyColors.coral, width: 3),
                                ),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Seller Reply',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: CartzyColors.coral,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    reply,
                                    style: const TextStyle(fontSize: 12, color: Color(0xFF475569)),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),
              );
            })
          else
            const Center(
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 20),
                child: Text(
                  'No reviews yet for this product. Be the first to review!',
                  style: TextStyle(fontSize: 13, color: CartzyColors.gray),
                ),
              ),
            ),
        ],
      ),
    );
  }

  // ==========================================
  // STICKY BOTTOM ACTION BAR
  // ==========================================
  Widget _buildBottomActionBar() {
    return Container(
      padding: EdgeInsets.only(
        left: 16,
        right: 16,
        top: 10,
        bottom: MediaQuery.of(context).padding.bottom + 10,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 10,
            offset: const Offset(0, -3),
          ),
        ],
      ),
      child: Row(
        children: [
          // Chat Button
          IconButton(
            style: IconButton.styleFrom(
              backgroundColor: const Color(0xFFF1F5F9),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            icon: const Icon(Icons.chat_bubble_outline, color: CartzyColors.navy, size: 20),
            tooltip: 'Chat with Seller',
            onPressed: _openChatWithSeller,
          ),
          const SizedBox(width: 8),

          // Favorite Button
          IconButton(
            style: IconButton.styleFrom(
              backgroundColor: const Color(0xFFF1F5F9),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            icon: Icon(
              _isWishlisted ? Icons.favorite : Icons.favorite_border,
              color: _isWishlisted ? Colors.redAccent : CartzyColors.navy,
              size: 20,
            ),
            tooltip: 'Save to Wishlist',
            onPressed: () {
              setState(() => _isWishlisted = !_isWishlisted);
              ScaffoldMessenger.of(context).hideCurrentSnackBar();
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(_isWishlisted ? 'Added to your Wishlist!' : 'Removed from Wishlist'),
                  duration: const Duration(seconds: 1),
                ),
              );
            },
          ),
          const SizedBox(width: 10),

          // Large Add to Cart / Buy Button
          Expanded(
            child: SizedBox(
              height: 48,
              child: ElevatedButton.icon(
                onPressed: _inStock ? _handleAddToCart : null,
                icon: const Icon(Icons.shopping_cart_outlined, size: 20),
                label: Text(
                  _inStock
                      ? (_quantity > 1 ? 'Add to Cart ($_quantity)' : 'Add to Cart')
                      : 'Sold Out',
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: CartzyColors.coral,
                  foregroundColor: Colors.white,
                  disabledBackgroundColor: const Color(0xFFCBD5E1),
                  disabledForegroundColor: const Color(0xFF94A3B8),
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

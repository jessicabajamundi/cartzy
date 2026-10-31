import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/products/product_details_screen.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/services/wishlist_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/utils/product_images.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';
import 'package:cartzy/widgets/wishlist_empty_state.dart';

// ============================================================
// WISHLIST SCREEN — Live Database Connected
// ============================================================

class WishlistScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onDiscoverProducts;

  const WishlistScreen({
    super.key,
    required this.onBack,
    this.onDiscoverProducts,
  });

  @override
  State<WishlistScreen> createState() => _WishlistScreenState();
}

class _WishlistScreenState extends State<WishlistScreen> {
  List<WishlistItem> _items = [];
  bool _isLoading = true;
  String? _errorMessage;
  final Set<String> _removingRefs = {};

  @override
  void initState() {
    super.initState();
    _loadWishlist();
  }

  Future<void> _loadWishlist() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final items = await WishlistService.fetchWishlist();
      if (mounted) {
        setState(() {
          _items = items;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = 'Could not load your wishlist. Please try again.';
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _removeItem(WishlistItem item) async {
    if (_removingRefs.contains(item.ref)) return;

    setState(() {
      _removingRefs.add(item.ref);
    });

    try {
      await WishlistService.removeFromWishlist(item.ref);
      if (mounted) {
        setState(() {
          _items.removeWhere((i) => i.ref == item.ref);
          _removingRefs.remove(item.ref);
        });

        ScaffoldMessenger.of(context).hideCurrentSnackBar();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${item.name} removed from wishlist'),
            duration: const Duration(seconds: 2),
            behavior: SnackBarBehavior.floating,
            action: SnackBarAction(
              label: 'Undo',
              textColor: Colors.amberAccent,
              onPressed: () async {
                try {
                  await WishlistService.toggleWishlist(
                    ref: item.ref,
                    name: item.name,
                    price: item.price,
                    image: item.image,
                    variation: item.variation,
                  );
                  _loadWishlist();
                } catch (_) {}
              },
            ),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _removingRefs.remove(item.ref);
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to remove item: $e'),
            backgroundColor: Colors.redAccent,
          ),
        );
      }
    }
  }

  void _addToCart(WishlistItem item) {
    CartService.instance.addItem(
      name: item.name,
      price: item.price,
      image: item.image,
      variation: item.variation != 'Standard' ? item.variation : '',
      quantity: 1,
    );

    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Added "${item.name}" to your cart'),
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 2),
        action: SnackBarAction(
          label: 'View Cart',
          textColor: Colors.white,
          onPressed: _openCart,
        ),
      ),
    );
  }

  void _openDetails(WishlistItem item) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => ProductDetailsScreen(
          productId: item.productId,
          name: item.name,
          category: 'General',
          price: '₱${item.price.toStringAsFixed(2)}',
          imageUrl: item.image.isNotEmpty ? item.image : null,
          sellerName: item.sellerName,
          rating: item.rating,
          soldCount: item.soldCount > 0 ? '${item.soldCount} sold' : null,
          onBack: () => Navigator.of(context).pop(),
        ),
      ),
    ).then((_) {
      if (mounted) {
        _loadWishlist();
      }
    });
  }

  void _openCart() {
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        leading: BackButton(
          onPressed: widget.onBack,
          color: CartzyColors.brandDark,
        ),
        centerTitle: true,
        toolbarHeight: 76,
        backgroundColor: CartzyColors.brand50Lavender,
        elevation: 0,
        scrolledUnderElevation: 0,
        foregroundColor: CartzyColors.brandDark,
        surfaceTintColor: Colors.transparent,
        title: Column(
          children: [
            Text(
              'YOUR FAVORITE FINDS',
              style: GoogleFonts.dmSans(
                fontSize: 10,
                letterSpacing: 1.6,
                fontWeight: FontWeight.w700,
                color: CartzyColors.rose700,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              'Your wishlist',
              style: GoogleFonts.playfairDisplay(
                fontSize: 26,
                fontWeight: FontWeight.w700,
                color: CartzyColors.brand900,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Cart',
            icon: ListenableBuilder(
              listenable: CartService.instance,
              builder: (context, _) {
                final count = CartService.instance.totalItemCount;
                return Stack(
                  clipBehavior: Clip.none,
                  children: [
                    const Icon(
                      Icons.shopping_bag_outlined,
                      color: CartzyColors.brandDark,
                      size: 24,
                    ),
                    if (count > 0)
                      Positioned(
                        right: -4,
                        top: -4,
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: const BoxDecoration(
                            color: Color(0xFFEF4444),
                            shape: BoxShape.circle,
                          ),
                          constraints: const BoxConstraints(
                            minWidth: 16,
                            minHeight: 16,
                          ),
                          child: Center(
                            child: Text(
                              '$count',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 9,
                                fontWeight: FontWeight.bold,
                                height: 1,
                              ),
                            ),
                          ),
                        ),
                      ),
                  ],
                );
              },
            ),
            onPressed: _openCart,
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SafeArea(
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          color: CartzyColors.burgundy,
        ),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.error_outline_rounded,
                size: 48,
                color: Colors.redAccent,
              ),
              const SizedBox(height: 12),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: GoogleFonts.dmSans(
                  fontSize: 14,
                  color: CartzyColors.gray,
                ),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: _loadWishlist,
                icon: const Icon(Icons.refresh, size: 18),
                label: const Text('Try Again'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: CartzyColors.burgundy,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (_items.isEmpty) {
      return WishlistEmptyState(
        onPrimary: widget.onDiscoverProducts ?? widget.onBack,
      );
    }

    return RefreshIndicator(
      onRefresh: _loadWishlist,
      color: CartzyColors.burgundy,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          // Subheader with counter
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 12),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    '${_items.length} ${_items.length == 1 ? 'item' : 'items'} saved',
                    style: GoogleFonts.dmSans(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: CartzyColors.brandMuted,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: CartzyColors.brand50Lavender,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(
                          Icons.favorite_rounded,
                          size: 13,
                          color: Color(0xFFD1495B),
                        ),
                        const SizedBox(width: 4),
                        Text(
                          'Live in Cartzy',
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: CartzyColors.burgundy,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Product Grid
          SliverPadding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
            sliver: SliverGrid(
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
                childAspectRatio: 0.62,
              ),
              delegate: SliverChildBuilderDelegate(
                (context, index) {
                  return _buildWishlistCard(_items[index]);
                },
                childCount: _items.length,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildWishlistCard(WishlistItem item) {
    final isRemoving = _removingRefs.contains(item.ref);
    final effectiveImage = item.image.isNotEmpty
        ? item.image
        : CartzyProductImages.getImage(item.name);

    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => _openDetails(item),
        child: Ink(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFEDE6EA)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Photo with remove button & stock tag
              AspectRatio(
                aspectRatio: 1,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    ColoredBox(
                      color: const Color(0xFFF6F3F5),
                      child: CartzyProductImage(
                        imageUrl: effectiveImage,
                        fit: BoxFit.cover,
                      ),
                    ),

                    // Sold out overlay if not in stock
                    if (!item.inStock)
                      ColoredBox(
                        color: Colors.white.withValues(alpha: 0.45),
                      ),

                    // Stock status pill (bottom-left)
                    Positioned(
                      left: 7,
                      bottom: 7,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 7,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: item.inStock
                              ? const Color(0xFFE8F5E9)
                              : const Color(0xFF282133),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          item.inStock ? 'In Stock' : 'Sold out',
                          style: GoogleFonts.dmSans(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w700,
                            color: item.inStock
                                ? const Color(0xFF2E7D32)
                                : Colors.white,
                          ),
                        ),
                      ),
                    ),

                    // Remove / Heart button (top-right)
                    Positioned(
                      top: 7,
                      right: 7,
                      child: Semantics(
                        button: true,
                        label: 'Remove from wishlist',
                        child: GestureDetector(
                          onTap: isRemoving ? null : () => _removeItem(item),
                          child: Container(
                            width: 30,
                            height: 30,
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.94),
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.10),
                                  blurRadius: 6,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: isRemoving
                                ? const Center(
                                    child: SizedBox(
                                      width: 14,
                                      height: 14,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                        color: CartzyColors.burgundy,
                                      ),
                                    ),
                                  )
                                : const Icon(
                                    Icons.favorite_rounded,
                                    size: 16,
                                    color: Color(0xFFD1495B),
                                  ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              // Info & actions
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(10),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Product Name
                      Text(
                        item.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.dmSans(
                          fontWeight: FontWeight.w600,
                          fontSize: 12.5,
                          height: 1.25,
                          color: const Color(0xFF282133),
                        ),
                      ),

                      const SizedBox(height: 4),

                      // Seller / Variation tag
                      if (item.variation != 'Standard' && item.variation.isNotEmpty)
                        Container(
                          margin: const EdgeInsets.only(bottom: 4),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 1.5,
                          ),
                          decoration: BoxDecoration(
                            color: CartzyColors.brand50Lavender,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            item.variation,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.dmSans(
                              fontSize: 9.5,
                              fontWeight: FontWeight.w600,
                              color: CartzyColors.burgundy,
                            ),
                          ),
                        )
                      else
                        Text(
                          item.sellerName,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.dmSans(
                            fontSize: 10.5,
                            color: const Color(0xFF7D7388),
                          ),
                        ),

                      const Spacer(),

                      // Price & Add to Cart row
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          Expanded(
                            child: Text(
                              '₱${item.price.toStringAsFixed(2)}',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.dmSans(
                                fontWeight: FontWeight.w800,
                                fontSize: 13.5,
                                color: CartzyColors.burgundy,
                              ),
                            ),
                          ),
                          const SizedBox(width: 4),
                          // Small Add to cart button
                          InkWell(
                            onTap: item.inStock ? () => _addToCart(item) : null,
                            borderRadius: BorderRadius.circular(8),
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 6,
                              ),
                              decoration: BoxDecoration(
                                color: item.inStock
                                    ? CartzyColors.burgundy
                                    : Colors.grey.shade300,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Icon(
                                Icons.add_shopping_cart_rounded,
                                size: 14,
                                color: Colors.white,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';
import 'package:cartzy/utils/product_images.dart';

/// Mobile product card used by Daily Discover and the Sale / product grids:
/// - Edge-to-edge square photo with a corner discount tag, a floating
///   wishlist button and a stock tag (low stock / sold out)
/// - Name, store (with a verified mark for official stores), rating, sold
///   count and a free-shipping truck
/// - Price pinned to the bottom next to a round "+" add-to-cart button
///
/// The card fills the height it is given; see [ProductCard.bodyHeight].
class ProductCard extends StatefulWidget {
  final String name;
  final String price;
  final String? originalPrice;
  final int? discountPercent;
  final String? badge;
  final double? rating;
  final String? soldCount;
  final String? imageUrl;
  final VoidCallback onTap;
  final VoidCallback? onAddToCart;
  final VoidCallback? onFavoriteToggle;
  final bool hasFreeShipping;
  final String? sellerName;
  final bool inStock;
  final int? stock;

  const ProductCard({
    super.key,
    required this.name,
    required this.price,
    this.originalPrice,
    this.discountPercent,
    this.badge,
    this.rating,
    this.soldCount,
    this.imageUrl,
    required this.onTap,
    this.onAddToCart,
    this.onFavoriteToggle,
    this.hasFreeShipping = true,
    this.sellerName,
    this.inStock = true,
    this.stock,
  });

  /// Height of everything below the square photo. A card of width `w` needs
  /// a height of `w + bodyHeight(context)`.
  static double bodyHeight(BuildContext context) =>
      MediaQuery.textScalerOf(context).scale(132);

  @override
  State<ProductCard> createState() => _ProductCardState();
}

class _ProductCardState extends State<ProductCard> {
  static const _ink = Color(0xFF282133);
  static const _muted = Color(0xFF7D7388);
  static const _rose = Color(0xFF8C5A50);

  bool _isFavorite = false;

  @override
  Widget build(BuildContext context) {
    final cleanPrice = widget.price.startsWith('₱')
        ? widget.price.substring(1).trim()
        : widget.price.trim();
    final badge = widget.badge?.trim() ?? '';
    final isOfficial = badge.toLowerCase().contains('official');
    final seller = (widget.sellerName?.trim().isNotEmpty ?? false)
        ? widget.sellerName!.trim()
        : 'Cartzy Store';
    final lowStock = widget.inStock && widget.stock != null && widget.stock! <= 5;
    final hasDiscount =
        widget.discountPercent != null && widget.discountPercent! > 0 && widget.inStock;

    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: widget.onTap,
        child: Ink(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFEDE6EA)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // ── PHOTO ──
              AspectRatio(
                aspectRatio: 1,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    ColoredBox(
                      color: const Color(0xFFF6F3F5),
                      child: CartzyProductImage(
                        imageUrl: (widget.imageUrl?.isNotEmpty ?? false)
                            ? widget.imageUrl
                            : CartzyProductImages.getImage(widget.name),
                        fit: BoxFit.cover,
                      ),
                    ),

                    // Sold out: soften the photo.
                    if (!widget.inStock)
                      ColoredBox(color: Colors.white.withValues(alpha: 0.45)),

                    // Discount tag tucked into the top-left corner.
                    if (hasDiscount)
                      Positioned(
                        top: 0,
                        left: 0,
                        child: Container(
                          padding: const EdgeInsets.fromLTRB(9, 5, 10, 5),
                          decoration: const BoxDecoration(
                            color: _rose,
                            borderRadius: BorderRadius.only(bottomRight: Radius.circular(12)),
                          ),
                          child: Text(
                            '-${widget.discountPercent}%',
                            style: GoogleFonts.dmSans(
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                              color: Colors.white,
                              height: 1.1,
                            ),
                          ),
                        ),
                      ),

                    // Floating wishlist button.
                    Positioned(
                      top: 7,
                      right: 7,
                      child: Semantics(
                        button: true,
                        label: _isFavorite ? 'Remove from wishlist' : 'Add to wishlist',
                        child: GestureDetector(
                          onTap: () {
                            setState(() => _isFavorite = !_isFavorite);
                            widget.onFavoriteToggle?.call();
                          },
                          child: Container(
                            width: 30,
                            height: 30,
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.92),
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.10),
                                  blurRadius: 6,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Icon(
                              _isFavorite ? Icons.favorite_rounded : Icons.favorite_border_rounded,
                              size: 16,
                              color: _isFavorite ? const Color(0xFFD1495B) : _ink,
                            ),
                          ),
                        ),
                      ),
                    ),

                    // Stock tag along the bottom of the photo.
                    if (!widget.inStock || lowStock)
                      Positioned(
                        left: 7,
                        bottom: 7,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                          decoration: BoxDecoration(
                            color: widget.inStock ? const Color(0xFFFFF4E5) : _ink,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            widget.inStock ? 'Only ${widget.stock} left' : 'Sold out',
                            style: GoogleFonts.dmSans(
                              fontSize: 9.5,
                              fontWeight: FontWeight.w700,
                              color: widget.inStock ? const Color(0xFFB4651E) : Colors.white,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),

              // ── DETAILS ──
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(10, 8, 8, 9),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.dmSans(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
                          color: _ink,
                          height: 1.25,
                        ),
                      ),
                      const SizedBox(height: 4),

                      // Store
                      Row(
                        children: [
                          Icon(
                            isOfficial ? Icons.verified_rounded : Icons.storefront_outlined,
                            size: 12,
                            color: isOfficial ? const Color(0xFF3B82F6) : _muted,
                          ),
                          const SizedBox(width: 3),
                          Expanded(
                            child: Text(
                              seller,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.dmSans(fontSize: 10.5, color: _muted),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),

                      // Rating · sold · free shipping
                      Row(
                        children: [
                          const Icon(Icons.star_rounded, size: 13, color: Color(0xFFF2A93B)),
                          const SizedBox(width: 2),
                          Text(
                            (widget.rating ?? 4.8).toStringAsFixed(1),
                            style: GoogleFonts.dmSans(
                              fontSize: 10.5,
                              fontWeight: FontWeight.w700,
                              color: _ink,
                            ),
                          ),
                          Expanded(
                            child: Text(
                              '  ·  ${widget.soldCount ?? '0 sold'}',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.dmSans(fontSize: 10.5, color: _muted),
                            ),
                          ),
                          if (widget.hasFreeShipping)
                            const Tooltip(
                              message: 'Free shipping',
                              child: Icon(
                                Icons.local_shipping_outlined,
                                size: 14,
                                color: Color(0xFF2F8F6B),
                              ),
                            ),
                        ],
                      ),
                      const Spacer(),

                      // Price + add to cart
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                if (widget.originalPrice != null)
                                  Text(
                                    widget.originalPrice!,
                                    maxLines: 1,
                                    style: GoogleFonts.dmSans(
                                      fontSize: 10,
                                      color: const Color(0xFFA59BAD),
                                      decoration: TextDecoration.lineThrough,
                                    ),
                                  ),
                                FittedBox(
                                  fit: BoxFit.scaleDown,
                                  alignment: Alignment.centerLeft,
                                  child: Text.rich(
                                    TextSpan(children: [
                                      TextSpan(
                                        text: '₱',
                                        style: GoogleFonts.dmSans(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w700,
                                        ),
                                      ),
                                      TextSpan(text: cleanPrice),
                                    ]),
                                    style: GoogleFonts.dmSans(
                                      fontSize: 16.5,
                                      fontWeight: FontWeight.w800,
                                      color: hasDiscount ? _rose : _ink,
                                      letterSpacing: -0.3,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 6),
                          _AddButton(
                            enabled: widget.inStock,
                            onTap: widget.onAddToCart ?? widget.onTap,
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

class _AddButton extends StatelessWidget {
  final bool enabled;
  final VoidCallback onTap;

  const _AddButton({required this.enabled, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      enabled: enabled,
      label: enabled ? 'Add to cart' : 'Sold out',
      child: GestureDetector(
        onTap: enabled ? onTap : null,
        child: Container(
          width: 32,
          height: 32,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: enabled ? null : const Color(0xFFEDEAF0),
            gradient: enabled
                ? const LinearGradient(
                    colors: [Color(0xFF564B68), Color(0xFF8C5A50)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  )
                : null,
            boxShadow: enabled
                ? [
                    BoxShadow(
                      color: const Color(0xFF564B68).withValues(alpha: 0.28),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ]
                : null,
          ),
          child: Icon(
            enabled ? Icons.add_rounded : Icons.remove_shopping_cart_outlined,
            size: enabled ? 20 : 15,
            color: enabled ? Colors.white : const Color(0xFFA59BAD),
          ),
        ),
      ),
    );
  }
}

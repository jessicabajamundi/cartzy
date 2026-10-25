import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';
import 'package:cartzy/utils/product_images.dart';

/// Compact, mobile-tailored product card matching the website design:
/// - Rose-gold border (#B76E79) & rounded 12px mobile frame
/// - Square 1:1 photo with dark "Official" / gold check and rose discount pill
/// - Store name, 2-line title, and Free Shipping tag
/// - Pinned pricing section with ₱ symbol and strikethrough was-price
/// - Rating star + sold count, and live stock dot indicator
/// - Discovery wishlist circle button & multi-stop gradient "Add to Cart" button
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

  @override
  State<ProductCard> createState() => _ProductCardState();
}

class _ProductCardState extends State<ProductCard> {
  bool _isFavorite = false;

  @override
  Widget build(BuildContext context) {
    final cleanPrice = widget.price.startsWith('₱')
        ? widget.price.substring(1).trim()
        : widget.price.trim();

    final sellerDisplay = (widget.sellerName != null && widget.sellerName!.isNotEmpty)
        ? widget.sellerName!.toUpperCase()
        : 'OFFICIAL STORE';

    final String? badgeText = (widget.badge != null && widget.badge!.trim().isNotEmpty)
        ? widget.badge!.trim()
        : null;

    final isOfficial = badgeText != null && badgeText.toLowerCase().contains('official');
    final effectiveBadge = badgeText;

    // Stock indicator colors matching web: green (in stock), amber (low stock <= 5), gray (out of stock)
    final Color stockColor;
    final String stockText;
    if (!widget.inStock) {
      stockColor = const Color(0xFF9A90A3);
      stockText = 'Out of stock';
    } else if (widget.stock != null && widget.stock! <= 5) {
      stockColor = const Color(0xFFB4651E);
      stockText = 'Only ${widget.stock} left';
    } else {
      stockColor = const Color(0xFF3F7A55);
      stockText = widget.stock != null ? '${widget.stock} in stock' : 'In stock';
    }

    return GestureDetector(
      onTap: widget.onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            // ── SQUARE MEDIA AREA (discovery-media) ──
            Padding(
              padding: const EdgeInsets.fromLTRB(5, 5, 5, 0),
              child: AspectRatio(
                aspectRatio: 1.05,
                child: Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFFAF9FB),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: Stack(
                    children: [
                      // Product Image
                      Positioned.fill(
                        child: Opacity(
                          opacity: widget.inStock ? 1.0 : 0.75,
                          child: CartzyProductImage(
                            imageUrl: (widget.imageUrl != null && widget.imageUrl!.isNotEmpty)
                                ? widget.imageUrl
                                : CartzyProductImages.getImage(widget.name),
                            fit: BoxFit.cover,
                          ),
                        ),
                      ),

                      // Top-Left: Seller Badge ("Official" dark capsule with gold check)
                      if (effectiveBadge != null && effectiveBadge.isNotEmpty)
                        Positioned(
                          top: 5,
                          left: 5,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFF282133), // brand-darkest
                              borderRadius: BorderRadius.circular(999),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.15),
                                  blurRadius: 3,
                                  offset: const Offset(0, 1),
                                ),
                              ],
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                if (isOfficial) ...[
                                  Container(
                                    width: 9,
                                    height: 9,
                                    decoration: const BoxDecoration(
                                      color: Color(0xFFC9A86A), // gold check
                                      shape: BoxShape.circle,
                                    ),
                                    child: const Center(
                                      child: Icon(
                                        Icons.check,
                                        size: 6.5,
                                        color: Color(0xFF282133),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 3),
                                ],
                                Text(
                                  effectiveBadge,
                                  style: GoogleFonts.dmSans(
                                    fontSize: 8.5,
                                    fontWeight: FontWeight.w700,
                                    color: Colors.white,
                                    height: 1.1,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),

                      // Top-Right: Discount Pill ("-20%" rose capsule)
                      if (widget.discountPercent != null &&
                          widget.discountPercent! > 0 &&
                          widget.inStock)
                        Positioned(
                          top: 5,
                          right: 5,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFF8C5A50), // accent-dark / discovery-rose
                              borderRadius: BorderRadius.circular(999),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFF8C5A50).withValues(alpha: 0.22),
                                  blurRadius: 3,
                                  offset: const Offset(0, 1),
                                ),
                              ],
                            ),
                            child: Text(
                              '-${widget.discountPercent}%',
                              style: GoogleFonts.dmSans(
                                fontSize: 8.5,
                                fontWeight: FontWeight.w700,
                                color: Colors.white,
                                height: 1.1,
                              ),
                            ),
                          ),
                        ),

                      // Bottom-Left: Out of Stock Pill
                      if (!widget.inStock)
                        Positioned(
                          bottom: 5,
                          left: 5,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFF282133).withValues(alpha: 0.9),
                              borderRadius: BorderRadius.circular(3),
                            ),
                            child: Text(
                              'Out of Stock',
                              style: GoogleFonts.dmSans(
                                fontSize: 8,
                                fontWeight: FontWeight.w700,
                                color: Colors.white,
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ),

            // ── BODY DETAILS (discovery-body) ──
            Padding(
              padding: const EdgeInsets.fromLTRB(7, 5, 7, 6),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Store Link (Uppercase brand, e.g. "OFFICIAL STORE")
                  Text(
                    sellerDisplay,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.dmSans(
                      fontSize: 8.5,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF6F6382), // discovery-muted
                      letterSpacing: 0.3,
                    ),
                  ),
                  const SizedBox(height: 1),

                  // Product Title (2 lines max, line-clamp-2)
                  Text(
                    widget.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.dmSans(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF282133), // discovery-ink
                      height: 1.2,
                      letterSpacing: -0.15,
                    ),
                  ),
                  const SizedBox(height: 2.5),

                  // Free Shipping Tag (discovery-tag)
                  if (widget.hasFreeShipping)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFF5F3), // accent-soft
                        borderRadius: BorderRadius.circular(3),
                        border: Border.all(color: const Color(0xFFF5CFC7), width: 0.8),
                      ),
                      child: Text(
                        'Free Shipping',
                        style: GoogleFonts.dmSans(
                          fontSize: 8.5,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF8C5A50), // discovery-rose
                        ),
                      ),
                    )
                  else
                    const SizedBox(height: 2.5),

                  const SizedBox(height: 3),

                  // Top Divider before pricing
                  const Divider(color: Color(0xFFE1DDE7), height: 1, thickness: 0.8),
                  const SizedBox(height: 3),

                  // Price Row (discovery-price)
                  FittedBox(
                    fit: BoxFit.scaleDown,
                    alignment: Alignment.centerLeft,
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.baseline,
                      textBaseline: TextBaseline.alphabetic,
                      children: [
                        Text(
                          '₱',
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF8C5A50), // discovery-currency
                          ),
                        ),
                        const SizedBox(width: 1),
                        Text(
                          cleanPrice,
                          style: GoogleFonts.dmSans(
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                            color: const Color(0xFF282133), // discovery-price-now
                            letterSpacing: -0.3,
                          ),
                        ),
                        if (widget.originalPrice != null) ...[
                          const SizedBox(width: 4),
                          Text(
                            widget.originalPrice!,
                            style: GoogleFonts.dmSans(
                              fontSize: 9.5,
                              color: const Color(0xFF9A90A3), // discovery-price-was
                              decoration: TextDecoration.lineThrough,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 3),

                  // Rating & Sold Row (discovery-rating)
                  Row(
                    children: [
                      const Icon(Icons.star_rounded, size: 11.5, color: Color(0xFFD9A13B)), // amber star
                      const SizedBox(width: 2),
                      Text(
                        (widget.rating ?? 4.8).toStringAsFixed(1),
                        style: GoogleFonts.dmSans(
                          fontSize: 9.5,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF564B68), // discovery-score
                        ),
                      ),
                      const SizedBox(width: 4),
                      Container(
                        width: 2.5,
                        height: 2.5,
                        decoration: const BoxDecoration(
                          color: Color(0xFFC9C1CF), // discovery-dot
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 4),
                      Flexible(
                        child: Text(
                          widget.soldCount ?? '0 sold',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.dmSans(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w500,
                            color: const Color(0xFF6F6382), // discovery-muted
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 2.5),

                  // Stock Status Row (discovery-stock)
                  Row(
                    children: [
                      Container(
                        width: 5.5,
                        height: 5.5,
                        decoration: BoxDecoration(
                          color: stockColor,
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: stockColor.withValues(alpha: 0.22),
                              spreadRadius: 1.5,
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        stockText,
                        style: GoogleFonts.dmSans(
                          fontSize: 9.5,
                          fontWeight: FontWeight.w700,
                          color: stockColor,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 5),

                  // ── ACTIONS ROW: Wishlist Circle + Add to Cart Button (discovery-actions) ──
                  Row(
                    children: [
                      // Wishlist Circle Button (discovery-wishlist)
                      GestureDetector(
                        onTap: () {
                          setState(() => _isFavorite = !_isFavorite);
                          widget.onFavoriteToggle?.call();
                        },
                        child: Container(
                          width: 26,
                          height: 26,
                          decoration: BoxDecoration(
                            color: _isFavorite ? const Color(0xFFFFF5F3) : Colors.white,
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: _isFavorite
                                  ? const Color(0xFFC08B7F)
                                  : const Color(0xFFD4A69A),
                              width: 1,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF302938).withValues(alpha: 0.06),
                                blurRadius: 3,
                                offset: const Offset(0, 1),
                              ),
                            ],
                          ),
                          child: Center(
                            child: Icon(
                              _isFavorite ? Icons.favorite : Icons.favorite_border_rounded,
                              size: 13,
                              color: const Color(0xFF8C5A50), // discovery-rose
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 5),

                      // Add to Cart Button with web 4-stop gradient
                      Expanded(
                        child: SizedBox(
                          height: 26,
                          child: widget.inStock
                              ? InkWell(
                                  onTap: widget.onAddToCart ?? widget.onTap,
                                  borderRadius: BorderRadius.circular(8),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 4),
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(
                                        color: const Color(0xFFD4A69A), // accent-primary
                                        width: 0.8,
                                      ),
                                      gradient: const LinearGradient(
                                        colors: [
                                          Color(0xFF564B68), // brand-dark
                                          Color(0xFF6F6382), // brand-deep
                                          Color(0xFF8C5A50), // accent-dark
                                          Color(0xFF4A2520), // accent-darkest
                                        ],
                                        begin: Alignment.topLeft,
                                        end: Alignment.bottomRight,
                                      ),
                                      boxShadow: [
                                        BoxShadow(
                                          color: const Color(0xFF6F6382).withValues(alpha: 0.2),
                                          blurRadius: 4,
                                          offset: const Offset(0, 1.5),
                                        ),
                                      ],
                                    ),
                                    child: FittedBox(
                                      fit: BoxFit.scaleDown,
                                      child: Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          const Icon(
                                            Icons.shopping_bag_outlined,
                                            size: 11.5,
                                            color: Colors.white,
                                          ),
                                          const SizedBox(width: 3),
                                          Text(
                                            'Add to Cart',
                                            style: GoogleFonts.dmSans(
                                              fontSize: 10,
                                              fontWeight: FontWeight.w700,
                                              color: Colors.white,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                )
                              : Container(
                                  decoration: BoxDecoration(
                                    borderRadius: BorderRadius.circular(8),
                                    color: const Color(0xFFF3F4F6),
                                    border: Border.all(
                                      color: const Color(0xFFE5E7EB),
                                      width: 0.8,
                                    ),
                                  ),
                                  alignment: Alignment.center,
                                  child: Text(
                                    'Out of Stock',
                                    style: GoogleFonts.dmSans(
                                      fontSize: 9.5,
                                      fontWeight: FontWeight.w700,
                                      color: const Color(0xFF9CA3AF),
                                    ),
                                  ),
                                ),
                        ),
                      ),
                    ],
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

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';
import 'package:cartzy/utils/product_images.dart';

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

    return GestureDetector(
      onTap: widget.onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFEADBD9), width: 1.2),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.03),
              blurRadius: 10,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── TOP IMAGE AREA ──
            Expanded(
              child: Stack(
                children: [
                  ClipRRect(
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(15)),
                    child: SizedBox(
                      width: double.infinity,
                      height: double.infinity,
                      child: CartzyProductImage(
                        imageUrl: (widget.imageUrl != null && widget.imageUrl!.isNotEmpty)
                            ? widget.imageUrl
                            : CartzyProductImages.getImage(widget.name),
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),

                  // Top Left: Badge ("Official", "Preferred")
                  if (widget.badge != null && widget.badge!.isNotEmpty)
                    Positioned(
                      top: 8,
                      left: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.95),
                          borderRadius: BorderRadius.circular(5),
                          border: Border.all(color: const Color(0xFFEADBD9), width: 1),
                        ),
                        child: Text(
                          widget.badge!,
                          style: GoogleFonts.dmSans(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF6E565B),
                          ),
                        ),
                      ),
                    ),

                  // Top Right: Discount Pill ("-35%")
                  if (widget.discountPercent != null && widget.discountPercent! > 0)
                    Positioned(
                      top: 8,
                      right: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFCEEEF),
                          borderRadius: BorderRadius.circular(5),
                          border: Border.all(color: const Color(0xFFF5D6D8), width: 1),
                        ),
                        child: Text(
                          '-${widget.discountPercent}%',
                          style: GoogleFonts.dmSans(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFFA35C62),
                          ),
                        ),
                      ),
                    ),

                  // Bottom Right: Floating Heart Action
                  Positioned(
                    bottom: 8,
                    right: 8,
                    child: GestureDetector(
                      onTap: () {
                        setState(() {
                          _isFavorite = !_isFavorite;
                        });
                        widget.onFavoriteToggle?.call();
                      },
                      child: Container(
                        width: 32,
                        height: 32,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          shape: BoxShape.circle,
                          border: Border.all(color: const Color(0xFFEADBD9), width: 1),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.08),
                              blurRadius: 4,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Center(
                          child: Icon(
                            _isFavorite ? Icons.favorite : Icons.favorite_border_rounded,
                            size: 16,
                            color: _isFavorite ? const Color(0xFFB85D5D) : const Color(0xFF9E6464),
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // ── BOTTOM PRODUCT DETAILS ──
            Padding(
              padding: const EdgeInsets.fromLTRB(10, 8, 10, 10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Product Title (2 lines max)
                  Text(
                    widget.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      fontWeight: FontWeight.w500,
                      color: const Color(0xFF231F20),
                      height: 1.3,
                    ),
                  ),

                  // Free Shipping Badge
                  if (widget.hasFreeShipping)
                    Container(
                      margin: const EdgeInsets.only(top: 4, bottom: 6),
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFAF1EE),
                        borderRadius: BorderRadius.circular(4),
                        border: Border.all(color: const Color(0xFFECDED9), width: 1),
                      ),
                      child: Text(
                        'Free Shipping',
                        style: GoogleFonts.dmSans(
                          fontSize: 9,
                          fontWeight: FontWeight.w500,
                          color: const Color(0xFFA66E65),
                        ),
                      ),
                    )
                  else
                    const SizedBox(height: 6),

                  // Price: Currency symbol + Bold amount
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.baseline,
                    textBaseline: TextBaseline.alphabetic,
                    children: [
                      Text(
                        '₱ ',
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF6E565B),
                        ),
                      ),
                      Text(
                        cleanPrice,
                        style: GoogleFonts.dmSans(
                          fontSize: 16,
                          fontWeight: FontWeight.w800,
                          color: const Color(0xFF231F20),
                        ),
                      ),
                      if (widget.originalPrice != null) ...[
                        const SizedBox(width: 5),
                        Text(
                          widget.originalPrice!,
                          style: GoogleFonts.dmSans(
                            fontSize: 10,
                            color: CartzyColors.gray,
                            decoration: TextDecoration.lineThrough,
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 4),

                  // Rating & Sold Count
                  Row(
                    children: [
                      const Icon(Icons.star, size: 11, color: Color(0xFFE5A93C)),
                      const SizedBox(width: 3),
                      Text(
                        (widget.rating ?? 4.8).toStringAsFixed(1),
                        style: GoogleFonts.dmSans(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF444444),
                        ),
                      ),
                      const Spacer(),
                      Text(
                        widget.soldCount ?? '1.2k sold',
                        style: GoogleFonts.dmSans(
                          fontSize: 10.5,
                          color: const Color(0xFF888888),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),

                  // Add to Cart Button (Dark dusty plum / aubergine)
                  SizedBox(
                    width: double.infinity,
                    height: 32,
                    child: ElevatedButton(
                      onPressed: widget.onAddToCart ?? widget.onTap,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF685363),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: EdgeInsets.zero,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.shopping_cart_outlined, size: 13, color: Colors.white),
                          const SizedBox(width: 5),
                          Text(
                            'Add to Cart',
                            style: GoogleFonts.dmSans(
                              fontSize: 11.5,
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
          ],
        ),
      ),
    );
  }
}

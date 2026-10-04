import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';
import 'package:cartzy/utils/product_images.dart';

class ProductCard extends StatelessWidget {
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
  });

  @override
  Widget build(BuildContext context) {
    final isOfficial = badge == 'Official';

    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: CartzyColors.surface,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 10,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Stack(
                children: [
                  SizedBox(
                    width: double.infinity,
                    height: double.infinity,
                    child: CartzyProductImage(
                      imageUrl: (imageUrl != null && imageUrl!.isNotEmpty)
                          ? imageUrl
                          : CartzyProductImages.getImage(name),
                      fit: BoxFit.cover,
                      borderRadius: const BorderRadius.vertical(top: Radius.circular(14)),
                    ),
                  ),
                  if (badge != null && badge!.isNotEmpty)
                    Positioned(
                      top: 8,
                      left: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                        decoration: BoxDecoration(
                          color: isOfficial ? CartzyColors.navy : CartzyColors.gold,
                          borderRadius: BorderRadius.circular(5),
                        ),
                        child: Text(
                          badge!,
                          style: TextStyle(
                            fontSize: 9,
                            fontWeight: FontWeight.bold,
                            color: isOfficial ? Colors.white : CartzyColors.navy,
                          ),
                        ),
                      ),
                    ),
                  if (discountPercent != null && discountPercent! > 0)
                    Positioned(
                      top: 8,
                      right: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                        decoration: BoxDecoration(
                          color: CartzyColors.coral,
                          borderRadius: BorderRadius.circular(5),
                        ),
                        child: Text(
                          '-$discountPercent%',
                          style: const TextStyle(
                            fontSize: 9,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 12,
                      color: CartzyColors.text,
                      height: 1.3,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Text(
                                  price,
                                  style: const TextStyle(
                                    fontSize: 14,
                                    fontWeight: FontWeight.bold,
                                    color: CartzyColors.navy,
                                  ),
                                ),
                                if (originalPrice != null) ...[
                                  const SizedBox(width: 6),
                                  Text(
                                    originalPrice!,
                                    style: const TextStyle(
                                      fontSize: 11,
                                      color: CartzyColors.gray,
                                      decoration: TextDecoration.lineThrough,
                                    ),
                                  ),
                                ],
                              ],
                            ),
                            if (rating != null || soldCount != null) ...[
                              const SizedBox(height: 6),
                              Row(
                                children: [
                                  if (rating != null) ...[
                                    const Icon(Icons.star, size: 12, color: CartzyColors.gold),
                                    const SizedBox(width: 3),
                                    Text(
                                      rating!.toStringAsFixed(1),
                                      style: const TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w600,
                                        color: CartzyColors.text,
                                      ),
                                    ),
                                    const SizedBox(width: 6),
                                  ],
                                  if (soldCount != null)
                                    Text(
                                      soldCount!,
                                      style: const TextStyle(
                                        fontSize: 11,
                                        color: CartzyColors.gray,
                                      ),
                                    ),
                                ],
                              ),
                            ],
                          ],
                        ),
                      ),
                      if (onAddToCart != null)
                        InkWell(
                          onTap: onAddToCart,
                          borderRadius: BorderRadius.circular(8),
                          child: Container(
                            padding: const EdgeInsets.all(6),
                            decoration: BoxDecoration(
                              color: CartzyColors.coral.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(
                              Icons.add_shopping_cart,
                              color: CartzyColors.coral,
                              size: 16,
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

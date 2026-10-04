import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/services/review_service.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/widgets/write_review_dialog.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';
import 'package:cartzy/utils/product_images.dart';

class ProductDetailsScreen extends StatelessWidget {
  final String name;
  final String category;
  final String price;
  final String? originalPrice;
  final String? imageUrl;
  final VoidCallback onBack;
  final VoidCallback? onAddToCart;
  final bool isGuest;

  const ProductDetailsScreen({
    super.key,
    required this.name,
    required this.category,
    required this.price,
    this.originalPrice,
    this.imageUrl,
    required this.onBack,
    this.onAddToCart,
    this.isGuest = false,
  });

  void _handleAddToCart(BuildContext context) {
    final effectiveImage = (imageUrl != null && imageUrl!.isNotEmpty)
        ? imageUrl!
        : CartzyProductImages.getImage(name, category);

    if (onAddToCart != null) {
      onAddToCart!();
    } else {
      CartService.instance.addItem(
        name: name,
        price: price,
        originalPrice: originalPrice,
        category: category,
        image: effectiveImage,
      );

      ScaffoldMessenger.of(context).hideCurrentSnackBar();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('$name added to cart!'),
          backgroundColor: CartzyColors.navy,
          duration: const Duration(seconds: 3),
          action: SnackBarAction(
            label: 'VIEW CART',
            textColor: CartzyColors.coral,
            onPressed: () {
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
            },
          ),
        ),
      );
    }
  }

  void _navigateToCart(BuildContext context) {
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
        backgroundColor: CartzyColors.surface,
        elevation: 0,

        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back,
            color: CartzyColors.navy,
          ),
          onPressed: onBack,
        ),

        title: const Text(
          'Product Details',
          style: TextStyle(
            color: CartzyColors.navy,
            fontWeight: FontWeight.bold,
          ),
        ),

        actions: [
          ListenableBuilder(
            listenable: CartService.instance,
            builder: (context, _) {
              final count = CartService.instance.totalItemCount;
              return Stack(
                alignment: Alignment.center,
                children: [
                  IconButton(
                    icon: const Icon(
                      Icons.shopping_cart_outlined,
                      color: CartzyColors.navy,
                    ),
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
                        constraints: const BoxConstraints(
                          minWidth: 18,
                          minHeight: 18,
                        ),
                        child: Text(
                          count > 99 ? '99+' : '',
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

      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),

        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,

          children: [

            // ==================================
            // PRODUCT IMAGE
            // ==================================

            Container(
              width: double.infinity,
              height: 280,
              decoration: BoxDecoration(
                color: CartzyColors.surface,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.05),
                    blurRadius: 12,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: CartzyProductImage(
                imageUrl: (imageUrl != null && imageUrl!.isNotEmpty)
                    ? imageUrl
                    : CartzyProductImages.getImage(name, category),
                width: double.infinity,
                height: 280,
                fit: BoxFit.cover,
                borderRadius: BorderRadius.circular(20),
                fallbackIcon: Icons.shopping_bag_outlined,
              ),
            ),

            const SizedBox(height: 22),

            // ==================================
            // CATEGORY BADGE
            // ==================================

            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: CartzyColors.coral.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(6),
              ),
              child: Text(
                category.toUpperCase(),
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: CartzyColors.coral,
                  letterSpacing: 0.5,
                ),
              ),
            ),

            const SizedBox(height: 10),

            // ==================================
            // PRODUCT NAME
            // ==================================

            Text(
              name,
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
                height: 1.3,
              ),
            ),

            const SizedBox(height: 12),

            // ==================================
            // PRICE ROW
            // ==================================

            Row(
              crossAxisAlignment: CrossAxisAlignment.baseline,
              textBaseline: TextBaseline.alphabetic,
              children: [
                Text(
                  price,
                  style: const TextStyle(
                    fontSize: 26,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.coral,
                  ),
                ),
                if (originalPrice != null && originalPrice!.isNotEmpty) ...[
                  const SizedBox(width: 10),
                  Text(
                    originalPrice!,
                    style: const TextStyle(
                      fontSize: 15,
                      color: CartzyColors.gray,
                      decoration: TextDecoration.lineThrough,
                    ),
                  ),
                ],
              ],
            ),

            const SizedBox(height: 22),

            // ==================================
            // DESCRIPTION
            // ==================================

            const Text(
              'Product Details',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),

            const SizedBox(height: 8),

            const Text(
              'Experience premium quality and exceptional reliability with this item. '
              'Features durable materials, modern design aesthetics, and tested performance '
              'for daily use.',
              style: TextStyle(
                fontSize: 14,
                height: 1.5,
                color: CartzyColors.gray,
              ),
            ),

            const SizedBox(height: 25),

            // ==================================
            // ADD TO CART BUTTON
            // ==================================

            SizedBox(
              width: double.infinity,
              height: 52,

              child: ElevatedButton.icon(
                onPressed: () => _handleAddToCart(context),

                icon: const Icon(
                  Icons.shopping_cart,
                ),

                label: const Text(
                  'Add to Cart',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),

                style: ElevatedButton.styleFrom(
                  backgroundColor: CartzyColors.coral,
                  foregroundColor: Colors.white,
                  elevation: 0,

                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
              ),
            ),

            const SizedBox(height: 18),

            // ==================================
            // NOTICE / GUARANTEE
            // ==================================

            if (isGuest)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: CartzyColors.surface,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: CartzyColors.border,
                    width: 1.2,
                  ),
                ),
                child: const Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.info_outline,
                      color: CartzyColors.navy,
                    ),
                    SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        'You are currently browsing as a guest. '
                        'Items added to cart will be available during your session.',
                        style: TextStyle(
                          fontSize: 12,
                          color: CartzyColors.gray,
                          height: 1.4,
                        ),
                      ),
                    ),
                  ],
                ),
              )
            else
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: CartzyColors.surface,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: CartzyColors.border,
                    width: 1.2,
                  ),
                ),
                child: const Row(
                  children: [
                    Icon(
                      Icons.verified_outlined,
                      color: Colors.green,
                      size: 26,
                    ),
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
                            style: TextStyle(
                              fontSize: 11,
                              color: CartzyColors.gray,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

            const SizedBox(height: 24),

            // ==================================
            // RATINGS & REVIEWS SECTION
            // ==================================

            ListenableBuilder(
              listenable: ReviewService.instance,
              builder: (context, _) {
                final reviews = ReviewService.instance.getReviewsForProduct(name);
                final avgRating = ReviewService.instance.getAverageRating(name);

                return Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: CartzyColors.surface,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: CartzyColors.border),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Ratings & Reviews',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: CartzyColors.navy,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Row(
                                children: [
                                  const Icon(Icons.star, color: CartzyColors.gold, size: 18),
                                  const SizedBox(width: 4),
                                  Text(
                                    '$avgRating / 5.0',
                                    style: const TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.bold,
                                      color: CartzyColors.navy,
                                    ),
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    '(${reviews.length} reviews)',
                                    style: const TextStyle(
                                      fontSize: 12,
                                      color: CartzyColors.gray,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                          OutlinedButton.icon(
                            onPressed: () {
                              WriteReviewDialog.show(
                                context,
                                productName: name,
                              );
                            },
                            icon: const Icon(Icons.rate_review_outlined, size: 16),
                            label: const Text('Add Review'),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: CartzyColors.coral,
                              side: const BorderSide(color: CartzyColors.coral),
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(10),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                      const Divider(height: 1, color: CartzyColors.border),
                      const SizedBox(height: 12),
                      ...reviews.map((r) => _buildReviewItem(r)),
                    ],
                  ),
                );
              },
            ),

            const SizedBox(height: 25),
          ],
        ),
      ),
    );
  }

  Widget _buildReviewItem(ProductReview r) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 14,
                backgroundColor: CartzyColors.coral.withValues(alpha: 0.15),
                child: Text(
                  r.displayName.isEmpty ? 'B' : r.displayName.substring(0, 1).toUpperCase(),
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.coral,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  r.displayName,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.navy,
                  ),
                ),
              ),
              Row(
                children: List.generate(5, (idx) {
                  return Icon(
                    idx < r.rating.round() ? Icons.star : Icons.star_border,
                    size: 13,
                    color: CartzyColors.gold,
                  );
                }),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            r.comment,
            style: const TextStyle(
              fontSize: 12,
              color: CartzyColors.text,
              height: 1.4,
            ),
          ),
          if (r.tags.isNotEmpty) ...[
            const SizedBox(height: 6),
            Wrap(
              spacing: 6,
              runSpacing: 4,
              children: r.tags.map((tag) {
                return Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: CartzyColors.background,
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: CartzyColors.border),
                  ),
                  child: Text(
                    tag,
                    style: const TextStyle(fontSize: 10, color: CartzyColors.gray),
                  ),
                );
              }).toList(),
            ),
          ],
          const SizedBox(height: 10),
          const Divider(height: 1, color: CartzyColors.border),
        ],
      ),
    );
  }
}

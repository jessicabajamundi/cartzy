import 'package:flutter/material.dart';

import 'cartzy_colors.dart';

class ProductDetailsScreen extends StatelessWidget {
  final String name;
  final String category;
  final String price;
  final VoidCallback onBack;
  final VoidCallback onAddToCart;

  const ProductDetailsScreen({
    super.key,
    required this.name,
    required this.category,
    required this.price,
    required this.onBack,
    required this.onAddToCart,
  });

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
              height: 300,

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

              alignment: Alignment.center,

              child: const Icon(
                Icons.shopping_bag_outlined,
                size: 64,
                color: CartzyColors.coral,
              ),
            ),

            const SizedBox(height: 25),

            // ==================================
            // CATEGORY
            // ==================================

            Text(
              category,
              style: const TextStyle(
                fontSize: 13,
                color: CartzyColors.gray,
              ),
            ),

            const SizedBox(height: 8),

            // ==================================
            // PRODUCT NAME
            // ==================================

            Text(
              name,
              style: const TextStyle(
                fontSize: 27,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),

            const SizedBox(height: 12),

            // ==================================
            // PRICE
            // ==================================

            Text(
              price,
              style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: CartzyColors.coral,
              ),
            ),

            const SizedBox(height: 25),

            // ==================================
            // DESCRIPTION
            // ==================================

            const Text(
              'Product Description',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),

            const SizedBox(height: 8),

            const Text(
              'This is a sample product description. '
              'More product information, specifications, '
              'reviews, and other details will be added '
              'when the product database is connected.',
              style: TextStyle(
                fontSize: 14,
                height: 1.5,
                color: CartzyColors.gray,
              ),
            ),

            const SizedBox(height: 30),

            // ==================================
            // ADD TO CART
            // ==================================

            SizedBox(
              width: double.infinity,
              height: 54,

              child: ElevatedButton.icon(
                onPressed: onAddToCart,

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

            const SizedBox(height: 20),

            // ==================================
            // LOGIN NOTICE
            // ==================================

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
                      'You will need to log in or create an '
                      'account before adding this product '
                      'to your cart.',
                      style: TextStyle(
                        fontSize: 12,
                        color: CartzyColors.gray,
                        height: 1.4,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 30),
          ],
        ),
      ),
    );
  }
}
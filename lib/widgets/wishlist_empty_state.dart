import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/theme/cartzy_button_styles.dart';

/// Shared wishlist presentation for guests and buyers without saved items.
class WishlistEmptyState extends StatelessWidget {
  final bool isGuest;
  final VoidCallback onPrimary;
  final VoidCallback? onSignUp;

  const WishlistEmptyState({
    super.key,
    this.isGuest = false,
    required this.onPrimary,
    this.onSignUp,
  });

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) => SingleChildScrollView(
        child: ConstrainedBox(
          constraints: BoxConstraints(minHeight: constraints.maxHeight),
          child: Center(
            child: Container(
              constraints: const BoxConstraints(maxWidth: 440),
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Center(
                    child: SizedBox(
                      width: 170,
                      height: 150,
                      child: Stack(
                        alignment: Alignment.center,
                        children: [
                          Container(
                            width: 144,
                            height: 144,
                            decoration: const BoxDecoration(
                              shape: BoxShape.circle,
                              gradient: LinearGradient(
                                colors: [
                                  CartzyColors.brand100,
                                  CartzyColors.rose50Warm
                                ],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                            ),
                          ),
                          Transform.rotate(
                            angle: -0.16,
                            child: Container(
                              width: 90,
                              height: 108,
                              decoration: BoxDecoration(
                                color: CartzyColors.brand200,
                                borderRadius: BorderRadius.circular(22),
                              ),
                            ),
                          ),
                          Transform.rotate(
                            angle: 0.12,
                            child: Container(
                              width: 90,
                              height: 108,
                              margin: const EdgeInsets.only(left: 18),
                              decoration: BoxDecoration(
                                color: CartzyColors.rose50Warm,
                                borderRadius: BorderRadius.circular(22),
                                border: Border.all(color: CartzyColors.rose200),
                                boxShadow: [
                                  BoxShadow(
                                    color: CartzyColors.brandDark
                                        .withValues(alpha: 0.08),
                                    blurRadius: 18,
                                    offset: const Offset(0, 8),
                                  ),
                                ],
                              ),
                              child: const Icon(Icons.favorite_rounded,
                                  size: 40, color: CartzyColors.rose500),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                  Text(
                    'YOUR LITTLE COLLECTION',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.dmSans(
                      fontSize: 10,
                      letterSpacing: 2,
                      fontWeight: FontWeight.w700,
                      color: CartzyColors.rose700,
                    ),
                  ),
                  const SizedBox(height: 10),
                  Text(
                    isGuest ? 'Keep what you love' : 'Make room for favorites',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.playfairDisplay(
                      fontSize: 28,
                      height: 1.2,
                      fontWeight: FontWeight.w700,
                      color: CartzyColors.brand900,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    isGuest
                        ? 'Log in to save your favorite finds. A little wishlist, all your own.'
                        : 'Your wishlist is waiting. Tap the heart on a product to save it for later.',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.dmSans(
                      fontSize: 14,
                      height: 1.6,
                      color: CartzyColors.brandDeep,
                    ),
                  ),
                  const SizedBox(height: 28),
                  ElevatedButton(
                    style: CartzyButtonStyles.primary.copyWith(
                      minimumSize: const WidgetStatePropertyAll(Size(0, 50)),
                      shape: const WidgetStatePropertyAll(StadiumBorder()),
                    ),
                    onPressed: onPrimary,
                    child: Text(isGuest ? 'Log In' : 'Discover products'),
                  ),
                  if (isGuest) ...[
                    const SizedBox(height: 12),
                    OutlinedButton(
                      style: CartzyButtonStyles.outline.copyWith(
                        foregroundColor:
                            const WidgetStatePropertyAll(CartzyColors.rose700),
                        backgroundColor: const WidgetStatePropertyAll(
                            CartzyColors.rose50Warm),
                        side: const WidgetStatePropertyAll(
                            BorderSide(color: CartzyColors.rose200)),
                        minimumSize: const WidgetStatePropertyAll(Size(0, 50)),
                        shape: const WidgetStatePropertyAll(StadiumBorder()),
                      ),
                      onPressed: onSignUp,
                      child: const Text('Create Account'),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/wishlist_empty_state.dart';

class GuestWishlistScreen extends StatelessWidget {
  final VoidCallback onLogin;
  final VoidCallback onSignUp;

  const GuestWishlistScreen({
    super.key,
    required this.onLogin,
    required this.onSignUp,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        centerTitle: true,
        toolbarHeight: 76,
        backgroundColor: CartzyColors.brand50Lavender,
        elevation: 0,
        scrolledUnderElevation: 0,
        foregroundColor: CartzyColors.brandDark,
        surfaceTintColor: Colors.transparent,
        title: Column(
          children: [
            Text('YOUR FAVORITE FINDS',
                style: GoogleFonts.dmSans(
                  fontSize: 10,
                  letterSpacing: 1.6,
                  fontWeight: FontWeight.w700,
                  color: CartzyColors.rose700,
                )),
            const SizedBox(height: 4),
            Text('Your wishlist',
                style: GoogleFonts.playfairDisplay(
                  fontSize: 26,
                  fontWeight: FontWeight.w700,
                  color: CartzyColors.brand900,
                )),
          ],
        ),
      ),
      body: SafeArea(
        child: WishlistEmptyState(
          isGuest: true,
          onPrimary: onLogin,
          onSignUp: onSignUp,
        ),
      ),
    );
  }
}

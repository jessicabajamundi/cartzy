import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/theme/cartzy_button_styles.dart';
import 'package:cartzy/guest/guest_wishlist_screen.dart';
import 'package:cartzy/products/product_list_screen.dart';

class GuestCartScreen extends StatelessWidget {
  static void open(BuildContext context) {
    final navigator = Navigator.of(context);
    void goTo(String route) =>
        navigator.pushNamedAndRemoveUntil(route, (_) => false);
    navigator.push(MaterialPageRoute(
      builder: (_) => GuestCartScreen(
        onLogin: () => goTo('/login'),
        onSignUp: () => goTo('/signup'),
        onHome: () => goTo('/guest'),
        onWishlist: () => navigator.pushReplacement(MaterialPageRoute(
          builder: (_) => GuestWishlistScreen(
            onLogin: () => goTo('/login'),
            onSignUp: () => goTo('/signup'),
          ),
        )),
      ),
    ));
  }

  final VoidCallback onLogin;
  final VoidCallback onSignUp;
  final VoidCallback onHome;
  final VoidCallback onWishlist;

  const GuestCartScreen({
    super.key,
    required this.onLogin,
    required this.onSignUp,
    required this.onHome,
    required this.onWishlist,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      extendBody: true,
      backgroundColor: CartzyColors.rose50Warm,
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
            Text('A LITTLE SOMETHING FOR YOU',
                style: GoogleFonts.dmSans(
                  fontSize: 10,
                  letterSpacing: 1.6,
                  fontWeight: FontWeight.w700,
                  color: CartzyColors.rose700,
                )),
            const SizedBox(height: 4),
            Text('Your cart',
                style: GoogleFonts.playfairDisplay(
                  fontSize: 26,
                  fontWeight: FontWeight.w700,
                  color: CartzyColors.brand900,
                )),
          ],
        ),
      ),
      body: DecoratedBox(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [CartzyColors.brand50Lavender, CartzyColors.rose50Warm],
          ),
        ),
        child: SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) => SingleChildScrollView(
              child: ConstrainedBox(
                constraints: BoxConstraints(minHeight: constraints.maxHeight),
                child: Center(
                  child: Container(
                    constraints: const BoxConstraints(maxWidth: 440),
                    padding: const EdgeInsets.symmetric(
                        horizontal: 28, vertical: 28),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        const Center(child: _CartIllustration()),
                        const SizedBox(height: 24),
                        Text('GOOD FINDS START HERE',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.dmSans(
                              fontSize: 10,
                              letterSpacing: 1.8,
                              fontWeight: FontWeight.w700,
                              color: CartzyColors.rose700,
                            )),
                        const SizedBox(height: 10),
                        Text('Your next favorite awaits',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.playfairDisplay(
                              fontSize: 28,
                              height: 1.2,
                              fontWeight: FontWeight.w700,
                              color: CartzyColors.brand900,
                            )),
                        const SizedBox(height: 12),
                        Text(
                            'Make it yours. Log in to add your favorite finds to your cart and check out.',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.dmSans(
                              fontSize: 14,
                              height: 1.6,
                              color: CartzyColors.brandDeep,
                            )),
                        const SizedBox(height: 28),
                        ElevatedButton.icon(
                          style: CartzyButtonStyles.primary.copyWith(
                            minimumSize:
                                const WidgetStatePropertyAll(Size(0, 50)),
                            shape:
                                const WidgetStatePropertyAll(StadiumBorder()),
                          ),
                          onPressed: onLogin,
                          icon: const Icon(Icons.login_rounded, size: 18),
                          label: const Text('Log In'),
                        ),
                        const SizedBox(height: 12),
                        OutlinedButton(
                          style: CartzyButtonStyles.outline.copyWith(
                            foregroundColor: const WidgetStatePropertyAll(
                                CartzyColors.rose700),
                            backgroundColor: const WidgetStatePropertyAll(
                                CartzyColors.rose50Warm),
                            side: const WidgetStatePropertyAll(
                                BorderSide(color: CartzyColors.rose200)),
                            minimumSize:
                                const WidgetStatePropertyAll(Size(0, 50)),
                            shape:
                                const WidgetStatePropertyAll(StadiumBorder()),
                          ),
                          onPressed: onSignUp,
                          child: const Text('Create Account'),
                        ),
                        const SizedBox(height: 12),
                        TextButton(
                          onPressed: onHome,
                          style: TextButton.styleFrom(
                            foregroundColor: CartzyColors.brandDeep,
                            minimumSize: const Size(0, 48),
                          ),
                          child: const Text('Keep exploring'),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
      bottomNavigationBar: SafeArea(
        top: false,
        child: Container(
          margin: const EdgeInsets.fromLTRB(16, 8, 16, 12),
          decoration: const ShapeDecoration(
            color: CartzyColors.surface,
            shape: StadiumBorder(
              side: BorderSide(color: CartzyColors.border),
            ),
            shadows: [
              BoxShadow(
                color: Color(0x148B3A3A),
                blurRadius: 16,
                offset: Offset(0, 4),
              ),
            ],
          ),
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
          child: Row(
            children: [
              _BottomNavItem(
                icon: Icons.home_rounded,
                label: 'Home',
                selected: false,
                onClick: onHome,
              ),
              _BottomNavItem(
                icon: Icons.shopping_cart_outlined,
                label: 'Cart',
                selected: true,
                onClick: () {},
              ),
              _BottomNavItem(
                icon: Icons.local_offer_outlined,
                label: 'Sale',
                selected: false,
                isSale: true,
                onClick: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const ProductListScreen(
                        initialCategory: 'Flash Deals',
                        title: 'Sale & Flash Deals',
                      ),
                    ),
                  );
                },
              ),
              _BottomNavItem(
                icon: Icons.person_outline_rounded,
                label: 'Profile',
                selected: false,
                onClick: onLogin,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _CartIllustration extends StatelessWidget {
  const _CartIllustration();

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 180,
      height: 156,
      child: ExcludeSemantics(
        child: Stack(
          alignment: Alignment.center,
          children: [
            Container(
              width: 148,
              height: 148,
              decoration: const BoxDecoration(
                shape: BoxShape.circle,
                color: CartzyColors.brand100,
              ),
            ),
            Transform.rotate(
              angle: -0.12,
              child: Container(
                width: 106,
                height: 112,
                decoration: BoxDecoration(
                  color: CartzyColors.brand200,
                  borderRadius: BorderRadius.circular(28),
                ),
              ),
            ),
            Container(
              width: 106,
              height: 112,
              margin: const EdgeInsets.only(left: 16, top: 8),
              decoration: BoxDecoration(
                color: CartzyColors.rose50Warm,
                borderRadius: BorderRadius.circular(28),
                border: Border.all(color: CartzyColors.rose200),
                boxShadow: [
                  BoxShadow(
                    color: CartzyColors.brandDark.withValues(alpha: 0.1),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: const Icon(Icons.shopping_cart_outlined,
                  size: 46, color: CartzyColors.rose700),
            ),
            Positioned(
              top: 12,
              right: 18,
              child: Container(
                padding: const EdgeInsets.all(10),
                decoration: const BoxDecoration(
                  color: CartzyColors.rose500,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.favorite_rounded,
                    size: 18, color: CartzyColors.rose50),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _BottomNavItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final bool isSale;
  final VoidCallback onClick;

  const _BottomNavItem({
    required this.icon,
    required this.label,
    required this.selected,
    this.isSale = false,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    final color = isSale
        ? CartzyColors.burgundy
        : selected
            ? CartzyColors.burgundy
            : CartzyColors.gray;

    return Expanded(
      child: InkWell(
        onTap: onClick,
        borderRadius: BorderRadius.circular(8),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, color: color, size: 22),
              const SizedBox(height: 3),
              Text(
                label,
                style: GoogleFonts.dmSans(
                  fontSize: 11,
                  color: color,
                  fontWeight:
                      selected || isSale ? FontWeight.w600 : FontWeight.w400,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 2),
              AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                height: 2,
                width: selected ? 16 : 0,
                decoration: BoxDecoration(
                  color: CartzyColors.burgundy,
                  borderRadius: BorderRadius.circular(1),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

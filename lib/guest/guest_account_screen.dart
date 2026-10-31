import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/theme/cartzy_button_styles.dart';

class GuestAccountScreen extends StatelessWidget {
  const GuestAccountScreen({
    super.key,
    required this.onLogin,
    required this.onSignUp,
  });

  final VoidCallback onLogin;
  final VoidCallback onSignUp;

  static void open(
    BuildContext context, {
    required VoidCallback onLogin,
    required VoidCallback onSignUp,
  }) {
    final navigator = Navigator.of(context);
    navigator.push(MaterialPageRoute<void>(
      builder: (_) => GuestAccountScreen(
        onLogin: () {
          navigator.pop();
          onLogin();
        },
        onSignUp: () {
          navigator.pop();
          onSignUp();
        },
      ),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.rose50Warm,
      appBar: AppBar(
        centerTitle: true,
        toolbarHeight: 76,
        backgroundColor: CartzyColors.brand50Lavender,
        elevation: 0,
        scrolledUnderElevation: 0,
        foregroundColor: CartzyColors.brandDark,
        surfaceTintColor: Colors.transparent,
        leading: const BackButton(color: CartzyColors.brandDark),
        title: Column(
          children: [
            Text(
              'YOUR CARTZY',
              style: GoogleFonts.dmSans(
                fontSize: 10,
                letterSpacing: 1.6,
                fontWeight: FontWeight.w700,
                color: CartzyColors.rose700,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              'Account',
              style: GoogleFonts.playfairDisplay(
                fontSize: 26,
                fontWeight: FontWeight.w700,
                color: CartzyColors.brand900,
              ),
            ),
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
                        const Center(child: _AccountIllustration()),
                        const SizedBox(height: 24),
                        Text(
                          'A LITTLE JOY IN EVERY FIND',
                          textAlign: TextAlign.center,
                          style: GoogleFonts.dmSans(
                            fontSize: 10,
                            letterSpacing: 1.8,
                            fontWeight: FontWeight.w700,
                            color: CartzyColors.rose700,
                          ),
                        ),
                        const SizedBox(height: 10),
                        Text(
                          'Welcome to Cartzy',
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
                          'Sign in to track your orders, save your favorites, and manage your Cartzy account.',
                          textAlign: TextAlign.center,
                          style: GoogleFonts.dmSans(
                            fontSize: 14,
                            height: 1.6,
                            color: CartzyColors.brandDeep,
                          ),
                        ),
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
                          onPressed: () => Navigator.of(context).pop(),
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
    );
  }
}

class _AccountIllustration extends StatelessWidget {
  const _AccountIllustration();

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
              child: const Icon(
                Icons.person_outline_rounded,
                size: 46,
                color: CartzyColors.rose700,
              ),
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
                child: const Icon(
                  Icons.auto_awesome_rounded,
                  size: 18,
                  color: CartzyColors.rose50,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/cartzy_logo.dart';


class WelcomeScreen extends StatelessWidget {
  final VoidCallback onGetStarted;

  const WelcomeScreen({super.key, required this.onGetStarted});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.elevatedOf(context, light: CartzyColors.background),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const Spacer(),

              // ==================================
              // CARTZY LOGO
              // ==================================
              const CartzyLogo(height: 110, width: 300),
              const SizedBox(height: 16),

              // ==================================
              // MAIN MESSAGE
              // ==================================
              RichText(
                textAlign: TextAlign.center,
                text: TextSpan(
                  style: const TextStyle(fontSize: 25),
                  children: [
                    TextSpan(
                      text: 'Find Everything You ',
                      style: TextStyle(
                        color: CartzyColors.textOf(context, light: CartzyColors.brandDarkest),
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    TextSpan(
                      text: 'Love',
                      style: TextStyle(
                        color: CartzyColors.mutedOf(context, light: CartzyColors.brandDeep),
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 10),

              Text(
                'Top quality products at best prices.',
                style: TextStyle(fontSize: 14, color: CartzyColors.textOf(context, light: CartzyColors.brandDarkest)),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 4),

              Text(
                'Shop easy, live better',
                style: TextStyle(fontSize: 14, color: CartzyColors.mutedOf(context, light: CartzyColors.brandDeep)),
                textAlign: TextAlign.center,
              ),

              const Spacer(),

              // ==================================
              // GET STARTED
              // ==================================
              SizedBox(
                width: 250,
                height: 54,
                child: ElevatedButton(
                  onPressed: onGetStarted,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CartzyColors.brandDark,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                  child: const Text(
                    'Get Started',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
              const SizedBox(height: 10),

              Text(
                'Your shopping journey starts here',
                style: TextStyle(fontSize: 12, color: CartzyColors.mutedOf(context, light: CartzyColors.brandDeep)),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }
}

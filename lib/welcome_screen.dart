import 'package:flutter/material.dart';

// ==========================================
// LOCAL COLORS (specific to this screen in the Kotlin original —
// swap these for your shared CartzyColors.navy/coral/gray if you'd
// rather match the Coral Horizon palette used elsewhere)
// ==========================================

class _WelcomeColors {
  static const teal = Color(0xFF2A9D8F);
  static const peach = Color(0xFFF4A261);
  static const text = Color(0xFF1F2933);
}

class WelcomeScreen extends StatelessWidget {
  final VoidCallback onGetStarted;

  const WelcomeScreen({super.key, required this.onGetStarted});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
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
              Image.asset(
                'assets/cartzy_splash.png',
                width: 170,
                height: 170,
                fit: BoxFit.contain,
              ),
              const SizedBox(height: 16),

              // ==================================
              // MAIN MESSAGE
              // ==================================
              RichText(
                textAlign: TextAlign.center,
                text: const TextSpan(
                  style: TextStyle(fontSize: 25),
                  children: [
                    TextSpan(
                      text: 'Find Everything You ',
                      style: TextStyle(
                        color: _WelcomeColors.text,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    TextSpan(
                      text: 'Love',
                      style: TextStyle(
                        color: _WelcomeColors.teal,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 10),

              const Text(
                'Top quality products at best prices.',
                style: TextStyle(fontSize: 14, color: _WelcomeColors.text),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 4),

              const Text(
                'Shop easy, live better',
                style: TextStyle(fontSize: 14, color: Colors.grey),
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
                    backgroundColor: _WelcomeColors.teal,
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

              const Text(
                'Your shopping journey starts here',
                style: TextStyle(fontSize: 12, color: _WelcomeColors.peach),
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
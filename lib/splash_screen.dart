import 'package:flutter/material.dart';

import 'cartzy_colors.dart';
import 'guest_home_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _navigateToGuestHome();
  }

  Future<void> _navigateToGuestHome() async {
    await Future.delayed(const Duration(seconds: 2));

    if (!mounted) return;

    Navigator.of(context).pushReplacement(
      MaterialPageRoute(
        builder: (context) => GuestHomeScreen(
          onLogin: () {
            // Navigation to login is handled inside GuestHomeScreen's
            // parent (CartzyApp router) if you're using that pattern —
            // see note below.
          },
          onSignUp: () {},
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: Center(
        child: Image.asset(
          'assets/cartzy_splash.png',
          width: 170,
          height: 170,
          fit: BoxFit.contain,
        ),
      ),
    );
  }
}
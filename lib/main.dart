import 'package:flutter/material.dart';

import 'guest_home_screen.dart';
import 'login_screen.dart';
import 'buyer_registration.dart';
import 'dashboard.dart';
import 'buyer_profile_screen.dart';
import 'cartzy_colors.dart';

void main() {
  runApp(const CartzyMaterialApp());
}

class CartzyMaterialApp extends StatelessWidget {
  const CartzyMaterialApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Cartzy',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        scaffoldBackgroundColor: CartzyColors.background,
      ),
      home: const CartzyApp(),
    );
  }
}

class CartzyApp extends StatefulWidget {
  const CartzyApp({super.key});

  @override
  State<CartzyApp> createState() => _CartzyAppState();
}

class _CartzyAppState extends State<CartzyApp> {
  String currentScreen = 'splash';

  @override
  void initState() {
    super.initState();
    _showSplashThenGuest();
  }

  Future<void> _showSplashThenGuest() async {
    await Future.delayed(const Duration(seconds: 2));
    if (!mounted) return;
    setState(() => currentScreen = 'guest');
  }

  void goTo(String screen) {
    setState(() {
      currentScreen = screen;
    });
  }

  @override
  Widget build(BuildContext context) {
    switch (currentScreen) {
      // --------------------------------------------------------
      // SPLASH
      // --------------------------------------------------------
      case 'splash':
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

      // --------------------------------------------------------
      // GUEST HOME
      // --------------------------------------------------------
      case 'guest':
        return GuestHomeScreen(
          onLogin: () => goTo('login'),
          onSignUp: () => goTo('registration'),
        );

      // --------------------------------------------------------
      // LOGIN
      // --------------------------------------------------------
      case 'login':
        return LoginScreen(
          onLogin: () => goTo('dashboard'),
          onSignUp: () => goTo('registration'),
          onBackToGuest: () => goTo('guest'),
        );

      // --------------------------------------------------------
      // BUYER REGISTRATION
      // --------------------------------------------------------
      case 'registration':
        return BuyerRegistration(
          onRegistrationSubmitted: () => goTo('login'),
        );

      // --------------------------------------------------------
      // DASHBOARD
      // --------------------------------------------------------
      case 'dashboard':
        return DashboardScreen(
          onProfileClick: () => goTo('profile'),
          onCartClick: () {
            // Cart screen — connect later
          },
        );

      // --------------------------------------------------------
      // BUYER PROFILE
      // --------------------------------------------------------
      case 'profile':
        return BuyerProfileScreen(
          onBack: () => goTo('dashboard'),
          onLogout: () => goTo('guest'),
        );

      default:
        return const Scaffold(
          body: Center(child: Text('Cartzy screen not found')),
        );
    }
  }
}
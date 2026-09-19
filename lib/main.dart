import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'cartzy_colors.dart';
import 'splash_screen.dart';
import 'guest_home_screen.dart';
import 'login_screen.dart';
import 'dashboard.dart';
import 'rider_dashboard.dart';
import 'buyer_profile_screen.dart';
import 'account_menu_screen.dart';
import 'my_addresses_screen.dart';
import 'add_address_screen.dart';

void main() {
  runApp(const CartzyApp());
}

class CartzyApp extends StatelessWidget {
  const CartzyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Cartzy',
      theme: ThemeData(
        useMaterial3: true,
        scaffoldBackgroundColor: CartzyColors.background,
        fontFamily: GoogleFonts.poppins().fontFamily,
        textTheme: GoogleFonts.poppinsTextTheme(
          ThemeData.light().textTheme,
        ),
        colorScheme: ColorScheme.fromSeed(
          seedColor: CartzyColors.navy,
          brightness: Brightness.light,
        ),
      ),
      home: const CartzyHome(),
    );
  }
}

class CartzyHome extends StatefulWidget {
  const CartzyHome({super.key});

  @override
  State<CartzyHome> createState() => _CartzyHomeState();
}

class _CartzyHomeState extends State<CartzyHome> {
  String currentRoute = 'splash';

  // Currently logged-in user
  Map<String, dynamic>? loggedInUser;

  // ------------------------------------------------------------
  // NAVIGATION
  // ------------------------------------------------------------

  void goTo(String route) {
    setState(() {
      currentRoute = route;
    });
  }

  // ------------------------------------------------------------
  // LOGOUT
  // ------------------------------------------------------------

  void logout() {
    setState(() {
      loggedInUser = null;
    });

    goTo('guest');
  }

  // ------------------------------------------------------------
  // ROUTES
  // ------------------------------------------------------------

  Widget buildRoute() {
    switch (currentRoute) {
      // ==========================================================
      // SPLASH
      // ==========================================================

      case 'splash':
        return SplashScreen(
          onFinished: () {
            goTo('guest');
          },
        );

      // ==========================================================
      // GUEST HOME
      // ==========================================================

      case 'guest':
        return GuestHomeScreen(
          onLogin: () {
            goTo('login');
          },
          onSignUp: () {
            goTo('login');
          },
        );

      // ==========================================================
      // LOGIN
      // ==========================================================

      case 'login':
        return LoginScreen(
          onLogin: (accountType, user) {
            setState(() {
              loggedInUser = Map<String, dynamic>.from(user);
            });

            if (accountType == AccountType.rider) {
              goTo('riderDashboard');
            } else {
              goTo('dashboard');
            }
          },

          onSignUp: (accountType) {
            goTo('guest');
          },

          onBackToGuest: () {
            goTo('guest');
          },
        );

      // ==========================================================
      // BUYER DASHBOARD
      // ==========================================================

      case 'dashboard':
        return DashboardScreen(
          onProfileClick: () {
            goTo('profile');
          },
        );

      // ==========================================================
      // RIDER DASHBOARD
      // ==========================================================

      case 'riderDashboard':
        return RiderDashboard(
          onLogout: logout,
        );

      // ==========================================================
      // MY PROFILE
      // ==========================================================

      case 'profile':
        return BuyerProfileScreen(
          userName:
              loggedInUser?['name']?.toString() ?? 'Buyer',

          userEmail:
              loggedInUser?['email']?.toString() ?? '',

          onBack: () {
            goTo('dashboard');
          },

          onLogout: logout,

          // Pencil / Edit button
          onEditProfile: () {
            goTo('accountMenu');
          },
        );

      // ==========================================================
      // ACCOUNT MENU
      // ==========================================================

      case 'accountMenu':
        return AccountMenuScreen(
          userId: int.tryParse(
                loggedInUser?['id']?.toString() ?? '',
              ) ??
              0,

          userName:
              loggedInUser?['name']?.toString() ?? 'Buyer',

          userEmail:
              loggedInUser?['email']?.toString() ?? '',

          // Profile update callback
          onProfileUpdated: (updatedUser) {
            setState(() {
              loggedInUser = {
                ...(loggedInUser ?? {}),
                ...updatedUser,
              };
            });
          },

          onBack: () {
            goTo('profile');
          },

          onMyPurchase: () {
            goTo('profile');
          },

          onLogout: logout,

          // Addresses
          onAddresses: () {
            goTo('addresses');
          },
        );

      // ==========================================================
      // MY ADDRESSES
      // ==========================================================

      case 'addresses':
        return MyAddressesScreen(
          onBack: () {
            goTo('accountMenu');
          },

          onAddAddress: () {
            goTo('addAddress');
          },
        );

      // ==========================================================
      // ADD NEW ADDRESS
      // ==========================================================

      case 'addAddress':
        return AddAddressScreen(
          onBack: () {
            goTo('addresses');
          },

          onSaved: () {
            goTo('addresses');
          },
        );

      // ==========================================================
      // DEFAULT
      // ==========================================================

      default:
        return GuestHomeScreen(
          onLogin: () {
            goTo('login');
          },
          onSignUp: () {
            goTo('login');
          },
        );
    }
  }

  // ------------------------------------------------------------
  // BUILD
  // ------------------------------------------------------------

  @override
  Widget build(BuildContext context) {
    return buildRoute();
  }
}
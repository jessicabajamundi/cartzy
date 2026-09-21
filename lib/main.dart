import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'cartzy_colors.dart';
import 'splash_screen.dart';
import 'guest_home_screen.dart';
import 'account_type_selection_screen.dart';
import 'login_screen.dart';
import 'dashboard.dart';
import 'rider_dashboard.dart';
import 'logistics_dashboard.dart';
import 'buyer_profile_screen.dart';
import 'account_menu_screen.dart';
import 'my_addresses_screen.dart';
import 'add_address_screen.dart';
import 'buyer_registration.dart';
import 'rider_registration.dart';
import 'logistics_registration.dart';

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

  Map<String, dynamic>? loggedInUser;
  String? adminToken;
  AccountType pendingAccountType = AccountType.buyer;

  // ============================================================
  // ROUTING
  // ============================================================

  void goTo(String route) {
    setState(() {
      currentRoute = route;
    });
  }

  // ============================================================
  // LOGOUT
  // ============================================================

  void logout() {
    setState(() {
      loggedInUser = null;
      adminToken = null;
    });

    goTo('guest');
  }

  // ============================================================
  // ACCOUNT TYPE SELECTED
  // ============================================================

  void handleAccountTypeSelected(AccountType accountType) {
    setState(() {
      pendingAccountType = accountType;
    });
    goTo('login');
  }

  // ============================================================
  // LOGIN
  // ============================================================

  void handleLogin(
    AccountType accountType,
    Map<String, dynamic> user, {
    String? token,
  }) {
    setState(() {
      loggedInUser = Map<String, dynamic>.from(user);
      adminToken = accountType == AccountType.admin ? token : null;
    });

    if (accountType == AccountType.rider) {
      goTo('riderDashboard');
    } else if (accountType == AccountType.admin) {
      goTo('adminDashboard');
    } else {
      goTo('dashboard');
    }
  }

  // ============================================================
  // REGISTRATION SUCCESS
  // ============================================================
  //
  // Buyer registration now hands back the REAL created account
  // (id, name, email, etc.) from the backend, so we log the
  // person straight into their own dashboard — not a leftover
  // or hardcoded account.

  void handleBuyerRegistrationSuccess(Map<String, dynamic> user) {
    setState(() {
      loggedInUser = Map<String, dynamic>.from(user);
    });
    goTo('dashboard');
  }

  // ============================================================
  // ROUTES
  // ============================================================

  Widget buildRoute() {
    switch (currentRoute) {
      // ========================================================
      // SPLASH
      // ========================================================

      case 'splash':
        return SplashScreen(
          onFinished: () {
            goTo('guest');
          },
        );

      // ========================================================
      // GUEST
      // ========================================================

      case 'guest':
        return GuestHomeScreen(
          onLogin: () {
            goTo('accountTypeSelect');
          },
          onSignUp: () {
            goTo('accountTypeSelect');
          },
        );

      // ========================================================
      // ACCOUNT TYPE SELECTION (Buyer / Rider / Logistics)
      // ========================================================

      case 'accountTypeSelect':
        return AccountTypeSelectionScreen(
          onSelected: handleAccountTypeSelected,
          onBackToGuest: () {
            goTo('guest');
          },
        );

      // ========================================================
      // LOGIN
      // ========================================================

      case 'login':
        return LoginScreen(
          initialAccountType: pendingAccountType,
          onLogin: handleLogin,
          onSignUp: (accountType) {
            if (accountType == AccountType.buyer) {
              goTo('buyerRegistration');
            } else if (accountType == AccountType.rider) {
              goTo('riderRegistration');
            } else {
              goTo('logisticsRegistration');
            }
          },
          onBack: () {
            goTo('accountTypeSelect');
          },
          onBackToGuest: () {
            goTo('guest');
          },
        );

      // ========================================================
      // BUYER REGISTRATION
      // ========================================================

      case 'buyerRegistration':
        return BuyerRegistration(
          onRegistrationSubmitted: handleBuyerRegistrationSuccess,
          onBackToLogin: () {
            goTo('login');
          },
        );

      // ========================================================
      // RIDER REGISTRATION
      // ========================================================
      //
      // Rider accounts wait for logistics/admin approval, so this
      // just sends them back to the login screen (already set to
      // Rider) after they submit, rather than logging them in.

      case 'riderRegistration':
        return RiderRegistration(
          onRegistrationSubmitted: () {
            setState(() {
              pendingAccountType = AccountType.rider;
            });
            goTo('login');
          },
          onBackToLogin: () {
            goTo('login');
          },
        );

      // ========================================================
      // LOGISTICS / SORTING CENTER REGISTRATION
      // ========================================================
      //
      // Same "pending approval" pattern as riders: they submit,
      // then wait for an existing admin to approve them from the
      // logistics dashboard before they can log in.

      case 'logisticsRegistration':
        return LogisticsRegistration(
          onRegistrationSubmitted: () {
            setState(() {
              pendingAccountType = AccountType.admin;
            });
            goTo('login');
          },
          onBackToLogin: () {
            goTo('login');
          },
        );

      // ========================================================
      // BUYER DASHBOARD
      // ========================================================

      case 'dashboard':
        return DashboardScreen(
          onProfileClick: () {
            goTo('profile');
          },
        );

      // ========================================================
      // RIDER DASHBOARD
      // ========================================================

      case 'riderDashboard':
        return RiderDashboard(
          onLogout: logout,
        );

      // ========================================================
      // ADMIN DASHBOARD (rider approvals / logistics)
      // ========================================================

      case 'adminDashboard':
        return LogisticsDashboardScreen(
          token: adminToken ?? '',
          adminName: loggedInUser?['name']?.toString() ?? 'Admin',
          onLogout: logout,
        );

      // ========================================================
      // PROFILE
      // ========================================================

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
          onEditProfile: () {
            goTo('accountMenu');
          },
        );

      // ========================================================
      // ACCOUNT MENU
      // ========================================================

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
          onAddresses: () {
            goTo('addresses');
          },
        );

      // ========================================================
      // MY ADDRESSES
      // ========================================================

      case 'addresses':
        return MyAddressesScreen(
          userId: int.tryParse(
                loggedInUser?['id']?.toString() ?? '',
              ) ??
              0,
          onBack: () {
            goTo('accountMenu');
          },
          onAddAddress: () {
            goTo('addAddress');
          },
          onAddressSelected: (address) {
            debugPrint(
              'Selected address: $address',
            );
          },
        );

      // ========================================================
      // ADD ADDRESS
      // ========================================================

      case 'addAddress':
        return AddAddressScreen(
          userId: int.tryParse(
                loggedInUser?['id']?.toString() ?? '',
              ) ??
              0,
          onBack: () {
            goTo('addresses');
          },
          onSaved: () {
            goTo('addresses');
          },
        );

      // ========================================================
      // DEFAULT
      // ========================================================

      default:
        return GuestHomeScreen(
          onLogin: () {
            goTo('accountTypeSelect');
          },
          onSignUp: () {
            goTo('accountTypeSelect');
          },
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    return buildRoute();
  }
}
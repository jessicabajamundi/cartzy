import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_theme.dart';
import 'splash_screen.dart';
import 'package:cartzy/guest/guest_home_screen.dart';
import 'package:cartzy/auth/account_type_selection_screen.dart';
import 'package:cartzy/auth/login_screen.dart';
import 'package:cartzy/buyer/dashboard.dart';
import 'package:cartzy/rider/rider_dashboard.dart';
import 'package:cartzy/buyer/buyer_profile_screen.dart';
import 'package:cartzy/buyer/account_menu_screen.dart';
import 'package:cartzy/address/my_addresses_screen.dart';
import 'package:cartzy/address/add_address_screen.dart';
import 'package:cartzy/auth/buyer_registration.dart';
import 'package:cartzy/rider/rider_registration.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/services/cart_service.dart';

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
      theme: CartzyTheme.lightTheme,
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

  String? riderToken;

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
      riderToken = null;
      pendingAccountType = AccountType.buyer;
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

      if (accountType == AccountType.rider) {
        riderToken = token;
      } else {
        riderToken = null;
      }
    });

    // IMPORTANT:
    // Rider → Rider Dashboard
    // Buyer → Buyer Dashboard
    if (accountType == AccountType.rider) {
      goTo('riderDashboard');
    } else {
      goTo('dashboard');
    }
  }

  // ============================================================
  // BUYER REGISTRATION SUCCESS
  // ============================================================

  void handleBuyerRegistrationSuccess(
    Map<String, dynamic> user,
  ) {
    setState(() {
      loggedInUser = Map<String, dynamic>.from(user);
      pendingAccountType = AccountType.buyer;
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
      // ACCOUNT TYPE SELECTION
      //
      // Buyer and Rider only.
      // Logistics has been removed.
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
            // Buyer registration
            if (accountType == AccountType.buyer) {
              goTo('buyerRegistration');
            }

            // Rider registration
            else if (accountType == AccountType.rider) {
              goTo('riderRegistration');
            }

            // No Logistics registration
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
          onRegistrationSubmitted:
              handleBuyerRegistrationSuccess,
          onBackToLogin: () {
            goTo('login');
          },
        );

      // ========================================================
      // RIDER REGISTRATION
      //
      // Existing Rider accounts are still supported.
      // ========================================================

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
      // BUYER DASHBOARD
      // ========================================================

      case 'dashboard':
        return DashboardScreen(
          onCartClick: () {
            goTo('cart');
          },
          onProfileClick: () {
            goTo('profile');
          },
        );

      // ========================================================
      // CART
      // ========================================================

      case 'cart':
        return CartScreen(
          onBack: () {
            goTo('dashboard');
          },
          onCheckout: () {
            goTo('checkout');
          },
        );

      // ========================================================
      // CHECKOUT
      // ========================================================

      case 'checkout':
        return CheckoutScreen(
          onBack: () {
            goTo('cart');
          },
          onOrderPlaced: () {
            CartService.instance.clearSelected();
            goTo('dashboard');
          },
        );

      // ========================================================
      // RIDER DASHBOARD
      //
      // Existing Rider account goes here.
      // ========================================================

      case 'riderDashboard':
        return RiderDashboard(
          token: riderToken ?? '',
          userId: int.tryParse(
                loggedInUser?['id']?.toString() ?? '',
              ) ??
              0,
          userName:
              loggedInUser?['name']?.toString() ?? 'Rider',
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

  // ============================================================
  // BUILD
  // ============================================================

  @override
  Widget build(BuildContext context) {
    return buildRoute();
  }
}
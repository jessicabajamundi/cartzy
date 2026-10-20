import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_theme.dart';
import 'splash_screen.dart';
import 'package:cartzy/guest/guest_home_screen.dart';
import 'package:cartzy/auth/account_type_selection_screen.dart';
import 'package:cartzy/auth/login_screen.dart';
import 'package:cartzy/buyer/dashboard.dart';
import 'package:cartzy/rider/rider_dashboard.dart';
import 'package:cartzy/buyer/account_menu_screen.dart';
import 'package:cartzy/buyer/account_settings_screen.dart';
import 'package:cartzy/address/my_addresses_screen.dart';
import 'package:cartzy/address/add_address_screen.dart';
import 'package:cartzy/auth/buyer_registration.dart';
import 'package:cartzy/rider/rider_registration.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/auth/email_verification_screen.dart';
import 'package:cartzy/widgets/access_denied_screen.dart';
import 'package:cartzy/buyer/vouchers_screen.dart';

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
  String pendingVerificationEmail = '';

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
      pendingVerificationEmail = '';
      pendingAccountType = AccountType.buyer;
    });

    goTo('guest');
  }

  Future<void> confirmLogout() async {
    final userName = loggedInUser?['name']?.toString().trim();
    final displayName = (userName == null || userName.isEmpty)
        ? 'your account'
        : userName;
    var signingOut = false;

    final confirmed = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          backgroundColor: Colors.white,
          surfaceTintColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          title: const Text(
            'Sign out?',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
          ),
          content: Text.rich(
            TextSpan(
              style: const TextStyle(fontSize: 12, color: Color(0xFF777083), height: 1.45),
              children: [
                const TextSpan(text: "You're signed in as "),
                TextSpan(text: displayName, style: const TextStyle(fontWeight: FontWeight.w700)),
                const TextSpan(text: ". You'll need to log in again to continue."),
              ],
            ),
            textAlign: TextAlign.center,
          ),
          actionsAlignment: MainAxisAlignment.center,
          actions: [
            SizedBox(
              width: 108,
              height: 38,
              child: OutlinedButton(
                onPressed: signingOut ? null : () => Navigator.pop(dialogContext, false),
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF514B5D),
                  side: const BorderSide(color: Color(0xFFE7E2EB)),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
                  padding: EdgeInsets.zero,
                  textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
                ),
                child: const Text('Stay signed in'),
              ),
            ),
            const SizedBox(width: 6),
            SizedBox(
              width: 108,
              height: 38,
              child: FilledButton(
                onPressed: signingOut
                    ? null
                    : () async {
                        setDialogState(() => signingOut = true);
                        await Future<void>.delayed(const Duration(milliseconds: 450));
                        if (dialogContext.mounted) Navigator.pop(dialogContext, true);
                      },
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFFC35C56),
                  foregroundColor: Colors.white,
                  disabledBackgroundColor: const Color(0xFFC35C56),
                  disabledForegroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
                  padding: EdgeInsets.zero,
                  textStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
                ),
                child: Text(signingOut ? 'Signing out...' : 'Sign out'),
              ),
            ),
          ],
        ),
      ),
    );

    if (confirmed == true && mounted) logout();
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
  // BUYER REGISTRATION SUCCESS -> EMAIL VERIFICATION (OTP)
  // ============================================================

  void handleBuyerRegistrationSuccess(
    Map<String, dynamic> user,
  ) {
    setState(() {
      pendingVerificationEmail = user['email']?.toString() ?? '';
    });

    goTo('emailVerification');
  }

  void handleEmailVerificationSuccess(
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
            goTo('login');
          },
          onSignUp: () {
            goTo('buyerRegistration');
          },
        );

      // ========================================================
      // ACCOUNT TYPE SELECTION (Legacy fallback)
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

          onVerifyEmail: (email) {
            setState(() {
              pendingVerificationEmail = email;
            });
            goTo('emailVerification');
          },

          onSignUp: (accountType) {
            // Buyer registration (with role switcher inside)
            goTo('buyerRegistration');
          },

          onBack: () {
            goTo('guest');
          },

          onBackToGuest: () {
            goTo('guest');
          },
        );

      // ========================================================
      // EMAIL VERIFICATION (OTP)
      // ========================================================

      case 'emailVerification':
        return EmailVerificationScreen(
          email: pendingVerificationEmail.isNotEmpty
              ? pendingVerificationEmail
              : 'juanmiguel.delacruz@example.com',
          onVerificationSuccess: handleEmailVerificationSuccess,
          onBackToLogin: () {
            goTo('login');
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
          onSwitchToRiderRegistration: () {
            goTo('riderRegistration');
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
          onSwitchToBuyerRegistration: () {
            goTo('buyerRegistration');
          },
        );

      // ========================================================
      // BUYER DASHBOARD
      // ========================================================

      case 'dashboard':
        return DashboardScreen(
          userName: loggedInUser?['name']?.toString() ?? 'Leonardo, Tiffany Joy O.',
          onCartClick: () {
            goTo('cart');
          },
          onProfileClick: () {
            goTo('accountSettings');
          },
          onVouchersClick: () {
            goTo('vouchers');
          },
          onLogout: confirmLogout,
        );

      // ========================================================
      // MY VOUCHERS
      // ========================================================

      case 'vouchers':
        return VouchersScreen(
          onBack: () {
            goTo('dashboard');
          },
          onUseAtCheckout: () {
            goTo('checkout');
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
      // ROLE ACCESS CONTROL (TC-BUY-030)
      // ========================================================

      case 'sellerDashboard':
      case 'logisticsDashboard':
      case 'adminDashboard':
        return AccessDeniedScreen(
          attemptedPage: currentRoute == 'sellerDashboard'
              ? 'Seller Dashboard'
              : currentRoute == 'logisticsDashboard'
                  ? 'Logistics Dashboard'
                  : 'Admin Dashboard',
          userRole: loggedInUser?['role']?.toString().toUpperCase() ?? 'BUYER',
          onReturn: () => goTo(loggedInUser != null ? 'dashboard' : 'guest'),
        );

      // ========================================================
      // RIDER DASHBOARD
      // ========================================================

      case 'riderDashboard':
        if (loggedInUser?['role'] == 'buyer') {
          return AccessDeniedScreen(
            attemptedPage: 'Rider Dashboard',
            userRole: 'BUYER',
            onReturn: () => goTo('dashboard'),
          );
        }
        return RiderDashboard(
          token: riderToken ?? '',
          userId: int.tryParse(
                loggedInUser?['id']?.toString() ?? '',
              ) ??
              0,
          userName:
              loggedInUser?['name']?.toString() ?? 'Rider',
          onLogout: confirmLogout,
        );

      // ========================================================
      // ACCOUNT SETTINGS & PROFILE
      // ========================================================

      case 'accountSettings':
      case 'profile':
        return AccountSettingsScreen(
          userName:
              loggedInUser?['name']?.toString() ?? 'Leonardo, Tiffany Joy O.',
          userEmail:
              loggedInUser?['email']?.toString() ?? 'tiffany.leonardolspu@gmail.com',
          userPhone:
              loggedInUser?['phone']?.toString() ?? '09171234567',
          onBack: () {
            goTo('dashboard');
          },
          onLogout: confirmLogout,
          onProfileUpdated: (updatedUser) {
            setState(() {
              loggedInUser = {
                ...(loggedInUser ?? {}),
                ...updatedUser,
              };
            });
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
          onLogout: confirmLogout,
          onAddresses: () {
            goTo('addresses');
          },
          onNavigateRole: (route) {
            goTo(route);
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
            goTo('login');
          },
          onSignUp: () {
            goTo('buyerRegistration');
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

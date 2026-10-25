// Renders every screen on small phones and with larger text, scrolls through
// it, and fails with the source location of any RenderFlex overflow.
import 'package:cartzy/address/add_address_screen.dart';
import 'package:cartzy/address/my_addresses_screen.dart';
import 'package:cartzy/auth/account_type_selection_screen.dart';
import 'package:cartzy/auth/buyer_registration.dart';
import 'package:cartzy/auth/email_verification_screen.dart';
import 'package:cartzy/auth/login_screen.dart';
import 'package:cartzy/buyer/account_menu_screen.dart';
import 'package:cartzy/buyer/account_settings_screen.dart';
import 'package:cartzy/buyer/buyer_account_screen.dart';
import 'package:cartzy/buyer/buyer_profile_screen.dart';
import 'package:cartzy/buyer/dashboard.dart';
import 'package:cartzy/buyer/edit_buyer_profile_screen.dart';
import 'package:cartzy/buyer/id_verification_screen.dart';
import 'package:cartzy/buyer/messages_screen.dart';
import 'package:cartzy/buyer/my_orders_screen.dart';
import 'package:cartzy/buyer/notifications_screen.dart';
import 'package:cartzy/buyer/order_history_screen.dart';
import 'package:cartzy/buyer/reviews_screen.dart';
import 'package:cartzy/buyer/track_order_screen.dart';
import 'package:cartzy/buyer/vouchers_screen.dart';
import 'package:cartzy/buyer/wishlist_screen.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/guest/guest_home_screen.dart';
import 'package:cartzy/products/all_categories_screen.dart';
import 'package:cartzy/products/product_details_screen.dart';
import 'package:cartzy/products/product_list_screen.dart';
import 'package:cartzy/rider/rider_account_screen.dart';
import 'package:cartzy/rider/rider_chat_list_screen.dart';
import 'package:cartzy/rider/rider_chat_screen.dart';
import 'package:cartzy/rider/rider_dashboard.dart';
import 'package:cartzy/rider/rider_delivery_dashboard.dart';
import 'package:cartzy/rider/rider_delivery_history_screen.dart';
import 'package:cartzy/rider/rider_delivery_process.dart';
import 'package:cartzy/rider/rider_earnings_dashboard.dart';
import 'package:cartzy/rider/rider_order_detail.dart';
import 'package:cartzy/rider/rider_pickup_dashboard.dart';
import 'package:cartzy/rider/rider_pickup_process.dart';
import 'package:cartzy/rider/rider_registration.dart';
import 'package:cartzy/welcome_screen.dart';
import 'package:cartzy/widgets/access_denied_screen.dart';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

void _n() {}

RiderOrder _order() => RiderOrder(
      id: 1024,
      status: 'to_ship',
      buyerName: 'Maria Clara Dela Cruz-Santos',
      buyerPhone: '09171234567',
      deliveryAddress:
          'Unit 402, Sunset Towers, 5th Avenue corner 26th Street, Bonifacio Global City, Taguig City, Metro Manila',
      itemsSummary: '2x Logitech Zone True Wireless Earbuds, 1x Air Fryer',
      totalAmount: 12345.67,
      createdAt: '2026-10-25 14:30',
    );

final screens = <String, Widget Function()>{
  'Welcome': () => const WelcomeScreen(onGetStarted: _n),
  'AccountTypeSelection': () => AccountTypeSelectionScreen(
      onSelected: (_) {}, onBackToGuest: _n),
  'Login': () => LoginScreen(
      initialAccountType: AccountType.buyer,
      onLogin: (_, __, {token}) {},
      onSignUp: (_) {},
      onBack: _n,
      onBackToGuest: _n),
  'BuyerRegistration': () => BuyerRegistration(
      onRegistrationSubmitted: (_) {}, onBackToLogin: _n),
  'RiderRegistration': () =>
      const RiderRegistration(onRegistrationSubmitted: _n, onBackToLogin: _n),
  'EmailVerification': () => EmailVerificationScreen(
      email: 'very.long.email.address@example.com',
      onVerificationSuccess: (_) {},
      onBackToLogin: _n),
  'GuestHome': () => const GuestHomeScreen(onLogin: _n, onSignUp: _n),
  'Dashboard': () => const DashboardScreen(userName: 'Maria Clara'),
  'AllCategories': () => const AllCategoriesScreen(onBack: _n),
  'ProductList': () => const ProductListScreen(title: 'New Arrivals', onBack: _n),
  'ProductDetails': () => const ProductDetailsScreen(
      name: 'Nike Jr Tiempo Maestro Club Flex Kids Football Boots',
      category: 'Shoes',
      price: '₱2,195',
      originalPrice: '₱3,499',
      sellerName: 'Nike Official Store',
      rating: 4.8,
      soldCount: '1.2k',
      onBack: _n),
  'Cart': () => const CartScreen(onBack: _n, onCheckout: _n),
  'Checkout': () => const CheckoutScreen(onBack: _n, onOrderPlaced: _n),
  'AccountMenu': () => const AccountMenuScreen(
      onBack: _n,
      onMyPurchase: _n,
      onLogout: _n,
      onAddresses: _n,
      userId: 1,
      userName: 'Maria Clara Dela Cruz-Santos',
      userEmail: 'maria.clara.delacruz@example.com'),
  'AccountSettings': () => const AccountSettingsScreen(
      isLoggedIn: true,
      userName: 'Maria Clara Dela Cruz-Santos',
      userEmail: 'maria.clara.delacruz@example.com',
      onBack: _n,
      onLogout: _n),
  'AccountSettingsGuest': () => const AccountSettingsScreen(
      isLoggedIn: false, userName: '', userEmail: '', onBack: _n, onLogout: _n),
  'BuyerAccount': () => const BuyerAccountScreen(onBack: _n, userId: 1),
  'BuyerProfile': () => const BuyerProfileScreen(
      userName: 'Maria Clara Dela Cruz-Santos',
      userEmail: 'maria.clara.delacruz@example.com',
      onBack: _n,
      onLogout: _n,
      onEditProfile: _n),
  'EditBuyerProfile': () => const EditBuyerProfileScreen(onBack: _n),
  'IdVerification': () => const IdVerificationScreen(),
  'Messages': () => const MessagesScreen(onBack: _n),
  'MyOrders': () => const MyOrdersScreen(onBack: _n),
  'Notifications': () => const NotificationsScreen(onBack: _n),
  'OrderHistory': () => const OrderHistoryScreen(onBack: _n),
  'Reviews': () => const ReviewsScreen(onBack: _n),
  'TrackOrder': () => const TrackOrderScreen(onBack: _n),
  'Vouchers': () => const VouchersScreen(onBack: _n),
  'Wishlist': () => const WishlistScreen(onBack: _n),
  'MyAddresses': () =>
      const MyAddressesScreen(userId: 1, onBack: _n, onAddAddress: _n),
  'AddAddress': () => const AddAddressScreen(onBack: _n, userId: 1),
  'AccessDenied': () => const AccessDeniedScreen(
      attemptedPage: 'Rider Earnings Dashboard', onReturn: _n),
  'RiderDashboard': () => const RiderDashboard(
      token: 't', userId: 1, userName: 'Juan Dela Cruz', onLogout: _n),
  'RiderAccount': () => const RiderAccountScreen(userId: 1, onBack: _n),
  'RiderChatList': () => const RiderChatListScreen(
      token: 't', myUserId: 1, onBack: _n, onLogout: _n),
  'RiderChat': () => const RiderChatScreen(
      token: 't',
      myUserId: 1,
      otherUserId: 2,
      otherUserName: 'Maria Clara Dela Cruz-Santos'),
  'RiderDeliveryDashboard': () =>
      const RiderDeliveryDashboard(token: 't', onBack: _n),
  'RiderDeliveryHistory': () =>
      const RiderDeliveryHistoryScreen(token: 't', onBack: _n),
  'RiderDeliverProcess': () => RiderDeliverProcess(order: _order(), token: 't'),
  'RiderEarnings': () => const RiderEarningsScreen(token: 't', onBack: _n),
  'RiderPickupDashboard': () =>
      const RiderPickupDashboard(token: 't', onBack: _n),
  'RiderPickupProcess': () => RiderPickupProcess(order: _order(), token: 't'),
};

const configs = <String, (Size, double)>{
  'small320@1.3x': (Size(320, 640), 1.3),
  'phone360@1.0x': (Size(360, 760), 1.0),
  'phone360@1.5x': (Size(360, 760), 1.5),
};

Future<void> _loadRealFonts() async {
  // Tests normally draw every glyph as a square block, which makes text far
  // wider than on a phone. Load the Roboto files that ship with the Flutter
  // SDK under the app's Google Fonts family names so widths are realistic.
  final dir =
      '${Platform.environment['FLUTTER_ROOT']}/bin/cache/artifacts/material_fonts';
  ByteData font(String name) =>
      ByteData.sublistView(File('$dir/$name').readAsBytesSync());
  final byWeight = {
    'regular': 'roboto-regular.ttf',
    '300': 'roboto-light.ttf',
    '500': 'roboto-medium.ttf',
    '600': 'roboto-medium.ttf',
    '700': 'roboto-bold.ttf',
    '800': 'roboto-black.ttf',
    '900': 'roboto-black.ttf',
  };
  final loaders = <FontLoader>[];
  for (final family in ['DMSans', 'PlayfairDisplay', 'CormorantGaramond', 'Poppins']) {
    for (final e in byWeight.entries) {
      for (final v in [e.key, e.key == 'regular' ? 'italic' : '${e.key}italic']) {
        loaders.add(FontLoader('${family}_$v')..addFont(Future.value(font(e.value))));
      }
    }
  }
  loaders.add(FontLoader('Roboto')..addFont(Future.value(font('roboto-regular.ttf'))));
  for (final l in loaders) {
    await l.load();
  }
}

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;
  setUpAll(_loadRealFonts);
  for (final screen in screens.entries) {
    for (final config in configs.entries) {
      testWidgets('${screen.key} ${config.key}', (tester) async {
        final (size, scale) = config.value;
        tester.view.physicalSize = size;
        tester.view.devicePixelRatio = 1;
        final found = <String>{};
        final old = FlutterError.onError;
        FlutterError.onError = (d) {
          final s = d.toString();
          if (!s.contains('overflowed')) return;
          final msg = RegExp(r'A RenderFlex overflowed by [\d.]+ pixels on the \w+')
                  .firstMatch(s)
                  ?.group(0) ??
              s.split('\n').firstWhere((l) => l.contains('overflowed'));
          final loc = RegExp(r'file:///[^\s]*?/lib/([^\s:]+:\d+)')
                  .firstMatch(s)
                  ?.group(1) ??
              '?';
          found.add('$loc | $msg');
        };
        try {
          await tester.pumpWidget(MaterialApp(
            builder: (context, child) => MediaQuery(
              data: MediaQuery.of(context)
                  .copyWith(textScaler: TextScaler.linear(scale)),
              child: child!,
            ),
            home: screens[screen.key]!(),
          ));
          for (var i = 0; i < 5; i++) {
            await tester.pump(const Duration(milliseconds: 400));
          }
          final scrollables = find.byType(Scrollable);
          for (var i = 0; i < 8; i++) {
            if (scrollables.evaluate().isEmpty) break;
            try {
              await tester.drag(scrollables.first, const Offset(0, -500),
                  warnIfMissed: false);
            } catch (_) {}
            await tester.pump(const Duration(milliseconds: 300));
          }
        } finally {
          FlutterError.onError = old;
        }
        // Let pending network timeouts and timers finish before the next screen.
        await tester.pumpWidget(const SizedBox());
        await tester.pump(const Duration(seconds: 30));
        tester.view.resetPhysicalSize();
        tester.view.resetDevicePixelRatio();
        expect(found, isEmpty,
            reason: '${screen.key} overflows on ${config.key}');
      });
    }
  }
}

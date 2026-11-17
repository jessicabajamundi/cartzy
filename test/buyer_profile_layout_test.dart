import 'package:cartzy/buyer/account_settings_screen.dart';
import 'package:cartzy/theme/cartzy_theme.dart';
import 'package:cartzy/widgets/theme_toggle_tile.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  for (final mode in [ThemeMode.light, ThemeMode.dark]) {
    testWidgets(
        'buyer profile has full-width dividers and theme control: $mode',
        (tester) async {
      SharedPreferences.setMockInitialValues({});
      await tester.binding.setSurfaceSize(const Size(360, 800));
      addTearDown(() => tester.binding.setSurfaceSize(null));
      await tester.pumpWidget(MaterialApp(
        theme: CartzyTheme.lightTheme,
        darkTheme: CartzyTheme.darkTheme,
        themeMode: mode,
        home: AccountSettingsScreen(
          isLoggedIn: true,
          userName: 'Test Buyer',
          userEmail: 'buyer@example.com',
          onBack: () {},
          onLogout: () {},
        ),
      ));
      await tester.pump(const Duration(milliseconds: 300));

      expect(find.byType(ThemeToggleTile), findsOneWidget);
      expect(find.text('Cartzy Wallet'), findsOneWidget);
      expect(tester.getTopLeft(find.text('MY ORDERS')).dy,
          lessThan(tester.getTopLeft(find.text('Cartzy Wallet')).dy));
      expect(tester.getTopLeft(find.text('Cartzy Wallet')).dy,
          lessThan(tester.getTopLeft(find.text('ACCOUNT & SETTINGS')).dy));
      expect(tester.getTopLeft(find.byType(ThemeToggleTile)).dy,
          lessThan(tester.getTopLeft(find.text('Messages')).dy));

      final dividers = find.byWidgetPredicate((widget) =>
          widget is Divider && widget.indent == 0 && widget.endIndent == 0);
      expect(dividers, findsNWidgets(10));
      for (final element in dividers.evaluate()) {
        // The card's 1.1px border is preserved on both sides.
        expect(tester.getSize(find.byWidget(element.widget)).width,
            closeTo(320 - 2.2, .01));
      }
      expect(tester.takeException(), isNull);
      await tester.pumpWidget(const SizedBox());
    });
  }
}

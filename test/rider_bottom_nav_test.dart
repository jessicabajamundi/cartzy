import 'package:cartzy/rider/rider_dashboard.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  Future<void> pumpRider(WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.625;
    addTearDown(tester.view.reset);
    await tester.pumpWidget(MaterialApp(
      home: RiderDashboard(token: '', userId: 7, userName: 'Arnel Gomez', onLogout: () {}),
    ));
    await tester.pump(const Duration(milliseconds: 300));
  }

  Finder navLabel(String label) => find.descendant(
        of: find.byWidgetPredicate((w) => w is Semantics && w.properties.label == label),
        matching: find.text(label),
      );

  testWidgets('rider has a bottom nav with Home, History, Earnings and Profile', (tester) async {
    await pumpRider(tester);

    for (final label in ['Home', 'History', 'Earnings', 'Profile']) {
      expect(navLabel(label), findsOneWidget, reason: label);
    }
    expect(tester.takeException(), isNull);
  });

  testWidgets('each tab opens its screen and back returns home', (tester) async {
    await pumpRider(tester);

    await tester.tap(navLabel('Earnings'));
    await tester.pump(const Duration(milliseconds: 300));
    expect(find.text('Profit & Earnings Dashboard'), findsOneWidget);

    await tester.tap(navLabel('History'));
    await tester.pump(const Duration(milliseconds: 300));
    final historySelected = tester.widget<Semantics>(
      find.byWidgetPredicate((w) => w is Semantics && w.properties.label == 'History'),
    );
    expect(historySelected.properties.selected, isTrue);

    await tester.tap(navLabel('Profile'));
    await tester.pump(const Duration(milliseconds: 300));
    expect(
      tester.widget<Semantics>(find.byWidgetPredicate((w) => w is Semantics && w.properties.label == 'Profile')).properties.selected,
      isTrue,
    );

    await tester.tap(navLabel('Home'));
    await tester.pump(const Duration(milliseconds: 300));
    expect(
      tester.widget<Semantics>(find.byWidgetPredicate((w) => w is Semantics && w.properties.label == 'Home')).properties.selected,
      isTrue,
    );
    expect(tester.takeException(), isNull);
  });

  testWidgets('rider bottom nav has a center Scan button between History and Earnings', (tester) async {
    await pumpRider(tester);

    final scanButton = find.byWidgetPredicate((w) => w is Semantics && w.properties.label == 'Scan parcel waybill barcode or QR');
    expect(scanButton, findsOneWidget);
    expect(find.descendant(of: scanButton, matching: find.text('Scan')), findsOneWidget);
    expect(find.descendant(of: scanButton, matching: find.byIcon(Icons.qr_code_scanner_rounded)), findsOneWidget);

    final history = tester.getCenter(navLabel('History'));
    final scan = tester.getCenter(find.descendant(of: scanButton, matching: find.text('Scan')));
    final earnings = tester.getCenter(navLabel('Earnings'));

    expect(history.dx < scan.dx, isTrue);
    expect(scan.dx < earnings.dx, isTrue);
  });
}

import 'dart:convert';

import 'package:cartzy/rider/courier_dashboard.dart';
import 'package:cartzy/rider/rider_home_address.dart';
import 'package:cartzy/services/courier_service.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  test('home address reads only real values', () {
    expect(HomeAddress.fromJson(null), isNull);
    expect(HomeAddress.fromJson({'barangay': null, 'city': '', 'province': null}), isNull);
    final home = HomeAddress.fromJson({'barangay': 'Bubukal', 'city': 'Santa Cruz', 'province': 'Laguna'})!;
    expect(home.label, 'Bubukal, Santa Cruz, Laguna');
    expect(home.isComplete, isTrue);
  });

  testWidgets('if the PSGC list cannot load, the rider types the address and it is saved', (tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.625;
    addTearDown(tester.view.reset);

    Map<String, dynamic>? sent;
    final client = MockClient((request) async {
      if (request.url.host.contains('psgc')) return http.Response('down', 503);
      if (request.method == 'PUT' && request.url.path.endsWith('/rider/home-address')) {
        sent = jsonDecode(request.body) as Map<String, dynamic>;
        return http.Response(jsonEncode({'message': 'Saved', 'home_address': sent}), 200, headers: {'content-type': 'application/json'});
      }
      return http.Response('{}', 404);
    });

    HomeAddress? result;
    await http.runWithClient(() async {
      await tester.pumpWidget(MaterialApp(
        home: Builder(
          builder: (context) => Scaffold(
            body: Center(
              child: TextButton(
                onPressed: () async => result = await showHomeAddressSheet(context),
                child: const Text('open'),
              ),
            ),
          ),
        ),
      ));
      await tester.tap(find.text('open'));
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 400));
      await tester.pump();
      await tester.pump();

      // Saving with nothing filled in is refused.
      await tester.tap(find.text('Save home address'));
      await tester.pump();
      expect(find.text('Required'), findsNWidgets(3));
      expect(sent, isNull);

      await tester.enterText(find.widgetWithText(TextField, 'Province'), 'Laguna');
      await tester.enterText(find.widgetWithText(TextField, 'City / municipality'), 'Santa Cruz');
      await tester.enterText(find.widgetWithText(TextField, 'Barangay'), 'Bubukal');
      await tester.tap(find.text('Save home address'));
      for (var i = 0; i < 5; i++) {
        await tester.pump(const Duration(milliseconds: 100));
      }
    }, () => client);

    expect(sent, {'barangay': 'Bubukal', 'city': 'Santa Cruz', 'province': 'Laguna'});
    expect(result?.label, 'Bubukal, Santa Cruz, Laguna');
  });

  testWidgets('the courier profile shows the saved home address, or asks for one', (tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.625;
    addTearDown(tester.view.reset);

    Future<void> open(Map<String, dynamic>? home) async {
      await tester.pumpWidget(MaterialApp(
        home: CourierDashboard(
          key: UniqueKey(),
          token: '',
          onLogout: () {},
          loadDashboard: () async => CourierDashboardData.fromJson({
            'courier': {'name': 'Dante Ramos', 'home_address': home},
            'shift': {'on_shift': false},
            'today': {},
          }),
        ),
      ));
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 200));
      await tester.tap(find.descendant(
        of: find.byWidgetPredicate((w) => w is Semantics && w.properties.label == 'Profile'),
        matching: find.text('Profile'),
      ));
      await tester.pump(const Duration(milliseconds: 300));
    }

    await open(null);
    expect(find.textContaining('Not added yet'), findsOneWidget);
    expect(find.text('Add'), findsOneWidget);

    await open({'barangay': 'Bubukal', 'city': 'Santa Cruz', 'province': 'Laguna'});
    expect(find.text('Bubukal, Santa Cruz, Laguna'), findsOneWidget);
    expect(find.text('Change'), findsOneWidget);
  });
}

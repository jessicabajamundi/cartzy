import 'package:cartzy/rider/courier_dashboard.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/courier_service.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

Map<String, dynamic> _json({
  Map<String, dynamic> courier = const {},
  bool onShift = false,
  List<Map<String, dynamic>> pickups = const [],
  List<Map<String, dynamic>> inVehicle = const [],
  List<Map<String, dynamic>> handed = const [],
}) =>
    {
      'courier': {
        'name': 'Dante Ramos',
        'email': 'dante@example.com',
        'courier_type': 'first_mile',
        'courier_type_label': 'First Mile',
        'vehicle_label': 'Van',
        'hub': 'Metro South Hub',
        'status': 'approved',
        'is_active': true,
        ...courier,
      },
      'shift': {'on_shift': onShift, 'since': onShift ? '2026-11-09T00:05:00+00:00' : null},
      'today': {
        'pickups_to_do': pickups.length,
        'parcels_to_collect': pickups.fold<int>(0, (n, p) => n + (p['parcels_waiting'] as int)),
        'in_vehicle': inVehicle.length,
        'collected_today': inVehicle.length,
        'handed_to_hub_today': handed.length,
      },
      'pickups': pickups,
      'in_vehicle': inVehicle,
      'handed_to_hub': handed,
    };

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  Future<void> pump(WidgetTester tester, Future<CourierDashboardData> Function() load) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.625;
    addTearDown(tester.view.reset);
    await tester.pumpWidget(MaterialApp(home: CourierDashboard(token: '', onLogout: () {}, loadDashboard: load)));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 200));
  }

  Future<void> openTab(WidgetTester tester, String label) async {
    await tester.tap(find.descendant(
      of: find.byWidgetPredicate((w) => w is Semantics && w.properties.label == label),
      matching: find.text(label),
    ));
    await tester.pump(const Duration(milliseconds: 300));
  }

  testWidgets('a new courier sees their own details and empty states, not sample data', (tester) async {
    await pump(tester, () async => CourierDashboardData.fromJson(_json()));

    expect(find.text('Hi, Dante'), findsOneWidget);
    expect(find.text('First Mile courier'), findsOneWidget);
    expect(find.text('Metro South Hub'), findsOneWidget);
    expect(find.text('Not on shift'), findsOneWidget);
    expect(find.textContaining('No pickups assigned to you'), findsOneWidget);
    expect(find.textContaining('Nothing in your vehicle'), findsOneWidget);
    // No made-up rating or numbers.
    expect(find.textContaining('Rating'), findsNothing);
    expect(find.textContaining('★'), findsNothing);
    expect(tester.takeException(), isNull);
  });

  testWidgets('pickups and parcels from the server are listed', (tester) async {
    await pump(
      tester,
      () async => CourierDashboardData.fromJson(_json(
        onShift: true,
        pickups: [
          {'id': 1, 'seller_name': 'Laguna Gadgets', 'address': '123 Rizal St, Santa Cruz', 'pickup_time': '2:00 PM', 'parcel_count': 3, 'parcels_waiting': 2, 'weight_kg': 2.5, 'seller_phone': '09171234567', 'notes': 'Gate 2'},
        ],
        inVehicle: [
          {'tracking_code': 'CTZ-IN-VAN-1', 'seller_name': 'Laguna Gadgets', 'items_count': 2},
        ],
        handed: [
          {'tracking_code': 'CTZ-AT-HUB-1', 'seller_name': 'Bay Books', 'items_count': 1, 'at': '2026-11-09T02:00:00+00:00'},
        ],
      )),
    );

    expect(find.text('On shift'), findsOneWidget);
    expect(find.text('Laguna Gadgets'), findsNWidgets(1));
    expect(find.textContaining('2 parcels waiting'), findsOneWidget);
    expect(find.text('CTZ-IN-VAN-1'), findsOneWidget);

    await openTab(tester, 'Pickups');
    expect(find.text('09171234567'), findsOneWidget);
    expect(find.text('Gate 2'), findsOneWidget);

    await openTab(tester, 'History');
    expect(find.text('CTZ-AT-HUB-1'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  testWidgets('profile shows "Not set" instead of a default plate or area', (tester) async {
    await pump(tester, () async => CourierDashboardData.fromJson(_json(courier: {'plate_no': null, 'area': null})));

    await openTab(tester, 'Profile');
    expect(find.text('Dante Ramos'), findsOneWidget);
    expect(find.text('Van'), findsOneWidget);
    expect(find.text('Not set'), findsNWidgets(4)); // phone, plate number, area, member since
    expect(find.text('123ABC'), findsNothing);
    expect(find.text('Log out'), findsWidgets);
  });

  testWidgets('a failed load says so and offers to try again', (tester) async {
    await pump(tester, () async => throw ApiException(500, 'Server error (500). Please try again.'));

    expect(find.text("Can't load your dashboard"), findsOneWidget);
    expect(find.text('Try again'), findsOneWidget);
  });
}

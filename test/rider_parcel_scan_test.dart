import 'dart:convert';

import 'package:cartzy/rider/rider_parcel_scan.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

const _parcel = {
  'tracking_code': 'CTZ-ABC123',
  'order_reference': 'ORD-2026-12345',
  'seller_name': 'Laguna Gadgets',
  'recipient_name': 'Juan Dela Cruz',
  'recipient_address': '45 Magsaysay St, Santa Cruz, Laguna',
  'items_count': 2,
  'is_cod': true,
  'cod_amount': 499.5,
};

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  /// Fake server: records each call (action and body) and answers like the API.
  late List<(String, Map<String, dynamic>)> calls;
  late http.Response Function(String action) answer;

  http.Response json(Object body, [int status = 200]) =>
      http.Response(jsonEncode(body), status, headers: {'content-type': 'application/json'});

  Future<http.Response> server(http.Request request) async {
    final action = request.url.pathSegments.last;
    calls.add((action, jsonDecode(request.body) as Map<String, dynamic>));
    return answer(action);
  }

  setUp(() {
    calls = [];
    answer = (action) => switch (action) {
          'scan' => json({'message': 'Check the parcel, then accept or decline it.', 'already': false, 'stage': 'picked_up', 'parcel': _parcel}),
          'accept' => json({'message': 'Parcel CTZ-ABC123 is now with you. The seller, buyer and hub were notified.', 'already': false, 'stage': 'picked_up', 'parcel': _parcel}),
          _ => json({'message': 'You declined parcel CTZ-ABC123. Nothing changed and no one was notified.', 'released': false, 'parcel': _parcel}),
        };
  });

  Future<void> settle(WidgetTester tester) async {
    for (var i = 0; i < 10; i++) {
      await tester.pump(const Duration(milliseconds: 100));
    }
  }

  Future<void> scan(WidgetTester tester) async {
    await tester.pumpWidget(MaterialApp(
      home: Builder(
        builder: (context) => Scaffold(
          body: Center(
            child: ElevatedButton(
              onPressed: () => RiderParcelScan.reportCode(context, 'CTZ-ABC123'),
              child: const Text('report'),
            ),
          ),
        ),
      ),
    ));
    await tester.tap(find.text('report'));
    await settle(tester);
  }

  testWidgets('scanning shows the parcel with Accept and Decline and changes nothing yet', (tester) async {
    await http.runWithClient(() => scan(tester), () => MockClient(server));

    expect(calls.map((c) => c.$1), ['scan']);
    expect(calls.single.$2, {'code': 'CTZ-ABC123'});
    expect(find.text('Accept this parcel?'), findsOneWidget);
    expect(find.text('Laguna Gadgets'), findsOneWidget);
    expect(find.textContaining('COD · collect'), findsOneWidget);
    expect(find.text('Accept'), findsOneWidget);
    expect(find.text('Decline'), findsOneWidget);
  });

  testWidgets('accepting hands the parcel over and says who was notified', (tester) async {
    await http.runWithClient(() async {
      await scan(tester);
      await tester.tap(find.text('Accept'));
      await settle(tester);
    }, () => MockClient(server));

    expect(calls.map((c) => c.$1), ['scan', 'accept']);
    expect(find.text('Parcel is now with you'), findsOneWidget);
    expect(find.textContaining('seller, buyer and hub were notified'), findsOneWidget);
    expect(find.text('Scan another'), findsOneWidget);
  });

  testWidgets('declining asks for an optional reason and sends it', (tester) async {
    await http.runWithClient(() async {
      await scan(tester);
      await tester.tap(find.text('Decline'));
      await settle(tester);
      expect(find.text('Decline this parcel?'), findsOneWidget);
      await tester.enterText(find.byType(TextField), 'Vehicle problem');
      await tester.tap(find.widgetWithText(TextButton, 'Decline'));
      await settle(tester);
    }, () => MockClient(server));

    expect(calls.map((c) => c.$1), ['scan', 'decline']);
    expect(calls.last.$2, {'code': 'CTZ-ABC123', 'reason': 'Vehicle problem'});
    expect(find.text('Parcel declined'), findsOneWidget);
    expect(find.textContaining('no one was notified'), findsOneWidget);
  });

  testWidgets('backing out of the decline dialog sends nothing', (tester) async {
    await http.runWithClient(() async {
      await scan(tester);
      await tester.tap(find.text('Decline'));
      await settle(tester);
      await tester.tap(find.text('Back'));
      await settle(tester);
    }, () => MockClient(server));

    expect(calls.map((c) => c.$1), ['scan']);
  });

  testWidgets('a refused scan shows the reason and offers no Accept', (tester) async {
    answer = (_) => json({'message': 'Parcel CTZ-ABC123 is assigned to another rider.'}, 422);

    await http.runWithClient(() => scan(tester), () => MockClient(server));

    expect(find.text("Can't take this parcel"), findsOneWidget);
    expect(find.text('Parcel CTZ-ABC123 is assigned to another rider.'), findsOneWidget);
    expect(find.text('Scanned: CTZ-ABC123'), findsOneWidget);
    expect(find.text('Accept'), findsNothing);
  });

  testWidgets('a parcel already with the rider skips the choice', (tester) async {
    answer = (_) => json({'message': 'Parcel CTZ-ABC123 is already with you.', 'already': true, 'stage': 'picked_up', 'parcel': _parcel});

    await http.runWithClient(() => scan(tester), () => MockClient(server));

    expect(find.text('Already with you'), findsOneWidget);
    expect(find.text('Accept'), findsNothing);
  });
}

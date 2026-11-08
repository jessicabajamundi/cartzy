import 'dart:convert';

import 'package:cartzy/rider/rider_parcel_scan.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  /// Runs [body] with every HTTP call answered by [handler].
  Future<void> withServer(
    WidgetTester tester,
    Future<http.Response> Function(http.Request) handler,
    Future<void> Function() body,
  ) =>
      http.runWithClient(body, () => MockClient(handler));

  Future<void> pumpAndScan(WidgetTester tester, String code) async {
    await tester.pumpWidget(MaterialApp(
      home: Builder(
        builder: (context) => Scaffold(
          body: Center(
            child: ElevatedButton(
              onPressed: () => RiderParcelScan.reportCode(context, code),
              child: const Text('report'),
            ),
          ),
        ),
      ),
    ));
    await tester.tap(find.text('report'));
    // Let the request finish and the sheet slide up.
    for (var i = 0; i < 10; i++) {
      await tester.pump(const Duration(milliseconds: 100));
    }
  }

  testWidgets('a good scan sends the code and shows the parcel now with the rider', (tester) async {
    late Map<String, dynamic> sent;
    late String path;

    await withServer(tester, (request) async {
      path = request.url.path;
      sent = jsonDecode(request.body) as Map<String, dynamic>;
      return http.Response(
        jsonEncode({
          'message': 'Parcel CTZ-ABC123 is now with you. The seller, buyer and hub were notified.',
          'already': false,
          'stage': 'picked_up',
          'parcel': {
            'tracking_code': 'CTZ-ABC123',
            'order_reference': 'ORD-2026-12345',
            'seller_name': 'Laguna Gadgets',
            'recipient_name': 'Juan Dela Cruz',
            'recipient_address': '45 Magsaysay St, Santa Cruz, Laguna',
            'items_count': 2,
            'is_cod': true,
            'cod_amount': 499.5,
          },
        }),
        200,
        headers: {'content-type': 'application/json'},
      );
    }, () => pumpAndScan(tester, 'CTZ-ABC123'));

    expect(path, endsWith('/rider/parcels/scan'));
    expect(sent, {'code': 'CTZ-ABC123'});
    expect(find.text('Parcel is now with you'), findsOneWidget);
    expect(find.textContaining('seller, buyer and hub were notified'), findsOneWidget);
    expect(find.text('Laguna Gadgets'), findsOneWidget);
    expect(find.textContaining('COD · collect'), findsOneWidget);
    expect(find.text('Scan another'), findsOneWidget);
  });

  testWidgets('a refused scan shows the reason from the server', (tester) async {
    await withServer(
      tester,
      (_) async => http.Response(
        jsonEncode({'message': "Parcel CTZ-ABC123 is assigned to another rider."}),
        422,
        headers: {'content-type': 'application/json'},
      ),
      () => pumpAndScan(tester, 'CTZ-ABC123'),
    );

    expect(find.text("Can't take this parcel"), findsOneWidget);
    expect(find.text('Parcel CTZ-ABC123 is assigned to another rider.'), findsOneWidget);
    expect(find.text('Scanned: CTZ-ABC123'), findsOneWidget);
  });
}

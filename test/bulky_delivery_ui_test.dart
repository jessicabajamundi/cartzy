import 'dart:convert';
import 'package:cartzy/rider/courier_dashboard.dart';
import 'package:cartzy/services/courier_service.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  testWidgets('van courier can open assigned bulky buyer deliveries',
      (tester) async {
    tester.view.physicalSize = const Size(800, 1400);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);
    var queueRequests = 0;
    final client = MockClient((request) async {
      if (request.url.path.endsWith('/rider/deliveries')) {
        queueRequests++;
        return http.Response(
            jsonEncode({
              'deliveries': [
                {
                  'id': 7,
                  'tracking_code': 'CTZ-COUCH7',
                  'status': 'assigned',
                  'recipient_name': 'Buyer',
                  'seller_name': 'Furniture Store',
                  'items_summary': '1x Couch',
                  'package_profile': {'bulky': true},
                }
              ],
              'pickups': [],
              'cod_to_collect': 0
            }),
            200);
      }
      return http.Response('{"items":[],"unread_count":0}', 200);
    });
    await http.runWithClient(() async {
      await tester.pumpWidget(MaterialApp(
          home: CourierDashboard(
        token: 'test',
        onLogout: () {},
        loadDashboard: () async => CourierDashboardData.fromJson({
          'courier': {
            'name': 'Van Driver',
            'vehicle_label': 'Van',
            'status': 'approved'
          },
          'today': {},
          'shift': {},
          'pickups': [],
          'in_vehicle': [],
          'handed_to_hub': [],
          'linehaul_transfers': [],
        }),
      )));
      await tester.pumpAndSettle();
      await tester.scrollUntilVisible(
          find.text('Bulky pickups & buyer deliveries'), 200,
          scrollable: find.byType(Scrollable).first);
      await tester.tap(find.text('Bulky pickups & buyer deliveries'));
      await tester.pumpAndSettle();
      expect(queueRequests, 1);
      expect(find.text('CTZ-COUCH7'), findsOneWidget);
      expect(find.textContaining('BULKY · Van/truck required'), findsOneWidget);
      final layoutError = tester.takeException();
      if (layoutError is FlutterError) {
        debugPrint(layoutError.diagnostics
            .map((item) => item.toStringDeep())
            .join('\n'));
      }
      expect(layoutError, isNull);
      await tester.pumpWidget(const SizedBox.shrink());
    }, () => client);
  });
}

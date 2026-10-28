import 'dart:convert';

import 'package:cartzy/buyer/my_orders_screen.dart';
import 'package:cartzy/services/order_service.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

Map<String, dynamic> _order({
  required String ref,
  required int id,
  required String status,
  required String product,
  bool canCancel = false,
  String? cancellation,
}) =>
    {
      'id': ref,
      'seller_order_id': id,
      'tracking_code': 'TRK$id',
      'can_cancel': canCancel,
      'cancellation_status': cancellation,
      'report': null,
      'can_report': false,
      'store': 'Store $id',
      'status': status,
      'date': 'Oct 27, 2026',
      'total': 1280.5,
      'payment': 'GCash',
      'items': [
        {'id': id * 10, 'name': product, 'variation': 'Blue', 'qty': 2, 'price': 640.25, 'image': '', 'rating': null},
      ],
    };

BuyerOrdersData _data({String? cancellation}) => BuyerOrdersData.fromJson({
      'orders': [
        _order(ref: 'CTZ-001', id: 1, status: 'to_pay', product: 'Coffee mug', canCancel: cancellation == null, cancellation: cancellation),
        _order(ref: 'CTZ-002', id: 2, status: 'completed', product: 'Desk lamp'),
      ],
      'counts': {'to_pay': 1, 'completed': 1},
      'cancellation_reasons': ['I changed my mind', 'Other reason'],
      'report_categories': [],
      'report_resolutions': [],
      'contact_phone': '09171234567',
    });

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  testWidgets('shows website-style cards, filters and search', (tester) async {
    await tester.pumpWidget(MaterialApp(
      home: MyOrdersScreen(onBack: () {}, loadOrders: () async => _data()),
    ));
    await tester.pumpAndSettle();

    expect(find.text('2 orders'), findsOneWidget);
    expect(find.text('Coffee mug'), findsOneWidget);
    expect(find.text('Desk lamp'), findsOneWidget);
    expect(find.text('₱1,280.50'), findsNWidgets(2));
    expect(find.text('CTZ-001 · Oct 27, 2026 · Tracking TRK1'), findsOneWidget);
    // to_pay card: Cancel, Track, Pay now. Completed card: Track, Rate, Buy again.
    expect(find.text('Pay now'), findsOneWidget);
    expect(find.text('Cancel order'), findsOneWidget);
    expect(find.text('Rate'), findsOneWidget);
    expect(find.text('Buy again'), findsOneWidget);

    // Filter chips show their counts, like the website.
    final chips = find.byWidgetPredicate((w) => w is ListView && w.scrollDirection == Axis.horizontal);
    final completedChip = find.textContaining('Completed  1', findRichText: true);
    await tester.dragUntilVisible(completedChip, chips, const Offset(-150, 0));
    await tester.ensureVisible(completedChip);
    await tester.pumpAndSettle();
    await tester.tap(completedChip);
    await tester.pump();
    expect(find.text('Coffee mug'), findsNothing);
    expect(find.text('Desk lamp'), findsOneWidget);

    final allChip = find.textContaining('All  2', findRichText: true);
    await tester.dragUntilVisible(allChip, chips, const Offset(150, 0));
    await tester.ensureVisible(allChip);
    await tester.pumpAndSettle();
    await tester.tap(allChip);
    await tester.enterText(find.byType(TextField), 'mug');
    await tester.pump();
    expect(find.text('Coffee mug'), findsOneWidget);
    expect(find.text('Desk lamp'), findsNothing);

    await tester.enterText(find.byType(TextField), 'nothing like this');
    await tester.pump();
    expect(find.text('No matching orders.'), findsOneWidget);
  });

  testWidgets('cancel sends the chosen reason and reloads', (tester) async {
    Map<String, dynamic>? sent;
    var loads = 0;
    final client = MockClient((request) async {
      sent = {'path': request.url.path, ...jsonDecode(request.body) as Map<String, dynamic>};
      return http.Response('{"message": "Cancellation request sent."}', 200);
    });

    await http.runWithClient(() async {
      await tester.pumpWidget(MaterialApp(
        home: MyOrdersScreen(
          onBack: () {},
          loadOrders: () async => _data(cancellation: loads++ == 0 ? null : 'pending'),
        ),
      ));
      await tester.pumpAndSettle();

      await tester.tap(find.text('Cancel order'));
      await tester.pumpAndSettle();
      expect(find.text('Request cancellation'), findsOneWidget);

      await tester.tap(find.text('Other reason'));
      await tester.pump();
      await tester.tap(find.text('Send cancellation request'));
      await tester.pump();
      expect(find.text('Request cancellation'), findsOneWidget, reason: 'details are required for Other reason');

      await tester.enterText(find.byType(TextField).last, 'Ordered by mistake');
      await tester.pump();
      await tester.tap(find.text('Send cancellation request'));
      await tester.pumpAndSettle();
    }, () => client);
    await tester.pumpAndSettle(const Duration(seconds: 5));

    expect(sent, {
      'path': '/api/orders/1/cancel',
      'reason': 'Other reason',
      'reason_details': 'Ordered by mistake',
    });
    expect(find.text('Cancellation requested — awaiting seller approval.'), findsOneWidget);
    expect(find.text('Cancel order'), findsNothing);
  });

  testWidgets('shows a retry card when orders fail to load', (tester) async {
    var calls = 0;
    await tester.pumpWidget(MaterialApp(
      home: MyOrdersScreen(
        onBack: () {},
        loadOrders: () async {
          if (calls++ == 0) throw Exception('offline');
          return _data();
        },
      ),
    ));
    await tester.pumpAndSettle();
    expect(find.text("Couldn't load your orders"), findsOneWidget);

    await tester.tap(find.text('Try again'));
    await tester.pumpAndSettle();
    expect(find.text('Coffee mug'), findsOneWidget);
  });
}

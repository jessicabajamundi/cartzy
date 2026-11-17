import 'package:cartzy/buyer/order_tracking_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  testWidgets('live timeline replaces seller waiting with confirmation then cancellation', (tester) async {
    tester.view.physicalSize = const Size(1000, 3000);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);
    var stage = 0;
    await tester.pumpWidget(MaterialApp(home: OrderTrackingScreen(
      sellerOrderId: 43,
      reference: 'ORD-PROGRESS-43',
      loadTracking: (_) async => {
        'current_status': stage == 0 ? 'Order placed' : (stage == 1 ? 'Order confirmed' : 'Order cancelled'),
        'steps': [
          {'label': 'Order placed', 'time': '10:35 AM', 'done': true},
          if (stage == 0)
            {'label': 'Awaiting seller confirmation', 'time': 'Waiting for seller', 'done': false, 'current': true}
          else ...[
            {'label': 'Order confirmed', 'time': '10:42 AM', 'done': true},
            if (stage == 1)
              {'label': 'Preparing your order', 'time': 'Awaiting update', 'done': false, 'current': true}
            else
              {'label': 'Order cancelled', 'detail': 'Buyer cancellation approved', 'time': '10:45 AM',
                'done': true, 'current': true, 'error': true},
          ],
        ],
        'map': {'is_live': false, 'route': List.generate(12, (i) => [14.5 + i * 0.001, 121.0 + i * 0.001])},
      },
    )));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 100));
    expect(find.text('Awaiting seller confirmation'), findsOneWidget);
    expect(find.text('Preparing your order'), findsNothing);
    expect(find.text('Pending'), findsOneWidget);

    stage = 1;
    await tester.pump(const Duration(seconds: 3));
    await tester.pump();
    expect(find.text('Awaiting seller confirmation'), findsNothing);
    expect(find.text('Preparing your order'), findsOneWidget);

    stage = 2;
    await tester.pump(const Duration(seconds: 3));
    await tester.pump();
    expect(find.text('Preparing your order'), findsNothing);
    expect(find.text('Stopped'), findsOneWidget);
    expect(find.text('Buyer cancellation approved'), findsOneWidget);
    await tester.pumpWidget(const SizedBox.shrink());
  });

  testWidgets('poll applies pickup confirmation and crew changes in the same minute', (tester) async {
    tester.view.physicalSize = const Size(1000, 2400);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);
    var requests = 0;
    await tester.pumpWidget(MaterialApp(home: OrderTrackingScreen(
      sellerOrderId: 42,
      reference: 'ORD-LIVE-42',
      loadTracking: (_) async {
        requests++;
        return {
          'current_status': 'Pickup schedule confirmed',
          'last_update': 'Nov 16, 2026 · 04:00 PM',
          'pickup_rider': requests == 1 ? 'Not assigned yet' : 'Maria Pickup',
          'hub_to_hub_courier': 'Pedro Transfer',
          'rider': 'Not assigned yet',
          'steps': [
            {'label': 'Pickup schedule confirmed', 'detail': requests == 1 ? 'Original confirmation' : 'Updated confirmation',
              'time': 'Nov 16, 2026 · 04:00 PM', 'done': true, 'current': true},
          ],
          'map': {
            'is_live': false,
            'route': List.generate(12, (i) => [14.5 + i * 0.001, 121.0 + i * 0.001]),
          },
        };
      },
    )));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 100));
    expect(requests, 1);
    await tester.pump(const Duration(seconds: 3));
    await tester.pump();
    expect(requests, 2);
    expect(find.text('Maria Pickup'), findsOneWidget);
    expect(find.text('Updated confirmation'), findsOneWidget);
    expect(find.text('Original confirmation'), findsNothing);
    expect(find.text('Hub-to-Hub courier'), findsOneWidget);
    await tester.pumpWidget(const SizedBox.shrink());
  });
}

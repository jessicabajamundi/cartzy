import 'package:cartzy/rider/rider_delivery_dashboard.dart';
import 'package:cartzy/rider/rider_delivery_process.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/rider_delivery_service.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

RiderDelivery _parcel({String status = 'assigned'}) => RiderDelivery(
      id: 7,
      trackingCode: 'CTZ-DLV42',
      orderReference: 'ORD-2026-30042',
      status: status,
      recipientName: 'Juan Dela Cruz',
      recipientPhone: '09171234567',
      recipientAddress: '45 Magsaysay St, Santa Cruz, Laguna',
      sellerName: 'Laguna Gadgets',
      itemsCount: 1,
      itemsSummary: '1x Earbuds',
      isCod: true,
      codAmount: 250,
    );

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  Future<void> phone(WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.625;
    addTearDown(tester.view.reset);
  }

  testWidgets('Start Delivery saves "out for delivery" on the server, then the card can be delivered', (tester) async {
    await phone(tester);
    var serverStatus = 'assigned';
    final started = <String>[];

    await tester.pumpWidget(MaterialApp(
      home: RiderDeliveryDashboard(
        token: '',
        onBack: () {},
        loadQueue: () async => RiderDeliveryQueue(deliveries: [_parcel(status: serverStatus)], codToCollect: 250),
        startDelivery: (d) async {
          started.add(d.trackingCode);
          serverStatus = 'out_for_delivery';
          return 'Parcel ${d.trackingCode} is out for delivery with you.';
        },
      ),
    ));
    await tester.pump();

    expect(find.text('CTZ-DLV42'), findsOneWidget);
    expect(find.text('AT HUB · ASSIGNED'), findsOneWidget);
    expect(find.text('Start Delivery'), findsOneWidget);

    await tester.tap(find.text('Start Delivery'));
    await tester.pump();
    await tester.pump();

    expect(started, ['CTZ-DLV42']);
    expect(find.text('OUT FOR DELIVERY'), findsOneWidget);
    expect(find.text('Confirm Delivery'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  testWidgets('a server refusal is shown and nothing changes', (tester) async {
    await phone(tester);
    await tester.pumpWidget(MaterialApp(
      home: RiderDeliveryDashboard(
        token: '',
        onBack: () {},
        loadQueue: () async => RiderDeliveryQueue(deliveries: [_parcel()]),
        startDelivery: (d) async => throw ApiException(422, 'Parcel CTZ-DLV42 is assigned to another rider.'),
      ),
    ));
    await tester.pump();
    await tester.tap(find.text('Start Delivery'));
    await tester.pump();
    await tester.pump();

    expect(find.text('Parcel CTZ-DLV42 is assigned to another rider.'), findsOneWidget);
    expect(find.text('AT HUB · ASSIGNED'), findsOneWidget);
  });

  testWidgets('no assigned parcels shows an empty queue, not sample orders', (tester) async {
    await phone(tester);
    await tester.pumpWidget(MaterialApp(
      home: RiderDeliveryDashboard(token: '', onBack: () {}, loadQueue: () async => const RiderDeliveryQueue(deliveries: [])),
    ));
    await tester.pump();
    expect(find.text('No active parcels to deliver'), findsOneWidget);
    expect(find.textContaining('CTZY-RD-'), findsNothing);
  });

  testWidgets('delivery needs this parcel\'s waybill, then is saved with the code and receiver', (tester) async {
    await phone(tester);
    final saved = <List<String>>[];
    await tester.pumpWidget(MaterialApp(
      home: RiderDeliverProcess(
        delivery: _parcel(status: 'out_for_delivery'),
        showCamera: false,
        markDelivered: (d, code, by, note) async {
          saved.add([code, by]);
          return 'Delivered.';
        },
      ),
    ));
    await tester.pump();

    expect(find.text('SCAN THE WAYBILL FIRST'), findsOneWidget);

    await tester.enterText(find.byType(TextField).first, 'CTZ-OTHER1');
    await tester.tap(find.text('Lookup'));
    await tester.pump();
    expect(find.textContaining("doesn't match this parcel"), findsOneWidget);
    expect(find.text('SCAN THE WAYBILL FIRST'), findsOneWidget);

    // A QR code holding a link that ends in the tracking code also works.
    await tester.enterText(find.byType(TextField).first, 'https://cartzy.example/track/CTZ-DLV42');
    await tester.tap(find.text('Lookup'));
    await tester.pump();
    expect(find.text('Waybill verified for this delivery.'), findsOneWidget);

    await tester.tap(find.text('CONFIRM DELIVERY (NOTIFY SELLER & BUYER)'));
    await tester.pump();
    expect(saved, [
      ['https://cartzy.example/track/CTZ-DLV42', 'Juan Dela Cruz']
    ]);
  });

  testWidgets('"Couldn\'t deliver it" saves the reason', (tester) async {
    await phone(tester);
    final reasons = <String>[];
    await tester.pumpWidget(MaterialApp(
      home: RiderDeliverProcess(
        delivery: _parcel(status: 'out_for_delivery'),
        showCamera: false,
        markFailed: (d, reason) async {
          reasons.add(reason);
          return 'Marked.';
        },
      ),
    ));
    await tester.pump();
    await tester.tap(find.text("Couldn't deliver it"));
    await tester.pump();
    await tester.tap(find.text('Buyer not home'));
    await tester.pump();
    expect(reasons, ['Buyer not home']);
  });
}

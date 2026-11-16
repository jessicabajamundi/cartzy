import 'package:cartzy/rider/rider_chat_list_screen.dart';
import 'package:cartzy/rider/rider_delivery_history_screen.dart';
import 'package:cartzy/rider/rider_earnings_dashboard.dart';
import 'package:cartzy/rider/rider_order_detail.dart';
import 'package:cartzy/services/review_service.dart';
import 'package:cartzy/services/rider_records_service.dart';
import 'package:cartzy/services/user_account_service.dart';
import 'package:cartzy/widgets/gcash_checkout_sheet.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  test('fresh services have no sample orders, reviews or demo accounts', () {
    expect(ReviewService.instance.orders, isEmpty);
    expect(ReviewService.instance.allReviews, isEmpty);
    expect(UserAccountService.instance.allAccounts, isEmpty);
  });

  Future<void> mount(WidgetTester tester, Widget screen) async {
    tester.view.physicalSize = const Size(390, 900);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);
    await tester.pumpWidget(MaterialApp(home: screen));
    await tester.pump(const Duration(milliseconds: 300));
  }

  RiderOrder record() => RiderOrder(
        id: 92,
        status: 'completed',
        buyerName: 'Actual customer',
        buyerPhone: '',
        deliveryAddress: 'Saved customer address',
        itemsSummary: 'Real product',
        totalAmount: 150,
        createdAt: '2026-11-01T10:00:00Z',
        completedAt: DateTime.now().toIso8601String(),
        riderPayout: 37,
        trackingCode: 'CTZ-ACTUAL-92',
        orderReference: 'ORD-ACTUAL-92',
      );

  testWidgets('history renders server records with genuine tracking references',
      (tester) async {
    await mount(
        tester,
        RiderDeliveryHistoryScreen(
            token: 'test',
            loadRecords: () async =>
                RiderRecords(orders: [record()], hubName: 'Actual Hub')));
    expect(find.text('Actual customer'), findsWidgets);
    expect(find.text('CTZ-ACTUAL-92'), findsWidgets);
    expect(find.textContaining('CTZY-RD-'), findsNothing);
    expect(tester.takeException(), isNull);
    await tester.pumpWidget(const SizedBox());
  });

  testWidgets('history load failure is shown as an error with retry',
      (tester) async {
    await mount(
        tester,
        RiderDeliveryHistoryScreen(
            token: 'test',
            loadRecords: () async => throw Exception('offline')));
    expect(
        find.textContaining('Could not load delivery history'), findsOneWidget);
    expect(find.text('Retry'), findsOneWidget);
    expect(find.text('No completed deliveries found.'), findsNothing);
    await tester.pumpWidget(const SizedBox());
  });

  testWidgets('earnings use returned records and server delivery fee',
      (tester) async {
    await mount(
        tester,
        RiderEarningsScreen(
            token: 'test',
            onBack: () {},
            loadRecords: () async => RiderRecords(
                orders: [record()],
                hubName: 'Actual Hub',
                feePerDelivery: 37)));
    expect(find.text('Actual customer'), findsWidgets);
    expect(find.textContaining('37.00 per completed drop'), findsOneWidget);
    expect(find.textContaining('not payout receipts'), findsOneWidget);
    expect(tester.takeException(), isNull);
    await tester.pumpWidget(const SizedBox());
  });

  testWidgets('dispatch shows only saved messages and does not invent replies',
      (tester) async {
    final messages = <Map<String, dynamic>>[];
    await mount(
        tester,
        RiderChatListScreen(
            token: 'test',
            myUserId: 1,
            onBack: () {},
            loadMessages: () async =>
                {'hub_name': 'Actual Hub', 'messages': messages},
            sendMessage: (text) async {
              messages.add({
                'id': 1,
                'body': text,
                'is_me': true,
                'created_at': DateTime.now().toIso8601String()
              });
            }));
    expect(find.textContaining('Route assignments updated'), findsNothing);
    await tester.enterText(find.byType(TextField), 'Actual saved update');
    await tester.testTextInput.receiveAction(TextInputAction.send);
    await tester.pump(const Duration(milliseconds: 300));
    expect(find.text('Actual saved update'), findsOneWidget);
    await tester.pump(const Duration(seconds: 2));
    expect(find.textContaining('Central dispatch has noted'), findsNothing);
    expect(messages, hasLength(1));
    await tester.pumpWidget(const SizedBox());
  });

  testWidgets('failed GCash lookup never shows a placeholder recipient',
      (tester) async {
    var confirmed = false;
    await mount(
        tester,
        Scaffold(
            body: GCashCheckoutSheet(
                totalAmount: 100,
                productIds: const [1],
                onConfirmOrder: (_, __) async {
                  confirmed = true;
                },
                loadPaymentInfo: () async => throw Exception('offline'))));
    expect(
        find.textContaining('payment details are unavailable'), findsOneWidget);
    expect(find.text('Retry payment details'), findsOneWidget);
    expect(find.textContaining('0917-555-2278'), findsNothing);
    expect(confirmed, isFalse);
    expect(tester.takeException(), isNull);
    await tester.pumpWidget(const SizedBox());
  });
}

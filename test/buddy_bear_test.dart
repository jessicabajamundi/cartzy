import 'dart:convert';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:cartzy/buddy/buddy_chat_screen.dart';
import 'package:cartzy/buyer/account_settings_screen.dart';
import 'package:cartzy/buyer/inbox_screen.dart';
import 'package:cartzy/products/product_list_screen.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/buddy_service.dart';
import 'package:cartzy/widgets/buddy_bear.dart';

void main() {
  setUp(() {
    TestWidgetsFlutterBinding.ensureInitialized();
    SharedPreferences.setMockInitialValues({});
    AuthService.clearSession();
    BuddyService.reset();
    BuddyLauncher.restore();
  });

  test('unknown moods fall back to happy', () {
    expect(BuddyMood.parse('love'), BuddyMood.love);
    expect(BuddyMood.parse('grumpy'), BuddyMood.happy);
  });

  test('reply parsing tolerates missing and null fields', () {
    final reply = BuddyReply.fromJson(jsonDecode('''{
      "reply": "Look!", "mood": "excited", "suggestions": ["More"],
      "products": [{"id": 3, "name": "Candle", "price": 250, "regular_price": null,
        "image": null, "rating": null, "sold": 0, "in_stock": false}]
    }''') as Map<String, dynamic>);

    expect(reply.text, 'Look!');
    expect(reply.products.single.price, 250);
    expect(reply.products.single.inStock, isFalse);
    expect(reply.products.single.image, '');
  });

  test('chat history does not carry over to another account', () {
    BuddyService.messages.add(const BuddyMessage(fromUser: true, text: 'Where is my order?'));
    AuthService.setSession(user: {'id': 7, 'name': 'Someone'}, token: 'token');

    expect(BuddyService.messages, isEmpty);
  });

  test('eyes wander over time', () async {
    Future<List<int>> frame(double seconds) async {
      final recorder = ui.PictureRecorder();
      BuddyBearPainter(mood: BuddyMood.happy, seconds: seconds)
          .paint(Canvas(recorder), const Size(200, 200));
      final image = await recorder.endRecording().toImage(200, 200);
      final eyes = await image.toByteData(format: ui.ImageByteFormat.rawRgba);
      // The row of pixels through the eyes.
      return eyes!.buffer.asUint8List(80 * 200 * 4, 200 * 4);
    }

    expect(await frame(1.0), isNot(equals(await frame(3.0))));
  });

  testWidgets('every mood paints without error', (tester) async {
    for (final mood in BuddyMood.values) {
      await tester.pumpWidget(MaterialApp(home: Center(child: BuddyBear(size: 120, mood: mood))));
      await tester.pump(const Duration(milliseconds: 100));
      expect(tester.takeException(), isNull);
    }
  });

  testWidgets('chat screen greets by first name and offers starters on a small phone', (tester) async {
    tester.view.physicalSize = const Size(320, 568);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);
    AuthService.setSession(user: {'id': 7, 'name': 'Rey Santos'}, token: 'token');

    await tester.pumpWidget(const MaterialApp(home: BuddyChatScreen()));
    await tester.pump(const Duration(milliseconds: 100));

    expect(find.textContaining('Hi Rey!'), findsOneWidget);
    expect(find.text('Help me find a gift'), findsOneWidget);
    expect(tester.takeException(), isNull);

    await tester.pumpWidget(const SizedBox());
    await tester.pump(const Duration(seconds: 2));
  });

  testWidgets('BuddyChatScreen pops up as modal bottom sheet and can be closed', (tester) async {
    tester.view.physicalSize = const Size(360, 640);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);
    AuthService.setSession(user: {'id': 7, 'name': 'Rey Santos'}, token: 'token');

    await tester.pumpWidget(MaterialApp(
      home: Scaffold(
        body: Builder(
          builder: (ctx) => ElevatedButton(
            onPressed: () => BuddyChatScreen.showPopup(ctx),
            child: const Text('Open Cozy'),
          ),
        ),
      ),
    ));

    await tester.tap(find.text('Open Cozy'));
    for (int i = 0; i < 5; i++) {
      await tester.pump(const Duration(milliseconds: 100));
    }

    expect(find.textContaining('Hi Rey!'), findsOneWidget);
    expect(find.byTooltip('Close'), findsOneWidget);

    // Tap Close to dismiss popup
    await tester.tap(find.byTooltip('Close'));
    for (int i = 0; i < 5; i++) {
      await tester.pump(const Duration(milliseconds: 100));
    }

    expect(find.textContaining('Hi Rey!'), findsNothing);

    await tester.pumpWidget(const SizedBox());
    await tester.pump(const Duration(seconds: 2));
  });

  testWidgets('BuddyLauncher displays and can be dragged to discard on X target', (tester) async {
    tester.view.physicalSize = const Size(360, 640);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);

    BuddyLauncher.summon();

    await tester.pumpWidget(const MaterialApp(
      home: Scaffold(
        body: Stack(
          children: [
            BuddyLauncher(aboveNav: true),
          ],
        ),
      ),
    ));
    await tester.pump(const Duration(milliseconds: 100));

    expect(find.byType(BuddyBear), findsOneWidget);

    // Drag bear toward the discard zone (bottom center)
    await tester.drag(find.byType(BuddyBear), const Offset(-131, 6));
    // Release over discard
    await tester.pump(const Duration(milliseconds: 300));
    await tester.pump(const Duration(seconds: 3));

    expect(BuddyLauncher.isDismissed, isTrue);

    // Summon brings Cozy back.
    BuddyLauncher.summon();
    await tester.pump(const Duration(milliseconds: 300));

    expect(BuddyLauncher.isDismissed, isFalse);
    expect(find.byType(BuddyBear), findsOneWidget);

    await tester.pumpWidget(const SizedBox());
    await tester.pump(const Duration(seconds: 3));
  });

  testWidgets('Clicking Cozy card in Profile summons bear form; clicking Chat button opens chat; Cozy persists across Sale and Inbox tabs', (tester) async {
    tester.view.physicalSize = const Size(390, 844);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);

    BuddyLauncher.isDismissed = true;

    await tester.pumpWidget(MaterialApp(
      home: AccountSettingsScreen(
        isLoggedIn: true,
        userName: 'Jessie Buyer',
        userEmail: 'jessie@example.com',
        onBack: () {},
        onLogout: () {},
      ),
    ));
    await tester.pump(const Duration(milliseconds: 200));

    // Initially Cozy floating launcher is dismissed (only the small 42px card avatar is rendered).
    expect(BuddyLauncher.isDismissed, isTrue);

    // Scroll down to Cozy card in Profile
    final cozyCard = find.byKey(const ValueKey('profile-cozy-card'));
    await tester.ensureVisible(cozyCard);
    await tester.pump(const Duration(milliseconds: 200));

    // Tap the Cozy card (e.g., on the "Need shopping advice..." text) -> summons bear form, does NOT open chat modal
    await tester.tap(find.textContaining('Need shopping advice'));
    await tester.pump(const Duration(milliseconds: 200));

    expect(BuddyLauncher.isDismissed, isFalse);
    expect(find.text('Tap me to chat with Cozy! 💖'), findsOneWidget);
    // Chat modal is NOT open yet
    expect(find.byType(BuddyChatScreen), findsNothing);

    // Tap the Chat button on the Cozy card -> opens BuddyChatScreen modal
    final chatBtn = find.byKey(const ValueKey('profile-cozy-chat-btn'));
    await tester.tap(chatBtn);
    for (int i = 0; i < 5; i++) {
      await tester.pump(const Duration(milliseconds: 100));
    }
    expect(find.byType(BuddyChatScreen), findsOneWidget);

    // Close the chat modal
    await tester.tap(find.byTooltip('Close'));
    for (int i = 0; i < 5; i++) {
      await tester.pump(const Duration(milliseconds: 100));
    }
    expect(find.byType(BuddyChatScreen), findsNothing);

    // Switch to Sale tab (ProductListScreen) -> Cozy bear form is present on Sale tab
    await tester.pumpWidget(const MaterialApp(
      home: ProductListScreen(
        initialCategory: 'Flash Deals',
        title: 'Sale & Flash Deals',
      ),
    ));
    await tester.pump(const Duration(milliseconds: 200));
    expect(find.byType(BuddyLauncher), findsOneWidget);
    expect(find.byType(BuddyBear), findsOneWidget);

    // Switch to Inbox tab (InboxScreen) -> Cozy bear form is present on Inbox tab
    await tester.pumpWidget(const MaterialApp(
      home: InboxScreen(),
    ));
    await tester.pump(const Duration(milliseconds: 200));
    expect(find.byType(BuddyLauncher), findsOneWidget);
    expect(find.byType(BuddyBear), findsOneWidget);

    await tester.pumpWidget(const SizedBox());
    await tester.pump(const Duration(seconds: 5));
  });
}

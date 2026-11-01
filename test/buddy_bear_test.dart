import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:cartzy/buddy/buddy_chat_screen.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/buddy_service.dart';
import 'package:cartzy/widgets/buddy_bear.dart';

void main() {
  setUp(() {
    AuthService.clearSession();
    BuddyService.reset();
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
}

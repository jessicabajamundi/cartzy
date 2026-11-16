import 'dart:convert';

import 'package:cartzy/buyer/messages_screen.dart';
import 'package:cartzy/buyer/notifications_screen.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/notification_service.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  test('read updates preserve the order conversation target', () {
    final item = AppNotificationItem.fromJson({
      'id': 9, 'conversation_order_id': 42, 'order_reference': 'ORD-2026-12345',
    });
    expect(item.copyWith(isRead: true).conversationOrderId, 42);
    expect(AppNotificationItem.fromJson({'id': 10}).conversationOrderId, isNull);
  });

  testWidgets('new acceptance notification refreshes and opens the matching order chat', (tester) async {
    SharedPreferences.setMockInitialValues({});
    tester.view.physicalSize = const Size(390, 900);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);
    var loads = 0;
    var markedRead = false;
    var openedThread = false;
    final client = MockClient((request) async {
      Map<String, dynamic> response = {};
      if (request.url.path.endsWith('/notifications')) {
        loads++;
        response = {'notifications': loads == 1 ? [] : [{
          'id': 9, 'type': 'order_accepted', 'title': 'Order accepted by Maria Shop',
          'message': 'Maria Shop accepted order ORD-2026-12345. Order date: Nov 16, 2026.',
          'tag': 'orders', 'conversation_order_id': 42, 'order_reference': 'ORD-2026-12345',
          'is_read': false, 'time': 'Just now',
        }], 'unread_count': loads == 1 ? 0 : 1};
      } else if (request.url.path.endsWith('/notifications/9/read')) {
        markedRead = true;
        response = {'unread_count': 0};
      } else if (request.url.path.endsWith('/messages')) {
        response = {'conversations': [{
          'id': 42, 'type': 'order', 'key': 'order-42', 'store': 'Maria Shop',
          'order_reference': 'ORD-2026-12345', 'unread_count': 1,
        }]};
      } else if (request.url.path.endsWith('/messages/thread')) {
        openedThread = request.url.queryParameters['id'] == '42' && request.url.queryParameters['type'] == 'order';
        response = {'messages': [{
          'id': 70, 'body': 'Hi, Dear Customer!\n\nThank you for choosing Maria Shop!',
          'sender_id': 2, 'is_me': false, 'time': '04:00 PM', 'date': 'Nov 16, 2026',
        }]};
      }
      return http.Response(jsonEncode(response), 200, headers: {'content-type': 'application/json'});
    });
    await http.runWithClient(() async {
      AuthService.setSession(token: 'test-buyer-token');
      await tester.pumpWidget(MaterialApp(home: NotificationsScreen(onBack: () {})));
      await tester.pumpAndSettle();
      expect(find.text('Order accepted by Maria Shop'), findsNothing);
      await tester.pump(const Duration(seconds: 5));
      await tester.pumpAndSettle();
      expect(find.text('Order accepted by Maria Shop'), findsOneWidget);
      expect(tester.takeException(), isNull);
      await tester.tap(find.text('Order accepted by Maria Shop'));
      await tester.pumpAndSettle();
      expect(markedRead, isTrue);
      expect(openedThread, isTrue);
      expect(find.byType(MessagesScreen), findsOneWidget);
      expect(find.textContaining('Thank you for choosing Maria Shop!'), findsOneWidget);
      expect(tester.takeException(), isNull);
      await tester.pumpWidget(const SizedBox.shrink());
    }, () => client);
  });
}

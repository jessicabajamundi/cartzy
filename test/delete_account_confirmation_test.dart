import 'dart:convert';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/widgets/delete_account_section.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;
  setUp(() {
    SharedPreferences.setMockInitialValues({});
    AuthService.clearSession();
    AuthService.onAccountDeleted = null;
  });
  tearDown(AuthService.clearSession);

  for (final role in ['buyer', 'seller', 'logistics', 'rider']) {
    testWidgets(
        '$role can show password and cancel before explicitly confirming deletion',
        (tester) async {
      tester.view.physicalSize = const Size(800, 1400);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.reset);
      var requests = 0;
      final client = MockClient((request) async {
        if (request.url.path.endsWith('/account/delete')) {
          requests++;
          expect(jsonDecode(request.body)['password'], 'MyCartzyPassword123!');
        }
        return http.Response('{"success":true,"items":[]}', 200);
      });
      await http.runWithClient(() async {
        AuthService.setSession(
            user: {'id': 1, 'email': '$role@example.test', 'role': role},
            token: 'test-session');
        await tester.pumpWidget(
            const MaterialApp(home: Scaffold(body: DeleteAccountSection())));
        await tester.tap(find.text('DELETE ACCOUNT'));
        await tester.pumpAndSettle();
        final label =
            tester.widget<Text>(find.text('Confirm with your Cartzy password'));
        expect(label.style?.decoration, TextDecoration.underline);
        await tester.enterText(find.byType(TextField), 'MyCartzyPassword123!');
        expect(
            tester.widget<TextField>(find.byType(TextField)).obscureText, true);
        await tester.tap(find.byTooltip('Show Cartzy password'));
        await tester.pump();
        expect(tester.widget<TextField>(find.byType(TextField)).obscureText,
            false);
        expect(requests, 0);
        await tester.tap(find.widgetWithText(ElevatedButton, 'Delete'));
        await tester.pumpAndSettle();
        expect(find.text('Permanently delete your account?'), findsOneWidget);
        expect(find.textContaining('This action cannot be undone.'),
            findsOneWidget);
        expect(requests, 0);
        await tester.tap(find.widgetWithText(TextButton, 'Cancel').last);
        await tester.pumpAndSettle();
        expect(requests, 0);
        expect(AuthService.token, 'test-session');
        await tester.tap(find.widgetWithText(ElevatedButton, 'Delete'));
        await tester.pumpAndSettle();
        await tester.tap(find.text('Yes, delete my account'));
        await tester.pumpAndSettle();
        expect(requests, 1);
        expect(AuthService.token, isNull);
        await tester.pumpWidget(const SizedBox.shrink());
      }, () => client);
    });
  }

  testWidgets('admin does not receive account deletion controls',
      (tester) async {
    AuthService.setSession(user: {'role': 'admin'});
    await tester.pumpWidget(
        const MaterialApp(home: Scaffold(body: DeleteAccountSection())));
    expect(find.text('DELETE ACCOUNT'), findsNothing);
    await tester.pumpWidget(const SizedBox.shrink());
  });
}

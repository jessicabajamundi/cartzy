import 'dart:convert';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/widgets/delete_account_section.dart';
import 'package:cartzy/auth/forgot_password_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;
  setUp(() { SharedPreferences.setMockInitialValues({}); AuthService.clearSession(); });
  tearDown(AuthService.clearSession);

  testWidgets('Google buyer verifies email, sets Cartzy password, then confirms deletion', (tester) async {
    tester.view.physicalSize = const Size(800, 1600);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);
    var custom = false;
    var deleted = false;
    final user = <String, dynamic>{'id': 1, 'email': 'google@example.test', 'role': 'buyer', 'google_linked': true, 'has_custom_password': false};
    final client = MockClient((request) async {
      final path = request.url.path;
      if (path.endsWith('/auth/me')) return http.Response(jsonEncode({'user': {...user, 'has_custom_password': custom}}), 200);
      if (path.endsWith('/auth/password/reset')) {
        expect(request.headers['authorization'], 'Bearer google-session');
        custom = true;
        return http.Response(jsonEncode({'session_preserved': true, 'user': {...user, 'has_custom_password': true}}), 200);
      }
      if (path.endsWith('/account/delete')) {
        expect(jsonDecode(request.body)['password'], 'MyCartzyPass123!');
        deleted = true;
      }
      return http.Response('{"success":true,"mail_sent":true,"items":[]}', 200);
    });
    await http.runWithClient(() async {
      AuthService.setSession(user: user, token: 'google-session');
      await tester.pumpWidget(const MaterialApp(home: Scaffold(body: SingleChildScrollView(child: DeleteAccountSection()))));
      await tester.tap(find.text('DELETE ACCOUNT'));
      await tester.pumpAndSettle();
      expect(find.text('Cartzy password'), findsOneWidget);
      await tester.tap(find.text('Set/reset Cartzy password by email'));
      await tester.pumpAndSettle();
      expect(find.text('Set your Cartzy password'), findsOneWidget);
      await tester.tap(find.text('Send Reset Code'));
      await tester.pumpAndSettle();
      await tester.enterText(find.byType(TextField), '123456');
      await tester.tap(find.text('Verify Code'));
      await tester.pumpAndSettle();
      await tester.enterText(find.byType(TextField).at(0), 'MyCartzyPass123!');
      await tester.enterText(find.byType(TextField).at(1), 'MyCartzyPass123!');
      await tester.tap(find.text('Reset Password'));
      await tester.pumpAndSettle();
      expect(AuthService.currentUser?['has_custom_password'], true);
      expect(AuthService.token, 'google-session');
      expect(find.text('Delete your account?'), findsOneWidget);
      expect(deleted, false);
      await tester.enterText(find.byType(TextField), 'MyCartzyPass123!');
      await tester.tap(find.widgetWithText(ElevatedButton, 'Delete'));
      await tester.pumpAndSettle();
      expect(deleted, true);
      expect(AuthService.token, isNull);
      await tester.pumpWidget(const SizedBox.shrink());
    }, () => client);
  });

  testWidgets('legacy Google buyer can open email setup even when a password flag is absent', (tester) async {
    AuthService.setSession(user: {'id': 2, 'email': 'legacy@example.test', 'role': 'buyer'});
    await tester.pumpWidget(const MaterialApp(home: Scaffold(body: DeleteAccountSection())));
    await tester.tap(find.text('DELETE ACCOUNT'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Set/reset Cartzy password by email'));
    await tester.pumpAndSettle();
    expect(find.byType(ForgotPasswordScreen), findsOneWidget);
    expect(find.text('legacy@example.test'), findsOneWidget);
    await tester.pumpWidget(const SizedBox.shrink());
  });
}

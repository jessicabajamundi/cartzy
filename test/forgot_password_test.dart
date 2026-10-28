import 'dart:convert';

import 'package:cartzy/auth/forgot_password_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  testWidgets('courier resets a password: email, code, new password', (tester) async {
    final calls = <String, Map<String, dynamic>>{};
    final client = MockClient((request) async {
      calls[request.url.path] = jsonDecode(request.body) as Map<String, dynamic>;
      return http.Response('{"success": true}', 200);
    });

    String? poppedEmail;
    await http.runWithClient(() async {
      await tester.pumpWidget(MaterialApp(
        home: Builder(
          builder: (context) => TextButton(
            onPressed: () async {
              poppedEmail = await Navigator.of(context).push<String>(MaterialPageRoute(
                builder: (_) => const ForgotPasswordScreen(role: 'rider', initialEmail: 'Rider@Example.test '),
              ));
            },
            child: const Text('open'),
          ),
        ),
      ));
      await tester.tap(find.text('open'));
      await tester.pumpAndSettle();

      await tester.tap(find.text('Send Reset Code'));
      await tester.pump();
      await tester.pump();
      expect(calls['/api/auth/password/forgot'], {'email': 'rider@example.test', 'role': 'rider'});
      expect(find.text('Check your email'), findsOneWidget);

      await tester.enterText(find.byType(TextField), '123456');
      await tester.tap(find.text('Verify Code'));
      await tester.pump();
      await tester.pump();
      expect(find.text('Set a new password'), findsOneWidget);

      final fields = find.byType(TextField);
      await tester.enterText(fields.at(0), 'short');
      await tester.enterText(fields.at(1), 'short');
      await tester.tap(find.text('Reset Password'));
      await tester.pump();
      expect(find.text('Password must be at least 8 characters long.'), findsOneWidget);
      expect(calls.containsKey('/api/auth/password/reset'), isFalse);

      await tester.enterText(fields.at(0), 'new-password-1');
      await tester.enterText(fields.at(1), 'new-password-1');
      await tester.tap(find.text('Reset Password'));
      await tester.pump();
      await tester.pump();
    }, () => client);
    await tester.pumpAndSettle(const Duration(seconds: 5));

    expect(calls['/api/auth/password/reset'], {
      'email': 'rider@example.test',
      'otp': '123456',
      'password': 'new-password-1',
      'password_confirmation': 'new-password-1',
    });
    expect(poppedEmail, 'rider@example.test');
  });
}

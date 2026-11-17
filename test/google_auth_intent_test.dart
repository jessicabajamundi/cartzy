import 'dart:convert';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  setUp(() { SharedPreferences.setMockInitialValues({}); AuthService.clearSession(); });
  tearDown(AuthService.clearSession);

  for (final intent in ['login', 'register']) {
    test('Google $intent sends intent and identity proof, and does not sign in on rejection', () async {
      final client = MockClient((request) async {
        expect(request.url.path, '/api/auth/google');
        final body = jsonDecode(request.body) as Map;
        expect(body['intent'], intent);
        expect(body['id_token'], 'verified-google-id-token');
        return http.Response(jsonEncode({'message': intent == 'login'
            ? 'No Cartzy account found. Please register first.'
            : 'This Google email already has a Cartzy account. Please log in.'}), 422);
      });
      await http.runWithClient(() async {
        await expectLater(AuthService.loginWithGoogle(idToken: 'verified-google-id-token',
          email: 'buyer@gmail.com', intent: intent), throwsA(isA<ApiException>()));
        expect(AuthService.token, isNull);
        expect(AuthService.currentUser, isNull);
      }, () => client);
    });
  }

  test('successful Google login stores only the returned existing account session', () async {
    final client = MockClient((request) async => http.Response(jsonEncode({
      'token': 'existing-buyer-session', 'user': {'id': 7, 'email': 'buyer@gmail.com', 'role': 'buyer'},
    }), 200));
    await http.runWithClient(() async {
      await AuthService.loginWithGoogle(idToken: 'verified-google-id-token', email: 'buyer@gmail.com', intent: 'login');
      expect(AuthService.token, 'existing-buyer-session');
      expect(AuthService.currentUser?['id'], 7);
    }, () => client);
  });
}

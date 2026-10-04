import 'api_service.dart';

class AuthService {
  static Map<String, dynamic>? _currentUser;
  static String? _token;

  static Map<String, dynamic>? get currentUser => _currentUser;
  static String? get token => _token;
  static bool get isAuthenticated => _token != null || _currentUser != null;

  static void setSession({Map<String, dynamic>? user, String? token}) {
    _currentUser = user != null ? Map<String, dynamic>.from(user) : null;
    _token = token;
  }

  static void clearSession() {
    _currentUser = null;
    _token = null;
  }

  static Future<Map<String, dynamic>> login({
    required String email,
    required String password,
    required String role,
  }) async {
    final response = await ApiService.post(
      '/auth/login',
      {
        'email': email,
        'password': password,
        'role': role,
      },
    );

    if (response['token'] != null) {
      _token = response['token'].toString();
    }
    if (response['user'] != null && response['user'] is Map) {
      _currentUser = Map<String, dynamic>.from(response['user'] as Map);
    }

    return response;
  }

  static Future<Map<String, dynamic>> registerBuyer(Map<String, dynamic> data) async {
    final response = await ApiService.post('/auth/register/buyer', data);
    return response;
  }

  static Future<Map<String, dynamic>> registerRider(Map<String, dynamic> data) async {
    final response = await ApiService.post('/auth/register/rider', data);
    return response;
  }

  static Future<void> logout() async {
    try {
      if (_token != null) {
        await ApiService.post('/auth/logout', {}, token: _token);
      }
    } catch (_) {}
    clearSession();
  }
}

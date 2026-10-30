import 'api_service.dart';
import 'smtp_email_service.dart';

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

  static Future<Map<String, dynamic>> loginWithGoogle({
    required String email,
    String? name,
    String? googleId,
    String? avatar,
  }) async {
    final response = await ApiService.post(
      '/auth/google',
      {
        'email': email,
        if (name != null && name.isNotEmpty) 'name': name,
        if (googleId != null && googleId.isNotEmpty) 'google_id': googleId,
        if (avatar != null && avatar.isNotEmpty) 'avatar': avatar,
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

  /// Emails a 6-digit registration code. The server also rejects emails and
  /// phone numbers that already belong to an account.
  static Future<void> requestRegistrationCode({
    required String email,
    required String name,
    required String phone,
    String role = 'buyer',
  }) async {
    final response = await ApiService.post('/auth/otp/request', {
      'email': email,
      'name': name,
      'phone': phone,
      'role': role,
      'client_send': true,
    });

    if (response['mail_sent'] == false && response['otp_code'] != null) {
      await SmtpEmailService.sendOtpEmail(
        toEmail: email,
        name: name,
        otp: response['otp_code'].toString(),
      );
    }
  }

  /// Creates the buyer account once the emailed [otp] is entered, and signs
  /// them in. [data] holds first_name, last_name, email, phone (09XXXXXXXXX),
  /// birthday (YYYY-MM-DD) and password, plus an optional `address` map.
  static Future<Map<String, dynamic>> registerBuyer(
    Map<String, dynamic> data, {
    required String otp,
  }) async {
    final address = data['address'];
    final response = await ApiService.post('/auth/register/buyer', {
      ...Map<String, dynamic>.from(data)..remove('address'),
      'password_confirmation': data['password'],
      'otp': otp,
    });

    if (response['token'] != null) {
      _token = response['token'].toString();
    }
    if (response['user'] is Map) {
      _currentUser = Map<String, dynamic>.from(response['user'] as Map);
    }

    // The account exists now; a failed address save shouldn't undo signup.
    if (address is Map && _token != null) {
      try {
        await ApiService.post(
          '/addresses',
          {...Map<String, dynamic>.from(address), 'is_default': true},
          token: _token,
        );
      } catch (_) {}
    }

    return response;
  }

  /// Emails a 6-digit password reset code. [role] is 'buyer' or 'rider', so
  /// the server can turn away accounts that belong to the other login.
  static Future<void> requestPasswordReset({
    required String email,
    required String role,
  }) async {
    final response = await ApiService.post('/auth/password/forgot', {
      'email': email,
      'role': role,
      'client_send': true,
    });

    if (response['mail_sent'] == false && response['otp_code'] != null) {
      await SmtpEmailService.sendPasswordResetEmail(
        toEmail: email,
        name: response['name']?.toString() ?? '',
        otp: response['otp_code'].toString(),
      );
    }
  }

  static Future<void> verifyPasswordResetCode({
    required String email,
    required String otp,
  }) async {
    await ApiService.post('/auth/password/verify', {'email': email, 'otp': otp});
  }

  static Future<void> resetPassword({
    required String email,
    required String otp,
    required String password,
  }) async {
    await ApiService.post('/auth/password/reset', {
      'email': email,
      'otp': otp,
      'password': password,
      'password_confirmation': password,
    });
  }

  static Future<Map<String, dynamic>> registerRider(
    Map<String, String> data, {
    Map<String, String> files = const {},
  }) async {
    if (files.isNotEmpty) {
      return await ApiService.postMultipart(
        '/auth/register/rider',
        data,
        files: files,
      );
    }
    return await ApiService.post('/auth/register/rider', data);
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

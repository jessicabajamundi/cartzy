import 'api_service.dart';
import 'smtp_email_service.dart';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'wishlist_service.dart';
import 'cart_service.dart';

class AuthService {
  static Map<String, dynamic>? _currentUser;
  static String? _token;

  static const String _kAuthToken = 'cartzy_auth_token';
  static const String _kAuthUser = 'cartzy_auth_user';
  static const String _kAuthRole = 'cartzy_auth_role';

  static Map<String, dynamic>? get currentUser => _currentUser;
  static String? get token => _token;

  /// Registered vehicles determine the dashboard, including saved legacy sessions.
  static bool isCourierAccount(Map<String, dynamic>? user) {
    final vehicle = '${user?['vehicle_type'] ?? ''}'.toLowerCase().replaceAll(RegExp(r'[-_\s]'), '');
    if (['car', 'van', 'truck', 'cargo'].contains(vehicle)) return true;
    if (['motorcycle', 'tricycle', 'ebike'].contains(vehicle)) return false;
    return ['first_mile', 'linehaul'].contains(user?['courier_type']);
  }
  static bool get isAuthenticated => (_token != null && _token!.isNotEmpty) || _currentUser != null;
  static bool get isLoggedIn => isAuthenticated;

  /// The profile photo the buyer picked themselves, or null to show initials.
  /// A Google account photo saved at sign-in is never used as the default.
  static String? profilePhoto(Map? user) {
    final value = user?['avatar']?.toString().trim() ?? '';
    if (value.isEmpty || value.contains('googleusercontent.com')) return null;
    return value;
  }

  static void setSession({Map<String, dynamic>? user, String? token}) {
    _currentUser = user != null ? Map<String, dynamic>.from(user) : null;
    if (token != null && token.isNotEmpty) {
      _token = token;
    }
    _persistSession();
    CartService.instance.fetchCartFromServer(force: true);
  }

  static void updateCurrentUser(Map<String, dynamic> user) {
    if (_currentUser != null) {
      _currentUser = {..._currentUser!, ...user};
    } else {
      _currentUser = Map<String, dynamic>.from(user);
    }
    _persistSession();
  }

  static Future<void> _persistSession() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      if (_token != null && _token!.isNotEmpty) {
        await prefs.setString(_kAuthToken, _token!);
      }
      if (_currentUser != null) {
        await prefs.setString(_kAuthUser, jsonEncode(_currentUser));
        if (_currentUser!['role'] != null) {
          await prefs.setString(_kAuthRole, _currentUser!['role'].toString());
        }
      }
    } catch (_) {}
  }

  static Future<bool> restoreSession() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final savedToken = prefs.getString(_kAuthToken);
      final savedUserJson = prefs.getString(_kAuthUser);
      if (savedUserJson != null && savedUserJson.isNotEmpty) {
        _currentUser = Map<String, dynamic>.from(jsonDecode(savedUserJson) as Map);
        _token = savedToken;
        CartService.instance.fetchCartFromServer(force: true);
        return true;
      }
    } catch (_) {}
    return false;
  }

  static void clearSession() {
    _currentUser = null;
    _token = null;
    WishlistService.clear();
    CartService.instance.clearAll();
    SharedPreferences.getInstance().then((prefs) {
      prefs.remove(_kAuthToken);
      prefs.remove(_kAuthUser);
      prefs.remove(_kAuthRole);
    }).catchError((_) {});
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
    _persistSession();

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
    _persistSession();

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
    _persistSession();

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
    final response = await ApiService.post('/auth/password/reset', {
      'email': email,
      'otp': otp,
      'password': password,
      'password_confirmation': password,
    }, token: _token);
    if (response['session_preserved'] == true && response['user'] is Map) {
      updateCurrentUser(Map<String, dynamic>.from(response['user'] as Map));
    } else if (_currentUser?['email']?.toString().toLowerCase() == email.toLowerCase()) {
      clearSession();
    }
  }

  static Future<void> refreshCurrentUser() async {
    if ((_token ?? '').isEmpty) return;
    final response = await ApiService.get('/auth/me', token: _token);
    if (response['user'] is Map) updateCurrentUser(Map<String, dynamic>.from(response['user'] as Map));
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

  static Future<Map<String, dynamic>> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    if ((_token ?? '').isEmpty) {
      throw ApiException(401, 'Please sign in again before changing your Cartzy password.');
    }
    final response = await ApiService.post('/account/password', {
      if (currentPassword.isNotEmpty) 'current_password': currentPassword,
      'password': newPassword,
      'password_confirmation': confirmPassword,
    }, token: _token);
    if (response['user'] is Map) {
      updateCurrentUser(Map<String, dynamic>.from(response['user'] as Map));
    }
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

  /// Optional hook registered by the root app shell to reset navigation state
  /// when the user permanently deletes their account from any nested screen.
  static VoidCallback? onAccountDeleted;

  /// Permanently deletes the signed-in non-admin user's account on the server
  /// and clears the local session.
  static Future<void> deleteAccount(String password) async {
    await ApiService.post(
      '/account/delete',
      {'password': password},
      token: _token,
    );
    clearSession();
    onAccountDeleted?.call();
  }
}

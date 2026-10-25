import 'dart:convert';

import 'package:flutter/foundation.dart' show kReleaseMode;
import 'package:http/http.dart' as http;

import 'auth_service.dart';

/// Error returned by the Cartzy Laravel API, with the server's message.
class ApiException implements Exception {
  final int statusCode;
  final String message;
  final Map<String, dynamic> body;

  ApiException(this.statusCode, this.message, [this.body = const {}]);

  @override
  String toString() => message;
}

class ApiService {
  static const String _productionBaseUrl = 'https://cartzyy.shop/api';

  /// The server this build always uses, or empty to try the local
  /// development servers below:
  /// - `--dart-define=API_BASE_URL=...` wins when given.
  /// - Otherwise release builds (the APK people install) use the live site,
  ///   so they never depend on a developer's PC.
  /// - Debug runs (`flutter run`) use the local Laravel server.
  static const String _fixedBaseUrl = String.fromEnvironment('API_BASE_URL') != ''
      ? String.fromEnvironment('API_BASE_URL')
      : (kReleaseMode ? _productionBaseUrl : '');

  /// Active base URL.
  /// - Debug default 'http://127.0.0.1:8000/api' works on physical Android phones
  ///   over USB (via `adb reverse tcp:8000 tcp:8000`), as well as Desktop and Web.
  /// - Debug runs fall back to 'http://10.0.2.2:8000/api' for Android Emulators.
  static String baseUrl = _fixedBaseUrl.isNotEmpty
      ? _fixedBaseUrl
      : 'http://127.0.0.1:8000/api';

  static final List<String> _localCandidateUrls = [
    'http://127.0.0.1:8000/api',
    'http://192.168.100.188:8000/api',
    'http://10.0.2.2:8000/api',
  ];

  /// Rewrites loopback hosts (127.0.0.1 or localhost) in image URLs to match
  /// the active base URL host (e.g. 10.0.2.2 on Android emulator or Wi-Fi IP).
  static String normalizeImageUrl(String? url) {
    if (url == null || url.trim().isEmpty) return '';
    var trimmed = url.trim();
    if (trimmed.startsWith('http://127.0.0.1:8000') || trimmed.startsWith('http://localhost:8000')) {
      try {
        final apiUri = Uri.parse(baseUrl);
        final imageUri = Uri.parse(trimmed);
        if (apiUri.host.isNotEmpty && apiUri.host != '127.0.0.1' && apiUri.host != 'localhost') {
          return imageUri.replace(host: apiUri.host, port: apiUri.port).toString();
        }
      } catch (_) {}
    }
    return trimmed;
  }

  /// The API talks to a remote (cloud) database, so list endpoints like
  /// /products can take several seconds. Keep this generous.
  static const Duration _requestTimeout = Duration(seconds: 20);

  /// Helper to send requests with automatic fallback across local development candidates.
  static Future<http.Response> _sendWithFallback(
    Future<http.Response> Function(String base) requestFn,
  ) async {
    if (_fixedBaseUrl.isNotEmpty) {
      return await requestFn(_fixedBaseUrl).timeout(_requestTimeout);
    }

    try {
      return await requestFn(baseUrl).timeout(_requestTimeout);
    } catch (e) {
      for (final candidate in _localCandidateUrls) {
        if (candidate == baseUrl) continue;
        try {
          final res = await requestFn(candidate).timeout(_requestTimeout);
          baseUrl = candidate;
          return res;
        } catch (_) {}
      }
      rethrow;
    }
  }

  /// Turns a non-2xx response into an [ApiException] carrying Laravel's message.
  static Never _fail(http.Response response) {
    Map<String, dynamic> body = {};
    try {
      final decoded = jsonDecode(response.body);
      if (decoded is Map) body = Map<String, dynamic>.from(decoded);
    } catch (_) {}
    final errors = body['errors'];
    final firstError = errors is Map && errors.isNotEmpty && errors.values.first is List
        ? (errors.values.first as List).first.toString()
        : null;
    throw ApiException(
      response.statusCode,
      firstError ?? body['message']?.toString() ?? 'Server error (${response.statusCode}). Please try again.',
      body,
    );
  }

  // ==========================================================
  // GET (single object response, e.g. { user: {...} })
  // Pass `token` to attach an Authorization: Bearer header
  // for endpoints protected by requireAuth/requireRole.
  // ==========================================================

  static Future<Map<String, dynamic>> get(
    String endpoint, {
    String? token,
  }) async {
    final response = await _sendWithFallback((base) => http.get(
      Uri.parse('$base$endpoint'),
      headers: {
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
    ));

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      _fail(response);
    }

    if (response.body.isEmpty) {
      throw Exception(
        'Server returned an empty response.',
      );
    }

    try {
      final data = jsonDecode(response.body);

      return Map<String, dynamic>.from(data);
    } catch (e) {
      throw Exception(
        'Server did not return JSON. Response: ${response.body}',
      );
    }
  }

  // ==========================================================
  // GET LIST (array response, e.g. GET /riders/pending which
  // returns [ {...}, {...} ] directly, not wrapped in an object)
  // ==========================================================

  static Future<List<dynamic>> getList(
    String endpoint, {
    String? token,
  }) async {
    final response = await _sendWithFallback((base) => http.get(
      Uri.parse('$base$endpoint'),
      headers: {
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
    ));

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      _fail(response);
    }

    if (response.body.isEmpty) {
      return [];
    }

    try {
      final data = jsonDecode(response.body);

      return List<dynamic>.from(data);
    } catch (e) {
      throw Exception(
        'Server did not return a JSON list. Response: ${response.body}',
      );
    }
  }

  // ==========================================================
  // POST
  // ==========================================================

  static Future<Map<String, dynamic>> post(
    String endpoint,
    Map<String, dynamic> body, {
    String? token,
  }) async {
    final response = await _sendWithFallback((base) => http.post(
      Uri.parse('$base$endpoint'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
      body: jsonEncode(body),
    ));

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      _fail(response);
    }

    if (response.body.isEmpty) {
      return {};
    }

    final data = jsonDecode(response.body);

    return Map<String, dynamic>.from(data);
  }

  // ==========================================================
  // PUT
  // ==========================================================

  static Future<Map<String, dynamic>> put(
    String endpoint,
    Map<String, dynamic> body, {
    String? token,
  }) async {
    final response = await _sendWithFallback((base) => http.put(
      Uri.parse('$base$endpoint'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
      body: jsonEncode(body),
    ));

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      _fail(response);
    }

    if (response.body.isEmpty) {
      return {};
    }

    final data = jsonDecode(response.body);

    return Map<String, dynamic>.from(data);
  }

  // ==========================================================
  // PATCH
  // ==========================================================

  static Future<Map<String, dynamic>> patch(
    String endpoint,
    Map<String, dynamic> body, {
    String? token,
  }) async {
    final response = await _sendWithFallback((base) => http.patch(
      Uri.parse('$base$endpoint'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
      body: jsonEncode(body),
    ));

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      _fail(response);
    }

    if (response.body.isEmpty) {
      return {};
    }

    final data = jsonDecode(response.body);

    return Map<String, dynamic>.from(data);
  }

  // ==========================================================
  // DELETE
  // ==========================================================

  static Future<Map<String, dynamic>> delete(
    String endpoint, {
    String? token,
  }) async {
    final response = await _sendWithFallback((base) => http.delete(
      Uri.parse('$base$endpoint'),
      headers: {
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
    ));

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      _fail(response);
    }

    if (response.body.isEmpty) {
      return {};
    }

    final data = jsonDecode(response.body);

    return Map<String, dynamic>.from(data);
  }

  // ==========================================================
  // ADDRESSES - GET
  // ==========================================================

  static Future<List<dynamic>> getAddresses(
    int userId,
  ) async {
    final response = await get(
      '/addresses/$userId',
      token: AuthService.token,
    );

    return List<dynamic>.from(
      response['addresses'] ?? [],
    );
  }

  // ==========================================================
  // ADDRESSES - ADD
  // ==========================================================

  static Future<Map<String, dynamic>> addAddress(
    Map<String, dynamic> address,
  ) async {
    return await post(
      '/addresses',
      address,
      token: AuthService.token,
    );
  }

  // ==========================================================
  // ADDRESSES - UPDATE
  // ==========================================================

  static Future<Map<String, dynamic>> updateAddress(
    int addressId,
    Map<String, dynamic> address,
  ) async {
    return await put(
      '/addresses/$addressId',
      address,
      token: AuthService.token,
    );
  }

  // ==========================================================
  // ADDRESSES - SET DEFAULT
  // ==========================================================

  static Future<Map<String, dynamic>> setDefaultAddress(
    int addressId,
  ) async {
    return await put(
      '/addresses/$addressId/default',
      {},
      token: AuthService.token,
    );
  }

  // ==========================================================
  // ADDRESSES - DELETE
  // ==========================================================

  static Future<Map<String, dynamic>> deleteAddress(
    int addressId,
  ) async {
    return await delete(
      '/addresses/$addressId',
      token: AuthService.token,
    );
  }
}
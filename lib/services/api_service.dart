import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl =
      'http://localhost:4000/api';

  // ==========================================================
  // GET (single object response, e.g. { user: {...} })
  // Pass `token` to attach an Authorization: Bearer header
  // for endpoints protected by requireAuth/requireRole.
  // ==========================================================

  static Future<Map<String, dynamic>> get(
    String endpoint, {
    String? token,
  }) async {
    final response = await http.get(
      Uri.parse('$baseUrl$endpoint'),
      headers: {
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      throw Exception(
        'Server returned ${response.statusCode}: ${response.body}',
      );
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
    final response = await http.get(
      Uri.parse('$baseUrl$endpoint'),
      headers: {
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      throw Exception(
        'Server returned ${response.statusCode}: ${response.body}',
      );
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
    final response = await http.post(
      Uri.parse('$baseUrl$endpoint'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
      body: jsonEncode(body),
    );

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      throw Exception(
        'Server returned ${response.statusCode}: ${response.body}',
      );
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
    final response = await http.put(
      Uri.parse('$baseUrl$endpoint'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
      body: jsonEncode(body),
    );

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      throw Exception(
        'Server returned ${response.statusCode}: ${response.body}',
      );
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
    final response = await http.patch(
      Uri.parse('$baseUrl$endpoint'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
      body: jsonEncode(body),
    );

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      throw Exception(
        'Server returned ${response.statusCode}: ${response.body}',
      );
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
    final response = await http.delete(
      Uri.parse('$baseUrl$endpoint'),
      headers: {
        'Accept': 'application/json',
        if (token != null && token.isNotEmpty)
          'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      throw Exception(
        'Server returned ${response.statusCode}: ${response.body}',
      );
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
    );
  }
}
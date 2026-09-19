import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl =
      'http://localhost:4000/api';

  static Future<Map<String, dynamic>> get(
    String endpoint,
  ) async {
    final response = await http.get(
      Uri.parse(
        '$baseUrl$endpoint',
      ),
      headers: {
        'Accept': 'application/json',
      },
    );

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      throw Exception(
        'Server returned ${response.statusCode}: '
        '${response.body}',
      );
    }

    if (response.body.isEmpty) {
      throw Exception(
        'Server returned an empty response.',
      );
    }

    try {
      final data =
          jsonDecode(response.body);

      return Map<String, dynamic>.from(
        data,
      );
    } catch (e) {
      throw Exception(
        'Server did not return JSON. '
        'Response: ${response.body.substring(
          0,
          response.body.length > 200
              ? 200
              : response.body.length,
        )}',
      );
    }
  }

  static Future<Map<String, dynamic>> put(
    String endpoint,
    Map<String, dynamic> body,
  ) async {
    final response = await http.put(
      Uri.parse(
        '$baseUrl$endpoint',
      ),
      headers: {
        'Content-Type':
            'application/json',
        'Accept':
            'application/json',
      },
      body: jsonEncode(body),
    );

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      throw Exception(
        'Server returned ${response.statusCode}: '
        '${response.body}',
      );
    }

    if (response.body.isEmpty) {
      throw Exception(
        'Server returned an empty response.',
      );
    }

    try {
      final data =
          jsonDecode(response.body);

      return Map<String, dynamic>.from(
        data,
      );
    } catch (e) {
      throw Exception(
        'Server did not return JSON. '
        'Response: ${response.body.substring(
          0,
          response.body.length > 200
              ? 200
              : response.body.length,
        )}',
      );
    }
  }

  static Future<Map<String, dynamic>> post(
    String endpoint,
    Map<String, dynamic> body,
  ) async {
    final response = await http.post(
      Uri.parse(
        '$baseUrl$endpoint',
      ),
      headers: {
        'Content-Type':
            'application/json',
        'Accept':
            'application/json',
      },
      body: jsonEncode(body),
    );

    if (response.statusCode < 200 ||
        response.statusCode >= 300) {
      throw Exception(
        'Server returned ${response.statusCode}: '
        '${response.body}',
      );
    }

    final data =
        jsonDecode(response.body);

    return Map<String, dynamic>.from(
      data,
    );
  }
}
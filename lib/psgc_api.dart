import 'dart:convert';
import 'package:http/http.dart' as http;

// ============================================================
// PSGC API MODELS
// ============================================================

class PsgcRegion {
  final String code;
  final String name;

  PsgcRegion({
    required this.code,
    required this.name,
  });

  factory PsgcRegion.fromJson(
    Map<String, dynamic> json,
  ) {
    return PsgcRegion(
      code: json['code']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
    );
  }
}

class PsgcProvince {
  final String code;
  final String name;

  PsgcProvince({
    required this.code,
    required this.name,
  });

  factory PsgcProvince.fromJson(
    Map<String, dynamic> json,
  ) {
    return PsgcProvince(
      code: json['code']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
    );
  }
}

class PsgcCityMunicipality {
  final String code;
  final String name;

  PsgcCityMunicipality({
    required this.code,
    required this.name,
  });

  factory PsgcCityMunicipality.fromJson(
    Map<String, dynamic> json,
  ) {
    return PsgcCityMunicipality(
      code: json['code']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
    );
  }
}

class PsgcBarangay {
  final String code;
  final String name;

  PsgcBarangay({
    required this.code,
    required this.name,
  });

  factory PsgcBarangay.fromJson(
    Map<String, dynamic> json,
  ) {
    return PsgcBarangay(
      code: json['code']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
    );
  }
}

// ============================================================
// PSGC API SERVICE
// ============================================================

class PsgcApi {
  static const String _baseUrl =
      'https://psgc.gitlab.io/api';

  // ==========================================================
  // GET REGIONS
  // ==========================================================

  static Future<List<PsgcRegion>>
      getRegions() async {
    final response = await http.get(
      Uri.parse(
        '$_baseUrl/regions/',
      ),
    );

    if (response.statusCode != 200) {
      throw Exception(
        'Failed to load regions',
      );
    }

    final List<dynamic> data =
        jsonDecode(response.body);

    return data
        .map(
          (json) =>
              PsgcRegion.fromJson(json),
        )
        .toList();
  }

  // ==========================================================
  // GET PROVINCES
  // ==========================================================

  static Future<List<PsgcProvince>>
      getProvinces() async {
    final response = await http.get(
      Uri.parse(
        '$_baseUrl/provinces/',
      ),
    );

    if (response.statusCode != 200) {
      throw Exception(
        'Failed to load provinces',
      );
    }

    final List<dynamic> data =
        jsonDecode(response.body);

    return data
        .map(
          (json) =>
              PsgcProvince.fromJson(json),
        )
        .toList();
  }

  // ==========================================================
  // GET PROVINCES BY REGION
  // ==========================================================

  static Future<List<PsgcProvince>>
      getProvincesByRegion(
    String regionCode,
  ) async {
    final response = await http.get(
      Uri.parse(
        '$_baseUrl/regions/$regionCode/provinces/',
      ),
    );

    if (response.statusCode != 200) {
      throw Exception(
        'Failed to load provinces',
      );
    }

    final List<dynamic> data =
        jsonDecode(response.body);

    return data
        .map(
          (json) =>
              PsgcProvince.fromJson(json),
        )
        .toList();
  }

  // ==========================================================
  // GET CITIES / MUNICIPALITIES
  // ==========================================================

  static Future<List<PsgcCityMunicipality>>
      getMunicipalities(
    String provinceCode,
  ) async {
    final response = await http.get(
      Uri.parse(
        '$_baseUrl/provinces/$provinceCode/cities-municipalities/',
      ),
    );

    if (response.statusCode != 200) {
      throw Exception(
        'Failed to load municipalities',
      );
    }

    final List<dynamic> data =
        jsonDecode(response.body);

    return data
        .map(
          (json) =>
              PsgcCityMunicipality.fromJson(
            json,
          ),
        )
        .toList();
  }

  // ==========================================================
  // GET BARANGAYS
  // ==========================================================

  static Future<List<PsgcBarangay>>
      getBarangays(
    String municipalityCode,
  ) async {
    final response = await http.get(
      Uri.parse(
        '$_baseUrl/cities-municipalities/$municipalityCode/barangays/',
      ),
    );

    if (response.statusCode != 200) {
      throw Exception(
        'Failed to load barangays',
      );
    }

    final List<dynamic> data =
        jsonDecode(response.body);

    return data
        .map(
          (json) =>
              PsgcBarangay.fromJson(
            json,
          ),
        )
        .toList();
  }
}
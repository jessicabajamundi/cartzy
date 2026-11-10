import 'dart:convert';
import 'package:http/http.dart' as http;

// ============================================================
// PSGC API MODELS
// ============================================================

class PsgcRegion {
  final String code;
  final String name;
  final String? regionName;

  PsgcRegion({
    required this.code,
    required this.name,
    this.regionName,
  });

  factory PsgcRegion.fromJson(Map<String, dynamic> json) {
    return PsgcRegion(
      code: json['code']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      regionName: json['regionName']?.toString(),
    );
  }

  /// User-friendly label matching the website (e.g. "NCR – Metro Manila")
  String get displayName {
    if (regionName != null && regionName!.isNotEmpty) {
      return '$regionName – $name';
    }
    return name;
  }
}

class PsgcProvince {
  final String code;
  final String name;

  PsgcProvince({
    required this.code,
    required this.name,
  });

  factory PsgcProvince.fromJson(Map<String, dynamic> json) {
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

  factory PsgcCityMunicipality.fromJson(Map<String, dynamic> json) {
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

  factory PsgcBarangay.fromJson(Map<String, dynamic> json) {
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
  static const String _baseUrl = 'https://psgc.gitlab.io/api';
  static final Map<String, dynamic> _cache = {};

  /// Region code mapping matching the website
  static const Map<String, String> regionCodeMap = {
    'NCR': '130000000',
    'CAR': '140000000',
    'I': '010000000',
    'II': '020000000',
    'III': '030000000',
    'IV-A': '040000000',
    'IV-B': '170000000',
    'V': '050000000',
    'VI': '060000000',
    'VII': '070000000',
    'VIII': '080000000',
    'IX': '090000000',
    'X': '100000000',
    'XI': '110000000',
    'XII': '120000000',
    'XIII': '160000000',
    'BARMM': '150000000',
  };

  /// Display titles matching the website's regionList
  static const Map<String, String> regionDisplayList = {
    'NCR': 'NCR – Metro Manila',
    'CAR': 'CAR – Cordillera Administrative Region',
    'I': 'Region I – Ilocos Region',
    'II': 'Region II – Cagayan Valley',
    'III': 'Region III – Central Luzon',
    'IV-A': 'Region IV-A – CALABARZON',
    'IV-B': 'Region IV-B – MIMAROPA',
    'V': 'Region V – Bicol Region',
    'VI': 'Region VI – Western Visayas',
    'VII': 'Region VII – Central Visayas',
    'VIII': 'Region VIII – Eastern Visayas',
    'IX': 'Region IX – Zamboanga Peninsula',
    'X': 'Region X – Northern Mindanao',
    'XI': 'Region XI – Davao Region',
    'XII': 'Region XII – SOCCSKSARGEN',
    'XIII': 'Region XIII – CARAGA',
    'BARMM': 'BARMM – Bangsamoro',
  };

  static Future<dynamic> _fetch(String url) async {
    if (_cache.containsKey(url)) return _cache[url];
    try {
      final response = await http.get(
        Uri.parse(url),
        headers: {'User-Agent': 'CartzyMobile/1.0'},
      ).timeout(const Duration(seconds: 15));

      if (response.statusCode != 200) {
        throw Exception('HTTP ${response.statusCode}');
      }
      final decoded = jsonDecode(response.body);
      _cache[url] = decoded;
      return decoded;
    } catch (e) {
      throw Exception('Failed to load PSGC data from $url: $e');
    }
  }

  // ==========================================================
  // GET REGIONS
  // ==========================================================

  static Future<List<PsgcRegion>> getRegions() async {
    final data = await _fetch('$_baseUrl/regions.json') as List<dynamic>;
    final list = data.map((json) => PsgcRegion.fromJson(json)).toList();
    list.sort((a, b) => a.name.compareTo(b.name));
    return list;
  }

  // ==========================================================
  // GET PROVINCES BY REGION
  // ==========================================================

  /// Every province, plus Metro Manila (which has no provinces in PSGC).
  static Future<List<PsgcProvince>> getAllProvinces() async {
    final data = await _fetch('$_baseUrl/provinces.json') as List<dynamic>;
    final list = data.map((json) => PsgcProvince.fromJson(json)).toList()
      ..add(PsgcProvince(code: '130000000', name: 'Metro Manila'));
    list.sort((a, b) => a.name.compareTo(b.name));
    return list;
  }

  static Future<List<PsgcProvince>> getProvincesByRegion(String regionCode) async {
    // In Philippine PSGC, NCR has no provinces, only cities under region 130000000.
    if (regionCode == '130000000' || regionCode.toUpperCase() == 'NCR') {
      return [
        PsgcProvince(code: '130000000', name: 'Metro Manila'),
      ];
    }

    final data = await _fetch('$_baseUrl/regions/$regionCode/provinces.json') as List<dynamic>;
    final list = data.map((json) => PsgcProvince.fromJson(json)).toList();
    list.sort((a, b) => a.name.compareTo(b.name));
    return list;
  }

  // ==========================================================
  // GET CITIES / MUNICIPALITIES
  // ==========================================================

  static Future<List<PsgcCityMunicipality>> getMunicipalities(
    String code, {
    bool isNcrRegion = false,
  }) async {
    final url = (isNcrRegion || code == '130000000')
        ? '$_baseUrl/regions/130000000/cities-municipalities.json'
        : '$_baseUrl/provinces/$code/cities-municipalities.json';

    final data = await _fetch(url) as List<dynamic>;
    final list = data.map((json) => PsgcCityMunicipality.fromJson(json)).toList();
    list.sort((a, b) => a.name.compareTo(b.name));
    return list;
  }

  // ==========================================================
  // GET BARANGAYS
  // ==========================================================

  static Future<List<PsgcBarangay>> getBarangays(String municipalityCode) async {
    final data = await _fetch('$_baseUrl/cities-municipalities/$municipalityCode/barangays.json') as List<dynamic>;
    final list = data.map((json) => PsgcBarangay.fromJson(json)).toList();
    list.sort((a, b) => a.name.compareTo(b.name));
    return list;
  }
}

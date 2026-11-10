import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/psgc_api.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Where a rider or courier lives. The hub gives them pickups and deliveries
/// in their own barangay first.
class HomeAddress {
  final String barangay;
  final String city;
  final String province;

  const HomeAddress({required this.barangay, required this.city, required this.province});

  bool get isComplete => barangay.trim().isNotEmpty && city.trim().isNotEmpty && province.trim().isNotEmpty;

  /// "Bubukal, Santa Cruz, Laguna"
  String get label => [barangay, city, province].where((p) => p.trim().isNotEmpty).join(', ');

  Map<String, String> toFields() => {'barangay': barangay.trim(), 'city': city.trim(), 'province': province.trim()};

  static HomeAddress? fromJson(Object? json) {
    if (json is! Map) return null;
    String s(Object? v) => v?.toString().trim() ?? '';
    final home = HomeAddress(barangay: s(json['barangay']), city: s(json['city']), province: s(json['province']));
    return home.label.isEmpty ? null : home;
  }
}

class RiderHomeAddressService {
  static Future<HomeAddress?> save(HomeAddress home) async {
    final response = await ApiService.put('/rider/home-address', home.toFields(), token: AuthService.token);
    return HomeAddress.fromJson(response['home_address']);
  }
}

/// Province, city and barangay pickers from the PSGC list (the same names the
/// website uses). If the list can't load, the rider types the names instead.
class HomeAddressFields extends StatefulWidget {
  final HomeAddress? initial;
  final ValueChanged<HomeAddress> onChanged;
  final bool showError;

  const HomeAddressFields({super.key, this.initial, required this.onChanged, this.showError = false});

  @override
  State<HomeAddressFields> createState() => _HomeAddressFieldsState();
}

class _HomeAddressFieldsState extends State<HomeAddressFields> {
  List<PsgcProvince> _provinces = [];
  List<PsgcCityMunicipality> _cities = [];
  List<PsgcBarangay> _barangays = [];
  String? _province;
  String? _city;
  String? _barangay;
  bool _loading = true;
  bool _typeInstead = false;

  final _provinceText = TextEditingController();
  final _cityText = TextEditingController();
  final _barangayText = TextEditingController();

  @override
  void initState() {
    super.initState();
    final initial = widget.initial;
    _provinceText.text = initial?.province ?? '';
    _cityText.text = initial?.city ?? '';
    _barangayText.text = initial?.barangay ?? '';
    _loadProvinces();
  }

  @override
  void dispose() {
    _provinceText.dispose();
    _cityText.dispose();
    _barangayText.dispose();
    super.dispose();
  }

  Future<void> _loadProvinces() async {
    try {
      final provinces = await PsgcApi.getAllProvinces();
      if (!mounted) return;
      setState(() {
        _provinces = provinces;
        _loading = false;
      });
      final initial = widget.initial;
      if (initial != null && provinces.any((p) => p.name == initial.province)) {
        await _pickProvince(initial.province, keepCity: initial.city, keepBarangay: initial.barangay);
      }
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _typeInstead = true;
      });
    }
  }

  Future<void> _pickProvince(String? name, {String? keepCity, String? keepBarangay}) async {
    setState(() {
      _province = name;
      _city = null;
      _barangay = null;
      _cities = [];
      _barangays = [];
    });
    _emit();
    final code = _provinces.where((p) => p.name == name).firstOrNull?.code;
    if (code == null) return;
    try {
      final cities = await PsgcApi.getMunicipalities(code);
      if (!mounted || _province != name) return;
      setState(() => _cities = cities);
      if (keepCity != null && cities.any((c) => c.name == keepCity)) {
        await _pickCity(keepCity, keepBarangay: keepBarangay);
      }
    } catch (_) {
      if (mounted) setState(() => _typeInstead = true);
    }
  }

  Future<void> _pickCity(String? name, {String? keepBarangay}) async {
    setState(() {
      _city = name;
      _barangay = null;
      _barangays = [];
    });
    _emit();
    final code = _cities.where((c) => c.name == name).firstOrNull?.code;
    if (code == null) return;
    try {
      final barangays = await PsgcApi.getBarangays(code);
      if (!mounted || _city != name) return;
      setState(() {
        _barangays = barangays;
        if (keepBarangay != null && barangays.any((b) => b.name == keepBarangay)) _barangay = keepBarangay;
      });
      _emit();
    } catch (_) {
      if (mounted) setState(() => _typeInstead = true);
    }
  }

  void _emit() {
    widget.onChanged(_typeInstead
        ? HomeAddress(barangay: _barangayText.text, city: _cityText.text, province: _provinceText.text)
        : HomeAddress(barangay: _barangay ?? '', city: _city ?? '', province: _province ?? ''));
  }

  InputDecoration _decoration(String label, {bool missing = false}) => InputDecoration(
        labelText: label,
        labelStyle: GoogleFonts.dmSans(fontSize: 13),
        isDense: true,
        filled: true,
        fillColor: Colors.white,
        errorText: missing ? 'Required' : null,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
      );

  Widget _dropdown(String label, String? value, List<String> names, ValueChanged<String?>? onChanged) {
    return DropdownButtonFormField<String>(
      key: ValueKey('$label-${names.length}-$value'),
      initialValue: value,
      isExpanded: true,
      decoration: _decoration(label, missing: widget.showError && (value == null || value.isEmpty)),
      items: [for (final n in names) DropdownMenuItem(value: n, child: Text(n, overflow: TextOverflow.ellipsis, style: GoogleFonts.dmSans(fontSize: 14)))],
      onChanged: names.isEmpty ? null : onChanged,
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Padding(
        padding: EdgeInsets.symmetric(vertical: 12),
        child: Center(child: SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2))),
      );
    }
    if (_typeInstead) {
      Widget field(String label, TextEditingController c) => Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: TextField(
              controller: c,
              maxLength: 100,
              decoration: _decoration(label, missing: widget.showError && c.text.trim().isEmpty).copyWith(counterText: ''),
              onChanged: (_) => _emit(),
            ),
          );
      return Column(children: [
        field('Province', _provinceText),
        field('City / municipality', _cityText),
        field('Barangay', _barangayText),
      ]);
    }
    return Column(children: [
      _dropdown('Province', _province, _provinces.map((p) => p.name).toList(), (v) => _pickProvince(v)),
      const SizedBox(height: 10),
      _dropdown('City / municipality', _city, _cities.map((c) => c.name).toList(), (v) => _pickCity(v)),
      const SizedBox(height: 10),
      _dropdown('Barangay', _barangay, _barangays.map((b) => b.name).toList(), (v) {
        setState(() => _barangay = v);
        _emit();
      }),
    ]);
  }
}

/// Bottom sheet to add or change the home address from the Profile tab.
/// Returns the saved address, or null when closed without saving.
Future<HomeAddress?> showHomeAddressSheet(BuildContext context, {HomeAddress? initial}) {
  return showModalBottomSheet<HomeAddress>(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.white,
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
    builder: (context) => _HomeAddressSheet(initial: initial),
  );
}

class _HomeAddressSheet extends StatefulWidget {
  final HomeAddress? initial;
  const _HomeAddressSheet({this.initial});

  @override
  State<_HomeAddressSheet> createState() => _HomeAddressSheetState();
}

class _HomeAddressSheetState extends State<_HomeAddressSheet> {
  late HomeAddress _value = widget.initial ?? const HomeAddress(barangay: '', city: '', province: '');
  bool _saving = false;
  bool _showError = false;
  String? _error;

  Future<void> _save() async {
    if (!_value.isComplete) {
      setState(() => _showError = true);
      return;
    }
    setState(() {
      _saving = true;
      _error = null;
    });
    try {
      final saved = await RiderHomeAddressService.save(_value);
      if (!mounted) return;
      Navigator.of(context).pop(saved ?? _value);
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _saving = false;
        _error = e is ApiException ? e.message : "Couldn't reach the server. Try again.";
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(20, 18, 20, 20 + MediaQuery.viewInsetsOf(context).bottom),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Home address', style: GoogleFonts.dmSans(fontSize: 17, fontWeight: FontWeight.w800)),
            const SizedBox(height: 4),
            Text('Your hub gives you pickups and deliveries near where you live.',
                style: GoogleFonts.dmSans(fontSize: 12.5, color: const Color(0xFF6B6275))),
            const SizedBox(height: 14),
            HomeAddressFields(initial: widget.initial, showError: _showError, onChanged: (v) => _value = v),
            if (_error != null) ...[
              const SizedBox(height: 8),
              Text(_error!, style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFFB54747))),
            ],
            const SizedBox(height: 14),
            FilledButton(
              onPressed: _saving ? null : _save,
              style: FilledButton.styleFrom(backgroundColor: const Color(0xFF6F6382), minimumSize: const Size.fromHeight(46)),
              child: Text(_saving ? 'Saving…' : 'Save home address', style: GoogleFonts.dmSans(fontWeight: FontWeight.w700)),
            ),
          ],
        ),
      ),
    );
  }
}

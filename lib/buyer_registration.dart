import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import 'cartzy_colors.dart';

// ============================================================
// PSGC API MODELS + SERVICE
// ============================================================

class PsgcProvince {
  final String code;
  final String name;
  PsgcProvince({required this.code, required this.name});

  factory PsgcProvince.fromJson(Map<String, dynamic> json) =>
      PsgcProvince(code: json['code'], name: json['name']);
}

class PsgcCityMunicipality {
  final String code;
  final String name;
  PsgcCityMunicipality({required this.code, required this.name});

  factory PsgcCityMunicipality.fromJson(Map<String, dynamic> json) =>
      PsgcCityMunicipality(code: json['code'], name: json['name']);
}

class PsgcBarangay {
  final String code;
  final String name;
  PsgcBarangay({required this.code, required this.name});

  factory PsgcBarangay.fromJson(Map<String, dynamic> json) =>
      PsgcBarangay(code: json['code'], name: json['name']);
}

class PsgcApi {
  static const _baseUrl = 'https://psgc.gitlab.io/api';

  static Future<List<PsgcProvince>> getProvinces() async {
    final response = await http.get(Uri.parse('$_baseUrl/provinces/'));
    if (response.statusCode != 200) {
      throw Exception('Failed to load provinces');
    }
    final List<dynamic> data = jsonDecode(response.body);
    return data.map((e) => PsgcProvince.fromJson(e)).toList();
  }

  static Future<List<PsgcCityMunicipality>> getMunicipalities(
      String provinceCode) async {
    final response = await http.get(
      Uri.parse('$_baseUrl/provinces/$provinceCode/cities-municipalities/'),
    );
    if (response.statusCode != 200) {
      throw Exception('Failed to load municipalities');
    }
    final List<dynamic> data = jsonDecode(response.body);
    return data.map((e) => PsgcCityMunicipality.fromJson(e)).toList();
  }

  static Future<List<PsgcBarangay>> getBarangays(
      String municipalityCode) async {
    final response = await http.get(
      Uri.parse('$_baseUrl/cities-municipalities/$municipalityCode/barangays/'),
    );
    if (response.statusCode != 200) {
      throw Exception('Failed to load barangays');
    }
    final List<dynamic> data = jsonDecode(response.body);
    return data.map((e) => PsgcBarangay.fromJson(e)).toList();
  }
}

// ============================================================
// BUYER REGISTRATION
// ============================================================

class BuyerRegistration extends StatefulWidget {
  final VoidCallback onRegistrationSubmitted;

  const BuyerRegistration({super.key, required this.onRegistrationSubmitted});

  @override
  State<BuyerRegistration> createState() => _BuyerRegistrationState();
}

class _BuyerRegistrationState extends State<BuyerRegistration> {
  int _currentStep = 1;

  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _middleInitialController = TextEditingController();
  String _selectedSex = '';
  String _birthday = '';

  bool _firstNameError = false;
  bool _lastNameError = false;
  bool _sexError = false;
  bool _birthdayError = false;

  final _houseStreetController = TextEditingController();
  String _selectedProvince = '';
  String _selectedMunicipality = '';
  String _selectedBarangay = '';
  String _selectedProvinceCode = '';
  String _selectedMunicipalityCode = '';

  bool _houseStreetError = false;
  bool _provinceError = false;
  bool _municipalityError = false;
  bool _barangayError = false;

  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _passwordVisible = false;
  bool _confirmPasswordVisible = false;

  bool _emailError = false;
  bool _passwordError = false;
  bool _confirmPasswordError = false;
  String _confirmPasswordMessage = 'This field is required';

  static const List<String> sexOptions = ['Male', 'Female', 'Prefer not to say'];

  List<PsgcProvince> _provinces = [];
  bool _provincesLoading = true;
  bool _provincesLoadError = false;

  List<PsgcCityMunicipality> _municipalities = [];
  bool _municipalitiesLoading = false;
  bool _municipalitiesLoadError = false;

  List<PsgcBarangay> _barangays = [];
  bool _barangaysLoading = false;
  bool _barangaysLoadError = false;

  @override
  void initState() {
    super.initState();
    _loadProvinces();
  }

  @override
  void dispose() {
    _firstNameController.dispose();
    _lastNameController.dispose();
    _middleInitialController.dispose();
    _houseStreetController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _loadProvinces() async {
    try {
      final provinces = await PsgcApi.getProvinces();
      provinces.sort((a, b) => a.name.compareTo(b.name));
      setState(() {
        _provinces = provinces;
        _provincesLoadError = false;
      });
    } catch (_) {
      setState(() {
        _provinces = [];
        _provincesLoadError = true;
      });
    }
    if (mounted) setState(() => _provincesLoading = false);
  }

  Future<void> _loadMunicipalities(String provinceCode) async {
    if (provinceCode.isEmpty) {
      setState(() => _municipalities = []);
      return;
    }
    setState(() => _municipalitiesLoading = true);
    try {
      final municipalities = await PsgcApi.getMunicipalities(provinceCode);
      municipalities.sort((a, b) => a.name.compareTo(b.name));
      setState(() {
        _municipalities = municipalities;
        _municipalitiesLoadError = false;
      });
    } catch (_) {
      setState(() {
        _municipalities = [];
        _municipalitiesLoadError = true;
      });
    }
    if (mounted) setState(() => _municipalitiesLoading = false);
  }

  Future<void> _loadBarangays(String municipalityCode) async {
    if (municipalityCode.isEmpty) {
      setState(() => _barangays = []);
      return;
    }
    setState(() => _barangaysLoading = true);
    try {
      final barangays = await PsgcApi.getBarangays(municipalityCode);
      barangays.sort((a, b) => a.name.compareTo(b.name));
      setState(() {
        _barangays = barangays;
        _barangaysLoadError = false;
      });
    } catch (_) {
      setState(() {
        _barangays = [];
        _barangaysLoadError = true;
      });
    }
    if (mounted) setState(() => _barangaysLoading = false);
  }

  void _openBirthdayPicker() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => BirthdayWheelPicker(
        currentBirthday: _birthday,
        accent: CartzyColors.coral,
        onDismiss: () => Navigator.of(context).pop(),
        onDateSelected: (date) {
          Navigator.of(context).pop();
          setState(() {
            _birthday = date;
            _birthdayError = false;
          });
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Column(
          children: [
            Container(
              width: double.infinity,
              color: CartzyColors.surface,
              padding: const EdgeInsets.only(
                left: 24,
                right: 24,
                top: 28,
                bottom: 18,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Create Your Account',
                    style: TextStyle(
                      fontSize: 26,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.navy,
                    ),
                  ),
                  const SizedBox(height: 6),
                  const Text(
                    'Buyer Registration',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: CartzyColors.coral,
                    ),
                  ),
                ],
              ),
            ),
            StepIndicator(
              currentStep: _currentStep,
              accent: CartzyColors.coral,
              gray: CartzyColors.border,
            ),
            const Divider(color: CartzyColors.border, height: 1),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 28),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (_currentStep == 1) _buildStep1(),
                    if (_currentStep == 2) _buildStep2(),
                    if (_currentStep == 3) _buildStep3(),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStep1() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Personal Information',
          style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
        const SizedBox(height: 8),
        const Text(
          'Tell us a little about yourself.',
          style: TextStyle(fontSize: 13, color: CartzyColors.gray),
        ),
        const SizedBox(height: 28),
        RegistrationTextField(
          controller: _firstNameController,
          label: 'First Name *',
          icon: Icons.person_outline,
          isError: _firstNameError,
          errorMessage: 'First name is required',
          onChanged: (value) {
            if (_firstNameError && value.trim().isNotEmpty) {
              setState(() => _firstNameError = false);
            }
          },
        ),
        const SizedBox(height: 18),
        RegistrationTextField(
          controller: _lastNameController,
          label: 'Last Name *',
          icon: Icons.person_outline,
          isError: _lastNameError,
          errorMessage: 'Last name is required',
          onChanged: (value) {
            if (_lastNameError && value.trim().isNotEmpty) {
              setState(() => _lastNameError = false);
            }
          },
        ),
        const SizedBox(height: 18),
        RegistrationTextField(
          controller: _middleInitialController,
          label: 'Middle Initial',
          icon: Icons.person_outline,
        ),
        const SizedBox(height: 18),
        RegistrationDropdown(
          label: 'Sex *',
          icon: Icons.wc,
          selectedValue: _selectedSex,
          options: sexOptions,
          isError: _sexError,
          errorMessage: 'Please select your sex',
          onSelected: (value) {
            setState(() {
              _selectedSex = value;
              _sexError = false;
            });
          },
        ),
        const SizedBox(height: 18),
        RegistrationDateField(
          birthday: _birthday,
          onClick: _openBirthdayPicker,
          isError: _birthdayError,
          errorMessage: 'Please select your birthday',
        ),
        const SizedBox(height: 36),
        PrimaryButton(
          text: 'NEXT',
          color: CartzyColors.navy,
          onClick: () {
            setState(() {
              _firstNameError = _firstNameController.text.trim().isEmpty;
              _lastNameError = _lastNameController.text.trim().isEmpty;
              _sexError = _selectedSex.isEmpty;
              _birthdayError = _birthday.isEmpty;

              if (!_firstNameError && !_lastNameError && !_sexError && !_birthdayError) {
                _currentStep = 2;
              }
            });
          },
        ),
      ],
    );
  }

  Widget _buildStep2() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Delivery Address',
          style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
        const SizedBox(height: 8),
        const Text(
          'Where should we deliver your orders?',
          style: TextStyle(fontSize: 13, color: CartzyColors.gray),
        ),
        const SizedBox(height: 28),
        RegistrationTextField(
          controller: _houseStreetController,
          label: 'House / Unit No. and Street *',
          icon: Icons.home_outlined,
          isError: _houseStreetError,
          errorMessage: 'This field is required',
          onChanged: (value) {
            if (_houseStreetError && value.trim().isNotEmpty) {
              setState(() => _houseStreetError = false);
            }
          },
        ),
        const SizedBox(height: 18),
        if (_provincesLoadError) ...[
          const Text(
            "Couldn't load provinces. Check your connection.",
            style: TextStyle(fontSize: 12, color: CartzyColors.error),
          ),
          const SizedBox(height: 8),
        ],
        RegistrationDropdown(
          label: _provincesLoading ? 'Loading provinces...' : 'Province *',
          icon: Icons.map_outlined,
          selectedValue: _selectedProvince,
          options: _provinces.map((p) => p.name).toList(),
          isError: _provinceError,
          errorMessage: 'Please select a province',
          onSelected: (name) {
            final province = _provinces.firstWhere((p) => p.name == name);
            setState(() {
              _selectedProvince = name;
              _selectedProvinceCode = province.code;
              _selectedMunicipality = '';
              _selectedMunicipalityCode = '';
              _selectedBarangay = '';
              _provinceError = false;
            });
            _loadMunicipalities(province.code);
          },
        ),
        const SizedBox(height: 18),
        if (_municipalitiesLoadError) ...[
          const Text(
            "Couldn't load municipalities. Check your connection.",
            style: TextStyle(fontSize: 12, color: CartzyColors.error),
          ),
          const SizedBox(height: 8),
        ],
        RegistrationDropdown(
          label: _municipalitiesLoading ? 'Loading...' : 'Municipality / City *',
          icon: Icons.location_city_outlined,
          selectedValue: _selectedMunicipality,
          options: _municipalities.map((m) => m.name).toList(),
          isError: _municipalityError,
          errorMessage: 'Please select a municipality or city',
          onSelected: (name) {
            final municipality = _municipalities.firstWhere((m) => m.name == name);
            setState(() {
              _selectedMunicipality = name;
              _selectedMunicipalityCode = municipality.code;
              _selectedBarangay = '';
              _municipalityError = false;
            });
            _loadBarangays(municipality.code);
          },
        ),
        const SizedBox(height: 18),
        if (_barangaysLoadError) ...[
          const Text(
            "Couldn't load barangays. Check your connection.",
            style: TextStyle(fontSize: 12, color: CartzyColors.error),
          ),
          const SizedBox(height: 8),
        ],
        RegistrationDropdown(
          label: _barangaysLoading ? 'Loading...' : 'Barangay *',
          icon: Icons.place_outlined,
          selectedValue: _selectedBarangay,
          options: _barangays.map((b) => b.name).toList(),
          isError: _barangayError,
          errorMessage: 'Please select a barangay',
          onSelected: (name) {
            setState(() {
              _selectedBarangay = name;
              _barangayError = false;
            });
          },
        ),
        const SizedBox(height: 36),
        Row(
          children: [
            Expanded(
              child: SecondaryButton(
                text: 'BACK',
                color: CartzyColors.navy,
                onClick: () => setState(() => _currentStep = 1),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: PrimaryButton(
                text: 'NEXT',
                color: CartzyColors.navy,
                onClick: () {
                  setState(() {
                    _houseStreetError = _houseStreetController.text.trim().isEmpty;
                    _provinceError = _selectedProvince.isEmpty;
                    _municipalityError = _selectedMunicipality.isEmpty;
                    _barangayError = _selectedBarangay.isEmpty;

                    if (!_houseStreetError &&
                        !_provinceError &&
                        !_municipalityError &&
                        !_barangayError) {
                      _currentStep = 3;
                    }
                  });
                },
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildStep3() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Account Information',
          style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
        const SizedBox(height: 8),
        const Text(
          'Create your Cartzy login details.',
          style: TextStyle(fontSize: 13, color: CartzyColors.gray),
        ),
        const SizedBox(height: 28),
        RegistrationTextField(
          controller: _emailController,
          label: 'Email Address *',
          icon: Icons.email_outlined,
          isError: _emailError,
          errorMessage: 'Email is required',
          onChanged: (value) {
            if (_emailError && value.trim().isNotEmpty) {
              setState(() => _emailError = false);
            }
          },
        ),
        const SizedBox(height: 18),
        RegistrationTextField(
          controller: _passwordController,
          label: 'Password *',
          icon: Icons.lock_outline,
          isError: _passwordError,
          errorMessage: 'Password is required',
          obscureText: !_passwordVisible,
          trailing: IconButton(
            icon: Icon(
              _passwordVisible ? Icons.visibility_off : Icons.visibility,
              color: CartzyColors.gray,
            ),
            onPressed: () => setState(() => _passwordVisible = !_passwordVisible),
          ),
          onChanged: (value) {
            if (_passwordError && value.trim().isNotEmpty) {
              setState(() => _passwordError = false);
            }
          },
        ),
        const SizedBox(height: 18),
        RegistrationTextField(
          controller: _confirmPasswordController,
          label: 'Confirm Password *',
          icon: Icons.lock_outline,
          isError: _confirmPasswordError,
          errorMessage: _confirmPasswordMessage,
          obscureText: !_confirmPasswordVisible,
          trailing: IconButton(
            icon: Icon(
              _confirmPasswordVisible ? Icons.visibility_off : Icons.visibility,
              color: CartzyColors.gray,
            ),
            onPressed: () => setState(() => _confirmPasswordVisible = !_confirmPasswordVisible),
          ),
          onChanged: (value) {
            if (_confirmPasswordError) {
              setState(() => _confirmPasswordError = false);
            }
          },
        ),
        const SizedBox(height: 36),
        Row(
          children: [
            Expanded(
              child: SecondaryButton(
                text: 'BACK',
                color: CartzyColors.navy,
                onClick: () => setState(() => _currentStep = 2),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: PrimaryButton(
                text: 'CREATE ACCOUNT',
                color: CartzyColors.navy,
                onClick: () {
                  setState(() {
                    _emailError = _emailController.text.trim().isEmpty;
                    _passwordError = _passwordController.text.trim().isEmpty;

                    if (_confirmPasswordController.text.trim().isEmpty) {
                      _confirmPasswordMessage = 'Please confirm your password';
                      _confirmPasswordError = true;
                    } else if (_confirmPasswordController.text != _passwordController.text) {
                      _confirmPasswordMessage = 'Passwords do not match';
                      _confirmPasswordError = true;
                    } else {
                      _confirmPasswordError = false;
                    }

                    if (!_emailError && !_passwordError && !_confirmPasswordError) {
                      widget.onRegistrationSubmitted();
                    }
                  });
                },
              ),
            ),
          ],
        ),
      ],
    );
  }
}

// ============================================================
// DATE FIELD — matches login screen field style
// ============================================================

class RegistrationDateField extends StatelessWidget {
  final String birthday;
  final VoidCallback onClick;
  final bool isError;
  final String errorMessage;

  const RegistrationDateField({
    super.key,
    required this.birthday,
    required this.onClick,
    this.isError = false,
    this.errorMessage = '',
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onClick,
      child: AbsorbPointer(
        child: TextField(
          controller: TextEditingController(text: birthday),
          readOnly: true,
          decoration: InputDecoration(
            hintText: 'Select your birthday',
            hintStyle: const TextStyle(fontSize: 13),
            prefixIcon: const Icon(Icons.cake_outlined, color: CartzyColors.coral),
            suffixIcon: const Icon(Icons.arrow_drop_down, color: CartzyColors.coral),
            errorText: isError ? errorMessage : null,
            filled: true,
            fillColor: CartzyColors.background,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: BorderSide.none,
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: BorderSide.none,
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: CartzyColors.coral),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: CartzyColors.error),
            ),
          ),
        ),
      ),
    );
  }
}

// ============================================================
// BIRTHDAY PICKER
// ============================================================

class BirthdayWheelPicker extends StatefulWidget {
  final String currentBirthday;
  final Color accent;
  final VoidCallback onDismiss;
  final ValueChanged<String> onDateSelected;

  const BirthdayWheelPicker({
    super.key,
    required this.currentBirthday,
    required this.accent,
    required this.onDismiss,
    required this.onDateSelected,
  });

  @override
  State<BirthdayWheelPicker> createState() => _BirthdayWheelPickerState();
}

class _BirthdayWheelPickerState extends State<BirthdayWheelPicker> {
  static const List<String> monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  late int _selectedMonth;
  late int _selectedDay;
  late int _selectedYear;
  late List<int> _years;

  @override
  void initState() {
    super.initState();
    final currentYear = DateTime.now().year;
    _years = List.generate(currentYear - 1900 + 1, (i) => currentYear - i);

    _selectedMonth = 0;
    _selectedDay = 1;
    _selectedYear = 2000;

    if (widget.currentBirthday.isNotEmpty) {
      try {
        final parts = widget.currentBirthday.split(' ');
        final monthIndex = monthNames.indexOf(parts[0]);
        final day = int.parse(parts[1].replaceAll(',', ''));
        final year = int.parse(parts[2]);
        _selectedMonth = monthIndex >= 0 ? monthIndex : 0;
        _selectedDay = day;
        _selectedYear = year;
      } catch (_) {
        // use defaults
      }
    }
  }

  int get _daysInMonth => DateTime(_selectedYear, _selectedMonth + 2, 0).day;

  @override
  Widget build(BuildContext context) {
    if (_selectedDay > _daysInMonth) {
      _selectedDay = _daysInMonth;
    }

    return Container(
      decoration: const BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(24),
          topRight: Radius.circular(24),
        ),
      ),
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Select Birthday',
            style: TextStyle(fontSize: 21, fontWeight: FontWeight.bold, color: CartzyColors.navy),
          ),
          const SizedBox(height: 6),
          const Text(
            'Scroll each column to select your date',
            style: TextStyle(fontSize: 13, color: CartzyColors.gray),
          ),
          const SizedBox(height: 18),
          Text(
            '${monthNames[_selectedMonth]} $_selectedDay, $_selectedYear',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 19,
              fontWeight: FontWeight.bold,
              color: widget.accent,
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 220,
            child: Row(
              children: [
                Expanded(
                  flex: 15,
                  child: _wheel(
                    itemCount: monthNames.length,
                    initialIndex: _selectedMonth,
                    labelBuilder: (i) => monthNames[i],
                    onChanged: (i) => setState(() => _selectedMonth = i),
                  ),
                ),
                Expanded(
                  flex: 7,
                  child: _wheel(
                    itemCount: _daysInMonth,
                    initialIndex: _selectedDay - 1,
                    labelBuilder: (i) => '${i + 1}',
                    onChanged: (i) => setState(() => _selectedDay = i + 1),
                  ),
                ),
                Expanded(
                  flex: 9,
                  child: _wheel(
                    itemCount: _years.length,
                    initialIndex: _years.indexOf(_selectedYear).clamp(0, _years.length - 1),
                    labelBuilder: (i) => '${_years[i]}',
                    onChanged: (i) => setState(() => _selectedYear = _years[i]),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          const Divider(),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              TextButton(
                onPressed: widget.onDismiss,
                child: Text(
                  'CANCEL',
                  style: TextStyle(color: widget.accent, fontWeight: FontWeight.bold),
                ),
              ),
              TextButton(
                onPressed: () {
                  final formatted =
                      '${monthNames[_selectedMonth]} ${_selectedDay.toString().padLeft(2, '0')}, $_selectedYear';
                  widget.onDateSelected(formatted);
                },
                child: Text(
                  'DONE',
                  style: TextStyle(color: widget.accent, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _wheel({
    required int itemCount,
    required int initialIndex,
    required String Function(int) labelBuilder,
    required ValueChanged<int> onChanged,
  }) {
    return ListWheelScrollView.useDelegate(
      itemExtent: 44,
      diameterRatio: 1.5,
      physics: const FixedExtentScrollPhysics(),
      controller: FixedExtentScrollController(initialItem: initialIndex),
      onSelectedItemChanged: onChanged,
      childDelegate: ListWheelChildBuilderDelegate(
        childCount: itemCount,
        builder: (context, index) {
          return Center(
            child: Text(
              labelBuilder(index),
              style: TextStyle(
                fontSize: index == initialIndex ? 17 : 14,
                fontWeight: index == initialIndex ? FontWeight.bold : FontWeight.normal,
                color: index == initialIndex ? widget.accent : const Color(0xFF9CA3AF),
              ),
            ),
          );
        },
      ),
    );
  }
}

// ============================================================
// DROPDOWN — matches login screen field style
// ============================================================

class RegistrationDropdown extends StatelessWidget {
  final String label;
  final IconData icon;
  final String selectedValue;
  final List<String> options;
  final ValueChanged<String> onSelected;
  final bool isError;
  final String errorMessage;

  const RegistrationDropdown({
    super.key,
    required this.label,
    required this.icon,
    required this.selectedValue,
    required this.options,
    required this.onSelected,
    this.isError = false,
    this.errorMessage = '',
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: options.isEmpty
          ? null
          : () async {
              final selected = await showModalBottomSheet<String>(
                context: context,
                constraints: const BoxConstraints(maxHeight: 400),
                shape: const RoundedRectangleBorder(
                  borderRadius: BorderRadius.only(
                    topLeft: Radius.circular(20),
                    topRight: Radius.circular(20),
                  ),
                ),
                builder: (context) {
                  return ListView.builder(
                    itemCount: options.length,
                    itemBuilder: (context, index) {
                      return ListTile(
                        title: Text(options[index]),
                        onTap: () => Navigator.of(context).pop(options[index]),
                      );
                    },
                  );
                },
              );
              if (selected != null) onSelected(selected);
            },
      child: AbsorbPointer(
        child: TextField(
          controller: TextEditingController(text: selectedValue),
          readOnly: true,
          decoration: InputDecoration(
            hintText: label,
            hintStyle: const TextStyle(fontSize: 13),
            prefixIcon: Icon(icon, color: CartzyColors.coral),
            suffixIcon: const Icon(Icons.arrow_drop_down, color: CartzyColors.coral),
            errorText: isError ? errorMessage : null,
            filled: true,
            fillColor: CartzyColors.background,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: BorderSide.none,
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: BorderSide.none,
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: CartzyColors.coral),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(14),
              borderSide: const BorderSide(color: CartzyColors.error),
            ),
          ),
        ),
      ),
    );
  }
}

// ============================================================
// TEXT FIELD — matches login screen field style
// ============================================================

class RegistrationTextField extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final IconData icon;
  final bool isError;
  final String errorMessage;
  final bool obscureText;
  final Widget? trailing;
  final ValueChanged<String>? onChanged;

  const RegistrationTextField({
    super.key,
    required this.controller,
    required this.label,
    required this.icon,
    this.isError = false,
    this.errorMessage = '',
    this.obscureText = false,
    this.trailing,
    this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      obscureText: obscureText,
      onChanged: onChanged,
      decoration: InputDecoration(
        hintText: label,
        hintStyle: const TextStyle(fontSize: 13),
        prefixIcon: Icon(icon, color: CartzyColors.coral),
        suffixIcon: trailing,
        errorText: isError ? errorMessage : null,
        filled: true,
        fillColor: CartzyColors.background,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: CartzyColors.coral),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: CartzyColors.error),
        ),
      ),
    );
  }
}

// ============================================================
// PRIMARY BUTTON
// ============================================================

class PrimaryButton extends StatelessWidget {
  final String text;
  final Color color;
  final VoidCallback onClick;

  const PrimaryButton({
    super.key,
    required this.text,
    required this.color,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: ElevatedButton(
        onPressed: onClick,
        style: ElevatedButton.styleFrom(
          backgroundColor: color,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
        child: Text(
          text,
          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
        ),
      ),
    );
  }
}

// ============================================================
// SECONDARY BUTTON
// ============================================================

class SecondaryButton extends StatelessWidget {
  final String text;
  final Color color;
  final VoidCallback onClick;

  const SecondaryButton({
    super.key,
    required this.text,
    required this.color,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      height: 52,
      child: OutlinedButton(
        onPressed: onClick,
        style: OutlinedButton.styleFrom(
          foregroundColor: color,
          side: BorderSide(color: color),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
        child: Text(
          text,
          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
        ),
      ),
    );
  }
}

// ============================================================
// STEP INDICATOR
// ============================================================

class StepIndicator extends StatelessWidget {
  final int currentStep;
  final Color accent;
  final Color gray;

  const StepIndicator({
    super.key,
    required this.currentStep,
    required this.accent,
    required this.gray,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      color: CartzyColors.surface,
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 18),
      child: Row(
        children: [
          StepCircle(number: '1', active: currentStep >= 1, accent: accent, gray: gray),
          const SizedBox(width: 8),
          Expanded(
            child: Divider(color: currentStep >= 2 ? accent : gray, thickness: 1),
          ),
          const SizedBox(width: 8),
          StepCircle(number: '2', active: currentStep >= 2, accent: accent, gray: gray),
          const SizedBox(width: 8),
          Expanded(
            child: Divider(color: currentStep >= 3 ? accent : gray, thickness: 1),
          ),
          const SizedBox(width: 8),
          StepCircle(number: '3', active: currentStep >= 3, accent: accent, gray: gray),
        ],
      ),
    );
  }
}

// ============================================================
// STEP CIRCLE
// ============================================================

class StepCircle extends StatelessWidget {
  final String number;
  final bool active;
  final Color accent;
  final Color gray;

  const StepCircle({
    super.key,
    required this.number,
    required this.active,
    required this.accent,
    required this.gray,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 34,
      height: 34,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: active ? accent : gray,
        shape: BoxShape.circle,
      ),
      child: Text(
        number,
        style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.bold),
      ),
    );
  }
}
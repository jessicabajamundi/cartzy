import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';

import 'cartzy_colors.dart';
import 'services/api_service.dart';
import 'cartzy_flash_notif.dart';
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
// EMAIL AVAILABILITY CHECK (shared helper)
// ============================================================

Future<bool?> checkEmailAvailable(String email) async {
  try {
    final response = await http.get(
      Uri.parse('${ApiService.baseUrl}/check-email?email=${Uri.encodeQueryComponent(email)}'),
    );
    if (response.statusCode != 200) return null;
    final data = jsonDecode(response.body);
    return data['available'] == true;
  } catch (_) {
    // Network hiccup — don't block the user here, the final
    // submit will still catch a duplicate email server-side.
    return null;
  }
}

// ============================================================
// BUYER REGISTRATION
// ============================================================

class BuyerRegistration extends StatefulWidget {
  // Now passes back the real created account (id, name, email, etc.)
  // so main.dart can log the person straight into THEIR dashboard,
  // not a leftover/hardcoded one.
  final ValueChanged<Map<String, dynamic>> onRegistrationSubmitted;
  final VoidCallback onBackToLogin;

  const BuyerRegistration({
    super.key,
    required this.onRegistrationSubmitted,
    required this.onBackToLogin,
  });

  @override
  State<BuyerRegistration> createState() => _BuyerRegistrationState();
}

class _BuyerRegistrationState extends State<BuyerRegistration> {
  int _currentStep = 1;

  // ========================================================
  // PERSONAL INFO + CONTACT + BIRTHDAY
  // ========================================================

  final _lastNameController = TextEditingController();
  final _firstNameController = TextEditingController();
  final _middleInitialController = TextEditingController();
  final _emailController = TextEditingController();
  final _contactNoController = TextEditingController();
  final _emailShakeKey = GlobalKey<ShakeWidgetState>();

  String _selectedSex = '';
  String _birthday = '';
  int? _age;

  bool _lastNameError = false;
  bool _firstNameError = false;
  bool _sexError = false;
  bool _emailError = false;
  String? _emailErrorMessage;
  bool _contactNoError = false;
  bool _birthdayError = false;
  bool _isCheckingEmail = false;

  // ========================================================
  // ADDRESS
  // ========================================================

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

  List<PsgcProvince> _provinces = [];
  bool _provincesLoading = true;
  bool _provincesLoadError = false;

  List<PsgcCityMunicipality> _municipalities = [];
  bool _municipalitiesLoading = false;
  bool _municipalitiesLoadError = false;

  List<PsgcBarangay> _barangays = [];
  bool _barangaysLoading = false;
  bool _barangaysLoadError = false;

  // ========================================================
  // ACCOUNT + ID UPLOAD
  // ========================================================

  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _passwordVisible = false;
  bool _confirmPasswordVisible = false;

  bool _passwordError = false;
  bool _confirmPasswordError = false;
  String _confirmPasswordMessage = 'This field is required';

  String? _idFileName;
  XFile? _idFile;
  bool _idError = false;

  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _loadProvinces();
  }

  @override
  void dispose() {
    _lastNameController.dispose();
    _firstNameController.dispose();
    _middleInitialController.dispose();
    _emailController.dispose();
    _contactNoController.dispose();
    _houseStreetController.dispose();
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
            _age = _calculateAge(date);
            _birthdayError = false;
          });
        },
      ),
    );
  }

  int? _calculateAge(String formattedDate) {
    try {
      final parts = formattedDate.split(' ');
      const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];
      final monthIndex = months.indexOf(parts[0]);
      final day = int.parse(parts[1].replaceAll(',', ''));
      final year = int.parse(parts[2]);

      final birthDate = DateTime(year, monthIndex + 1, day);
      final today = DateTime.now();

      int age = today.year - birthDate.year;
      if (today.month < birthDate.month ||
          (today.month == birthDate.month && today.day < birthDate.day)) {
        age--;
      }
      return age;
    } catch (_) {
      return null;
    }
  }

  Future<void> _pickIdFile() async {
    try {
      final picker = ImagePicker();

      final XFile? pickedFile = await picker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 85,
      );

      if (pickedFile == null) return;

      setState(() {
        _idFile = pickedFile;
        _idFileName = pickedFile.name;
        _idError = false;
      });
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Unable to select ID photo: $e'),
        ),
      );
    }
  }

  String _birthdayForDatabase() {
    if (_birthday.isEmpty) return '';

    try {
      final parts = _birthday.replaceAll(',', '').split(' ');
      const months = <String, String>{
        'January': '01',
        'February': '02',
        'March': '03',
        'April': '04',
        'May': '05',
        'June': '06',
        'July': '07',
        'August': '08',
        'September': '09',
        'October': '10',
        'November': '11',
        'December': '12',
      };

      final month = months[parts[0]];
      if (month == null) return '';

      final day = parts[1].padLeft(2, '0');
      final year = parts[2];
      return '$year-$month-$day';
    } catch (_) {
      return '';
    }
  }

  // ============================================================
  // STEP 1 → STEP 2: validate fields, THEN check email availability
  // ============================================================

  Future<void> _handleStep1Next() async {
    setState(() {
      _lastNameError = _lastNameController.text.trim().isEmpty;
      _firstNameError = _firstNameController.text.trim().isEmpty;
      _sexError = _selectedSex.isEmpty;
      _emailError = _emailController.text.trim().isEmpty;
      _emailErrorMessage = _emailError ? 'Email is required' : null;
      _contactNoError = _contactNoController.text.trim().isEmpty;
      _birthdayError = _birthday.isEmpty;
    });

    if (_lastNameError ||
        _firstNameError ||
        _sexError ||
        _emailError ||
        _contactNoError ||
        _birthdayError) {
      return;
    }

    setState(() => _isCheckingEmail = true);

    final available = await checkEmailAvailable(_emailController.text.trim());

    if (!mounted) return;

    if (available == false) {
      setState(() {
        _isCheckingEmail = false;
        _emailError = true;
        _emailErrorMessage = 'This email is already registered. Try logging in instead.';
      });
      _emailShakeKey.currentState?.shake();
      return;
    }

    // available == true, or available == null (check failed —
    // let them continue; the final submit still catches duplicates).
    setState(() {
      _isCheckingEmail = false;
      _currentStep = 2;
    });
  }

  Future<void> _registerBuyer() async {
    if (_idFile == null) {
      setState(() => _idError = true);
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final request = http.MultipartRequest(
        'POST',
        Uri.parse('${ApiService.baseUrl}/register/buyer'),
      );

      final firstName = _firstNameController.text.trim();
      final lastName = _lastNameController.text.trim();
      final middleInitial = _middleInitialController.text.trim();

      request.fields['name'] = '$firstName $lastName'.trim();
      request.fields['middle_initial'] = middleInitial;
      request.fields['sex'] = _selectedSex;
      request.fields['birthday'] = _birthdayForDatabase();
      request.fields['age'] = _age?.toString() ?? '';
      request.fields['email'] = _emailController.text.trim();
      request.fields['phone'] = _contactNoController.text.trim();
      request.fields['street_address'] = _houseStreetController.text.trim();
      request.fields['address'] = _houseStreetController.text.trim();
      request.fields['region'] = '';
      request.fields['province'] = _selectedProvince;
      request.fields['city'] = _selectedMunicipality;
      request.fields['barangay'] = _selectedBarangay;
      request.fields['postal_code'] = '';
      request.fields['password'] = _passwordController.text;

      final idBytes = await _idFile!.readAsBytes();

      request.files.add(
        http.MultipartFile.fromBytes(
          'id_photo',
          idBytes,
          filename: _idFileName ?? 'id_photo.jpg',
        ),
      );

      final response = await request.send();
      final responseBody = await response.stream.bytesToString();
      final data = responseBody.isNotEmpty
          ? jsonDecode(responseBody)
          : <String, dynamic>{};

      if (response.statusCode < 200 || response.statusCode >= 300) {
        // Duplicate email caught server-side too (race condition
        // safety net even though we already checked in Step 1).
        if (response.statusCode == 409) {
          setState(() {
            _isSubmitting = false;
            _currentStep = 1;
            _emailError = true;
            _emailErrorMessage = 'This email is already registered. Try logging in instead.';
          });
          // Step 1 (and the ShakeWidget inside it) only mounts on the next
          // frame after switching _currentStep back to 1, so defer the
          // shake until after that rebuild.
          WidgetsBinding.instance.addPostFrameCallback((_) {
            _emailShakeKey.currentState?.shake();
          });
          return;
        }

        throw Exception(
          data['error'] ??
              data['message'] ??
              'Registration failed (${response.statusCode})',
        );
      }

      if (!mounted) return;

      setState(() => _isSubmitting = false);

      showCartzyFlash(context, 'Account created! Welcome to Cartzy.');

      // Let the flash notif be visible for a moment before handing off
      // to the dashboard.
      await Future.delayed(const Duration(milliseconds: 700));

      if (!mounted) return;

      final createdUser = Map<String, dynamic>.from(data['user'] ?? {});
      widget.onRegistrationSubmitted(createdUser);
    } catch (e) {
      if (!mounted) return;

      setState(() => _isSubmitting = false);

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Registration failed: ${e.toString().replaceFirst('Exception: ', '')}',
          ),
        ),
      );
    }
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
              child: const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Create Your Account',
                    style: TextStyle(
                      fontSize: 26,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.navy,
                    ),
                  ),
                  SizedBox(height: 6),
                  Text(
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
              icons: const [
                Icons.person_outline,
                Icons.location_on_outlined,
                Icons.lock_outline,
              ],
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

  // ============================================================
  // STEP 1 — PERSONAL + CONTACT + BIRTHDAY
  // ============================================================

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
          controller: _middleInitialController,
          label: 'Middle Initial',
          icon: Icons.person_outline,
        ),
        const SizedBox(height: 18),

        SexDropdown(
          selectedValue: _selectedSex,
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

        ShakeWidget(
          key: _emailShakeKey,
          child: RegistrationTextField(
            controller: _emailController,
            label: 'E-mail *',
            icon: Icons.email_outlined,
            isError: _emailError,
            errorMessage: _emailErrorMessage ?? 'Email is required',
            onChanged: (value) {
              if (_emailError) {
                setState(() {
                  _emailError = false;
                  _emailErrorMessage = null;
                });
              }
            },
          ),
        ),
        const SizedBox(height: 18),

        RegistrationTextField(
          controller: _contactNoController,
          label: 'Contact No. *',
          icon: Icons.phone_outlined,
          isError: _contactNoError,
          errorMessage: 'Contact number is required',
          onChanged: (value) {
            if (_contactNoError && value.trim().isNotEmpty) {
              setState(() => _contactNoError = false);
            }
          },
        ),
        const SizedBox(height: 18),

        RegistrationDateField(
          birthday: _birthday,
          onClick: _openBirthdayPicker,
          isError: _birthdayError,
          errorMessage: 'Please select your birthday',
        ),
        const SizedBox(height: 18),

        // AGE (AUTOGEN)
        TextField(
          readOnly: true,
          controller: TextEditingController(text: _age?.toString() ?? ''),
          style: const TextStyle(fontSize: 15, color: CartzyColors.text),
          decoration: InputDecoration(
            labelText: 'Age (auto-generated)',
            labelStyle: const TextStyle(fontSize: 14, color: CartzyColors.gray),
            prefixIcon: const Icon(Icons.badge_outlined, color: CartzyColors.coral, size: 20),
            filled: true,
            fillColor: CartzyColors.surface,
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
            ),
          ),
        ),
        const SizedBox(height: 36),

        Row(
          children: [
            Expanded(
              child: SecondaryButton(
                text: 'BACK',
                color: CartzyColors.navy,
                onClick: widget.onBackToLogin,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: PrimaryButton(
                text: _isCheckingEmail ? 'CHECKING...' : 'NEXT',
                color: CartzyColors.navy,
                onClick: _isCheckingEmail ? () {} : _handleStep1Next,
              ),
            ),
          ],
        ),
      ],
    );
  }

  // ============================================================
  // STEP 2 — ADDRESS
  // ============================================================

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
        const SizedBox(height: 18),

        RegistrationTextField(
          controller: _houseStreetController,
          label: 'Street / House Number *',
          icon: Icons.home_outlined,
          isError: _houseStreetError,
          errorMessage: 'This field is required',
          onChanged: (value) {
            if (_houseStreetError && value.trim().isNotEmpty) {
              setState(() => _houseStreetError = false);
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
                    _provinceError = _selectedProvince.isEmpty;
                    _municipalityError = _selectedMunicipality.isEmpty;
                    _barangayError = _selectedBarangay.isEmpty;
                    _houseStreetError = _houseStreetController.text.trim().isEmpty;

                    if (!_provinceError &&
                        !_municipalityError &&
                        !_barangayError &&
                        !_houseStreetError) {
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

  // ============================================================
  // STEP 3 — ACCOUNT + ID UPLOAD
  // ============================================================

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
        const SizedBox(height: 30),

        _UploadField(
          label: 'Upload ID *',
          fileName: _idFileName,
          isError: _idError,
          errorMessage: 'Please upload a valid ID',
          onTap: _pickIdFile,
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
                text: _isSubmitting ? 'CREATING...' : 'CREATE ACCOUNT',
                color: CartzyColors.navy,
                onClick: _isSubmitting
                    ? () {}
                    : () {
                        setState(() {
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

                          _idError = _idFile == null;
                        });

                        if (!_passwordError &&
                            !_confirmPasswordError &&
                            !_idError) {
                          _registerBuyer();
                        }
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
// UPLOAD FIELD (placeholder — real file picking added later)
// ============================================================

class _UploadField extends StatelessWidget {
  final String label;
  final String? fileName;
  final bool isError;
  final String errorMessage;
  final VoidCallback onTap;

  const _UploadField({
    required this.label,
    required this.fileName,
    required this.isError,
    required this.errorMessage,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: CartzyColors.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isError ? CartzyColors.error : CartzyColors.border,
            width: isError ? 1.5 : 1.2,
          ),
        ),
        child: Row(
          children: [
            const Icon(Icons.upload_file, color: CartzyColors.coral),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    label,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: CartzyColors.navy,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    fileName ?? 'Tap to upload',
                    style: TextStyle(
                      fontSize: 12,
                      color: fileName != null ? CartzyColors.coral : CartzyColors.gray,
                    ),
                  ),
                  if (isError) ...[
                    const SizedBox(height: 4),
                    Text(
                      errorMessage,
                      style: const TextStyle(fontSize: 11, color: CartzyColors.error),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ============================================================
// DATE FIELD
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
          style: const TextStyle(fontSize: 15, color: CartzyColors.text),
          decoration: InputDecoration(
            labelText: 'Birthday *',
            labelStyle: const TextStyle(fontSize: 14, color: CartzyColors.gray),
            floatingLabelStyle: const TextStyle(fontSize: 14, color: CartzyColors.coral, fontWeight: FontWeight.w600),
            hintText: 'Select your birthday',
            hintStyle: const TextStyle(fontSize: 14, color: CartzyColors.gray),
            prefixIcon: const Icon(Icons.cake_outlined, color: CartzyColors.coral, size: 20),
            suffixIcon: const Icon(Icons.keyboard_arrow_down_rounded, color: CartzyColors.coral),
            errorText: isError ? errorMessage : null,
            errorStyle: const TextStyle(fontSize: 12, color: CartzyColors.error),
            filled: true,
            fillColor: CartzyColors.surface,
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.coral, width: 1.8),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.error, width: 1.2),
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
          topLeft: Radius.circular(28),
          topRight: Radius.circular(28),
        ),
      ),
      padding: const EdgeInsets.fromLTRB(24, 12, 24, 24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 40,
              height: 4,
              margin: const EdgeInsets.only(bottom: 20),
              decoration: BoxDecoration(
                color: CartzyColors.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),

          const Text(
            'Select Birthday',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: CartzyColors.navy),
          ),
          const SizedBox(height: 4),
          const Text(
            'Scroll to choose your date of birth',
            style: TextStyle(fontSize: 13, color: CartzyColors.gray),
          ),
          const SizedBox(height: 20),

          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 14),
            decoration: BoxDecoration(
              color: widget.accent.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Text(
              '${monthNames[_selectedMonth]} $_selectedDay, $_selectedYear',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: widget.accent,
              ),
            ),
          ),
          const SizedBox(height: 20),

          SizedBox(
            height: 240,
            child: Stack(
              children: [
                Row(
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
                IgnorePointer(
                  child: Center(
                    child: Container(
                      height: 48,
                      margin: const EdgeInsets.symmetric(horizontal: 4),
                      decoration: BoxDecoration(
                        color: widget.accent.withValues(alpha: 0.10),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.symmetric(
                          horizontal: BorderSide(color: widget.accent.withValues(alpha: 0.25), width: 1),
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),
          const Divider(color: CartzyColors.border),
          const SizedBox(height: 8),

          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              TextButton(
                onPressed: widget.onDismiss,
                style: TextButton.styleFrom(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                ),
                child: const Text(
                  'CANCEL',
                  style: TextStyle(color: CartzyColors.gray, fontWeight: FontWeight.w600, fontSize: 13),
                ),
              ),
              const SizedBox(width: 8),
              ElevatedButton(
                onPressed: () {
                  final formatted =
                      '${monthNames[_selectedMonth]} ${_selectedDay.toString().padLeft(2, '0')}, $_selectedYear';
                  widget.onDateSelected(formatted);
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: widget.accent,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                child: const Text('DONE', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
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
      itemExtent: 48,
      diameterRatio: 1.8,
      physics: const FixedExtentScrollPhysics(),
      controller: FixedExtentScrollController(initialItem: initialIndex),
      onSelectedItemChanged: onChanged,
      childDelegate: ListWheelChildBuilderDelegate(
        childCount: itemCount,
        builder: (context, index) {
          final isSelected = index == initialIndex;
          return Center(
            child: Text(
              labelBuilder(index),
              style: TextStyle(
                fontSize: isSelected ? 19 : 15,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                color: isSelected ? widget.accent : CartzyColors.gray.withValues(alpha: 0.6),
              ),
            ),
          );
        },
      ),
    );
  }
}

// ============================================================
// DROPDOWN — polished bottom sheet with search
// ============================================================

class RegistrationDropdown extends StatelessWidget {
  final String label;
  final IconData? icon;
  final String selectedValue;
  final List<String> options;
  final ValueChanged<String> onSelected;
  final bool isError;
  final String errorMessage;

  const RegistrationDropdown({
    super.key,
    required this.label,
    this.icon,
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
                isScrollControlled: true,
                backgroundColor: Colors.transparent,
                builder: (context) => _DropdownSheet(
                  label: label,
                  icon: icon,
                  options: options,
                ),
              );
              if (selected != null) onSelected(selected);
            },
      child: AbsorbPointer(
        child: TextField(
          controller: TextEditingController(text: selectedValue),
          readOnly: true,
          style: const TextStyle(fontSize: 15, color: CartzyColors.text),
          decoration: InputDecoration(
            labelText: label,
            labelStyle: const TextStyle(fontSize: 14, color: CartzyColors.gray),
            floatingLabelStyle: const TextStyle(fontSize: 14, color: CartzyColors.coral, fontWeight: FontWeight.w600),
            prefixIcon: Icon(icon ?? Icons.list_alt_outlined, color: CartzyColors.coral, size: 20),
            suffixIcon: const Icon(Icons.keyboard_arrow_down_rounded, color: CartzyColors.coral),
            errorText: isError ? errorMessage : null,
            errorStyle: const TextStyle(fontSize: 12, color: CartzyColors.error),
            filled: true,
            fillColor: CartzyColors.surface,
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.coral, width: 1.8),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.error, width: 1.2),
            ),
          ),
        ),
      ),
    );
  }
}

// ============================================================
// DROPDOWN BOTTOM SHEET (with search)
// ============================================================

class _DropdownSheet extends StatefulWidget {
  final String label;
  final IconData? icon;
  final List<String> options;

  const _DropdownSheet({
    required this.label,
    required this.icon,
    required this.options,
  });

  @override
  State<_DropdownSheet> createState() => _DropdownSheetState();
}

class _DropdownSheetState extends State<_DropdownSheet> {
  final TextEditingController _searchController = TextEditingController();
  late List<String> _filteredOptions;

  @override
  void initState() {
    super.initState();
    _filteredOptions = widget.options;
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _filter(String query) {
    setState(() {
      _filteredOptions = widget.options
          .where((o) => o.toLowerCase().contains(query.toLowerCase()))
          .toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.7,
      minChildSize: 0.4,
      maxChildSize: 0.9,
      expand: false,
      builder: (context, scrollController) {
        return Container(
          decoration: const BoxDecoration(
            color: CartzyColors.surface,
            borderRadius: BorderRadius.only(
              topLeft: Radius.circular(24),
              topRight: Radius.circular(24),
            ),
          ),
          child: Column(
            children: [
              const SizedBox(height: 12),
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: CartzyColors.border,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 12),
                child: Row(
                  children: [
                    Icon(widget.icon ?? Icons.list_alt_outlined, color: CartzyColors.coral, size: 22),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        widget.label.replaceAll('*', '').trim(),
                        style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: CartzyColors.navy),
                      ),
                    ),
                    Text(
                      '${widget.options.length} options',
                      style: const TextStyle(fontSize: 12, color: CartzyColors.gray),
                    ),
                  ],
                ),
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: TextField(
                  controller: _searchController,
                  onChanged: _filter,
                  style: const TextStyle(fontSize: 14, color: CartzyColors.text),
                  decoration: InputDecoration(
                    hintText: 'Search...',
                    hintStyle: const TextStyle(fontSize: 14, color: CartzyColors.gray),
                    prefixIcon: const Icon(Icons.search, color: CartzyColors.gray, size: 20),
                    filled: true,
                    fillColor: CartzyColors.background,
                    contentPadding: const EdgeInsets.symmetric(vertical: 12),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide.none,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 8),
              const Divider(height: 1, color: CartzyColors.border),
              Expanded(
                child: _filteredOptions.isEmpty
                    ? const Center(
                        child: Text(
                          'No matches found',
                          style: TextStyle(fontSize: 13, color: CartzyColors.gray),
                        ),
                      )
                    : ListView.separated(
                        controller: scrollController,
                        padding: const EdgeInsets.symmetric(vertical: 4),
                        itemCount: _filteredOptions.length,
                        separatorBuilder: (_, __) => const Divider(height: 1, color: CartzyColors.border),
                        itemBuilder: (context, index) {
                          return ListTile(
                            contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
                            title: Text(
                              _filteredOptions[index],
                              style: const TextStyle(fontSize: 15, color: CartzyColors.text),
                            ),
                            trailing: const Icon(Icons.chevron_right, color: CartzyColors.border, size: 18),
                            onTap: () => Navigator.of(context).pop(_filteredOptions[index]),
                          );
                        },
                      ),
              ),
            ],
          ),
        );
      },
    );
  }
}

// ============================================================
// SEX DROPDOWN (with icon per option)
// ============================================================

class SexDropdown extends StatelessWidget {
  final String selectedValue;
  final ValueChanged<String> onSelected;
  final bool isError;
  final String errorMessage;

  const SexDropdown({
    super.key,
    required this.selectedValue,
    required this.onSelected,
    this.isError = false,
    this.errorMessage = '',
  });

  static const List<Map<String, dynamic>> _options = [
    {'label': 'Male', 'icon': Icons.male},
    {'label': 'Female', 'icon': Icons.female},
    {'label': 'Prefer not to say', 'icon': Icons.remove_circle_outline},
  ];

  IconData get _selectedIcon {
    final match = _options.firstWhere(
      (o) => o['label'] == selectedValue,
      orElse: () => {'icon': Icons.wc},
    );
    return match['icon'] as IconData;
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () async {
        final selected = await showModalBottomSheet<String>(
          context: context,
          backgroundColor: Colors.transparent,
          builder: (context) {
            return Container(
              decoration: const BoxDecoration(
                color: CartzyColors.surface,
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(24),
                  topRight: Radius.circular(24),
                ),
              ),
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 40,
                    height: 4,
                    margin: const EdgeInsets.only(bottom: 16),
                    decoration: BoxDecoration(
                      color: CartzyColors.border,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const Align(
                    alignment: Alignment.centerLeft,
                    child: Text(
                      'Sex',
                      style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: CartzyColors.navy),
                    ),
                  ),
                  const SizedBox(height: 12),
                  ..._options.map((option) {
                    final isSelected = option['label'] == selectedValue;
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(12),
                        onTap: () => Navigator.of(context).pop(option['label'] as String),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          decoration: BoxDecoration(
                            color: isSelected ? CartzyColors.coral.withValues(alpha: 0.08) : CartzyColors.background,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: isSelected ? CartzyColors.coral : CartzyColors.border,
                              width: isSelected ? 1.6 : 1,
                            ),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                option['icon'] as IconData,
                                color: isSelected ? CartzyColors.coral : CartzyColors.gray,
                                size: 22,
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Text(
                                  option['label'] as String,
                                  style: TextStyle(
                                    fontSize: 15,
                                    fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                                    color: isSelected ? CartzyColors.coral : CartzyColors.text,
                                  ),
                                ),
                              ),
                              if (isSelected)
                                const Icon(Icons.check_circle, color: CartzyColors.coral, size: 20),
                            ],
                          ),
                        ),
                      ),
                    );
                  }),
                ],
              ),
            );
          },
        );
        if (selected != null) onSelected(selected);
      },
      child: AbsorbPointer(
        child: TextField(
          controller: TextEditingController(text: selectedValue),
          readOnly: true,
          style: const TextStyle(fontSize: 15, color: CartzyColors.text),
          decoration: InputDecoration(
            labelText: 'Sex *',
            labelStyle: const TextStyle(fontSize: 14, color: CartzyColors.gray),
            floatingLabelStyle: const TextStyle(fontSize: 14, color: CartzyColors.coral, fontWeight: FontWeight.w600),
            prefixIcon: Icon(_selectedIcon, color: CartzyColors.coral, size: 20),
            suffixIcon: const Icon(Icons.keyboard_arrow_down_rounded, color: CartzyColors.coral),
            errorText: isError ? errorMessage : null,
            errorStyle: const TextStyle(fontSize: 12, color: CartzyColors.error),
            filled: true,
            fillColor: CartzyColors.surface,
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.coral, width: 1.8),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: CartzyColors.error, width: 1.2),
            ),
          ),
        ),
      ),
    );
  }
}

// ============================================================
// SHAKE WIDGET
// ============================================================
//
// Wrap any field with this + a GlobalKey<ShakeWidgetState> to trigger a
// horizontal "shake" (e.g. when a duplicate email is detected):
//
//   final _emailShakeKey = GlobalKey<ShakeWidgetState>();
//   ShakeWidget(key: _emailShakeKey, child: someField)
//   ...
//   _emailShakeKey.currentState?.shake();

class ShakeWidget extends StatefulWidget {
  final Widget child;

  const ShakeWidget({super.key, required this.child});

  @override
  State<ShakeWidget> createState() => ShakeWidgetState();
}

class ShakeWidgetState extends State<ShakeWidget> with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 420),
  );

  late final Animation<double> _offset = TweenSequence<double>([
    TweenSequenceItem(tween: Tween(begin: 0.0, end: -10.0), weight: 1),
    TweenSequenceItem(tween: Tween(begin: -10.0, end: 10.0), weight: 2),
    TweenSequenceItem(tween: Tween(begin: 10.0, end: -8.0), weight: 2),
    TweenSequenceItem(tween: Tween(begin: -8.0, end: 6.0), weight: 2),
    TweenSequenceItem(tween: Tween(begin: 6.0, end: 0.0), weight: 1),
  ]).animate(CurvedAnimation(parent: _controller, curve: Curves.linear));

  void shake() {
    _controller.forward(from: 0);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _offset,
      builder: (context, child) {
        return Transform.translate(
          offset: Offset(_offset.value, 0),
          child: child,
        );
      },
      child: widget.child,
    );
  }
}

// ============================================================
// TEXT FIELD
// ============================================================

class RegistrationTextField extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final IconData? icon;
  final bool isError;
  final String errorMessage;
  final bool obscureText;
  final Widget? trailing;
  final ValueChanged<String>? onChanged;

  const RegistrationTextField({
    super.key,
    required this.controller,
    required this.label,
    this.icon,
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
      style: const TextStyle(fontSize: 15, color: CartzyColors.text),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(fontSize: 14, color: CartzyColors.gray),
        floatingLabelStyle: const TextStyle(fontSize: 14, color: CartzyColors.coral, fontWeight: FontWeight.w600),
        prefixIcon: Icon(icon ?? Icons.edit_outlined, color: CartzyColors.coral, size: 20),
        suffixIcon: trailing,
        errorText: isError ? errorMessage : null,
        errorStyle: const TextStyle(fontSize: 12, color: CartzyColors.error),
        filled: true,
        fillColor: CartzyColors.surface,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: CartzyColors.coral, width: 1.8),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: CartzyColors.error, width: 1.2),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: CartzyColors.error, width: 1.8),
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
      height: 54,
      child: ElevatedButton(
        onPressed: onClick,
        style: ElevatedButton.styleFrom(
          backgroundColor: color,
          foregroundColor: Colors.white,
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        child: Text(
          text,
          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, letterSpacing: 0.3),
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
      height: 54,
      child: OutlinedButton(
        onPressed: onClick,
        style: OutlinedButton.styleFrom(
          foregroundColor: color,
          side: BorderSide(color: color, width: 1.4),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        child: Text(
          text,
          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, letterSpacing: 0.3),
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
  final List<IconData> icons;

  const StepIndicator({
    super.key,
    required this.currentStep,
    required this.accent,
    required this.gray,
    required this.icons,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      color: CartzyColors.surface,
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 18),
      child: Row(
        children: [
          for (int i = 0; i < icons.length; i++) ...[
            StepCircle(
              icon: icons[i],
              active: currentStep >= i + 1,
              accent: accent,
              gray: gray,
            ),
            if (i < icons.length - 1) ...[
              const SizedBox(width: 8),
              Expanded(
                child: Divider(
                  color: currentStep >= i + 2 ? accent : gray,
                  thickness: 1,
                ),
              ),
              const SizedBox(width: 8),
            ],
          ],
        ],
      ),
    );
  }
}

// ============================================================
// STEP CIRCLE
// ============================================================

class StepCircle extends StatelessWidget {
  final IconData icon;
  final bool active;
  final Color accent;
  final Color gray;

  const StepCircle({
    super.key,
    required this.icon,
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
      child: Icon(
        icon,
        color: Colors.white,
        size: 17,
      ),
    );
  }
}
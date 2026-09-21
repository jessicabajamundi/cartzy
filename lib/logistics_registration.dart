import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';

import 'cartzy_colors.dart';
import 'services/api_service.dart';
import 'buyer_registration.dart'; // reuses PsgcApi + shared form widgets + checkEmailAvailable

// ============================================================
// LOGISTICS / SORTING CENTER REGISTRATION
// ============================================================

class LogisticsRegistration extends StatefulWidget {
  final VoidCallback onRegistrationSubmitted;
  final VoidCallback onBackToLogin;

  const LogisticsRegistration({
    super.key,
    required this.onRegistrationSubmitted,
    required this.onBackToLogin,
  });

  @override
  State<LogisticsRegistration> createState() => _LogisticsRegistrationState();
}

class _LogisticsRegistrationState extends State<LogisticsRegistration> {
  // ========================================================
  // STEP
  // ========================================================

  int _currentStep = 1;

  // ========================================================
  // PERSONAL INFORMATION
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

  // ========================================================
  // BUSINESS + DOCUMENTS
  // ========================================================

  final _businessNameController = TextEditingController();
  bool _businessNameError = false;

  String? _idFileName;
  XFile? _idFile;

  String? _permitFileName;
  XFile? _permitFile;

  bool _idError = false;
  bool _permitError = false;

  // ========================================================
  // ACCOUNT
  // ========================================================

  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _passwordVisible = false;
  bool _confirmPasswordVisible = false;

  bool _passwordError = false;
  bool _confirmPasswordError = false;
  String _confirmPasswordMessage = 'This field is required';

  bool _isSubmitting = false;

  // ========================================================
  // PROVINCES / MUNICIPALITIES / BARANGAYS (PSGC API)
  // ========================================================

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
    _lastNameController.dispose();
    _firstNameController.dispose();
    _middleInitialController.dispose();
    _emailController.dispose();
    _contactNoController.dispose();
    _houseStreetController.dispose();
    _businessNameController.dispose();
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

  String _birthdayForDatabase() {
    if (_birthday.isEmpty) return '';
    try {
      final parts = _birthday.replaceAll(',', '').split(' ');
      const months = <String, String>{
        'January': '01', 'February': '02', 'March': '03', 'April': '04',
        'May': '05', 'June': '06', 'July': '07', 'August': '08',
        'September': '09', 'October': '10', 'November': '11', 'December': '12',
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

  Future<void> _pickIdFile() async {
    try {
      final picker = ImagePicker();
      final XFile? pickedFile = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (pickedFile == null) return;
      setState(() {
        _idFile = pickedFile;
        _idFileName = pickedFile.name;
        _idError = false;
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Unable to select ID photo: $e')),
      );
    }
  }

  Future<void> _pickPermitFile() async {
    try {
      final picker = ImagePicker();
      final XFile? pickedFile = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (pickedFile == null) return;
      setState(() {
        _permitFile = pickedFile;
        _permitFileName = pickedFile.name;
        _permitError = false;
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Unable to select business/DTI permit: $e')),
      );
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
      _houseStreetError = _houseStreetController.text.trim().isEmpty;
      _provinceError = _selectedProvince.isEmpty;
      _municipalityError = _selectedMunicipality.isEmpty;
      _barangayError = _selectedBarangay.isEmpty;
    });

    if (_lastNameError ||
        _firstNameError ||
        _sexError ||
        _emailError ||
        _contactNoError ||
        _birthdayError ||
        _houseStreetError ||
        _provinceError ||
        _municipalityError ||
        _barangayError) {
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

    setState(() {
      _isCheckingEmail = false;
      _currentStep = 2;
    });
  }

  // ============================================================
  // SUBMIT
  // ============================================================

  Future<void> _registerLogistics() async {
    setState(() => _isSubmitting = true);

    try {
      final request = http.MultipartRequest(
        'POST',
        Uri.parse('${ApiService.baseUrl}/register/logistics'),
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
      request.fields['business_name'] = _businessNameController.text.trim();

      if (_idFile != null) {
        final idBytes = await _idFile!.readAsBytes();
        request.files.add(
          http.MultipartFile.fromBytes(
            'id_photo',
            idBytes,
            filename: _idFileName ?? 'id.jpg',
          ),
        );
      }

      if (_permitFile != null) {
        final permitBytes = await _permitFile!.readAsBytes();
        request.files.add(
          http.MultipartFile.fromBytes(
            'permit_photo',
            permitBytes,
            filename: _permitFileName ?? 'permit.jpg',
          ),
        );
      }

      final response = await request.send();
      final responseBody = await response.stream.bytesToString();
      final data = responseBody.isNotEmpty ? jsonDecode(responseBody) : <String, dynamic>{};

      if (response.statusCode < 200 || response.statusCode >= 300) {
        if (response.statusCode == 409) {
          setState(() {
            _isSubmitting = false;
            _currentStep = 1;
            _emailError = true;
            _emailErrorMessage = 'This email is already registered. Try logging in instead.';
          });
          WidgetsBinding.instance.addPostFrameCallback((_) {
            _emailShakeKey.currentState?.shake();
          });
          return;
        }

        throw Exception(
          data['error'] ?? data['message'] ?? 'Registration failed (${response.statusCode})',
        );
      }

      if (!mounted) return;

      setState(() => _isSubmitting = false);

      _showPendingApprovalDialog();
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
            // =================================================
            // HEADER
            // =================================================
            Container(
              width: double.infinity,
              color: CartzyColors.surface,
              padding: const EdgeInsets.only(
                left: 8,
                right: 24,
                top: 8,
                bottom: 18,
              ),
              child: Stack(
                alignment: Alignment.center,
                children: [
                  Align(
                    alignment: Alignment.centerLeft,
                    child: IconButton(
                      icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
                      onPressed: widget.onBackToLogin,
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.only(top: 20),
                    child: const Column(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Text(
                          'Register Your Logistics Hub',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.w700,
                            color: CartzyColors.navy,
                            letterSpacing: -0.3,
                          ),
                        ),
                        SizedBox(height: 8),
                        Text(
                          'LOGISTICS / SORTING CENTER REGISTRATION',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: CartzyColors.coral,
                            letterSpacing: 1.0,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // =================================================
            // STEP INDICATOR
            // =================================================
            StepIndicator(
              currentStep: _currentStep,
              accent: CartzyColors.coral,
              gray: CartzyColors.border,
              icons: const [
                Icons.person_outline,
                Icons.local_shipping_outlined,
                Icons.lock_outline,
              ],
            ),
            // =================================================
            // FORM
            // =================================================
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
  // STEP 1 — PERSONAL INFO + ADDRESS
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
          'Tell us about yourself.',
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
            hintText: 'Select birthday first',
            hintStyle: const TextStyle(fontSize: 13, color: CartzyColors.gray),
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
        const SizedBox(height: 30),

        // -----------------------------------------------------
        // ADDRESS
        // -----------------------------------------------------
        const Text(
          'Address',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
        const SizedBox(height: 16),

        RegistrationTextField(
          controller: _houseStreetController,
          label: 'House Number / Street *',
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

        PrimaryButton(
          text: _isCheckingEmail ? 'CHECKING...' : 'NEXT',
          color: CartzyColors.navy,
          onClick: _isCheckingEmail ? () {} : _handleStep1Next,
        ),
      ],
    );
  }

  // ============================================================
  // STEP 2 — BUSINESS + DOCUMENTS
  // ============================================================

  Widget _buildStep2() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Business Information',
          style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
        const SizedBox(height: 8),
        const Text(
          'Tell us about your logistics/sorting center.',
          style: TextStyle(fontSize: 13, color: CartzyColors.gray),
        ),
        const SizedBox(height: 28),

        RegistrationTextField(
          controller: _businessNameController,
          label: 'Business Name',
          icon: Icons.storefront_outlined,
          isError: _businessNameError,
          errorMessage: 'Business name is required',
          onChanged: (value) {
            if (_businessNameError && value.trim().isNotEmpty) {
              setState(() => _businessNameError = false);
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
        const SizedBox(height: 18),

        _UploadField(
          label: 'Upload Business / DTI Permit *',
          fileName: _permitFileName,
          isError: _permitError,
          errorMessage: 'Please upload your business or DTI permit',
          onTap: _pickPermitFile,
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
                    _idError = _idFile == null;
                    _permitError = _permitFile == null;

                    if (!_idError && !_permitError) {
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
  // STEP 3 — ACCOUNT
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
          'Create your Cartzy Logistics login details.',
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
                text: _isSubmitting ? 'SUBMITTING...' : 'SUBMIT',
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
                        });

                        if (!_passwordError && !_confirmPasswordError) {
                          _registerLogistics();
                        }
                      },
              ),
            ),
          ],
        ),
      ],
    );
  }

  // ============================================================
  // PENDING APPROVAL DIALOG
  // ============================================================

  void _showPendingApprovalDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Row(
            children: [
              Icon(Icons.hourglass_top, color: CartzyColors.coral),
              SizedBox(width: 10),
              Text(
                'Registration Submitted',
                style: TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          content: const Text(
            "After submitting your registration, please wait for the "
            "administrator's approval, which will be sent to your email.",
            style: TextStyle(fontSize: 14, color: CartzyColors.gray),
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
                widget.onRegistrationSubmitted();
              },
              child: const Text(
                'OK',
                style: TextStyle(color: CartzyColors.coral, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        );
      },
    );
  }
}

// ============================================================
// UPLOAD FIELD
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
import 'dart:convert';
import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'buyer_registration_validation.dart';
import 'package:cartzy/widgets/cartzy_logo.dart';
import 'package:flutter/gestures.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';
import 'package:cartzy/services/user_account_service.dart';
import 'package:cartzy/services/google_sign_in_service.dart';
import 'package:cartzy/services/api_service.dart' show ApiException;
import 'package:cartzy/services/auth_service.dart';
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
  await Future.delayed(const Duration(milliseconds: 100));
  return true;
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
  final VoidCallback? onSwitchToRiderRegistration;

  const BuyerRegistration({
    super.key,
    required this.onRegistrationSubmitted,
    required this.onBackToLogin,
    this.onSwitchToRiderRegistration,
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
  final _emailController = TextEditingController();
  final _contactNoController = TextEditingController();
  final _emailShakeKey = GlobalKey<ShakeWidgetState>();

  String _selectedSex = '';
  String _birthday = '';
  int? _age;

  bool _lastNameError = false;
  String? _lastNameErrorMessage;
  bool _firstNameError = false;
  String? _firstNameErrorMessage;
  bool _sexError = false;
  bool _emailError = false;
  String? _emailErrorMessage;
  bool _contactNoError = false;
  String? _contactNoErrorMessage;
  bool _birthdayError = false;
  String? _birthdayErrorMessage;
  final bool _isCheckingEmail = false;

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
  String? _passwordErrorMessage;
  bool _confirmPasswordError = false;
  String _confirmPasswordMessage = 'This field is required';

  String? _idFileName;
  XFile? _idFile;
  bool _idError = false;

  bool _agreeToTerms = false;
  bool _agreeToPrivacy = false;
  bool _termsError = false;
  bool _privacyError = false;

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
  // STEP 1 → STEP 2: validate fields (TC-BUY-010 to 016)
  // ============================================================

  Future<void> _handleStep1Next() async {
    final rawEmail = _emailController.text;
    final trimmedEmail = rawEmail.trim(); // TC-BUY-012: Trim spaces before and after
    _emailController.text = trimmedEmail;

    final rawPhone = _contactNoController.text;
    final trimmedPhone = rawPhone.trim();

    final fName = _firstNameController.text.trim();
    final lName = _lastNameController.text.trim();

    setState(() {
      _firstNameErrorMessage = BuyerRegistrationValidation.name(fName, 'first name');
      _firstNameError = _firstNameErrorMessage != null;
      _lastNameErrorMessage = BuyerRegistrationValidation.name(lName, 'last name');
      _lastNameError = _lastNameErrorMessage != null;
      _sexError = _selectedSex.isEmpty;

      // Birthday & Age validation (18+ requirement)
      if (_birthday.isEmpty) {
        _birthdayError = true;
        _birthdayErrorMessage = 'Please enter your birthday.';
      } else if (_age != null && _age! < 18) {
        _birthdayError = true;
        _birthdayErrorMessage = 'You must be at least 18 years old to register.';
      } else {
        _birthdayError = false;
        _birthdayErrorMessage = null;
      }

      // Email validation (TC-BUY-010, TC-BUY-011, TC-BUY-012)
      final emailFormatError = BuyerRegistrationValidation.email(trimmedEmail);
      if (emailFormatError != null) {
        _emailError = true;
        _emailErrorMessage = emailFormatError;
      } else if (UserAccountService.instance.isEmailRegistered(trimmedEmail)) {
        // TC-BUY-010 & TC-BUY-011: Duplicate check (case-insensitive)
        _emailError = true;
        _emailErrorMessage = 'This email is already in use. No account created.';
      } else {
        _emailError = false;
        _emailErrorMessage = null;
      }

      // Phone: normalize +63 to 09 on blur before validation (TC-BUY-016)
      final normalizedPhone = BuyerRegistrationValidation.phoneForApi(trimmedPhone);


      // Mobile number validation (TC-BUY-013, TC-BUY-014, TC-BUY-015, TC-BUY-016)
      final phoneFormatError = BuyerRegistrationValidation.phone(trimmedPhone);
      if (phoneFormatError != null) {
        _contactNoError = true;
        _contactNoErrorMessage = phoneFormatError;
      } else if (UserAccountService.instance.isPhoneRegistered(normalizedPhone)) {
        // TC-BUY-015 & TC-BUY-016: Duplicate mobile check (normalized +63 / 09)
        _contactNoError = true;
        _contactNoErrorMessage = 'This mobile number is already in use.';
      } else {
        _contactNoError = false;
        _contactNoErrorMessage = null;
      }
    });

    if (_emailError) {
      _emailShakeKey.currentState?.shake();
    }

    if (_lastNameError ||
        _firstNameError ||
        _sexError ||
        _emailError ||
        _contactNoError ||
        _birthdayError) {
      return;
    }

    setState(() {
      _currentStep = 2;
    });
  }

  // ============================================================
  // TERMS & PRIVACY MODAL DIALOGS
  // ============================================================

  void _showTermsDialog() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.75,
        maxChildSize: 0.9,
        minChildSize: 0.5,
        builder: (_, controller) => Container(
          decoration: const BoxDecoration(
            color: CartzyColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          padding: const EdgeInsets.all(24),
          child: ListView(
            controller: controller,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Cartzy Terms of Service', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
                  IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const Divider(color: CartzyColors.border),
              const SizedBox(height: 12),
              const Text(
                '1. Acceptance of Terms\nBy creating a buyer account on Cartzy, you agree to comply with and be bound by these Terms of Service.\n\n'
                '2. Eligibility\nYou must be at least 18 years of age to register an account and place orders.\n\n'
                '3. Account Responsibility\nYou are responsible for maintaining the confidentiality of your credentials and OTP verification codes.\n\n'
                '4. Orders and Payments\nAll orders placed through Cartzy are subject to product availability and payment verification.\n\n'
                '5. Delivery and Returns\nDeliveries are handled by registered Cartzy riders. Returns and refunds follow standard Cartzy policies.',
                style: TextStyle(fontSize: 13, height: 1.5, color: CartzyColors.text),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showPrivacyPolicyDialog() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.75,
        maxChildSize: 0.9,
        minChildSize: 0.5,
        builder: (_, controller) => Container(
          decoration: const BoxDecoration(
            color: CartzyColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          padding: const EdgeInsets.all(24),
          child: ListView(
            controller: controller,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Cartzy Privacy Policy', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
                  IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(ctx)),
                ],
              ),
              const Divider(color: CartzyColors.border),
              const SizedBox(height: 12),
              const Text(
                '1. Information We Collect\nWe collect personal details such as your full name, email address, contact number, delivery address, and government-issued ID for identity verification.\n\n'
                '2. Use of Information\nYour information is used solely to process your orders, facilitate deliveries, prevent fraudulent activity, and comply with Philippine Republic Act No. 10173 (Data Privacy Act of 2012).\n\n'
                '3. Data Protection\nCartzy implements industry-standard encryption and security protocols to safeguard your credentials and personal records.',
                style: TextStyle(fontSize: 13, height: 1.5, color: CartzyColors.text),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ============================================================
  // PASSWORD STRENGTH CHECKLIST WIDGET
  // ============================================================

  Widget _buildPasswordChecklist() {
    final pass = _passwordController.text;
    final hasLength = pass.length >= 8;
    final hasUpper = RegExp(r'[A-Z]').hasMatch(pass);
    final hasLower = RegExp(r'[a-z]').hasMatch(pass);
    final hasNumber = RegExp(r'[0-9]').hasMatch(pass);
    final hasSpecial = RegExp(r'[!@#\$%^&*(),.?":{}|<>_\+\-=\\/\[\]~`]').hasMatch(pass);

    int satisfiedCount = 0;
    if (hasLength) satisfiedCount++;
    if (hasUpper) satisfiedCount++;
    if (hasLower) satisfiedCount++;
    if (hasNumber) satisfiedCount++;
    if (hasSpecial) satisfiedCount++;

    Color strengthColor = CartzyColors.error;
    String strengthText = 'Weak';
    double strengthPercent = 0.2;
    if (satisfiedCount >= 5) {
      strengthColor = Colors.green;
      strengthText = 'Strong';
      strengthPercent = 1.0;
    } else if (satisfiedCount >= 3) {
      strengthColor = Colors.orange;
      strengthText = 'Medium';
      strengthPercent = 0.6;
    }

    if (pass.isEmpty) {
      return const SizedBox.shrink();
    }

    Widget item(bool satisfied, String text) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 2),
        child: Row(
          children: [
            Icon(
              satisfied ? Icons.check_circle : Icons.radio_button_unchecked,
              size: 14,
              color: satisfied ? Colors.green : CartzyColors.gray,
            ),
            const SizedBox(width: 8),
            Text(
              text,
              style: TextStyle(
                fontSize: 11,
                color: satisfied ? CartzyColors.brandDarkest : CartzyColors.gray,
                fontWeight: satisfied ? FontWeight.w600 : FontWeight.normal,
              ),
            ),
          ],
        ),
      );
    }

    return Container(
      margin: const EdgeInsets.only(top: 8, bottom: 4),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: CartzyColors.brandLight,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: CartzyColors.brandBorder),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Password Strength:', style: TextStyle(fontSize: 11, color: CartzyColors.gray)),
              Text(strengthText, style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: strengthColor)),
            ],
          ),
          const SizedBox(height: 6),
          ClipRRect(
            borderRadius: BorderRadius.circular(4),
            child: LinearProgressIndicator(
              value: strengthPercent,
              backgroundColor: CartzyColors.brandBorder,
              valueColor: AlwaysStoppedAnimation<Color>(strengthColor),
              minHeight: 4,
            ),
          ),
          const SizedBox(height: 8),
          item(hasLength, 'At least 8 characters (required)'),
          item(hasUpper, 'At least one uppercase letter [A-Z] (recommended)'),
          item(hasLower, 'At least one lowercase letter [a-z] (recommended)'),
          item(hasNumber, 'At least one number [0-9] (recommended)'),
          item(hasSpecial, 'At least one special character [!@#\$%...] (recommended)'),
        ],
      ),
    );
  }

  // ============================================================
  // STEP 3: CREATE ACCOUNT (TC-BUY-017 to 022)
  // ============================================================

  Future<void> _registerBuyer() async {
    // TC-BUY-022: Prevent multiple clicks in quick succession
    if (_isSubmitting) return;

    setState(() {
      // TC-BUY-017, TC-BUY-018: Password length and complexity
      final passError = BuyerRegistrationValidation.password(_passwordController.text);
      if (passError != null) {
        _passwordError = true;
        _passwordErrorMessage = passError;
      } else {
        _passwordError = false;
        _passwordErrorMessage = null;
      }

      // TC-BUY-019: Confirm password match
      if (_confirmPasswordController.text.isEmpty) {
        _confirmPasswordMessage = 'Please confirm your password';
        _confirmPasswordError = true;
      } else if (_confirmPasswordController.text != _passwordController.text) {
        _confirmPasswordMessage = 'The password confirmation does not match.';
        _confirmPasswordError = true;
      } else {
        _confirmPasswordError = false;
      }

      _idError = _idFile == null;

      // TC-BUY-021: Terms AND Privacy Policy must both be accepted
      _termsError = !_agreeToTerms;
      _privacyError = !_agreeToPrivacy;
    });

    if (_passwordError ||
        _confirmPasswordError ||
        _idError ||
        _termsError ||
        _privacyError) {
      return;
    }

    setState(() => _isSubmitting = true);

    final firstName = _firstNameController.text.trim();
    final lastName = _lastNameController.text.trim();
    final email = _emailController.text.trim().toLowerCase();
    final phone = BuyerRegistrationValidation.phoneForApi(_contactNoController.text.trim());

    try {
      // The server emails the code; the account is created once it's entered.
      await AuthService.requestRegistrationCode(
        email: email,
        name: '$firstName $lastName',
        phone: phone,
      );

      if (!mounted) return;
      setState(() => _isSubmitting = false);
      showCartzyFlash(context, 'We sent a 6-digit code to $email');

      widget.onRegistrationSubmitted({
        'first_name': firstName,
        'last_name': lastName,
        'email': email,
        'phone': phone,
        'birthday': _birthdayForDatabase(),
        'password': _passwordController.text,
        'address': {
          'name': '$firstName $lastName',
          'phone': phone,
          'street': _houseStreetController.text.trim(),
          'barangay': _selectedBarangay,
          'municipality': _selectedMunicipality,
          'province': _selectedProvince,
        },
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      final errors = e.body['errors'];
      setState(() {
        _isSubmitting = false;
        // Taken email or phone: send the buyer back to fix it on step 1.
        if (errors is Map && errors['email'] is List) {
          _emailError = true;
          _emailErrorMessage = (errors['email'] as List).first.toString();
          _currentStep = 1;
        }
        if (errors is Map && errors['phone'] is List) {
          _contactNoError = true;
          _contactNoErrorMessage = (errors['phone'] as List).first.toString();
          _currentStep = 1;
        }
      });
      showCartzyFlash(context, e.message);
    } catch (_) {
      if (!mounted) return;
      setState(() => _isSubmitting = false);
      showCartzyFlash(context, "Couldn't reach the server. Check your connection and try again.");
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              CartzyRegisterHeader(
                title: 'Create your account',
                subtitle: 'Join cartzy and discover your everyday favorites',
                onBack: widget.onBackToLogin,
              ),
              const SizedBox(height: 12),
              StepIndicator(
                currentStep: _currentStep,
                labels: const ['Details', 'Sign-in', 'Verify'],
              ),
              const SizedBox(height: 16),
              if (_currentStep == 1) _buildStep1(),
              if (_currentStep == 2) _buildStep2(),
              if (_currentStep == 3) _buildStep3(),
            ],
          ),
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
        RoleSelectionSection(
          isBuyerSelected: true,
          onSelectBuyer: () {},
          onSelectRider: () {
            widget.onSwitchToRiderRegistration?.call();
          },
        ),
        const SizedBox(height: 22),
        Text(
          'Your personal details',
          style: GoogleFonts.dmSans(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          'Enter your name and personal details',
          style: GoogleFonts.dmSans(
            fontSize: 12.5,
            color: const Color(0xFF7A6E72),
          ),
        ),
        const SizedBox(height: 18),

        RegistrationTextField(
          controller: _firstNameController,
          label: 'First name *',
          validator: (value) => BuyerRegistrationValidation.name(value, 'first name'),
          maxLength: 50,
          inputFormatters: [FilteringTextInputFormatter.allow(BuyerRegistrationValidation.nameCharacters)],
          icon: Icons.person_outline,
          isError: _firstNameError,
          errorMessage: _firstNameErrorMessage ?? 'First name is required',
          onChanged: (value) {
            if (_firstNameError && value.trim().isNotEmpty) {
              setState(() {
                _firstNameError = false;
                _firstNameErrorMessage = null;
              });
            }
          },
        ),
        const SizedBox(height: 16),

        RegistrationTextField(
          controller: _lastNameController,
          label: 'Last name *',
          validator: (value) => BuyerRegistrationValidation.name(value, 'last name'),
          maxLength: 50,
          inputFormatters: [FilteringTextInputFormatter.allow(BuyerRegistrationValidation.nameCharacters)],
          icon: Icons.person_outline,
          isError: _lastNameError,
          errorMessage: _lastNameErrorMessage ?? 'Last name is required',
          onChanged: (value) {
            if (_lastNameError && value.trim().isNotEmpty) {
              setState(() {
                _lastNameError = false;
                _lastNameErrorMessage = null;
              });
            }
          },
        ),
        const SizedBox(height: 16),

        RegistrationTextField(
          controller: _contactNoController,
          label: 'Mobile number *',
          validator: BuyerRegistrationValidation.phone,
          validateOnChange: true,
          isPhone: true,
          isError: _contactNoError,
          errorMessage: _contactNoErrorMessage ?? 'Contact number is required',
          onChanged: (value) {
            if (_contactNoError) {
              setState(() {
                _contactNoError = false;
                _contactNoErrorMessage = null;
              });
            }
          },
        ),
        const SizedBox(height: 16),

        RegistrationDateField(
          birthday: _birthday,
          onClick: _openBirthdayPicker,
          isError: _birthdayError,
          errorMessage: _birthdayErrorMessage ?? 'Please enter your birthday.',
        ),
        const SizedBox(height: 16),

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
        const SizedBox(height: 16),

        ShakeWidget(
          key: _emailShakeKey,
          child: RegistrationTextField(
            controller: _emailController,
            label: 'E-mail *',
            validator: BuyerRegistrationValidation.email,
            maxLength: 255,
            keyboardType: TextInputType.emailAddress,
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
        const SizedBox(height: 28),

        PrimaryButton(
          text: _isCheckingEmail ? 'Checking...' : 'Continue',
          onClick: _isCheckingEmail ? () {} : _handleStep1Next,
        ),
        RegistrationFooter(
          isBuyer: true,
          onBackToLogin: widget.onBackToLogin,
          onSwitchRole: widget.onSwitchToRiderRegistration,
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
        Text(
          'Delivery address',
          style: GoogleFonts.dmSans(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          'Where should we deliver your orders?',
          style: GoogleFonts.dmSans(
            fontSize: 12.5,
            color: const Color(0xFF7A6E72),
          ),
        ),
        const SizedBox(height: 20),

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
        const SizedBox(height: 16),

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
        const SizedBox(height: 16),

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
        const SizedBox(height: 16),

        RegistrationTextField(
          controller: _houseStreetController,
          maxLength: 500,
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
        const SizedBox(height: 28),

        Row(
          children: [
            Expanded(
              child: SecondaryButton(
                text: 'Back',
                onClick: () => setState(() => _currentStep = 1),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: PrimaryButton(
                text: 'Continue',
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
        RegistrationFooter(
          isBuyer: true,
          onBackToLogin: widget.onBackToLogin,
          onSwitchRole: widget.onSwitchToRiderRegistration,
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
        Text(
          'Account & security',
          style: GoogleFonts.dmSans(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          'Create your Cartzy login details and upload ID',
          style: GoogleFonts.dmSans(
            fontSize: 12.5,
            color: const Color(0xFF7A6E72),
          ),
        ),
        const SizedBox(height: 20),

        RegistrationTextField(
          controller: _passwordController,
          label: 'Password *',
          validator: BuyerRegistrationValidation.password,
          validateOnChange: true,
          icon: Icons.lock_outline,
          isError: _passwordError,
          errorMessage: _passwordErrorMessage ?? 'Password is required',
          obscureText: !_passwordVisible,
          trailing: IconButton(
            icon: Icon(
              _passwordVisible ? Icons.visibility_off : Icons.visibility,
              color: const Color(0xFF7A6E72),
            ),
            onPressed: () => setState(() => _passwordVisible = !_passwordVisible),
          ),
          onChanged: (value) {
            setState(() {
              if (_passwordError) {
                _passwordError = false;
                _passwordErrorMessage = null;
              }
            });
          },
        ),
        _buildPasswordChecklist(),
        const SizedBox(height: 16),

        RegistrationTextField(
          controller: _confirmPasswordController,
          label: 'Confirm Password *',
          validator: (value) => value == _passwordController.text ? null : 'The password confirmation does not match.',
          validateOnChange: true,
          icon: Icons.lock_outline,
          isError: _confirmPasswordError,
          errorMessage: _confirmPasswordMessage,
          obscureText: !_confirmPasswordVisible,
          trailing: IconButton(
            icon: Icon(
              _confirmPasswordVisible ? Icons.visibility_off : Icons.visibility,
              color: const Color(0xFF7A6E72),
            ),
            onPressed: () => setState(() => _confirmPasswordVisible = !_confirmPasswordVisible),
          ),
          onChanged: (value) {
            if (_confirmPasswordError) {
              setState(() => _confirmPasswordError = false);
            }
          },
        ),
        const SizedBox(height: 20),

        RegistrationUploadField(
          label: 'Upload ID *',
          fileName: _idFileName,
          isError: _idError,
          errorMessage: 'Please upload a valid ID',
          onTap: _pickIdFile,
        ),
        const SizedBox(height: 20),

        // ── Terms of Service ──
        InkWell(
          onTap: () {
            setState(() {
              _agreeToTerms = !_agreeToTerms;
              if (_agreeToTerms) _termsError = false;
            });
          },
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 4),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  width: 24,
                  height: 24,
                  child: Checkbox(
                    value: _agreeToTerms,
                    activeColor: const Color(0xFF865561),
                    side: BorderSide(
                      color: _termsError ? CartzyColors.error : const Color(0xFFD0C3C5),
                      width: 1.5,
                    ),
                    onChanged: (val) {
                      setState(() {
                        _agreeToTerms = val ?? false;
                        if (_agreeToTerms) _termsError = false;
                      });
                    },
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: RichText(
                    text: TextSpan(
                      style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF231F20), height: 1.4),
                      children: [
                        const TextSpan(text: 'I have read and agree to the '),
                        TextSpan(
                          text: 'Terms of Service',
                          style: const TextStyle(
                            color: Color(0xFF865561),
                            fontWeight: FontWeight.bold,
                            decoration: TextDecoration.underline,
                          ),
                          recognizer: TapGestureRecognizer()..onTap = _showTermsDialog,
                        ),
                        const TextSpan(text: ' *', style: TextStyle(color: Color(0xFFD32F2F))),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        if (_termsError) ...[
          const SizedBox(height: 4),
          const Padding(
            padding: EdgeInsets.only(left: 36),
            child: Text(
              'You must accept the Terms of Service to continue.',
              style: TextStyle(fontSize: 12, color: CartzyColors.error, fontWeight: FontWeight.w500),
            ),
          ),
        ],
        const SizedBox(height: 12),

        // ── Privacy Policy ──
        InkWell(
          onTap: () {
            setState(() {
              _agreeToPrivacy = !_agreeToPrivacy;
              if (_agreeToPrivacy) _privacyError = false;
            });
          },
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 4),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  width: 24,
                  height: 24,
                  child: Checkbox(
                    value: _agreeToPrivacy,
                    activeColor: const Color(0xFF865561),
                    side: BorderSide(
                      color: _privacyError ? CartzyColors.error : const Color(0xFFD0C3C5),
                      width: 1.5,
                    ),
                    onChanged: (val) {
                      setState(() {
                        _agreeToPrivacy = val ?? false;
                        if (_agreeToPrivacy) _privacyError = false;
                      });
                    },
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: RichText(
                    text: TextSpan(
                      style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF231F20), height: 1.4),
                      children: [
                        const TextSpan(text: 'I have read and agree to the '),
                        TextSpan(
                          text: 'Privacy Policy',
                          style: const TextStyle(
                            color: Color(0xFF865561),
                            fontWeight: FontWeight.bold,
                            decoration: TextDecoration.underline,
                          ),
                          recognizer: TapGestureRecognizer()..onTap = _showPrivacyPolicyDialog,
                        ),
                        const TextSpan(text: ' *', style: TextStyle(color: Color(0xFFD32F2F))),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        if (_privacyError) ...[
          const SizedBox(height: 4),
          const Padding(
            padding: EdgeInsets.only(left: 36),
            child: Text(
              'You must accept the Privacy Policy to continue.',
              style: TextStyle(fontSize: 12, color: CartzyColors.error, fontWeight: FontWeight.w500),
            ),
          ),
        ],
        const SizedBox(height: 28),

        Row(
          children: [
            Expanded(
              child: SecondaryButton(
                text: 'Back',
                onClick: () => setState(() => _currentStep = 2),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: PrimaryButton(
                text: _isSubmitting ? 'Sending code...' : 'Create account',
                onClick: _isSubmitting ? () {} : _registerBuyer,
              ),
            ),
          ],
        ),
        RegistrationFooter(
          isBuyer: true,
          onBackToLogin: widget.onBackToLogin,
          onSwitchRole: widget.onSwitchToRiderRegistration,
        ),
      ],
    );
  }
}

// ============================================================
// UPLOAD FIELD (Screenshot Reference Style)
// ============================================================

class RegistrationUploadField extends StatelessWidget {
  final String label;
  final String? fileName;
  final bool isError;
  final String errorMessage;
  final VoidCallback onTap;
  final String? helperText;

  const RegistrationUploadField({
    super.key,
    required this.label,
    required this.fileName,
    required this.isError,
    required this.errorMessage,
    required this.onTap,
    this.helperText,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: GoogleFonts.dmSans(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 6),
        GestureDetector(
          onTap: onTap,
          child: Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: isError ? const Color(0xFFD32F2F) : const Color(0xFFE5D7D7),
                width: 1.0,
              ),
            ),
            child: Row(
              children: [
                const Icon(
                  Icons.file_upload_outlined,
                  color: Color(0xFF865561),
                  size: 22,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    fileName ?? 'Tap to choose file',
                    style: GoogleFonts.dmSans(
                      fontSize: 14,
                      color: fileName != null ? const Color(0xFF231F20) : const Color(0xFFAAA0A2),
                      fontWeight: fileName != null ? FontWeight.w600 : FontWeight.normal,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF7F2F3),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: const Color(0xFFE5D7D7)),
                  ),
                  child: Text(
                    'Browse',
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF4A3E4D),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        if (isError) ...[
          const SizedBox(height: 4),
          Text(
            errorMessage,
            style: const TextStyle(fontSize: 12, color: Color(0xFFD32F2F)),
          ),
        ] else if (helperText != null) ...[
          const SizedBox(height: 5),
          Text(
            helperText!,
            style: GoogleFonts.dmSans(
              fontSize: 11.5,
              color: const Color(0xFF7A6E72),
            ),
          ),
        ],
      ],
    );
  }
}

typedef _UploadField = RegistrationUploadField;

// ============================================================
// DATE FIELD
// ============================================================

class RegistrationDateField extends StatelessWidget {
  final String birthday;
  final VoidCallback onClick;
  final bool isError;
  final String errorMessage;
  final String label;
  final String hint;
  final String helperText;

  const RegistrationDateField({
    super.key,
    required this.birthday,
    required this.onClick,
    this.isError = false,
    this.errorMessage = '',
    this.label = 'Birthday',
    this.hint = 'Select your birthday',
    this.helperText = 'You must be at least 18 years old to register.',
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        RichText(
          text: TextSpan(
            text: label,
            style: GoogleFonts.dmSans(
              fontSize: 13.5,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF231F20),
            ),
            children: const [
              TextSpan(
                text: ' *',
                style: TextStyle(color: Color(0xFFD32F2F), fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        GestureDetector(
          onTap: onClick,
          child: AbsorbPointer(
            child: TextField(
              controller: TextEditingController(text: birthday),
              readOnly: true,
              style: GoogleFonts.dmSans(fontSize: 14.5, color: const Color(0xFF231F20)),
              decoration: InputDecoration(
                hintText: hint,
                hintStyle: GoogleFonts.dmSans(fontSize: 14, color: const Color(0xFFB5A8AA)),
                prefixIcon: const Icon(Icons.calendar_today_outlined, color: Color(0xFF9E8F91), size: 20),
                suffixIcon: const Icon(Icons.keyboard_arrow_down_rounded, color: Color(0xFF9E8F91)),
                errorText: isError ? errorMessage : null,
            errorMaxLines: 3,
                errorStyle: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFFEF4444)),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFFE5D7D7), width: 1.2),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFFE5D7D7), width: 1.2),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFF865561), width: 1.5),
                ),
                errorBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFFD32F2F), width: 1.2),
                ),
              ),
            ),
          ),
        ),
        const SizedBox(height: 5),
        Text(
          helperText,
          style: GoogleFonts.dmSans(
            fontSize: 11.5,
            color: const Color(0xFF7A6E72),
          ),
        ),
      ],
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
    // Extract display label
    final displayLabel = label.replaceAll('*', '').replaceAll('Loading provinces...', 'Province').replaceAll('Loading...', 'Municipality / City').trim();
    final isRequired = label.contains('*') || label.contains('Loading');
    final isLoading = label.contains('Loading');

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Label above field
        RichText(
          text: TextSpan(
            text: displayLabel,
            style: GoogleFonts.dmSans(
              fontSize: 13.5,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF231F20),
            ),
            children: [
              if (isRequired)
                const TextSpan(
                  text: ' *',
                  style: TextStyle(color: Color(0xFFD32F2F), fontWeight: FontWeight.bold),
                ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        GestureDetector(
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
              style: GoogleFonts.dmSans(fontSize: 14.5, color: const Color(0xFF231F20)),
              decoration: InputDecoration(
                hintText: isLoading ? 'Loading...' : 'Select ${displayLabel.toLowerCase()}',
                hintStyle: GoogleFonts.dmSans(fontSize: 14, color: const Color(0xFFB5A8AA)),
                prefixIcon: Icon(icon ?? Icons.list_alt_outlined, color: const Color(0xFF9E8F91), size: 20),
                suffixIcon: isLoading
                    ? const Padding(
                        padding: EdgeInsets.all(14),
                        child: SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF865561)),
                        ),
                      )
                    : const Icon(Icons.keyboard_arrow_down_rounded, color: Color(0xFF9E8F91)),
                errorText: isError ? errorMessage : null,
            errorMaxLines: 3,
                errorStyle: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFFEF4444)),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFFE5D7D7), width: 1.2),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFFE5D7D7), width: 1.2),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFF865561), width: 1.5),
                ),
                errorBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFFD32F2F), width: 1.2),
                ),
              ),
            ),
          ),
        ),
      ],
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
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        RichText(
          text: TextSpan(
            text: 'Sex',
            style: GoogleFonts.dmSans(
              fontSize: 13.5,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF231F20),
            ),
            children: const [
              TextSpan(
                text: ' *',
                style: TextStyle(color: Color(0xFFD32F2F), fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        GestureDetector(
          onTap: () async {
            final selected = await showModalBottomSheet<String>(
              context: context,
              backgroundColor: Colors.transparent,
              builder: (context) {
                return Container(
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.only(
                      topLeft: Radius.circular(20),
                      topRight: Radius.circular(20),
                    ),
                  ),
                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 40,
                        height: 4,
                        margin: const EdgeInsets.only(bottom: 16),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE5D7D7),
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                      Align(
                        alignment: Alignment.centerLeft,
                        child: Text(
                          'Select Sex',
                          style: GoogleFonts.playfairDisplay(fontSize: 18, fontWeight: FontWeight.w700, color: const Color(0xFF231F20)),
                        ),
                      ),
                      const SizedBox(height: 14),
                      ..._options.map((option) {
                        final isSelected = option['label'] == selectedValue;
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 10),
                          child: InkWell(
                            borderRadius: BorderRadius.circular(10),
                            onTap: () => Navigator.of(context).pop(option['label'] as String),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                              decoration: BoxDecoration(
                                color: isSelected ? const Color(0xFFFFF9F9) : Colors.white,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(
                                  color: isSelected ? const Color(0xFFD3A8A8) : const Color(0xFFE5D7D7),
                                  width: isSelected ? 1.5 : 1,
                                ),
                              ),
                              child: Row(
                                children: [
                                  Icon(
                                    option['icon'] as IconData,
                                    color: isSelected ? const Color(0xFF865561) : const Color(0xFF7A6E72),
                                    size: 22,
                                  ),
                                  const SizedBox(width: 14),
                                  Expanded(
                                    child: Text(
                                      option['label'] as String,
                                      style: GoogleFonts.dmSans(
                                        fontSize: 14.5,
                                        fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                                        color: const Color(0xFF231F20),
                                      ),
                                    ),
                                  ),
                                  if (isSelected)
                                    const Icon(Icons.check, color: Color(0xFF865561), size: 20),
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
              style: GoogleFonts.dmSans(fontSize: 14.5, color: const Color(0xFF231F20)),
              decoration: InputDecoration(
                hintText: 'Select your sex',
                hintStyle: GoogleFonts.dmSans(fontSize: 14, color: const Color(0xFFB5A8AA)),
                prefixIcon: Icon(_selectedIcon, color: const Color(0xFF9E8F91), size: 20),
                suffixIcon: const Icon(Icons.keyboard_arrow_down_rounded, color: Color(0xFF9E8F91)),
                errorText: isError ? errorMessage : null,
            errorMaxLines: 3,
                errorStyle: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFFEF4444)),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFFE5D7D7), width: 1.2),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFFE5D7D7), width: 1.2),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFF865561), width: 1.5),
                ),
                errorBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: Color(0xFFD32F2F), width: 1.2),
                ),
              ),
            ),
          ),
        ),
      ],
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

// ============================================================
// CARTZY REGISTER HEADER (Screenshot Reference)
// ============================================================

class CartzyRegisterHeader extends StatelessWidget {
  final String title;
  final String subtitle;
  final VoidCallback? onBack;
  final ValueChanged<Map<String, dynamic>>? onGoogleSuccess;
  final bool showGoogleSignUp;

  const CartzyRegisterHeader({
    super.key,
    this.title = 'Create your account',
    this.subtitle = 'Join cartzy and discover your everyday favorites',
    this.onBack,
    this.onGoogleSuccess,
    this.showGoogleSignUp = true,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        if (onBack != null)
          Align(
            alignment: Alignment.centerLeft,
            child: IconButton(
              icon: const Icon(Icons.arrow_back, color: Color(0xFF231F20)),
              onPressed: onBack,
            ),
          ),
        Center(
          child: const CartzyLogo(height: 34),
        ),
        const SizedBox(height: 14),
        Text(
          title,
          textAlign: TextAlign.center,
          style: GoogleFonts.playfairDisplay(
            fontSize: 26,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
            letterSpacing: -0.3,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          subtitle,
          textAlign: TextAlign.center,
          style: GoogleFonts.dmSans(
            fontSize: 13,
            color: const Color(0xFF7A6E72),
          ),
        ),
        if (showGoogleSignUp) ...[
        const SizedBox(height: 18),
        GestureDetector(
          onTap: () async {
            try {
              final response = await GoogleSignInService.signIn();
              if (response != null && response['user'] != null && onGoogleSuccess != null) {
                final user = Map<String, dynamic>.from(response['user'] as Map);
                onGoogleSuccess!(user);
              }
            } catch (e) {
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text('Google Sign-In: $e'),
                    backgroundColor: Colors.redAccent,
                  ),
                );
              }
            }
          },
          child: Container(
            width: double.infinity,
            height: 46,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFE5D7D7), width: 1.2),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Image.network(
                  'https://www.gstatic.com/images/branding/product/2x/googleg_48dp.png',
                  width: 18,
                  height: 18,
                  errorBuilder: (_, __, ___) => const Icon(Icons.g_mobiledata, size: 24, color: Colors.red),
                ),
                const SizedBox(width: 10),
                Text(
                  'Sign up with Google',
                  style: GoogleFonts.dmSans(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF231F20),
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 18),
        Row(
          children: [
            const Expanded(child: Divider(color: Color(0xFFE5D7D7), height: 1)),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 14),
              child: Text(
                'or register with email',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  color: const Color(0xFF9E8F91),
                ),
              ),
            ),
            const Expanded(child: Divider(color: Color(0xFFE5D7D7), height: 1)),
          ],
        ),
        ],
      ],
    );
  }
}

// ============================================================
// ROLE SELECTION SECTION (Screenshot Reference)
// ============================================================

class RoleSelectionSection extends StatelessWidget {
  final bool isBuyerSelected;
  final VoidCallback onSelectBuyer;
  final VoidCallback onSelectRider;

  const RoleSelectionSection({
    super.key,
    required this.isBuyerSelected,
    required this.onSelectBuyer,
    required this.onSelectRider,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        RichText(
          text: TextSpan(
            text: 'I want to register as',
            style: GoogleFonts.dmSans(
              fontSize: 13.5,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF231F20),
            ),
            children: const [
              TextSpan(
                text: ' *',
                style: TextStyle(color: Color(0xFFD32F2F), fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ),
        const SizedBox(height: 10),

        // Buyer Card
        GestureDetector(
          onTap: onSelectBuyer,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: isBuyerSelected ? const Color(0xFFFFF9F9) : Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: isBuyerSelected ? const Color(0xFFD3A8A8) : const Color(0xFFE5D7D7),
                width: isBuyerSelected ? 1.5 : 1,
              ),
            ),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: isBuyerSelected ? const Color(0xFF8B6370) : const Color(0xFFF7F2F3),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    Icons.shopping_bag_outlined,
                    color: isBuyerSelected ? Colors.white : const Color(0xFF7A6E72),
                    size: 22,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Buyer',
                        style: GoogleFonts.dmSans(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF231F20),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Shop & discover items',
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          color: const Color(0xFF7A6E72),
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  width: 22,
                  height: 22,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: isBuyerSelected ? const Color(0xFF8B6370) : Colors.transparent,
                    border: Border.all(
                      color: isBuyerSelected ? const Color(0xFF8B6370) : const Color(0xFFD0C3C5),
                      width: 1.5,
                    ),
                  ),
                  child: isBuyerSelected
                      ? const Icon(Icons.check, size: 14, color: Colors.white)
                      : null,
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 10),

        // Rider Card
        GestureDetector(
          onTap: onSelectRider,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: !isBuyerSelected ? const Color(0xFFFFF9F9) : Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: !isBuyerSelected ? const Color(0xFFD3A8A8) : const Color(0xFFE5D7D7),
                width: !isBuyerSelected ? 1.5 : 1,
              ),
            ),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: !isBuyerSelected ? const Color(0xFF8B6370) : const Color(0xFFF7F2F3),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    Icons.two_wheeler_outlined,
                    color: !isBuyerSelected ? Colors.white : const Color(0xFF7A6E72),
                    size: 22,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Delivery Partner / Rider',
                        style: GoogleFonts.dmSans(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF231F20),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Deliver orders & earn',
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          color: const Color(0xFF7A6E72),
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  width: 22,
                  height: 22,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: !isBuyerSelected ? const Color(0xFF8B6370) : Colors.transparent,
                    border: Border.all(
                      color: !isBuyerSelected ? const Color(0xFF8B6370) : const Color(0xFFD0C3C5),
                      width: 1.5,
                    ),
                  ),
                  child: !isBuyerSelected
                      ? const Icon(Icons.check, size: 14, color: Colors.white)
                      : null,
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

// ============================================================
// REGISTRATION FOOTER (Screenshot Reference)
// ============================================================

class RegistrationFooter extends StatelessWidget {
  final bool isBuyer;
  final VoidCallback onBackToLogin;
  final VoidCallback? onSwitchRole;

  const RegistrationFooter({
    super.key,
    required this.isBuyer,
    required this.onBackToLogin,
    this.onSwitchRole,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        const SizedBox(height: 18),
        Wrap(
          alignment: WrapAlignment.center,
          children: [
            Text(
              'Already have an account? ',
              style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF7A6E72)),
            ),
            GestureDetector(
              onTap: onBackToLogin,
              child: Text(
                'Log in',
                style: GoogleFonts.dmSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF865561),
                ),
              ),
            ),
          ],
        ),
        if (!isBuyer && onSwitchRole != null) ...[
          const SizedBox(height: 16),
          const Divider(color: Color(0xFFE5D7D7), height: 1),
          const SizedBox(height: 16),
          Wrap(
            alignment: WrapAlignment.center,
            children: [
              Text(
                'Looking to shop on cartzy instead? ',
                style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
              ),
              GestureDetector(
                onTap: onSwitchRole,
                child: Text(
                  'Register as buyer',
                  style: GoogleFonts.dmSans(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF865561),
                    decoration: TextDecoration.underline,
                  ),
                ),
              ),
            ],
          ),
        ],
        const SizedBox(height: 24),
      ],
    );
  }
}

// ============================================================
// TEXT FIELD (Screenshot Reference)
// ============================================================

class RegistrationTextField extends StatefulWidget {
  final TextEditingController controller;
  final String label;
  final IconData? icon;
  final bool isError;
  final String errorMessage;
  final bool obscureText;
  final Widget? trailing;
  final ValueChanged<String>? onChanged;
  final String? helperText;
  final bool isPhone;
  final String? customHint;
  final int? maxLength;
  final List<TextInputFormatter>? inputFormatters;
  final TextInputType? keyboardType;
  final String? Function(String)? validator;
  final bool validateOnChange;

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
    this.helperText,
    this.isPhone = false,
    this.customHint,
    this.maxLength,
    this.inputFormatters,
    this.keyboardType,
    this.validator,
    this.validateOnChange = false,
  });

  @override
  State<RegistrationTextField> createState() => _RegistrationTextFieldState();
}

class _RegistrationTextFieldState extends State<RegistrationTextField> {
  final _focusNode = FocusNode();
  Timer? _warningTimer;
  String? _warning;
  String? _validationError;

  @override
  void initState() {
    super.initState();
    _focusNode.addListener(() {
      if (!_focusNode.hasFocus) {
        _warningTimer?.cancel();
        setState(() {
          _warning = null;
          _validationError = widget.validator?.call(widget.controller.text);
        });
      }
    });
  }

  TextEditingValue _checkInput(TextEditingValue oldValue, TextEditingValue newValue) {
    if (!newValue.composing.isCollapsed) return newValue;
    final isName = widget.label.toLowerCase().contains('name');
    String? message;
    if (isName && RegExp(r'[^\p{L}\p{M} ]', unicode: true).hasMatch(newValue.text)) {
      message = 'Special characters and numbers are not allowed.';
    } else if (widget.maxLength != null && newValue.text.runes.length > widget.maxLength!) {
      final label = widget.label.replaceAll('*', '').trim();
      message = '$label must not exceed ${widget.maxLength} characters.';
    } else if (widget.isPhone && RegExp(r'[^0-9]').hasMatch(newValue.text)) {
      message = 'Please enter digits only.';
    } else if (widget.isPhone && newValue.text.length > 10) {
      message = 'Enter exactly 10 digits after +63.';
    }
    _warningTimer?.cancel();
    setState(() => _warning = message);
    if (message != null) {
      _warningTimer = Timer(const Duration(milliseconds: 2500), () {
        if (mounted) setState(() => _warning = null);
      });
    }
    return newValue;
  }

  @override
  void dispose() {
    _warningTimer?.cancel();
    _focusNode.dispose();
    super.dispose();
  }
  TextEditingController get controller => widget.controller;
  String get label => widget.label;
  IconData? get icon => widget.icon;
  bool get isError => widget.isError;
  String get errorMessage => widget.errorMessage;
  bool get obscureText => widget.obscureText;
  Widget? get trailing => widget.trailing;
  ValueChanged<String>? get onChanged => widget.onChanged;
  String? get helperText => widget.helperText;
  bool get isPhone => widget.isPhone;
  String? get customHint => widget.customHint;
  int? get maxLength => widget.maxLength;
  List<TextInputFormatter>? get inputFormatters => widget.inputFormatters;
  TextInputType? get keyboardType => widget.keyboardType;
  @override
  Widget build(BuildContext context) {
    final displayLabel = label.replaceAll('*', '').trim();
    final isRequired = label.contains('*');
    final checkPhone = isPhone || label.toLowerCase().contains('mobile') || label.toLowerCase().contains('contact');

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        RichText(
          text: TextSpan(
            text: displayLabel,
            style: GoogleFonts.dmSans(
              fontSize: 13.5,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF231F20),
            ),
            children: [
              if (isRequired)
                const TextSpan(
                  text: ' *',
                  style: TextStyle(color: Color(0xFFD32F2F), fontWeight: FontWeight.bold),
                ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        TextField(
          controller: controller,
          focusNode: _focusNode,
          maxLength: checkPhone ? 10 : maxLength,
          keyboardType: checkPhone ? TextInputType.phone : keyboardType,
          inputFormatters: [
            TextInputFormatter.withFunction(_checkInput),
            if (checkPhone) FilteringTextInputFormatter.digitsOnly,
            ...?widget.inputFormatters,
          ],
          obscureText: obscureText,
          onChanged: (value) {
            setState(() {
              _validationError = widget.validateOnChange
                  ? widget.validator?.call(value) : null;
            });
            widget.onChanged?.call(value);
          },
          style: GoogleFonts.dmSans(fontSize: 14.5, color: const Color(0xFF231F20)),
          decoration: InputDecoration(
            counterText: '',
            hintText: customHint ?? (checkPhone ? '9171234567' : displayLabel),
            hintStyle: GoogleFonts.dmSans(fontSize: 14, color: const Color(0xFFB5A8AA)),
            prefixIcon: checkPhone
                ? Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const SizedBox(width: 14),
                      const Icon(Icons.phone_outlined, color: Color(0xFF9E8F91), size: 20),
                      const SizedBox(width: 8),
                      Text(
                        '+63',
                        style: GoogleFonts.dmSans(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF231F20),
                        ),
                      ),
                      const SizedBox(width: 10),
                    ],
                  )
                : (icon != null ? Icon(icon, color: const Color(0xFF9E8F91), size: 20) : null),
            suffixIcon: trailing,
            errorText: _warning ?? _validationError ?? (isError ? errorMessage : null),
            errorMaxLines: 3,
            errorStyle: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFFEF4444)),
            filled: true,
            fillColor: Colors.white,
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: const BorderSide(color: Color(0xFFE5D7D7), width: 1.2),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: const BorderSide(color: Color(0xFFE5D7D7), width: 1.2),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: const BorderSide(color: Color(0xFF865561), width: 1.5),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: const BorderSide(color: Color(0xFFD32F2F), width: 1.2),
            ),
            focusedErrorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: const BorderSide(color: Color(0xFFD32F2F), width: 1.5),
            ),
          ),
        ),
        if (checkPhone) ...[
          const SizedBox(height: 5),
          Text(
            '+63 Philippines · Enter 10 digits starting with 9.',
            style: GoogleFonts.dmSans(
              fontSize: 11.5,
              color: const Color(0xFF7A6E72),
            ),
          ),
        ] else if (helperText != null && helperText!.isNotEmpty) ...[
          const SizedBox(height: 5),
          Text(
            helperText!,
            style: GoogleFonts.dmSans(
              fontSize: 11.5,
              color: const Color(0xFF7A6E72),
            ),
          ),
        ],
      ],
    );
  }
}

// ============================================================
// PRIMARY BUTTON (Screenshot Gradient Style)
// ============================================================

class PrimaryButton extends StatelessWidget {
  final String text;
  final Color? color;
  final VoidCallback onClick;

  const PrimaryButton({
    super.key,
    required this.text,
    this.color,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      height: 48,
      child: DecoratedBox(
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFF4A3E4D), Color(0xFF865561)],
            begin: Alignment.centerLeft,
            end: Alignment.centerRight,
          ),
          borderRadius: BorderRadius.circular(10),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF865561).withValues(alpha: 0.25),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: ElevatedButton(
          onPressed: onClick,
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.transparent,
            shadowColor: Colors.transparent,
            foregroundColor: Colors.white,
            elevation: 0,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
          child: Text(
            text,
            style: GoogleFonts.dmSans(
              fontSize: 14.5,
              fontWeight: FontWeight.w600,
              color: Colors.white,
            ),
          ),
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
  final Color? color;
  final VoidCallback onClick;

  const SecondaryButton({
    super.key,
    required this.text,
    this.color,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      height: 48,
      child: OutlinedButton(
        onPressed: onClick,
        style: OutlinedButton.styleFrom(
          side: const BorderSide(color: Color(0xFFE5D7D7), width: 1.2),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
        child: Text(
          text,
          style: GoogleFonts.dmSans(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: const Color(0xFF231F20),
          ),
        ),
      ),
    );
  }
}

// ============================================================
// STEP INDICATOR (Screenshot Reference Text Stepper)
// ============================================================

class StepIndicator extends StatelessWidget {
  final int currentStep;
  final List<String> labels;
  final Color? accent;
  final Color? gray;
  final List<IconData>? icons;

  const StepIndicator({
    super.key,
    required this.currentStep,
    this.labels = const ['Details', 'Sign-in', 'Verify'],
    this.accent,
    this.gray,
    this.icons,
  });

  @override
  Widget build(BuildContext context) {
    final effectiveLabels = labels.isNotEmpty ? labels : const ['Details', 'Sign-in', 'Verify'];

    return Container(
      color: Colors.transparent,
      padding: const EdgeInsets.symmetric(vertical: 14),
      child: Row(
        children: [
          for (int i = 0; i < effectiveLabels.length; i++) ...[
            Flexible(
              flex: 2,
              child: Text(
              effectiveLabels[i],
              style: GoogleFonts.dmSans(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: currentStep >= i + 1
                    ? (accent ?? const Color(0xFF705A7B))
                    : (gray ?? const Color(0xFFAA9CB4)),
              ),
              ),
            ),
            if (i < effectiveLabels.length - 1) ...[
              Expanded(
                child: Container(
                  height: 2,
                  margin: const EdgeInsets.symmetric(horizontal: 6),
                  color: currentStep > i + 1
                      ? (accent ?? const Color(0xFF705A7B))
                      : const Color(0xFFE2DCE8),
                ),
              ),
            ],
          ],
        ],
      ),
    );
  }
}

// ============================================================
// STEP CIRCLE (Preserved for compatibility)
// ============================================================

class StepCircle extends StatelessWidget {
  final IconData icon;
  final bool active;
  final Color accent;
  final Color gray;
  final int stepNumber;

  const StepCircle({
    super.key,
    required this.icon,
    required this.active,
    required this.accent,
    required this.gray,
    this.stepNumber = 1,
  });

  @override
  Widget build(BuildContext context) {
    return const SizedBox.shrink();
  }
}

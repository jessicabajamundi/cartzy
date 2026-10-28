import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:cartzy/auth/buyer_registration.dart'
    show
        BirthdayWheelPicker,
        CartzyRegisterHeader,
        PrimaryButton,
        RegistrationDateField,
        RegistrationDropdown,
        RegistrationFooter,
        RegistrationTextField,
        RegistrationUploadField,
        RoleSelectionSection,
        SecondaryButton,
        StepIndicator;
import 'package:cartzy/auth/buyer_registration_validation.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:image_picker/image_picker.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/widgets/cartzy_flash_notif.dart';
import 'package:cartzy/services/government_id_parser.dart';
import 'package:cartzy/widgets/id_scanner_screen.dart';

// ============================================================
// RIDER REGISTRATION
// Built from the same widgets as buyer registration so both
// sign-up flows look and behave alike.
// ============================================================

class RiderRegistration extends StatefulWidget {
  final VoidCallback onRegistrationSubmitted;
  final VoidCallback onBackToLogin;
  final VoidCallback? onSwitchToBuyerRegistration;

  const RiderRegistration({
    super.key,
    required this.onRegistrationSubmitted,
    required this.onBackToLogin,
    this.onSwitchToBuyerRegistration,
  });

  @override
  State<RiderRegistration> createState() => _RiderRegistrationState();
}

class _RiderRegistrationState extends State<RiderRegistration> {
  // ── Step tracking: 1 = Details, 2 = Sign-in, 3 = Verify ──
  int _currentStep = 1;

  // ── Step 1: Personal Details ──
  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _contactNoController = TextEditingController();
  String _birthday = '';

  // ── Step 1: Where you'll deliver ──
  String _selectedLogisticsPartner = '';
  static const List<String> logisticsPartnerOptions = [
    'Metro South Sorting & Fulfillment Hub',
    'Laguna Express Logistics Hub',
    'North Manila Central Distribution',
    'Cavite Regional Fulfillment Depot',
    'Batangas Central Hub',
  ];

  // ── Step 1: Your vehicle ──
  String _selectedVehicle = '';
  static const List<String> vehicleOptions = [
    'Motorcycle',
    'Bicycle',
    'E-bike',
    'Tricycle',
    'Car',
    'Van',
    'Truck',
  ];
  final _plateNumberController = TextEditingController();

  // ── Step 1: Driver's license ──
  final _licenseNumberController = TextEditingController();
  String _licenseExpiry = '';

  // ── Step 1: Documents ──
  String? _orCrFileName;

  // Errors for Step 1
  bool _firstNameError = false;
  String? _firstNameErrorMessage;
  bool _lastNameError = false;
  String? _lastNameErrorMessage;
  bool _contactNoError = false;
  String? _contactNoErrorMessage;
  bool _birthdayError = false;
  String? _birthdayErrorMessage;
  bool _logisticsPartnerError = false;
  bool _vehicleError = false;
  bool _plateNumberError = false;
  bool _licenseNumberError = false;
  bool _licenseExpiryError = false;
  bool _orCrError = false;

  // ── Step 1: Valid government ID (required for every rider) ──
  String? _govIdImagePath;
  bool _isReadingGovId = false;
  bool _govIdAutofilled = false;
  bool _govIdImageError = false;

  // ── Step 2: Sign-in details ──
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _passwordVisible = false;
  bool _confirmPasswordVisible = false;

  bool _emailError = false;
  String? _emailErrorMessage;
  bool _passwordError = false;
  String? _passwordErrorMessage;
  bool _confirmPasswordError = false;
  String? _confirmPasswordErrorMessage;

  // ── Step 3: Verification ──
  final _otpController = TextEditingController();
  bool _otpError = false;
  bool _isSubmitting = false;

  static final _upperCase = TextInputFormatter.withFunction(
      (_, value) => value.copyWith(text: value.text.toUpperCase()));

  /// LTO plates: letters and digits, e.g. NBC1234, ABC123 or 123ABC.
  static String? _plateNumberRule(String value) {
    final plate = value.trim();
    if (plate.isEmpty) return 'Plate number is required';
    if (!RegExp(r'^(?=.*[A-Z])(?=.*\d)[A-Z0-9]{5,8}$').hasMatch(plate)) {
      return 'Enter a valid plate number (e.g. NBC1234 or 123ABC).';
    }
    return null;
  }

  /// LTO license number format: A00-00-000000.
  static String? _licenseNumberRule(String value) {
    final license = value.trim();
    if (license.isEmpty) return 'License number is required';
    if (!RegExp(r'^[A-Z]\d{2}-\d{2}-\d{6}$').hasMatch(license)) {
      return 'Use the format A00-00-000000 (e.g. H83-78-937937).';
    }
    return null;
  }

  /// Bicycle and e-bike riders don't need a plate, license or OR/CR.
  bool get _isNonMotorized =>
      _selectedVehicle == 'Bicycle' || _selectedVehicle == 'E-bike';

  @override
  void dispose() {
    _firstNameController.dispose();
    _lastNameController.dispose();
    _contactNoController.dispose();
    _plateNumberController.dispose();
    _licenseNumberController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _otpController.dispose();
    super.dispose();
  }

  // ── Date Pickers ──
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
            _birthdayErrorMessage = BuyerRegistrationValidation.birthday(date);
            _birthdayError = _birthdayErrorMessage != null;
          });
        },
      ),
    );
  }

  Future<void> _pickLicenseExpiry() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: now.add(const Duration(days: 365)),
      firstDate: now,
      lastDate: DateTime(now.year + 20),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: Color(0xFF865561),
              onPrimary: Colors.white,
              onSurface: Color(0xFF231F20),
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() {
        _licenseExpiry = _formatDate(picked);
        _licenseExpiryError = false;
      });
    }
  }

  /// Same "January 5, 2030" format the birthday picker produces.
  static String _formatDate(DateTime date) {
    const months = [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return '${months[date.month - 1]} ${date.day}, ${date.year}';
  }

  // ── File Pickers ──
  Future<void> _pickOrCrFile() async {
    try {
      final picker = ImagePicker();
      final XFile? file =
          await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (file != null) {
        setState(() {
          _orCrFileName = file.name;
          _orCrError = false;
        });
      }
    } catch (_) {
      setState(() {
        _orCrFileName = 'vehicle_or_cr.jpg';
        _orCrError = false;
      });
    }
  }

  // ── Government ID scan ──
  Future<void> _scanGovernmentId() async {
    final result = await Navigator.of(context).push<IdScanResult>(
      MaterialPageRoute(
        builder: (_) => const IdScannerScreen(
          preferredType: GovernmentIdParser.driversLicense,
        ),
      ),
    );
    if (result != null) _applyIdScan(result);
  }

  Future<void> _uploadGovernmentId() async {
    final XFile? file;
    try {
      file = await ImagePicker()
          .pickImage(source: ImageSource.gallery, imageQuality: 90);
    } catch (_) {
      if (mounted) showCartzyFlash(context, "Couldn't open your photos.");
      return;
    }
    if (file == null || !mounted) return;
    setState(() => _isReadingGovId = true);
    var fields = const GovernmentIdFields();
    try {
      fields = await IdScannerScreen.readIdImage(file.path,
          preferredType: GovernmentIdParser.driversLicense);
    } catch (_) {
      // Unreadable photo: keep it, the rider types the details.
    }
    if (!mounted) return;
    setState(() => _isReadingGovId = false);
    _applyIdScan(IdScanResult(imagePath: file.path, fields: fields));
  }

  /// Fills the form from the ID. Name and birthday are only filled when
  /// empty so nothing the rider typed is overwritten.
  void _applyIdScan(IdScanResult result) {
    final f = result.fields;
    setState(() {
      _govIdImagePath = result.imagePath;
      _govIdImageError = false;
      if (f.idNumber != null) {
        _licenseNumberController.text = f.idNumber!;
        _licenseNumberError = false;
      }
      if (f.firstName != null && _firstNameController.text.trim().isEmpty) {
        _firstNameController.text = f.firstName!;
        _firstNameError = false;
      }
      if (f.lastName != null && _lastNameController.text.trim().isEmpty) {
        _lastNameController.text = f.lastName!;
        _lastNameError = false;
      }
      if (f.birthday != null && _birthday.isEmpty) {
        _birthday = _formatDate(f.birthday!);
        _birthdayErrorMessage = BuyerRegistrationValidation.birthday(_birthday);
        _birthdayError = _birthdayErrorMessage != null;
      }
      _govIdAutofilled = !f.isEmpty;
    });
    showCartzyFlash(
      context,
      f.isEmpty
          ? "ID photo added. We couldn't read the details, so please type them in."
          : 'Details filled in from your ID. Please check them.',
    );
  }

  // ── Step 1 Validation ──
  void _validateAndContinueToStep2() {
    setState(() {
      _firstNameErrorMessage = BuyerRegistrationValidation.name(
          _firstNameController.text, 'first name');
      _firstNameError = _firstNameErrorMessage != null;
      _lastNameErrorMessage = BuyerRegistrationValidation.name(
          _lastNameController.text, 'last name');
      _lastNameError = _lastNameErrorMessage != null;
      _contactNoErrorMessage =
          BuyerRegistrationValidation.phone(_contactNoController.text.trim());
      _contactNoError = _contactNoErrorMessage != null;
      _birthdayErrorMessage = BuyerRegistrationValidation.birthday(_birthday);
      _birthdayError = _birthdayErrorMessage != null;
      _govIdImageError = !_isNonMotorized && _govIdImagePath == null;
      _logisticsPartnerError = _selectedLogisticsPartner.isEmpty;
      _vehicleError = _selectedVehicle.isEmpty;

      if (!_isNonMotorized) {
        _plateNumberError = _plateNumberRule(_plateNumberController.text) != null;
        _licenseNumberError =
            _licenseNumberRule(_licenseNumberController.text) != null;
        _licenseExpiryError = _licenseExpiry.isEmpty;
        _orCrError = _orCrFileName == null;
      } else {
        _plateNumberError = false;
        _licenseNumberError = false;
        _licenseExpiryError = false;
        _orCrError = false;
      }
    });

    if (_govIdImageError ||
        _firstNameError ||
        _lastNameError ||
        _contactNoError ||
        _birthdayError ||
        _logisticsPartnerError ||
        _vehicleError ||
        _plateNumberError ||
        _licenseNumberError ||
        _licenseExpiryError ||
        _orCrError) {
      showCartzyFlash(context, 'Please complete all required fields.');
      return;
    }

    setState(() {
      _currentStep = 2;
    });
  }

  // ── Step 2 Validation ──
  void _validateAndSendVerification() {
    final email = _emailController.text.trim();
    final password = _passwordController.text;
    final confirm = _confirmPasswordController.text;

    setState(() {
      _emailErrorMessage = BuyerRegistrationValidation.email(email);
      _emailError = _emailErrorMessage != null;

      _passwordErrorMessage = BuyerRegistrationValidation.password(password);
      _passwordError = _passwordErrorMessage != null;

      if (confirm.isEmpty) {
        _confirmPasswordError = true;
        _confirmPasswordErrorMessage = 'Please confirm your password';
      } else if (confirm != password) {
        _confirmPasswordError = true;
        _confirmPasswordErrorMessage =
            'The password confirmation does not match.';
      } else {
        _confirmPasswordError = false;
        _confirmPasswordErrorMessage = null;
      }
    });

    if (_emailError || _passwordError || _confirmPasswordError) {
      showCartzyFlash(context, 'Please correct the errors before proceeding.');
      return;
    }

    showCartzyFlash(context, 'Verification code sent to $email');
    setState(() {
      _currentStep = 3;
    });
  }

  // ── Step 3 / Submit Application ──
  Future<void> _submitApplication() async {
    if (_isSubmitting) return;

    final otp = _otpController.text.trim();
    if (otp.length < 6) {
      setState(() => _otpError = true);
      showCartzyFlash(context, 'Please enter the 6-digit verification code.');
      return;
    }

    // Guard the submit too, in case step 1 was bypassed.
    final birthdayError = BuyerRegistrationValidation.birthday(_birthday);
    if (birthdayError != null) {
      setState(() {
        _birthdayError = true;
        _birthdayErrorMessage = birthdayError;
        _currentStep = 1;
      });
      showCartzyFlash(context, birthdayError);
      return;
    }

    setState(() {
      _otpError = false;
      _isSubmitting = true;
    });

    try {
      await Future.delayed(const Duration(milliseconds: 600));

      if (!mounted) return;
      setState(() => _isSubmitting = false);

      _showPendingApprovalDialog();
    } catch (e) {
      if (!mounted) return;
      setState(() => _isSubmitting = false);
      showCartzyFlash(context, 'Registration error: $e');
    }
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
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFF7F2F3),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.hourglass_top,
                    color: Color(0xFF865561), size: 22),
              ),
              const SizedBox(width: 12),
              Flexible(
                child: Text(
                  'Registration Submitted',
                  style: GoogleFonts.dmSans(
                    color: const Color(0xFF231F20),
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
              ),
            ],
          ),
          content: Text(
            "After submitting your registration, please wait for the "
            "Logistics/Sorting Center's approval, which will be sent to "
            "your email.",
            style: GoogleFonts.dmSans(
                fontSize: 14, color: const Color(0xFF7A6E72), height: 1.4),
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
                widget.onRegistrationSubmitted();
              },
              child: Text(
                'OK',
                style: GoogleFonts.dmSans(
                  color: const Color(0xFF865561),
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  // ============================================================
  // UI BUILD
  // ============================================================

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              CartzyRegisterHeader(
                title: 'Become a rider',
                subtitle:
                    "Apply to deliver for one of cartzy's logistics partners",
                onBack: widget.onBackToLogin,
                showGoogleSignUp: false,
              ),
              const SizedBox(height: 12),
              _buildNoticeBox(),
              const SizedBox(height: 16),
              StepIndicator(
                currentStep: _currentStep,
                labels: const ['Details', 'Sign-in', 'Verify'],
              ),
              const SizedBox(height: 16),
              if (_currentStep == 1) _buildStep1Details(),
              if (_currentStep == 2) _buildStep2SignIn(),
              if (_currentStep == 3) _buildStep3Verify(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title, [String? subtitle]) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: GoogleFonts.dmSans(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        if (subtitle != null) ...[
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: GoogleFonts.dmSans(
              fontSize: 12.5,
              color: const Color(0xFF7A6E72),
            ),
          ),
        ],
        const SizedBox(height: 18),
      ],
    );
  }

  Widget _buildGovernmentIdSection() {
    final image = _govIdImagePath;
    final borderColor =
        _govIdImageError ? const Color(0xFFD32F2F) : const Color(0xFFE5D7D7);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: const Color(0xFFFFF9F9),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: borderColor, width: 1.2),
          ),
          child: _isReadingGovId
              ? Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                            strokeWidth: 2, color: Color(0xFF865561)),
                      ),
                      const SizedBox(width: 10),
                      Flexible(
                        child: Text('Reading your ID…',
                            style: GoogleFonts.dmSans(
                                fontSize: 13, color: const Color(0xFF7A6E72))),
                      ),
                    ],
                  ),
                )
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    if (image != null) ...[
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.file(
                          File(image),
                          height: 80,
                          fit: BoxFit.contain,
                        ),
                      ),
                      const SizedBox(height: 8),
                    ],
                    Wrap(
                      spacing: 8,
                      runSpacing: 4,
                      children: [
                        FilledButton.icon(
                          onPressed: _scanGovernmentId,
                          style: FilledButton.styleFrom(
                            backgroundColor: const Color(0xFF865561),
                            minimumSize: const Size(0, 48),
                          ),
                          icon: const Icon(Icons.camera_alt_outlined, size: 18),
                          label: Text(image == null ? 'Scan ID' : 'Scan again'),
                        ),
                        TextButton.icon(
                          onPressed: _uploadGovernmentId,
                          style: TextButton.styleFrom(
                            foregroundColor: const Color(0xFF865561),
                            minimumSize: const Size(0, 48),
                          ),
                          icon: const Icon(Icons.upload_outlined, size: 18),
                          label: const Text('Upload photo'),
                        ),
                      ],
                    ),
                  ],
                ),
        ),
        const SizedBox(height: 5),
        Text(
          _govIdImageError
              ? "Please add a photo of your driver's license"
              : 'Use a clear photo of the front of your license.',
          style: GoogleFonts.dmSans(
            fontSize: _govIdImageError ? 13 : 11.5,
            color: _govIdImageError
                ? const Color(0xFFEF4444)
                : const Color(0xFF7A6E72),
          ),
        ),
        if (_govIdAutofilled) ...[
          const SizedBox(height: 4),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(Icons.auto_awesome, size: 16, color: Color(0xFF865561)),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Details filled in. Please check them.',
                  style: GoogleFonts.dmSans(
                      fontSize: 12, color: const Color(0xFF865561), height: 1.35),
                ),
              ),
            ],
          ),
        ],
      ],
    );
  }

  Widget _buildFooter() {
    return RegistrationFooter(
      isBuyer: false,
      onBackToLogin: widget.onBackToLogin,
    );
  }

  // ============================================================
  // NOTICE BOX
  // ============================================================

  Widget _buildNoticeBox() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF9F9),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE5D7D7)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.info_outline, size: 18, color: Color(0xFF865561)),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'The logistics partner you choose reviews your application. You can sign in and accept deliveries once they approve you.',
              style: GoogleFonts.dmSans(
                fontSize: 12.5,
                color: const Color(0xFF7A6E72),
                height: 1.35,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // STEP 1 — DETAILS
  // ============================================================

  Widget _buildStep1Details() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        RoleSelectionSection(
          isBuyerSelected: false,
          onSelectBuyer: () => widget.onSwitchToBuyerRegistration?.call(),
          onSelectRider: () {},
        ),
        const SizedBox(height: 22),

        // ── 1. Your personal details ──
        _buildSectionHeader(
            'Your personal details', 'Enter your name and personal details'),
        RegistrationTextField(
          controller: _firstNameController,
          label: 'First name *',
          validator: (value) =>
              BuyerRegistrationValidation.name(value, 'first name'),
          maxLength: 50,
          inputFormatters: [
            FilteringTextInputFormatter.allow(
                BuyerRegistrationValidation.nameCharacters)
          ],
          icon: Icons.person_outline,
          isError: _firstNameError,
          errorMessage: _firstNameErrorMessage ?? 'First name is required',
          onChanged: (_) {
            if (_firstNameError) setState(() => _firstNameError = false);
          },
        ),
        const SizedBox(height: 16),
        RegistrationTextField(
          controller: _lastNameController,
          label: 'Last name *',
          validator: (value) =>
              BuyerRegistrationValidation.name(value, 'last name'),
          maxLength: 50,
          inputFormatters: [
            FilteringTextInputFormatter.allow(
                BuyerRegistrationValidation.nameCharacters)
          ],
          icon: Icons.person_outline,
          isError: _lastNameError,
          errorMessage: _lastNameErrorMessage ?? 'Last name is required',
          onChanged: (_) {
            if (_lastNameError) setState(() => _lastNameError = false);
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
          errorMessage: _contactNoErrorMessage ?? 'Mobile number is required',
          onChanged: (_) {
            if (_contactNoError) setState(() => _contactNoError = false);
          },
        ),
        const SizedBox(height: 16),
        RegistrationDateField(
          birthday: _birthday,
          onClick: _openBirthdayPicker,
          isError: _birthdayError,
          errorMessage: _birthdayErrorMessage ?? 'Please enter your birthday.',
          helperText: 'Riders must be at least 18 years old.',
        ),
        const SizedBox(height: 28),

        // ── 2. Where you'll deliver ──
        _buildSectionHeader("Where you'll deliver"),
        RegistrationDropdown(
          label: 'Logistics partner *',
          icon: Icons.local_shipping_outlined,
          selectedValue: _selectedLogisticsPartner,
          options: logisticsPartnerOptions,
          isError: _logisticsPartnerError,
          errorMessage: 'Please select a logistics partner',
          onSelected: (value) => setState(() {
            _selectedLogisticsPartner = value;
            _logisticsPartnerError = false;
          }),
        ),
        const SizedBox(height: 28),

        // ── 3. Your vehicle ──
        _buildSectionHeader('Your vehicle',
            "Bicycle and e-bike riders don't need a plate, driver's license or OR/CR."),
        RegistrationDropdown(
          label: 'Vehicle type *',
          icon: Icons.two_wheeler_outlined,
          selectedValue: _selectedVehicle,
          options: vehicleOptions,
          showSearch: false,
          isError: _vehicleError,
          errorMessage: 'Please select your vehicle type',
          onSelected: (value) => setState(() {
            _selectedVehicle = value;
            _vehicleError = false;
          }),
        ),
        if (!_isNonMotorized) ...[
          const SizedBox(height: 16),
          RegistrationTextField(
            controller: _plateNumberController,
            label: 'Plate number *',
            icon: Icons.pin_outlined,
            customHint: '123ABC or NBC1234',
            maxLength: 8,
            inputFormatters: [
              FilteringTextInputFormatter.allow(RegExp(r'[A-Za-z0-9]')),
              _upperCase,
            ],
            validator: _plateNumberRule,
            isError: _plateNumberError,
            errorMessage: _plateNumberRule(_plateNumberController.text) ??
                'Plate number is required',
            onChanged: (_) {
              if (_plateNumberError) setState(() => _plateNumberError = false);
            },
          ),
          const SizedBox(height: 28),

          // ── 4. Driver's license ──
          _buildSectionHeader("Driver's license"),
          _buildGovernmentIdSection(),
          const SizedBox(height: 10),
          RegistrationTextField(
            controller: _licenseNumberController,
            label: 'License number *',
            icon: Icons.badge_outlined,
            customHint: 'A00-00-000000',
            maxLength: 13,
            inputFormatters: [
              FilteringTextInputFormatter.allow(RegExp(r'[A-Za-z0-9-]')),
              _upperCase,
            ],
            validator: _licenseNumberRule,
            isError: _licenseNumberError,
            errorMessage: _licenseNumberRule(_licenseNumberController.text) ??
                'License number is required',
            onChanged: (_) {
              if (_licenseNumberError) {
                setState(() => _licenseNumberError = false);
              }
            },
          ),
          const SizedBox(height: 10),
          RegistrationDateField(
            label: 'Expiry date',
            hint: 'Select expiry date',
            helperText: 'Your license must not be expired.',
            birthday: _licenseExpiry,
            onClick: _pickLicenseExpiry,
            isError: _licenseExpiryError,
            errorMessage: 'Please select the expiry date.',
          ),
          const SizedBox(height: 28),

          // ── 5. Documents ──
          _buildSectionHeader('Documents',
              'JPG, PNG or PDF, up to 5 MB each. Your logistics partner checks these before approving you.'),
          RegistrationUploadField(
            label: 'Vehicle OR/CR *',
            fileName: _orCrFileName,
            isError: _orCrError,
            errorMessage: 'Please upload your vehicle OR/CR',
            helperText:
                'The Official Receipt and Certificate of Registration of the vehicle you will use.',
            onTap: _pickOrCrFile,
          ),
        ],
        const SizedBox(height: 28),

        PrimaryButton(
          text: 'Continue',
          onClick: _validateAndContinueToStep2,
        ),
        _buildFooter(),
      ],
    );
  }

  // ============================================================
  // STEP 2 — SIGN-IN
  // ============================================================

  Widget _buildStep2SignIn() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildSectionHeader(
            'Your sign-in details', 'Set up your login credentials'),
        RegistrationTextField(
          controller: _emailController,
          label: 'E-mail *',
          validator: BuyerRegistrationValidation.email,
          maxLength: 255,
          keyboardType: TextInputType.emailAddress,
          icon: Icons.email_outlined,
          customHint: 'you@example.com',
          isError: _emailError,
          errorMessage: _emailErrorMessage ?? 'Email is required',
          onChanged: (_) {
            if (_emailError) setState(() => _emailError = false);
          },
        ),
        const SizedBox(height: 16),
        RegistrationTextField(
          controller: _passwordController,
          label: 'Password *',
          validator: BuyerRegistrationValidation.password,
          validateOnChange: true,
          icon: Icons.lock_outline,
          customHint: 'Create a password',
          helperText: 'Use at least 8 characters.',
          isError: _passwordError,
          errorMessage: _passwordErrorMessage ?? 'Password is required',
          obscureText: !_passwordVisible,
          trailing: IconButton(
            icon: Icon(
              _passwordVisible ? Icons.visibility_off : Icons.visibility,
              color: const Color(0xFF7A6E72),
            ),
            onPressed: () =>
                setState(() => _passwordVisible = !_passwordVisible),
          ),
          onChanged: (_) {
            if (_passwordError) setState(() => _passwordError = false);
          },
        ),
        const SizedBox(height: 16),
        RegistrationTextField(
          controller: _confirmPasswordController,
          label: 'Confirm Password *',
          validator: (value) => value == _passwordController.text
              ? null
              : 'The password confirmation does not match.',
          validateOnChange: true,
          icon: Icons.lock_outline,
          customHint: 'Repeat your password',
          helperText:
              "We'll send a six-digit verification code to your email to finish creating your account.",
          isError: _confirmPasswordError,
          errorMessage: _confirmPasswordErrorMessage ?? '',
          obscureText: !_confirmPasswordVisible,
          trailing: IconButton(
            icon: Icon(
              _confirmPasswordVisible
                  ? Icons.visibility_off
                  : Icons.visibility,
              color: const Color(0xFF7A6E72),
            ),
            onPressed: () => setState(
                () => _confirmPasswordVisible = !_confirmPasswordVisible),
          ),
          onChanged: (_) {
            if (_confirmPasswordError) {
              setState(() => _confirmPasswordError = false);
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
                text: 'Send code',
                onClick: _validateAndSendVerification,
              ),
            ),
          ],
        ),
        _buildFooter(),
      ],
    );
  }

  // ============================================================
  // STEP 3 — VERIFY
  // ============================================================

  Widget _buildStep3Verify() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildSectionHeader('Verify your email',
            "We've sent a 6-digit verification code to ${_emailController.text.trim()}"),
        RegistrationTextField(
          controller: _otpController,
          label: 'Verification code *',
          icon: Icons.lock_clock_outlined,
          customHint: '6-digit code',
          keyboardType: TextInputType.number,
          maxLength: 6,
          inputFormatters: [FilteringTextInputFormatter.digitsOnly],
          helperText: 'For testing, you can enter any 6 digits (e.g. 123456).',
          isError: _otpError,
          errorMessage: 'Please enter a valid 6-digit code',
          onChanged: (_) {
            if (_otpError) setState(() => _otpError = false);
          },
        ),
        const SizedBox(height: 8),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              "Didn't receive the code? ",
              style: GoogleFonts.dmSans(
                  fontSize: 13, color: const Color(0xFF7A6E72)),
            ),
            GestureDetector(
              onTap: () =>
                  showCartzyFlash(context, 'New verification code sent!'),
              child: Text(
                'Resend code',
                style: GoogleFonts.dmSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF865561),
                ),
              ),
            ),
          ],
        ),
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
                text: _isSubmitting ? 'Submitting...' : 'Submit application',
                onClick: _submitApplication,
              ),
            ),
          ],
        ),
        _buildFooter(),
      ],
    );
  }
}

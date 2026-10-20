import 'package:flutter/material.dart';
import 'package:cartzy/widgets/cartzy_logo.dart';
import 'package:image_picker/image_picker.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/widgets/cartzy_flash_notif.dart';

// ============================================================
// RIDER REGISTRATION
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
  final _firstNameController = TextEditingController(text: 'driver');
  final _lastNameController = TextEditingController(text: 'pila');
  final _contactNoController = TextEditingController(text: '09566407337');
  final _birthdayController = TextEditingController(text: '28/09/2008');

  // ── Step 1: Where you'll deliver ──
  String _selectedLogisticsPartner = 'Metro South Sorting & Fulfillment Hub';
  static const List<String> logisticsPartnerOptions = [
    'Metro South Sorting & Fulfillment Hub',
    'Laguna Express Logistics Hub',
    'North Manila Central Distribution',
    'Cavite Regional Fulfillment Depot',
    'Batangas Central Hub',
  ];

  // ── Step 1: Your vehicle ──
  String _selectedVehicle = 'Tricycle';
  static const List<String> vehicleOptions = [
    'Select vehicle',
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
  final _licenseExpiryController = TextEditingController();

  // ── Step 1: Documents ──
  String? _idLicenseFileName;
  String? _orCrFileName;

  // Errors for Step 1
  bool _firstNameError = false;
  bool _lastNameError = false;
  bool _contactNoError = false;
  bool _birthdayError = false;
  bool _plateNumberError = false;
  bool _licenseNumberError = false;
  bool _licenseExpiryError = false;
  bool _idLicenseError = false;
  bool _orCrError = false;

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

  @override
  void dispose() {
    _firstNameController.dispose();
    _lastNameController.dispose();
    _contactNoController.dispose();
    _birthdayController.dispose();
    _plateNumberController.dispose();
    _licenseNumberController.dispose();
    _licenseExpiryController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _otpController.dispose();
    super.dispose();
  }

  // ── Date Pickers ──
  Future<void> _pickBirthday() async {
    final now = DateTime.now();
    final initialDate = DateTime(now.year - 18, now.month, now.day);
    final picked = await showDatePicker(
      context: context,
      initialDate: initialDate,
      firstDate: DateTime(1940),
      lastDate: now,
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: Color(0xFF5A4452),
              onPrimary: Colors.white,
              onSurface: Color(0xFF231F20),
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      final day = picked.day.toString().padLeft(2, '0');
      final month = picked.month.toString().padLeft(2, '0');
      final year = picked.year.toString();
      setState(() {
        _birthdayController.text = '$day/$month/$year';
        _birthdayError = false;
      });
    }
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
              primary: Color(0xFF5A4452),
              onPrimary: Colors.white,
              onSurface: Color(0xFF231F20),
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      final day = picked.day.toString().padLeft(2, '0');
      final month = picked.month.toString().padLeft(2, '0');
      final year = picked.year.toString();
      setState(() {
        _licenseExpiryController.text = '$day/$month/$year';
        _licenseExpiryError = false;
      });
    }
  }

  // ── File Pickers ──
  Future<void> _pickIdLicenseFile() async {
    try {
      final picker = ImagePicker();
      final XFile? file = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (file != null) {
        setState(() {
          _idLicenseFileName = file.name;
          _idLicenseError = false;
        });
      }
    } catch (_) {
      setState(() {
        _idLicenseFileName = 'drivers_license.jpg';
        _idLicenseError = false;
      });
    }
  }

  Future<void> _pickOrCrFile() async {
    try {
      final picker = ImagePicker();
      final XFile? file = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
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

  // ── Step 1 Validation ──
  void _validateAndContinueToStep2() {
    final isNonMotorized = _selectedVehicle == 'Bicycle' || _selectedVehicle == 'E-bike';

    setState(() {
      _firstNameError = _firstNameController.text.trim().isEmpty;
      _lastNameError = _lastNameController.text.trim().isEmpty;
      _contactNoError = _contactNoController.text.trim().isEmpty;
      _birthdayError = _birthdayController.text.trim().isEmpty;

      if (!isNonMotorized) {
        _plateNumberError = _plateNumberController.text.trim().isEmpty;
        _licenseNumberError = _licenseNumberController.text.trim().isEmpty;
        _licenseExpiryError = _licenseExpiryController.text.trim().isEmpty;
        _idLicenseError = _idLicenseFileName == null;
        _orCrError = _orCrFileName == null;
      } else {
        _plateNumberError = false;
        _licenseNumberError = false;
        _licenseExpiryError = false;
        _idLicenseError = false;
        _orCrError = false;
      }
    });

    if (_firstNameError ||
        _lastNameError ||
        _contactNoError ||
        _birthdayError ||
        _plateNumberError ||
        _licenseNumberError ||
        _licenseExpiryError ||
        _idLicenseError ||
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
      _emailError = email.isEmpty || !email.contains('@');
      _emailErrorMessage = email.isEmpty
          ? 'Email address is required'
          : (!email.contains('@') ? 'Enter a valid email address' : null);

      _passwordError = password.isEmpty || password.length < 8;
      _passwordErrorMessage = password.isEmpty
          ? 'Password is required'
          : (password.length < 8 ? 'Password must be at least 8 characters' : null);

      if (confirm.isEmpty) {
        _confirmPasswordError = true;
        _confirmPasswordErrorMessage = 'Please repeat your password';
      } else if (confirm != password) {
        _confirmPasswordError = true;
        _confirmPasswordErrorMessage = 'Passwords do not match';
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
    final otp = _otpController.text.trim();
    if (otp.length < 6) {
      setState(() => _otpError = true);
      showCartzyFlash(context, 'Please enter the 6-digit verification code.');
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
  // PENDING APPROVAL DIALOG (Original flow preserved)
  // ============================================================

  void _showPendingApprovalDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFF7F2F3),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.hourglass_top, color: Color(0xFF865561), size: 22),
              ),
              const SizedBox(width: 12),
              Text(
                'Registration Submitted',
                style: GoogleFonts.dmSans(
                  color: const Color(0xFF231F20),
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
            ],
          ),
          content: Text(
            "After submitting your registration, please wait for the "
            "Logistics/Sorting Center's approval, which will be sent to "
            "your email.",
            style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF7A6E72), height: 1.4),
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
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 580),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── Cartzy Logo ──
                  Center(
                    child: const CartzyLogo(height: 40),
                  ),
                  const SizedBox(height: 14),

                  // ── Pre-header ──
                  Center(
                    child: Text(
                      'CARTZY PARTNERS',
                      style: GoogleFonts.dmSans(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 1.4,
                        color: const Color(0xFF6B4B58),
                      ),
                    ),
                  ),
                  const SizedBox(height: 4),

                  // ── Headline ──
                  Center(
                    child: Text(
                      'Become a rider',
                      style: GoogleFonts.playfairDisplay(
                        fontSize: 30,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF231F20),
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),

                  // ── Subtitle ──
                  Center(
                    child: Text(
                      'Apply to deliver for one of CARTZY\'s logistics partners.',
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        color: const Color(0xFF7A6E72),
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                  const SizedBox(height: 18),

                  // ── Info Notice Box ──
                  _buildNoticeBox(),
                  const SizedBox(height: 20),

                  // ── Step Indicator (Details ── Sign-in ── Verify) ──
                  _buildStepIndicator(),
                  const SizedBox(height: 24),

                  // ── Active Step View ──
                  if (_currentStep == 1) _buildStep1Details(),
                  if (_currentStep == 2) _buildStep2SignIn(),
                  if (_currentStep == 3) _buildStep3Verify(),

                  const SizedBox(height: 24),

                  // ── Footer link: Already applied or registered? Log in ──
                  Center(
                    child: GestureDetector(
                      onTap: widget.onBackToLogin,
                      child: RichText(
                        text: TextSpan(
                          style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF7A6E72)),
                          children: [
                            const TextSpan(text: 'Already applied or registered? '),
                            TextSpan(
                              text: 'Log in',
                              style: GoogleFonts.dmSans(
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF865561),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            ),
          ),
        ),
      ),
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
        color: const Color(0xFFFDF7F5),
        borderRadius: BorderRadius.circular(8),
        border: const Border(
          left: BorderSide(color: Color(0xFFC48B71), width: 3),
          top: BorderSide(color: Color(0xFFF0DFD7)),
          right: BorderSide(color: Color(0xFFF0DFD7)),
          bottom: BorderSide(color: Color(0xFFF0DFD7)),
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            margin: const EdgeInsets.only(top: 2),
            padding: const EdgeInsets.all(2),
            decoration: const BoxDecoration(
              color: Color(0xFFC48B71),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.info_outline, size: 12, color: Colors.white),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'The logistics partner you choose reviews your application. You can sign in and accept deliveries once they approve you.',
              style: GoogleFonts.dmSans(
                fontSize: 12,
                color: const Color(0xFF5A4940),
                height: 1.35,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // STEP INDICATOR (Details ──── Sign-in ──── Verify)
  // ============================================================

  Widget _buildStepIndicator() {
    return Row(
      children: [
        Text(
          'Details',
          style: GoogleFonts.dmSans(
            fontSize: 12.5,
            fontWeight: _currentStep >= 1 ? FontWeight.w700 : FontWeight.w500,
            color: _currentStep >= 1 ? const Color(0xFF231F20) : const Color(0xFFA09699),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Container(
            height: 2,
            color: _currentStep >= 2 ? const Color(0xFF865561) : const Color(0xFFE5DFE2),
          ),
        ),
        const SizedBox(width: 8),
        Text(
          'Sign-in',
          style: GoogleFonts.dmSans(
            fontSize: 12.5,
            fontWeight: _currentStep >= 2 ? FontWeight.w700 : FontWeight.w500,
            color: _currentStep >= 2 ? const Color(0xFF231F20) : const Color(0xFFA09699),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Container(
            height: 2,
            color: _currentStep >= 3 ? const Color(0xFF865561) : const Color(0xFFE5DFE2),
          ),
        ),
        const SizedBox(width: 8),
        Text(
          'Verify',
          style: GoogleFonts.dmSans(
            fontSize: 12.5,
            fontWeight: _currentStep >= 3 ? FontWeight.w700 : FontWeight.w500,
            color: _currentStep >= 3 ? const Color(0xFF231F20) : const Color(0xFFA09699),
          ),
        ),
      ],
    );
  }

  // ============================================================
  // STEP 1 — DETAILS (Screenshots 1, 2, 3)
  // ============================================================

  Widget _buildStep1Details() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // ── 1. Your personal details ──
        Text(
          'Your personal details',
          style: GoogleFonts.dmSans(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 2),
        Text(
          'Enter your name and personal details',
          style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
        ),
        const SizedBox(height: 14),

        // First name & Last name Row
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: _buildInputField(
                label: 'First name *',
                controller: _firstNameController,
                prefixIcon: Icons.person_outline,
                isError: _firstNameError,
                onChanged: (_) {
                  if (_firstNameError) setState(() => _firstNameError = false);
                },
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildInputField(
                label: 'Last name *',
                controller: _lastNameController,
                isError: _lastNameError,
                onChanged: (_) {
                  if (_lastNameError) setState(() => _lastNameError = false);
                },
              ),
            ),
          ],
        ),
        const SizedBox(height: 14),

        // Mobile number
        _buildInputField(
          label: 'Mobile number *',
          controller: _contactNoController,
          prefixIcon: Icons.phone_outlined,
          keyboardType: TextInputType.phone,
          isError: _contactNoError,
          helperText: '09XXXXXXXXX or +639XXXXXXXXX. Each mobile number can only be used for one account.',
          onChanged: (_) {
            if (_contactNoError) setState(() => _contactNoError = false);
          },
        ),
        const SizedBox(height: 14),

        // Birthday
        _buildInputField(
          label: 'Birthday *',
          controller: _birthdayController,
          prefixIcon: Icons.calendar_today_outlined,
          suffixIcon: Icons.calendar_today_outlined,
          readOnly: true,
          onTap: _pickBirthday,
          isError: _birthdayError,
          helperText: 'Riders must be at least 18 years old.',
        ),
        const SizedBox(height: 22),

        // ── 2. Where you'll deliver ──
        Text(
          'Where you\'ll deliver',
          style: GoogleFonts.dmSans(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 12),

        _buildLabel('Logistics partner *'),
        const SizedBox(height: 6),
        _buildDropdownContainer(
          icon: Icons.local_shipping_outlined,
          child: DropdownButtonHideUnderline(
            child: DropdownButton<String>(
              value: _selectedLogisticsPartner,
              isExpanded: true,
              icon: const Icon(Icons.keyboard_arrow_down, color: Color(0xFF7A6E72), size: 20),
              style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF231F20)),
              items: logisticsPartnerOptions.map((partner) {
                return DropdownMenuItem<String>(
                  value: partner,
                  child: Text(partner, overflow: TextOverflow.ellipsis),
                );
              }).toList(),
              onChanged: (val) {
                if (val != null) setState(() => _selectedLogisticsPartner = val);
              },
            ),
          ),
        ),
        const SizedBox(height: 22),

        // ── 3. Your vehicle ──
        Text(
          'Your vehicle',
          style: GoogleFonts.dmSans(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 12),

        // Vehicle type & Plate number Row
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildLabel('Vehicle type *'),
                  const SizedBox(height: 6),
                  _buildDropdownContainer(
                    icon: Icons.two_wheeler_outlined,
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<String>(
                        value: _selectedVehicle,
                        isExpanded: true,
                        icon: const Icon(Icons.keyboard_arrow_down, color: Color(0xFF7A6E72), size: 20),
                        style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF231F20)),
                        items: vehicleOptions.map((veh) {
                          return DropdownMenuItem<String>(
                            value: veh,
                            child: Text(veh, overflow: TextOverflow.ellipsis),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val != null) setState(() => _selectedVehicle = val);
                        },
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildInputField(
                label: 'Plate number *',
                controller: _plateNumberController,
                prefixIcon: Icons.badge_outlined,
                hintText: '123ABC OR NBC1234',
                isError: _plateNumberError,
                onChanged: (_) {
                  if (_plateNumberError) setState(() => _plateNumberError = false);
                },
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          'Bicycle and e-bike riders don\'t need a plate, driver\'s license or OR/CR.',
          style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFF7A6E72)),
        ),
        const SizedBox(height: 22),

        // ── 4. Driver's license ──
        Text(
          'Driver\'s license',
          style: GoogleFonts.dmSans(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 12),

        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: _buildInputField(
                label: 'License number *',
                controller: _licenseNumberController,
                prefixIcon: Icons.badge_outlined,
                hintText: 'A00-00-000000',
                isError: _licenseNumberError,
                onChanged: (_) {
                  if (_licenseNumberError) setState(() => _licenseNumberError = false);
                },
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildInputField(
                label: 'Expiry date *',
                controller: _licenseExpiryController,
                suffixIcon: Icons.calendar_today_outlined,
                hintText: 'dd/mm/yyyy',
                readOnly: true,
                onTap: _pickLicenseExpiry,
                isError: _licenseExpiryError,
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          'Format: A00-00-000000 (e.g. H83-78-937937)',
          style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFF7A6E72)),
        ),
        const SizedBox(height: 22),

        // ── 5. Documents ──
        Text(
          'Documents',
          style: GoogleFonts.dmSans(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 2),
        Text(
          'JPG, PNG or PDF, up to 5 MB each. Your logistics partner checks these before approving you.',
          style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
        ),
        const SizedBox(height: 14),

        _buildLabel('Driver\'s license *'),
        const SizedBox(height: 6),
        _buildFileUploadBox(
          icon: Icons.badge_outlined,
          fileName: _idLicenseFileName,
          isError: _idLicenseError,
          onTap: _pickIdLicenseFile,
          helperText: 'A clear photo of the front of your license.',
        ),
        const SizedBox(height: 16),

        _buildLabel('Vehicle OR/CR *'),
        const SizedBox(height: 6),
        _buildFileUploadBox(
          icon: Icons.description_outlined,
          fileName: _orCrFileName,
          isError: _orCrError,
          onTap: _pickOrCrFile,
          helperText: 'The Official Receipt and Certificate of Registration of the vehicle you will use.',
        ),
        const SizedBox(height: 28),

        // ── CONTINUE TO SIGN-IN DETAILS Button ──
        SizedBox(
          width: double.infinity,
          height: 48,
          child: ElevatedButton(
            onPressed: _validateAndContinueToStep2,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF5A4452),
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: Text(
              'CONTINUE TO SIGN-IN DETAILS',
              style: GoogleFonts.dmSans(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                letterSpacing: 0.5,
              ),
            ),
          ),
        ),
      ],
    );
  }

  // ============================================================
  // STEP 2 — SIGN-IN (Screenshot 4)
  // ============================================================

  Widget _buildStep2SignIn() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Your sign-in details',
          style: GoogleFonts.dmSans(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 2),
        Text(
          'Set up your login credentials',
          style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
        ),
        const SizedBox(height: 18),

        // Email address
        _buildInputField(
          label: 'Email address *',
          controller: _emailController,
          prefixIcon: Icons.mail_outline,
          hintText: 'you@example.com',
          keyboardType: TextInputType.emailAddress,
          isError: _emailError,
          errorMessage: _emailErrorMessage,
          onChanged: (_) {
            if (_emailError) setState(() => _emailError = false);
          },
        ),
        const SizedBox(height: 14),

        // Password
        _buildInputField(
          label: 'Password *',
          controller: _passwordController,
          prefixIcon: Icons.lock_outline,
          hintText: 'Create a password',
          obscureText: !_passwordVisible,
          suffixIcon: _passwordVisible ? Icons.visibility_off_outlined : Icons.visibility_outlined,
          onSuffixTap: () => setState(() => _passwordVisible = !_passwordVisible),
          isError: _passwordError,
          errorMessage: _passwordErrorMessage,
          helperText: 'Use at least 8 characters, one uppercase letter, one lowercase letter, one number, and one symbol.',
          onChanged: (_) {
            if (_passwordError) setState(() => _passwordError = false);
          },
        ),
        const SizedBox(height: 14),

        // Confirm password
        _buildInputField(
          label: 'Confirm password *',
          controller: _confirmPasswordController,
          prefixIcon: Icons.lock_outline,
          hintText: 'Repeat your password',
          obscureText: !_confirmPasswordVisible,
          suffixIcon: _confirmPasswordVisible ? Icons.visibility_off_outlined : Icons.visibility_outlined,
          onSuffixTap: () => setState(() => _confirmPasswordVisible = !_confirmPasswordVisible),
          isError: _confirmPasswordError,
          errorMessage: _confirmPasswordErrorMessage,
          helperText: 'Please type both passwords manually. Copying, cutting, pasting and dropping text are disabled in these fields.\nWe\'ll send a six-digit verification code to this email address to finish creating your Cartzy account.',
          onChanged: (_) {
            if (_confirmPasswordError) setState(() => _confirmPasswordError = false);
          },
        ),
        const SizedBox(height: 28),

        // Row of buttons: Back + Send verification code
        Row(
          children: [
            Expanded(
              flex: 1,
              child: SizedBox(
                height: 48,
                child: OutlinedButton(
                  onPressed: () => setState(() => _currentStep = 1),
                  style: OutlinedButton.styleFrom(
                    backgroundColor: const Color(0xFFFBF8F6),
                    side: const BorderSide(color: Color(0xFFEBDCCE), width: 1.2),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.arrow_back, size: 16, color: Color(0xFF4A3E47)),
                      const SizedBox(width: 6),
                      Text(
                        'Back',
                        style: GoogleFonts.dmSans(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF4A3E47),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              flex: 2,
              child: SizedBox(
                height: 48,
                child: ElevatedButton(
                  onPressed: _validateAndSendVerification,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF5A4452),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        'Send verification code',
                        style: GoogleFonts.dmSans(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(width: 6),
                      const Icon(Icons.arrow_forward_rounded, size: 16),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
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
        Text(
          'Verify your email',
          style: GoogleFonts.dmSans(
            fontSize: 15,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 2),
        Text(
          'We\'ve sent a 6-digit verification code to ${_emailController.text}',
          style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
        ),
        const SizedBox(height: 20),

        _buildInputField(
          label: 'Verification code *',
          controller: _otpController,
          prefixIcon: Icons.lock_clock_outlined,
          hintText: 'Enter 6-digit code (e.g. 123456)',
          keyboardType: TextInputType.number,
          isError: _otpError,
          errorMessage: _otpError ? 'Please enter a valid 6-digit code' : null,
          helperText: 'For testing, you can enter any 6 digits (e.g. 123456).',
          onChanged: (_) {
            if (_otpError) setState(() => _otpError = false);
          },
        ),
        const SizedBox(height: 12),

        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Didn\'t receive code?',
              style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
            ),
            TextButton(
              onPressed: () {
                showCartzyFlash(context, 'New verification code sent!');
              },
              child: Text(
                'Resend code',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF865561),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 20),

        Row(
          children: [
            Expanded(
              flex: 1,
              child: SizedBox(
                height: 48,
                child: OutlinedButton(
                  onPressed: () => setState(() => _currentStep = 2),
                  style: OutlinedButton.styleFrom(
                    backgroundColor: const Color(0xFFFBF8F6),
                    side: const BorderSide(color: Color(0xFFEBDCCE), width: 1.2),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.arrow_back, size: 16, color: Color(0xFF4A3E47)),
                      const SizedBox(width: 6),
                      Text(
                        'Back',
                        style: GoogleFonts.dmSans(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF4A3E47),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              flex: 2,
              child: SizedBox(
                height: 48,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _submitApplication,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF5A4452),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: _isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              'Submit application',
                              style: GoogleFonts.dmSans(
                                fontSize: 13.5,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(width: 6),
                            const Icon(Icons.check_circle_outline, size: 16),
                          ],
                        ),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  // ============================================================
  // REUSABLE FIELD BUILDERS
  // ============================================================

  Widget _buildLabel(String label) {
    final parts = label.split('*');
    return RichText(
      text: TextSpan(
        style: GoogleFonts.dmSans(
          fontSize: 12.5,
          fontWeight: FontWeight.w600,
          color: const Color(0xFF231F20),
        ),
        children: [
          TextSpan(text: parts[0]),
          if (parts.length > 1)
            const TextSpan(
              text: '*',
              style: TextStyle(color: Color(0xFFC44D4D), fontWeight: FontWeight.w700),
            ),
        ],
      ),
    );
  }

  Widget _buildInputField({
    required String label,
    required TextEditingController controller,
    IconData? prefixIcon,
    IconData? suffixIcon,
    VoidCallback? onSuffixTap,
    String? hintText,
    String? helperText,
    String? errorMessage,
    bool isError = false,
    bool obscureText = false,
    bool readOnly = false,
    TextInputType? keyboardType,
    VoidCallback? onTap,
    ValueChanged<String>? onChanged,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildLabel(label),
        const SizedBox(height: 6),
        Container(
          height: 44,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: isError ? const Color(0xFFC44D4D) : const Color(0xFFE2DADB),
              width: 1.1,
            ),
          ),
          child: TextField(
            controller: controller,
            obscureText: obscureText,
            readOnly: readOnly,
            onTap: onTap,
            keyboardType: keyboardType,
            onChanged: onChanged,
            style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF231F20)),
            decoration: InputDecoration(
              isDense: true,
              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              border: InputBorder.none,
              hintText: hintText,
              hintStyle: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFFA89CA0)),
              prefixIcon: prefixIcon != null
                  ? Icon(prefixIcon, size: 18, color: const Color(0xFF7A6E72))
                  : null,
              suffixIcon: suffixIcon != null
                  ? GestureDetector(
                      onTap: onSuffixTap ?? onTap,
                      child: Icon(suffixIcon, size: 18, color: const Color(0xFF7A6E72)),
                    )
                  : null,
            ),
          ),
        ),
        if (isError && errorMessage != null) ...[
          const SizedBox(height: 4),
          Text(
            errorMessage,
            style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFFC44D4D)),
          ),
        ] else if (helperText != null) ...[
          const SizedBox(height: 4),
          Text(
            helperText,
            style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFF7A6E72), height: 1.3),
          ),
        ],
      ],
    );
  }

  Widget _buildDropdownContainer({required IconData icon, required Widget child}) {
    return Container(
      height: 44,
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE2DADB), width: 1.1),
      ),
      child: Row(
        children: [
          Icon(icon, size: 18, color: const Color(0xFF7A6E72)),
          const SizedBox(width: 8),
          Expanded(child: child),
        ],
      ),
    );
  }

  Widget _buildFileUploadBox({
    required IconData icon,
    required String? fileName,
    required VoidCallback onTap,
    required String helperText,
    bool isError = false,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          height: 44,
          padding: const EdgeInsets.symmetric(horizontal: 12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: isError ? const Color(0xFFC44D4D) : const Color(0xFFE2DADB),
              width: 1.1,
            ),
          ),
          child: Row(
            children: [
              Icon(icon, size: 18, color: const Color(0xFF7A6E72)),
              const SizedBox(width: 8),
              ElevatedButton(
                onPressed: onTap,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFEDE5E8),
                  foregroundColor: const Color(0xFF4A3E47),
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  minimumSize: Size.zero,
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(7)),
                ),
                child: Text(
                  'Choose file',
                  style: GoogleFonts.dmSans(fontSize: 12, fontWeight: FontWeight.w600),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  fileName ?? 'No file chosen',
                  style: GoogleFonts.dmSans(
                    fontSize: 12,
                    color: fileName != null ? const Color(0xFF231F20) : const Color(0xFF7A6E72),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 4),
        Text(
          helperText,
          style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFF7A6E72)),
        ),
      ],
    );
  }
}

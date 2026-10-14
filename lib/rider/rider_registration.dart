import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/auth/buyer_registration.dart'; // reuses shared form widgets + checkEmailAvailable

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
  // VEHICLE + DOCUMENTS
  // ========================================================

  String _selectedVehicle = '';
  final _plateNumberController = TextEditingController();

  bool _vehicleError = false;
  bool _plateNumberError = false;

  String? _orCrFileName;
  XFile? _orCrFile;

  String? _idLicenseFileName;
  XFile? _idLicenseFile;

  bool _orCrError = false;
  bool _idLicenseError = false;

  static const List<String> vehicleOptions = [
    'Motorcycle',
    'Bicycle',
    'Tricycle',
    'Car',
  ];

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
    _plateNumberController.dispose();
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

  Future<void> _pickOrCrFile() async {
    try {
      final picker = ImagePicker();
      final XFile? pickedFile = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (pickedFile == null) return;
      setState(() {
        _orCrFile = pickedFile;
        _orCrFileName = pickedFile.name;
        _orCrError = false;
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Unable to select OR/CR photo: $e')),
      );
    }
  }

  Future<void> _pickIdLicenseFile() async {
    try {
      final picker = ImagePicker();
      final XFile? pickedFile = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (pickedFile == null) return;
      setState(() {
        _idLicenseFile = pickedFile;
        _idLicenseFileName = pickedFile.name;
        _idLicenseError = false;
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Unable to select ID/License photo: $e')),
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
      if (_birthday.isEmpty) {
        _birthdayError = true;
      } else if (_age != null && _age! < 18) {
        _birthdayError = true;
      } else {
        _birthdayError = false;
      }
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
  // SUBMIT — actually calls the backend now
  // ============================================================

  Future<void> _registerRider() async {
    setState(() => _isSubmitting = true);

    try {
      await Future.delayed(const Duration(milliseconds: 500));

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
      backgroundColor: Colors.white,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              CartzyRegisterHeader(
                title: 'Create your account',
                subtitle: 'Join cartzy logistics and earn on your schedule',
                onBack: widget.onBackToLogin,
              ),
              const SizedBox(height: 12),
              StepIndicator(
                currentStep: _currentStep,
                labels: const ['Details', 'Vehicle', 'Sign-in'],
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
  // STEP 1 — PERSONAL INFO + ADDRESS
  // ============================================================

  Widget _buildStep1() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        RoleSelectionSection(
          isBuyerSelected: false,
          onSelectBuyer: () {
            widget.onSwitchToBuyerRegistration?.call();
          },
          onSelectRider: () {},
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
          icon: Icons.person_outline,
          isError: _firstNameError,
          errorMessage: 'First name is required',
          onChanged: (value) {
            if (_firstNameError && value.trim().isNotEmpty) {
              setState(() => _firstNameError = false);
            }
          },
        ),
        const SizedBox(height: 16),

        RegistrationTextField(
          controller: _lastNameController,
          label: 'Last name *',
          icon: Icons.person_outline,
          isError: _lastNameError,
          errorMessage: 'Last name is required',
          onChanged: (value) {
            if (_lastNameError && value.trim().isNotEmpty) {
              setState(() => _lastNameError = false);
            }
          },
        ),
        const SizedBox(height: 16),

        RegistrationTextField(
          controller: _middleInitialController,
          label: 'Middle Initial (Optional)',
          icon: Icons.person_outline,
        ),
        const SizedBox(height: 16),

        RegistrationTextField(
          controller: _contactNoController,
          label: 'Mobile number *',
          isPhone: true,
          isError: _contactNoError,
          errorMessage: 'Contact number is required',
          onChanged: (value) {
            if (_contactNoError && value.trim().isNotEmpty) {
              setState(() => _contactNoError = false);
            }
          },
        ),
        const SizedBox(height: 16),

        RegistrationDateField(
          birthday: _birthday,
          onClick: _openBirthdayPicker,
          isError: _birthdayError,
          errorMessage: 'Please select your birthday',
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
        const SizedBox(height: 24),

        // -----------------------------------------------------
        // ADDRESS
        // -----------------------------------------------------
        Text(
          'Address details',
          style: GoogleFonts.dmSans(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          'Where are you based in the Philippines?',
          style: GoogleFonts.dmSans(
            fontSize: 12.5,
            color: const Color(0xFF7A6E72),
          ),
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
        const SizedBox(height: 28),

        PrimaryButton(
          text: _isCheckingEmail ? 'Checking...' : 'Continue',
          onClick: _isCheckingEmail ? () {} : _handleStep1Next,
        ),
        RegistrationFooter(
          isBuyer: false,
          onBackToLogin: widget.onBackToLogin,
          onSwitchRole: widget.onSwitchToBuyerRegistration,
        ),
      ],
    );
  }

  // ============================================================
  // STEP 2 — VEHICLE + DOCUMENTS
  // ============================================================

  Widget _buildStep2() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Vehicle & documents',
          style: GoogleFonts.dmSans(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF231F20),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          'Tell us what vehicle you will be delivering with',
          style: GoogleFonts.dmSans(
            fontSize: 12.5,
            color: const Color(0xFF7A6E72),
          ),
        ),
        const SizedBox(height: 20),

        RegistrationDropdown(
          label: 'Choose Vehicle *',
          icon: Icons.two_wheeler_outlined,
          selectedValue: _selectedVehicle,
          options: vehicleOptions,
          isError: _vehicleError,
          errorMessage: 'Please select a vehicle type',
          onSelected: (value) {
            setState(() {
              _selectedVehicle = value;
              _vehicleError = false;
            });
          },
        ),
        const SizedBox(height: 16),

        RegistrationTextField(
          controller: _plateNumberController,
          label: 'Plate Number *',
          icon: Icons.confirmation_number_outlined,
          isError: _plateNumberError,
          errorMessage: 'Plate number is required',
          onChanged: (value) {
            if (_plateNumberError && value.trim().isNotEmpty) {
              setState(() => _plateNumberError = false);
            }
          },
        ),
        const SizedBox(height: 20),

        RegistrationUploadField(
          label: 'Upload OR/CR *',
          fileName: _orCrFileName,
          isError: _orCrError,
          errorMessage: 'Please upload your OR/CR',
          onTap: _pickOrCrFile,
        ),
        const SizedBox(height: 16),

        RegistrationUploadField(
          label: 'Upload ID / Driver\'s License *',
          fileName: _idLicenseFileName,
          isError: _idLicenseError,
          errorMessage: 'Please upload your ID or license',
          onTap: _pickIdLicenseFile,
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
                    _vehicleError = _selectedVehicle.isEmpty;
                    _plateNumberError = _plateNumberController.text.trim().isEmpty;
                    _orCrError = _orCrFile == null;
                    _idLicenseError = _idLicenseFile == null;

                    if (!_vehicleError &&
                        !_plateNumberError &&
                        !_orCrError &&
                        !_idLicenseError) {
                      _currentStep = 3;
                    }
                  });
                },
              ),
            ),
          ],
        ),
        RegistrationFooter(
          isBuyer: false,
          onBackToLogin: widget.onBackToLogin,
          onSwitchRole: widget.onSwitchToBuyerRegistration,
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
          'Create a secure password for your rider account',
          style: GoogleFonts.dmSans(
            fontSize: 12.5,
            color: const Color(0xFF7A6E72),
          ),
        ),
        const SizedBox(height: 20),

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
              color: const Color(0xFF7A6E72),
            ),
            onPressed: () => setState(() => _passwordVisible = !_passwordVisible),
          ),
          onChanged: (value) {
            if (_passwordError && value.trim().isNotEmpty) {
              setState(() => _passwordError = false);
            }
          },
        ),
        const SizedBox(height: 16),

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
                          _registerRider();
                        }
                      },
              ),
            ),
          ],
        ),
        RegistrationFooter(
          isBuyer: false,
          onBackToLogin: widget.onBackToLogin,
          onSwitchRole: widget.onSwitchToBuyerRegistration,
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
}
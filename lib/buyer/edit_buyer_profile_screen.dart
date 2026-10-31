import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/utils/input_rules.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';

// ============================================================
// PROFILE SETTINGS SCREEN (Cartzy Design)
// Matches the dedicated profile settings screen layout
// ============================================================

class ProfileSettingsScreen extends StatefulWidget {
  final VoidCallback onBack;
  final ValueChanged<Map<String, dynamic>>? onProfileUpdated;
  final String? initialName;
  final String? initialEmail;
  final String? initialPhone;
  final String? initialBirthday;

  const ProfileSettingsScreen({
    super.key,
    required this.onBack,
    this.onProfileUpdated,
    this.initialName,
    this.initialEmail,
    this.initialPhone,
    this.initialBirthday,
  });

  @override
  State<ProfileSettingsScreen> createState() => _ProfileSettingsScreenState();
}

// Backwards-compatibility alias
typedef EditBuyerProfileScreen = ProfileSettingsScreen;

class _ProfileSettingsScreenState extends State<ProfileSettingsScreen> {
  late final TextEditingController _nameController;
  late final TextEditingController _emailController;
  late final TextEditingController _phoneController;
  late final TextEditingController _birthdayController;

  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    final user = AuthService.currentUser ?? {};
    final rawName = (widget.initialName != null && widget.initialName!.isNotEmpty)
        ? widget.initialName!
        : (user['name']?.toString() ?? '');
    final rawEmail = (widget.initialEmail != null && widget.initialEmail!.isNotEmpty)
        ? widget.initialEmail!
        : (user['email']?.toString() ?? '');
    final rawPhone = (widget.initialPhone != null && widget.initialPhone!.isNotEmpty)
        ? widget.initialPhone!
        : (user['phone']?.toString() ?? '');
    final rawBirthday = (widget.initialBirthday != null && widget.initialBirthday!.isNotEmpty)
        ? widget.initialBirthday!
        : (user['birthday']?.toString() ?? '');

    _nameController = TextEditingController(text: rawName);
    _emailController = TextEditingController(text: rawEmail);
    _phoneController = TextEditingController(text: rawPhone);
    _birthdayController = TextEditingController(
      text: rawBirthday.isNotEmpty ? rawBirthday : '2005-09-20',
    );
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _birthdayController.dispose();
    super.dispose();
  }

  Future<void> _pickBirthday() async {
    DateTime initial = DateTime.tryParse(_birthdayController.text.trim()) ??
        DateTime(2005, 9, 20);
    if (initial.isAfter(DateTime.now())) initial = DateTime(2005, 9, 20);

    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(1930),
      lastDate: DateTime.now(),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: CartzyColors.burgundy,
              onPrimary: Colors.white,
              onSurface: CartzyColors.navy,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      final formatted =
          '${picked.year.toString().padLeft(4, '0')}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}';
      setState(() {
        _birthdayController.text = formatted;
      });
    }
  }

  void _saveChanges() {
    final nameError = InputRules.fullName(_nameController.text);
    if (nameError != null) {
      showCartzyFlash(context, nameError);
      return;
    }

    final phoneText = _phoneController.text.trim();
    if (phoneText.isNotEmpty) {
      final phoneError = InputRules.phone(phoneText);
      if (phoneError != null) {
        showCartzyFlash(context, phoneError);
        return;
      }
    }

    setState(() => _isSaving = true);

    final updated = Map<String, dynamic>.from(AuthService.currentUser ?? {});
    updated['name'] = _nameController.text.trim();
    updated['phone'] = _phoneController.text.trim();
    updated['birthday'] = _birthdayController.text.trim();

    AuthService.setSession(user: updated, token: AuthService.token);
    widget.onProfileUpdated?.call(updated);

    showCartzyFlash(context, 'Profile updated successfully!');
    widget.onBack();
  }

  @override
  Widget build(BuildContext context) {
    const fieldBorderColor = Color(0xFFEDE4E7);
    const textColor = Color(0xFF231F20);
    const labelColor = Color(0xFF231F20);
    const subtitleColor = Color(0xFF8E8286);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F5),
      appBar: AppBar(
        backgroundColor: const Color(0xFFFAF7F5),
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: textColor),
          onPressed: widget.onBack,
        ),
        title: Text(
          'Profile settings',
          style: GoogleFonts.dmSans(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: textColor,
          ),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ── Full Name ──
              Text(
                'Full name',
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: labelColor,
                ),
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _nameController,
                inputFormatters: InputRules.fullNameFormatters,
                style: GoogleFonts.dmSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w500,
                  color: textColor,
                ),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: textColor, width: 1.5),
                  ),
                ),
              ),

              const SizedBox(height: 18),

              // ── Email Address ──
              Text(
                'Email address',
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: labelColor,
                ),
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _emailController,
                readOnly: true,
                style: GoogleFonts.dmSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w500,
                  color: textColor,
                ),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                ),
              ),
              const SizedBox(height: 6),
              Text(
                'This email is linked to Google and cannot be changed here.',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  color: subtitleColor,
                  height: 1.35,
                ),
              ),

              const SizedBox(height: 18),

              // ── Mobile Number ──
              Text(
                'Mobile number',
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: labelColor,
                ),
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _phoneController,
                keyboardType: TextInputType.phone,
                inputFormatters: InputRules.phoneFormatters,
                style: GoogleFonts.dmSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w500,
                  color: textColor,
                ),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: textColor, width: 1.5),
                  ),
                ),
              ),

              const SizedBox(height: 18),

              // ── Birthday ──
              Text(
                'Birthday',
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: labelColor,
                ),
              ),
              const SizedBox(height: 8),
              InkWell(
                onTap: _pickBirthday,
                borderRadius: BorderRadius.circular(14),
                child: IgnorePointer(
                  child: TextFormField(
                    controller: _birthdayController,
                    readOnly: true,
                    style: GoogleFonts.dmSans(
                      fontSize: 15,
                      fontWeight: FontWeight.w500,
                      color: textColor,
                    ),
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: Colors.white,
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 15),
                      suffixIcon: const Padding(
                        padding: EdgeInsets.only(right: 14),
                        child: Icon(
                          Icons.calendar_month_outlined,
                          color: Color(0xFF7A6E72),
                          size: 22,
                        ),
                      ),
                      suffixIconConstraints:
                          const BoxConstraints(minWidth: 40, minHeight: 40),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: const BorderSide(
                            color: fieldBorderColor, width: 1.2),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: const BorderSide(
                            color: fieldBorderColor, width: 1.2),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide:
                            const BorderSide(color: textColor, width: 1.5),
                      ),
                    ),
                  ),
                ),
              ),

              const SizedBox(height: 32),

              // ── SAVE CHANGES Button ──
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isSaving ? null : _saveChanges,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF231F20),
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: const Color(0xFF7A6E72),
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                  child: Text(
                    'SAVE CHANGES',
                    style: GoogleFonts.dmSans(
                      fontSize: 13.5,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 0.8,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

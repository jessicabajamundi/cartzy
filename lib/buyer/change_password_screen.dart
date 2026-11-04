import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _formKey = GlobalKey<FormState>();

  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _obscureCurrent = true;
  bool _obscureNew = true;
  bool _obscureConfirm = true;

  bool _isLoading = false;
  String? _serverErrorMessage;

  @override
  void initState() {
    super.initState();
    _newPasswordController.addListener(() => setState(() {}));
    _confirmPasswordController.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  // Password criteria checks
  bool get _hasMinLength => _newPasswordController.text.length >= 8;
  bool get _hasUppercase => RegExp(r'[A-Z]').hasMatch(_newPasswordController.text);
  bool get _hasLowercase => RegExp(r'[a-z]').hasMatch(_newPasswordController.text);
  bool get _hasNumber => RegExp(r'[0-9]').hasMatch(_newPasswordController.text);
  bool get _hasSpecialChar =>
      RegExp(r'[!@#\$%^&*(),.?":{}|<>_\+\-=\\/\[\]~`]').hasMatch(_newPasswordController.text);

  bool get _isPasswordValid =>
      _hasMinLength && _hasUppercase && _hasLowercase && _hasNumber && _hasSpecialChar;

  Future<void> _submitChangePassword() async {
    setState(() {
      _serverErrorMessage = null;
    });

    final current = _currentPasswordController.text.trim();
    final newPass = _newPasswordController.text;
    final confirm = _confirmPasswordController.text;

    if (current.isEmpty) {
      setState(() => _serverErrorMessage = 'Please enter your current password.');
      return;
    }

    if (newPass.isEmpty) {
      setState(() => _serverErrorMessage = 'Please enter your new password.');
      return;
    }

    if (!_isPasswordValid) {
      setState(() => _serverErrorMessage = 'New password does not meet the security requirements.');
      return;
    }

    if (newPass != confirm) {
      setState(() => _serverErrorMessage = 'New password and confirmation do not match.');
      return;
    }

    if (current == newPass) {
      setState(() => _serverErrorMessage = 'New password must be different from current password.');
      return;
    }

    setState(() => _isLoading = true);

    try {
      await AuthService.changePassword(
        currentPassword: current,
        newPassword: newPass,
        confirmPassword: confirm,
      );

      if (!mounted) return;

      showCartzyFlash(context, 'Password updated successfully!');
      Navigator.of(context).pop(true);
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _serverErrorMessage = e.message;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _serverErrorMessage = 'Unable to update password. Please check your connection and try again.';
      });
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: CartzyColors.navy, size: 20),
          onPressed: () => Navigator.of(context).maybePop(),
        ),
        title: Text(
          'Change Password',
          style: GoogleFonts.dmSans(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: CartzyColors.navy,
          ),
        ),
        centerTitle: true,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Header Card
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.04),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: CartzyColors.rose50,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.lock_reset_rounded,
                        color: CartzyColors.rose700,
                        size: 26,
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Update Your Password',
                            style: GoogleFonts.dmSans(
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                              color: CartzyColors.navy,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            'Create a strong password with at least 8 characters to secure your account.',
                            style: GoogleFonts.dmSans(
                              fontSize: 12,
                              color: const Color(0xFF7A6E72),
                              height: 1.3,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              // Server Error Banner
              if (_serverErrorMessage != null) ...[
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFDE8E8),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFF8B4B4)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.error_outline_rounded, color: Color(0xFFE02424), size: 20),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          _serverErrorMessage!,
                          style: GoogleFonts.dmSans(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: const Color(0xFF9B1C1C),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
              ],

              // Form fields card
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.04),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // CURRENT PASSWORD
                    _buildFieldLabel('Current Password'),
                    const SizedBox(height: 8),
                    TextField(
                      controller: _currentPasswordController,
                      obscureText: _obscureCurrent,
                      textInputAction: TextInputAction.next,
                      style: GoogleFonts.dmSans(fontSize: 14, color: CartzyColors.navy),
                      decoration: _buildInputDecoration(
                        hintText: 'Enter current password',
                        prefixIcon: Icons.lock_outline_rounded,
                        isObscured: _obscureCurrent,
                        onToggleVisibility: () => setState(() => _obscureCurrent = !_obscureCurrent),
                      ),
                    ),

                    const SizedBox(height: 20),

                    // NEW PASSWORD
                    _buildFieldLabel('New Password'),
                    const SizedBox(height: 8),
                    TextField(
                      controller: _newPasswordController,
                      obscureText: _obscureNew,
                      textInputAction: TextInputAction.next,
                      style: GoogleFonts.dmSans(fontSize: 14, color: CartzyColors.navy),
                      decoration: _buildInputDecoration(
                        hintText: 'Enter new password',
                        prefixIcon: Icons.key_rounded,
                        isObscured: _obscureNew,
                        onToggleVisibility: () => setState(() => _obscureNew = !_obscureNew),
                      ),
                    ),

                    const SizedBox(height: 12),

                    // Checklist
                    _buildPasswordChecklist(),

                    const SizedBox(height: 20),

                    // CONFIRM PASSWORD
                    _buildFieldLabel('Confirm New Password'),
                    const SizedBox(height: 8),
                    TextField(
                      controller: _confirmPasswordController,
                      obscureText: _obscureConfirm,
                      textInputAction: TextInputAction.done,
                      onSubmitted: (_) => _submitChangePassword(),
                      style: GoogleFonts.dmSans(fontSize: 14, color: CartzyColors.navy),
                      decoration: _buildInputDecoration(
                        hintText: 'Re-enter new password',
                        prefixIcon: Icons.check_circle_outline_rounded,
                        isObscured: _obscureConfirm,
                        onToggleVisibility: () => setState(() => _obscureConfirm = !_obscureConfirm),
                      ),
                    ),

                    // Password match indicator
                    if (_confirmPasswordController.text.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Icon(
                            _newPasswordController.text == _confirmPasswordController.text
                                ? Icons.check_circle_rounded
                                : Icons.cancel_rounded,
                            size: 16,
                            color: _newPasswordController.text == _confirmPasswordController.text
                                ? const Color(0xFF0E9F6E)
                                : const Color(0xFFE02424),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            _newPasswordController.text == _confirmPasswordController.text
                                ? 'Passwords match'
                                : 'Passwords do not match',
                            style: GoogleFonts.dmSans(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: _newPasswordController.text == _confirmPasswordController.text
                                  ? const Color(0xFF0E9F6E)
                                  : const Color(0xFFE02424),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),

              const SizedBox(height: 28),

              // Action button
              SizedBox(
                height: 52,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CartzyColors.navy,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                    disabledBackgroundColor: CartzyColors.navy.withValues(alpha: 0.6),
                  ),
                  onPressed: _isLoading ? null : _submitChangePassword,
                  child: _isLoading
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(
                            strokeWidth: 2.5,
                            color: Colors.white,
                          ),
                        )
                      : Text(
                          'Update Password',
                          style: GoogleFonts.dmSans(
                            fontSize: 15,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 0.3,
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

  Widget _buildFieldLabel(String text) {
    return Text(
      text,
      style: GoogleFonts.dmSans(
        fontSize: 13,
        fontWeight: FontWeight.w600,
        color: CartzyColors.navy,
      ),
    );
  }

  InputDecoration _buildInputDecoration({
    required String hintText,
    required IconData prefixIcon,
    required bool isObscured,
    required VoidCallback onToggleVisibility,
  }) {
    return InputDecoration(
      hintText: hintText,
      hintStyle: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF9E9E9E)),
      filled: true,
      fillColor: const Color(0xFFF9F7F8),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      prefixIcon: Icon(prefixIcon, color: const Color(0xFF7A6E72), size: 20),
      suffixIcon: IconButton(
        icon: Icon(
          isObscured ? Icons.visibility_off_outlined : Icons.visibility_outlined,
          color: const Color(0xFF7A6E72),
          size: 20,
        ),
        onPressed: onToggleVisibility,
      ),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFEDE4E7)),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFEDE4E7)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: CartzyColors.navy, width: 1.5),
      ),
    );
  }

  Widget _buildPasswordChecklist() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFF9F7F8),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Password requirements:',
            style: GoogleFonts.dmSans(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF554D50),
            ),
          ),
          const SizedBox(height: 6),
          _buildCheckItem('At least 8 characters', _hasMinLength),
          _buildCheckItem('At least one uppercase letter (A-Z)', _hasUppercase),
          _buildCheckItem('At least one lowercase letter (a-z)', _hasLowercase),
          _buildCheckItem('At least one number (0-9)', _hasNumber),
          _buildCheckItem('At least one special character (!@#\$%^&*)', _hasSpecialChar),
        ],
      ),
    );
  }

  Widget _buildCheckItem(String label, bool passed) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        children: [
          Icon(
            passed ? Icons.check_circle_rounded : Icons.radio_button_unchecked_rounded,
            size: 14,
            color: passed ? const Color(0xFF0E9F6E) : const Color(0xFFA0989B),
          ),
          const SizedBox(width: 6),
          Expanded(
            child: Text(
              label,
              style: GoogleFonts.dmSans(
                fontSize: 11,
                fontWeight: passed ? FontWeight.w600 : FontWeight.w400,
                color: passed ? const Color(0xFF0E9F6E) : const Color(0xFF7A6E72),
              ),
            ),
          ),
        ],
      ),
    );
  }
}


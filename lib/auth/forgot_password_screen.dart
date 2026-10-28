import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/auth/buyer_registration_validation.dart';
import 'package:cartzy/services/api_service.dart' show ApiException;
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';

/// Forgot password for buyers and couriers: email → 6-digit code → new
/// password. Uses the same reset code as the website.
class ForgotPasswordScreen extends StatefulWidget {
  /// 'buyer' or 'rider', matching the login screen the user came from.
  final String role;
  final String initialEmail;

  const ForgotPasswordScreen({
    super.key,
    required this.role,
    this.initialEmail = '',
  });

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

enum _ResetStep { email, code, password }

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  /// Matches how long the server keeps a reset code.
  static const _codeLifetimeSeconds = 15 * 60;

  static const _ink = Color(0xFF231F20);
  static const _muted = Color(0xFF7A6E72);
  static const _brand = Color(0xFF865561);
  static const _focus = Color(0xFFC66B5E);
  static const _border = Color(0xFFE5D7D7);
  static const _error = Color(0xFFD32F2F);

  late final TextEditingController _emailController =
      TextEditingController(text: widget.initialEmail.trim());
  final _otpController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmController = TextEditingController();

  _ResetStep _step = _ResetStep.email;
  bool _isBusy = false;
  bool _isResending = false;
  bool _obscurePassword = true;
  bool _obscureConfirm = true;

  String? _emailError;
  String? _otpError;
  String? _passwordError;
  String? _confirmError;

  Timer? _countdownTimer;
  int _secondsRemaining = _codeLifetimeSeconds;

  String get _email => _emailController.text.trim().toLowerCase();

  @override
  void dispose() {
    _countdownTimer?.cancel();
    _emailController.dispose();
    _otpController.dispose();
    _passwordController.dispose();
    _confirmController.dispose();
    super.dispose();
  }

  void _startTimer() {
    _countdownTimer?.cancel();
    _secondsRemaining = _codeLifetimeSeconds;
    _countdownTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) {
        timer.cancel();
        return;
      }
      setState(() {
        if (_secondsRemaining > 0) {
          _secondsRemaining--;
        } else {
          timer.cancel();
        }
      });
    });
  }

  String _formatTimer() {
    final mins = (_secondsRemaining ~/ 60).toString().padLeft(2, '0');
    final secs = (_secondsRemaining % 60).toString().padLeft(2, '0');
    return '$mins:$secs';
  }

  /// Runs [request], showing server and connection errors through [onError].
  Future<bool> _call(Future<void> Function() request, void Function(String) onError) async {
    try {
      await request();
      return true;
    } on ApiException catch (e) {
      if (mounted) setState(() => onError(e.message));
    } catch (_) {
      if (mounted) {
        setState(() => onError("Couldn't reach the server. Check your connection and try again."));
      }
    }
    return false;
  }

  Future<void> _sendCode() async {
    final emailError = BuyerRegistrationValidation.email(_email);
    setState(() => _emailError = _email.isEmpty ? 'Email is required' : emailError);
    if (_emailError != null) return;

    setState(() => _isBusy = true);
    final ok = await _call(
      () => AuthService.requestPasswordReset(email: _email, role: widget.role),
      (message) => _emailError = message,
    );
    if (!mounted) return;
    setState(() {
      _isBusy = false;
      if (ok) {
        _step = _ResetStep.code;
        _otpController.clear();
        _otpError = null;
      }
    });
    if (ok) {
      _startTimer();
      showCartzyFlash(context, 'A 6-digit code was sent to $_email');
    }
  }

  Future<void> _resendCode() async {
    if (_isResending) return;
    setState(() {
      _isResending = true;
      _otpError = null;
    });
    final ok = await _call(
      () => AuthService.requestPasswordReset(email: _email, role: widget.role),
      (message) => _otpError = message,
    );
    if (!mounted) return;
    setState(() => _isResending = false);
    if (ok) {
      _startTimer();
      _otpController.clear();
      showCartzyFlash(context, 'A new code was sent to $_email');
    }
  }

  Future<void> _verifyCode() async {
    final otp = _otpController.text.trim();
    if (_secondsRemaining == 0) {
      setState(() => _otpError = 'This code has expired. Please request a new one.');
      return;
    }
    if (otp.length != 6) {
      setState(() => _otpError = 'Enter the 6-digit code from your email.');
      return;
    }

    setState(() {
      _otpError = null;
      _isBusy = true;
    });
    final ok = await _call(
      () => AuthService.verifyPasswordResetCode(email: _email, otp: otp),
      (message) => _otpError = message,
    );
    if (!mounted) return;
    setState(() {
      _isBusy = false;
      if (ok) _step = _ResetStep.password;
    });
  }

  Future<void> _resetPassword() async {
    final password = _passwordController.text;
    setState(() {
      _passwordError = password.isEmpty
          ? 'Please enter a new password.'
          : BuyerRegistrationValidation.password(password);
      _confirmError = _confirmController.text != password ? 'Passwords do not match.' : null;
    });
    if (_passwordError != null || _confirmError != null) return;

    setState(() => _isBusy = true);
    final ok = await _call(
      () => AuthService.resetPassword(
        email: _email,
        otp: _otpController.text.trim(),
        password: password,
      ),
      (message) => _passwordError = message,
    );
    if (!mounted) return;
    setState(() => _isBusy = false);
    if (ok) {
      _countdownTimer?.cancel();
      showCartzyFlash(context, 'Password reset! Log in with your new password.');
      Navigator.of(context).pop(_email);
    }
  }

  void _handleBack() {
    if (_isBusy) return;
    switch (_step) {
      case _ResetStep.email:
        Navigator.of(context).pop();
      case _ResetStep.code:
        _countdownTimer?.cancel();
        setState(() => _step = _ResetStep.email);
      case _ResetStep.password:
        setState(() => _step = _ResetStep.code);
    }
  }

  @override
  Widget build(BuildContext context) {
    final (icon, title, subtitle) = switch (_step) {
      _ResetStep.email => (
          Icons.lock_reset_rounded,
          'Forgot password?',
          "Enter the email you use for your ${widget.role == 'rider' ? 'courier' : 'buyer'} account and we'll send you a reset code.",
        ),
      _ResetStep.code => (
          Icons.mark_email_read_outlined,
          'Check your email',
          'We sent a 6-digit code to\n$_email',
        ),
      _ResetStep.password => (
          Icons.password_rounded,
          'Set a new password',
          'Use at least 8 characters. You will use this on both the app and the website.',
        ),
    };

    return PopScope(
      canPop: _step == _ResetStep.email && !_isBusy,
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop) _handleBack();
      },
      child: Scaffold(
        backgroundColor: Colors.white,
        body: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 460),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    InkWell(
                      onTap: _handleBack,
                      borderRadius: BorderRadius.circular(6),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 2),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.arrow_back_rounded, size: 18, color: _brand),
                            const SizedBox(width: 5),
                            Text(
                              _step == _ResetStep.email ? 'Back to login' : 'Back',
                              style: GoogleFonts.dmSans(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: _brand,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 28),
                    Center(
                      child: Container(
                        width: 72,
                        height: 72,
                        decoration: BoxDecoration(
                          color: _brand.withValues(alpha: 0.1),
                          shape: BoxShape.circle,
                        ),
                        child: Icon(icon, color: _brand, size: 36),
                      ),
                    ),
                    const SizedBox(height: 18),
                    Center(
                      child: Text(
                        title,
                        textAlign: TextAlign.center,
                        style: GoogleFonts.playfairDisplay(
                          fontSize: 26,
                          fontWeight: FontWeight.w700,
                          color: _ink,
                          letterSpacing: -0.3,
                        ),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Center(
                      child: Text(
                        subtitle,
                        textAlign: TextAlign.center,
                        style: GoogleFonts.dmSans(fontSize: 13, height: 1.45, color: _muted),
                      ),
                    ),
                    const SizedBox(height: 24),
                    ...switch (_step) {
                      _ResetStep.email => _emailStep(),
                      _ResetStep.code => _codeStep(),
                      _ResetStep.password => _passwordStep(),
                    },
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  List<Widget> _emailStep() => [
        _label('Email address'),
        _field(
          controller: _emailController,
          hint: 'Enter your email address',
          icon: Icons.mail_outline,
          error: _emailError,
          keyboardType: TextInputType.emailAddress,
          onChanged: () => _emailError = null,
          onSubmitted: _sendCode,
        ),
        const SizedBox(height: 22),
        _primaryButton('Send Reset Code', _sendCode),
      ];

  List<Widget> _codeStep() {
    final expired = _secondsRemaining == 0;
    return [
      Center(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
          decoration: BoxDecoration(
            color: (expired ? _error : _brand).withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(6),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.timer_outlined, size: 13, color: expired ? _error : _brand),
              const SizedBox(width: 4),
              Text(
                expired ? 'Code expired' : 'Code expires in ${_formatTimer()}',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: expired ? _error : _brand,
                ),
              ),
            ],
          ),
        ),
      ),
      const SizedBox(height: 18),
      _field(
        controller: _otpController,
        hint: '• • • • • •',
        error: _otpError,
        keyboardType: TextInputType.number,
        inputFormatters: [
          FilteringTextInputFormatter.digitsOnly,
          LengthLimitingTextInputFormatter(6),
        ],
        autofillHints: const [AutofillHints.oneTimeCode],
        isCode: true,
        onChanged: () => _otpError = null,
        onSubmitted: _verifyCode,
      ),
      const SizedBox(height: 22),
      _primaryButton('Verify Code', _verifyCode),
      const SizedBox(height: 10),
      Wrap(
        alignment: WrapAlignment.center,
        crossAxisAlignment: WrapCrossAlignment.center,
        children: [
          Text("Didn't get the code? ",
              style: GoogleFonts.dmSans(fontSize: 13, color: _muted)),
          TextButton(
            onPressed: _isResending || _isBusy ? null : _resendCode,
            child: Text(
              _isResending ? 'Sending...' : 'Resend code',
              style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w700, color: _brand),
            ),
          ),
        ],
      ),
      Center(
        child: Text(
          "Can't find it? Check your spam folder.",
          style: GoogleFonts.dmSans(fontSize: 12, color: _muted),
        ),
      ),
    ];
  }

  List<Widget> _passwordStep() => [
        _label('New password'),
        _field(
          controller: _passwordController,
          hint: 'At least 8 characters',
          icon: Icons.lock_outline,
          error: _passwordError,
          obscure: _obscurePassword,
          onToggleObscure: () => setState(() => _obscurePassword = !_obscurePassword),
          onChanged: () => _passwordError = null,
        ),
        const SizedBox(height: 14),
        _label('Confirm new password'),
        _field(
          controller: _confirmController,
          hint: 'Re-enter your new password',
          icon: Icons.lock_outline,
          error: _confirmError,
          obscure: _obscureConfirm,
          onToggleObscure: () => setState(() => _obscureConfirm = !_obscureConfirm),
          onChanged: () => _confirmError = null,
          onSubmitted: _resetPassword,
        ),
        const SizedBox(height: 22),
        _primaryButton('Reset Password', _resetPassword),
      ];

  Widget _label(String text) => Padding(
        padding: const EdgeInsets.only(bottom: 5),
        child: Text(
          text,
          style: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w600, color: _ink),
        ),
      );

  /// Same bordered input as the login screen.
  Widget _field({
    required TextEditingController controller,
    required String hint,
    required String? error,
    required VoidCallback onChanged,
    IconData? icon,
    TextInputType? keyboardType,
    List<TextInputFormatter>? inputFormatters,
    Iterable<String>? autofillHints,
    bool obscure = false,
    VoidCallback? onToggleObscure,
    VoidCallback? onSubmitted,
    bool isCode = false,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TextField(
          controller: controller,
          keyboardType: keyboardType,
          inputFormatters: inputFormatters,
          autofillHints: autofillHints,
          obscureText: obscure,
          enabled: !_isBusy,
          textAlign: isCode ? TextAlign.center : TextAlign.start,
          textAlignVertical: TextAlignVertical.center,
          onChanged: (_) {
            if (error != null) setState(onChanged);
          },
          onSubmitted: onSubmitted == null ? null : (_) => onSubmitted(),
          style: isCode
              ? GoogleFonts.dmSans(fontSize: 22, fontWeight: FontWeight.w700, letterSpacing: 8, color: _ink)
              : GoogleFonts.dmSans(fontSize: 14, color: _ink),
          decoration: InputDecoration(
            isDense: true,
            hintText: hint,
            hintStyle: GoogleFonts.dmSans(
              fontSize: isCode ? 20 : 14,
              letterSpacing: isCode ? 6 : null,
              color: const Color(0xFFAAA0A2),
            ),
            prefixIcon: icon == null ? null : Icon(icon, size: 21, color: _brand),
            prefixIconConstraints: const BoxConstraints(minWidth: 48, minHeight: 52),
            suffixIcon: onToggleObscure == null
                ? null
                : IconButton(
                    icon: Icon(
                      obscure ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                      size: 21,
                      color: _muted,
                    ),
                    onPressed: onToggleObscure,
                  ),
            suffixIconConstraints: const BoxConstraints(minWidth: 48, minHeight: 52),
            filled: true,
            fillColor: Colors.white,
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: BorderSide(color: error != null ? _error : _border),
            ),
            disabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: const BorderSide(color: _border),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: BorderSide(color: error != null ? _error : _focus, width: 1.4),
            ),
          ),
        ),
        if (error != null) ...[
          const SizedBox(height: 4),
          Text(error, style: const TextStyle(fontSize: 14, color: _error)),
        ],
      ],
    );
  }

  /// Same gradient button as "Sign In to Account".
  Widget _primaryButton(String label, VoidCallback onTap) {
    return GestureDetector(
      onTap: _isBusy ? null : onTap,
      child: Container(
        width: double.infinity,
        constraints: const BoxConstraints(minHeight: 50),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFF4A3E4D), _brand],
            begin: Alignment.centerLeft,
            end: Alignment.centerRight,
          ),
          borderRadius: BorderRadius.circular(10),
          boxShadow: [
            BoxShadow(
              color: _brand.withValues(alpha: 0.25),
              offset: const Offset(0, 4),
              blurRadius: 10,
            ),
          ],
        ),
        alignment: Alignment.center,
        child: _isBusy
            ? const SizedBox(
                height: 20,
                width: 20,
                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
              )
            : Text(
                label,
                style: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w600, color: Colors.white),
              ),
      ),
    );
  }
}

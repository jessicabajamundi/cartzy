import 'dart:async';
import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/user_account_service.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';

class EmailVerificationScreen extends StatefulWidget {
  final String email;
  final ValueChanged<Map<String, dynamic>> onVerificationSuccess;
  final VoidCallback onBackToLogin;

  const EmailVerificationScreen({
    super.key,
    required this.email,
    required this.onVerificationSuccess,
    required this.onBackToLogin,
  });

  @override
  State<EmailVerificationScreen> createState() => _EmailVerificationScreenState();
}

class _EmailVerificationScreenState extends State<EmailVerificationScreen> {
  final TextEditingController _otpController = TextEditingController();
  bool _isSubmitting = false;
  String? _errorMessage;
  bool _simulateExpired = false;

  Timer? _countdownTimer;
  int _secondsRemaining = 300; // 5 minutes

  @override
  void initState() {
    super.initState();
    _startTimer();
  }

  void _startTimer() {
    _countdownTimer?.cancel();
    _secondsRemaining = 300;
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
          _errorMessage = 'Verification code has expired. Please request a new code.';
        }
      });
    });
  }

  String _formatTimer() {
    final mins = (_secondsRemaining ~/ 60).toString().padLeft(2, '0');
    final secs = (_secondsRemaining % 60).toString().padLeft(2, '0');
    return '$mins:$secs';
  }

  @override
  void dispose() {
    _countdownTimer?.cancel();
    _otpController.dispose();
    super.dispose();
  }

  Future<void> _handleVerify() async {
    setState(() {
      _errorMessage = null;
      _isSubmitting = true;
    });

    final entered = _otpController.text.trim();

    // Verify OTP using UserAccountService
    final error = UserAccountService.instance.verifyOtp(
      email: widget.email,
      enteredOtp: entered,
      simulateExpired: _simulateExpired,
    );

    if (error != null) {
      setState(() {
        _errorMessage = error;
        _isSubmitting = false;
      });
      return;
    }

    // Success (TC-BUY-023)
    final account = UserAccountService.instance.findAccountByEmail(widget.email);
    showCartzyFlash(context, 'Email verified successfully! Welcome to Cartzy.');

    await Future.delayed(const Duration(milliseconds: 700));
    if (!mounted) return;
    setState(() => _isSubmitting = false);

    if (account != null) {
      widget.onVerificationSuccess(account.toMap());
    } else {
      widget.onBackToLogin();
    }
  }

  void _handleResendOtp() {
    final newCode = UserAccountService.instance.resendOtp(widget.email);
    _startTimer();
    setState(() {
      _simulateExpired = false;
      _errorMessage = null;
      _otpController.clear();
    });

    showCartzyFlash(context, 'New OTP ($newCode) sent to ${widget.email}!');
  }

  @override
  Widget build(BuildContext context) {
    final account = UserAccountService.instance.findAccountByEmail(widget.email);
    final currentOtp = account?.otp ?? '482913';

    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
          onPressed: widget.onBackToLogin,
        ),
        title: const Text(
          'Email Verification',
          style: TextStyle(
            color: CartzyColors.navy,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 28),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Icon Header
              Center(
                child: Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: CartzyColors.brandPrimary.withValues(alpha: 0.2),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.mark_email_read_outlined,
                    color: CartzyColors.brandDark,
                    size: 42,
                  ),
                ),
              ),
              const SizedBox(height: 24),

              const Text(
                'Verify Your Email',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: CartzyColors.brandDarkest,
                ),
              ),
              const SizedBox(height: 10),

              Text(
                'We have sent a 6-digit verification code to:\n${widget.email}',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 14,
                  color: CartzyColors.gray,
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 24),

              // ============================================================
              // MAIL CATCHER BANNER (for automated/manual testing)
              // ============================================================
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: CartzyColors.brandLight,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: CartzyColors.brandBorder),
                ),
                child: Column(
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.mail_outline, size: 20, color: CartzyColors.brandDark),
                        const SizedBox(width: 8),
                        const Expanded(
                          child: Text(
                            'Mail Catcher (Testing Simulator)',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: CartzyColors.brandDark,
                            ),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: _secondsRemaining > 0
                                ? CartzyColors.brandDark.withValues(alpha: 0.1)
                                : CartzyColors.error.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.timer_outlined,
                                size: 12,
                                color: _secondsRemaining > 0 ? CartzyColors.brandDark : CartzyColors.error,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                _secondsRemaining > 0 ? 'Expires in ${_formatTimer()}' : 'Expired',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: _secondsRemaining > 0 ? CartzyColors.brandDark : CartzyColors.error,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Latest OTP Code:',
                          style: TextStyle(fontSize: 13, color: CartzyColors.gray),
                        ),
                        GestureDetector(
                          onTap: () {
                            _otpController.text = currentOtp;
                            setState(() => _errorMessage = null);
                          },
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: CartzyColors.surface,
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(color: CartzyColors.brandBorder),
                            ),
                            child: Row(
                              children: [
                                Text(
                                  currentOtp,
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: 2,
                                    color: CartzyColors.brandDark,
                                  ),
                                ),
                                const SizedBox(width: 6),
                                const Icon(Icons.copy, size: 14, color: CartzyColors.gray),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),

              // OTP Input Field
              TextField(
                controller: _otpController,
                keyboardType: TextInputType.text,
                maxLength: 7, // allows entering test inputs of 5, 6, 7 chars
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 8,
                  color: CartzyColors.brandDarkest,
                ),
                decoration: InputDecoration(
                  counterText: '',
                  hintText: '• • • • • •',
                  hintStyle: const TextStyle(
                    fontSize: 20,
                    letterSpacing: 6,
                    color: CartzyColors.brandPrimary,
                  ),
                  filled: true,
                  fillColor: CartzyColors.surface,
                  contentPadding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(
                      color: _errorMessage != null ? CartzyColors.error : CartzyColors.border,
                      width: 1.2,
                    ),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(
                      color: _errorMessage != null ? CartzyColors.error : CartzyColors.navy,
                      width: 1.8,
                    ),
                  ),
                ),
                onChanged: (_) {
                  if (_errorMessage != null) {
                    setState(() => _errorMessage = null);
                  }
                },
              ),

              if (_errorMessage != null) ...[
                const SizedBox(height: 10),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: CartzyColors.error.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: CartzyColors.error.withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.error_outline, color: CartzyColors.error, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          _errorMessage!,
                          style: const TextStyle(
                            color: CartzyColors.error,
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 28),

              // Verify Button
              SizedBox(
                height: 52,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _handleVerify,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CartzyColors.brandDark,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: const Text(
                    'VERIFY',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Resend OTP action
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text(
                    "Didn't receive the code? ",
                    style: TextStyle(fontSize: 13, color: CartzyColors.gray),
                  ),
                  TextButton(
                    onPressed: _handleResendOtp,
                    child: const Text(
                      'Resend OTP',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: CartzyColors.brandDark,
                      ),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 24),
              const Divider(color: CartzyColors.border),
              const SizedBox(height: 12),

              // Test helper: Simulate expired OTP for TC-BUY-026
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Checkbox(
                    value: _simulateExpired,
                    activeColor: CartzyColors.brandDark,
                    onChanged: (val) {
                      setState(() {
                        _simulateExpired = val ?? false;
                        if (_simulateExpired) {
                          UserAccountService.instance.expireCurrentOtp(widget.email);
                        }
                      });
                    },
                  ),
                  GestureDetector(
                    onTap: () {
                      setState(() {
                        _simulateExpired = !_simulateExpired;
                        if (_simulateExpired) {
                          UserAccountService.instance.expireCurrentOtp(widget.email);
                        }
                      });
                    },
                    child: const Text(
                      'Simulate expired code (> 5 minutes)',
                      style: TextStyle(fontSize: 12, color: CartzyColors.gray),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

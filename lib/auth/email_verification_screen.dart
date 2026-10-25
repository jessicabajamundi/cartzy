import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/api_service.dart' show ApiException;
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';

/// Second half of buyer sign-up: the server has emailed a 6-digit code, and
/// entering it here creates the account and signs the buyer in.
class EmailVerificationScreen extends StatefulWidget {
  final String email;

  /// The registration form's details (see [AuthService.registerBuyer]).
  final Map<String, dynamic>? registration;
  final ValueChanged<Map<String, dynamic>> onVerificationSuccess;
  final VoidCallback onBackToLogin;

  const EmailVerificationScreen({
    super.key,
    required this.email,
    this.registration,
    required this.onVerificationSuccess,
    required this.onBackToLogin,
  });

  @override
  State<EmailVerificationScreen> createState() => _EmailVerificationScreenState();
}

class _EmailVerificationScreenState extends State<EmailVerificationScreen> {
  /// Matches how long the server keeps a code.
  static const _codeLifetimeSeconds = 600;

  final TextEditingController _otpController = TextEditingController();
  bool _isSubmitting = false;
  bool _isResending = false;
  String? _errorMessage;

  Timer? _countdownTimer;
  int _secondsRemaining = _codeLifetimeSeconds;

  @override
  void initState() {
    super.initState();
    _startTimer();
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
    final registration = widget.registration;
    if (registration == null) {
      setState(() => _errorMessage = 'Your sign-up details were lost. Please register again.');
      return;
    }

    final entered = _otpController.text.trim();
    if (entered.length != 6) {
      setState(() => _errorMessage = 'Enter the 6-digit code from your email.');
      return;
    }

    setState(() {
      _errorMessage = null;
      _isSubmitting = true;
    });

    try {
      final response = await AuthService.registerBuyer(registration, otp: entered);
      if (!mounted) return;
      _countdownTimer?.cancel();
      showCartzyFlash(context, 'Email verified successfully! Welcome to Cartzy.');
      widget.onVerificationSuccess(
        Map<String, dynamic>.from(response['user'] as Map? ?? const {}),
      );
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _isSubmitting = false;
        _errorMessage = e.message;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _isSubmitting = false;
        _errorMessage = "Couldn't reach the server. Check your connection and try again.";
      });
    }
  }

  Future<void> _handleResendOtp() async {
    final registration = widget.registration;
    if (registration == null || _isResending) return;

    setState(() {
      _isResending = true;
      _errorMessage = null;
    });

    try {
      await AuthService.requestRegistrationCode(
        email: widget.email,
        name: '${registration['first_name']} ${registration['last_name']}',
        phone: registration['phone']?.toString() ?? '',
      );
      if (!mounted) return;
      _startTimer();
      _otpController.clear();
      showCartzyFlash(context, 'A new code was sent to ${widget.email}');
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() => _errorMessage = e.message);
    } catch (_) {
      if (!mounted) return;
      setState(() => _errorMessage = "Couldn't reach the server. Check your connection and try again.");
    } finally {
      if (mounted) setState(() => _isResending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
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
              const SizedBox(height: 6),
              const Text(
                "Can't find it? Check your spam folder.",
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 12, color: CartzyColors.gray),
              ),
              const SizedBox(height: 16),

              // Code expiry
              Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
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
                        size: 13,
                        color: _secondsRemaining > 0 ? CartzyColors.brandDark : CartzyColors.error,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        _secondsRemaining > 0 ? 'Code expires in ${_formatTimer()}' : 'Code expired',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: _secondsRemaining > 0 ? CartzyColors.brandDark : CartzyColors.error,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // OTP Input Field
              TextField(
                controller: _otpController,
                keyboardType: TextInputType.number,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                maxLength: 6,
                autofillHints: const [AutofillHints.oneTimeCode],
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
                  child: _isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text(
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
              Wrap(
                alignment: WrapAlignment.center,
                crossAxisAlignment: WrapCrossAlignment.center,
                children: [
                  const Text(
                    "Didn't receive the code? ",
                    style: TextStyle(fontSize: 13, color: CartzyColors.gray),
                  ),
                  TextButton(
                    onPressed: _isResending ? null : _handleResendOtp,
                    child: Text(
                      _isResending ? 'Sending...' : 'Resend code',
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: CartzyColors.brandDark,
                      ),
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

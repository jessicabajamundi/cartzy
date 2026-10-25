import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/google_sign_in_service.dart';
import 'package:cartzy/widgets/cartzy_logo.dart';

enum AccountType { buyer, rider, admin }

class LoginScreen extends StatefulWidget {
  final AccountType initialAccountType;
  final void Function(
    AccountType accountType,
    Map<String, dynamic> user, {
    String? token,
  }) onLogin;
  final ValueChanged<AccountType> onSignUp;
  final ValueChanged<String>? onVerifyEmail;

  /// Back arrow â€” goes back to the role-selection screen or shop.
  final VoidCallback onBack;

  /// "Continue as Guest" / "Continue to the shop".
  final VoidCallback onBackToGuest;

  const LoginScreen({
    super.key,
    required this.initialAccountType,
    required this.onLogin,
    required this.onSignUp,
    this.onVerifyEmail,
    required this.onBack,
    required this.onBackToGuest,
  });

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _emailHasFocus = false;
  bool _passwordHasFocus = false;

  late AccountType _accountType;

  bool _obscurePassword = true;
  bool _rememberMe = false;
  bool _isLoggingIn = false;
  int _loginAttempts = 0;
  static const int _maxAttempts = 5;

  String? _emailError;
  String? _passwordError;

  @override
  void initState() {
    super.initState();
    _accountType = widget.initialAccountType;
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  bool _validateLogin() {
    bool valid = true;

    setState(() {
      _emailError = null;
      _passwordError = null;
    });

    if (_emailController.text.trim().isEmpty) {
      setState(() => _emailError = 'Email is required');
      valid = false;
    } else if (!_emailController.text.trim().contains('@')) {
      setState(() => _emailError = 'Enter a valid email address');
      valid = false;
    }

    if (_passwordController.text.isEmpty) {
      setState(() => _passwordError = 'Password is required');
      valid = false;
    }

    return valid;
  }

  Future<void> _handleLogin() async {
    if (!_validateLogin()) return;

    // TC: Account lockout after 5 failed attempts
    if (_loginAttempts >= _maxAttempts) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Account temporarily locked after 5 failed attempts. Please try again later.'),
          backgroundColor: Colors.red,
          duration: Duration(seconds: 4),
        ),
      );
      return;
    }

    setState(() => _isLoggingIn = true);

    final email = _emailController.text.trim().toLowerCase();
    final password = _passwordController.text;

    // Real accounts: the same users table as the website, via the Laravel API.
    try {
      final response = await AuthService.login(email: email, password: password, role: _selectedRole);
      if (!mounted) return;
      setState(() {
        _isLoggingIn = false;
        _loginAttempts = 0;
      });
      final user = Map<String, dynamic>.from(response['user'] as Map);
      final token = response['token']?.toString();
      widget.onLogin(user['role'] == 'rider' ? AccountType.rider : AccountType.buyer, user, token: token);
      return;
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() => _isLoggingIn = false);
      if (e.statusCode == 403 && e.body['account_role'] != null) {
        _showRoleMismatch();
        return;
      }
      if (e.statusCode == 422 || e.statusCode == 403) {
        setState(() => _loginAttempts++);
        final remaining = _maxAttempts - _loginAttempts;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.statusCode == 403
                ? e.message
                : remaining > 0
                    ? 'Invalid credentials. $remaining attempt(s) left before lockout.'
                    : 'Account locked. Too many failed attempts.'),
            backgroundColor: Colors.redAccent,
            duration: const Duration(seconds: 3),
          ),
        );
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.message), backgroundColor: Colors.redAccent),
      );
      return;
    } catch (_) {
      if (!mounted) return;
      setState(() => _isLoggingIn = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text("Can't reach the Cartzy server. Check your internet connection and try again."),
          backgroundColor: Colors.redAccent,
        ),
      );
      return;
    }
  }

  Future<void> _handleGoogleSignIn() async {
    setState(() => _isLoggingIn = true);
    try {
      final response = await GoogleSignInService.signIn();
      if (!mounted) return;
      setState(() => _isLoggingIn = false);
      if (response == null) {
        return;
      }

      final user = Map<String, dynamic>.from(response['user'] as Map);
      final token = response['token']?.toString();
      final userRole = (user['role'] == 'rider' || user['role'] == 'courier')
          ? AccountType.rider
          : AccountType.buyer;

      widget.onLogin(userRole, user, token: token);
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoggingIn = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Google Sign-In: ${e.toString()}'),
          backgroundColor: Colors.redAccent,
          duration: const Duration(seconds: 4),
        ),
      );
    }
  }

  String get _selectedRole => _accountType == AccountType.rider ? 'rider' : 'buyer';

  void _showRoleMismatch() {
    final selectedLabel = _accountType == AccountType.rider ? 'Courier' : 'Buyer';
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Please use a $selectedLabel account to sign in here.')),
    );
  }

  void _showForgotPasswordDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text('Forgot Password',
            style: GoogleFonts.dmSans(color: const Color(0xFF231F20), fontWeight: FontWeight.bold)),
        content: Text(
          'Your Cartzy account works on both the app and the website.\n\n'
          'To reset your password, open the Cartzy website, choose "Log in", then "Forgot password?". '
          'Use your new password here afterwards.',
          style: GoogleFonts.dmSans(fontSize: 13.5, height: 1.5, color: const Color(0xFF4A3E4D)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('Close', style: GoogleFonts.dmSans(color: const Color(0xFF7A6E72))),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
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
                  Align(
                    alignment: Alignment.centerLeft,
                    child: InkWell(
                      onTap: widget.onBackToGuest,
                      borderRadius: BorderRadius.circular(6),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 2),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(
                              Icons.arrow_back_rounded,
                              size: 18,
                              color: Color(0xFF865561),
                            ),
                            const SizedBox(width: 5),
                            Text(
                              'Continue to the shop',
                              style: GoogleFonts.dmSans(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: const Color(0xFF865561),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 32),
                  Center(
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const CartzyLogo(height: 40),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 5,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFF6B4B68),
                            borderRadius: BorderRadius.circular(18),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.16),
                                blurRadius: 5,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Text(
                            _accountType == AccountType.rider ? 'COURIER' : 'BUYER',
                            style: GoogleFonts.dmSans(
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              color: Colors.white,
                              letterSpacing: 0.7,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                  Center(
                    child: Text(
                      'Welcome back',
                      style: GoogleFonts.playfairDisplay(
                        fontSize: 28,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF231F20),
                        letterSpacing: -0.3,
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Center(
                    child: Text(
                      'Login to your account and continue shopping',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        color: const Color(0xFF7A6E72),
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),
                  if (_loginAttempts > 0)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        decoration: BoxDecoration(
                          color: _loginAttempts >= 4
                              ? CartzyColors.error.withValues(alpha: 0.08)
                              : Colors.orange.withValues(alpha: 0.08),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: _loginAttempts >= 4
                                ? CartzyColors.error.withValues(alpha: 0.4)
                                : Colors.orange.withValues(alpha: 0.4),
                          ),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              Icons.warning_amber_rounded,
                              size: 18,
                              color: _loginAttempts >= 4 ? CartzyColors.error : Colors.orange,
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                _loginAttempts >= _maxAttempts
                                    ? 'Account temporarily locked due to too many failed attempts.'
                                    : '${_maxAttempts - _loginAttempts} attempt(s) remaining before your account is locked.',
                                style: TextStyle(
                                  fontSize: 14,
                                  color: _loginAttempts >= 4
                                      ? CartzyColors.error
                                      : Colors.orange.shade800,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

              // â”€â”€ Email address â”€â”€
              Text(
                'Email address',
                style: GoogleFonts.dmSans(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF231F20),
                ),
              ),
              const SizedBox(height: 5),
              Focus(
                onFocusChange: (hasFocus) {
                  if (mounted) setState(() => _emailHasFocus = hasFocus);
                },
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: _emailError != null
                          ? const Color(0xFFD32F2F)
                          : _emailHasFocus == true
                              ? const Color(0xFFC66B5E)
                              : const Color(0xFFE5D7D7),
                      width: _emailHasFocus == true ? 1.4 : 1.0,
                    ),
                    boxShadow: _emailHasFocus == true
                        ? [
                            BoxShadow(
                              color: const Color(0xFFC66B5E).withValues(alpha: 0.18),
                              blurRadius: 5,
                              spreadRadius: 1,
                            ),
                          ]
                        : null,
                  ),
                  child: TextField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    textAlignVertical: TextAlignVertical.center,
                    onChanged: (_) {
                      if (_emailError != null) setState(() => _emailError = null);
                    },
                    style: GoogleFonts.dmSans(fontSize: 14, color: const Color(0xFF231F20)),
                    decoration: InputDecoration(
                      isDense: true,
                      hintText: 'Enter your email address',
                      hintStyle: GoogleFonts.dmSans(
                        fontSize: 14,
                        color: const Color(0xFFAAA0A2),
                      ),
                      prefixIcon: const Icon(
                        Icons.mail_outline,
                        size: 21,
                        color: Color(0xFF865561),
                      ),
                      prefixIconConstraints: const BoxConstraints(minWidth: 48, minHeight: 52),
                      border: InputBorder.none,
                      enabledBorder: InputBorder.none,
                      focusedBorder: InputBorder.none,
                      errorBorder: InputBorder.none,
                      disabledBorder: InputBorder.none,
                      filled: false,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
                    ),
                  ),
                ),
              ),
              if (_emailError != null) ...[
                const SizedBox(height: 4),
                Text(
                  _emailError!,
                  style: const TextStyle(fontSize: 14, color: Color(0xFFD32F2F)),
                ),
              ],
              const SizedBox(height: 14),

              // â”€â”€ Password â”€â”€
              Text(
                'Password',
                style: GoogleFonts.dmSans(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF231F20),
                ),
              ),
              const SizedBox(height: 5),
              Focus(
                onFocusChange: (hasFocus) {
                  if (mounted) setState(() => _passwordHasFocus = hasFocus);
                },
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: _passwordError != null
                          ? const Color(0xFFD32F2F)
                          : _passwordHasFocus == true
                              ? const Color(0xFFC66B5E)
                              : const Color(0xFFE5D7D7),
                      width: _passwordHasFocus == true ? 1.4 : 1.0,
                    ),
                    boxShadow: _passwordHasFocus == true
                        ? [
                            BoxShadow(
                              color: const Color(0xFFC66B5E).withValues(alpha: 0.18),
                              blurRadius: 5,
                              spreadRadius: 1,
                            ),
                          ]
                        : null,
                  ),
                  child: TextField(
                    controller: _passwordController,
                    obscureText: _obscurePassword,
                    textAlignVertical: TextAlignVertical.center,
                    onChanged: (_) {
                      if (_passwordError != null) setState(() => _passwordError = null);
                    },
                    style: GoogleFonts.dmSans(fontSize: 14, color: const Color(0xFF231F20)),
                    decoration: InputDecoration(
                      isDense: true,
                      hintText: 'Enter your password',
                      hintStyle: GoogleFonts.dmSans(
                        fontSize: 14,
                        color: const Color(0xFFAAA0A2),
                      ),
                      prefixIcon: const Icon(
                        Icons.lock_outline,
                        size: 21,
                        color: Color(0xFF865561),
                      ),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                          size: 21,
                          color: const Color(0xFF7A6E72),
                        ),
                        onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                      ),
                      prefixIconConstraints: const BoxConstraints(minWidth: 48, minHeight: 52),
                      suffixIconConstraints: const BoxConstraints(minWidth: 48, minHeight: 52),
                      border: InputBorder.none,
                      enabledBorder: InputBorder.none,
                      focusedBorder: InputBorder.none,
                      errorBorder: InputBorder.none,
                      disabledBorder: InputBorder.none,
                      filled: false,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 16),
                    ),
                  ),
                ),
              ),
              if (_passwordError != null) ...[
                const SizedBox(height: 4),
                Text(
                  _passwordError!,
                  style: const TextStyle(fontSize: 14, color: Color(0xFFD32F2F)),
                ),
              ],
              const SizedBox(height: 6),

              // â”€â”€ Remember me / Forgot password â”€â”€
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Flexible(
                    child: Row(
                    children: [
                      SizedBox(
                        width: 20,
                        height: 20,
                        child: Checkbox(
                          value: _rememberMe,
                          activeColor: const Color(0xFF865561),
                          side: const BorderSide(color: Color(0xFFD0C3C5), width: 1.2),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                          onChanged: (v) => setState(() => _rememberMe = v ?? false),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Flexible(
                        child: Text(
                          'Remember me',
                          style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF4A3E4D)),
                        ),
                      ),
                    ],
                  ),
                  ),
                  const SizedBox(width: 8),
                  GestureDetector(
                    onTap: _showForgotPasswordDialog,
                    child: Text(
                      'Forgot password?',
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFF865561),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),

              // â”€â”€ Sign In to Account button â”€â”€
              GestureDetector(
                onTap: _isLoggingIn ? null : _handleLogin,
                child: Container(
                  width: double.infinity,
                  constraints: const BoxConstraints(minHeight: 50),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
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
                        offset: const Offset(0, 4),
                        blurRadius: 10,
                      ),
                    ],
                  ),
                  alignment: Alignment.center,
                  child: _isLoggingIn
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : Text(
                          'Sign In to Account',
                          style: GoogleFonts.dmSans(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: Colors.white,
                          ),
                        ),
                ),
              ),
              const SizedBox(height: 16),

              // â”€â”€ Divider: or continue with â”€â”€
              Row(
                children: [
                  const Expanded(child: Divider(color: Color(0xFFE5D7D7), height: 1)),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 14),
                    child: Text(
                      'or continue with',
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        color: const Color(0xFF9E8F93),
                      ),
                    ),
                  ),
                  const Expanded(child: Divider(color: Color(0xFFE5D7D7), height: 1)),
                ],
              ),
              // â”€â”€ Continue with Google â”€â”€
              GestureDetector(
                onTap: _isLoggingIn ? null : _handleGoogleSignIn,
                child: Container(
                  width: double.infinity,
                  constraints: const BoxConstraints(minHeight: 50),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
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
                        'Continue with Google',
                        style: GoogleFonts.dmSans(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF231F20),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // â”€â”€ Don't have an account? Sign up â”€â”€
              Center(
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      "Don't have an account? ",
                      style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF7A6E72)),
                    ),
                    GestureDetector(
                      onTap: () => widget.onSignUp(_accountType),
                      child: Text(
                        'Sign up',
                        style: GoogleFonts.dmSans(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF865561),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              const Divider(height: 1, color: Color(0xFFEDE8EA)),
              const SizedBox(height: 14),
              Center(
                child: Text(
                  'Not shopping? Log in to your workspace',
                  style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF7A6E72)),
                ),
              ),
              const SizedBox(height: 5),
              Center(
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _workspaceLink('Buyer', AccountType.buyer),
                    const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 9),
                      child: Text('|', style: TextStyle(color: Color(0xFFB7AAAE), fontSize: 13)),
                    ),
                    _workspaceLink('Courier', AccountType.rider),
                  ],
                ),
              ),
              const SizedBox(height: 14),
            ],
          ),
        ),
      ),
        ),
      ),
    );
  }

  Widget _workspaceLink(String label, AccountType type) {
    final isSelected = _accountType == type;
    return GestureDetector(
      onTap: () => setState(() => _accountType = type),
      child: Text(
        label,
        style: GoogleFonts.dmSans(
          fontSize: 13,
          fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
          color: isSelected ? const Color(0xFF865561) : const Color(0xFF7A6E72),
          decoration: isSelected ? TextDecoration.underline : TextDecoration.none,
        ),
      ),
    );
  }
}

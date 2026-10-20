import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/user_account_service.dart';
import 'package:cartzy/widgets/cartzy_logo.dart';

enum AccountType { buyer, rider, admin }

// ============================================================
// DEMO ACCOUNTS (no database needed)
// ============================================================
const _demoAccounts = [
  {'email': 'buyer@cartzy.com',  'password': '123456', 'role': 'buyer',  'name': 'Demo Buyer',  'id': 1},
  {'email': 'rider@cartzy.com',  'password': '123456', 'role': 'rider',  'name': 'Demo Rider',  'id': 2},
  {'email': 'admin@cartzy.com',  'password': '123456', 'role': 'admin',  'name': 'Demo Admin',  'id': 3},
];

class LoginScreen extends StatefulWidget {
  final AccountType initialAccountType;
  final void Function(
    AccountType accountType,
    Map<String, dynamic> user, {
    String? token,
  }) onLogin;
  final ValueChanged<AccountType> onSignUp;
  final ValueChanged<String>? onVerifyEmail;

  /// Back arrow — goes back to the role-selection screen or shop.
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

    // Simulate a short delay for UX
    await Future.delayed(const Duration(milliseconds: 600));

    if (!mounted) return;

    final email = _emailController.text.trim().toLowerCase();
    final password = _passwordController.text;

    // Check with UserAccountService (TC-BUY-028 & TC-BUY-029)
    final authResult = UserAccountService.instance.authenticate(
      email: email,
      password: password,
      expectedRole: _selectedRole,
    );

    if (authResult.isUnverified) {
      setState(() => _isLoggingIn = false);
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Row(
            children: [
              Icon(Icons.mark_email_unread_outlined, color: CartzyColors.navy),
              SizedBox(width: 10),
              Expanded(
                child: Text('Email Verification Required', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
          content: Text(
            'The buyer account $email exists with status Unverified.\n\nPlease verify your email to access your account.',
            style: const TextStyle(fontSize: 14, height: 1.4),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Cancel', style: TextStyle(color: CartzyColors.gray)),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(ctx);
                if (widget.onVerifyEmail != null) {
                  widget.onVerifyEmail!(email);
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: CartzyColors.navy,
                foregroundColor: Colors.white,
              ),
              child: const Text('Verify Email Now'),
            ),
          ],
        ),
      );
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Account unverified. Please verify your email before logging in.'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    if (authResult.success && authResult.account != null) {
      final account = authResult.account!;
      final role = account.role;
      final user = account.toMap();

      if (role != _selectedRole) {
        setState(() => _isLoggingIn = false);
        _showRoleMismatch();
        return;
      }

      setState(() => _isLoggingIn = false);

      if (role == 'buyer') {
        widget.onLogin(AccountType.buyer, user, token: 'user-token');
      } else if (role == 'rider') {
        widget.onLogin(AccountType.rider, user, token: 'rider-token');
      } else {
        widget.onLogin(AccountType.admin, user, token: 'admin-token');
      }
      return;
    }

    // Match against demo accounts
    final match = _demoAccounts.where(
      (a) =>
          a['email'] == email &&
          a['password'] == password &&
          a['role'] == _selectedRole,
    ).firstOrNull;

    setState(() => _isLoggingIn = false);

    if (match == null) {
      setState(() => _loginAttempts++);
      final remaining = _maxAttempts - _loginAttempts;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            remaining > 0
                ? 'Invalid credentials. $remaining attempt(s) left before lockout.'
                : 'Account locked. Too many failed attempts.',
          ),
          backgroundColor: remaining > 0 ? Colors.redAccent : Colors.red.shade900,
          duration: const Duration(seconds: 3),
        ),
      );
      return;
    }

    // Successful login — reset attempt counter
    setState(() => _loginAttempts = 0);
    final role = match['role'].toString();
    final user = Map<String, dynamic>.from(match);

    if (role == 'buyer') {
      widget.onLogin(AccountType.buyer, user, token: 'demo-token');
    } else if (role == 'rider') {
      widget.onLogin(AccountType.rider, user, token: 'demo-rider-token');
    } else if (role == 'admin') {
      widget.onLogin(AccountType.admin, user, token: 'demo-admin-token');
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
          'Use the sample accounts to sign in:\n\nBuyer:  buyer@cartzy.com\nCourier:  rider@cartzy.com\nPassword: 123456',
          style: GoogleFonts.dmSans(fontSize: 13.5, height: 1.5, color: const Color(0xFF4A3E4D)),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              setState(() {
                _emailController.text = 'buyer@cartzy.com';
                _passwordController.text = '123456';
                _emailError = null;
                _passwordError = null;
              });
            },
            child: Text('Fill Buyer', style: GoogleFonts.dmSans(color: const Color(0xFF865561), fontWeight: FontWeight.bold)),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              setState(() {
                _emailController.text = 'rider@cartzy.com';
                _passwordController.text = '123456';
                _emailError = null;
                _passwordError = null;
              });
            },
            child: Text('Fill Rider', style: GoogleFonts.dmSans(color: const Color(0xFF865561), fontWeight: FontWeight.bold)),
          ),
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
          padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 10),
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
                        padding: const EdgeInsets.symmetric(vertical: 5, horizontal: 2),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(
                              Icons.arrow_back_rounded,
                              size: 14,
                              color: Color(0xFF865561),
                            ),
                            const SizedBox(width: 5),
                            Text(
                              'Continue to the shop',
                              style: GoogleFonts.dmSans(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: const Color(0xFF865561),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 58),
                  Center(
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const CartzyLogo(height: 30),
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
                              fontSize: 8,
                              fontWeight: FontWeight.w800,
                              color: Colors.white,
                              letterSpacing: 0.7,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 8),
                  Center(
                    child: Text(
                      'Welcome back',
                      style: GoogleFonts.playfairDisplay(
                        fontSize: 20,
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
                        fontSize: 10,
                        color: const Color(0xFF7A6E72),
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
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
                                  fontSize: 12,
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

              // ── Email address ──
              Text(
                'Email address',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
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
                    style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF231F20)),
                    decoration: InputDecoration(
                      isDense: true,
                      hintText: 'Enter your email address',
                      hintStyle: GoogleFonts.dmSans(
                        fontSize: 11.5,
                        color: const Color(0xFFAAA0A2),
                      ),
                      prefixIcon: const Icon(
                        Icons.mail_outline,
                        size: 17,
                        color: Color(0xFF865561),
                      ),
                      prefixIconConstraints: const BoxConstraints(minWidth: 38, minHeight: 38),
                      border: InputBorder.none,
                      enabledBorder: InputBorder.none,
                      focusedBorder: InputBorder.none,
                      errorBorder: InputBorder.none,
                      disabledBorder: InputBorder.none,
                      filled: false,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 9, vertical: 8),
                    ),
                  ),
                ),
              ),
              if (_emailError != null) ...[
                const SizedBox(height: 4),
                Text(
                  _emailError!,
                  style: const TextStyle(fontSize: 12, color: Color(0xFFD32F2F)),
                ),
              ],
              const SizedBox(height: 8),

              // ── Password ──
              Text(
                'Password',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
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
                    style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF231F20)),
                    decoration: InputDecoration(
                      isDense: true,
                      hintText: 'Enter your password',
                      hintStyle: GoogleFonts.dmSans(
                        fontSize: 11.5,
                        color: const Color(0xFFAAA0A2),
                      ),
                      prefixIcon: const Icon(
                        Icons.lock_outline,
                        size: 17,
                        color: Color(0xFF865561),
                      ),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                          size: 17,
                          color: const Color(0xFF7A6E72),
                        ),
                        onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                      ),
                      prefixIconConstraints: const BoxConstraints(minWidth: 38, minHeight: 38),
                      suffixIconConstraints: const BoxConstraints(minWidth: 38, minHeight: 38),
                      border: InputBorder.none,
                      enabledBorder: InputBorder.none,
                      focusedBorder: InputBorder.none,
                      errorBorder: InputBorder.none,
                      disabledBorder: InputBorder.none,
                      filled: false,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 9, vertical: 8),
                    ),
                  ),
                ),
              ),
              if (_passwordError != null) ...[
                const SizedBox(height: 4),
                Text(
                  _passwordError!,
                  style: const TextStyle(fontSize: 12, color: Color(0xFFD32F2F)),
                ),
              ],
              const SizedBox(height: 6),

              // ── Remember me / Forgot password ──
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
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
                      Text(
                        'Remember me',
                        style: GoogleFonts.dmSans(fontSize: 10, color: const Color(0xFF4A3E4D)),
                      ),
                    ],
                  ),
                  GestureDetector(
                    onTap: _showForgotPasswordDialog,
                    child: Text(
                      'Forgot password?',
                      style: GoogleFonts.dmSans(
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFF865561),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),

              // ── Sign In to Account button ──
              GestureDetector(
                onTap: _isLoggingIn ? null : _handleLogin,
                child: Container(
                  width: double.infinity,
                  height: 32,
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
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: Colors.white,
                          ),
                        ),
                ),
              ),
              const SizedBox(height: 10),

              // ── Divider: or continue with ──
              Row(
                children: [
                  const Expanded(child: Divider(color: Color(0xFFE5D7D7), height: 1)),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 14),
                    child: Text(
                      'or continue with',
                      style: GoogleFonts.dmSans(
                        fontSize: 12.5,
                        color: const Color(0xFF9E8F93),
                      ),
                    ),
                  ),
                  const Expanded(child: Divider(color: Color(0xFFE5D7D7), height: 1)),
                ],
              ),
              // ── Continue with Google ──
              GestureDetector(
                onTap: () {
                  // Simulate Google sign-in with a demo account for the selected role.
                  setState(() {
                    _emailController.text = _accountType == AccountType.rider
                        ? 'rider@cartzy.com'
                        : 'buyer@cartzy.com';
                    _passwordController.text = '123456';
                  });
                  _handleLogin();
                },
                child: Container(
                  width: double.infinity,
                  height: 32,
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
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF231F20),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 10),

              // ── Don't have an account? Sign up ──
              Center(
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      "Don't have an account? ",
                      style: GoogleFonts.dmSans(fontSize: 10, color: const Color(0xFF7A6E72)),
                    ),
                    GestureDetector(
                      onTap: () => widget.onSignUp(_accountType),
                      child: Text(
                        'Sign up',
                        style: GoogleFonts.dmSans(
                          fontSize: 10,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF865561),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 8),
              const Divider(height: 1, color: Color(0xFFEDE8EA)),
              const SizedBox(height: 8),
              Center(
                child: Text(
                  'Not shopping? Log in to your workspace',
                  style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFF7A6E72)),
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
                      child: Text('|', style: TextStyle(color: Color(0xFFB7AAAE), fontSize: 11)),
                    ),
                    _workspaceLink('Courier', AccountType.rider),
                  ],
                ),
              ),
              const SizedBox(height: 8),
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
          fontSize: 11,
          fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
          color: isSelected ? const Color(0xFF865561) : const Color(0xFF7A6E72),
          decoration: isSelected ? TextDecoration.underline : TextDecoration.none,
        ),
      ),
    );
  }
}

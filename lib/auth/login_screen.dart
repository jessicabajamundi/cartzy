import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

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

  /// Back arrow — goes back to the role-selection screen.
  final VoidCallback onBack;

  /// "Continue as Guest" — skips straight to the guest home screen.
  final VoidCallback onBackToGuest;

  const LoginScreen({
    super.key,
    required this.initialAccountType,
    required this.onLogin,
    required this.onSignUp,
    required this.onBack,
    required this.onBackToGuest,
  });

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  late AccountType _accountType;

  bool _obscurePassword = true;
  bool _rememberMe = false;
  bool _isLoggingIn = false;

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

    setState(() => _isLoggingIn = true);

    // Simulate a short delay for UX
    await Future.delayed(const Duration(milliseconds: 600));

    if (!mounted) return;

    final email = _emailController.text.trim().toLowerCase();
    final password = _passwordController.text;

    // Match against demo accounts
    final match = _demoAccounts.where(
      (a) => a['email'] == email && a['password'] == password,
    ).firstOrNull;

    setState(() => _isLoggingIn = false);

    if (match == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Invalid email or password. Try buyer@cartzy.com / 123456'),
          backgroundColor: Colors.redAccent,
        ),
      );
      return;
    }

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

  void _showForgotPasswordDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Forgot Password',
            style: TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold)),
        content: const Text(
          'Use the demo credentials:\n\nBuyer:  buyer@cartzy.com\nRider:  rider@cartzy.com\nPassword: 123456',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Got it', style: TextStyle(color: CartzyColors.coral)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ── Back ──
              IconButton(
                icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
                onPressed: widget.onBack,
                padding: EdgeInsets.zero,
              ),
              const SizedBox(height: 24),

              // ── Title ──
              const Text(
                'Welcome back',
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: CartzyColors.navy),
              ),
              const SizedBox(height: 6),
              const Text(
                'Sign in to continue shopping',
                style: TextStyle(fontSize: 14, color: CartzyColors.gray),
              ),
              const SizedBox(height: 32),

              // ── Role tabs ──
              _RoleTabs(
                selected: _accountType,
                onChanged: (t) => setState(() => _accountType = t),
              ),
              const SizedBox(height: 28),

              // ── Demo hint ──
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: CartzyColors.coral.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: CartzyColors.coral.withValues(alpha: 0.3)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.info_outline, size: 16, color: CartzyColors.coral),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Demo: ${_accountType == AccountType.buyer ? 'buyer' : 'rider'}@cartzy.com  /  123456',
                        style: const TextStyle(fontSize: 12, color: CartzyColors.navy),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // ── Email ──
              _InputField(
                controller: _emailController,
                label: 'Email',
                hint: 'you@example.com',
                keyboardType: TextInputType.emailAddress,
                icon: Icons.email_outlined,
                errorText: _emailError,
                onChanged: (_) => setState(() => _emailError = null),
              ),
              const SizedBox(height: 16),

              // ── Password ──
              _InputField(
                controller: _passwordController,
                label: 'Password',
                hint: '••••••',
                obscure: _obscurePassword,
                icon: Icons.lock_outline,
                errorText: _passwordError,
                onChanged: (_) => setState(() => _passwordError = null),
                suffix: IconButton(
                  icon: Icon(
                    _obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                    color: CartzyColors.gray,
                    size: 20,
                  ),
                  onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                ),
              ),
              const SizedBox(height: 12),

              // ── Remember / Forgot ──
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Checkbox(
                        value: _rememberMe,
                        activeColor: CartzyColors.navy,
                        onChanged: (v) => setState(() => _rememberMe = v ?? false),
                      ),
                      const Text('Remember me', style: TextStyle(fontSize: 13, color: CartzyColors.navy)),
                    ],
                  ),
                  TextButton(
                    onPressed: _showForgotPasswordDialog,
                    child: const Text('Forgot password?',
                        style: TextStyle(fontSize: 13, color: CartzyColors.coral)),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // ── Login button ──
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CartzyColors.navy,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    elevation: 0,
                  ),
                  onPressed: _isLoggingIn ? null : _handleLogin,
                  child: _isLoggingIn
                      ? const SizedBox(
                          height: 18, width: 18,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('Sign In',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                ),
              ),
              const SizedBox(height: 20),

              // ── Sign up ──
              Center(
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Text("Don't have an account? ",
                        style: TextStyle(fontSize: 13, color: CartzyColors.gray)),
                    GestureDetector(
                      onTap: () => widget.onSignUp(_accountType),
                      child: const Text('Sign Up',
                          style: TextStyle(
                              fontSize: 13,
                              color: CartzyColors.coral,
                              fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // ── Guest ──
              Center(
                child: TextButton(
                  onPressed: widget.onBackToGuest,
                  child: const Text('Continue as Guest',
                      style: TextStyle(color: CartzyColors.gray, fontSize: 13)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Role Tabs ──────────────────────────────────────────────────
class _RoleTabs extends StatelessWidget {
  final AccountType selected;
  final ValueChanged<AccountType> onChanged;
  const _RoleTabs({required this.selected, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: CartzyColors.border),
      ),
      child: Row(
        children: [
          _tab(AccountType.buyer, 'Buyer', Icons.shopping_bag_outlined),
          _tab(AccountType.rider, 'Rider', Icons.directions_bike_outlined),
        ],
      ),
    );
  }

  Widget _tab(AccountType type, String label, IconData icon) {
    final active = selected == type;
    return Expanded(
      child: GestureDetector(
        onTap: () => onChanged(type),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          margin: const EdgeInsets.all(4),
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: active ? CartzyColors.navy : Colors.transparent,
            borderRadius: BorderRadius.circular(9),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 16, color: active ? Colors.white : CartzyColors.gray),
              const SizedBox(width: 6),
              Text(label,
                  style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: active ? Colors.white : CartzyColors.gray)),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Reusable Input Field ─────────────────────────────────────
class _InputField extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final String hint;
  final IconData icon;
  final bool obscure;
  final TextInputType keyboardType;
  final String? errorText;
  final ValueChanged<String>? onChanged;
  final Widget? suffix;

  const _InputField({
    required this.controller,
    required this.label,
    required this.hint,
    required this.icon,
    this.obscure = false,
    this.keyboardType = TextInputType.text,
    this.errorText,
    this.onChanged,
    this.suffix,
  });

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      obscureText: obscure,
      keyboardType: keyboardType,
      onChanged: onChanged,
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        prefixIcon: Icon(icon, color: CartzyColors.gray, size: 20),
        suffixIcon: suffix,
        errorText: errorText,
        filled: true,
        fillColor: CartzyColors.surface,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: CartzyColors.border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: CartzyColors.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: CartzyColors.coral, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: CartzyColors.error),
        ),
      ),
    );
  }
}
import 'package:flutter/material.dart';
import 'cartzy_colors.dart';
import 'neumorphic_rounded_button.dart';
import 'services/api_service.dart';

enum AccountType { buyer, rider }

class LoginScreen extends StatefulWidget {
  final void Function(AccountType accountType, Map<String, dynamic> user) onLogin;
  final ValueChanged<AccountType> onSignUp;
  final VoidCallback onBackToGuest;

  const LoginScreen({
    super.key,
    required this.onLogin,
    required this.onSignUp,
    required this.onBackToGuest,
  });

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  AccountType _accountType = AccountType.buyer;

  bool _obscurePassword = true;
  bool _rememberMe = false;
  bool _isLoggingIn = false;

  String? _emailError;
  String? _passwordError;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  bool _validateLogin() {
    final email = _emailController.text.trim();
    final password = _passwordController.text;

    setState(() {
      _emailError = null;
      _passwordError = null;
    });

    bool valid = true;

    if (email.isEmpty) {
      setState(() {
        _emailError = 'Please enter your email address.';
      });
      valid = false;
    } else if (!email.contains('@') || !email.contains('.')) {
      setState(() {
        _emailError = 'Please enter a valid email address.';
      });
      valid = false;
    }

    if (password.isEmpty) {
      setState(() {
        _passwordError = 'Please enter your password.';
      });
      valid = false;
    }

    return valid;
  }

  Future<void> _handleLogin() async {
  if (!_validateLogin()) {
    return;
  }

  setState(() {
    _isLoggingIn = true;
  });

  try {
    final response = await ApiService.post(
      '/login',
      {
        'email': _emailController.text.trim(),
        'password': _passwordController.text,
      },
    );

    final user = Map<String, dynamic>.from(
      response['user'] ?? {},
    );

    final role = user['role']?.toString().toLowerCase();

    if (!mounted) return;

    setState(() {
      _isLoggingIn = false;
    });

    if (role == 'buyer') {
      widget.onLogin(AccountType.buyer, user);
    } else if (role == 'rider' || role == 'courier') {
      widget.onLogin(AccountType.rider, user);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('This account type is not supported.'),
        ),
      );
    }
  } catch (e) {
    if (!mounted) return;

    setState(() {
      _isLoggingIn = false;
    });

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          'Login failed: ${e.toString().replaceFirst('Exception: ', '')}',
        ),
      ),
    );
  }
}

  void _showForgotPasswordDialog() {
    final TextEditingController resetEmailController = TextEditingController();

    showDialog(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: const Text(
            'Forgot Password?',
            style: TextStyle(
              color: CartzyColors.navy,
              fontWeight: FontWeight.bold,
            ),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text(
                'Enter your email address to reset your password.',
              ),
              const SizedBox(height: 16),
              TextField(
                controller: resetEmailController,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  labelText: 'Email Address',
                  prefixIcon: const Icon(Icons.email_outlined),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(dialogContext);
              },
              child: const Text('Cancel'),
            ),
            TextButton(
              onPressed: () {
                Navigator.pop(dialogContext);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text(
                      'Password reset instructions will be sent to your email.',
                    ),
                  ),
                );
              },
              child: const Text(
                'Continue',
                style: TextStyle(
                  color: CartzyColors.coral,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildAccountTypeToggle() {
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: CartzyColors.background,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Expanded(
            child: _AccountTypeOption(
              label: 'Buyer',
              icon: Icons.shopping_bag_outlined,
              selected: _accountType == AccountType.buyer,
              onTap: () {
                setState(() => _accountType = AccountType.buyer);
              },
            ),
          ),
          Expanded(
            child: _AccountTypeOption(
              label: 'Rider',
              icon: Icons.two_wheeler_outlined,
              selected: _accountType == AccountType.rider,
              onTap: () {
                setState(() => _accountType = AccountType.rider);
              },
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.background,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back,
            color: CartzyColors.navy,
          ),
          onPressed: widget.onBackToGuest,
        ),
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(
              horizontal: 28,
              vertical: 20,
            ),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 450),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'Welcome Back!',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 30,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.navy,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Sign in to continue',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 14, color: CartzyColors.gray),
                  ),
                  const SizedBox(height: 25),

                  _buildAccountTypeToggle(),

                  const SizedBox(height: 30),

                  const Text(
                    'Email Address',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: CartzyColors.navy,
                    ),
                  ),
                  const SizedBox(height: 8),

                  TextField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    textInputAction: TextInputAction.next,
                    onChanged: (_) {
                      if (_emailError != null) {
                        setState(() => _emailError = null);
                      }
                    },
                    decoration: InputDecoration(
                      hintText: 'Enter your email',
                      prefixIcon: const Icon(Icons.email_outlined, color: CartzyColors.gray),
                      errorText: _emailError,
                      filled: true,
                      fillColor: CartzyColors.surface,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: const BorderSide(color: CartzyColors.border),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: const BorderSide(color: CartzyColors.border),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: const BorderSide(color: CartzyColors.coral, width: 2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),

                  const Text(
                    'Password',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: CartzyColors.navy,
                    ),
                  ),
                  const SizedBox(height: 8),

                  TextField(
                    controller: _passwordController,
                    obscureText: _obscurePassword,
                    textInputAction: TextInputAction.done,
                    onSubmitted: (_) {
                      _handleLogin();
                    },
                    onChanged: (_) {
                      if (_passwordError != null) {
                        setState(() => _passwordError = null);
                      }
                    },
                    decoration: InputDecoration(
                      hintText: 'Enter your password',
                      prefixIcon: const Icon(Icons.lock_outline, color: CartzyColors.gray),
                      suffixIcon: IconButton(
                        icon: Icon(
                          _obscurePassword
                              ? Icons.visibility_outlined
                              : Icons.visibility_off_outlined,
                          color: CartzyColors.gray,
                        ),
                        onPressed: () {
                          setState(() => _obscurePassword = !_obscurePassword);
                        },
                      ),
                      errorText: _passwordError,
                      filled: true,
                      fillColor: CartzyColors.surface,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: const BorderSide(color: CartzyColors.border),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: const BorderSide(color: CartzyColors.border),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: const BorderSide(color: CartzyColors.coral, width: 2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),

                  Row(
                    children: [
                      Checkbox(
                        value: _rememberMe,
                        activeColor: CartzyColors.coral,
                        onChanged: (value) {
                          setState(() => _rememberMe = value ?? false);
                        },
                      ),
                      const Text(
                        'Remember me',
                        style: TextStyle(color: CartzyColors.gray, fontSize: 13),
                      ),
                      const Spacer(),
                      TextButton(
                        onPressed: _showForgotPasswordDialog,
                        child: const Text(
                          'Forgot Password?',
                          style: TextStyle(
                            color: CartzyColors.coral,
                            fontWeight: FontWeight.w600,
                            fontSize: 13,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 25),

                  Center(
                    child: NeumorphicRoundedButton(
                      text: _accountType == AccountType.buyer
                          ? 'Login as Buyer'
                          : 'Login as Rider',
                      borderRadius: 14,
                      width: 220,
                      height: 50,
                      textColor: Colors.white,
                      onTap: _handleLogin,
                    ),
                  ),
                  const SizedBox(height: 30),

                  const Row(
                    children: [
                      Expanded(child: Divider(color: CartzyColors.border)),
                      Padding(
                        padding: EdgeInsets.symmetric(horizontal: 15),
                        child: Text(
                          'OR',
                          style: TextStyle(
                            color: CartzyColors.gray,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                      Expanded(child: Divider(color: CartzyColors.border)),
                    ],
                  ),
                  const SizedBox(height: 25),

                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        _accountType == AccountType.buyer
                            ? "Don't have an account? "
                            : "Not registered as a rider yet? ",
                        style: const TextStyle(color: CartzyColors.gray, fontSize: 14),
                      ),
                      TextButton(
                        onPressed: () => widget.onSignUp(_accountType),
                        child: const Text(
                          'Create Account',
                          style: TextStyle(
                            color: CartzyColors.coral,
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),

                  TextButton(
                    onPressed: widget.onBackToGuest,
                    child: const Text(
                      'Continue as Guest',
                      style: TextStyle(
                        color: CartzyColors.navy,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                  const SizedBox(height: 15),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _AccountTypeOption extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool selected;
  final VoidCallback onTap;

  const _AccountTypeOption({
    required this.label,
    required this.icon,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: selected ? CartzyColors.coral : Colors.transparent,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 18,
              color: selected ? Colors.white : CartzyColors.gray,
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: selected ? Colors.white : CartzyColors.gray,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
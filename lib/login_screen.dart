import 'package:flutter/material.dart';

import 'cartzy_colors.dart';
import 'neumorphic_rounded_button.dart';

class LoginScreen extends StatefulWidget {
  final VoidCallback onLogin;
  final VoidCallback onSignUp;
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
  // ============================================================
  // TEXT CONTROLLERS
  // ============================================================

  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();

  // ============================================================
  // STATES
  // ============================================================

  bool _obscurePassword = true;
  bool _rememberMe = false;

  String? _emailError;
  String? _passwordError;

  // ============================================================
  // DISPOSE
  // ============================================================

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  // ============================================================
  // VALIDATE LOGIN
  // ============================================================

  bool _validateLogin() {
    final email = _emailController.text.trim();
    final password = _passwordController.text;

    setState(() {
      _emailError = null;
      _passwordError = null;
    });

    bool valid = true;

    // EMAIL VALIDATION
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

    // PASSWORD VALIDATION
    if (password.isEmpty) {
      setState(() {
        _passwordError = 'Please enter your password.';
      });
      valid = false;
    }

    return valid;
  }

  // ============================================================
  // LOGIN
  // ============================================================

  void _handleLogin() {
    if (!_validateLogin()) {
      return;
    }

    // ----------------------------------------------------------
    // TEMPORARY LOGIN
    // ----------------------------------------------------------
    // For now, clicking Login takes the user to the dashboard.
    //
    // Later we can connect this to your actual backend/database.
    // ----------------------------------------------------------

    widget.onLogin();
  }

  // ============================================================
  // FORGOT PASSWORD
  // ============================================================

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

  // ============================================================
  // BUILD
  // ============================================================

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,

      // ========================================================
      // APP BAR
      // ========================================================
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

      // ========================================================
      // BODY
      // ========================================================
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
                  // TITLE
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
                    'Sign in to continue shopping',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 14, color: CartzyColors.gray),
                  ),
                  const SizedBox(height: 35),

                  // EMAIL LABEL
                  const Text(
                    'Email Address',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: CartzyColors.navy,
                    ),
                  ),
                  const SizedBox(height: 8),

                  // EMAIL FIELD
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

                  // PASSWORD LABEL
                  const Text(
                    'Password',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: CartzyColors.navy,
                    ),
                  ),
                  const SizedBox(height: 8),

                  // PASSWORD FIELD
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

                  // REMEMBER ME + FORGOT PASSWORD
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

                  // NEUMORPHIC LOGIN BUTTON
                  Center(
                    child: NeumorphicRoundedButton(
                      text: 'Login',
                      borderRadius: 14,
                      width: 200,
                      height: 50,
                      textColor: Colors.white,
                      onTap: _handleLogin,
                    ),
                  ),
                  const SizedBox(height: 30),

                  // OR DIVIDER
                  Row(
                    children: [
                      const Expanded(child: Divider(color: CartzyColors.border)),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 15),
                        child: Text(
                          'OR',
                          style: TextStyle(
                            color: CartzyColors.gray,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                      const Expanded(child: Divider(color: CartzyColors.border)),
                    ],
                  ),
                  const SizedBox(height: 25),

                  // CREATE ACCOUNT
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text(
                        "Don't have an account? ",
                        style: TextStyle(color: CartzyColors.gray, fontSize: 14),
                      ),
                      TextButton(
                        onPressed: widget.onSignUp,
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

                  // CONTINUE AS GUEST
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
import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/theme/cartzy_typography.dart';

class GuestAccountScreen extends StatelessWidget {
  const GuestAccountScreen({
    super.key,
    required this.onLogin,
    required this.onSignUp,
  });

  final VoidCallback onLogin;
  final VoidCallback onSignUp;

  static void open(
    BuildContext context, {
    required VoidCallback onLogin,
    required VoidCallback onSignUp,
  }) {
    final navigator = Navigator.of(context);
    navigator.push(MaterialPageRoute<void>(
      builder: (_) => GuestAccountScreen(
        onLogin: () {
          navigator.pop();
          onLogin();
        },
        onSignUp: () {
          navigator.pop();
          onSignUp();
        },
      ),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        toolbarHeight: 84,
        centerTitle: true,
        backgroundColor: CartzyColors.brand50,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        leading: const BackButton(color: CartzyColors.brandDark),
        title: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('YOUR CARTZY', style: CartzyTypography.overline),
            const SizedBox(height: 4),
            Text('Account', style: CartzyTypography.h1),
          ],
        ),
        bottom: const PreferredSize(
          preferredSize: Size.fromHeight(1),
          child: Divider(height: 1, color: CartzyColors.rose200),
        ),
      ),
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) => SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 32),
            child: ConstrainedBox(
              constraints: BoxConstraints(
                minHeight:
                    (constraints.maxHeight - 64).clamp(0, double.infinity),
              ),
              child: Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 420),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Center(
                        child: Container(
                          width: 80,
                          height: 80,
                          decoration: const BoxDecoration(
                            color: CartzyColors.rose200,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.person_outline_rounded,
                            size: 32,
                            color: CartzyColors.rose700,
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      Text(
                        'Welcome to Cartzy',
                        textAlign: TextAlign.center,
                        style: CartzyTypography.h1,
                      ),
                      const SizedBox(height: 10),
                      Text(
                        'Sign in to track your orders, save your favorites, '
                        'and manage your Cartzy account.',
                        textAlign: TextAlign.center,
                        style: CartzyTypography.bodyLarge.copyWith(
                          color: CartzyColors.brandDeep,
                        ),
                      ),
                      const SizedBox(height: 28),
                      ElevatedButton(
                        onPressed: onLogin,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: CartzyColors.rose700,
                          foregroundColor: CartzyColors.rose50,
                          elevation: 0,
                          minimumSize: const Size.fromHeight(50),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 16, vertical: 14),
                          textStyle: CartzyTypography.buttonText,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: const Text('SIGN IN'),
                      ),
                      const SizedBox(height: 12),
                      OutlinedButton(
                        onPressed: onSignUp,
                        style: OutlinedButton.styleFrom(
                          foregroundColor: CartzyColors.brandDark,
                          minimumSize: const Size.fromHeight(50),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 16, vertical: 14),
                          side: const BorderSide(color: CartzyColors.brandDark),
                          textStyle: CartzyTypography.buttonTextDark,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: const Text('CREATE A CARTZY ACCOUNT',
                            textAlign: TextAlign.center),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

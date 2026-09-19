import 'package:flutter/material.dart';
import 'cartzy_colors.dart';

class SplashScreen extends StatefulWidget {
  final VoidCallback onFinished;

  const SplashScreen({super.key, required this.onFinished});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _finish();
  }

  Future<void> _finish() async {
    await Future.delayed(const Duration(seconds: 2));
    if (!mounted) return;
    widget.onFinished();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: Center(
        child: Image.asset(
          'assets/cartzy_splash.png',
          width: 170,
          height: 170,
          fit: BoxFit.contain,
        ),
      ),
    );
  }
}
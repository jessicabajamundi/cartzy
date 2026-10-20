import 'package:flutter/material.dart';
import 'package:cartzy/widgets/cartzy_logo.dart';

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
      backgroundColor: Colors.white,
      body: Center(
        child: LayoutBuilder(
          builder: (context, constraints) {
            final logoWidth = (constraints.maxWidth * 0.36).clamp(0.0, 180.0);
            return CartzyLogo(height: 68, width: logoWidth);
          },
        ),
      ),
    );
  }
}

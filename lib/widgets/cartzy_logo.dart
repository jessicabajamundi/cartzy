import 'package:flutter/material.dart';

/// Displays the horizontal Cartzy wordmark while cropping the asset's blank
/// canvas so the logo remains legible at mobile header sizes.
class CartzyLogo extends StatelessWidget {
  final double height;
  final double? width;

  const CartzyLogo({super.key, required this.height, this.width});

  @override
  Widget build(BuildContext context) {
    return ClipRect(
      child: SizedBox(
        width: width ?? height * 2.65,
        height: height,
        child: Image.asset(
          'assets/cartzy_logo_new.png',
          fit: BoxFit.cover,
          // The source art sits slightly above the center of its white canvas.
          alignment: const Alignment(0, -0.15),
        ),
      ),
    );
  }
}

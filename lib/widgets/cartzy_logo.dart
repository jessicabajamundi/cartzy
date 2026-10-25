import 'package:flutter/material.dart';

/// Clean, transparent horizontal Cartzy wordmark logo.
/// Seamlessly blends with any background color without white boxes or clipping artifacts.
class CartzyLogo extends StatelessWidget {
  final double height;
  final double? width;
  final BoxFit fit;

  const CartzyLogo({
    super.key,
    required this.height,
    this.width,
    this.fit = BoxFit.contain,
  });

  @override
  Widget build(BuildContext context) {
    final effectiveWidth = width ?? (height * 2.75);

    return Image.asset(
      'assets/logo-transparent.png',
      height: height,
      width: effectiveWidth,
      fit: fit,
      filterQuality: FilterQuality.high,
    );
  }
}

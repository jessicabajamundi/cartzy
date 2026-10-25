import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

/// A two-tone icon that stays distinct over light and dark banner photos.
class BannerActionIcon extends StatelessWidget {
  final IconData icon;

  const BannerActionIcon(this.icon, {super.key});

  @override
  Widget build(BuildContext context) {
    return Icon(
      icon,
      size: 25,
      color: CartzyColors.burgundyDeep,
      shadows: const [
        Shadow(color: Color(0x50000000), blurRadius: 4, offset: Offset(0, 1)),
        Shadow(color: Colors.white, blurRadius: 1, offset: Offset(-1, -1)),
        Shadow(color: Colors.white, blurRadius: 1, offset: Offset(1, -1)),
        Shadow(color: Colors.white, blurRadius: 1, offset: Offset(-1, 1)),
        Shadow(color: Colors.white, blurRadius: 1, offset: Offset(1, 1)),
      ],
    );
  }
}

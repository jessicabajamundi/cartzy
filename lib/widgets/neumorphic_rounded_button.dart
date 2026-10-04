import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

class NeumorphicRoundedButton extends StatelessWidget {
  final String text;
  final double borderRadius;
  final double width;
  final double height;
  final Color textColor;
  final VoidCallback onTap;

  const NeumorphicRoundedButton({
    super.key,
    required this.text,
    required this.borderRadius,
    required this.width,
    required this.height,
    required this.textColor,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: width,
        height: height,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: CartzyColors.coral,
          borderRadius: BorderRadius.circular(borderRadius),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.15),
              offset: const Offset(2, 2),
              blurRadius: 4,
            ),
          ],
        ),
        child: Text(
          text,
          style: TextStyle(
            color: textColor,
            fontWeight: FontWeight.bold,
            fontSize: 13,
          ),
        ),
      ),
    );
  }
}

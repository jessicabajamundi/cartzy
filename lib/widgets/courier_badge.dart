import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class CourierBadge extends StatelessWidget {
  final double fontSize;
  final EdgeInsetsGeometry padding;

  const CourierBadge({
    super.key,
    this.fontSize = 9.5,
    this.padding = const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: padding,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF4C4054), Color(0xFF6E4A56)],
          begin: Alignment.centerLeft,
          end: Alignment.centerRight,
        ),
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF4C4054).withValues(alpha: 0.3),
            blurRadius: 4,
            offset: const Offset(0, 1.5),
          ),
        ],
      ),
      child: Text(
        'COURIER',
        style: GoogleFonts.dmSans(
          fontSize: fontSize,
          fontWeight: FontWeight.w800,
          color: Colors.white,
          letterSpacing: 0.9,
        ),
      ),
    );
  }
}

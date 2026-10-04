import 'package:flutter/material.dart';
import 'cartzy_colors.dart';
import 'cartzy_typography.dart';

// ============================================================
// CARTZY BUTTON STYLES — Centralized Button Styles ("CSS Buttons")
// ============================================================

class CartzyButtonStyles {
  // Primary Action Button (Navy/Coral)
  static final ButtonStyle primary = ElevatedButton.styleFrom(
    backgroundColor: CartzyColors.navy,
    foregroundColor: Colors.white,
    elevation: 0,
    textStyle: CartzyTypography.buttonText,
    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(12),
    ),
  );

  // Accent Button (Coral)
  static final ButtonStyle accent = ElevatedButton.styleFrom(
    backgroundColor: CartzyColors.coral,
    foregroundColor: Colors.white,
    elevation: 0,
    textStyle: CartzyTypography.buttonText,
    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(12),
    ),
  );

  // Secondary / Outlined Button
  static final ButtonStyle outline = OutlinedButton.styleFrom(
    foregroundColor: CartzyColors.navy,
    backgroundColor: CartzyColors.surface,
    textStyle: CartzyTypography.buttonTextDark,
    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
    side: const BorderSide(color: CartzyColors.border, width: 1.2),
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(12),
    ),
  );

  // Pill / Rounded Small Button
  static final ButtonStyle pill = ElevatedButton.styleFrom(
    backgroundColor: CartzyColors.navy,
    foregroundColor: Colors.white,
    elevation: 0,
    textStyle: CartzyTypography.caption.copyWith(fontWeight: FontWeight.bold, color: Colors.white),
    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(999),
    ),
  );

  // Danger Button (Red)
  static final ButtonStyle danger = ElevatedButton.styleFrom(
    backgroundColor: CartzyColors.error,
    foregroundColor: Colors.white,
    elevation: 0,
    textStyle: CartzyTypography.buttonText,
    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 15),
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(12),
    ),
  );
}

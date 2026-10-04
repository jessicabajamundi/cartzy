import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'cartzy_colors.dart';

// ============================================================
// CARTZY TYPOGRAPHY — Centralized Text Styles ("CSS Typography")
// ============================================================

class CartzyTypography {
  static final TextStyle fontBase = GoogleFonts.poppins();

  // Headings
  static final TextStyle h1 = fontBase.copyWith(
    fontSize: 28,
    fontWeight: FontWeight.bold,
    color: CartzyColors.navy,
    letterSpacing: -0.5,
  );

  static final TextStyle h2 = fontBase.copyWith(
    fontSize: 22,
    fontWeight: FontWeight.bold,
    color: CartzyColors.navy,
    letterSpacing: -0.3,
  );

  static final TextStyle h3 = fontBase.copyWith(
    fontSize: 18,
    fontWeight: FontWeight.w600,
    color: CartzyColors.navy,
  );

  static final TextStyle title = fontBase.copyWith(
    fontSize: 16,
    fontWeight: FontWeight.w600,
    color: CartzyColors.navy,
  );

  static final TextStyle subtitle = fontBase.copyWith(
    fontSize: 14,
    fontWeight: FontWeight.w500,
    color: CartzyColors.gray,
  );

  // Body Text
  static final TextStyle bodyLarge = fontBase.copyWith(
    fontSize: 16,
    fontWeight: FontWeight.normal,
    color: CartzyColors.text,
  );

  static final TextStyle body = fontBase.copyWith(
    fontSize: 14,
    fontWeight: FontWeight.normal,
    color: CartzyColors.text,
  );

  static final TextStyle bodySmall = fontBase.copyWith(
    fontSize: 12,
    fontWeight: FontWeight.normal,
    color: CartzyColors.gray,
  );

  // Labels & Captions
  static final TextStyle label = fontBase.copyWith(
    fontSize: 13,
    fontWeight: FontWeight.w600,
    color: CartzyColors.navy,
  );

  static final TextStyle caption = fontBase.copyWith(
    fontSize: 11,
    fontWeight: FontWeight.normal,
    color: CartzyColors.gray,
  );

  // Button Labels
  static final TextStyle buttonText = fontBase.copyWith(
    fontSize: 15,
    fontWeight: FontWeight.w600,
    color: Colors.white,
    letterSpacing: 0.2,
  );

  static final TextStyle buttonTextDark = fontBase.copyWith(
    fontSize: 15,
    fontWeight: FontWeight.w600,
    color: CartzyColors.navy,
  );

  // Price Displays
  static final TextStyle priceLarge = fontBase.copyWith(
    fontSize: 24,
    fontWeight: FontWeight.bold,
    color: CartzyColors.navy,
  );

  static final TextStyle price = fontBase.copyWith(
    fontSize: 16,
    fontWeight: FontWeight.bold,
    color: CartzyColors.navy,
  );

  static final TextStyle priceSmall = fontBase.copyWith(
    fontSize: 13,
    fontWeight: FontWeight.bold,
    color: CartzyColors.coral,
  );

  // Status & Badges
  static final TextStyle badge = fontBase.copyWith(
    fontSize: 11,
    fontWeight: FontWeight.w600,
  );

  static final TextStyle error = fontBase.copyWith(
    fontSize: 12,
    fontWeight: FontWeight.w500,
    color: CartzyColors.error,
  );
}

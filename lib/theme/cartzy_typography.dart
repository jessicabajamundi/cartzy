import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'cartzy_colors.dart';

// ============================================================
// CARTZY TYPOGRAPHY — Warm Editorial Design System
// Headlines: Playfair Display (serif, like cartzyy.shop)
// Body: DM Sans (clean modern sans-serif)
// ============================================================

class CartzyTypography {
  static final TextStyle _serif  = GoogleFonts.playfairDisplay();
  static final TextStyle _sans   = GoogleFonts.dmSans();

  // ── Headings (serif) ──────────────────────────────────────
  static final TextStyle h1 = _serif.copyWith(
    fontSize: 30,
    fontWeight: FontWeight.w700,
    color: CartzyColors.navy,
    height: 1.2,
    letterSpacing: -0.5,
  );

  static final TextStyle h1Accent = _serif.copyWith(
    fontSize: 30,
    fontWeight: FontWeight.w700,
    color: CartzyColors.burgundy,
    height: 1.2,
    letterSpacing: -0.5,
  );

  static final TextStyle h2 = _serif.copyWith(
    fontSize: 22,
    fontWeight: FontWeight.w700,
    color: CartzyColors.navy,
    letterSpacing: -0.3,
  );

  static final TextStyle h3 = _sans.copyWith(
    fontSize: 18,
    fontWeight: FontWeight.w600,
    color: CartzyColors.navy,
  );

  static final TextStyle title = _sans.copyWith(
    fontSize: 16,
    fontWeight: FontWeight.w600,
    color: CartzyColors.navy,
  );

  static final TextStyle subtitle = _sans.copyWith(
    fontSize: 14,
    fontWeight: FontWeight.w500,
    color: CartzyColors.gray,
  );

  // ── Body (sans) ───────────────────────────────────────────
  static final TextStyle bodyLarge = _sans.copyWith(
    fontSize: 16,
    color: CartzyColors.text,
    height: 1.55,
  );

  static final TextStyle body = _sans.copyWith(
    fontSize: 14,
    color: CartzyColors.text,
    height: 1.5,
  );

  static final TextStyle bodySmall = _sans.copyWith(
    fontSize: 12,
    color: CartzyColors.gray,
    height: 1.45,
  );

  // ── Labels & Captions ─────────────────────────────────────
  static final TextStyle label = _sans.copyWith(
    fontSize: 13,
    fontWeight: FontWeight.w600,
    color: CartzyColors.navy,
  );

  static final TextStyle caption = _sans.copyWith(
    fontSize: 11,
    color: CartzyColors.gray,
  );

  static final TextStyle overline = _sans.copyWith(
    fontSize: 10,
    fontWeight: FontWeight.w700,
    color: CartzyColors.burgundy,
    letterSpacing: 1.2,
  );

  // ── Buttons ───────────────────────────────────────────────
  static final TextStyle buttonText = _sans.copyWith(
    fontSize: 13,
    fontWeight: FontWeight.w700,
    color: Colors.white,
    letterSpacing: 0.8,
  );

  static final TextStyle buttonTextDark = _sans.copyWith(
    fontSize: 13,
    fontWeight: FontWeight.w700,
    color: CartzyColors.navy,
    letterSpacing: 0.5,
  );

  // ── Price ─────────────────────────────────────────────────
  static final TextStyle priceLarge = _sans.copyWith(
    fontSize: 24,
    fontWeight: FontWeight.w700,
    color: CartzyColors.navy,
  );

  static final TextStyle price = _sans.copyWith(
    fontSize: 16,
    fontWeight: FontWeight.w700,
    color: CartzyColors.navy,
  );

  static final TextStyle priceSmall = _sans.copyWith(
    fontSize: 13,
    fontWeight: FontWeight.w700,
    color: CartzyColors.burgundy,
  );

  // ── Status ────────────────────────────────────────────────
  static final TextStyle badge = _sans.copyWith(
    fontSize: 11,
    fontWeight: FontWeight.w600,
  );

  static final TextStyle error = _sans.copyWith(
    fontSize: 12,
    fontWeight: FontWeight.w500,
    color: CartzyColors.error,
  );

  // ── Nav Tabs ──────────────────────────────────────────────
  static final TextStyle navTab = _sans.copyWith(
    fontSize: 14,
    fontWeight: FontWeight.w500,
    color: CartzyColors.gray,
  );

  static final TextStyle navTabActive = _sans.copyWith(
    fontSize: 14,
    fontWeight: FontWeight.w600,
    color: CartzyColors.burgundy,
  );
}


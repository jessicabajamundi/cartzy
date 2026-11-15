import 'package:flutter/material.dart';

/// Shared mobile color tokens: rose accents and lavender brand colors.
class CartzyColors {
  // Rose palette.
  static const rose700 = Color(0xFF8C5A50);
  static const rose500 = Color(0xFFC08B7F);
  static const rose400 = Color(0xFFD4A69A);
  static const rose200 = Color(0xFFE5CFC7);
  static const rose50 = Color(0xFFFAF8F6);
  static const rose50Warm = Color(0xFFFFF5F3);
  static const rose900 = Color(0xFF4A2520);

  // Lavender brand palette.
  static const brandDark = Color(0xFF564B68);
  static const brandDeep = Color(0xFF6F6382);
  static const brand500 = Color(0xFF91879E);
  static const brand400 = Color(0xFFA3A0B2);
  static const brand200 = Color(0xFFE1DDE7);
  static const brand100 = Color(0xFFF1EFF5);
  static const brand50 = Color(0xFFFAF9FB);
  static const brand50Lavender = Color(0xFFF8F6FA);
  static const brand900 = Color(0xFF28213D);

  static const brandPrimary = brand400;
  static const brandMuted = brand500;
  static const brandDarkest = brand900;
  static const brandSoft = brand50;
  static const brandLight = brand100;
  static const brandBorder = brand200;

  static const roseDark = rose700;
  static const rosePrimary = rose500;
  static const roseMuted = rose400;
  static const roseLight = rose200;
  static const roseSoft = rose50Warm;
  static const roseDarkest = rose900;
  static const rose = rose500;

  // Existing screen aliases use the shared palettes.
  static const cream = rose50;
  static const creamLight = brand50;
  static const creamSection = brand100;
  static const burgundy = rose700;
  static const burgundyDeep = rose900;
  static const navyDark = brandDark;
  static const navyMid = brandDeep;
  static const navy = navyDark;
  static const coral = rose700;
  static const primary = rose700;
  static const deep = rose900;
  static const pinkBorder = rose200;

  // Neutral text and status colors.
  static const grayMid = Color(0xFF8A7E8A);
  static const grayLight = Color(0xFFBEB4BE);
  static const gray = grayMid;
  static const gold = Color(0xFFE53935); // Logo only.
  static const error = Color(0xFFD32F2F);

  static const background = rose50;
  static const surface = brand50;
  static const surfaceLight = brand100;
  static const border = brand200;
  static const text = brand900;

  // Dark mode palette (plum-charcoal & dusty mauve).
  static const darkBackground = Color(0xFF171419);
  static const darkSurface = Color(0xFF211C23);
  static const darkSurfaceElevated = Color(0xFF2A242D);
  static const darkBorder = Color(0xFF3A313E);
  static const darkText = Color(0xFFF4EEF5);
  static const darkMuted = Color(0xFFA89CA9);
  static const darkAccent = Color(0xFF9E7789);
  static const darkAccentDeep = Color(0xFF6B4C5D);

  static bool isDark(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark;
  static Color bgOf(BuildContext context) =>
      isDark(context) ? darkBackground : background;
  static Color surfaceOf(BuildContext context) =>
      isDark(context) ? darkSurface : Colors.white;
  static Color elevatedOf(BuildContext context) =>
      isDark(context) ? darkSurfaceElevated : brand50;
  static Color borderOf(BuildContext context) =>
      isDark(context) ? darkBorder : border;
  static Color textOf(BuildContext context) =>
      isDark(context) ? darkText : text;
  static Color mutedOf(BuildContext context) =>
      isDark(context) ? darkMuted : grayMid;
}
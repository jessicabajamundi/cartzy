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
  static const darkAccentText = Color(0xFFDDB4C7);
  static const darkError = Color(0xFFFFB4AB);

  static bool isDark(BuildContext context) =>
      Theme.of(context).brightness == Brightness.dark;
  static Color bgOf(BuildContext context) =>
      isDark(context) ? darkBackground : background;
  // Optional light values retain each component's original light palette.
  static Color surfaceOf(BuildContext context, {Color light = Colors.white}) =>
      isDark(context) ? darkSurface : light;
  static Color elevatedOf(BuildContext context, {Color light = brand50}) =>
      isDark(context) ? darkSurfaceElevated : light;
  static Color borderOf(BuildContext context, {Color light = border}) =>
      isDark(context) ? darkBorder : light;
  static Color textOf(BuildContext context, {Color light = text}) =>
      isDark(context) ? darkText : light;
  static Color mutedOf(BuildContext context, {Color light = grayMid}) =>
      isDark(context) ? darkMuted : light;

  /// Retain the light palette and adjust dark-mode labels against their actual
  /// surface, including bright status badges that still need dark lettering.
  static Color readableOf(
    BuildContext context, {
    required Color light,
    Color? background,
  }) {
    if (!isDark(context)) return light;
    final surface =
        Color.alphaBlend(background ?? darkSurfaceElevated, darkSurface);
    double contrast(Color foreground) {
      final a = Color.alphaBlend(foreground, surface).computeLuminance();
      final b = surface.computeLuminance();
      return a > b ? (a + 0.05) / (b + 0.05) : (b + 0.05) / (a + 0.05);
    }

    if (contrast(light) >= 4.5) return light;
    final target = contrast(Colors.white) >= contrast(Colors.black)
        ? Colors.white
        : Colors.black;
    // Blend the opaque visible color to retain its status/accent hue.
    final visible = Color.alphaBlend(light, surface);
    for (var step = 1; step <= 20; step++) {
      final candidate = Color.lerp(visible, target, step / 20)!;
      if (contrast(candidate) >= 4.5) return candidate;
    }
    return target;
  }

  static LinearGradient softGradientOf(
    BuildContext context, {
    required LinearGradient light,
  }) =>
      isDark(context)
          ? LinearGradient(
              begin: light.begin,
              end: light.end,
              colors: [
                for (var i = 0; i < light.colors.length; i++)
                  i == 0 ? darkSurfaceElevated : darkSurface,
              ],
              stops: light.stops,
              tileMode: light.tileMode,
              transform: light.transform,
            )
          : light;
}

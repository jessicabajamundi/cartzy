import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

export 'cartzy_colors.dart';
export 'cartzy_typography.dart';
export 'cartzy_decorations.dart';
export 'cartzy_input_styles.dart';
export 'cartzy_button_styles.dart';

import 'cartzy_colors.dart';
import 'cartzy_typography.dart';
import 'cartzy_input_styles.dart';
import 'cartzy_button_styles.dart';

// ============================================================
// CARTZY THEME — Warm Cream & Burgundy Material Theme
// ============================================================

class CartzyTheme {
  static ThemeData get lightTheme {
    final baseFont = GoogleFonts.dmSans();

    return ThemeData(
      useMaterial3: true,
      scaffoldBackgroundColor: CartzyColors.background,
      fontFamily: baseFont.fontFamily,
      textTheme: GoogleFonts.dmSansTextTheme(
        ThemeData.light().textTheme,
      ),
      colorScheme: ColorScheme.fromSeed(
        seedColor: CartzyColors.burgundy,
        primary: CartzyColors.navy,
        secondary: CartzyColors.burgundy,
        surface: CartzyColors.surface,
        error: CartzyColors.error,
        brightness: Brightness.light,
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        centerTitle: false,
        iconTheme: const IconThemeData(color: CartzyColors.navy),
        titleTextStyle: CartzyTypography.h3,
      ),
      cardTheme: CardThemeData(
        color: CartzyColors.surface,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
          side: const BorderSide(color: CartzyColors.border),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: CartzyButtonStyles.primary,
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: CartzyButtonStyles.outline,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: CartzyColors.surface,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        enabledBorder: CartzyInputStyles.defaultBorder,
        focusedBorder: CartzyInputStyles.focusedBorder,
        errorBorder: CartzyInputStyles.errorBorder,
        hintStyle: CartzyTypography.bodySmall.copyWith(color: CartzyColors.gray),
        labelStyle: CartzyTypography.subtitle,
      ),
    );
  }
}



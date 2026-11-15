import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

export 'cartzy_colors.dart';
export 'cartzy_typography.dart';
export 'cartzy_decorations.dart';
export 'cartzy_input_styles.dart';
export 'cartzy_button_styles.dart';
export 'theme_controller.dart';

import 'cartzy_colors.dart';
import 'cartzy_typography.dart';
import 'cartzy_input_styles.dart';
import 'cartzy_button_styles.dart';

// ============================================================
// CARTZY THEME — Warm Cream & Burgundy Material Theme
// ============================================================

class CartzyTheme {
  static const darkColorScheme = ColorScheme.dark(
    primary: CartzyColors.darkAccentText,
    onPrimary: CartzyColors.darkBackground,
    secondary: CartzyColors.rose400,
    onSecondary: CartzyColors.darkBackground,
    surface: CartzyColors.darkSurface,
    onSurface: CartzyColors.darkText,
    surfaceContainerHighest: CartzyColors.darkSurfaceElevated,
    outline: CartzyColors.darkBorder,
    error: CartzyColors.darkError,
    onError: CartzyColors.darkBackground,
  );

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
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        enabledBorder: CartzyInputStyles.defaultBorder,
        focusedBorder: CartzyInputStyles.focusedBorder,
        errorBorder: CartzyInputStyles.errorBorder,
        hintStyle:
            CartzyTypography.bodySmall.copyWith(color: CartzyColors.gray),
        labelStyle: CartzyTypography.subtitle,
      ),
    );
  }

  static ThemeData get darkTheme {
    final baseFont = GoogleFonts.dmSans();
    final baseDarkText = GoogleFonts.dmSansTextTheme(
      ThemeData.dark().textTheme,
    ).apply(
      bodyColor: CartzyColors.darkText,
      displayColor: CartzyColors.darkText,
    );

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      scaffoldBackgroundColor: CartzyColors.darkBackground,
      fontFamily: baseFont.fontFamily,
      textTheme: baseDarkText,
      dividerColor: CartzyColors.darkBorder,
      colorScheme: darkColorScheme,
      appBarTheme: AppBarTheme(
        backgroundColor: CartzyColors.darkSurface,
        foregroundColor: CartzyColors.darkText,
        elevation: 0,
        centerTitle: false,
        iconTheme: const IconThemeData(color: CartzyColors.darkText),
        titleTextStyle:
            CartzyTypography.h3.copyWith(color: CartzyColors.darkText),
      ),
      cardTheme: CardThemeData(
        color: CartzyColors.darkSurface,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
          side: const BorderSide(color: CartzyColors.darkBorder),
        ),
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: CartzyColors.darkSurface,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(18),
          side: const BorderSide(color: CartzyColors.darkBorder),
        ),
      ),
      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: CartzyColors.darkSurface,
        surfaceTintColor: Colors.transparent,
      ),
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: CartzyColors.darkSurface,
        selectedItemColor: CartzyColors.darkAccentText,
        unselectedItemColor: CartzyColors.darkMuted,
      ),
      dividerTheme: const DividerThemeData(
        color: CartzyColors.darkBorder,
        thickness: 1,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: CartzyColors.darkAccentDeep,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: CartzyColors.darkText,
          side: const BorderSide(color: CartzyColors.darkBorder),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style:
            TextButton.styleFrom(foregroundColor: CartzyColors.darkAccentText),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: CartzyColors.darkSurfaceElevated,
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: CartzyColors.darkBorder),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide:
              const BorderSide(color: CartzyColors.darkAccent, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: CartzyColors.darkError),
        ),
        errorStyle: const TextStyle(color: CartzyColors.darkError),
        hintStyle:
            CartzyTypography.bodySmall.copyWith(color: CartzyColors.darkMuted),
        labelStyle:
            CartzyTypography.subtitle.copyWith(color: CartzyColors.darkText),
      ),
    );
  }
}

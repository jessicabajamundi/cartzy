import 'package:flutter/material.dart';
import 'cartzy_colors.dart';
import 'cartzy_typography.dart';

// ============================================================
// CARTZY INPUT STYLES — Centralized Form & Input Styles ("CSS Forms")
// ============================================================

class CartzyInputStyles {
  // Border Definitions
  static final OutlineInputBorder defaultBorder = OutlineInputBorder(
    borderRadius: BorderRadius.circular(12),
    borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
  );

  static final OutlineInputBorder focusedBorder = OutlineInputBorder(
    borderRadius: BorderRadius.circular(12),
    borderSide: const BorderSide(color: CartzyColors.navy, width: 1.8),
  );

  static final OutlineInputBorder errorBorder = OutlineInputBorder(
    borderRadius: BorderRadius.circular(12),
    borderSide: const BorderSide(color: CartzyColors.error, width: 1.4),
  );

  // Reusable InputDecoration Builder
  static InputDecoration standard({
    String? hintText,
    String? labelText,
    Widget? prefixIcon,
    Widget? suffixIcon,
    EdgeInsetsGeometry? contentPadding,
  }) {
    return InputDecoration(
      hintText: hintText,
      labelText: labelText,
      hintStyle: CartzyTypography.bodySmall.copyWith(color: CartzyColors.gray),
      labelStyle: CartzyTypography.subtitle,
      prefixIcon: prefixIcon,
      suffixIcon: suffixIcon,
      filled: true,
      fillColor: CartzyColors.surface,
      contentPadding: contentPadding ?? const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      enabledBorder: defaultBorder,
      focusedBorder: focusedBorder,
      errorBorder: errorBorder,
      focusedErrorBorder: errorBorder,
    );
  }

  // Search Input Style
  static InputDecoration search({
    String hintText = 'Search items...',
    Widget? suffixIcon,
    VoidCallback? onClear,
  }) {
    return InputDecoration(
      hintText: hintText,
      hintStyle: CartzyTypography.bodySmall.copyWith(color: CartzyColors.gray),
      prefixIcon: const Icon(Icons.search, color: CartzyColors.gray, size: 20),
      suffixIcon: suffixIcon,
      filled: true,
      fillColor: CartzyColors.surface,
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(999),
        borderSide: BorderSide.none,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(999),
        borderSide: const BorderSide(color: CartzyColors.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(999),
        borderSide: const BorderSide(color: CartzyColors.navy, width: 1.5),
      ),
    );
  }
}

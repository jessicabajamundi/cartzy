import 'package:flutter/material.dart';
import 'cartzy_colors.dart';

// ============================================================
// CARTZY DECORATIONS — Centralized Box & Card Styles ("CSS Containers")
// ============================================================

class CartzyDecorations {
  // Border Radii
  static const BorderRadius radiusSm = BorderRadius.all(Radius.circular(8));
  static const BorderRadius radiusMd = BorderRadius.all(Radius.circular(12));
  static const BorderRadius radiusLg = BorderRadius.all(Radius.circular(16));
  static const BorderRadius radiusXl = BorderRadius.all(Radius.circular(24));
  static const BorderRadius radiusPill = BorderRadius.all(Radius.circular(999));

  // Box Shadows
  static const List<BoxShadow> shadowSm = [
    BoxShadow(
      color: Color(0x0A000000),
      offset: Offset(0, 2),
      blurRadius: 6,
      spreadRadius: 0,
    ),
  ];

  static const List<BoxShadow> shadowMd = [
    BoxShadow(
      color: Color(0x0F000000),
      offset: Offset(0, 4),
      blurRadius: 12,
      spreadRadius: 0,
    ),
  ];

  static const List<BoxShadow> shadowLg = [
    BoxShadow(
      color: Color(0x14000000),
      offset: Offset(0, 8),
      blurRadius: 24,
      spreadRadius: 0,
    ),
  ];

  // Card Decorations
  static final BoxDecoration card = BoxDecoration(
    color: CartzyColors.surface,
    borderRadius: radiusMd,
    border: Border.all(color: CartzyColors.border),
    boxShadow: shadowSm,
  );

  static const BoxDecoration cardElevated = BoxDecoration(
    color: CartzyColors.surface,
    borderRadius: radiusLg,
    boxShadow: shadowMd,
  );

  static const BoxDecoration cardFlat = BoxDecoration(
    color: CartzyColors.surface,
    borderRadius: radiusMd,
    border: Border.fromBorderSide(BorderSide(color: CartzyColors.border)),
  );

  // Status Badge Decorations
  static BoxDecoration statusBadge(Color background) => BoxDecoration(
    color: background.withValues(alpha: 0.12),
    borderRadius: radiusPill,
    border: Border.all(color: background.withValues(alpha: 0.28)),
  );

  // Input Field Container
  static final BoxDecoration searchBar = BoxDecoration(
    color: CartzyColors.surface,
    borderRadius: radiusPill,
    border: Border.all(color: CartzyColors.border),
    boxShadow: shadowSm,
  );

  // Circular Avatar / Icon Container
  static BoxDecoration circularIconContainer({Color color = CartzyColors.surface}) => BoxDecoration(
    color: color,
    shape: BoxShape.circle,
    border: Border.all(color: CartzyColors.border),
    boxShadow: shadowSm,
  );
}

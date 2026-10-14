import 'package:flutter/material.dart';

class CartzyColors {
  // ============================================================
  // BRAND COLOR PALETTE — Warm Cream & Burgundy (cartzyy.shop)
  // ============================================================

  // Warm cream backgrounds
  static const cream        = Color(0xFFFAF5F0);
  static const creamLight   = Color(0xFFFDF9F6);
  static const creamSection = Color(0xFFF2EAE1);

  // Burgundy / wine-red accent (Sale tag, colored headlines)
  static const burgundy     = Color(0xFF8B3A3A);
  static const burgundyDeep = Color(0xFF6B2929);

  // Dark navy / charcoal (buttons, nav text, bold UI)
  static const navyDark     = Color(0xFF2D2640);
  static const navyMid      = Color(0xFF3D3555);

  // Rose / dusty-pink (card borders, accent icons)
  static const rose         = Color(0xFFD4878A);
  static const roseSoft     = Color(0xFFF5E8E8);

  // Neutrals
  static const grayMid      = Color(0xFF8A7E8A);
  static const grayLight    = Color(0xFFBEB4BE);
  static const pinkBorder   = Color(0xFFE8D4D4);

  // ============================================================
  // SEMANTIC ALIASES (referenced throughout existing code)
  // ============================================================
  static const brandPrimary  = rose;
  static const brandDeep     = burgundy;
  static const brandDark     = navyDark;
  static const brandDarkest  = Color(0xFF1A1528);
  static const brandSoft     = cream;
  static const brandLight    = creamSection;
  static const brandBorder   = pinkBorder;

  static const navy          = navyDark;
  static const coral         = burgundy;   // accent → burgundy
  static const primary       = burgundy;
  static const deep          = burgundyDeep;
  static const gold          = Color(0xFFE53935); // logo only

  static const background    = cream;
  static const surface       = creamLight;
  static const surfaceLight  = creamSection;

  static const gray          = grayMid;
  static const border        = pinkBorder;
  static const text          = navyDark;
  static const error         = Color(0xFFD32F2F);
}
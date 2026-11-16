import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/theme/theme_controller.dart';

/// Theme preference shared by account pages and navigation drawers.
class ThemeToggleTile extends StatelessWidget {
  const ThemeToggleTile({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = ThemeController.isDark(context);
    return SwitchListTile(
      title: Text(
        'Dark Mode',
        style: TextStyle(
          color: CartzyColors.textOf(context),
          fontSize: 14,
          fontWeight: FontWeight.w600,
        ),
      ),
      secondary: Icon(
        isDark ? Icons.dark_mode_rounded : Icons.light_mode_rounded,
        color: CartzyColors.textOf(context),
      ),
      value: isDark,
      onChanged: ThemeController.setDark,
      activeThumbColor: CartzyColors.darkAccent,
    );
  }
}

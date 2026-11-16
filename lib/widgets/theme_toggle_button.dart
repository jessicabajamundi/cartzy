import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/theme/theme_controller.dart';

/// One shared theme action for guests and every mobile account type.
class ThemeToggleButton extends StatelessWidget {
  const ThemeToggleButton({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = ThemeController.isDark(context);
    return IconButton.filledTonal(
      tooltip: isDark ? 'Switch to light mode' : 'Switch to dark mode',
      isSelected: isDark,
      onPressed: () => ThemeController.toggle(context),
      style: IconButton.styleFrom(
        minimumSize: const Size(48, 48),
        backgroundColor: CartzyColors.elevatedOf(context),
        foregroundColor: CartzyColors.textOf(context),
      ),
      icon: const Icon(Icons.dark_mode_outlined),
      selectedIcon: const Icon(Icons.light_mode_outlined),
    );
  }
}

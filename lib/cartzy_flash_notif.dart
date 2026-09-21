import 'package:flutter/material.dart';

import 'cartzy_colors.dart';

// ============================================================
// FLASH NOTIFICATION
// ============================================================
//
// A short, floating success/info banner that auto-dismisses on its own
// (no button to tap) — used after actions like "account created" or
// "changes saved". Usage:
//
//   showCartzyFlash(context, 'Account created!');
//   showCartzyFlash(context, 'Changes saved.', icon: Icons.check_circle);
//
// Built on top of ScaffoldMessenger/SnackBar so it works with any screen
// that has a Scaffold ancestor (which every screen in this app has).

void showCartzyFlash(
  BuildContext context,
  String message, {
  IconData icon = Icons.check_circle,
  Duration duration = const Duration(seconds: 2),
}) {
  final messenger = ScaffoldMessenger.of(context);

  // Clear any flash currently showing so they don't stack/queue up.
  messenger.hideCurrentSnackBar();

  messenger.showSnackBar(
    SnackBar(
      duration: duration,
      behavior: SnackBarBehavior.floating,
      backgroundColor: CartzyColors.navy,
      elevation: 6,
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      content: Row(
        children: [
          Icon(icon, color: Colors.white, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 14,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    ),
  );
}
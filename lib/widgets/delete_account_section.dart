import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';

/// Reusable "Delete Account" Danger Zone card for Mobile Profile Settings.
/// Hidden automatically for admin accounts.
class DeleteAccountSection extends StatelessWidget {
  const DeleteAccountSection({super.key});

  @override
  Widget build(BuildContext context) {
    final role = (AuthService.currentUser?['role'] ?? '').toString().toLowerCase();
    if (role == 'admin') return const SizedBox.shrink();

    final isDark = Theme.of(context).brightness == Brightness.dark;
    const danger = Color(0xFFBE123C);

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF2A1419) : const Color(0xFFFFF1F2),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: danger.withValues(alpha: isDark ? 0.45 : 0.28),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'DANGER ZONE',
            style: GoogleFonts.dmSans(
              fontSize: 10.5,
              fontWeight: FontWeight.w800,
              letterSpacing: 0.9,
              color: isDark ? const Color(0xFFFDA4AF) : danger,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Delete account',
            style: GoogleFonts.dmSans(
              fontSize: 15,
              fontWeight: FontWeight.w700,
              color: CartzyColors.textOf(context, light: const Color(0xFF881337)),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Permanently remove your account from Cartzy. This cannot be undone. Accounts with active orders or deliveries cannot be deleted until resolved.',
            style: GoogleFonts.dmSans(
              fontSize: 12.5,
              height: 1.4,
              color: CartzyColors.mutedOf(context, light: const Color(0xFF6B7280)),
            ),
          ),
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            height: 46,
            child: OutlinedButton.icon(
              onPressed: () => _showDeleteDialog(context),
              icon: const Icon(Icons.delete_forever_outlined, size: 18),
              label: Text(
                'DELETE ACCOUNT',
                style: GoogleFonts.dmSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 0.6,
                ),
              ),
              style: OutlinedButton.styleFrom(
                foregroundColor: isDark ? const Color(0xFFFDA4AF) : danger,
                side: BorderSide(color: danger.withValues(alpha: 0.5)),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _showDeleteDialog(BuildContext context) async {
    final passwordCtrl = TextEditingController();
    String? errorMsg;
    bool submitting = false;
    bool obscure = true;

    await showDialog<void>(
      context: context,
      barrierDismissible: !submitting,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (ctx, setDialogState) {
            return AlertDialog(
              title: Text(
                'Delete your account?',
                style: GoogleFonts.dmSans(fontWeight: FontWeight.w800),
              ),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    AuthService.currentUser?['has_custom_password'] == false
                        ? 'You signed in with Google. Please set a Cartzy password in Change Password first, then enter it here to permanently delete your account.'
                        : 'Enter your current password to permanently delete your account. This action cannot be undone.',
                    style: GoogleFonts.dmSans(fontSize: 13, height: 1.4),
                  ),
                  const SizedBox(height: 14),
                  TextField(
                    controller: passwordCtrl,
                    obscureText: obscure,
                    enabled: !submitting,
                    decoration: InputDecoration(
                      labelText: 'Current password',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      suffixIcon: IconButton(
                        icon: Icon(
                          obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                          size: 19,
                        ),
                        onPressed: () => setDialogState(() => obscure = !obscure),
                      ),
                    ),
                  ),
                  if (errorMsg != null) ...[
                    const SizedBox(height: 10),
                    Text(
                      errorMsg!,
                      style: GoogleFonts.dmSans(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFFBE123C),
                      ),
                    ),
                  ],
                ],
              ),
              actions: [
                TextButton(
                  onPressed: submitting ? null : () => Navigator.of(dialogCtx).pop(),
                  child: const Text('Cancel'),
                ),
                ElevatedButton(
                  onPressed: submitting
                      ? null
                      : () async {
                          final password = passwordCtrl.text;
                          if (password.isEmpty) {
                            setDialogState(() => errorMsg = 'Please enter your password.');
                            return;
                          }
                          setDialogState(() {
                            submitting = true;
                            errorMsg = null;
                          });
                          try {
                            await AuthService.deleteAccount(password);
                            if (dialogCtx.mounted) Navigator.of(dialogCtx).pop();
                            if (context.mounted) {
                              Navigator.of(context).popUntil((route) => route.isFirst);
                              showCartzyFlash(
                                context,
                                'Your account has been permanently removed.',
                                icon: Icons.info_outline,
                              );
                            }
                          } catch (e) {
                            final msg = e is ApiException
                                ? e.message
                                : 'Could not delete account. Please try again.';
                            if (ctx.mounted) {
                              setDialogState(() {
                                submitting = false;
                                errorMsg = msg;
                              });
                            }
                          }
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFBE123C),
                    foregroundColor: Colors.white,
                  ),
                  child: submitting
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text('Delete'),
                ),
              ],
            );
          },
        );
      },
    );
    passwordCtrl.dispose();
  }
}

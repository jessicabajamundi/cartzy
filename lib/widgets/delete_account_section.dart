import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';
import 'package:cartzy/auth/forgot_password_screen.dart';

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
    try { await AuthService.refreshCurrentUser(); } catch (_) { /* Server still validates deletion. */ }
    if (!context.mounted) return;
    final result = await showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (_) => const _DeleteAccountDialog(),
    );
    if (!context.mounted) return;
    if (result == 'reset') {
      final saved = await Navigator.of(context).push<String>(MaterialPageRoute(
        builder: (_) => ForgotPasswordScreen(
          role: (AuthService.currentUser?['role'] ?? 'buyer').toString(),
          initialEmail: (AuthService.currentUser?['email'] ?? '').toString(),
          forAccountDeletion: true,
        ),
      ));
      if (saved != null && context.mounted) await _showDeleteDialog(context);
    } else if (result == 'deleted') {
      Navigator.of(context).popUntil((route) => route.isFirst);
      showCartzyFlash(context, 'Your account has been permanently removed.', icon: Icons.info_outline);
    }
  }
}

class _DeleteAccountDialog extends StatefulWidget {
  const _DeleteAccountDialog();
  @override
  State<_DeleteAccountDialog> createState() => _DeleteAccountDialogState();
}

class _DeleteAccountDialogState extends State<_DeleteAccountDialog> {
  final _password = TextEditingController();
  String? _error;
  bool _submitting = false;
  bool _obscure = true;

  @override
  void dispose() { _password.dispose(); super.dispose(); }

  Future<void> _delete() async {
    if (_submitting) return;
    if (_password.text.isEmpty) {
      setState(() => _error = 'Please enter your Cartzy password.');
      return;
    }
    setState(() { _submitting = true; _error = null; });
    try {
      await AuthService.deleteAccount(_password.text);
      if (mounted) {
        Navigator.of(context).pop('deleted');
      }
    } catch (error) {
      if (mounted) {
        setState(() {
          _submitting = false;
          _error = error is ApiException ? error.message : 'Could not delete account. Please try again.';
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) => PopScope(
    canPop: !_submitting,
    child: AlertDialog(
      title: Text('Delete your account?', style: GoogleFonts.dmSans(fontWeight: FontWeight.w800)),
      content: SingleChildScrollView(child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Confirm deletion with your Cartzy password, not your Google/Gmail password. '
            'If you signed in with Google and never set a Cartzy password, use Set/reset Cartzy password below. This action cannot be undone.',
            style: GoogleFonts.dmSans(fontSize: 13, height: 1.4)),
          const SizedBox(height: 14),
          TextButton(
            onPressed: _submitting ? null : () => Navigator.of(context).pop('reset'),
            child: const Text('Set/reset Cartzy password by email'),
          ),
          TextField(
            controller: _password, obscureText: _obscure, enabled: !_submitting,
            autofillHints: const [AutofillHints.password],
            decoration: InputDecoration(
              labelText: 'Cartzy password',
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              suffixIcon: IconButton(
                onPressed: _submitting ? null : () => setState(() => _obscure = !_obscure),
                icon: Icon(_obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined, size: 19),
              ),
            ),
          ),
          if (_error != null) Padding(padding: const EdgeInsets.only(top: 10),
            child: Text(_error!, style: GoogleFonts.dmSans(fontSize: 12.5, fontWeight: FontWeight.w600, color: const Color(0xFFBE123C)))),
        ],
      )),
      actions: [
        TextButton(onPressed: _submitting ? null : () => Navigator.of(context).pop(), child: const Text('Cancel')),
        ElevatedButton(
          onPressed: _submitting ? null : _delete,
          style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFBE123C), foregroundColor: Colors.white),
          child: _submitting
              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
              : const Text('Delete'),
        ),
      ],
    ),
  );
}

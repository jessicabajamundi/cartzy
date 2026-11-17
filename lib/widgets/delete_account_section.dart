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
    final role =
        (AuthService.currentUser?['role'] ?? '').toString().toLowerCase();
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
              color:
                  CartzyColors.textOf(context, light: const Color(0xFF881337)),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Permanently remove your account from Cartzy. This cannot be undone. Accounts with active orders or deliveries cannot be deleted until resolved.',
            style: GoogleFonts.dmSans(
              fontSize: 12.5,
              height: 1.4,
              color:
                  CartzyColors.mutedOf(context, light: const Color(0xFF6B7280)),
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
    final deleted = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (_) => const _DeleteAccountDialog(),
    );
    if (deleted == true && context.mounted) {
      Navigator.of(context).popUntil((route) => route.isFirst);
      showCartzyFlash(context, 'Your account has been permanently removed.',
          icon: Icons.info_outline);
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
  bool _confirming = false;
  bool _obscure = true;
  bool get _busy => _submitting || _confirming;

  @override
  void dispose() {
    _password.dispose();
    super.dispose();
  }

  Future<void> _delete() async {
    if (_busy) return;
    if (_password.text.isEmpty) {
      setState(() => _error = 'Please enter your Cartzy password.');
      return;
    }
    setState(() {
      _confirming = true;
      _error = null;
      _obscure = true;
    });
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (confirmationContext) => AlertDialog(
        title: const Text('Permanently delete your account?'),
        content: const Text(
            'Are you sure you want to permanently delete your Cartzy account? This action cannot be undone. You will lose access to this account.'),
        actions: [
          TextButton(
              autofocus: true,
              onPressed: () => Navigator.of(confirmationContext).pop(false),
              child: const Text('Cancel')),
          FilledButton(
            style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFFBE123C),
                foregroundColor: Colors.white),
            onPressed: () => Navigator.of(confirmationContext).pop(true),
            child: const Text('Yes, delete my account'),
          ),
        ],
      ),
    );
    if (!mounted) return;
    setState(() {
      _confirming = false;
      _submitting = confirmed == true;
    });
    if (confirmed != true) return;
    try {
      await AuthService.deleteAccount(_password.text);
      if (mounted) Navigator.of(context).pop(true);
    } catch (error) {
      if (mounted) {
        setState(() {
          _submitting = false;
          _error = error is ApiException
              ? error.message
              : 'Could not delete account. Please try again.';
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) => PopScope(
        canPop: !_busy,
        child: AlertDialog(
          title: Text('Delete your account?',
              style: GoogleFonts.dmSans(fontWeight: FontWeight.w800)),
          content: SingleChildScrollView(
              child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                  AuthService.currentUser?['has_custom_password'] == false
                      ? 'You signed in with Google. Set a Cartzy password in Change Password first, then enter it here. Do not enter your Google password.'
                      : 'Enter your Cartzy password to continue. You will be asked to confirm before your account is permanently deleted.',
                  style: GoogleFonts.dmSans(fontSize: 13, height: 1.4)),
              const SizedBox(height: 14),
              const Text('Confirm with your Cartzy password',
                  style: TextStyle(
                      fontWeight: FontWeight.w600,
                      decoration: TextDecoration.underline)),
              const SizedBox(height: 8),
              TextField(
                controller: _password,
                obscureText: _obscure,
                enabled: !_busy,
                autofillHints: const [AutofillHints.password],
                decoration: InputDecoration(
                  labelText: 'Cartzy password',
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10)),
                  suffixIcon: IconButton(
                    tooltip: _obscure
                        ? 'Show Cartzy password'
                        : 'Hide Cartzy password',
                    onPressed: _busy
                        ? null
                        : () => setState(() => _obscure = !_obscure),
                    icon: Icon(
                        _obscure
                            ? Icons.visibility_outlined
                            : Icons.visibility_off_outlined,
                        size: 20),
                  ),
                ),
              ),
              if (_error != null)
                Padding(
                    padding: const EdgeInsets.only(top: 10),
                    child: Text(_error!,
                        style: GoogleFonts.dmSans(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFFBE123C)))),
            ],
          )),
          actions: [
            TextButton(
                onPressed:
                    _busy ? null : () => Navigator.of(context).pop(false),
                child: const Text('Cancel')),
            ElevatedButton(
              onPressed: _busy ? null : _delete,
              style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFFBE123C),
                  foregroundColor: Colors.white),
              child: _submitting
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(
                          strokeWidth: 2, color: Colors.white))
                  : const Text('Delete'),
            ),
          ],
        ),
      );
}

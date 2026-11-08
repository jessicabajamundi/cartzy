import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/barcode_scanner_page.dart';

/// The rider scans a waybill barcode or QR code, checks the parcel, and then
/// accepts or declines it.
///
/// Scanning only looks the parcel up. Accepting hands it to the rider (picked
/// up from the seller, or out for delivery from the hub) and notifies the
/// seller, the buyer and the logistics hub. Declining leaves it where it is.
class RiderParcelScan {
  static Future<void> scanAndReport(BuildContext context) async {
    final code = await Navigator.of(context).push<String>(
      MaterialPageRoute(
        builder: (_) => const BarcodeScannerPage(title: 'Scan waybill barcode or QR'),
      ),
    );
    if (code == null || code.trim().isEmpty || !context.mounted) return;
    await reportCode(context, code);
  }

  /// Looks up a scanned [code], lets the rider accept or decline it, and shows the outcome.
  static Future<void> reportCode(BuildContext context, String code) async {
    final scanned = await _call(context, 'scan', code);
    if (!context.mounted) return;

    var outcome = scanned;
    if (scanned.kind == _Kind.decide) {
      final choice = await _sheet<String>(context, _DecisionSheet(result: scanned));
      if (choice == null || !context.mounted) return;

      if (choice == 'accept') {
        outcome = await _call(context, 'accept', code);
      } else {
        final reason = await _askDeclineReason(context);
        if (reason == null || !context.mounted) return;
        outcome = await _call(context, 'decline', code, reason: reason);
      }
      if (!context.mounted) return;
    }

    final scanAgain = await _sheet<bool>(context, _OutcomeSheet(result: outcome));
    if (scanAgain == true && context.mounted) {
      await scanAndReport(context);
    }
  }

  static Future<T?> _sheet<T>(BuildContext context, Widget sheet) => showModalBottomSheet<T>(
        context: context,
        isScrollControlled: true,
        backgroundColor: Colors.transparent,
        builder: (_) => sheet,
      );

  /// Returns the reason ('' when none is given), or null if the rider changes their mind.
  static Future<String?> _askDeclineReason(BuildContext context) {
    final controller = TextEditingController();
    return showDialog<String>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text('Decline this parcel?',
            style: GoogleFonts.playfairDisplay(fontWeight: FontWeight.w700, color: CartzyColors.brand900)),
        content: TextField(
          controller: controller,
          maxLength: 200,
          decoration: const InputDecoration(hintText: 'Reason (optional), e.g. vehicle problem'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogContext).pop(),
            child: Text('Back', style: GoogleFonts.dmSans(color: CartzyColors.gray, fontWeight: FontWeight.w600)),
          ),
          TextButton(
            onPressed: () => Navigator.of(dialogContext).pop(controller.text.trim()),
            child: Text('Decline', style: GoogleFonts.dmSans(color: const Color(0xFFC0392B), fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );
  }

  static Future<_ScanResult> _call(BuildContext context, String action, String code, {String? reason}) async {
    showDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator(color: CartzyColors.burgundy)),
    );
    try {
      final response = await ApiService.post(
        '/rider/parcels/$action',
        {'code': code, if (reason != null && reason.isNotEmpty) 'reason': reason},
        token: AuthService.token,
      );
      return _ScanResult.fromResponse(action, response);
    } on ApiException catch (e) {
      return _ScanResult.refused(e.message, code);
    } catch (_) {
      return _ScanResult.refused("Couldn't reach the server. Check your connection and scan again.", code);
    } finally {
      if (context.mounted) Navigator.of(context, rootNavigator: true).pop();
    }
  }
}

enum _Kind { decide, accepted, already, declined, refused }

class _ScanResult {
  final _Kind kind;
  final String message;
  final Map<String, dynamic> parcel;
  final String scannedCode;

  const _ScanResult(this.kind, this.message, this.parcel, this.scannedCode);

  factory _ScanResult.fromResponse(String action, Map<String, dynamic> json) {
    final parcel = Map<String, dynamic>.from(json['parcel'] as Map? ?? const {});
    final message = json['message']?.toString() ?? '';
    final kind = json['already'] == true
        ? _Kind.already
        : switch (action) {
            'scan' => _Kind.decide,
            'accept' => _Kind.accepted,
            _ => _Kind.declined,
          };
    return _ScanResult(kind, message, parcel, parcel['tracking_code']?.toString() ?? '');
  }

  factory _ScanResult.refused(String message, String code) => _ScanResult(_Kind.refused, message, const {}, code);
}

/// Right after scanning: the parcel, with Accept and Decline.
class _DecisionSheet extends StatelessWidget {
  final _ScanResult result;

  const _DecisionSheet({required this.result});

  @override
  Widget build(BuildContext context) {
    return _SheetFrame(
      icon: Icons.inventory_2_outlined,
      accent: CartzyColors.brandDeep,
      title: 'Accept this parcel?',
      message: 'Check the parcel details. Accepting puts it in your care and notifies the seller, buyer and hub.',
      parcel: result.parcel,
      actions: [
        Expanded(
          child: OutlinedButton(
            onPressed: () => Navigator.of(context).pop('decline'),
            style: OutlinedButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
              side: const BorderSide(color: Color(0xFFE3B5B0)),
              foregroundColor: const Color(0xFFC0392B),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
            child: Text('Decline', style: GoogleFonts.dmSans(fontWeight: FontWeight.w700)),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: FilledButton.icon(
            onPressed: () => Navigator.of(context).pop('accept'),
            icon: const Icon(Icons.check_rounded, size: 18),
            label: Text('Accept', style: GoogleFonts.dmSans(fontWeight: FontWeight.w700)),
            style: FilledButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
              backgroundColor: const Color(0xFF2E7D32),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
          ),
        ),
      ],
    );
  }
}

/// After accepting or declining, or when the scan was refused.
class _OutcomeSheet extends StatelessWidget {
  final _ScanResult result;

  const _OutcomeSheet({required this.result});

  @override
  Widget build(BuildContext context) {
    final (title, icon, accent) = switch (result.kind) {
      _Kind.accepted => ('Parcel is now with you', Icons.check_rounded, const Color(0xFF2E7D32)),
      _Kind.already => ('Already with you', Icons.check_rounded, const Color(0xFF2E7D32)),
      _Kind.declined => ('Parcel declined', Icons.block_rounded, CartzyColors.brandDeep),
      _ => ("Can't take this parcel", Icons.error_outline_rounded, const Color(0xFFC0392B)),
    };

    return _SheetFrame(
      icon: icon,
      accent: accent,
      title: title,
      message: result.message,
      parcel: result.kind == _Kind.accepted || result.kind == _Kind.already ? result.parcel : const {},
      footnote: result.kind == _Kind.refused && result.scannedCode.isNotEmpty ? 'Scanned: ${result.scannedCode}' : null,
      actions: [
        Expanded(
          child: OutlinedButton(
            onPressed: () => Navigator.of(context).pop(false),
            style: OutlinedButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
              side: const BorderSide(color: CartzyColors.border),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
            child: Text('Done', style: GoogleFonts.dmSans(fontWeight: FontWeight.w700, color: CartzyColors.brandDark)),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: FilledButton.icon(
            onPressed: () => Navigator.of(context).pop(true),
            icon: const Icon(Icons.qr_code_scanner, size: 18),
            label: Text('Scan another', style: GoogleFonts.dmSans(fontWeight: FontWeight.w700)),
            style: FilledButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
              backgroundColor: CartzyColors.brandDark,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
          ),
        ),
      ],
    );
  }
}

class _SheetFrame extends StatelessWidget {
  final IconData icon;
  final Color accent;
  final String title;
  final String message;
  final Map<String, dynamic> parcel;
  final String? footnote;
  final List<Widget> actions;

  const _SheetFrame({
    required this.icon,
    required this.accent,
    required this.title,
    required this.message,
    required this.parcel,
    required this.actions,
    this.footnote,
  });

  @override
  Widget build(BuildContext context) {
    final codAmount = (parcel['cod_amount'] as num?)?.toDouble() ?? 0;

    return Container(
      padding: EdgeInsets.fromLTRB(22, 14, 22, 22 + MediaQuery.of(context).padding.bottom),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(color: CartzyColors.border, borderRadius: BorderRadius.circular(2)),
            ),
          ),
          const SizedBox(height: 18),
          Row(
            children: [
              CircleAvatar(
                radius: 22,
                backgroundColor: accent.withValues(alpha: 0.12),
                child: Icon(icon, color: accent),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  title,
                  style: GoogleFonts.playfairDisplay(fontSize: 20, fontWeight: FontWeight.w700, color: CartzyColors.brand900),
                ),
              ),
            ],
          ),
          if (message.isNotEmpty) ...[
            const SizedBox(height: 12),
            Text(message, style: GoogleFonts.dmSans(fontSize: 14, height: 1.45, color: CartzyColors.brandDark)),
          ],
          if (footnote != null) ...[
            const SizedBox(height: 8),
            Text(footnote!, style: GoogleFonts.dmSans(fontSize: 12, color: CartzyColors.gray)),
          ],
          if (parcel.isNotEmpty) ...[
            const SizedBox(height: 16),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: CartzyColors.brand50Lavender,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: CartzyColors.border),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _row('Tracking', parcel['tracking_code']),
                  _row('Order', parcel['order_reference']),
                  _row('From', parcel['seller_name']),
                  _row('To', [parcel['recipient_name'], parcel['recipient_address']].whereType<String>().join(' · ')),
                  _row('Items', '${parcel['items_count'] ?? 0}'),
                  _row('Payment', parcel['is_cod'] == true ? 'COD · collect ₱${codAmount.toStringAsFixed(2)}' : 'Prepaid'),
                ],
              ),
            ),
          ],
          const SizedBox(height: 20),
          Row(children: actions),
        ],
      ),
    );
  }

  Widget _row(String label, Object? value) {
    final text = value?.toString() ?? '';
    if (text.isEmpty) return const SizedBox.shrink();
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 70, child: Text(label, style: GoogleFonts.dmSans(fontSize: 12.5, color: CartzyColors.gray))),
          Expanded(
            child: Text(text, style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w600, color: CartzyColors.brand900)),
          ),
        ],
      ),
    );
  }
}

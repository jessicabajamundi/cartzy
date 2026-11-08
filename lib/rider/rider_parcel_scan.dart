import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/barcode_scanner_page.dart';

/// The rider scans a waybill barcode or QR code to take the parcel.
///
/// The server records it (picked up from the seller, or out for delivery from
/// the hub) and notifies the seller, the buyer and the logistics hub. The rider
/// then sees what happened, or why the scan was refused.
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

  /// Sends a scanned [code] to the server and shows the rider the result.
  static Future<void> reportCode(BuildContext context, String code) async {
    final result = await _send(context, code);
    if (!context.mounted) return;

    final scanAgain = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _ScanResultSheet(result: result),
    );
    if (scanAgain == true && context.mounted) {
      await scanAndReport(context);
    }
  }

  static Future<_ScanResult> _send(BuildContext context, String code) async {
    showDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(
        child: CircularProgressIndicator(color: CartzyColors.burgundy),
      ),
    );
    try {
      final response = await ApiService.post(
        '/rider/parcels/scan',
        {'code': code},
        token: AuthService.token,
      );
      return _ScanResult.ok(response);
    } on ApiException catch (e) {
      return _ScanResult.refused(e.message, code);
    } catch (_) {
      return _ScanResult.refused(
        "Couldn't reach the server. Check your connection and scan again.",
        code,
      );
    } finally {
      if (context.mounted) Navigator.of(context, rootNavigator: true).pop();
    }
  }
}

class _ScanResult {
  final bool ok;
  final bool already;
  final String message;
  final Map<String, dynamic> parcel;
  final String scannedCode;

  const _ScanResult._(this.ok, this.already, this.message, this.parcel, this.scannedCode);

  factory _ScanResult.ok(Map<String, dynamic> json) => _ScanResult._(
        true,
        json['already'] == true,
        json['message']?.toString() ?? 'Parcel scanned.',
        Map<String, dynamic>.from(json['parcel'] as Map? ?? const {}),
        json['parcel']?['tracking_code']?.toString() ?? '',
      );

  factory _ScanResult.refused(String message, String code) =>
      _ScanResult._(false, false, message, const {}, code);
}

class _ScanResultSheet extends StatelessWidget {
  final _ScanResult result;

  const _ScanResultSheet({required this.result});

  @override
  Widget build(BuildContext context) {
    final p = result.parcel;
    final accent = result.ok ? const Color(0xFF2E7D32) : const Color(0xFFC0392B);
    final title = !result.ok
        ? "Can't take this parcel"
        : result.already
            ? 'Already with you'
            : 'Parcel is now with you';
    final codAmount = (p['cod_amount'] as num?)?.toDouble() ?? 0;

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
                child: Icon(result.ok ? Icons.check_rounded : Icons.error_outline_rounded, color: accent),
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
          const SizedBox(height: 12),
          Text(
            result.message,
            style: GoogleFonts.dmSans(fontSize: 14, height: 1.45, color: CartzyColors.brandDark),
          ),
          if (!result.ok && result.scannedCode.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text('Scanned: ${result.scannedCode}', style: GoogleFonts.dmSans(fontSize: 12, color: CartzyColors.gray)),
          ],
          if (result.ok) ...[
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
                  _row('Tracking', p['tracking_code']),
                  _row('Order', p['order_reference']),
                  _row('From', p['seller_name']),
                  _row('To', [p['recipient_name'], p['recipient_address']].whereType<String>().join(' · ')),
                  _row('Items', '${p['items_count'] ?? 0}'),
                  _row('Payment', p['is_cod'] == true ? 'COD · collect ₱${codAmount.toStringAsFixed(2)}' : 'Prepaid'),
                ],
              ),
            ),
          ],
          const SizedBox(height: 20),
          Row(
            children: [
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
          ),
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
          SizedBox(
            width: 70,
            child: Text(label, style: GoogleFonts.dmSans(fontSize: 12.5, color: CartzyColors.gray)),
          ),
          Expanded(
            child: Text(text, style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w600, color: CartzyColors.brand900)),
          ),
        ],
      ),
    );
  }
}

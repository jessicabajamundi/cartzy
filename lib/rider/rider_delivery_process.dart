import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/rider_delivery_service.dart';

/// Hand a parcel to the buyer. The rider scans (or types) the parcel's waybill,
/// then confirms; the server checks the code and saves "delivered".
/// Pops true when delivered, false when marked as not delivered.
class RiderDeliverProcess extends StatefulWidget {
  final RiderDelivery delivery;
  final String? token;

  /// Test hooks; the app uses [RiderDeliveryService].
  final Future<String> Function(RiderDelivery delivery, String code, String receivedBy, String note)? markDelivered;
  final Future<String> Function(RiderDelivery delivery, String reason)? markFailed;
  final bool showCamera;

  const RiderDeliverProcess({
    super.key,
    required this.delivery,
    this.token,
    this.markDelivered,
    this.markFailed,
    this.showCamera = true,
  });

  @override
  State<RiderDeliverProcess> createState() => _RiderDeliverProcessState();
}

typedef RiderDeliveryProcess = RiderDeliverProcess;

class _RiderDeliverProcessState extends State<RiderDeliverProcess> {
  late final TextEditingController _trackingController;
  late final TextEditingController _receiverController;
  final TextEditingController _noteController = TextEditingController();
  bool _isVerified = false;
  bool _isCompleting = false;
  bool _barcodeHandled = false;
  String? _verificationMessage;
  String? _error;

  RiderDelivery get _d => widget.delivery;
  String get _trackingNumber => _d.trackingCode;

  @override
  void initState() {
    super.initState();
    _trackingController = TextEditingController();
    _receiverController = TextEditingController(text: _d.recipientName ?? '');
  }

  @override
  void dispose() {
    _trackingController.dispose();
    _receiverController.dispose();
    _noteController.dispose();
    super.dispose();
  }

  static String _plain(String v) => v.toUpperCase().replaceAll(RegExp(r'[^A-Z0-9]'), '');

  void _verifyTracking(String value) {
    // A QR code may hold a link ending in the code, so look for the code inside it.
    final entered = _plain(value);
    final expected = _plain(_trackingNumber);
    final reference = _d.orderReference == null ? null : _plain(_d.orderReference!);
    final verified = expected.isNotEmpty &&
        (entered == expected || entered.endsWith(expected) || (reference != null && (entered == reference || entered.endsWith(reference))));
    setState(() {
      _error = null;
      _isVerified = verified;
      _verificationMessage = verified
          ? 'Waybill verified for this delivery.'
          : "That code doesn't match this parcel. Scan the waybill on it.";
    });
  }

  void _onBarcodeDetected(BarcodeCapture capture) {
    if (_barcodeHandled || capture.barcodes.isEmpty) return;
    final value = capture.barcodes.first.rawValue;
    if (value == null || value.isEmpty) return;
    _trackingController.text = value;
    _verifyTracking(value);
    // Keep scanning after a wrong code; stop once the right parcel is found.
    _barcodeHandled = _isVerified;
  }

  Future<void> _completeDelivery() async {
    if (!_isVerified || _isCompleting) return;
    setState(() {
      _isCompleting = true;
      _error = null;
    });
    try {
      final save = widget.markDelivered ??
          (d, code, by, note) => RiderDeliveryService.markDelivered(d, code: code, receivedBy: by, note: note);
      await save(_d, _trackingController.text.trim(), _receiverController.text, _noteController.text);
      if (!mounted) return;
      Navigator.of(context).pop(true);
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isCompleting = false;
        _error = e is ApiException ? e.message : "Couldn't reach the server, so the delivery wasn't saved. Try again.";
      });
    }
  }

  static const _failReasons = ['Buyer not home', 'Buyer refused the parcel', 'Wrong or incomplete address', "Can't reach the buyer's phone"];

  Future<void> _markNotDelivered() async {
    final reason = await showDialog<String>(
      context: context,
      builder: (context) => SimpleDialog(
        title: Text("Why couldn't you deliver it?", style: GoogleFonts.dmSans(fontSize: 15, fontWeight: FontWeight.w700)),
        children: [
          for (final r in _failReasons)
            SimpleDialogOption(
              onPressed: () => Navigator.of(context).pop(r),
              child: Text(r, style: GoogleFonts.dmSans(fontSize: 13)),
            ),
          SimpleDialogOption(
            onPressed: () => Navigator.of(context).pop(),
            child: Text('Cancel', style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF8A7780))),
          ),
        ],
      ),
    );
    if (reason == null || !mounted) return;
    setState(() {
      _isCompleting = true;
      _error = null;
    });
    try {
      await (widget.markFailed ?? RiderDeliveryService.markFailed)(_d, reason);
      if (!mounted) return;
      Navigator.of(context).pop(false);
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isCompleting = false;
        _error = e is ApiException ? e.message : "Couldn't reach the server. Nothing was saved; try again.";
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final isCod = _d.isCod;
    return Scaffold(
      backgroundColor: const Color(0xFFEEE9EB),
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            final mobile = constraints.maxWidth < 600;
            return SingleChildScrollView(
              padding: EdgeInsets.symmetric(horizontal: mobile ? 14 : 24, vertical: mobile ? 18 : 28),
              child: Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 430),
                  child: Container(
                    clipBehavior: Clip.antiAlias,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: const Color(0xFFE9E1E5)),
                      boxShadow: const [BoxShadow(color: Color(0x1F241C2D), blurRadius: 22, offset: Offset(0, 8))],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        _buildHeader(),
                        Padding(
                          padding: const EdgeInsets.all(14),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              if (widget.showCamera) ...[
                                _buildScannerPreview(),
                                const SizedBox(height: 12),
                              ],
                              _buildTrackingLookup(),
                              if (_verificationMessage != null) ...[
                                const SizedBox(height: 5),
                                Text(
                                  _verificationMessage!,
                                  style: GoogleFonts.dmSans(
                                    fontSize: 10,
                                    color: _isVerified ? const Color(0xFF168456) : const Color(0xFFB54747),
                                  ),
                                ),
                              ],
                              const SizedBox(height: 10),
                              _buildParcelDetails(isCod),
                              const SizedBox(height: 10),
                              _buildLabeledInput('Received By (Name / Relation):', _receiverController),
                              const SizedBox(height: 8),
                              _buildLabeledInput(
                                'Rider Delivery Note (Optional):',
                                _noteController,
                                hint: 'e.g. Package received in good condition',
                                maxLines: 1,
                              ),
                              const SizedBox(height: 8),
                              SizedBox(
                                width: double.infinity,
                                height: 42,
                                child: ElevatedButton.icon(
                                  onPressed: _isVerified && !_isCompleting ? _completeDelivery : null,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF5A4A69),
                                    foregroundColor: Colors.white,
                                    disabledBackgroundColor: const Color(0xFFBDB5C3),
                                    elevation: 0,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(9)),
                                  ),
                                  icon: _isCompleting
                                      ? const SizedBox(width: 15, height: 15, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                      : const Icon(Icons.check, size: 15),
                                  label: Text(
                                    _isCompleting ? 'SAVING…' : (_isVerified ? 'CONFIRM DELIVERY (NOTIFY SELLER & BUYER)' : 'SCAN THE WAYBILL FIRST'),
                                    style: GoogleFonts.dmSans(fontSize: 10, fontWeight: FontWeight.w700),
                                  ),
                                ),
                              ),
                              if (_error != null) ...[
                                const SizedBox(height: 8),
                                Text(_error!, style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFFB54747))),
                              ],
                              const SizedBox(height: 4),
                              Center(
                                child: TextButton(
                                  onPressed: _isCompleting ? null : _markNotDelivered,
                                  child: Text("Couldn't deliver it",
                                      style: GoogleFonts.dmSans(fontSize: 11, fontWeight: FontWeight.w700, color: const Color(0xFFB54747))),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildHeader() => Container(
        padding: const EdgeInsets.fromLTRB(14, 12, 10, 12),
        decoration: const BoxDecoration(
          gradient: LinearGradient(colors: [Color(0xFF5A4A69), Color(0xFF936762)]),
        ),
        child: Row(
          children: [
            Container(
              width: 30,
              height: 30,
              decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.18), borderRadius: BorderRadius.circular(8)),
              child: const Icon(Icons.qr_code_scanner, size: 17, color: Colors.white),
            ),
            const SizedBox(width: 9),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Optical Waybill Scanner', style: GoogleFonts.cormorantGaramond(fontSize: 15, fontWeight: FontWeight.w700, color: Colors.white)),
                  Text('Scan package barcode or enter tracking number', style: GoogleFonts.dmSans(fontSize: 9, color: Colors.white.withValues(alpha: 0.9))),
                ],
              ),
            ),
            IconButton(
              visualDensity: VisualDensity.compact,
              tooltip: 'Close',
              onPressed: () => Navigator.of(context).pop(),
              icon: const Icon(Icons.close, size: 17, color: Colors.white),
              style: IconButton.styleFrom(backgroundColor: Colors.white.withValues(alpha: 0.16)),
            ),
          ],
        ),
      );

  Widget _buildScannerPreview() => ClipRRect(
        borderRadius: BorderRadius.circular(12),
        child: SizedBox(
          height: 178,
          width: double.infinity,
          child: Stack(
            fit: StackFit.expand,
            children: [
              MobileScanner(onDetect: _onBarcodeDetected),
              IgnorePointer(
                child: DecoratedBox(
                  decoration: BoxDecoration(color: const Color(0xFF101827).withValues(alpha: 0.16)),
                  child: Center(
                    child: Container(
                      width: 128,
                      height: 66,
                      decoration: BoxDecoration(
                        color: const Color(0x66101827),
                        border: Border.all(color: const Color(0xFFDAB6A9), width: 1.2),
                        borderRadius: BorderRadius.circular(7),
                      ),
                      child: Center(
                        child: Container(height: 2, color: const Color(0xFFE6B5A8)),
                      ),
                    ),
                  ),
                ),
              ),
              Positioned(
                left: 8,
                right: 8,
                bottom: 8,
                child: Text(
                  'Point the camera at the waybill barcode or QR code',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.dmSans(fontSize: 8, color: Colors.white, shadows: const [Shadow(blurRadius: 4, color: Colors.black)]),
                ),
              ),
            ],
          ),
        ),
      );

  Widget _buildTrackingLookup() => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionLabel('ENTER TRACKING NUMBER'),
          const SizedBox(height: 4),
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _trackingController,
                  textCapitalization: TextCapitalization.characters,
                  decoration: _inputDecoration(_trackingNumber),
                  style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFF352B3D)),
                  onSubmitted: _verifyTracking,
                ),
              ),
              const SizedBox(width: 7),
              SizedBox(
                height: 38,
                child: ElevatedButton(
                  onPressed: () => _verifyTracking(_trackingController.text),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF5A4A69),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(horizontal: 13),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  child: Text('Lookup', style: GoogleFonts.dmSans(fontSize: 10, fontWeight: FontWeight.w700)),
                ),
              ),
            ],
          ),
        ],
      );

  Widget _buildParcelDetails(bool isCod) => Container(
        width: double.infinity,
        padding: const EdgeInsets.all(11),
        decoration: BoxDecoration(
          color: const Color(0xFFFBF9F8),
          border: Border.all(color: const Color(0xFFEDE5E0)),
          borderRadius: BorderRadius.circular(11),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                _statusBadge(_isVerified ? 'PARCEL VERIFIED' : 'AWAITING VERIFICATION', _isVerified),
                const Spacer(),
                _statusBadge(
                  isCod ? 'COLLECT COD ₱${_d.codAmount.toStringAsFixed(2)}' : 'PAID ONLINE (₱0.00)',
                  !isCod,
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(_d.recipientName ?? 'Buyer', style: GoogleFonts.dmSans(fontSize: 11, fontWeight: FontWeight.w700, color: const Color(0xFF352B3D))),
            const SizedBox(height: 2),
            Text(_d.recipientAddress ?? 'No address on the order', style: GoogleFonts.dmSans(fontSize: 9, color: const Color(0xFF67616D))),
            const SizedBox(height: 2),
            Text('Tracking: $_trackingNumber', style: GoogleFonts.dmSans(fontSize: 9, fontWeight: FontWeight.w600, color: const Color(0xFF67616D))),
            Text('Items: ${_d.itemsSummary ?? _d.itemsCount}', maxLines: 2, overflow: TextOverflow.ellipsis, style: GoogleFonts.dmSans(fontSize: 9, color: const Color(0xFF67616D))),
            const Padding(padding: EdgeInsets.symmetric(vertical: 8), child: Divider(height: 1, color: Color(0xFFE8E1E5))),
          ],
        ),
      );

  Widget _buildLabeledInput(String label, TextEditingController controller, {String? hint, int maxLines = 1}) => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _sectionLabel(label),
          const SizedBox(height: 4),
          TextField(
            controller: controller,
            maxLines: maxLines,
            decoration: _inputDecoration(hint ?? ''),
            style: GoogleFonts.dmSans(fontSize: 10, color: const Color(0xFF352B3D)),
          ),
        ],
      );

  Widget _sectionLabel(String text) => Text(
        text,
        style: GoogleFonts.dmSans(fontSize: 9, fontWeight: FontWeight.w700, color: const Color(0xFF514B5D)),
      );

  InputDecoration _inputDecoration(String hint) => InputDecoration(
        hintText: hint,
        hintStyle: GoogleFonts.dmSans(fontSize: 9, color: const Color(0xFFA49BA4)),
        filled: true,
        fillColor: Colors.white,
        isDense: true,
        contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 9),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(7), borderSide: const BorderSide(color: Color(0xFFDCD6E2))),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(7), borderSide: const BorderSide(color: Color(0xFFDCD6E2))),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(7), borderSide: const BorderSide(color: Color(0xFF746382), width: 1.3)),
      );

  Widget _statusBadge(String text, bool positive) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
        decoration: BoxDecoration(
          color: positive ? const Color(0xFFE1F8EF) : const Color(0xFFF1ECF3),
          borderRadius: BorderRadius.circular(4),
        ),
        child: Text(
          text,
          style: GoogleFonts.dmSans(
            fontSize: 7,
            fontWeight: FontWeight.w700,
            color: positive ? const Color(0xFF13784F) : const Color(0xFF5A4A69),
          ),
        ),
      );
}

import 'dart:io';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import 'package:path_provider/path_provider.dart';
import 'package:qr_flutter/qr_flutter.dart';

import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/order_service.dart';

class GCashCheckoutSheet extends StatefulWidget {
  final double totalAmount;
  final List<int> productIds;
  final Future<void> Function(String gcashReference, String? receiptPath) onConfirmOrder;

  const GCashCheckoutSheet({
    super.key,
    required this.totalAmount,
    required this.productIds,
    required this.onConfirmOrder,
  });

  static Future<void> show(
    BuildContext context, {
    required double totalAmount,
    required List<int> productIds,
    required Future<void> Function(String gcashReference, String? receiptPath) onConfirmOrder,
  }) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(ctx).viewInsets.bottom,
        ),
        child: GCashCheckoutSheet(
          totalAmount: totalAmount,
          productIds: productIds,
          onConfirmOrder: onConfirmOrder,
        ),
      ),
    );
  }

  @override
  State<GCashCheckoutSheet> createState() => _GCashCheckoutSheetState();
}

class _GCashCheckoutSheetState extends State<GCashCheckoutSheet> {
  final GlobalKey _qrRepaintKey = GlobalKey();
  final TextEditingController _refController = TextEditingController();
  final ImagePicker _picker = ImagePicker();

  bool _loading = true;
  bool _submitting = false;
  bool _savingQr = false;
  String? _errorMessage;
  File? _receiptFile;

  String _storeName = 'Seller Store';
  String _gcashName = 'Seller GCash';
  String _gcashNumber = '0917-555-2278';
  String? _gcashQrUrl;
  bool _hasCustomQr = false;

  @override
  void initState() {
    super.initState();
    _loadSellerPaymentInfo();
  }

  @override
  void dispose() {
    _refController.dispose();
    super.dispose();
  }

  Future<void> _loadSellerPaymentInfo() async {
    try {
      final info = await OrderService.fetchSellerPaymentInfo(
        productIds: widget.productIds,
      );
      if (!mounted) return;
      setState(() {
        _storeName = info['store_name']?.toString() ?? 'Official Store';
        _gcashName = info['gcash_name']?.toString() ?? 'Store Merchant';
        _gcashNumber = info['gcash_number']?.toString() ?? '0917-555-2278';
        _gcashQrUrl = info['gcash_qr_url']?.toString();
        _hasCustomQr = info['has_custom_qr'] == true;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loading = false;
      });
    }
  }

  void _copyToClipboard(String text, String label) {
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('$label copied to clipboard'),
        duration: const Duration(seconds: 2),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  Future<void> _pasteFromClipboard() async {
    final data = await Clipboard.getData(Clipboard.kTextPlain);
    if (data?.text != null && data!.text!.trim().isNotEmpty) {
      setState(() {
        _refController.text = data.text!.trim();
        _errorMessage = null;
      });
    }
  }

  Future<void> _pickReceipt(ImageSource source) async {
    try {
      final picked = await _picker.pickImage(
        source: source,
        imageQuality: 85,
        maxWidth: 1600,
      );
      if (picked != null) {
        setState(() {
          _receiptFile = File(picked.path);
          _errorMessage = null;
        });
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Could not open camera/gallery: $e'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  void _showImageSourcePicker() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Upload Payment Receipt',
                style: GoogleFonts.dmSans(
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                  color: const Color(0xFF282133),
                ),
              ),
              const SizedBox(height: 14),
              ListTile(
                leading: const CircleAvatar(
                  backgroundColor: Color(0xFFEBF5FF),
                  child: Icon(Icons.photo_library_outlined, color: Color(0xFF007DFE)),
                ),
                title: Text('Choose from Gallery', style: GoogleFonts.dmSans(fontWeight: FontWeight.w600)),
                subtitle: Text('Select saved GCash receipt screenshot', style: GoogleFonts.dmSans(fontSize: 12)),
                onTap: () {
                  Navigator.of(ctx).pop();
                  _pickReceipt(ImageSource.gallery);
                },
              ),
              ListTile(
                leading: const CircleAvatar(
                  backgroundColor: Color(0xFFEBF5FF),
                  child: Icon(Icons.camera_alt_outlined, color: Color(0xFF007DFE)),
                ),
                title: Text('Take a Photo', style: GoogleFonts.dmSans(fontWeight: FontWeight.w600)),
                subtitle: Text('Capture receipt using device camera', style: GoogleFonts.dmSans(fontSize: 12)),
                onTap: () {
                  Navigator.of(ctx).pop();
                  _pickReceipt(ImageSource.camera);
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _downloadQrCode() async {
    if (_savingQr) return;
    setState(() => _savingQr = true);

    try {
      Uint8List? pngBytes;

      if (_hasCustomQr && _gcashQrUrl != null && _gcashQrUrl!.isNotEmpty) {
        try {
          final res = await http.get(Uri.parse(ApiService.normalizeImageUrl(_gcashQrUrl)));
          if (res.statusCode == 200 && res.bodyBytes.isNotEmpty) {
            pngBytes = res.bodyBytes;
          }
        } catch (_) {}
      }

      if (pngBytes == null) {
        final boundary = _qrRepaintKey.currentContext?.findRenderObject() as RenderRepaintBoundary?;
        if (boundary != null) {
          final image = await boundary.toImage(pixelRatio: 3.0);
          final byteData = await image.toByteData(format: ui.ImageByteFormat.png);
          if (byteData != null) {
            pngBytes = byteData.buffer.asUint8List();
          }
        }
      }

      if (pngBytes == null) {
        throw Exception('Failed to generate QR image');
      }

      final fileName = 'Cartzy_GCash_QR_${DateTime.now().millisecondsSinceEpoch}.png';

      final downloadDir = Directory('/storage/emulated/0/Download');
      if (await downloadDir.exists()) {
        final file = File('${downloadDir.path}/$fileName');
        await file.writeAsBytes(pngBytes);
      } else {
        final dir = await getExternalStorageDirectory() ?? await getApplicationDocumentsDirectory();
        final file = File('${dir.path}/$fileName');
        await file.writeAsBytes(pngBytes);
      }

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            backgroundColor: const Color(0xFF16A34A),
            behavior: SnackBarBehavior.floating,
            content: Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.white, size: 20),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'QR Code saved to Downloads!\nYou can scan or upload it in the GCash app.',
                    style: GoogleFonts.dmSans(fontWeight: FontWeight.w600, fontSize: 13),
                  ),
                ),
              ],
            ),
            duration: const Duration(seconds: 4),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Could not save QR code: $e'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _savingQr = false);
    }
  }

  Future<void> _handleConfirm() async {
    final ref = _refController.text.trim();
    if (ref.isEmpty) {
      setState(() {
        _errorMessage = 'Please enter your GCash reference number';
      });
      return;
    }
    if (ref.length < 6) {
      setState(() {
        _errorMessage = 'Reference number is too short (usually 13 digits)';
      });
      return;
    }
    if (_receiptFile == null) {
      setState(() {
        _errorMessage = 'Please upload your GCash payment receipt';
      });
      return;
    }

    setState(() {
      _submitting = true;
      _errorMessage = null;
    });

    try {
      await widget.onConfirmOrder(ref, _receiptFile?.path);
      if (mounted) {
        Navigator.of(context).pop();
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _submitting = false;
          _errorMessage = e.toString().replaceFirst('Exception: ', '');
        });
      }
    }
  }

  String _buildDynamicQrPayload() {
    final amount = widget.totalAmount.toStringAsFixed(2);
    final amtLen = sprintf2(amount.length);
    final cleanPhone = _gcashNumber.replaceAll(RegExp(r'\D'), '');
    return '00020101021226580014ph.com.gcash0118$cleanPhone'
        '0210CARTZYPH520459995303608540$amtLen$amount'
        '5802PH5915Cartzy Commerce6006Manila6304';
  }

  static String sprintf2(int val) => val < 10 ? '0$val' : '$val';

  @override
  Widget build(BuildContext context) {
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;
    final bool hasValidRef = _refController.text.trim().length >= 6;
    final bool hasReceipt = _receiptFile != null;
    final bool isReadyToSubmit = hasValidRef && hasReceipt && !_submitting;

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.92,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: SafeArea(
        top: false,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Drag handle
            Center(
              child: Container(
                margin: const EdgeInsets.only(top: 12, bottom: 8),
                width: 44,
                height: 5,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),

            // Sheet Header
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
              child: Row(
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: const Color(0xFF007DFE),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      'G',
                      style: GoogleFonts.dmSans(
                        fontSize: 22,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'GCash QR Payment',
                          style: GoogleFonts.dmSans(
                            fontSize: 16.5,
                            fontWeight: FontWeight.w800,
                            color: const Color(0xFF282133),
                          ),
                        ),
                        Text(
                          'Scan seller QR, upload receipt & enter reference',
                          style: GoogleFonts.dmSans(
                            fontSize: 12,
                            color: const Color(0xFF7A6E72),
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Color(0xFF7A6E72)),
                    onPressed: _submitting ? null : () => Navigator.of(context).pop(),
                  ),
                ],
              ),
            ),
            const Divider(height: 1, color: Color(0xFFF3ECEE)),

            // Content
            Flexible(
              child: _loading
                  ? const Padding(
                      padding: EdgeInsets.all(40),
                      child: Center(
                        child: CircularProgressIndicator(color: Color(0xFF007DFE)),
                      ),
                    )
                  : SingleChildScrollView(
                      padding: EdgeInsets.fromLTRB(20, 16, 20, bottomInset > 0 ? 16 : 24),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          // Store badge
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF4F9FF),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: const Color(0xFFD0E4FF)),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.storefront_outlined, size: 16, color: Color(0xFF007DFE)),
                                const SizedBox(width: 6),
                                Text(
                                  _storeName,
                                  style: GoogleFonts.dmSans(
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w700,
                                    color: const Color(0xFF007DFE),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 14),

                          // QR Code Container with RepaintBoundary for saving
                          RepaintBoundary(
                            key: _qrRepaintKey,
                            child: Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(18),
                                border: Border.all(color: const Color(0xFF007DFE), width: 2),
                                boxShadow: [
                                  BoxShadow(
                                    color: const Color(0xFF007DFE).withValues(alpha: 0.12),
                                    blurRadius: 16,
                                    offset: const Offset(0, 6),
                                  ),
                                ],
                              ),
                              child: Column(
                                children: [
                                  if (_hasCustomQr &&
                                      _gcashQrUrl != null &&
                                      _gcashQrUrl!.isNotEmpty)
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(10),
                                      child: Image.network(
                                        ApiService.normalizeImageUrl(_gcashQrUrl),
                                        width: 190,
                                        height: 190,
                                        fit: BoxFit.contain,
                                        errorBuilder: (_, __, ___) => QrImageView(
                                          data: _buildDynamicQrPayload(),
                                          version: QrVersions.auto,
                                          size: 190,
                                          gapless: true,
                                        ),
                                      ),
                                    )
                                  else
                                    QrImageView(
                                      data: _buildDynamicQrPayload(),
                                      version: QrVersions.auto,
                                      size: 190,
                                      gapless: true,
                                    ),
                                  const SizedBox(height: 10),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: _hasCustomQr ? const Color(0xFFE8F5E9) : const Color(0xFFE6F2FF),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      _hasCustomQr ? 'SELLER UPLOADED QR' : 'MERCHANT GCASH QR',
                                      style: GoogleFonts.dmSans(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w800,
                                        letterSpacing: 0.6,
                                        color: _hasCustomQr ? const Color(0xFF2E7D32) : const Color(0xFF007DFE),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 10),

                          // Download QR Button
                          InkWell(
                            onTap: _savingQr ? null : _downloadQrCode,
                            borderRadius: BorderRadius.circular(10),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF0F7FF),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: const Color(0xFFB9DBFF)),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  if (_savingQr)
                                    const SizedBox(
                                      width: 14,
                                      height: 14,
                                      child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF007DFE)),
                                    )
                                  else
                                    const Icon(Icons.download_rounded, size: 18, color: Color(0xFF007DFE)),
                                  const SizedBox(width: 8),
                                  Text(
                                    _savingQr ? 'Saving QR…' : 'Download QR Code',
                                    style: GoogleFonts.dmSans(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w700,
                                      color: const Color(0xFF007DFE),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Account Information Card
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF9FBFF),
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(color: const Color(0xFFE1EBF7)),
                            ),
                            child: Column(
                              children: [
                                _buildDetailRow(
                                  label: 'Account Name',
                                  value: _gcashName,
                                  onCopy: () => _copyToClipboard(_gcashName, 'Account name'),
                                ),
                                const Divider(height: 16, color: Color(0xFFE8EEF5)),
                                _buildDetailRow(
                                  label: 'GCash Number',
                                  value: _gcashNumber,
                                  isMono: true,
                                  onCopy: () => _copyToClipboard(
                                    _gcashNumber.replaceAll(RegExp(r'\D'), ''),
                                    'GCash number',
                                  ),
                                ),
                                const Divider(height: 16, color: Color(0xFFE8EEF5)),
                                _buildDetailRow(
                                  label: 'Exact Amount to Pay',
                                  value: '₱${widget.totalAmount.toStringAsFixed(2)}',
                                  isHighlighted: true,
                                  onCopy: () => _copyToClipboard(
                                    widget.totalAmount.toStringAsFixed(2),
                                    'Amount',
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 18),

                          // Reference Number Input
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(
                                    children: [
                                      Text(
                                        '1. GCash Reference Number',
                                        style: GoogleFonts.dmSans(
                                          fontSize: 13,
                                          fontWeight: FontWeight.w700,
                                          color: const Color(0xFF282133),
                                        ),
                                      ),
                                      const SizedBox(width: 4),
                                      const Text('*', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
                                    ],
                                  ),
                                  InkWell(
                                    onTap: _pasteFromClipboard,
                                    borderRadius: BorderRadius.circular(6),
                                    child: Padding(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          const Icon(Icons.paste, size: 14, color: Color(0xFF007DFE)),
                                          const SizedBox(width: 4),
                                          Text(
                                            'Paste',
                                            style: GoogleFonts.dmSans(
                                              fontSize: 12,
                                              fontWeight: FontWeight.w700,
                                              color: const Color(0xFF007DFE),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              TextField(
                                controller: _refController,
                                keyboardType: TextInputType.text,
                                textCapitalization: TextCapitalization.characters,
                                style: GoogleFonts.dmSans(
                                  fontSize: 14.5,
                                  fontWeight: FontWeight.w600,
                                  letterSpacing: 0.8,
                                  color: const Color(0xFF282133),
                                ),
                                decoration: InputDecoration(
                                  hintText: 'e.g. 1024 5829 3819 (13 digits)',
                                  hintStyle: GoogleFonts.dmSans(
                                    fontSize: 13,
                                    color: const Color(0xFFB0A4A8),
                                  ),
                                  contentPadding: const EdgeInsets.symmetric(
                                    horizontal: 16,
                                    vertical: 14,
                                  ),
                                  filled: true,
                                  fillColor: const Color(0xFFF9FBFF),
                                  prefixIcon: const Icon(
                                    Icons.receipt_long_outlined,
                                    color: Color(0xFF007DFE),
                                    size: 20,
                                  ),
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: const BorderSide(color: Color(0xFFD0E4FF)),
                                  ),
                                  enabledBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: const BorderSide(color: Color(0xFFD0E4FF)),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(12),
                                    borderSide: const BorderSide(color: Color(0xFF007DFE), width: 1.8),
                                  ),
                                ),
                                onChanged: (_) {
                                  setState(() {});
                                },
                              ),
                            ],
                          ),
                          const SizedBox(height: 18),

                          // Upload Payment Receipt Section
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Text(
                                    '2. Upload Proof of Payment / Receipt',
                                    style: GoogleFonts.dmSans(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w700,
                                      color: const Color(0xFF282133),
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                  const Text('*', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
                                ],
                              ),
                              const SizedBox(height: 8),

                              if (_receiptFile == null)
                                InkWell(
                                  onTap: _showImageSourcePicker,
                                  borderRadius: BorderRadius.circular(12),
                                  child: Container(
                                    width: double.infinity,
                                    padding: const EdgeInsets.symmetric(vertical: 22, horizontal: 16),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFF8FAFF),
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(
                                        color: const Color(0xFFB9DBFF),
                                        style: BorderStyle.solid,
                                        width: 1.5,
                                      ),
                                    ),
                                    child: Column(
                                      children: [
                                        Container(
                                          width: 44,
                                          height: 44,
                                          decoration: BoxDecoration(
                                            color: const Color(0xFFEBF5FF),
                                            borderRadius: BorderRadius.circular(10),
                                          ),
                                          child: const Icon(
                                            Icons.cloud_upload_outlined,
                                            color: Color(0xFF007DFE),
                                            size: 26,
                                          ),
                                        ),
                                        const SizedBox(height: 10),
                                        Text(
                                          'Tap to upload GCash receipt screenshot',
                                          style: GoogleFonts.dmSans(
                                            fontSize: 13.5,
                                            fontWeight: FontWeight.w700,
                                            color: const Color(0xFF007DFE),
                                          ),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          'Supports JPG, PNG (Max 5MB)',
                                          style: GoogleFonts.dmSans(
                                            fontSize: 11.5,
                                            color: const Color(0xFF7A6E72),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                )
                              else
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF0FDF4),
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(color: const Color(0xFFBBF7D0)),
                                  ),
                                  child: Row(
                                    children: [
                                      ClipRRect(
                                        borderRadius: BorderRadius.circular(8),
                                        child: Image.file(
                                          _receiptFile!,
                                          width: 60,
                                          height: 60,
                                          fit: BoxFit.cover,
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Row(
                                              children: [
                                                const Icon(Icons.check_circle, color: Color(0xFF16A34A), size: 16),
                                                const SizedBox(width: 6),
                                                Text(
                                                  'Receipt Attached',
                                                  style: GoogleFonts.dmSans(
                                                    fontSize: 13,
                                                    fontWeight: FontWeight.w800,
                                                    color: const Color(0xFF16A34A),
                                                  ),
                                                ),
                                              ],
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              _receiptFile!.path.split(Platform.pathSeparator).last,
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                              style: GoogleFonts.dmSans(
                                                fontSize: 11.5,
                                                color: const Color(0xFF4B5563),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      IconButton(
                                        icon: const Icon(Icons.delete_outline, color: Color(0xFFDC2626), size: 20),
                                        onPressed: () => setState(() => _receiptFile = null),
                                      ),
                                    ],
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 12),

                          // Validation or Error Message
                          if (_errorMessage != null) ...[
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFEF2F2),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: const Color(0xFFFECACA)),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.error_outline, size: 16, color: Color(0xFFDC2626)),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      _errorMessage!,
                                      style: GoogleFonts.dmSans(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                        color: const Color(0xFFDC2626),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 10),
                          ] else if (!isReadyToSubmit) ...[
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFFFBEB),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: const Color(0xFFFDE68A)),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.info_outline, size: 16, color: Color(0xFFD97706)),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      !hasReceipt && !hasValidRef
                                          ? 'Enter GCash reference number and upload payment receipt to place order.'
                                          : (!hasValidRef
                                              ? 'Enter GCash reference number (from receipt).'
                                              : 'Upload your GCash payment receipt to place order.'),
                                      style: GoogleFonts.dmSans(
                                        fontSize: 11.5,
                                        fontWeight: FontWeight.w600,
                                        color: const Color(0xFFB45309),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 10),
                          ],

                          // Approval Notice
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.shield_outlined, size: 16, color: Color(0xFF64748B)),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    'Your order will stay pending until the seller or admin approves your payment receipt.',
                                    style: GoogleFonts.dmSans(
                                      fontSize: 11,
                                      color: const Color(0xFF64748B),
                                      height: 1.3,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Confirm Button (Disabled if not ready)
                          SizedBox(
                            width: double.infinity,
                            height: 52,
                            child: ElevatedButton(
                              onPressed: isReadyToSubmit ? _handleConfirm : null,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF007DFE),
                                disabledBackgroundColor: const Color(0xFFE2E8F0),
                                disabledForegroundColor: const Color(0xFF94A3B8),
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14),
                                ),
                                padding: const EdgeInsets.symmetric(horizontal: 20),
                              ),
                              child: _submitting
                                  ? const Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        SizedBox(
                                          width: 18,
                                          height: 18,
                                          child: CircularProgressIndicator(
                                            strokeWidth: 2,
                                            color: Colors.white,
                                          ),
                                        ),
                                        SizedBox(width: 12),
                                        Text('Submitting order for verification…'),
                                      ],
                                    )
                                  : Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(
                                          isReadyToSubmit ? Icons.check_circle_outline : Icons.lock_outline,
                                          size: 20,
                                        ),
                                        const SizedBox(width: 10),
                                        Text(
                                          'Confirm & Place Order',
                                          style: GoogleFonts.dmSans(
                                            fontSize: 15,
                                            fontWeight: FontWeight.w800,
                                          ),
                                        ),
                                      ],
                                    ),
                            ),
                          ),
                        ],
                      ),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow({
    required String label,
    required String value,
    bool isMono = false,
    bool isHighlighted = false,
    required VoidCallback onCopy,
  }) {
    return Row(
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: GoogleFonts.dmSans(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF7A6E72),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: isMono
                    ? GoogleFonts.dmMono(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: isHighlighted ? const Color(0xFF007DFE) : const Color(0xFF282133),
                      )
                    : GoogleFonts.dmSans(
                        fontSize: isHighlighted ? 17 : 14,
                        fontWeight: isHighlighted ? FontWeight.w800 : FontWeight.w700,
                        color: isHighlighted ? const Color(0xFF007DFE) : const Color(0xFF282133),
                      ),
              ),
            ],
          ),
        ),
        InkWell(
          onTap: onCopy,
          borderRadius: BorderRadius.circular(8),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFFEBF4FF),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.copy, size: 14, color: Color(0xFF007DFE)),
                const SizedBox(width: 4),
                Text(
                  'Copy',
                  style: GoogleFonts.dmSans(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF007DFE),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

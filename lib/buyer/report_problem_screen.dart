import 'dart:io';

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'package:cartzy/services/api_service.dart' show ApiException;
import 'package:cartzy/services/order_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';

// ============================================================
// REPORT A PROBLEM — opens a case on the admin Disputes page,
// same form and rules as the website.
// ============================================================

class ReportProblemScreen extends StatefulWidget {
  final BuyerOrder order;
  final List<ReportOption> categories;
  final List<ReportOption> resolutions;
  final String contactPhone;

  const ReportProblemScreen({
    super.key,
    required this.order,
    required this.categories,
    required this.resolutions,
    required this.contactPhone,
  });

  @override
  State<ReportProblemScreen> createState() => _ReportProblemScreenState();
}

class _ReportProblemScreenState extends State<ReportProblemScreen> {
  static const _ink = Color(0xFF261E30);
  static const _muted = Color(0xFF756A7C);
  static const _brand = Color(0xFF564B68);
  static const _maxPhotos = 4;

  String? _category;
  String? _resolution;
  final Set<int> _itemIds = {};
  final List<XFile> _photos = [];
  final _descriptionController = TextEditingController();
  late final _phoneController = TextEditingController(text: widget.contactPhone);
  bool _submitting = false;
  String? _error;

  ReportOption? get _selectedCategory =>
      widget.categories.where((c) => c.key == _category).firstOrNull;

  @override
  void dispose() {
    _descriptionController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  Future<void> _addPhotos() async {
    try {
      final picked = await ImagePicker().pickMultiImage(imageQuality: 85);
      if (picked.isEmpty || !mounted) return;
      setState(() {
        _photos.addAll(picked.take(_maxPhotos - _photos.length));
        _error = null;
      });
      if (picked.length > _maxPhotos) {
        showCartzyFlash(context, 'You can add up to $_maxPhotos photos.', icon: Icons.info_outline);
      }
    } catch (_) {
      if (mounted) showCartzyFlash(context, "Couldn't open your photos.", icon: Icons.error_outline);
    }
  }

  String? _validate() {
    if (_category == null) return 'Choose what went wrong.';
    if (_selectedCategory?.needsPhotos == true && _photos.isEmpty) {
      return 'Please add at least one photo so we can check the problem.';
    }
    if (_resolution == null) return 'Choose what you would like us to do.';
    if (_descriptionController.text.trim().length < 20) {
      return 'Please describe the problem in at least 20 characters.';
    }
    if (!RegExp(r'^[0-9+()\-\s]{7,30}$').hasMatch(_phoneController.text.trim())) {
      return 'Enter a valid contact number.';
    }
    return null;
  }

  Future<void> _submit() async {
    final error = _validate();
    setState(() => _error = error);
    if (error != null) return;

    setState(() => _submitting = true);
    try {
      final message = await OrderService.reportProblem(
        widget.order.sellerOrderId,
        category: _category!,
        resolution: _resolution!,
        description: _descriptionController.text.trim(),
        contactPhone: _phoneController.text.trim(),
        itemIds: _itemIds.toList(),
        photoPaths: _photos.map((p) => p.path).toList(),
      );
      if (!mounted) return;
      showCartzyFlash(context, message);
      Navigator.of(context).pop(true);
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _submitting = false;
        _error = e is ApiException
            ? e.message
            : "Couldn't reach the Cartzy server. Check your connection and try again.";
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final needsPhotos = _selectedCategory?.needsPhotos == true;
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.background,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
          onPressed: _submitting ? null : () => Navigator.of(context).pop(),
        ),
        title: Text('Report a problem',
            style: GoogleFonts.dmSans(fontSize: 17, fontWeight: FontWeight.w800, color: _ink)),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 4, 20, 32),
        children: [
          Text(
            '${widget.order.store} · ${widget.order.reference}',
            style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w600, color: _muted),
          ),
          const SizedBox(height: 4),
          Text(
            'Our team reviews every report and updates you in Notifications.',
            style: GoogleFonts.dmSans(fontSize: 12.5, color: const Color(0xFF9E929E)),
          ),
          _section('What went wrong?'),
          for (final category in widget.categories)
            _choice(
              label: category.label,
              selected: _category == category.key,
              onTap: () => setState(() {
                _category = category.key;
                _error = null;
              }),
            ),
          _section('Which items? (optional)'),
          for (final item in widget.order.items)
            CheckboxListTile(
              contentPadding: EdgeInsets.zero,
              controlAffinity: ListTileControlAffinity.leading,
              activeColor: _brand,
              value: _itemIds.contains(item.id),
              onChanged: (checked) => setState(() {
                checked == true ? _itemIds.add(item.id) : _itemIds.remove(item.id);
              }),
              title: Row(
                children: [
                  CartzyProductImage(
                    imageUrl: item.image,
                    width: 40,
                    height: 40,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(item.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.dmSans(fontSize: 13, color: _ink)),
                  ),
                ],
              ),
            ),
          _section(needsPhotos ? 'Photos (required)' : 'Photos (optional)'),
          Text('Up to $_maxPhotos photos, 5 MB each.',
              style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF9E929E))),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              for (var i = 0; i < _photos.length; i++)
                Stack(
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: Image.file(
                        File(_photos[i].path),
                        cacheWidth: 160,
                        width: 72,
                        height: 72,
                        fit: BoxFit.cover,
                      ),
                    ),
                    Positioned(
                      right: 2,
                      top: 2,
                      child: GestureDetector(
                        onTap: () => setState(() => _photos.removeAt(i)),
                        child: const CircleAvatar(
                          radius: 11,
                          backgroundColor: Colors.black54,
                          child: Icon(Icons.close, size: 14, color: Colors.white),
                        ),
                      ),
                    ),
                  ],
                ),
              if (_photos.length < _maxPhotos)
                InkWell(
                  onTap: _addPhotos,
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    width: 72,
                    height: 72,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE1DDE7)),
                    ),
                    child: const Icon(Icons.add_a_photo_outlined, color: _brand),
                  ),
                ),
            ],
          ),
          _section('What would you like us to do?'),
          for (final resolution in widget.resolutions)
            _choice(
              label: resolution.label,
              selected: _resolution == resolution.key,
              onTap: () => setState(() {
                _resolution = resolution.key;
                _error = null;
              }),
            ),
          _section('Describe the problem'),
          _input(_descriptionController,
              hint: 'Tell us what happened (at least 20 characters).', maxLines: 5, maxLength: 3000),
          _section('Contact number'),
          _input(_phoneController, hint: '09XXXXXXXXX', keyboardType: TextInputType.phone),
          if (_error != null) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: CartzyColors.error.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: CartzyColors.error.withValues(alpha: 0.3)),
              ),
              child: Text(_error!,
                  style: GoogleFonts.dmSans(fontSize: 13, color: CartzyColors.error)),
            ),
          ],
          const SizedBox(height: 20),
          SizedBox(
            height: 50,
            child: FilledButton(
              style: FilledButton.styleFrom(
                backgroundColor: _brand,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              onPressed: _submitting ? null : _submit,
              child: _submitting
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : Text('Send report',
                      style: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w700)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _section(String title) => Padding(
        padding: const EdgeInsets.only(top: 20, bottom: 8),
        child: Text(title,
            style: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w800, color: _ink)),
      );

  Widget _choice({required String label, required bool selected, required VoidCallback onTap}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
          decoration: BoxDecoration(
            color: selected ? const Color(0xFFF6F4F8) : Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: selected ? _brand : const Color(0xFFE5E7EB)),
          ),
          child: Row(
            children: [
              Icon(selected ? Icons.radio_button_checked : Icons.radio_button_unchecked,
                  size: 20, color: selected ? _brand : const Color(0xFFB7AAB5)),
              const SizedBox(width: 10),
              Expanded(child: Text(label, style: GoogleFonts.dmSans(fontSize: 13.5, color: _ink))),
            ],
          ),
        ),
      ),
    );
  }

  Widget _input(
    TextEditingController controller, {
    required String hint,
    int maxLines = 1,
    int? maxLength,
    TextInputType? keyboardType,
  }) {
    return TextField(
      controller: controller,
      maxLines: maxLines,
      maxLength: maxLength,
      keyboardType: keyboardType,
      onChanged: (_) {
        if (_error != null) setState(() => _error = null);
      },
      style: GoogleFonts.dmSans(fontSize: 13.5, color: _ink),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFFAAA0A2)),
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: Color(0xFFE1DDE7)),
        ),
      ),
    );
  }
}

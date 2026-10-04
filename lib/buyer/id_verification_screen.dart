import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';

class IdVerificationScreen extends StatefulWidget {
  const IdVerificationScreen({super.key});

  @override
  State<IdVerificationScreen> createState() => _IdVerificationScreenState();
}

class _IdVerificationScreenState extends State<IdVerificationScreen> {
  final _idNumberController = TextEditingController();
  String _selectedIdType = '';
  String? _fileName;
  bool _idTypeError = false;
  bool _fileError = false;

  static const List<String> idTypes = [
    "Driver's License",
    'Passport',
    'National ID (PhilSys)',
    'SSS ID',
    'PhilHealth ID',
    'Postal ID',
    "Voter's ID",
  ];

  @override
  void dispose() {
    _idNumberController.dispose();
    super.dispose();
  }

  void _pickFile() {
    // Placeholder — wire up image_picker / file_picker later.
    setState(() {
      _fileName = 'government_id.jpg';
      _fileError = false;
    });
  }

  void _submit() {
    setState(() {
      _idTypeError = _selectedIdType.isEmpty;
      _fileError = _fileName == null;
    });
    if (!_idTypeError && !_fileError) {
      Navigator.of(context).pop(true);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
          onPressed: () => Navigator.of(context).pop(false),
        ),
        title: const Text('ID Verification', style: TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold)),
      ),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: CartzyColors.surface,
                borderRadius: BorderRadius.circular(14),
                boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 3))],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.badge_outlined, color: CartzyColors.coral),
                      const SizedBox(width: 8),
                      const Text(
                        'Submit ID for Verification',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: CartzyColors.navy),
                      ),
                      const Spacer(),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: CartzyColors.background,
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text(
                          'Encrypted & Protected',
                          style: TextStyle(fontSize: 9, color: CartzyColors.gray, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),

                  const Text('Valid ID Type *',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: CartzyColors.navy)),
                  const SizedBox(height: 8),
                  GestureDetector(
                    onTap: () async {
                      final selected = await showModalBottomSheet<String>(
                        context: context,
                        shape: const RoundedRectangleBorder(
                          borderRadius: BorderRadius.only(topLeft: Radius.circular(20), topRight: Radius.circular(20)),
                        ),
                        builder: (context) => ListView(
                          shrinkWrap: true,
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          children: idTypes
                              .map((type) => ListTile(
                                    title: Text(type),
                                    onTap: () => Navigator.of(context).pop(type),
                                  ))
                              .toList(),
                        ),
                      );
                      if (selected != null) {
                        setState(() {
                          _selectedIdType = selected;
                          _idTypeError = false;
                        });
                      }
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                      decoration: BoxDecoration(
                        color: CartzyColors.background,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: _idTypeError ? CartzyColors.error : CartzyColors.border,
                          width: 1.2,
                        ),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              _selectedIdType.isEmpty ? '-- Select Valid ID Type --' : _selectedIdType,
                              style: TextStyle(
                                fontSize: 14,
                                color: _selectedIdType.isEmpty ? CartzyColors.gray : CartzyColors.text,
                              ),
                            ),
                          ),
                          const Icon(Icons.keyboard_arrow_down_rounded, color: CartzyColors.gray),
                        ],
                      ),
                    ),
                  ),
                  if (_idTypeError)
                    const Padding(
                      padding: EdgeInsets.only(top: 6),
                      child: Text('Please select a valid ID type', style: TextStyle(fontSize: 11, color: CartzyColors.error)),
                    ),
                  const SizedBox(height: 18),

                  const Text('ID Number (Optional)',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: CartzyColors.navy)),
                  const SizedBox(height: 8),
                  TextField(
                    controller: _idNumberController,
                    style: const TextStyle(fontSize: 14, color: CartzyColors.text),
                    decoration: InputDecoration(
                      hintText: 'e.g. 1234-5678-9012',
                      hintStyle: const TextStyle(fontSize: 13, color: CartzyColors.gray),
                      filled: true,
                      fillColor: CartzyColors.background,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: CartzyColors.border, width: 1.2),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: CartzyColors.coral, width: 1.8),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),

                  const Text('Front Photo or Scan of Valid ID *',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: CartzyColors.navy)),
                  const SizedBox(height: 8),
                  GestureDetector(
                    onTap: _pickFile,
                    child: Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 30),
                      decoration: BoxDecoration(
                        color: CartzyColors.background,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: _fileError ? CartzyColors.error : CartzyColors.border,
                          width: 1.4,
                          style: BorderStyle.solid,
                        ),
                      ),
                      child: Column(
                        children: [
                          Container(
                            width: 48,
                            height: 48,
                            decoration: const BoxDecoration(color: CartzyColors.surface, shape: BoxShape.circle),
                            child: const Icon(Icons.camera_alt_outlined, color: CartzyColors.coral),
                          ),
                          const SizedBox(height: 14),
                          Text(
                            _fileName ?? 'Click here to choose or take a photo of your Government ID',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: _fileName != null ? FontWeight.w600 : FontWeight.normal,
                              color: _fileName != null ? CartzyColors.coral : CartzyColors.navy,
                            ),
                          ),
                          const SizedBox(height: 6),
                          const Text(
                            'Supports PNG, JPG, JPEG, PDF up to 5MB. Ensure full name and birthdate are legible.',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 11, color: CartzyColors.gray),
                          ),
                        ],
                      ),
                    ),
                  ),
                  if (_fileError)
                    const Padding(
                      padding: EdgeInsets.only(top: 6),
                      child: Text('Please upload a photo of your ID', style: TextStyle(fontSize: 11, color: CartzyColors.error)),
                    ),
                  const SizedBox(height: 18),

                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: CartzyColors.background,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Row(
                      children: [
                        Icon(Icons.lock_outline, size: 16, color: CartzyColors.gray),
                        SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Your data is securely stored and verified solely for platform purchasing compliance.',
                            style: TextStyle(fontSize: 11, color: CartzyColors.gray, height: 1.4),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            SizedBox(
              width: double.infinity,
              height: 54,
              child: ElevatedButton(
                onPressed: _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: CartzyColors.navy,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('Save Changes', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
import 'dart:io';

import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:google_mlkit_text_recognition/google_mlkit_text_recognition.dart';

import 'package:cartzy/services/government_id_parser.dart';

/// A photo of an ID plus the details read from it.
class IdScanResult {
  final String imagePath;
  final GovernmentIdFields fields;

  const IdScanResult({required this.imagePath, required this.fields});
}

/// Full-screen camera with an ID-card frame. Pops with an [IdScanResult]
/// once the rider accepts a photo, or null if they back out.
class IdScannerScreen extends StatefulWidget {
  /// ID type picked on the form, used when the card's heading can't be read.
  final String? preferredType;

  const IdScannerScreen({super.key, this.preferredType});

  /// Reads the text on an ID photo on the device (no upload) and parses it.
  static Future<GovernmentIdFields> readIdImage(String path,
      {String? preferredType}) async {
    final recognizer = TextRecognizer(script: TextRecognitionScript.latin);
    try {
      final text = await recognizer.processImage(InputImage.fromFilePath(path));
      return GovernmentIdParser.parse(text.text, preferredType: preferredType);
    } finally {
      await recognizer.close();
    }
  }

  @override
  State<IdScannerScreen> createState() => _IdScannerScreenState();
}

class _IdScannerScreenState extends State<IdScannerScreen>
    with WidgetsBindingObserver {
  /// ID-1 card proportions (85.6 x 54 mm), shared by all PH government IDs.
  static const _cardAspectRatio = 85.6 / 54;

  CameraController? _controller;
  String? _cameraError;
  bool _flashOn = false;
  bool _busy = false;
  IdScanResult? _review;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _startCamera();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _controller?.dispose();
    super.dispose();
  }

  // The camera must be released while the app is in the background.
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    final controller = _controller;
    if (state == AppLifecycleState.inactive) {
      _controller = null;
      controller?.dispose();
    } else if (state == AppLifecycleState.resumed && controller == null) {
      _startCamera();
    }
  }

  Future<void> _startCamera() async {
    try {
      final cameras = await availableCameras();
      final back = cameras.firstWhere(
        (c) => c.lensDirection == CameraLensDirection.back,
        orElse: () => cameras.first,
      );
      final controller = CameraController(back, ResolutionPreset.high,
          enableAudio: false);
      await controller.initialize();
      await controller.setFlashMode(FlashMode.off);
      if (!mounted) {
        await controller.dispose();
        return;
      }
      setState(() {
        _controller = controller;
        _flashOn = false;
        _cameraError = null;
      });
    } on CameraException catch (e) {
      setState(() => _cameraError = e.code.contains('AccessDenied')
          ? 'Camera access is off. Allow it in your phone settings, or upload a photo of your ID instead.'
          : "The camera couldn't start. You can upload a photo of your ID instead.");
    } catch (_) {
      setState(() => _cameraError =
          "No camera was found. You can upload a photo of your ID instead.");
    }
  }

  Future<void> _toggleFlash() async {
    final controller = _controller;
    if (controller == null) return;
    final on = !_flashOn;
    await controller.setFlashMode(on ? FlashMode.torch : FlashMode.off);
    setState(() => _flashOn = on);
  }

  Future<void> _capture() async {
    final controller = _controller;
    if (controller == null || _busy || controller.value.isTakingPicture) return;
    setState(() => _busy = true);
    try {
      final photo = await controller.takePicture();
      if (_flashOn) {
        await controller.setFlashMode(FlashMode.off);
        _flashOn = false;
      }
      final fields = await IdScannerScreen.readIdImage(photo.path,
          preferredType: widget.preferredType);
      if (!mounted) return;
      setState(() {
        _review = IdScanResult(imagePath: photo.path, fields: fields);
        _busy = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _busy = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Couldn't take the photo. Please try again.")),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: Text(
          _review == null ? 'Scan your ID' : 'Check your photo',
          style: GoogleFonts.dmSans(fontSize: 17, fontWeight: FontWeight.w600),
        ),
        actions: [
          if (_review == null && _controller != null)
            IconButton(
              tooltip: _flashOn ? 'Turn off light' : 'Turn on light',
              icon: Icon(_flashOn ? Icons.flash_on : Icons.flash_off),
              onPressed: _toggleFlash,
            ),
        ],
      ),
      body: SafeArea(
        child: _review != null ? _buildReview(_review!) : _buildCamera(),
      ),
    );
  }

  Widget _buildCamera() {
    final controller = _controller;
    if (_cameraError != null) {
      return _Message(
        icon: Icons.no_photography_outlined,
        text: _cameraError!,
        action: 'Go back',
        onAction: () => Navigator.of(context).pop(),
      );
    }
    if (controller == null || !controller.value.isInitialized) {
      return const Center(child: CircularProgressIndicator(color: Colors.white));
    }

    return Column(
      children: [
        Expanded(
          child: LayoutBuilder(builder: (context, constraints) {
            final frameWidth = constraints.maxWidth * 0.86;
            final frame = Rect.fromCenter(
              center: constraints.biggest.center(Offset.zero),
              width: frameWidth,
              height: frameWidth / _cardAspectRatio,
            );
            return Stack(
              fit: StackFit.expand,
              children: [
                ClipRect(
                  child: FittedBox(
                    fit: BoxFit.cover,
                    child: SizedBox(
                      width: constraints.maxWidth,
                      height: constraints.maxWidth * controller.value.aspectRatio,
                      child: CameraPreview(controller),
                    ),
                  ),
                ),
                CustomPaint(painter: _CardFramePainter(frame)),
                Positioned(
                  left: 24,
                  right: 24,
                  top: frame.bottom + 18,
                  child: Text(
                    'Fit the front of your ID inside the frame.\nUse good light and avoid glare.',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.dmSans(color: Colors.white, fontSize: 13.5, height: 1.4),
                  ),
                ),
                if (_busy)
                  Container(
                    color: Colors.black54,
                    alignment: Alignment.center,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const CircularProgressIndicator(color: Colors.white),
                        const SizedBox(height: 14),
                        Text('Reading your ID…',
                            style: GoogleFonts.dmSans(color: Colors.white, fontSize: 14)),
                      ],
                    ),
                  ),
              ],
            );
          }),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 20),
          child: Semantics(
            button: true,
            label: 'Take photo',
            child: GestureDetector(
              onTap: _capture,
              child: Container(
                width: 72,
                height: 72,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 4),
                ),
                padding: const EdgeInsets.all(5),
                child: const DecoratedBox(
                  decoration: BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildReview(IdScanResult result) {
    final f = result.fields;
    String date(DateTime d) =>
        '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';
    final found = <(String, String)>[
      if (f.idType != null) ('ID type', f.idType!),
      if (f.idNumber != null) ('ID number', f.idNumber!),
      if (f.lastName != null) ('Last name', f.lastName!),
      if (f.firstName != null) ('First name', f.firstName!),
      if (f.birthday != null) ('Birthday', date(f.birthday!)),
    ];

    return Container(
      color: Colors.white,
      child: Column(
        children: [
          Expanded(
            child: ListView(
              padding: const EdgeInsets.all(20),
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.file(File(result.imagePath), fit: BoxFit.cover),
                ),
                const SizedBox(height: 18),
                Text(
                  'Make sure all text on your ID is clear and readable.',
                  style: GoogleFonts.dmSans(
                      fontSize: 14, fontWeight: FontWeight.w600, color: const Color(0xFF231F20)),
                ),
                const SizedBox(height: 12),
                if (found.isEmpty)
                  Text(
                    "We couldn't read the details on this photo. You can retake it, "
                    'or use it and type your details yourself.',
                    style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF7A6E72)),
                  )
                else ...[
                  Text(
                    "We'll fill these in for you. You can still edit them.",
                    style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF7A6E72)),
                  ),
                  const SizedBox(height: 8),
                  for (final (label, value) in found)
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 4),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          SizedBox(
                            width: 96,
                            child: Text(label,
                                style: GoogleFonts.dmSans(
                                    fontSize: 13, color: const Color(0xFF7A6E72))),
                          ),
                          Expanded(
                            child: Text(value,
                                style: GoogleFonts.dmSans(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                    color: const Color(0xFF231F20))),
                          ),
                        ],
                      ),
                    ),
                ],
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
            child: Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => setState(() => _review = null),
                    style: OutlinedButton.styleFrom(minimumSize: const Size.fromHeight(48)),
                    child: const Text('Retake'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () => Navigator.of(context).pop(result),
                    style: ElevatedButton.styleFrom(
                      minimumSize: const Size.fromHeight(48),
                      backgroundColor: const Color(0xFF865561),
                      foregroundColor: Colors.white,
                    ),
                    child: const Text('Use this photo'),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Darkens everything outside the card-shaped [frame].
class _CardFramePainter extends CustomPainter {
  final Rect frame;

  _CardFramePainter(this.frame);

  @override
  void paint(Canvas canvas, Size size) {
    final card = RRect.fromRectAndRadius(frame, const Radius.circular(14));
    canvas.drawPath(
      Path.combine(
        PathOperation.difference,
        Path()..addRect(Offset.zero & size),
        Path()..addRRect(card),
      ),
      Paint()..color = Colors.black.withValues(alpha: 0.55),
    );
    canvas.drawRRect(
      card,
      Paint()
        ..color = Colors.white
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2.5,
    );
  }

  @override
  bool shouldRepaint(_CardFramePainter old) => old.frame != frame;
}

class _Message extends StatelessWidget {
  final IconData icon;
  final String text;
  final String action;
  final VoidCallback onAction;

  const _Message({
    required this.icon,
    required this.text,
    required this.action,
    required this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: Colors.white70, size: 48),
            const SizedBox(height: 16),
            Text(text,
                textAlign: TextAlign.center,
                style: GoogleFonts.dmSans(color: Colors.white, fontSize: 14, height: 1.4)),
            const SizedBox(height: 20),
            OutlinedButton(
              onPressed: onAction,
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.white,
                side: const BorderSide(color: Colors.white54),
              ),
              child: Text(action),
            ),
          ],
        ),
      ),
    );
  }
}

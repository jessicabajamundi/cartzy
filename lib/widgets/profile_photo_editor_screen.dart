import 'dart:io';
import 'dart:math' as math;
import 'dart:typed_data';
import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

class ProfilePhotoEditorScreen extends StatefulWidget {
  final File imageFile;

  const ProfilePhotoEditorScreen({
    super.key,
    required this.imageFile,
  });

  @override
  State<ProfilePhotoEditorScreen> createState() =>
      _ProfilePhotoEditorScreenState();
}

class _ProfilePhotoEditorScreenState extends State<ProfilePhotoEditorScreen> {
  final GlobalKey _cropKey = GlobalKey();
  late TransformationController _transformController;

  double _fineAngle = 0.0; // degrees: -45.0 to +45.0
  int _quarterTurns = 0; // 0, 1, 2, 3 (90 degree steps)
  bool _isFlipped = false;
  bool _isCircle = true;
  bool _showGrid = true;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _transformController = TransformationController();
  }

  @override
  void dispose() {
    _transformController.dispose();
    super.dispose();
  }

  void _reset() {
    setState(() {
      _fineAngle = 0.0;
      _quarterTurns = 0;
      _isFlipped = false;
      _transformController.value = Matrix4.identity();
    });
  }

  void _rotate90() {
    setState(() {
      _quarterTurns = (_quarterTurns + 1) % 4;
    });
  }

  void _toggleFlip() {
    setState(() {
      _isFlipped = !_isFlipped;
    });
  }

  void _toggleShape() {
    setState(() {
      _isCircle = !_isCircle;
    });
  }

  void _toggleGrid() {
    setState(() {
      _showGrid = !_showGrid;
    });
  }

  void _zoomBy(double factor) {
    final matrix = _transformController.value.clone();
    matrix.scaleByDouble(factor, factor, 1.0, 1.0);
    _transformController.value = matrix;
  }

  Future<void> _handleSave() async {
    if (_isSaving) return;
    setState(() => _isSaving = true);

    try {
      // Small delay to ensure render tree finishes layout
      await Future.delayed(const Duration(milliseconds: 120));

      final boundary =
          _cropKey.currentContext?.findRenderObject() as RenderRepaintBoundary?;
      if (boundary == null) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Failed to capture photo preview.')),
          );
        }
        return;
      }

      final ui.Image image = await boundary.toImage(pixelRatio: 3.0);
      final ByteData? byteData =
          await image.toByteData(format: ui.ImageByteFormat.png);
      if (byteData == null) {
        throw Exception('Could not convert cropped image to byte data');
      }

      final Uint8List pngBytes = byteData.buffer.asUint8List();

      Directory dir;
      try {
        dir = File(widget.imageFile.path).parent;
        if (!dir.existsSync()) {
          dir = Directory.systemTemp;
        }
      } catch (_) {
        dir = Directory.systemTemp;
      }

      final timestamp = DateTime.now().millisecondsSinceEpoch;
      final outputFile = File('${dir.path}/cartzy_avatar_$timestamp.png');
      await outputFile.writeAsBytes(pngBytes, flush: true);

      if (mounted) {
        Navigator.of(context).pop(outputFile.path);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error saving photo: $e')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isSaving = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final media = MediaQuery.of(context);
    final screenWidth = media.size.width;
    final screenHeight = media.size.height;

    // Viewport size adapted to screen
    final cropSize = math.min(screenWidth - 48.0, screenHeight * 0.40);

    return Scaffold(
      backgroundColor: const Color(0xFF141215),
      appBar: AppBar(
        backgroundColor: const Color(0xFF141215),
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.close_rounded, color: Colors.white, size: 24),
          onPressed: () => Navigator.of(context).pop(),
        ),
        centerTitle: true,
        title: Text(
          'Edit Profile Photo',
          style: GoogleFonts.playfairDisplay(
            color: Colors.white,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 14),
            child: _isSaving
                ? const Center(
                    child: SizedBox(
                      width: 22,
                      height: 22,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.2,
                        color: CartzyColors.burgundy,
                      ),
                    ),
                  )
                : TextButton.icon(
                    style: TextButton.styleFrom(
                      backgroundColor: CartzyColors.burgundy,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 8),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                      ),
                    ),
                    onPressed: _handleSave,
                    icon: const Icon(Icons.check_rounded, size: 18),
                    label: Text(
                      'Done',
                      style: GoogleFonts.dmSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
          ),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Top guidance chip
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.touch_app_rounded,
                    size: 16,
                    color: CartzyColors.cream,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Pinch to zoom • Drag to move • Rotate below',
                    style: GoogleFonts.dmSans(
                      color: Colors.white.withValues(alpha: 0.85),
                      fontSize: 12,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),

            // Main crop viewport
            Expanded(
              child: Center(
                child: _buildCropViewport(cropSize),
              ),
            ),

            // Controls Panel at bottom
            _buildControlsPanel(),
          ],
        ),
      ),
    );
  }

  Widget _buildCropViewport(double cropSize) {
    final clipRadius = _isCircle ? cropSize / 2 : 20.0;

    return Stack(
      alignment: Alignment.center,
      children: [
        // 1. RepaintBoundary capturing the image exactly inside the crop boundary
        RepaintBoundary(
          key: _cropKey,
          child: Container(
            width: cropSize,
            height: cropSize,
            decoration: BoxDecoration(
              color: Colors.black,
              borderRadius: BorderRadius.circular(clipRadius),
            ),
            clipBehavior: Clip.antiAlias,
            child: InteractiveViewer(
              transformationController: _transformController,
              boundaryMargin: EdgeInsets.all(cropSize * 2),
              minScale: 0.3,
              maxScale: 6.0,
              child: Transform(
                alignment: Alignment.center,
                transform: Matrix4.identity()
                  ..rotateZ(_fineAngle * math.pi / 180.0)
                  ..setEntry(0, 0, _isFlipped ? -1.0 : 1.0),
                child: RotatedBox(
                  quarterTurns: _quarterTurns,
                  child: Image.file(
                    widget.imageFile,
                    fit: BoxFit.contain,
                  ),
                ),
              ),
            ),
          ),
        ),

        // 2. Guide Overlay (Border + Rule-of-Thirds Grid, not captured)
        IgnorePointer(
          child: Container(
            width: cropSize,
            height: cropSize,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(clipRadius),
              border: Border.all(
                color: CartzyColors.cream.withValues(alpha: 0.8),
                width: 2.0,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.4),
                  blurRadius: 16,
                ),
              ],
            ),
            child: _showGrid
                ? CustomPaint(
                    painter: _GridGuidePainter(
                      isCircle: _isCircle,
                      gridColor: Colors.white.withValues(alpha: 0.25),
                    ),
                  )
                : null,
          ),
        ),
      ],
    );
  }

  Widget _buildControlsPanel() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
      decoration: BoxDecoration(
        color: const Color(0xFF1B181D),
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        border: Border(
          top: BorderSide(
            color: Colors.white.withValues(alpha: 0.08),
            width: 1,
          ),
        ),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // ── Fine Angle Adjustment Slider ──
          Row(
            children: [
              GestureDetector(
                onTap: () => setState(() => _fineAngle = 0.0),
                child: Row(
                  children: [
                    const Icon(
                      Icons.tune_rounded,
                      size: 16,
                      color: CartzyColors.cream,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Angle: ${_fineAngle >= 0 ? '+' : ''}${_fineAngle.toStringAsFixed(1)}°',
                      style: GoogleFonts.dmSans(
                        color: Colors.white,
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SliderTheme(
                  data: SliderTheme.of(context).copyWith(
                    trackHeight: 3,
                    thumbShape:
                        const RoundSliderThumbShape(enabledThumbRadius: 7),
                    overlayShape:
                        const RoundSliderOverlayShape(overlayRadius: 14),
                    activeTrackColor: CartzyColors.burgundy,
                    inactiveTrackColor: Colors.white12,
                    thumbColor: CartzyColors.cream,
                  ),
                  child: Slider(
                    value: _fineAngle,
                    min: -45.0,
                    max: 45.0,
                    onChanged: (val) {
                      setState(() => _fineAngle = val);
                    },
                  ),
                ),
              ),
              IconButton(
                icon: const Icon(Icons.restart_alt_rounded,
                    size: 18, color: Colors.white70),
                tooltip: 'Reset angle to 0°',
                onPressed: () => setState(() => _fineAngle = 0.0),
              ),
            ],
          ),

          const SizedBox(height: 6),

          // ── Quick Action Buttons Row ──
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              _buildActionButton(
                icon: Icons.rotate_right_rounded,
                label: 'Rotate 90°',
                onTap: _rotate90,
              ),
              _buildActionButton(
                icon: Icons.flip_rounded,
                label: 'Flip',
                isActive: _isFlipped,
                onTap: _toggleFlip,
              ),
              _buildActionButton(
                icon: _isCircle
                    ? Icons.circle_outlined
                    : Icons.crop_square_rounded,
                label: _isCircle ? 'Circle' : 'Square',
                isActive: _isCircle,
                onTap: _toggleShape,
              ),
              _buildActionButton(
                icon:
                    _showGrid ? Icons.grid_on_rounded : Icons.grid_off_rounded,
                label: 'Grid',
                isActive: _showGrid,
                onTap: _toggleGrid,
              ),
              _buildActionButton(
                icon: Icons.zoom_out_rounded,
                label: 'Zoom -',
                onTap: () => _zoomBy(0.85),
              ),
              _buildActionButton(
                icon: Icons.zoom_in_rounded,
                label: 'Zoom +',
                onTap: () => _zoomBy(1.15),
              ),
              _buildActionButton(
                icon: Icons.restore_rounded,
                label: 'Reset',
                onTap: _reset,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildActionButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
    bool isActive = false,
  }) {
    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            padding: const EdgeInsets.all(9),
            decoration: BoxDecoration(
              color: isActive
                  ? CartzyColors.burgundy.withValues(alpha: 0.35)
                  : Colors.white.withValues(alpha: 0.06),
              shape: BoxShape.circle,
              border: Border.all(
                color: isActive
                    ? CartzyColors.burgundy
                    : Colors.white.withValues(alpha: 0.12),
                width: 1.2,
              ),
            ),
            child: Icon(
              icon,
              size: 19,
              color: isActive ? CartzyColors.cream : Colors.white,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: GoogleFonts.dmSans(
              color: isActive
                  ? CartzyColors.cream
                  : Colors.white.withValues(alpha: 0.75),
              fontSize: 10.5,
              fontWeight: isActive ? FontWeight.w700 : FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }
}

class _GridGuidePainter extends CustomPainter {
  final bool isCircle;
  final Color gridColor;

  _GridGuidePainter({
    required this.isCircle,
    required this.gridColor,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = gridColor
      ..strokeWidth = 1.0
      ..style = PaintingStyle.stroke;

    final oneThirdW = size.width / 3;
    final twoThirdsW = size.width * 2 / 3;
    final oneThirdH = size.height / 3;
    final twoThirdsH = size.height * 2 / 3;

    if (isCircle) {
      canvas.save();
      final clipPath = Path()
        ..addOval(Rect.fromLTWH(0, 0, size.width, size.height));
      canvas.clipPath(clipPath);
    }

    // Vertical grid lines
    canvas.drawLine(
        Offset(oneThirdW, 0), Offset(oneThirdW, size.height), paint);
    canvas.drawLine(
        Offset(twoThirdsW, 0), Offset(twoThirdsW, size.height), paint);

    // Horizontal grid lines
    canvas.drawLine(
        Offset(0, oneThirdH), Offset(size.width, oneThirdH), paint);
    canvas.drawLine(
        Offset(0, twoThirdsH), Offset(size.width, twoThirdsH), paint);

    if (isCircle) {
      canvas.restore();
    }
  }

  @override
  bool shouldRepaint(_GridGuidePainter oldDelegate) =>
      oldDelegate.isCircle != isCircle || oldDelegate.gridColor != gridColor;
}

import 'dart:math' as math;

import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';

enum BuddyMood {
  happy,
  excited,
  thinking,
  love,
  sad,
  wink;

  static BuddyMood parse(String value) => BuddyMood.values.firstWhere(
        (mood) => mood.name == value,
        orElse: () => BuddyMood.happy,
      );
}

/// Cozy, the lavender teddy bear with a rose-gold bow. Breathes, sways, blinks
/// and wiggles its ears on its own; call [BuddyBearState.wave] for a hello.
class BuddyBear extends StatefulWidget {
  final double size;
  final BuddyMood mood;
  final VoidCallback? onTap;

  const BuddyBear({
    super.key,
    required this.size,
    this.mood = BuddyMood.happy,
    this.onTap,
  });

  @override
  State<BuddyBear> createState() => BuddyBearState();
}

class BuddyBearState extends State<BuddyBear> with TickerProviderStateMixin {
  late final AnimationController _idle = AnimationController(
    vsync: this,
    duration: const Duration(seconds: 12),
  );
  late final AnimationController _wave = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1800),
  );
  late final AnimationController _bounce = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1100),
  );

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (MediaQuery.maybeDisableAnimationsOf(context) ?? false) {
      _idle.stop();
    } else if (!_idle.isAnimating) {
      _idle.repeat();
    }
  }

  @override
  void didUpdateWidget(BuddyBear oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.mood != oldWidget.mood &&
        (widget.mood == BuddyMood.excited || widget.mood == BuddyMood.love)) {
      _bounce.forward(from: 0);
    }
  }

  void wave() => _wave.forward(from: 0);

  void bounce() => _bounce.forward(from: 0);

  @override
  void dispose() {
    _idle.dispose();
    _wave.dispose();
    _bounce.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Semantics(
      label: 'Cozy the teddy bear',
      button: widget.onTap != null,
      onTap: widget.onTap,
      excludeSemantics: true,
      child: GestureDetector(
        onTap: widget.onTap,
        behavior: HitTestBehavior.opaque,
        child: RepaintBoundary(
          child: SizedBox(
            width: widget.size,
            height: widget.size,
            child: AnimatedBuilder(
              animation: Listenable.merge([_idle, _wave, _bounce]),
              builder: (_, __) => CustomPaint(
                painter: BuddyBearPainter(
                  mood: widget.mood,
                  seconds: _idle.value * 12,
                  wave: _wave.value,
                  bounce: _bounce.value,
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Paints the cartoon bear in a 200 x 200 space, matching the website's SVG.
class BuddyBearPainter extends CustomPainter {
  final BuddyMood mood;
  final double seconds;
  final double wave;
  final double bounce;

  BuddyBearPainter({
    required this.mood,
    this.seconds = 0,
    this.wave = 0,
    this.bounce = 0,
  });

  static const _outline = Color(0xFF4A3D5C);
  static const _eye = Color(0xFF2F2740);
  static const _eyeGlow = CartzyColors.brandDeep;
  static const _fur = Color(0xFFCFC6E6);
  static const _furHead = Color(0xFFD6CEEA);
  static const _furLight = Color(0xFFE9E3F5);
  static const _rose = Color(0xFFF5CFC7);
  static const _cheek = Color(0xFFF2B3B0);
  static const _cream = CartzyColors.rose50Warm;
  static const _gold = CartzyColors.rose400;
  static const _goldDeep = Color(0xFFB76E79);
  static const _tear = Color(0xFFBFD6EE);

  Rect _oval(double cx, double cy, double rx, double ry) =>
      Rect.fromCenter(center: Offset(cx, cy), width: rx * 2, height: ry * 2);

  Paint _fill(Color color) => Paint()..color = color;

  Paint _line(Color color, double width) => Paint()
    ..color = color
    ..style = PaintingStyle.stroke
    ..strokeWidth = width
    ..strokeCap = StrokeCap.round
    ..strokeJoin = StrokeJoin.round;

  void _blob(Canvas canvas, Rect rect, Color color) {
    canvas.drawOval(rect, _fill(color));
    canvas.drawOval(rect, _line(_outline, 5));
  }

  void _outlined(Canvas canvas, Path path, Color color, [double width = 4]) {
    canvas.drawPath(path, _fill(color));
    canvas.drawPath(path, _line(_outline, width));
  }

  void _rotateAround(Canvas canvas, double x, double y, double radians) {
    canvas.translate(x, y);
    canvas.rotate(radians);
    canvas.translate(-x, -y);
  }

  Path _arc(double x1, double y1, double cx, double cy, double x2, double y2) => Path()
    ..moveTo(x1, y1)
    ..quadraticBezierTo(cx, cy, x2, y2);

  @override
  void paint(Canvas canvas, Size size) {
    final t = seconds * 2 * math.pi;
    canvas.save();
    canvas.scale(size.width / 200, size.height / 200);

    // Bounce when delighted.
    if (bounce > 0 && bounce < 1) {
      canvas.translate(0, -14 * math.sin(bounce * 2 * math.pi * 2).abs());
    }

    // Breathing squashes the body slightly around the feet.
    final breathe = math.sin(t / 3.2);
    canvas.save();
    canvas.translate(100, 185);
    canvas.scale(1 + .025 * breathe, 1 - .025 * breathe);
    canvas.translate(-100, -185);
    _paintBody(canvas, t);
    canvas.restore();

    // The head sways; pondering is a bigger tilt.
    final tilt = switch (mood) {
      BuddyMood.thinking => .1 * math.sin(t / 1.8),
      BuddyMood.sad => -.09,
      _ => .026 * math.sin(t / 5),
    };
    canvas.save();
    _rotateAround(canvas, 100, 135, tilt);
    if (mood == BuddyMood.sad) canvas.translate(0, 4);
    _paintHead(canvas, t);
    canvas.restore();

    canvas.restore();
  }

  void _paintBody(Canvas canvas, double t) {
    for (final x in [72.0, 128.0]) {
      _blob(canvas, _oval(x, 183, 18, 12.5), _fur);
      canvas.drawOval(_oval(x, 185, 9, 6), _fill(_rose));
    }

    _blob(canvas, _oval(65, 152, 12, 18), _fur);

    // The screen-right arm lifts up and out to wave, or scratches behind the head.
    var lift = 0.0;
    if (wave > 0 && wave < 1) {
      final raise = math.min(1.0, math.min(wave, 1 - wave) * 8);
      lift = raise * (2.3 + .35 * math.sin(wave * 2 * math.pi * 4));
    } else if (mood == BuddyMood.thinking) {
      lift = 2.5 + .13 * math.sin(t / 1.2);
    }
    canvas.save();
    _rotateAround(canvas, 135, 136, -lift);
    _blob(canvas, _oval(135, 152, 12, 18), _fur);
    canvas.restore();

    _blob(canvas, _oval(100, 158, 35, 29), _fur);
    canvas.drawOval(_oval(100, 165, 21, 17), _fill(_cream));
    canvas.drawPath(_heart(100, 165, 24), _fill(_gold));
  }

  void _paintEar(Canvas canvas, double cx, double phase, double t) {
    final cycle = ((t / (2 * math.pi) + phase) % 6) / 6;
    final angle = cycle > .9 ? .21 * math.sin((cycle - .9) * 10 * 2 * math.pi) : 0.0;
    canvas.save();
    _rotateAround(canvas, cx, 38, angle);
    _blob(canvas, _oval(cx, 38, 24, 24), _fur);
    canvas.drawOval(_oval(cx, 39, 12.5, 12.5), _fill(_rose));
    canvas.restore();
  }

  void _paintHead(Canvas canvas, double t) {
    _paintEar(canvas, 38, 0, t);
    _paintEar(canvas, 162, 1.2, t);
    _blob(canvas, _oval(100, 78, 74, 62), _furHead);

    canvas.save();
    _rotateAround(canvas, 86, 36, -.14);
    canvas.drawOval(_oval(86, 36, 34, 13), _fill(_furLight));
    canvas.restore();

    final tuft = _line(_outline, 4.5);
    canvas.drawPath(_arc(92, 18, 94, 9, 101, 7), tuft);
    canvas.drawPath(_arc(101, 17, 104, 10, 111, 10), tuft);

    canvas.drawOval(_oval(38, 106, 14, 9), _fill(_cheek));
    canvas.drawOval(_oval(162, 106, 14, 9), _fill(_cheek));
    canvas.drawOval(_oval(100, 108, 25, 19), _fill(_cream));

    _paintEyes(canvas, t);

    final nose = Path()
      ..moveTo(100, 104)
      ..cubicTo(94, 102, 90, 99, 90, 96)
      ..cubicTo(90, 93, 94, 91, 100, 91)
      ..cubicTo(106, 91, 110, 93, 110, 96)
      ..cubicTo(110, 99, 106, 102, 100, 104)
      ..close();
    canvas.drawPath(nose, _fill(CartzyColors.rose700));
    canvas.drawOval(_oval(96, 94.5, 3.2, 1.7), _fill(Colors.white));

    _paintMouth(canvas);
    _paintBow(canvas, t);
  }

  void _bigEye(Canvas canvas, double cx, double cy, double glintX, double glintY, {double openness = 1}) {
    canvas.drawOval(_oval(cx, cy, 16, 19 * openness), _fill(_eye));
    if (openness < .5) return;
    canvas.drawOval(_oval(cx, cy + 9, 10, 7), _fill(_eyeGlow));
    canvas.drawCircle(Offset(cx + glintX, cy + glintY), 6.5, _fill(Colors.white));
    canvas.drawCircle(Offset(cx - 6, cy + 6), 3, _fill(Colors.white));
  }

  void _paintEyes(Canvas canvas, double t) {
    final closed = _line(_eye, 6.5);
    switch (mood) {
      case BuddyMood.happy:
        // A quick blink every few seconds.
        final open = (seconds % 4.6) > 4.42 ? .08 : 1.0;
        _bigEye(canvas, 62, 84, 6, -8, openness: open);
        _bigEye(canvas, 138, 84, 6, -8, openness: open);
      case BuddyMood.excited:
        canvas.drawPath(_arc(45, 90, 62, 66, 79, 90), closed);
        canvas.drawPath(_arc(121, 90, 138, 66, 155, 90), closed);
        canvas.drawPath(_sparkle(26, 74.5, 8.5), _fill(_gold));
        canvas.drawPath(_sparkle(174, 74.5, 8.5), _fill(_gold));
      case BuddyMood.love:
        final beat = 1 + .07 * math.sin(t * 1.5);
        _outlined(canvas, _heart(62, 84, 44 * beat), _goldDeep);
        _outlined(canvas, _heart(138, 84, 44 * beat), _goldDeep);
        canvas.drawCircle(const Offset(54, 76), 3.5, _fill(Colors.white));
        canvas.drawCircle(const Offset(130, 76), 3.5, _fill(Colors.white));
      case BuddyMood.sad:
        _bigEye(canvas, 62, 87, 5, -7);
        _bigEye(canvas, 138, 87, 5, -7);
        final brow = _line(_outline, 5);
        canvas.drawLine(const Offset(46, 63), const Offset(66, 55), brow);
        canvas.drawLine(const Offset(154, 63), const Offset(134, 55), brow);
        final tear = Path()
          ..moveTo(154, 100)
          ..cubicTo(148, 108, 146, 112, 150, 116)
          ..cubicTo(154, 120, 161, 116, 159, 111)
          ..cubicTo(158, 107, 156, 103, 154, 100)
          ..close();
        _outlined(canvas, tear, _tear, 3);
      case BuddyMood.wink:
        _bigEye(canvas, 62, 84, 6, -8);
        canvas.drawPath(_arc(121, 88, 138, 68, 155, 88), closed);
      case BuddyMood.thinking:
        _bigEye(canvas, 62, 82, 8, -10);
        _bigEye(canvas, 138, 82, 8, -10);
    }
  }

  void _paintMouth(Canvas canvas) {
    final line = _line(_outline, 4);
    switch (mood) {
      case BuddyMood.happy:
      case BuddyMood.love:
      case BuddyMood.wink:
        canvas.drawLine(const Offset(100, 104), const Offset(100, 107), line);
        canvas.drawPath(
          _arc(87, 108, 93.5, 116, 100, 107)..quadraticBezierTo(106.5, 116, 113, 108),
          line,
        );
      case BuddyMood.excited:
        _outlined(
          canvas,
          Path()
            ..moveTo(84, 106)
            ..quadraticBezierTo(100, 130, 116, 106)
            ..quadraticBezierTo(100, 111, 84, 106)
            ..close(),
          _goldDeep,
        );
      case BuddyMood.sad:
        canvas.drawLine(const Offset(100, 104), const Offset(100, 108), line);
        canvas.drawPath(_arc(89, 118, 100, 107, 111, 118), line);
      case BuddyMood.thinking:
        canvas.drawLine(const Offset(100, 104), const Offset(100, 107), line);
        _blob(canvas, _oval(100, 113, 5, 6), _goldDeep);
    }
  }

  void _paintBow(Canvas canvas, double t) {
    // A little flutter every few seconds.
    final cycle = (seconds % 4) / 4;
    final wiggle = cycle > .88 ? .15 * math.sin((cycle - .88) / .12 * 2 * math.pi) : 0.0;
    canvas.save();
    _rotateAround(canvas, 136, 22, wiggle);

    _outlined(
      canvas,
      Path()
        ..moveTo(136, 22)
        ..lineTo(112, 9)
        ..quadraticBezierTo(106, 6, 106, 13)
        ..lineTo(106, 33)
        ..quadraticBezierTo(106, 40, 112, 37)
        ..close(),
      _gold,
      4.5,
    );
    _outlined(
      canvas,
      Path()
        ..moveTo(136, 22)
        ..lineTo(160, 9)
        ..quadraticBezierTo(166, 6, 166, 13)
        ..lineTo(166, 33)
        ..quadraticBezierTo(166, 40, 160, 37)
        ..close(),
      _gold,
      4.5,
    );
    final fold = _line(_goldDeep, 3);
    canvas.drawLine(const Offset(115, 17), const Offset(126, 22), fold);
    canvas.drawLine(const Offset(115, 29), const Offset(126, 24), fold);
    canvas.drawLine(const Offset(157, 17), const Offset(146, 22), fold);
    canvas.drawLine(const Offset(157, 29), const Offset(146, 24), fold);
    final knot = RRect.fromRectAndRadius(const Rect.fromLTWH(128, 14, 16, 17), const Radius.circular(6));
    canvas.drawRRect(knot, _fill(_goldDeep));
    canvas.drawRRect(knot, _line(_outline, 4.5));
    canvas.restore();
  }

  /// A cartoon heart about [size] wide, centered on ([cx], [cy]).
  Path _heart(double cx, double cy, double size) {
    final s = size / 2;
    final top = cy - s * .55;
    return Path()
      ..moveTo(cx, cy + s * .95)
      ..cubicTo(cx - s * 1.3, cy + s * .2, cx - s * 1.15, top - s * .8, cx, top)
      ..cubicTo(cx + s * 1.15, top - s * .8, cx + s * 1.3, cy + s * .2, cx, cy + s * .95)
      ..close();
  }

  /// A four-point sparkle around ([cx], [cy]).
  Path _sparkle(double cx, double cy, double r) {
    final k = r * .29;
    return Path()
      ..moveTo(cx, cy - r)
      ..lineTo(cx + k, cy - k)
      ..lineTo(cx + r, cy)
      ..lineTo(cx + k, cy + k)
      ..lineTo(cx, cy + r)
      ..lineTo(cx - k, cy + k)
      ..lineTo(cx - r, cy)
      ..lineTo(cx - k, cy - k)
      ..close();
  }

  @override
  bool shouldRepaint(BuddyBearPainter old) =>
      old.mood != mood || old.seconds != seconds || old.wave != wave || old.bounce != bounce;
}

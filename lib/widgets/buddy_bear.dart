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

/// Paints the bear in a 200 x 200 space, matching the website's SVG.
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

  static const _furLight = Color(0xFFE4DDF0);
  static const _furMid = Color(0xFFCBC2DC);
  static const _furDark = Color(0xFFA99FBF);
  static const _ink = Color(0xFF3E354C);
  static const _roseDeep = Color(0xFFB76E79);
  static const _blush = Color(0xFFE8B8AD);

  Paint _fur(Rect rect) => Paint()
    ..shader = const RadialGradient(
      center: Alignment(0, -0.24),
      radius: 0.75,
      colors: [_furLight, _furMid, _furDark],
      stops: [0, .65, 1],
    ).createShader(rect);

  Paint _inner(Rect rect) => Paint()
    ..shader = const RadialGradient(
      center: Alignment(0, -0.2),
      radius: 0.8,
      colors: [CartzyColors.rose50Warm, Color(0xFFF5CFC7)],
    ).createShader(rect);

  Rect _oval(double cx, double cy, double rx, double ry) =>
      Rect.fromCenter(center: Offset(cx, cy), width: rx * 2, height: ry * 2);

  void _rotateAround(Canvas canvas, double x, double y, double radians) {
    canvas.translate(x, y);
    canvas.rotate(radians);
    canvas.translate(-x, -y);
  }

  Paint _stroke(Color color, double width) => Paint()
    ..color = color
    ..style = PaintingStyle.stroke
    ..strokeWidth = width
    ..strokeCap = StrokeCap.round;

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

    // Head and bow sway together; pondering is a bigger tilt.
    final swayAngle = switch (mood) {
      BuddyMood.thinking => .1 * math.sin(t / 1.8),
      BuddyMood.sad => -.09,
      _ => .026 * math.sin(t / 5),
    };
    canvas.save();
    _rotateAround(canvas, 100, 118, swayAngle);
    if (mood == BuddyMood.sad) canvas.translate(0, 4);
    _paintHead(canvas, t);
    canvas.restore();

    canvas.save();
    _rotateAround(canvas, 100, 120, mood == BuddyMood.thinking ? 0 : .026 * math.sin(t / 5));
    _paintBow(canvas);
    canvas.restore();

    canvas.restore();
  }

  void _paintBody(Canvas canvas, double t) {
    for (final x in [68.0, 132.0]) {
      final foot = _oval(x, 182, 20, 14);
      canvas.drawOval(foot, _fur(foot));
      final pad = _oval(x, 184, 11, 7.5);
      canvas.drawOval(pad, _inner(pad));
    }

    final belly = _oval(100, 152, 40, 35);
    canvas.drawOval(belly, _fur(belly));
    final tummy = _oval(100, 158, 24, 21);
    canvas.drawOval(tummy, _inner(tummy));
    canvas.drawPath(
      _heart(100, 164, 11),
      Paint()..color = CartzyColors.rose400.withValues(alpha: .85),
    );

    final leftArm = _oval(60, 148, 13, 21);
    canvas.drawOval(leftArm, _fur(leftArm));

    // Right arm waves hello, or scratches the head while thinking.
    var armAngle = 0.0;
    if (wave > 0 && wave < 1) {
      final lift = math.min(1.0, math.min(wave, 1 - wave) * 8);
      armAngle = -lift * (2.3 + .35 * math.sin(wave * 2 * math.pi * 4));
    } else if (mood == BuddyMood.thinking) {
      armAngle = -2.5 - .13 * math.sin(t / 1.2);
    }
    canvas.save();
    // Waving reads as the far side of the bear, so it's the screen-right arm.
    _rotateAround(canvas, 140, 132, -armAngle);
    final rightArm = _oval(140, 148, 13, 21);
    canvas.drawOval(rightArm, _fur(rightArm));
    canvas.restore();
  }

  void _paintEar(Canvas canvas, double cx, double phase, double t) {
    final cycle = ((t / (2 * math.pi) + phase) % 6) / 6;
    var angle = 0.0;
    if (cycle > .9) {
      angle = .21 * math.sin((cycle - .9) * 10 * 2 * math.pi);
    }
    canvas.save();
    _rotateAround(canvas, cx, 36, angle);
    final ear = _oval(cx, 36, 25, 25);
    canvas.drawOval(ear, _fur(ear));
    final inner = _oval(cx, 37, 14.5, 14.5);
    canvas.drawOval(inner, _inner(inner));
    canvas.restore();
  }

  void _paintHead(Canvas canvas, double t) {
    _paintEar(canvas, 42, 0, t);
    _paintEar(canvas, 158, 1.2, t);

    final head = _oval(100, 72, 68, 57);
    canvas.drawOval(head, _fur(head));
    canvas.drawPath(
      Path()
        ..moveTo(86, 28)
        ..cubicTo(91, 19, 97, 17, 100, 17)
        ..cubicTo(99, 21, 101, 25, 105, 28),
      _stroke(_furDark, 3.5),
    );

    final cheek = Paint()..color = _blush.withValues(alpha: .8);
    canvas.drawOval(_oval(50, 92, 13, 8.5), cheek);
    canvas.drawOval(_oval(150, 92, 13, 8.5), cheek);
    canvas.drawOval(_oval(100, 92, 24, 19), Paint()..color = CartzyColors.rose50Warm);

    _paintEyes(canvas, t);

    canvas.drawPath(
      Path()
        ..moveTo(100, 91)
        ..cubicTo(94, 91, 90, 88, 90, 85)
        ..cubicTo(90, 82, 94, 80, 100, 80)
        ..cubicTo(106, 80, 110, 82, 110, 85)
        ..cubicTo(110, 88, 106, 91, 100, 91)
        ..close(),
      Paint()..color = CartzyColors.rose700,
    );
    canvas.drawOval(_oval(96, 83, 3, 1.6), Paint()..color = Colors.white.withValues(alpha: .7));

    _paintMouth(canvas);
  }

  void _paintOpenEye(Canvas canvas, double cx, double cy, double openness) {
    canvas.drawOval(_oval(cx, cy, 13, 15 * openness), Paint()..color = _ink);
    if (openness > .5) {
      canvas.drawCircle(Offset(cx + 4, cy - 6), 5, Paint()..color = Colors.white);
      canvas.drawCircle(Offset(cx - 5, cy + 6), 2.5, Paint()..color = Colors.white.withValues(alpha: .85));
    }
  }

  void _paintEyes(Canvas canvas, double t) {
    final ink = _stroke(_ink, 5);
    switch (mood) {
      case BuddyMood.happy:
        // A quick blink every few seconds.
        final blink = (seconds % 4.6) > 4.42 ? .08 : 1.0;
        _paintOpenEye(canvas, 66, 72, blink);
        _paintOpenEye(canvas, 134, 72, blink);
      case BuddyMood.excited:
        canvas.drawPath(_arc(53, 76, 66, 58, 79, 76), ink);
        canvas.drawPath(_arc(121, 76, 134, 58, 147, 76), ink);
      case BuddyMood.love:
        final beat = 1 + .08 * math.sin(t * 1.5);
        final heart = Paint()..color = _roseDeep;
        canvas.drawPath(_heart(66, 72, 30 * beat), heart);
        canvas.drawPath(_heart(134, 72, 30 * beat), heart);
      case BuddyMood.sad:
        // Big wobbly eyes, worried brows and one little tear.
        canvas.drawOval(_oval(66, 75, 12, 13), Paint()..color = _ink);
        canvas.drawCircle(const Offset(69, 70), 5, Paint()..color = Colors.white);
        canvas.drawOval(_oval(134, 75, 12, 13), Paint()..color = _ink);
        canvas.drawCircle(const Offset(137, 70), 5, Paint()..color = Colors.white);
        final brow = _stroke(CartzyColors.brandDeep, 4.5);
        canvas.drawLine(const Offset(53, 56), const Offset(71, 49), brow);
        canvas.drawLine(const Offset(147, 56), const Offset(129, 49), brow);
        canvas.drawPath(_heart(145, 92, 11), Paint()..color = const Color(0xFFBFD6EE).withValues(alpha: .9));
      case BuddyMood.wink:
        _paintOpenEye(canvas, 66, 72, 1);
        canvas.drawPath(_arc(121, 76, 134, 61, 147, 76), ink);
      case BuddyMood.thinking:
        canvas.drawOval(_oval(66, 70, 13, 15), Paint()..color = _ink);
        canvas.drawCircle(const Offset(72, 62), 5, Paint()..color = Colors.white);
        canvas.drawOval(_oval(134, 70, 13, 15), Paint()..color = _ink);
        canvas.drawCircle(const Offset(140, 62), 5, Paint()..color = Colors.white);
    }
  }

  void _paintMouth(Canvas canvas) {
    final line = _stroke(CartzyColors.rose700, 3.5);
    switch (mood) {
      case BuddyMood.happy:
      case BuddyMood.love:
      case BuddyMood.wink:
        canvas.drawLine(const Offset(100, 91), const Offset(100, 95), line);
        canvas.drawPath(_arc(100, 95, 92, 103, 86, 97), line);
        canvas.drawPath(_arc(100, 95, 108, 103, 114, 97), line);
      case BuddyMood.excited:
        final open = Path()
          ..moveTo(86, 96)
          ..quadraticBezierTo(100, 114, 114, 96)
          ..close();
        canvas.drawPath(open, Paint()..color = _roseDeep);
        canvas.drawPath(open, line..strokeJoin = StrokeJoin.round);
      case BuddyMood.sad:
        canvas.drawLine(const Offset(100, 91), const Offset(100, 95), line);
        canvas.drawPath(_arc(88, 104, 100, 94, 112, 104), line);
      case BuddyMood.thinking:
        canvas.drawLine(const Offset(100, 91), const Offset(100, 95), line);
        canvas.drawOval(_oval(100, 101, 4.5, 5), Paint()..color = _roseDeep);
    }
  }

  void _paintBow(Canvas canvas) {
    canvas.translate(100, 120);
    canvas.scale(.82);
    canvas.translate(-100, -120);
    const rect = Rect.fromLTWH(63, 104, 74, 32);
    final gold = Paint()
      ..shader = const LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [Color(0xFFF0C4B8), CartzyColors.rose400, _roseDeep],
        stops: [0, .55, 1],
      ).createShader(rect);

    canvas.drawPath(
      Path()
        ..moveTo(100, 120)
        ..lineTo(70, 105)
        ..quadraticBezierTo(63, 102, 63, 109)
        ..lineTo(63, 131)
        ..quadraticBezierTo(63, 138, 70, 135)
        ..close(),
      gold,
    );
    canvas.drawPath(
      Path()
        ..moveTo(100, 120)
        ..lineTo(130, 105)
        ..quadraticBezierTo(137, 102, 137, 109)
        ..lineTo(137, 131)
        ..quadraticBezierTo(137, 138, 130, 135)
        ..close(),
      gold,
    );
    final fold = _stroke(_roseDeep.withValues(alpha: .7), 2);
    canvas.drawLine(const Offset(75, 113), const Offset(88, 120), fold);
    canvas.drawLine(const Offset(75, 127), const Offset(88, 120), fold);
    canvas.drawLine(const Offset(125, 113), const Offset(112, 120), fold);
    canvas.drawLine(const Offset(125, 127), const Offset(112, 120), fold);
    canvas.drawRRect(
      RRect.fromRectAndRadius(const Rect.fromLTWH(91, 110, 18, 20), const Radius.circular(7)),
      Paint()..color = CartzyColors.rose500,
    );
    canvas.drawPath(
      _arc(96, 114, 100, 112, 104, 114),
      _stroke(CartzyColors.rose50Warm.withValues(alpha: .75), 2),
    );
  }

  Path _arc(double x1, double y1, double cx, double cy, double x2, double y2) => Path()
    ..moveTo(x1, y1)
    ..quadraticBezierTo(cx, cy, x2, y2);

  /// A heart about [size] wide, centered near ([cx], [centerY]).
  Path _heart(double cx, double centerY, double size) {
    final s = size / 2;
    final top = centerY - s * .55;
    return Path()
      ..moveTo(cx, centerY + s * 1.05)
      ..cubicTo(cx - s * 1.4, centerY + s * .1, cx - s * 1.1, top - s * .75, cx, top)
      ..cubicTo(cx + s * 1.1, top - s * .75, cx + s * 1.4, centerY + s * .1, cx, centerY + s * 1.05)
      ..close();
  }

  @override
  bool shouldRepaint(BuddyBearPainter old) =>
      old.mood != mood || old.seconds != seconds || old.wave != wave || old.bounce != bounce;
}

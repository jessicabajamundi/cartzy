import 'dart:math' as math;

import 'package:flutter/material.dart';

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

/// Cozy, the fluffy brown teddy bear. Breathes, sways, blinks, looks around
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

/// Paints the fluffy brown bear in a 200 x 200 space, matching the website's SVG.
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

  static const _outline = Color(0xFF3D1D10);
  static const _furLine = Color(0xFF6A3319);
  static const _furDark = Color(0xFF5F2E1A);
  static const _pad = Color(0xFF7A4026);
  static const _socket = Color(0xFF2B1209);
  static const _pupil = Color(0xFF1E0E08);
  static const _nose = Color(0xFF2B1209);
  static const _earInner = Color(0xFFD9B08F);
  static const _flush = Color(0x80E0855A);
  static const _mouth = Color(0xFFA5432F);
  static const _heartRed = Color(0xFFB3402F);
  static const _tear = Color(0xFFBFD6EE);

  static const _furGradient = RadialGradient(
    center: Alignment(0, -.3),
    radius: .7,
    colors: [Color(0xFFBE6C40), Color(0xFF96492A), Color(0xFF6E341C)],
    stops: [0, .6, 1],
  );
  static const _creamGradient = RadialGradient(
    center: Alignment(0, -.3),
    radius: .7,
    colors: [Color(0xFFFCEBD8), Color(0xFFE4BE9B)],
  );
  static const _irisGradient = RadialGradient(
    center: Alignment(0, .4),
    radius: .65,
    colors: [Color(0xFFE0A873), _pad],
  );

  /// Where the eyes wander, as a looping path of targets one step apart.
  static const _gazePath = [
    Offset(0, 0), Offset(-4, -1), Offset(-4, 0), Offset(0, 0), Offset(4, 1), Offset(3, -3),
    Offset(0, 0), Offset(0, 3), Offset(-3, 3), Offset(0, 0), Offset(4, 0), Offset(0, -3),
  ];

  Rect _oval(double cx, double cy, double rx, double ry) =>
      Rect.fromCenter(center: Offset(cx, cy), width: rx * 2, height: ry * 2);

  Paint _fill(Color color) => Paint()..color = color;

  Paint _line(Color color, double width) => Paint()
    ..color = color
    ..style = PaintingStyle.stroke
    ..strokeWidth = width
    ..strokeCap = StrokeCap.round
    ..strokeJoin = StrokeJoin.round;

  void _rotateAround(Canvas canvas, double x, double y, double radians) {
    canvas.translate(x, y);
    canvas.rotate(radians);
    canvas.translate(-x, -y);
  }

  Path _arc(double x1, double y1, double cx, double cy, double x2, double y2) => Path()
    ..moveTo(x1, y1)
    ..quadraticBezierTo(cx, cy, x2, y2);

  /// An ellipse with a tufted edge, the same shape as the website's fur paths.
  void _furry(Canvas canvas, double cx, double cy, double rx, double ry, int tufts, double depth, {RadialGradient gradient = _furGradient}) {
    Offset at(double angle, double extra) =>
        Offset(cx + math.cos(angle) * (rx + extra), cy + math.sin(angle) * (ry + extra));

    final step = 2 * math.pi / tufts;
    final path = Path();
    final start = at(0, 0);
    path.moveTo(start.dx, start.dy);
    for (var i = 0; i < tufts; i++) {
      final a = i * step;
      final d = depth * (.7 + .3 * math.sin(i * 2.7));
      final c1 = at(a + step * .35, d * .75);
      final tip = at(a + step * .85, d);
      final c2 = at(a + step * .9, d * .35);
      final next = at(a + step, 0);
      path.quadraticBezierTo(c1.dx, c1.dy, tip.dx, tip.dy);
      path.quadraticBezierTo(c2.dx, c2.dy, next.dx, next.dy);
    }
    path.close();
    canvas.drawPath(path, Paint()..shader = gradient.createShader(_oval(cx, cy, rx, ry)));
    canvas.drawPath(path, _line(_furLine, 1.6));
  }

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

  void _paintFoot(Canvas canvas, double x, double degrees) {
    _furry(canvas, x, 172, 21, 22, 30, 1.5);
    canvas.save();
    canvas.translate(x, 172);
    canvas.rotate(degrees * math.pi / 180);
    canvas.drawOval(_oval(0, 6, 11, 10), _fill(_pad));
    for (final toe in const [Offset(-11, -8), Offset(-4, -12), Offset(4, -12), Offset(11, -8)]) {
      canvas.drawCircle(toe, 4.5, _fill(_pad));
    }
    canvas.restore();
  }

  void _paintBody(Canvas canvas, double t) {
    _furry(canvas, 168, 168, 14, 15, 22, 2.5);
    _furry(canvas, 100, 150, 39, 40, 46, 2.5);
    _furry(canvas, 100, 142, 18, 26, 26, 3, gradient: _creamGradient);
    _furry(canvas, 70, 166, 12, 20, 24, 2);

    // The screen-right arm lifts up and out to wave, or scratches behind the head.
    var lift = 0.0;
    if (wave > 0 && wave < 1) {
      final raise = math.min(1.0, math.min(wave, 1 - wave) * 8);
      lift = raise * (2.3 + .35 * math.sin(wave * 2 * math.pi * 4));
    } else if (mood == BuddyMood.thinking) {
      lift = 2.5 + .13 * math.sin(t / 1.2);
    }
    canvas.save();
    _rotateAround(canvas, 130, 148, -lift);
    _furry(canvas, 130, 166, 12, 20, 24, 2);
    canvas.restore();

    _paintFoot(canvas, 47, -12);
    _paintFoot(canvas, 153, 12);
  }

  void _paintEar(Canvas canvas, double cx, double innerX, double phase, double t) {
    final cycle = ((t / (2 * math.pi) + phase) % 6) / 6;
    final angle = cycle > .9 ? .21 * math.sin((cycle - .9) * 10 * 2 * math.pi) : 0.0;
    canvas.save();
    _rotateAround(canvas, cx, 32, angle);
    _furry(canvas, cx, 32, 24, 24, 34, 2.5);
    canvas.drawOval(_oval(innerX, 36, 12, 13), _fill(_earInner));
    canvas.restore();
  }

  void _paintHead(Canvas canvas, double t) {
    _paintEar(canvas, 36, 38, 0, t);
    _paintEar(canvas, 164, 162, 1.2, t);
    _furry(canvas, 100, 76, 74, 62, 72, 3.5);

    final tuft = _line(_furDark, 3.5);
    canvas.drawPath(_arc(84, 20, 87, 9, 97, 7), tuft);
    canvas.drawPath(_arc(95, 18, 99, 9, 108, 9), tuft);
    canvas.drawPath(_arc(106, 19, 111, 12, 119, 14), tuft);

    canvas.drawOval(_oval(44, 104, 15, 9), _fill(_flush));
    canvas.drawOval(_oval(156, 104, 15, 9), _fill(_flush));
    final muzzle = _oval(100, 108, 30, 23);
    canvas.drawOval(muzzle, Paint()..shader = _creamGradient.createShader(muzzle));

    _paintFace(canvas, t);

    canvas.drawOval(_oval(100, 97, 12, 8.5), _fill(_nose));
    canvas.drawOval(_oval(96, 94, 3.5, 2), _fill(Colors.white.withValues(alpha: .85)));
    _paintMouth(canvas);
  }

  /// The eyes' current wander, eased between the points of [_gazePath].
  Offset get _gaze {
    final position = (seconds / 1.5) % _gazePath.length;
    final index = position.floor();
    final progress = Curves.easeInOut.transform(math.min(1.0, (position - index) * 3));
    return Offset.lerp(_gazePath[index], _gazePath[(index + 1) % _gazePath.length], progress)!;
  }

  void _bigEye(Canvas canvas, double cx, double cy, Offset gaze, {double openness = 1}) {
    canvas.save();
    canvas.translate(cx, cy);
    canvas.scale(1, openness);
    canvas.translate(-cx, -cy);

    canvas.drawCircle(Offset(cx, cy), 20, _fill(_socket));
    if (openness > .5) {
      final x = cx + gaze.dx;
      final y = cy + gaze.dy;
      final iris = _oval(x, y, 16, 16);
      canvas.drawOval(iris, Paint()..shader = _irisGradient.createShader(iris));
      canvas.drawCircle(Offset(x, y), 10.5, _fill(_pupil));
      canvas.drawCircle(Offset(x + 6, y - 7), 6.5, _fill(Colors.white));
      canvas.drawCircle(Offset(x - 6, y + 7), 3, _fill(Colors.white));
    }
    canvas.restore();
  }

  void _brows(Canvas canvas, double left, double right) {
    final brow = _line(_furDark, 3);
    canvas.drawPath(_arc(54, left, 63, left - 6, 72, left - 3), brow);
    canvas.drawPath(_arc(128, right, 137, right - 3, 146, right + 3), brow);
  }

  void _paintFace(Canvas canvas, double t) {
    final closed = _line(_outline, 6.5);
    switch (mood) {
      case BuddyMood.happy:
        // A quick blink every few seconds.
        final open = (seconds % 4.6) > 4.42 ? .08 : 1.0;
        _brows(canvas, 50, 47);
        _bigEye(canvas, 66, 80, _gaze, openness: open);
        _bigEye(canvas, 134, 80, _gaze, openness: open);
      case BuddyMood.excited:
        canvas.drawPath(_arc(48, 86, 66, 62, 84, 86), closed);
        canvas.drawPath(_arc(116, 86, 134, 62, 152, 86), closed);
      case BuddyMood.love:
        final beat = 1 + .07 * math.sin(t * 1.5);
        for (final x in const [66.0, 134.0]) {
          final heart = _heart(x, 78, 48 * beat);
          canvas.drawPath(heart, _fill(_heartRed));
          canvas.drawPath(heart, _line(_outline, 3.5));
          canvas.drawCircle(Offset(x - 9, 72), 4, _fill(Colors.white));
        }
      case BuddyMood.sad:
        final brow = _line(_furDark, 3.5);
        canvas.drawLine(const Offset(50, 58), const Offset(70, 50), brow);
        canvas.drawLine(const Offset(150, 58), const Offset(130, 50), brow);
        final gaze = _gaze + const Offset(0, 2);
        _bigEye(canvas, 66, 82, gaze);
        _bigEye(canvas, 134, 82, gaze);
        final tear = Path()
          ..moveTo(152, 96)
          ..cubicTo(146, 104, 144, 108, 148, 112)
          ..cubicTo(152, 116, 159, 112, 157, 107)
          ..cubicTo(156, 103, 154, 99, 152, 96)
          ..close();
        canvas.drawPath(tear, _fill(_tear));
        canvas.drawPath(tear, _line(_outline, 2.5));
      case BuddyMood.wink:
        _brows(canvas, 50, 47);
        _bigEye(canvas, 66, 80, _gaze);
        canvas.drawPath(_arc(116, 84, 134, 64, 152, 84), closed);
      case BuddyMood.thinking:
        _brows(canvas, 46, 42);
        const up = Offset(3, -4);
        _bigEye(canvas, 66, 80, up);
        _bigEye(canvas, 134, 80, up);
    }
  }

  void _paintMouth(Canvas canvas) {
    final line = _line(_outline, 3.5);
    switch (mood) {
      case BuddyMood.happy:
      case BuddyMood.love:
      case BuddyMood.wink:
        canvas.drawLine(const Offset(100, 105), const Offset(100, 110), line);
        canvas.drawPath(_arc(82, 110, 100, 126, 118, 110), line);
      case BuddyMood.excited:
        final open = Path()
          ..moveTo(82, 108)
          ..quadraticBezierTo(100, 136, 118, 108)
          ..quadraticBezierTo(100, 114, 82, 108)
          ..close();
        canvas.drawPath(open, _fill(_mouth));
        canvas.drawPath(open, line);
      case BuddyMood.sad:
        canvas.drawLine(const Offset(100, 105), const Offset(100, 110), line);
        canvas.drawPath(_arc(88, 122, 100, 111, 112, 122), line);
      case BuddyMood.thinking:
        canvas.drawLine(const Offset(100, 105), const Offset(100, 108), line);
        canvas.drawOval(_oval(100, 116, 5.5, 6.5), _fill(_mouth));
        canvas.drawOval(_oval(100, 116, 5.5, 6.5), line);
    }
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

  @override
  bool shouldRepaint(BuddyBearPainter old) =>
      old.mood != mood || old.seconds != seconds || old.wave != wave || old.bounce != bounce;
}

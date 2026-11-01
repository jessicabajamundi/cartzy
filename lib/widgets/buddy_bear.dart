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

/// Cozy, the soft rose-gold teddy bear with a lavender bow. Breathes, sways, blinks, looks around
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

/// Paints the soft rose-gold bear in a 200 x 200 space, matching the website's SVG.
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

  static const _outline = Color(0xFFA47065);
  static const _fur = Color(0xFFEFC9BC);
  static const _furLight = Color(0xFFF9E3DA);
  static const _pad = Color(0xFFD9A797);
  static const _cream = Color(0xFFFFF5F3);
  static const _earInner = Color(0xFFE1DDE7);
  static const _cheek = Color(0xB3F2A9A0);
  static const _eye = Color(0xFF4A2520);
  static const _nose = Color(0xFF8C5A50);
  static const _rose = Color(0xFFC96F7E);
  static const _tear = Color(0xFFC7DDF0);
  static const _bow = Color(0xFFA8A0B2);
  static const _bowDark = Color(0xFF6F6382);

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

  void _furOval(Canvas canvas, Rect rect) {
    canvas.drawOval(rect, _fill(_fur));
    canvas.drawOval(rect, _line(_outline, 3.5));
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

  void _paintFoot(Canvas canvas, double x) {
    _furOval(canvas, _oval(x, 176, 17, 17));
    canvas.drawOval(_oval(x, 180, 8, 6.5), _fill(_pad));
    for (final toe in [Offset(x - 8, 170), Offset(x, 167), Offset(x + 8, 170)]) {
      canvas.drawCircle(toe, 3.2, _fill(_pad));
    }
  }

  void _paintBody(Canvas canvas, double t) {
    _furOval(canvas, _oval(166, 168, 10, 10));
    _furOval(canvas, _oval(68, 154, 11, 17));

    // The screen-right arm lifts up and out to wave, or scratches behind the head.
    var lift = 0.0;
    if (wave > 0 && wave < 1) {
      final raise = math.min(1.0, math.min(wave, 1 - wave) * 8);
      lift = raise * (2.3 + .35 * math.sin(wave * 2 * math.pi * 4));
    } else if (mood == BuddyMood.thinking) {
      lift = 2.5 + .13 * math.sin(t / 1.2);
    }
    canvas.save();
    _rotateAround(canvas, 132, 140, -lift);
    _furOval(canvas, _oval(132, 154, 11, 17));
    canvas.restore();

    _furOval(canvas, _oval(100, 156, 36, 32));
    canvas.drawOval(_oval(100, 162, 22, 20), _fill(_cream));
    _paintFoot(canvas, 62);
    _paintFoot(canvas, 138);
  }

  void _paintEar(Canvas canvas, double cx, double phase, double t) {
    final cycle = ((t / (2 * math.pi) + phase) % 6) / 6;
    final angle = cycle > .9 ? .21 * math.sin((cycle - .9) * 10 * 2 * math.pi) : 0.0;
    canvas.save();
    _rotateAround(canvas, cx, 38, angle);
    _furOval(canvas, _oval(cx, 38, 22, 22));
    canvas.drawCircle(Offset(cx, 39), 12, _fill(_earInner));
    canvas.restore();
  }

  void _paintHead(Canvas canvas, double t) {
    _paintEar(canvas, 38, 0, t);
    _paintEar(canvas, 162, 1.2, t);
    _furOval(canvas, _oval(100, 80, 72, 60));

    canvas.save();
    _rotateAround(canvas, 88, 42, -.14);
    canvas.drawOval(_oval(88, 42, 32, 12), _fill(_furLight));
    canvas.restore();

    canvas.drawOval(_oval(42, 110, 13, 8), _fill(_cheek));
    canvas.drawOval(_oval(158, 110, 13, 8), _fill(_cheek));
    canvas.drawOval(_oval(100, 112, 21, 16), _fill(_cream));

    _paintFace(canvas, t);

    canvas.drawOval(_oval(100, 106, 7, 4.8), _fill(_nose));
    _paintMouth(canvas);
    _paintBow(canvas);
  }

  /// The eyes' current wander, eased between the points of [_gazePath].
  Offset get _gaze {
    final position = (seconds / 1.5) % _gazePath.length;
    final index = position.floor();
    final progress = Curves.easeInOut.transform(math.min(1.0, (position - index) * 3));
    return Offset.lerp(_gazePath[index], _gazePath[(index + 1) % _gazePath.length], progress)!;
  }

  void _softEye(Canvas canvas, double cx, double cy, Offset gaze, {double openness = 1}) {
    final x = cx + gaze.dx;
    final y = cy + gaze.dy;
    canvas.drawOval(_oval(x, y, 12, 15 * openness), _fill(_eye));
    if (openness > .5) {
      canvas.drawCircle(Offset(x + 4, y - 6), 5, _fill(Colors.white));
      canvas.drawCircle(Offset(x - 4, y + 6), 2.5, _fill(Colors.white));
    }
  }

  void _paintFace(Canvas canvas, double t) {
    final closed = _line(_eye, 5.5);
    switch (mood) {
      case BuddyMood.happy:
        // A quick blink every few seconds.
        final open = (seconds % 4.6) > 4.42 ? .08 : 1.0;
        _softEye(canvas, 64, 88, _gaze, openness: open);
        _softEye(canvas, 136, 88, _gaze, openness: open);
      case BuddyMood.excited:
        canvas.drawPath(_arc(50, 92, 64, 74, 78, 92), closed);
        canvas.drawPath(_arc(122, 92, 136, 74, 150, 92), closed);
      case BuddyMood.love:
        final beat = 1 + .07 * math.sin(t * 1.5);
        for (final x in const [64.0, 136.0]) {
          canvas.drawPath(_heart(x, 86, 38 * beat), _fill(_rose));
          canvas.drawCircle(Offset(x - 8, 80), 3, _fill(Colors.white));
        }
      case BuddyMood.sad:
        final brow = _line(_outline, 4);
        canvas.drawLine(const Offset(52, 66), const Offset(70, 60), brow);
        canvas.drawLine(const Offset(148, 66), const Offset(130, 60), brow);
        _softEye(canvas, 64, 90, _gaze);
        _softEye(canvas, 136, 90, _gaze);
        final tear = Path()
          ..moveTo(150, 104)
          ..cubicTo(145, 111, 143, 114, 147, 118)
          ..cubicTo(151, 122, 157, 118, 155, 113)
          ..cubicTo(154, 110, 152, 107, 150, 104)
          ..close();
        canvas.drawPath(tear, _fill(_tear));
      case BuddyMood.wink:
        _softEye(canvas, 64, 88, _gaze);
        canvas.drawPath(_arc(122, 90, 136, 75, 150, 90), closed);
      case BuddyMood.thinking:
        const up = Offset(2, -3);
        _softEye(canvas, 64, 88, up);
        _softEye(canvas, 136, 88, up);
    }
  }

  void _paintMouth(Canvas canvas) {
    final line = _line(_nose, 3);
    switch (mood) {
      case BuddyMood.happy:
      case BuddyMood.love:
      case BuddyMood.wink:
        canvas.drawPath(_arc(90, 113, 95, 120, 100, 114)..quadraticBezierTo(105, 120, 110, 113), line);
      case BuddyMood.excited:
        final open = Path()
          ..moveTo(88, 112)
          ..quadraticBezierTo(100, 130, 112, 112)
          ..quadraticBezierTo(100, 116, 88, 112)
          ..close();
        canvas.drawPath(open, _fill(_rose));
        canvas.drawPath(open, line);
      case BuddyMood.sad:
        canvas.drawPath(_arc(91, 121, 100, 113, 109, 121), line);
      case BuddyMood.thinking:
        canvas.drawOval(_oval(100, 118, 4, 5), _fill(_rose));
    }
  }

  void _paintBow(Canvas canvas) {
    // A little flutter every few seconds.
    final cycle = (seconds % 4) / 4;
    final wiggle = cycle > .88 ? .15 * math.sin((cycle - .88) / .12 * 2 * math.pi) : 0.0;
    canvas.save();
    _rotateAround(canvas, 136, 26, wiggle);
    for (final side in const [-1.0, 1.0]) {
      final wing = Path()
        ..moveTo(136, 26)
        ..lineTo(136 + side * 22, 14)
        ..quadraticBezierTo(136 + side * 28, 11, 136 + side * 28, 18)
        ..lineTo(136 + side * 28, 34)
        ..quadraticBezierTo(136 + side * 28, 41, 136 + side * 22, 38)
        ..close();
      canvas.drawPath(wing, _fill(_bow));
      canvas.drawPath(wing, _line(_bowDark, 3.5));
    }
    canvas.drawRRect(
      RRect.fromRectAndRadius(const Rect.fromLTWH(129, 19, 14, 14), const Radius.circular(5)),
      _fill(_bowDark),
    );
    canvas.restore();
  }

  /// A heart about [size] wide, centered on ([cx], [cy]).
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

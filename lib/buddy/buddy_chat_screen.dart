import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/products/product_details_screen.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/buddy_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/buddy_bear.dart';

/// Full-screen chat with Cozy the teddy bear.
class BuddyChatScreen extends StatefulWidget {
  const BuddyChatScreen({super.key});

  @override
  State<BuddyChatScreen> createState() => _BuddyChatScreenState();
}

class _BuddyChatScreenState extends State<BuddyChatScreen> {
  final _input = TextEditingController();
  final _scroll = ScrollController();
  final _bearKey = GlobalKey<BuddyBearState>();
  final List<_FloatingHeart> _hearts = [];
  final _random = math.Random();

  BuddyMood _mood = BuddyMood.happy;
  List<String> _suggestions = const [];
  bool _busy = false;
  String? _speaking;
  Timer? _moodTimer;
  Timer? _speakTimer;

  String get _firstName {
    final name = AuthService.currentUser?['name']?.toString().trim() ?? '';
    return name.isEmpty ? '' : name.split(RegExp(r'\s+')).first;
  }

  // Cozy's opening line is greeting only: it's shown but never sent to the server.
  String get _welcome {
    final hello = _firstName.isEmpty ? 'Hi hi!' : 'Hi $_firstName!';
    return "$hello I'm Cozy, your cuddly shopping buddy. Want to chat, hunt for something cute, or check on an order?";
  }

  static const _starters = ['Help me find a gift', "What's new today?", "Where's my order?"];

  @override
  void initState() {
    super.initState();
    if (BuddyService.messages.isEmpty) _suggestions = _starters;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _bearKey.currentState?.wave();
      _scrollDown();
    });
  }

  @override
  void dispose() {
    _moodTimer?.cancel();
    _speakTimer?.cancel();
    _input.dispose();
    _scroll.dispose();
    super.dispose();
  }

  void _scrollDown() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!_scroll.hasClients) return;
      _scroll.animateTo(
        _scroll.position.maxScrollExtent,
        duration: const Duration(milliseconds: 220),
        curve: Curves.easeOut,
      );
    });
  }

  void _setMood(BuddyMood mood, {Duration? hold}) {
    _moodTimer?.cancel();
    setState(() => _mood = mood);
    if (hold != null) {
      _moodTimer = Timer(hold, () {
        if (mounted) setState(() => _mood = BuddyMood.happy);
      });
    }
  }

  void _floatHearts([int count = 5]) {
    if (MediaQuery.disableAnimationsOf(context)) return;
    setState(() {
      for (var i = 0; i < count; i++) {
        _hearts.add(_FloatingHeart(
          key: UniqueKey(),
          left: 8 + _random.nextDouble() * 54,
          delay: Duration(milliseconds: i * 90),
          color: i.isEven ? CartzyColors.rose400 : Colors.white,
          onDone: (heart) {
            if (mounted) setState(() => _hearts.remove(heart));
          },
        ));
      }
    });
  }

  void _patBear() {
    _bearKey.currentState?.bounce();
    _setMood(BuddyMood.love, hold: const Duration(milliseconds: 1800));
    _floatHearts(4);
  }

  /// Reveals Cozy's reply a few words at a time so it feels spoken.
  Future<void> _speak(String text) {
    final done = Completer<void>();
    final words = text.split(RegExp(r'(?<=\s)'));
    if (MediaQuery.disableAnimationsOf(context) || words.length < 5) {
      done.complete();
      return done.future;
    }
    var shown = 0;
    setState(() => _speaking = '');
    _speakTimer = Timer.periodic(const Duration(milliseconds: 50), (timer) {
      if (!mounted) {
        timer.cancel();
        if (!done.isCompleted) done.complete();
        return;
      }
      shown = math.min(words.length, shown + 3);
      setState(() => _speaking = words.take(shown).join());
      _scrollDown();
      if (shown >= words.length) {
        timer.cancel();
        done.complete();
      }
    });
    return done.future;
  }

  Future<void> _send(String raw) async {
    final text = raw.trim();
    if (text.isEmpty || _busy) return;

    _input.clear();
    setState(() {
      _busy = true;
      _suggestions = const [];
      BuddyService.messages.add(BuddyMessage(fromUser: true, text: text));
    });
    _setMood(BuddyMood.thinking);
    _scrollDown();

    BuddyReply reply;
    var failed = false;
    try {
      reply = await BuddyService.send();
    } on ApiException catch (e) {
      failed = true;
      reply = BuddyReply(
        text: e.statusCode == 429
            ? "Whew, my paws need a little rest! Let's chat again in a minute."
            : "Oh no, I couldn't reach the shop just now. Can you try that again?",
        mood: 'sad',
        suggestions: e.statusCode == 429 ? const [] : [text],
        products: const [],
      );
    } catch (_) {
      failed = true;
      reply = BuddyReply(
        text: "Oh no, I couldn't reach the shop just now. Can you try that again?",
        mood: 'sad',
        suggestions: [text],
        products: const [],
      );
    }
    if (!mounted) return;

    // A failed send stays off the server history, so a retry doesn't repeat it.
    if (failed) BuddyService.messages.removeLast();

    final mood = BuddyMood.parse(reply.mood);
    _setMood(mood, hold: const Duration(seconds: 6));
    if (mood == BuddyMood.excited || mood == BuddyMood.love) _floatHearts();

    await _speak(reply.text);
    if (!mounted) return;

    setState(() {
      _speaking = null;
      if (failed) {
        _errorNote = reply.text;
      } else {
        _errorNote = null;
        BuddyService.messages.add(BuddyMessage(fromUser: false, text: reply.text, products: reply.products));
      }
      _suggestions = reply.suggestions;
      _busy = false;
    });
    _scrollDown();
  }

  /// A connection or rate-limit line from Cozy, shown but not part of the chat history.
  String? _errorNote;

  void _reset() {
    if (_busy) return;
    setState(() {
      BuddyService.reset();
      _errorNote = null;
      _suggestions = _starters;
    });
    _setMood(BuddyMood.wink, hold: const Duration(seconds: 2));
  }

  void _openProduct(BuddyProduct product) {
    Navigator.of(context).push(MaterialPageRoute(
      builder: (routeContext) => ProductDetailsScreen(
        productId: product.id,
        name: product.name,
        category: product.category,
        price: _peso(product.price),
        imageUrl: product.image.isEmpty ? null : product.image,
        sellerName: product.store,
        rating: product.rating,
        soldCount: product.sold.toString(),
        onBack: () => Navigator.of(routeContext).pop(),
        isGuest: !AuthService.isAuthenticated,
      ),
    ));
  }

  static String _peso(double? price) {
    if (price == null) return '';
    return '₱${price == price.roundToDouble() ? price.toStringAsFixed(0) : price.toStringAsFixed(2)}';
  }

  @override
  Widget build(BuildContext context) {
    final messages = BuddyService.messages;
    return Scaffold(
      backgroundColor: CartzyColors.brand50,
      body: Column(
        children: [
          _buildHeader(),
          Expanded(
            child: ListView(
              controller: _scroll,
              padding: const EdgeInsets.fromLTRB(14, 16, 14, 12),
              children: [
                _Bubble(fromUser: false, text: _welcome),
                for (final m in messages) ...[
                  _Bubble(fromUser: m.fromUser, text: m.text),
                  if (m.products.isNotEmpty)
                    _ProductRow(products: m.products, onOpen: _openProduct, peso: _peso),
                ],
                if (_errorNote != null && _speaking == null) _Bubble(fromUser: false, text: _errorNote!),
                if (_speaking != null) _Bubble(fromUser: false, text: _speaking!),
                if (_busy && _speaking == null) const _TypingBubble(),
              ],
            ),
          ),
          if (_suggestions.isNotEmpty && !_busy) _buildSuggestions(),
          _buildComposer(),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [CartzyColors.brandDeep, CartzyColors.brand500, CartzyColors.rose500],
          stops: [0, .55, 1],
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: SizedBox(
          height: 112,
          child: Stack(
            children: [
              Positioned.fill(
                child: Row(
                  children: [
                    IconButton(
                      tooltip: 'Back',
                      icon: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                      onPressed: () => Navigator.of(context).maybePop(),
                    ),
                    BuddyBear(key: _bearKey, size: 92, mood: _mood, onTap: _patBear),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Cozy',
                            style: GoogleFonts.cormorantGaramond(
                              color: Colors.white,
                              fontSize: 30,
                              height: 1,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _busy ? 'Cozy is thinking...' : 'Your Cartzy shopping buddy',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(color: Colors.white, fontSize: 13),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      tooltip: 'Start a new chat',
                      icon: const Icon(Icons.refresh_rounded, color: Colors.white),
                      onPressed: _reset,
                    ),
                  ],
                ),
              ),
              ..._hearts,
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSuggestions() {
    return SizedBox(
      height: 46,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.fromLTRB(12, 0, 12, 8),
        itemCount: _suggestions.length,
        separatorBuilder: (_, __) => const SizedBox(width: 7),
        itemBuilder: (_, i) => ActionChip(
          label: Text(_suggestions[i]),
          labelStyle: const TextStyle(
            color: CartzyColors.rose700,
            fontWeight: FontWeight.w700,
            fontSize: 13,
          ),
          backgroundColor: CartzyColors.rose50Warm,
          side: const BorderSide(color: CartzyColors.rose400, width: 1.5),
          shape: const StadiumBorder(),
          onPressed: () => _send(_suggestions[i]),
        ),
      ),
    );
  }

  Widget _buildComposer() {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: CartzyColors.brand200)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Expanded(
                child: TextField(
                  controller: _input,
                  minLines: 1,
                  maxLines: 4,
                  maxLength: 2000,
                  textCapitalization: TextCapitalization.sentences,
                  textInputAction: TextInputAction.send,
                  onSubmitted: _send,
                  decoration: InputDecoration(
                    counterText: '',
                    hintText: 'Say hi to Cozy...',
                    isDense: true,
                    filled: true,
                    fillColor: CartzyColors.brand50,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(22),
                      borderSide: const BorderSide(color: CartzyColors.brand200, width: 1.5),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(22),
                      borderSide: const BorderSide(color: CartzyColors.brand200, width: 1.5),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(22),
                      borderSide: const BorderSide(color: Color(0xFFB76E79), width: 1.5),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Semantics(
                button: true,
                label: 'Send message',
                excludeSemantics: true,
                child: InkWell(
                  customBorder: const CircleBorder(),
                  onTap: _busy ? null : () => _send(_input.text),
                  child: Ink(
                    width: 46,
                    height: 46,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: _busy
                          ? null
                          : const LinearGradient(colors: [CartzyColors.rose500, Color(0xFFB76E79)]),
                      color: _busy ? const Color(0xFFC9C3D3) : null,
                    ),
                    child: const Icon(Icons.send_rounded, color: Colors.white, size: 21),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Bubble extends StatelessWidget {
  final bool fromUser;
  final String text;

  const _Bubble({required this.fromUser, required this.text});

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: fromUser ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        constraints: BoxConstraints(maxWidth: MediaQuery.sizeOf(context).width * .8),
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        decoration: BoxDecoration(
          color: fromUser ? null : Colors.white,
          gradient: fromUser
              ? const LinearGradient(colors: [CartzyColors.brandDeep, Color(0xFF8C7892)])
              : null,
          border: fromUser ? null : Border.all(color: CartzyColors.brand200),
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(18),
            topRight: const Radius.circular(18),
            bottomLeft: Radius.circular(fromUser ? 18 : 5),
            bottomRight: Radius.circular(fromUser ? 5 : 18),
          ),
        ),
        child: Text(
          text,
          style: TextStyle(
            color: fromUser ? Colors.white : const Color(0xFF3E354C),
            fontSize: 14.5,
            height: 1.4,
          ),
        ),
      ),
    );
  }
}

class _TypingBubble extends StatefulWidget {
  const _TypingBubble();

  @override
  State<_TypingBubble> createState() => _TypingBubbleState();
}

class _TypingBubbleState extends State<_TypingBubble> with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1100),
  )..repeat();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: Alignment.centerLeft,
      child: Semantics(
        label: 'Cozy is typing',
        child: Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          decoration: BoxDecoration(
            color: Colors.white,
            border: Border.all(color: CartzyColors.brand200),
            borderRadius: const BorderRadius.only(
              topLeft: Radius.circular(18),
              topRight: Radius.circular(18),
              bottomRight: Radius.circular(18),
              bottomLeft: Radius.circular(5),
            ),
          ),
          child: AnimatedBuilder(
            animation: _controller,
            builder: (_, __) => Row(
              mainAxisSize: MainAxisSize.min,
              children: List.generate(3, (i) {
                final phase = (_controller.value - i * .14) % 1;
                final lift = phase < .4 ? math.sin(phase / .4 * math.pi) : 0.0;
                return Container(
                  width: 8,
                  height: 8,
                  margin: EdgeInsets.only(right: i < 2 ? 5 : 0),
                  transform: Matrix4.translationValues(0, -4 * lift, 0),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: i == 1 ? CartzyColors.brand400 : CartzyColors.rose400,
                  ),
                );
              }),
            ),
          ),
        ),
      ),
    );
  }
}

class _ProductRow extends StatelessWidget {
  final List<BuddyProduct> products;
  final ValueChanged<BuddyProduct> onOpen;
  final String Function(double?) peso;

  const _ProductRow({required this.products, required this.onOpen, required this.peso});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: SizedBox(
        height: 196,
        child: ListView.separated(
          scrollDirection: Axis.horizontal,
          itemCount: products.length,
          separatorBuilder: (_, __) => const SizedBox(width: 9),
          itemBuilder: (_, i) {
            final product = products[i];
            final meta = !product.inStock
                ? 'Out of stock'
                : (product.rating != null ? '★ ${product.rating} · ${product.sold} sold' : '${product.sold} sold');
            return Material(
              color: Colors.white,
              shape: RoundedRectangleBorder(
                side: const BorderSide(color: CartzyColors.rose200, width: 1.5),
                borderRadius: BorderRadius.circular(16),
              ),
              clipBehavior: Clip.antiAlias,
              child: InkWell(
                onTap: () => onOpen(product),
                child: SizedBox(
                  width: 138,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(
                        width: 138,
                        height: 104,
                        child: product.image.isEmpty
                            ? const ColoredBox(color: CartzyColors.brand100)
                            : Image.network(
                                product.image,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => const ColoredBox(color: CartzyColors.brand100),
                              ),
                      ),
                      Padding(
                        padding: const EdgeInsets.fromLTRB(10, 7, 10, 0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              product.name,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(color: Color(0xFF3E354C), fontSize: 12.5, height: 1.2),
                            ),
                            const SizedBox(height: 3),
                            Text(
                              peso(product.price),
                              style: const TextStyle(
                                color: CartzyColors.rose700,
                                fontSize: 14,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                            Text(
                              meta,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(color: Color(0xFF75687D), fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}

/// A little heart that rises from Cozy and fades away.
class _FloatingHeart extends StatefulWidget {
  final double left;
  final Duration delay;
  final Color color;
  final void Function(_FloatingHeart) onDone;

  const _FloatingHeart({
    super.key,
    required this.left,
    required this.delay,
    required this.color,
    required this.onDone,
  });

  @override
  State<_FloatingHeart> createState() => _FloatingHeartState();
}

class _FloatingHeartState extends State<_FloatingHeart> with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1100),
  );
  Timer? _start;

  @override
  void initState() {
    super.initState();
    _start = Timer(widget.delay, () {
      if (mounted) _controller.forward().whenComplete(() => widget.onDone(widget));
    });
  }

  @override
  void dispose() {
    _start?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (_, __) {
        final v = _controller.value;
        return Positioned(
          left: 48 + widget.left,
          top: 56 - 56 * v,
          child: IgnorePointer(
            child: Opacity(
              opacity: v == 0 ? 0 : (1 - v).clamp(0.0, 1.0),
              child: Icon(Icons.favorite_rounded, size: 12 + 8 * v, color: widget.color),
            ),
          ),
        );
      },
    );
  }
}

/// The bear that floats over the home screens and opens the chat.
class BuddyLauncher extends StatefulWidget {
  /// Whether the bottom nav bar is showing, so the bear sits above it.
  final bool aboveNav;

  const BuddyLauncher({super.key, required this.aboveNav});

  @override
  State<BuddyLauncher> createState() => _BuddyLauncherState();
}

class _BuddyLauncherState extends State<BuddyLauncher> {
  final _bearKey = GlobalKey<BuddyBearState>();

  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(milliseconds: 900), () {
      if (mounted) _bearKey.currentState?.wave();
    });
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedPositioned(
      duration: const Duration(milliseconds: 260),
      curve: Curves.easeInOutCubic,
      right: 10,
      bottom: widget.aboveNav ? 82 : 14,
      child: DecoratedBox(
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          boxShadow: [
            BoxShadow(
              color: CartzyColors.brandDark.withValues(alpha: .28),
              blurRadius: 18,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: BuddyBear(
          key: _bearKey,
          size: 78,
          onTap: () => Navigator.of(context).push(
            MaterialPageRoute(builder: (_) => const BuddyChatScreen()),
          ),
        ),
      ),
    );
  }
}

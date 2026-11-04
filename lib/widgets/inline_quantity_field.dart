import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

/// The number between the − and + buttons, typed into directly.
///
/// Tapping it opens the number keyboard right there (no pop-up). The typed
/// value is applied when the buyer presses Done, taps elsewhere, or the field
/// loses focus. An empty or zero entry goes back to the current quantity.
/// [onCommitted] gets the typed number; the caller clamps it to stock.
class InlineQuantityField extends StatefulWidget {
  final int quantity;
  final ValueChanged<int> onCommitted;
  final TextStyle style;
  final double minWidth;
  final BoxDecoration? decoration;
  final EdgeInsetsGeometry padding;
  final bool enabled;

  const InlineQuantityField({
    super.key,
    required this.quantity,
    required this.onCommitted,
    required this.style,
    this.minWidth = 32,
    this.decoration,
    this.padding = const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
    this.enabled = true,
  });

  @override
  State<InlineQuantityField> createState() => _InlineQuantityFieldState();
}

class _InlineQuantityFieldState extends State<InlineQuantityField> {
  late final TextEditingController _controller =
      TextEditingController(text: '${widget.quantity}');
  final FocusNode _focus = FocusNode();

  @override
  void initState() {
    super.initState();
    _focus.addListener(_onFocusChange);
  }

  @override
  void didUpdateWidget(InlineQuantityField oldWidget) {
    super.didUpdateWidget(oldWidget);
    // Follow −/+ taps and cart updates, but never overwrite what is being typed.
    if (!_focus.hasFocus && _controller.text != '${widget.quantity}') {
      _controller.text = '${widget.quantity}';
    }
  }

  void _onFocusChange() {
    if (_focus.hasFocus) {
      // Select the number so typing replaces it.
      _controller.selection =
          TextSelection(baseOffset: 0, extentOffset: _controller.text.length);
    } else {
      _commit();
    }
    setState(() {});
  }

  void _commit() {
    final typed = int.tryParse(_controller.text.trim());
    if (typed != null && typed > 0 && typed != widget.quantity) {
      widget.onCommitted(typed);
    }
    // Show the quantity actually applied (e.g. limited to stock).
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted && !_focus.hasFocus) {
        _controller.text = '${widget.quantity}';
      }
    });
  }

  @override
  void dispose() {
    _focus.removeListener(_onFocusChange);
    _focus.dispose();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final focused = _focus.hasFocus;
    final digits = _controller.text.length.clamp(1, 5);
    final base = widget.decoration ?? const BoxDecoration();
    final effectiveMinWidth = widget.minWidth;
    final calculatedWidth = (widget.style.fontSize ?? 14) * 0.75 * digits + 14;
    final width = calculatedWidth > effectiveMinWidth ? calculatedWidth : effectiveMinWidth;

    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: () {
        if (!widget.enabled) return;
        if (!_focus.hasFocus) {
          _focus.requestFocus();
        }
        _controller.selection = TextSelection(
          baseOffset: 0,
          extentOffset: _controller.text.length,
        );
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: widget.padding,
        decoration: focused
            ? base.copyWith(
                color: Colors.white,
                borderRadius: base.borderRadius ?? BorderRadius.circular(6),
                border: Border.all(
                  color: widget.style.color ?? Colors.black,
                  width: 1.5,
                ),
              )
            : base,
        child: SizedBox(
          width: width,
          child: TextField(
            controller: _controller,
            focusNode: _focus,
            enabled: widget.enabled,
            keyboardType: TextInputType.number,
            textInputAction: TextInputAction.done,
            textAlign: TextAlign.center,
            style: widget.style,
            cursorColor: widget.style.color,
            inputFormatters: [
              FilteringTextInputFormatter.digitsOnly,
              LengthLimitingTextInputFormatter(5),
            ],
            decoration: const InputDecoration(
              isDense: true,
              isCollapsed: true,
              border: InputBorder.none,
              contentPadding: EdgeInsets.zero,
            ),
            onTap: () {
              // Select all on tap so typing immediately replaces the previous value
              _controller.selection = TextSelection(
                baseOffset: 0,
                extentOffset: _controller.text.length,
              );
            },
            onChanged: (_) => setState(() {}),
            onSubmitted: (_) {
              _commit();
              _focus.unfocus();
            },
            onTapOutside: (_) {
              _commit();
              _focus.unfocus();
            },
          ),
        ),
      ),
    );
  }
}

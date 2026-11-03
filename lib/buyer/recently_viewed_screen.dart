import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/products/product_details_screen.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/services/recently_viewed_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/product_card.dart';

class _Palette {
  static const lavender = CartzyColors.brandDeep;
  static const roseGold = Color(0xFFB76E79);
  static const roseGoldDeep = Color(0xFF9C4A5A);
  static const ink = CartzyColors.brand900;
  static const muted = CartzyColors.grayMid;
  static const line = Color(0xFFEDE6EE);

  static const blend = LinearGradient(
    colors: [lavender, roseGold],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const softBlend = LinearGradient(
    colors: [CartzyColors.brand100, CartzyColors.rose50Warm],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
}

/// Products the buyer opened recently, newest first.
class RecentlyViewedScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onViewCart;

  const RecentlyViewedScreen({super.key, required this.onBack, this.onViewCart});

  @override
  State<RecentlyViewedScreen> createState() => _RecentlyViewedScreenState();
}

class _RecentlyViewedScreenState extends State<RecentlyViewedScreen> {
  @override
  void initState() {
    super.initState();
    RecentlyViewedService.load();
  }

  void _open(RecentlyViewedProduct p) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => ProductDetailsScreen(
          productId: p.productId,
          name: p.name,
          category: p.category,
          price: p.price,
          originalPrice: p.originalPrice,
          imageUrl: p.imageUrl,
          sellerName: p.sellerName,
          badge: p.badge,
          rating: p.rating,
          soldCount: p.soldCount,
          onBack: () => Navigator.of(context).pop(),
        ),
      ),
    );
  }

  void _addToCart(RecentlyViewedProduct p) {
    final result = CartService.instance.addItem(
      name: p.name,
      price: p.price,
      originalPrice: p.originalPrice,
      category: p.category,
      image: p.imageUrl ?? '',
      productId: p.productId,
    );
    _snack(
      result.message(p.name),
      actionLabel: widget.onViewCart == null ? null : 'VIEW CART',
      onAction: widget.onViewCart,
    );
  }

  void _remove(RecentlyViewedProduct p) {
    final before = RecentlyViewedService.items.value;
    RecentlyViewedService.remove(p);
    _snack(
      'Removed from history',
      actionLabel: 'UNDO',
      onAction: () => RecentlyViewedService.restore(before),
    );
  }

  Future<void> _confirmClear() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
        title: Text(
          'Clear history?',
          style: GoogleFonts.playfairDisplay(
              fontWeight: FontWeight.w700, color: _Palette.ink),
        ),
        content: Text(
          'Your recently viewed products will be removed from this device.',
          style: GoogleFonts.dmSans(fontSize: 13.5, color: _Palette.muted),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: Text('Cancel',
                style: GoogleFonts.dmSans(
                    fontWeight: FontWeight.w600, color: _Palette.muted)),
          ),
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(true),
            child: Text('Clear',
                style: GoogleFonts.dmSans(
                    fontWeight: FontWeight.w700,
                    color: _Palette.roseGoldDeep)),
          ),
        ],
      ),
    );
    if (ok == true) RecentlyViewedService.clear();
  }

  void _snack(String message, {String? actionLabel, VoidCallback? onAction}) {
    final messenger = ScaffoldMessenger.of(context);
    messenger.hideCurrentSnackBar();
    messenger.showSnackBar(
      SnackBar(
        behavior: SnackBarBehavior.floating,
        backgroundColor: _Palette.ink,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        duration: const Duration(seconds: 3),
        content: Text(message,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: GoogleFonts.dmSans(color: Colors.white, fontSize: 13)),
        action: actionLabel == null
            ? null
            : SnackBarAction(
                label: actionLabel,
                textColor: CartzyColors.rose200,
                onPressed: onAction ?? () {},
              ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.brand50Lavender,
      body: SafeArea(
        child: ValueListenableBuilder<List<RecentlyViewedProduct>>(
          valueListenable: RecentlyViewedService.items,
          builder: (context, items, _) {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeader(items.length),
                Expanded(
                  child: AnimatedSwitcher(
                    duration: const Duration(milliseconds: 300),
                    child: items.isEmpty ? _buildEmpty() : _buildGrid(items),
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildHeader(int count) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Material(
                color: Colors.white,
                shape: const CircleBorder(
                    side: BorderSide(color: _Palette.line)),
                child: InkWell(
                  customBorder: const CircleBorder(),
                  onTap: widget.onBack,
                  child: const SizedBox(
                    width: 42,
                    height: 42,
                    child: Icon(Icons.arrow_back_ios_new_rounded,
                        size: 16, color: _Palette.ink),
                  ),
                ),
              ),
              const Spacer(),
              if (count > 0)
                TextButton.icon(
                  onPressed: _confirmClear,
                  icon: const Icon(Icons.delete_sweep_outlined,
                      size: 18, color: _Palette.roseGoldDeep),
                  label: Text('Clear',
                      style: GoogleFonts.dmSans(
                          fontWeight: FontWeight.w700,
                          color: _Palette.roseGoldDeep)),
                ),
            ],
          ),
          const SizedBox(height: 14),
          Text(
            'Recently Viewed',
            style: GoogleFonts.playfairDisplay(
              fontSize: 28,
              fontWeight: FontWeight.w700,
              color: _Palette.ink,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            count == 0
                ? 'Products you open will show up here.'
                : '$count product${count == 1 ? '' : 's'} · long-press to remove',
            style: GoogleFonts.dmSans(fontSize: 13, color: _Palette.muted),
          ),
        ],
      ),
    );
  }

  Widget _buildGrid(List<RecentlyViewedProduct> items) {
    return LayoutBuilder(
      key: const ValueKey('grid'),
      builder: (context, constraints) {
        final cardWidth = (constraints.maxWidth - 32 - 10) / 2;
        final cardHeight = cardWidth + ProductCard.bodyHeight(context);
        return GridView.builder(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            mainAxisSpacing: 10,
            crossAxisSpacing: 10,
            mainAxisExtent: cardHeight,
          ),
          itemCount: items.length,
          itemBuilder: (context, index) {
            final p = items[index];
            return GestureDetector(
              onLongPress: () => _remove(p),
              child: ProductCard(
                key: ValueKey('rv-${p.productId ?? p.name}'),
                productId: p.productId,
                name: p.name,
                price: p.price,
                originalPrice: p.originalPrice,
                badge: p.badge,
                rating: p.rating,
                soldCount: p.soldCount,
                imageUrl: p.imageUrl,
                sellerName: p.sellerName,
                onTap: () => _open(p),
                onAddToCart: () => _addToCart(p),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildEmpty() {
    return Center(
      key: const ValueKey('empty'),
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: const BoxDecoration(
                shape: BoxShape.circle,
                gradient: _Palette.softBlend,
              ),
              child: Center(
                child: ShaderMask(
                  shaderCallback: (r) => _Palette.blend.createShader(r),
                  child: const Icon(Icons.history_rounded,
                      size: 46, color: Colors.white),
                ),
              ),
            ),
            const SizedBox(height: 20),
            Text(
              'Nothing here yet',
              style: GoogleFonts.playfairDisplay(
                fontSize: 21,
                fontWeight: FontWeight.w700,
                color: _Palette.ink,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'Browse around — products you open will appear here so you can find them again.',
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(
                  fontSize: 13.5, height: 1.4, color: _Palette.muted),
            ),
            const SizedBox(height: 22),
            DecoratedBox(
              decoration: BoxDecoration(
                gradient: _Palette.blend,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Material(
                type: MaterialType.transparency,
                child: InkWell(
                  borderRadius: BorderRadius.circular(16),
                  onTap: widget.onBack,
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 28, vertical: 14),
                    child: Text(
                      'Start Shopping',
                      style: GoogleFonts.dmSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

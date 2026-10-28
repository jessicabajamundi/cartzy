import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/wishlist_empty_state.dart';

// ============================================================
// WISHLIST SCREEN  — "Saved for later" design
// ============================================================

class WishlistScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onDiscoverProducts;

  const WishlistScreen({
    super.key,
    required this.onBack,
    this.onDiscoverProducts,
  });

  @override
  State<WishlistScreen> createState() => _WishlistScreenState();
}

class _WishlistScreenState extends State<WishlistScreen> {
  // In a real app, load from a service/state
  final List<Map<String, dynamic>> _items = [];
  final bool _isLoading = false;

  // ── Empty State ─────────────────────────────────────────────
  Widget _emptyState() => WishlistEmptyState(
        onPrimary: widget.onDiscoverProducts ?? widget.onBack,
      );

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        leading: BackButton(onPressed: widget.onBack),
        centerTitle: true,
        toolbarHeight: 76,
        backgroundColor: CartzyColors.brand50Lavender,
        elevation: 0,
        scrolledUnderElevation: 0,
        foregroundColor: CartzyColors.brandDark,
        surfaceTintColor: Colors.transparent,
        title: Column(
          children: [
            Text('YOUR FAVORITE FINDS',
                style: GoogleFonts.dmSans(
                  fontSize: 10,
                  letterSpacing: 1.6,
                  fontWeight: FontWeight.w700,
                  color: CartzyColors.rose700,
                )),
            const SizedBox(height: 4),
            Text('Your wishlist',
                style: GoogleFonts.playfairDisplay(
                  fontSize: 26,
                  fontWeight: FontWeight.w700,
                  color: CartzyColors.brand900,
                )),
          ],
        ),
      ),
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Header ───────────────────────────────────────
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _items.isEmpty
                      ? _emptyState()
                      : GridView.builder(
                          padding: const EdgeInsets.all(20),
                          gridDelegate:
                              const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                            childAspectRatio: 0.72,
                          ),
                          itemCount: _items.length,
                          itemBuilder: (context, index) {
                            return _wishlistCard(_items[index]);
                          },
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _wishlistCard(Map<String, dynamic> item) {
    return Container(
      decoration: BoxDecoration(
        color: CartzyColors.brand50,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: CartzyColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Container(
              decoration: const BoxDecoration(
                color: CartzyColors.background,
                borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
              ),
              child: const Center(
                  child: Icon(Icons.image_outlined,
                      color: CartzyColors.gray, size: 40)),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item['name'] ?? '',
                  style: GoogleFonts.dmSans(
                      fontWeight: FontWeight.w600, fontSize: 13),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Text(
                  '₱${item['price'] ?? ''}',
                  style: GoogleFonts.dmSans(
                    fontWeight: FontWeight.w700,
                    fontSize: 13,
                    color: CartzyColors.navy,
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

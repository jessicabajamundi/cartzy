import 'dart:async';
import 'package:flutter/material.dart';

import 'cartzy_colors.dart';

// ============================================================
// HERO BANNER
// ============================================================

class HeroBanner extends StatelessWidget {
  final VoidCallback onShopNow;

  const HeroBanner({super.key, required this.onShopNow});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: CartzyColors.navy,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Stack(
        children: [
          Positioned(
            right: -10,
            bottom: -10,
            child: Icon(
              Icons.shopping_bag_outlined,
              size: 120,
              color: Colors.white.withValues(alpha: 0.06),
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.add, size: 12, color: Colors.black),
                    SizedBox(width: 4),
                    Text(
                      'NEW SEASON COLLECTION',
                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.black),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Premium Quality Essentials & Exclusive Deals',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                  height: 1.25,
                ),
              ),
              const SizedBox(height: 10),
              Text(
                'Enjoy up to 60% off select brands, complimentary shipping vouchers, and verified authentic products.',
                style: TextStyle(fontSize: 13, color: Colors.white.withValues(alpha: 0.75), height: 1.4),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: onShopNow,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: Colors.black,
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text('Shop the Collection', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                    SizedBox(width: 6),
                    Icon(Icons.arrow_forward, size: 16),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

// ============================================================
// PROMO CARD
// ============================================================

class PromoCard extends StatelessWidget {
  final String tag;
  final String title;
  final String description;
  final String linkText;
  final bool dark;

  const PromoCard({
    super.key,
    required this.tag,
    required this.title,
    required this.description,
    required this.linkText,
    this.dark = false,
  });

  @override
  Widget build(BuildContext context) {
    final bg = dark ? CartzyColors.navy : CartzyColors.surface;
    final textColor = dark ? Colors.white : CartzyColors.navy;
    final subColor = dark ? Colors.white.withValues(alpha: 0.7) : CartzyColors.gray;
    final tagBg = dark ? Colors.white.withValues(alpha: 0.12) : CartzyColors.background;
    final tagText = dark ? Colors.white : CartzyColors.navy;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(16),
        boxShadow: dark
            ? null
            : [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 3))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(color: tagBg, borderRadius: BorderRadius.circular(6)),
            child: Text(
              tag,
              style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: tagText, letterSpacing: 0.5),
            ),
          ),
          const SizedBox(height: 10),
          Text(title, style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: textColor)),
          const SizedBox(height: 4),
          Text(description, style: TextStyle(fontSize: 12, color: subColor)),
          const SizedBox(height: 8),
          Text(
            linkText,
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: dark ? Colors.white : CartzyColors.coral),
          ),
        ],
      ),
    );
  }
}

// ============================================================
// CATEGORY ICON ROW
// ============================================================

class CategoryIconItem {
  final String label;
  final IconData icon;
  const CategoryIconItem({required this.label, required this.icon});
}

const List<CategoryIconItem> categoryIcons = [
  CategoryIconItem(label: 'Pet Supplies', icon: Icons.pets_outlined),
  CategoryIconItem(label: 'Kids & Baby', icon: Icons.child_care_outlined),
  CategoryIconItem(label: 'Electronics', icon: Icons.devices_outlined),
  CategoryIconItem(label: 'Home & Garden', icon: Icons.yard_outlined),
  CategoryIconItem(label: "Women's", icon: Icons.checkroom_outlined),
  CategoryIconItem(label: 'Sports', icon: Icons.sports_soccer_outlined),
  CategoryIconItem(label: "Men's", icon: Icons.man_outlined),
  CategoryIconItem(label: 'Health & Beauty', icon: Icons.spa_outlined),
  CategoryIconItem(label: 'Books & Media', icon: Icons.menu_book_outlined),
  CategoryIconItem(label: 'Food & Gourmet', icon: Icons.restaurant_outlined),
  CategoryIconItem(label: 'Jewelry & Watches', icon: Icons.watch_outlined),
];

class CategoryIconRow extends StatelessWidget {
  final String selectedCategory;
  final ValueChanged<String> onSelected;

  const CategoryIconRow({
    super.key,
    required this.selectedCategory,
    required this.onSelected,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 84,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 20),
        itemCount: categoryIcons.length,
        separatorBuilder: (_, __) => const SizedBox(width: 14),
        itemBuilder: (context, index) {
          final item = categoryIcons[index];
          final selected = item.label == selectedCategory;
          return GestureDetector(
            onTap: () => onSelected(item.label),
            child: SizedBox(
              width: 68,
              child: Column(
                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      color: selected ? CartzyColors.coral : CartzyColors.surface,
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, 2)),
                      ],
                    ),
                    child: Icon(
                      item.icon,
                      color: selected ? Colors.white : CartzyColors.coral,
                      size: 24,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    item.label,
                    textAlign: TextAlign.center,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 10, color: CartzyColors.navy, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}

// ============================================================
// FLASH DEALS
// ============================================================

class FlashDeal {
  final String name;
  final String price;
  final int discountPercent;

  const FlashDeal({required this.name, required this.price, required this.discountPercent});
}

const List<FlashDeal> flashDeals = [
  FlashDeal(name: 'Smart Watch', price: '\$59.99', discountPercent: 65),
  FlashDeal(name: 'Wireless Headphones', price: '\$34.99', discountPercent: 50),
  FlashDeal(name: 'Instant Camera', price: '\$79.99', discountPercent: 40),
  FlashDeal(name: 'Sunglasses', price: '\$14.99', discountPercent: 70),
  FlashDeal(name: 'Vitamin Pack', price: '\$9.99', discountPercent: 55),
  FlashDeal(name: 'Running Shoes', price: '\$44.99', discountPercent: 45),
];

class FlashDealsSection extends StatefulWidget {
  final VoidCallback? onViewAll;

  const FlashDealsSection({super.key, this.onViewAll});

  @override
  State<FlashDealsSection> createState() => _FlashDealsSectionState();
}

class _FlashDealsSectionState extends State<FlashDealsSection> {
  Duration _remaining = const Duration(hours: 2, minutes: 45, seconds: 18);
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      setState(() {
        if (_remaining.inSeconds > 0) {
          _remaining -= const Duration(seconds: 1);
        } else {
          _remaining = const Duration(hours: 2, minutes: 45, seconds: 18);
        }
      });
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  String _two(int n) => n.toString().padLeft(2, '0');

  @override
  Widget build(BuildContext context) {
    final h = _two(_remaining.inHours);
    final m = _two(_remaining.inMinutes.remainder(60));
    final s = _two(_remaining.inSeconds.remainder(60));

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Row(
            children: [
              const Icon(Icons.bolt, color: CartzyColors.coral, size: 20),
              const SizedBox(width: 6),
              const Text(
                'FLASH DEALS',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: CartzyColors.navy, letterSpacing: 0.5),
              ),
              const SizedBox(width: 10),
              _timeBlock(h),
              _colon(),
              _timeBlock(m),
              _colon(),
              _timeBlock(s),
              const Spacer(),
              GestureDetector(
                onTap: widget.onViewAll,
                child: const Text(
                  'View All >',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: CartzyColors.coral),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),
        SizedBox(
          height: 190,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            itemCount: flashDeals.length,
            separatorBuilder: (_, __) => const SizedBox(width: 12),
            itemBuilder: (context, index) => _FlashDealCard(deal: flashDeals[index]),
          ),
        ),
      ],
    );
  }

  Widget _timeBlock(String value) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
      decoration: BoxDecoration(
        color: CartzyColors.navy,
        borderRadius: BorderRadius.circular(5),
      ),
      child: Text(value, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white)),
    );
  }

  Widget _colon() => const Padding(
        padding: EdgeInsets.symmetric(horizontal: 2),
        child: Text(':', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
      );
}

class _FlashDealCard extends StatelessWidget {
  final FlashDeal deal;

  const _FlashDealCard({required this.deal});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 130,
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 3))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Stack(
              children: [
                Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: CartzyColors.background,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Center(
                    child: Icon(Icons.image_outlined, color: CartzyColors.coral, size: 32),
                  ),
                ),
                Positioned(
                  top: 4,
                  right: 4,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                    decoration: BoxDecoration(
                      color: CartzyColors.navy,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      '-${deal.discountPercent}%',
                      style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.white),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            deal.name,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: CartzyColors.navy),
          ),
          const SizedBox(height: 2),
          Text(
            deal.price,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: CartzyColors.coral),
          ),
        ],
      ),
    );
  }
}

// ============================================================
// DAILY DISCOVER PRODUCT CARD
// ============================================================

class DiscoverProduct {
  final String name;
  final String price;
  final int discountPercent;
  final String badge;
  final double rating;
  final String soldCount;

  const DiscoverProduct({
    required this.name,
    required this.price,
    required this.discountPercent,
    required this.badge,
    required this.rating,
    required this.soldCount,
  });
}

const List<DiscoverProduct> discoverProducts = [
  DiscoverProduct(name: 'Smart Fitness Tracker Watch with Blood Oxygen & Heart Rate', price: '₱1,299', discountPercent: 35, badge: 'Official', rating: 4.9, soldCount: '3.4k sold'),
  DiscoverProduct(name: 'Minimalist Matte Chronograph Watch Waterproof', price: '₱459', discountPercent: 40, badge: 'Preferred', rating: 4.8, soldCount: '1.8k sold'),
  DiscoverProduct(name: 'ANC Pro Wireless Noise Cancelling Earphones', price: '₱890', discountPercent: 52, badge: 'Official', rating: 5.0, soldCount: '8.9k sold'),
  DiscoverProduct(name: 'Retro Colorblock Sneaker Lightweight Running', price: '₱650', discountPercent: 20, badge: 'Preferred', rating: 4.7, soldCount: '920 sold'),
  DiscoverProduct(name: 'Ceramic Aesthetic Coffee Mug & Saucer Set', price: '₱280', discountPercent: 30, badge: 'Official', rating: 4.9, soldCount: '540 sold'),
  DiscoverProduct(name: 'Instant Print Camera Retro Vintage Pocket Edition', price: '₱3,499', discountPercent: 30, badge: 'Preferred', rating: 4.8, soldCount: '2.1k sold'),
];

class DiscoverProductCard extends StatelessWidget {
  final DiscoverProduct product;
  final VoidCallback onTap;

  const DiscoverProductCard({super.key, required this.product, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final isOfficial = product.badge == 'Official';

    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: CartzyColors.surface,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 3))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Stack(
                children: [
                  Container(
                    width: double.infinity,
                    decoration: const BoxDecoration(
                      color: CartzyColors.background,
                      borderRadius: BorderRadius.vertical(top: Radius.circular(14)),
                    ),
                    child: const Center(
                      child: Icon(Icons.image_outlined, color: CartzyColors.coral, size: 32),
                    ),
                  ),
                  Positioned(
                    top: 8,
                    left: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                      decoration: BoxDecoration(
                        color: isOfficial ? CartzyColors.navy : CartzyColors.gold,
                        borderRadius: BorderRadius.circular(5),
                      ),
                      child: Text(
                        product.badge,
                        style: TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                          color: isOfficial ? Colors.white : CartzyColors.navy,
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    top: 8,
                    right: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                      decoration: BoxDecoration(color: CartzyColors.coral, borderRadius: BorderRadius.circular(5)),
                      child: Text(
                        '-${product.discountPercent}%',
                        style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.white),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 12, color: CartzyColors.text, height: 1.3),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    product.price,
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: CartzyColors.navy),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.star, size: 12, color: Colors.amber),
                      const SizedBox(width: 3),
                      Text(product.rating.toString(), style: const TextStyle(fontSize: 11, color: CartzyColors.gray)),
                      const Spacer(),
                      Text(product.soldCount, style: const TextStyle(fontSize: 11, color: CartzyColors.gray)),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ============================================================
// SECTION TITLE
// ============================================================

class SectionTitle extends StatelessWidget {
  final String title;
  final String? actionText;
  final VoidCallback? onAction;

  const SectionTitle({super.key, required this.title, this.actionText, this.onAction});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Row(
        children: [
          Text(title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
          const Spacer(),
          if (actionText != null)
            GestureDetector(
              onTap: onAction,
              child: Text(
                actionText!,
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: CartzyColors.coral),
              ),
            ),
        ],
      ),
    );
  }
}
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/product_card.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';
import 'package:cartzy/products/product_list_screen.dart';

import 'package:cartzy/services/catalog_service.dart';
import 'package:cartzy/services/api_service.dart';

// ============================================================
// HERO BANNER CAROUSEL (synced with website database & hero_slides table)
// ============================================================

class HeroBanner extends StatefulWidget {
  final VoidCallback? onShopNow;

  /// Space for the search and action icons overlaid on the banner.
  final double contentTopInset;

  const HeroBanner({
    super.key,
    this.onShopNow,
    this.contentTopInset = 0,
  }) : assert(contentTopInset >= 0);

  @override
  State<HeroBanner> createState() => HeroBannerState();
}

class HeroBannerState extends State<HeroBanner> {
  static const int _kInitialPage = 9000;
  late final PageController _pageController;
  int _current = _kInitialPage;
  Timer? _timer;
  List<HeroSlideItem> _slides = List<HeroSlideItem>.from(defaultHeroSlides);

  int _getSlideIndex(int pageIndex) {
    if (_slides.isEmpty) return 0;
    return (pageIndex % _slides.length + _slides.length) % _slides.length;
  }

  @override
  void initState() {
    super.initState();
    _pageController = PageController(initialPage: _kInitialPage);
    _loadHeroSlides();
    _startTimer();
  }

  @override
  void reassemble() {
    super.reassemble();
    _startTimer();
  }

  void _startTimer() {
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 7), (_) {
      if (mounted && _pageController.hasClients && _slides.isNotEmpty) {
        final currentPage = _pageController.page?.round() ?? _current;
        _pageController.animateToPage(
          currentPage + 1,
          duration: const Duration(milliseconds: 1100),
          curve: Curves.easeInOutCubic,
        );
      }
    });
  }

  Future<void> reload() async {
    await _loadHeroSlides();
  }

  Future<void> _loadHeroSlides() async {
    try {
      final fetched = await CatalogService.fetchHeroSlides();
      if (mounted && fetched.isNotEmpty) {
        setState(() {
          _slides = fetched;
        });
      }
    } catch (_) {}
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  void _openSlide(BuildContext context, HeroSlideItem slide) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => ProductListScreen(
          initialCategory: slide.targetCategory,
          title: slide.label,
        ),
      ),
    );
  }

  Widget _buildSlideImage(HeroSlideItem slide) {
    final rawUrl = slide.imageUrl;
    final imgUrl = ApiService.normalizeImageUrl(rawUrl);
    if (imgUrl.isNotEmpty) {
      return Image.network(
        imgUrl,
        fit: BoxFit.cover,
        alignment: Alignment.centerRight,
        width: double.infinity,
        height: double.infinity,
        errorBuilder: (_, __, ___) => _buildFallbackAsset(slide),
      );
    }
    return _buildFallbackAsset(slide);
  }

  Widget _buildFallbackAsset(HeroSlideItem slide) {
    return Image.asset(
      slide.localAssetPath,
      fit: BoxFit.cover,
      alignment: Alignment.centerRight,
      width: double.infinity,
      height: double.infinity,
      errorBuilder: (_, __, ___) => Image.asset(
        'assets/cartzy-${slide.key}.jpg',
        fit: BoxFit.cover,
        alignment: Alignment.centerRight,
        width: double.infinity,
        height: double.infinity,
        errorBuilder: (_, __, ___) => Image.asset(
          'assets/cartzy-home.jpg',
          fit: BoxFit.cover,
          alignment: Alignment.centerRight,
          width: double.infinity,
          height: double.infinity,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_slides.isEmpty) return const SizedBox.shrink();

    final screenWidth = MediaQuery.of(context).size.width;
    final isMobile = screenWidth < 650;
    final slideIndex = _getSlideIndex(_current);
    final slide = _slides[slideIndex];

    return Container(
      width: double.infinity,
      color: const Color(0xFFFDFBF7),
      child: AspectRatio(
        aspectRatio: screenWidth /
            // A shorter mobile frame also reduces the cover image's zoom.
            (screenWidth / (isMobile ? 1.85 : 16 / 9) + widget.contentTopInset),
        child: LayoutBuilder(
          builder: (context, constraints) {
            final w = constraints.maxWidth;
            final h = constraints.maxHeight - widget.contentTopInset;
            return Stack(
              children: [
                // ── 1. Full-bleed Background Image (Infinite Loop) ──
                Positioned.fill(
                  // Fill the header behind search as well as the hero content.
                  child: PageView.builder(
                    controller: _pageController,
                    onPageChanged: (i) => setState(() => _current = i),
                    itemBuilder: (context, index) {
                      final slideItem = _slides[_getSlideIndex(index)];
                      return _buildSlideImage(slideItem);
                    },
                  ),
                ),

                // ── 2. Precise Left Gradient Scrim (Leaves right 48% untouched) ──
                Positioned.fill(
                  child: IgnorePointer(
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.centerLeft,
                          end: Alignment.centerRight,
                          colors: [
                            Colors.white
                                .withValues(alpha: isMobile ? 0.96 : 0.55),
                            Colors.white
                                .withValues(alpha: isMobile ? 0.78 : 0.20),
                            Colors.white.withValues(alpha: 0.0),
                          ],
                          stops: isMobile
                              ? const [0.0, 0.38, 0.54]
                              : const [0.0, 0.42, 0.65],
                        ),
                      ),
                    ),
                  ),
                ),

                // ── 3. High-Fashion Editorial Typography (Strictly on Left 48%, Vertically Centered) ──
                Positioned(
                  left: isMobile ? 18 : w * 0.04,
                  top: widget.contentTopInset,
                  bottom: 0,
                  width: isMobile ? (w * 0.48) : (w * 0.52),
                  child: Align(
                    alignment: Alignment.centerLeft,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Eyebrow tag
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              width: 5,
                              height: 5,
                              decoration: BoxDecoration(
                                color: slide.accentColor,
                                shape: BoxShape.circle,
                              ),
                            ),
                            const SizedBox(width: 5),
                            Flexible(
                              child: Text(
                                slide.label.toUpperCase(),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: GoogleFonts.poppins(
                                  color: slide.accentColor,
                                  fontSize: isMobile
                                      ? 8.5
                                      : (w * 0.011).clamp(11.0, 14.0),
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: isMobile ? 0.8 : 1.5,
                                ),
                              ),
                            ),
                            Text(
                              ' | CARTZY',
                              style: GoogleFonts.poppins(
                                color: CartzyColors.gray,
                                fontSize: isMobile
                                    ? 8.0
                                    : (w * 0.011).clamp(11.0, 14.0),
                                fontWeight: FontWeight.w600,
                                letterSpacing: 0.5,
                              ),
                            ),
                          ],
                        ),
                        SizedBox(height: isMobile ? 3 : h * 0.03),

                        // Headline line 1 (Dark Navy)
                        Text(
                          slide.title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.playfairDisplay(
                            color: CartzyColors.navy,
                            fontSize: isMobile
                                ? (w * 0.042).clamp(14.0, 17.5)
                                : (w * 0.034).clamp(20.0, 42.0),
                            fontWeight: FontWeight.w800,
                            height: 1.15,
                          ),
                        ),

                        // Headline line 2 (Accent Color)
                        Text(
                          slide.accent,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.playfairDisplay(
                            color: slide.accentColor,
                            fontSize: isMobile
                                ? (w * 0.042).clamp(14.0, 17.5)
                                : (w * 0.034).clamp(20.0, 42.0),
                            fontWeight: FontWeight.w800,
                            height: 1.15,
                          ),
                        ),
                        SizedBox(height: isMobile ? 3 : h * 0.02),

                        // Description
                        Text(
                          slide.description,
                          maxLines: isMobile ? 2 : 3,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.poppins(
                            color: CartzyColors.gray,
                            fontSize: isMobile
                                ? (w * 0.027).clamp(9.5, 11.0)
                                : (w * 0.013).clamp(11.5, 16.0),
                            fontWeight: FontWeight.w400,
                            height: isMobile ? 1.30 : 1.6,
                          ),
                        ),
                        SizedBox(height: isMobile ? 8 : 14),

                        // Chic CTA Button
                        GestureDetector(
                          onTap: () => _openSlide(context, slide),
                          child: Container(
                            padding: isMobile
                                ? const EdgeInsets.symmetric(
                                    horizontal: 11, vertical: 5.5)
                                : EdgeInsets.symmetric(
                                    horizontal: (w * 0.018).clamp(14.0, 24.0),
                                    vertical: (h * 0.04).clamp(8.0, 14.0),
                                  ),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                                colors: [
                                  CartzyColors.navy,
                                  Color(0xFF2C2237),
                                ],
                              ),
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: [
                                BoxShadow(
                                  color:
                                      CartzyColors.navy.withValues(alpha: 0.28),
                                  blurRadius: 6,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Flexible(
                                  child: Text(
                                  slide.cta.toUpperCase(),
                                  overflow: TextOverflow.ellipsis,
                                  style: GoogleFonts.poppins(
                                    color: Colors.white,
                                    fontSize: isMobile
                                        ? 9.0
                                        : (w * 0.011).clamp(11.0, 13.5),
                                    fontWeight: FontWeight.w700,
                                    letterSpacing: 0.5,
                                  ),
                                ),
                                ),
                                const SizedBox(width: 4),
                                Container(
                                  width: isMobile
                                      ? 15
                                      : (w * 0.022).clamp(18.0, 26.0),
                                  height: isMobile
                                      ? 15
                                      : (w * 0.022).clamp(18.0, 26.0),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.20),
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(
                                    Icons.arrow_forward_rounded,
                                    color: Colors.white,
                                    size: isMobile
                                        ? 9.5
                                        : (w * 0.012).clamp(12.0, 16.0),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

                // ── 4. Circular Page Indicator Dots (Centered) ──
                Positioned(
                  bottom: isMobile ? 10 : 14,
                  left: 0,
                  right: 0,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(_slides.length, (i) {
                      final isActive = i == slideIndex;
                      return GestureDetector(
                        onTap: () {
                          _startTimer();
                          final pageDiff = i - slideIndex;
                          final targetPage =
                              (_pageController.page?.round() ?? _current) +
                                  pageDiff;
                          _pageController.animateToPage(
                            targetPage,
                            duration: const Duration(milliseconds: 700),
                            curve: Curves.easeOutCubic,
                          );
                        },
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 300),
                          margin: const EdgeInsets.symmetric(horizontal: 4.0),
                          width: isActive ? 8.5 : 6.0,
                          height: isActive ? 8.5 : 6.0,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: isActive
                                ? slide.accentColor
                                : Colors.black.withValues(alpha: 0.22),
                            border: Border.all(
                              color: Colors.white.withValues(alpha: 0.9),
                              width: 1.0,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.15),
                                blurRadius: 3,
                                offset: const Offset(0, 1),
                              ),
                            ],
                          ),
                        ),
                      );
                    }),
                  ),
                ),
              ],
            );
          },
        ),
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
    final subColor =
        dark ? Colors.white.withValues(alpha: 0.7) : CartzyColors.gray;
    final tagBg =
        dark ? Colors.white.withValues(alpha: 0.12) : CartzyColors.roseSoft;
    final tagText = dark ? Colors.white : CartzyColors.burgundy;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(16),
        border: dark ? null : Border.all(color: CartzyColors.border),
        boxShadow: dark
            ? null
            : [
                BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 8,
                    offset: const Offset(0, 2))
              ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
                color: tagBg, borderRadius: BorderRadius.circular(20)),
            child: Text(
              tag,
              style: GoogleFonts.dmSans(
                  fontSize: 9,
                  fontWeight: FontWeight.w700,
                  color: tagText,
                  letterSpacing: 0.6),
            ),
          ),
          const SizedBox(height: 10),
          Text(title,
              style: GoogleFonts.playfairDisplay(
                  fontSize: 15, fontWeight: FontWeight.w700, color: textColor)),
          const SizedBox(height: 4),
          Text(description,
              style: GoogleFonts.dmSans(
                  fontSize: 12, color: subColor, height: 1.4)),
          const SizedBox(height: 8),
          Text(
            linkText,
            style: GoogleFonts.dmSans(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: dark ? Colors.white : CartzyColors.burgundy,
            ),
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
  CategoryIconItem(label: 'General', icon: Icons.grid_view_outlined),
  CategoryIconItem(label: 'Women Clothing', icon: Icons.checkroom_outlined),
  CategoryIconItem(label: 'Beachwear', icon: Icons.beach_access_outlined),
  CategoryIconItem(label: 'Kids', icon: Icons.child_care_outlined),
  CategoryIconItem(label: 'Curve', icon: Icons.accessibility_new_outlined),
  CategoryIconItem(label: 'Men Clothing', icon: Icons.man_outlined),
  CategoryIconItem(label: 'Shoes', icon: Icons.roller_skating_outlined),
  CategoryIconItem(label: 'Jewelry & Accessories', icon: Icons.watch_outlined),
  CategoryIconItem(
      label: 'Underwear & Sleepwear', icon: Icons.bedtime_outlined),
  CategoryIconItem(
      label: 'Baby & Maternity', icon: Icons.child_friendly_outlined),
  CategoryIconItem(label: 'Bags & Luggage', icon: Icons.luggage_outlined),
  CategoryIconItem(label: 'Home & Living', icon: Icons.chair_outlined),
  CategoryIconItem(label: 'Beauty & Health', icon: Icons.spa_outlined),
  CategoryIconItem(
      label: 'Sports & Outdoors', icon: Icons.sports_soccer_outlined),
  CategoryIconItem(label: 'Home Textiles', icon: Icons.texture_outlined),
  CategoryIconItem(
      label: 'Cell Phones & Accessories', icon: Icons.smartphone_outlined),
  CategoryIconItem(label: 'Electronics', icon: Icons.devices_outlined),
  CategoryIconItem(
      label: 'Tools & Home Improvement', icon: Icons.build_outlined),
  CategoryIconItem(label: 'Toys & Games', icon: Icons.sports_esports_outlined),
  CategoryIconItem(label: 'Pet Supplies', icon: Icons.pets_outlined),
  CategoryIconItem(label: 'Appliances', icon: Icons.kitchen_outlined),
  CategoryIconItem(
      label: 'Office & School Supplies', icon: Icons.school_outlined),
  CategoryIconItem(label: 'Automotive', icon: Icons.directions_car_outlined),
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
      height: 98,
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
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      color:
                          selected ? CartzyColors.navy : CartzyColors.surface,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color:
                            selected ? CartzyColors.navy : CartzyColors.border,
                        width: 1.5,
                      ),
                    ),
                    child: Icon(
                      item.icon,
                      color: selected ? Colors.white : CartzyColors.burgundy,
                      size: 22,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    item.label,
                    textAlign: TextAlign.center,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.dmSans(
                      fontSize: 10,
                      color: selected ? CartzyColors.navy : CartzyColors.gray,
                      fontWeight: selected ? FontWeight.w700 : FontWeight.w500,
                      height: 1.2,
                    ),
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
// CATEGORY GRID  (Browse all categories — 2-column card grid)
// ============================================================
// CATEGORY GRID  (Shop by Category — "Find your next favorite" cards)
// ============================================================

// ignore: unused_element
class _CategoryCard {
  final String title;
  final String subtitle;
  final IconData icon;

  const _CategoryCard({
    required this.title,
    required this.subtitle,
    required this.icon,
  });
}

class _CategoryFeatureCard {
  final String title;
  final String subtitle;
  final String badge;
  final String imageUrl;
  final String assetFallback;
  final String targetCategory;
  final bool isHighlighted;

  const _CategoryFeatureCard({
    required this.title,
    required this.subtitle,
    required this.badge,
    required this.imageUrl,
    required this.assetFallback,
    required this.targetCategory,
    this.isHighlighted = false,
  });
}

const List<_CategoryFeatureCard> _categoryCards = [
  _CategoryFeatureCard(
    title: "Women's Fashion",
    subtitle: 'Everyday looks, your way.',
    badge: 'Wear your mood',
    imageUrl:
        'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&w=800&q=80',
    assetFallback: 'assets/banner_everyday_style.jpg',
    targetCategory: 'Women Clothing',
  ),
  _CategoryFeatureCard(
    title: 'Home & Living',
    subtitle: 'Thoughtful finds for your space.',
    badge: 'Make room for comfort',
    imageUrl:
        'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80',
    assetFallback: 'assets/banner_home_living.jpg',
    targetCategory: 'Home & Living',
  ),
  _CategoryFeatureCard(
    title: 'Tech & Gadgets',
    subtitle: 'Upgrade your everyday.',
    badge: 'Small upgrades, big possibilities',
    imageUrl:
        'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
    assetFallback: 'assets/banner_coffee_home.jpg',
    targetCategory: 'Electronics',
    isHighlighted: true,
  ),
  _CategoryFeatureCard(
    title: 'Beauty & Care',
    subtitle: 'Refresh your daily routine.',
    badge: 'A little time for you',
    imageUrl:
        'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=800&q=80',
    assetFallback: 'assets/banner_beauty.jpg',
    targetCategory: 'Beauty & Health',
  ),
];

class CategoryCardGrid extends StatelessWidget {
  final ValueChanged<String>? onCategoryTap;
  final VoidCallback? onBrowseAllCategories;

  const CategoryCardGrid({
    super.key,
    this.onCategoryTap,
    this.onBrowseAllCategories,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Header ───────────────────────────────────────
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Eyebrow
                    Row(
                      children: [
                        const Text(
                          '• ',
                          style: TextStyle(
                            color: Color(0xFF9E7766),
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Flexible(
                          child: Text(
                            'SHOP BY CATEGORY',
                            style: GoogleFonts.dmSans(
                              fontSize: 10.5,
                              fontWeight: FontWeight.w700,
                              letterSpacing: 1.4,
                              color: const Color(0xFF9E7766),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),

                    // Main Title: "Find your next favorite."
                    Text.rich(
                      TextSpan(
                        children: [
                          TextSpan(
                            text: 'Find your next ',
                            style: GoogleFonts.playfairDisplay(
                              fontSize: 27,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF261E30),
                              letterSpacing: -0.3,
                            ),
                          ),
                          TextSpan(
                            text: 'favorite.',
                            style: GoogleFonts.playfairDisplay(
                              fontSize: 27,
                              fontWeight: FontWeight.w600,
                              fontStyle: FontStyle.italic,
                              color: const Color(0xFF8A7684),
                              letterSpacing: -0.3,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 3),

                    // Subtitle
                    Text(
                      'Explore everyday essentials, style, and more.',
                      style: GoogleFonts.dmSans(
                        fontSize: 12.5,
                        color: const Color(0xFF756A7C),
                      ),
                    ),
                  ],
                ),
              ),

              // "Browse all categories ↗" pill button
              GestureDetector(
                onTap: () {
                  if (onBrowseAllCategories != null) {
                    onBrowseAllCategories!();
                  } else {
                    onCategoryTap?.call('All');
                  }
                },
                child: Container(
                  margin: const EdgeInsets.only(top: 4),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(22),
                    border: Border.all(
                      color: const Color(0xFFE6D8D5),
                      width: 1.1,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.02),
                        blurRadius: 4,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Browse all categories',
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          fontWeight: FontWeight.w500,
                          color: const Color(0xFF6E5E65),
                        ),
                      ),
                      const SizedBox(width: 5),
                      const Icon(
                        Icons.north_east,
                        size: 11,
                        color: Color(0xFF6E5E65),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 18),

          // ── Cards Layout (responsive 4-column row on web / desktop) ──
          LayoutBuilder(
            builder: (context, constraints) {
              if (constraints.maxWidth >= 960) {
                return Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: List.generate(_categoryCards.length, (index) {
                    final card = _categoryCards[index];
                    return Expanded(
                      child: Padding(
                        padding: EdgeInsets.only(
                          right: index < _categoryCards.length - 1 ? 14 : 0,
                        ),
                        child: _CategoryCardItem(
                          card: card,
                          onTap: () => onCategoryTap?.call(card.targetCategory),
                        ),
                      ),
                    );
                  }),
                );
              } else {
                return SizedBox(
                  height: 275,
                  child: ListView.separated(
                    scrollDirection: Axis.horizontal,
                    itemCount: _categoryCards.length,
                    separatorBuilder: (_, __) => const SizedBox(width: 14),
                    itemBuilder: (context, index) {
                      final card = _categoryCards[index];
                      return SizedBox(
                        width: 250,
                        child: _CategoryCardItem(
                          card: card,
                          onTap: () => onCategoryTap?.call(card.targetCategory),
                        ),
                      );
                    },
                  ),
                );
              }
            },
          ),
        ],
      ),
    );
  }
}

class _CategoryCardItem extends StatelessWidget {
  final _CategoryFeatureCard card;
  final VoidCallback onTap;

  const _CategoryCardItem({required this.card, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: const Color(0xFFE8DCDA),
            width: 1.1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.025),
              blurRadius: 10,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            // Image area with badge
            ClipRRect(
              borderRadius:
                  const BorderRadius.vertical(top: Radius.circular(17)),
              child: SizedBox(
                height: 180,
                width: double.infinity,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    Image.network(
                      card.imageUrl,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Image.asset(
                        card.assetFallback,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Container(
                          color: const Color(0xFFF2ECE9),
                          child: const Icon(
                            Icons.image_outlined,
                            size: 40,
                            color: Color(0xFFC0B2B4),
                          ),
                        ),
                      ),
                    ),

                    // Floating micro-chip on image
                    Positioned(
                      left: 12,
                      bottom: 12,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 5,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.94),
                          borderRadius: BorderRadius.circular(14),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.08),
                              blurRadius: 6,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Text(
                          card.badge,
                          style: GoogleFonts.dmSans(
                            fontSize: 10.5,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF2C2436),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Text + Arrow Button
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 12, 12, 14),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          card.title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.dmSans(
                            fontSize: 13.5,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF261E30),
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          card.subtitle,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            color: const Color(0xFF756A7C),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),

                  // Circle arrow button
                  Container(
                    width: 26,
                    height: 26,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: card.isHighlighted
                          ? const Color(0xFF9E4B5E)
                          : Colors.white,
                      border: Border.all(
                        color: card.isHighlighted
                            ? Colors.transparent
                            : const Color(0xFFE2D0D3),
                        width: 1.1,
                      ),
                    ),
                    child: Icon(
                      Icons.north_east,
                      size: 11,
                      color: card.isHighlighted
                          ? Colors.white
                          : const Color(0xFF9E4B5E),
                    ),
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
// FLASH DEALS (synced with website database)
// ============================================================

class FlashDealsSection extends StatefulWidget {
  final VoidCallback? onViewAll;
  final void Function(ProductItem product)? onProductTap;

  const FlashDealsSection({super.key, this.onViewAll, this.onProductTap});

  @override
  State<FlashDealsSection> createState() => _FlashDealsSectionState();
}

class _FlashDealsSectionState extends State<FlashDealsSection> {
  Duration _remaining = const Duration(hours: 2, minutes: 45, seconds: 18);
  Timer? _timer;
  List<ProductItem> _deals = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadDeals();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (!mounted) return;
      setState(() {
        if (_remaining.inSeconds > 0) {
          _remaining -= const Duration(seconds: 1);
        } else {
          _remaining = const Duration(hours: 2, minutes: 45, seconds: 18);
        }
      });
    });
  }

  Future<void> _loadDeals() async {
    try {
      var list = await CatalogService.fetchProducts(category: 'Flash Deals');
      if (list.isEmpty) {
        list = await CatalogService.fetchProducts(perPage: 10);
      }
      debugPrint('[FlashDeals] loaded: ${list.length} deals');
      if (mounted) {
        setState(() {
          _deals = list;
          _loading = false;
        });
      }
    } catch (e) {
      debugPrint('[FlashDeals] error: $e');
      if (mounted) {
        setState(() => _loading = false);
      }
    }
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
              Expanded(
                child: Wrap(
                spacing: 10,
                runSpacing: 6,
                crossAxisAlignment: WrapCrossAlignment.center,
                children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: CartzyColors.roseSoft,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.bolt,
                        color: CartzyColors.burgundy, size: 14),
                    const SizedBox(width: 4),
                    Text(
                      'FLASH DEALS',
                      style: GoogleFonts.dmSans(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: CartzyColors.burgundy,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
              ),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _timeBlock(h),
                  _colon(),
                  _timeBlock(m),
                  _colon(),
                  _timeBlock(s),
                ],
              ),
                ],
                ),
              ),
              const SizedBox(width: 8),
              GestureDetector(
                onTap: widget.onViewAll,
                child: Text(
                  'View All →',
                  style: GoogleFonts.dmSans(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: CartzyColors.burgundy,
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),
        SizedBox(
          height: 190,
          child: _loading
              ? const Center(
                  child:
                      CircularProgressIndicator(color: CartzyColors.burgundy))
              : _deals.isEmpty
                  ? Center(
                      child: Text(
                        'No flash deals at the moment',
                        style: GoogleFonts.dmSans(
                            color: CartzyColors.gray, fontSize: 12),
                      ),
                    )
                  : ListView.separated(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(horizontal: 20),
                      itemCount: _deals.length,
                      separatorBuilder: (_, __) => const SizedBox(width: 12),
                      itemBuilder: (context, index) {
                        final deal = _deals[index];
                        return _FlashDealCard(
                          deal: deal,
                          onTap: widget.onProductTap != null
                              ? () => widget.onProductTap!(deal)
                              : null,
                        );
                      },
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
      child: Text(value,
          style: GoogleFonts.dmSans(
              fontSize: 11, fontWeight: FontWeight.w700, color: Colors.white)),
    );
  }

  Widget _colon() => const Padding(
        padding: EdgeInsets.symmetric(horizontal: 2),
        child: Text(':',
            style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy)),
      );
}

class _FlashDealCard extends StatelessWidget {
  final ProductItem deal;
  final VoidCallback? onTap;

  const _FlashDealCard({required this.deal, this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 130,
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: CartzyColors.surface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: CartzyColors.border),
          boxShadow: [
            BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 8,
                offset: const Offset(0, 2))
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Stack(
                children: [
                  SizedBox(
                    width: double.infinity,
                    height: double.infinity,
                    child: CartzyProductImage(
                      imageUrl: deal.effectiveImageUrl,
                      fit: BoxFit.cover,
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  if (deal.discountPercent > 0)
                    Positioned(
                      top: 4,
                      right: 4,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 6, vertical: 3),
                        decoration: BoxDecoration(
                          color: CartzyColors.burgundy,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          '-${deal.discountPercent}%',
                          style: GoogleFonts.dmSans(
                              fontSize: 9,
                              fontWeight: FontWeight.w700,
                              color: Colors.white),
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
              style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: CartzyColors.navy),
            ),
            const SizedBox(height: 2),
            Text(
              deal.price,
              style: GoogleFonts.dmSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: CartzyColors.burgundy),
            ),
          ],
        ),
      ),
    );
  }
}

// ============================================================
// DAILY DISCOVER SECTION (synced with website database)
// ============================================================

class DailyDiscoverSection extends StatefulWidget {
  final VoidCallback onBrowseCategories;
  final void Function(ProductItem product) onProductTap;
  final void Function(ProductItem product)? onAddToCart;

  const DailyDiscoverSection({
    super.key,
    required this.onBrowseCategories,
    required this.onProductTap,
    this.onAddToCart,
  });

  @override
  State<DailyDiscoverSection> createState() => DailyDiscoverSectionState();
}

class DailyDiscoverSectionState extends State<DailyDiscoverSection> {
  static const int _productsPerRow = 6;
  List<ProductItem> _products = [];
  bool _loading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    reload();
  }

  Future<void> reload() async {
    if (mounted) {
      setState(() {
        _loading = true;
        _errorMessage = null;
      });
    }
    try {
      final list = await CatalogService.fetchProducts(perPage: 50);
      debugPrint('[DailyDiscover] loaded: ${list.length} products');
      if (mounted) {
        setState(() {
          _products = list;
          _loading = false;
          _errorMessage = null;
        });
      }
    } catch (e) {
      debugPrint('[DailyDiscover] error: $e');
      if (mounted) {
        setState(() {
          _loading = false;
          _errorMessage = e.toString();
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // ── Header Area ──
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: LayoutBuilder(
            builder: (context, constraints) {
              final isNarrow = constraints.maxWidth < 420;
              return Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Pill: "Picked for you"
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 9, vertical: 3.5),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFBF4F1),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                                color: const Color(0xFFECDED9), width: 1),
                          ),
                          child: Text(
                            'Picked for you',
                            style: GoogleFonts.dmSans(
                              fontSize: 10.5,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFFA66E65),
                            ),
                          ),
                        ),
                        const SizedBox(height: 6),
                        // Title: "Daily Discover"
                        Text(
                          'Daily Discover',
                          style: GoogleFonts.playfairDisplay(
                            fontSize: isNarrow ? 22 : 26,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF231F20),
                            letterSpacing: -0.3,
                          ),
                        ),
                        const SizedBox(height: 2),
                        // Subtitle: "Everyday favorites, all in one place."
                        Text(
                          'Everyday favorites, all in one place.',
                          style: GoogleFonts.dmSans(
                            fontSize: isNarrow ? 12 : 13,
                            fontWeight: FontWeight.w400,
                            color: const Color(0xFF7A6E72),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  // "Browse categories  →"
                  InkWell(
                    onTap: widget.onBrowseCategories,
                    borderRadius: BorderRadius.circular(8),
                    child: Container(
                      padding: EdgeInsets.symmetric(
                        horizontal: isNarrow ? 10 : 14,
                        vertical: isNarrow ? 6 : 8,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                            color: const Color(0xFFECDED9), width: 1.2),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            isNarrow ? 'Browse' : 'Browse categories',
                            style: GoogleFonts.dmSans(
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                              color: const Color(0xFF5A4449),
                            ),
                          ),
                          const SizedBox(width: 4),
                          const Icon(Icons.arrow_forward,
                              size: 12, color: Color(0xFF5A4449)),
                        ],
                      ),
                    ),
                  ),
                ],
              );
            },
          ),
        ),

        const SizedBox(height: 16),
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 20),
          child: Divider(color: Color(0xFFF0E5E2), height: 1, thickness: 1),
        ),
        const SizedBox(height: 18),

        // ── Products List ──
        SizedBox(
          height: _loading || _products.isEmpty
              ? 312
              : ((_products.length + _productsPerRow - 1) ~/ _productsPerRow) *
                      (312.0 + 16) -
                  16,
          child: _loading
              ? const Center(
                  child:
                      CircularProgressIndicator(color: CartzyColors.burgundy))
              : (_errorMessage != null && _products.isEmpty)
                  ? Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.wifi_off_rounded,
                              size: 36,
                              color: CartzyColors.gray.withValues(alpha: 0.6)),
                          const SizedBox(height: 8),
                          Text(
                            'Unable to connect to server',
                            style: GoogleFonts.dmSans(
                              color: CartzyColors.navy,
                              fontWeight: FontWeight.w600,
                              fontSize: 14,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Check connection or tap to retry',
                            style: GoogleFonts.dmSans(
                              color: CartzyColors.gray,
                              fontSize: 12,
                            ),
                          ),
                          const SizedBox(height: 10),
                          OutlinedButton.icon(
                            onPressed: reload,
                            icon: const Icon(Icons.refresh, size: 16),
                            label: const Text('Retry'),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: CartzyColors.burgundy,
                              side: const BorderSide(
                                  color: CartzyColors.burgundy),
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 16, vertical: 8),
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(20)),
                            ),
                          ),
                        ],
                      ),
                    )
                  : _products.isEmpty
                      ? Center(
                          child: Text(
                            'No products found in the catalog.',
                            style: GoogleFonts.dmSans(color: CartzyColors.gray),
                          ),
                        )
                      : Column(
                          children: [
                            for (var rowStart = 0;
                                rowStart < _products.length;
                                rowStart += _productsPerRow) ...[
                              if (rowStart > 0) const SizedBox(height: 16),
                              SizedBox(
                                // Square photo (card width 155) + details.
                                height: 155 + ProductCard.bodyHeight(context),
                                child: ListView.separated(
                                  key: PageStorageKey(
                                      'daily-discover-row-$rowStart'),
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 20),
                                  scrollDirection: Axis.horizontal,
                                  primary: false,
                                  itemCount: (_products.length - rowStart)
                                      .clamp(0, _productsPerRow),
                                  separatorBuilder: (_, __) =>
                                      const SizedBox(width: 10),
                                  itemBuilder: (context, index) {
                                    final p = _products[rowStart + index];
                                    return SizedBox(
                                      width: 155,
                                      child: ProductCard(
                                        name: p.name,
                                        price: p.price,
                                        originalPrice: p.originalPrice,
                                        discountPercent: p.discountPercent > 0
                                            ? p.discountPercent
                                            : null,
                                        badge: p.badge,
                                        rating: p.rating,
                                        soldCount: p.soldCount,
                                        hasFreeShipping: p.hasFreeShipping,
                                        sellerName: p.sellerName,
                                        inStock: p.inStock,
                                        stock: p.stock,
                                        imageUrl: p.effectiveImageUrl,
                                        onTap: () => widget.onProductTap(p),
                                        onAddToCart: widget.onAddToCart != null
                                            ? () => widget.onAddToCart!(p)
                                            : null,
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ],
                          ],
                        ),
        ),
      ],
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

  const SectionTitle(
      {super.key, required this.title, this.actionText, this.onAction});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Row(
        children: [
          Text(
            title,
            style: GoogleFonts.playfairDisplay(
              fontSize: 18,
              fontWeight: FontWeight.w700,
              color: CartzyColors.navy,
            ),
          ),
          const Spacer(),
          if (actionText != null)
            GestureDetector(
              onTap: onAction,
              child: Text(
                actionText!,
                style: GoogleFonts.dmSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: CartzyColors.burgundy,
                ),
              ),
            ),
        ],
      ),
    );
  }
}

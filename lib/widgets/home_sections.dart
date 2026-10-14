import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/product_card.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';
import 'package:cartzy/utils/product_images.dart';
import 'package:cartzy/products/product_list_screen.dart';

// ============================================================
// HERO BANNER CAROUSEL  (reference: cartzyy.shop dashboard banner)
// ============================================================

class _HeroBannerSlide {
  final String imagePath;
  final String category;
  final String title;
  final String overline;
  final String headlineLine1;
  final String headlineLine2;
  final String body;
  final String cta;
  final Color headlineAccentColor;

  const _HeroBannerSlide({
    required this.imagePath,
    required this.category,
    required this.title,
    this.overline = '',
    this.headlineLine1 = '',
    this.headlineLine2 = '',
    this.body = '',
    this.cta = 'SHOP NOW',
    this.headlineAccentColor = const Color(0xFF8B4513),
  });
}

const List<_HeroBannerSlide> _slides = [
  _HeroBannerSlide(
    imagePath: 'assets/banner_home_living.jpg',
    category: 'Home & Living',
    title: 'Home & Living',
    overline: 'HOME & LIVING',
    headlineLine1: 'Good finds.',
    headlineLine2: 'Great everyday living.',
    body: 'Make room for the things you love. Discover home\nessentials, fresh styles, and everyday favorites, all on\nCartzy.',
    cta: 'EXPLORE THE MARKETPLACE',
    headlineAccentColor: Color(0xFF8B4513),
  ),
  _HeroBannerSlide(
    imagePath: 'assets/banner_everyday_style.jpg',
    category: 'Shoes',
    title: 'Everyday Style',
    overline: 'EVERYDAY STYLE',
    headlineLine1: 'Go your own way.',
    headlineLine2: 'Find your everyday fit.',
    body: 'Easy layers, fresh kicks, and finishing touches. Meet the\npieces that make every day feel a little more you.',
    cta: 'FIND YOUR NEXT PAIR',
    headlineAccentColor: Color(0xFF8B4513),
  ),
  _HeroBannerSlide(
    imagePath: 'assets/banner_beauty.jpg',
    category: 'Beauty & Health',
    title: 'Beauty & Self-Care',
    overline: 'BEAUTY & SELF-CARE',
    headlineLine1: 'A little me-time.',
    headlineLine2: 'A softer kind of glow.',
    body: 'Slow down and make space for yourself. Discover\nbeauty essentials for your everyday rituals.',
    cta: 'EXPLORE BEAUTY',
    headlineAccentColor: Color(0xFFB5628F),
  ),
  _HeroBannerSlide(
    imagePath: 'assets/banner_jewelry.jpg',
    category: 'Jewelry & Accessories',
    title: 'Jewelry & Accessories',
    overline: 'JEWELRY & ACCESSORIES',
    headlineLine1: 'The little details.',
    headlineLine2: 'That make a look.',
    body: 'From everyday staples to statement pieces.\nFind the finishing touches that feel like you.',
    cta: 'EXPLORE JEWELRY',
    headlineAccentColor: Color(0xFFB8860B),
  ),
  _HeroBannerSlide(
    imagePath: 'assets/banner_coffee_home.jpg',
    category: 'Appliances',
    title: 'Coffee & Home',
    overline: 'COFFEE & HOME',
    headlineLine1: 'Morning rituals.',
    headlineLine2: 'Made a little better.',
    body: 'Upgrade your space, one small detail at a time.\nFrom kitchen must-haves to cozy home upgrades.',
    cta: 'EXPLORE APPLIANCES',
    headlineAccentColor: Color(0xFF6B4226),
  ),
  _HeroBannerSlide(
    imagePath: 'assets/banner_bags_travel.jpg',
    category: 'Bags & Luggage',
    title: 'Bags & Travel',
    overline: 'BAGS & TRAVEL',
    headlineLine1: 'Pack a little joy.',
    headlineLine2: 'Go somewhere new.',
    body: 'Weekend escapes or everyday adventures. Find bags\nand travel essentials ready to go with you.',
    cta: 'EXPLORE BAGS & TRAVEL',
    headlineAccentColor: Color(0xFF8B6F5C),
  ),
  _HeroBannerSlide(
    imagePath: 'assets/banner_wellness.png',
    category: 'Sports & Outdoors',
    title: 'Movement & Wellness',
    overline: 'MOVEMENT & WELLNESS',
    headlineLine1: 'Move more.',
    headlineLine2: 'Feel everything.',
    body: 'Gear up for the things that make you feel alive.\nFrom workouts to weekend hikes.',
    cta: 'EXPLORE WELLNESS',
    headlineAccentColor: Color(0xFF5B7F5E),
  ),
];

class HeroBanner extends StatefulWidget {
  final VoidCallback? onShopNow;

  const HeroBanner({super.key, this.onShopNow});

  @override
  State<HeroBanner> createState() => _HeroBannerState();
}

class _HeroBannerState extends State<HeroBanner> {
  late final PageController _pageController;
  int _current = 0;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
    _timer = Timer.periodic(const Duration(seconds: 5), (_) {
      if (mounted && _pageController.hasClients) {
        final next = (_current + 1) % _slides.length;
        _pageController.animateToPage(
          next,
          duration: const Duration(milliseconds: 600),
          curve: Curves.easeInOutCubic,
        );
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  void _prev() {
    if (_pageController.hasClients) {
      final prev = (_current - 1 + _slides.length) % _slides.length;
      _pageController.animateToPage(
        prev,
        duration: const Duration(milliseconds: 400),
        curve: Curves.easeInOut,
      );
    }
  }

  void _next() {
    if (_pageController.hasClients) {
      final next = (_current + 1) % _slides.length;
      _pageController.animateToPage(
        next,
        duration: const Duration(milliseconds: 400),
        curve: Curves.easeInOut,
      );
    }
  }

  void _openSlide(BuildContext context, _HeroBannerSlide slide) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => ProductListScreen(
          initialCategory: slide.category,
          title: slide.title,
        ),
      ),
    );
  }

  void _openDeals(BuildContext context) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => const ProductListScreen(
          initialCategory: 'Flash Deals',
          title: "Today's Deals",
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    final isMobile = screenWidth < 650;

    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(isMobile ? 16 : 20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: AspectRatio(
        aspectRatio: isMobile ? (screenWidth < 380 ? 1.25 : 1.38) : 16 / 9,
        child: LayoutBuilder(
          builder: (context, constraints) {
            final w = constraints.maxWidth;
            final h = constraints.maxHeight;
            final slide = _slides[_current];
            return Stack(
              children: [
                // ── Background image carousel ──
                PageView.builder(
                  controller: _pageController,
                  itemCount: _slides.length,
                  onPageChanged: (i) => setState(() => _current = i),
                  itemBuilder: (context, index) {
                    return Image.asset(
                      _slides[index].imagePath,
                      fit: BoxFit.cover,
                      alignment: Alignment.centerRight,
                      width: double.infinity,
                      height: double.infinity,
                    );
                  },
                ),

                // ── Soft readable gradient scrim for text side ──
                Positioned.fill(
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.centerLeft,
                        end: Alignment.centerRight,
                        colors: [
                          Colors.white.withValues(alpha: isMobile ? 0.92 : 0.45),
                          Colors.white.withValues(alpha: isMobile ? 0.70 : 0.15),
                          Colors.white.withValues(alpha: 0.0),
                        ],
                        stops: isMobile ? const [0.0, 0.58, 0.90] : const [0.0, 0.42, 0.72],
                      ),
                    ),
                  ),
                ),

                // ── Text overlay on left side ──
                Positioned(
                  left: isMobile ? 14 : w * 0.035,
                  top: isMobile ? 14 : h * 0.10,
                  width: isMobile ? (w * 0.65) : (w * 0.52),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Overline: "HOME & LIVING | CARTZY"
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            slide.overline,
                            style: GoogleFonts.poppins(
                              color: slide.headlineAccentColor,
                              fontSize: isMobile ? 9.5 : (w * 0.011).clamp(11.0, 14.0),
                              fontWeight: FontWeight.w700,
                              letterSpacing: isMobile ? 1.0 : 1.5,
                            ),
                          ),
                          Text(
                            '  |  CARTZY',
                            style: GoogleFonts.poppins(
                              color: CartzyColors.gray,
                              fontSize: isMobile ? 9.5 : (w * 0.011).clamp(11.0, 14.0),
                              fontWeight: FontWeight.w500,
                              letterSpacing: 1.0,
                            ),
                          ),
                        ],
                      ),
                      SizedBox(height: isMobile ? 4 : h * 0.05),
                      // Headline line 1 (dark)
                      Text(
                        slide.headlineLine1,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.playfairDisplay(
                          color: CartzyColors.navy,
                          fontSize: isMobile ? (w < 360 ? 15.0 : 17.0) : (w * 0.034).clamp(20.0, 42.0),
                          fontWeight: FontWeight.w700,
                          height: 1.15,
                        ),
                      ),
                      // Headline line 2 (accent color)
                      Text(
                        slide.headlineLine2,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.playfairDisplay(
                          color: slide.headlineAccentColor,
                          fontSize: isMobile ? (w < 360 ? 15.0 : 17.0) : (w * 0.034).clamp(20.0, 42.0),
                          fontWeight: FontWeight.w700,
                          height: 1.15,
                        ),
                      ),
                      SizedBox(height: isMobile ? 5 : h * 0.05),
                      // Body text
                      Text(
                        isMobile ? slide.body.replaceAll('\n', ' ') : slide.body,
                        maxLines: isMobile ? 2 : 3,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.poppins(
                          color: CartzyColors.gray,
                          fontSize: isMobile ? 10.5 : (w * 0.013).clamp(11.5, 16.0),
                          fontWeight: FontWeight.w400,
                          height: isMobile ? 1.35 : 1.6,
                        ),
                      ),
                      SizedBox(height: isMobile ? 9 : h * 0.07),
                      // CTA button + deals link
                      Wrap(
                        crossAxisAlignment: WrapCrossAlignment.center,
                        spacing: isMobile ? 8 : w * 0.015,
                        runSpacing: 6,
                        children: [
                          // Real CTA button
                          MouseRegion(
                            cursor: SystemMouseCursors.click,
                            child: GestureDetector(
                              onTap: () => _openSlide(context, slide),
                              child: Container(
                                padding: isMobile
                                    ? const EdgeInsets.symmetric(horizontal: 12, vertical: 7)
                                    : EdgeInsets.symmetric(
                                        horizontal: (w * 0.018).clamp(14.0, 24.0),
                                        vertical: (h * 0.04).clamp(8.0, 14.0),
                                      ),
                                decoration: BoxDecoration(
                                  color: CartzyColors.navy,
                                  borderRadius: BorderRadius.circular(30),
                                  boxShadow: [
                                    BoxShadow(
                                      color: CartzyColors.navy.withValues(alpha: 0.25),
                                      blurRadius: 6,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text(
                                      slide.cta,
                                      style: GoogleFonts.poppins(
                                        color: Colors.white,
                                        fontSize: isMobile ? 10.0 : (w * 0.011).clamp(11.0, 13.5),
                                        fontWeight: FontWeight.w600,
                                        letterSpacing: 0.6,
                                      ),
                                    ),
                                    SizedBox(width: isMobile ? 6 : w * 0.008),
                                    Container(
                                      width: isMobile ? 18.0 : (w * 0.022).clamp(18.0, 26.0),
                                      height: isMobile ? 18.0 : (w * 0.022).clamp(18.0, 26.0),
                                      decoration: BoxDecoration(
                                        color: Colors.white.withValues(alpha: 0.2),
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                      child: Icon(
                                        Icons.arrow_forward,
                                        color: Colors.white,
                                        size: isMobile ? 11.0 : (w * 0.012).clamp(12.0, 16.0),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          // "Explore today's deals" link
                          MouseRegion(
                            cursor: SystemMouseCursors.click,
                            child: GestureDetector(
                              onTap: () => _openDeals(context),
                              child: Text(
                                isMobile ? "Today's deals →" : "Explore today's deals  →",
                                style: GoogleFonts.poppins(
                                  color: CartzyColors.navy,
                                  fontSize: isMobile ? 10.5 : (w * 0.013).clamp(11.5, 14.5),
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                // ── Bottom-left category label ──
                Positioned(
                  left: isMobile ? 14 : w * 0.035,
                  bottom: isMobile ? 10 : h * 0.06,
                  child: Row(
                    children: [
                      Container(
                        width: isMobile ? 16 : 24,
                        height: 2,
                        color: slide.headlineAccentColor,
                      ),
                      SizedBox(width: isMobile ? 6 : 10),
                      Text(
                        slide.title,
                        style: GoogleFonts.poppins(
                          color: slide.headlineAccentColor,
                          fontSize: isMobile ? 10.0 : (w * 0.012).clamp(11.0, 14.0),
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                ),

                // ── Bottom-right: counter + nav arrows ──
                Positioned(
                  bottom: isMobile ? 8 : h * 0.06,
                  right: isMobile ? 10 : w * 0.02,
                  child: Row(
                    children: [
                      // Slide counter
                      Text(
                        '${(_current + 1).toString().padLeft(2, '0')} / ${_slides.length.toString().padLeft(2, '0')}',
                        style: GoogleFonts.poppins(
                          color: CartzyColors.navy.withValues(alpha: 0.65),
                          fontSize: isMobile ? 9.5 : (w * 0.012).clamp(11.0, 13.0),
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      SizedBox(width: isMobile ? 8 : w * 0.012),
                      // Previous arrow
                      MouseRegion(
                        cursor: SystemMouseCursors.click,
                        child: GestureDetector(
                          onTap: _prev,
                          child: Container(
                            width: isMobile ? 26.0 : (w * 0.032).clamp(28.0, 38.0),
                            height: isMobile ? 26.0 : (w * 0.032).clamp(28.0, 38.0),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.10),
                                  blurRadius: 4,
                                  offset: const Offset(0, 1),
                                ),
                              ],
                            ),
                            child: Icon(
                              Icons.chevron_left,
                              color: CartzyColors.navy,
                              size: isMobile ? 15.0 : (w * 0.018).clamp(16.0, 22.0),
                            ),
                          ),
                        ),
                      ),
                      SizedBox(width: isMobile ? 4 : w * 0.006),
                      // Page indicator dots
                      ...List.generate(_slides.length, (i) {
                        return AnimatedContainer(
                          duration: const Duration(milliseconds: 300),
                          width: i == _current
                              ? (isMobile ? 10.0 : (w * 0.016).clamp(12.0, 22.0))
                              : (isMobile ? 4.0 : (w * 0.005).clamp(4.0, 8.0)),
                          height: isMobile ? 4.0 : (w * 0.005).clamp(4.0, 8.0),
                          margin: EdgeInsets.symmetric(
                              horizontal: isMobile ? 1.5 : (w * 0.002).clamp(1.5, 4.0)),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(3),
                            color: i == _current
                                ? CartzyColors.burgundy
                                : CartzyColors.gray.withValues(alpha: 0.35),
                          ),
                        );
                      }),
                      SizedBox(width: isMobile ? 4 : w * 0.006),
                      // Next arrow
                      MouseRegion(
                        cursor: SystemMouseCursors.click,
                        child: GestureDetector(
                          onTap: _next,
                          child: Container(
                            width: isMobile ? 26.0 : (w * 0.032).clamp(28.0, 38.0),
                            height: isMobile ? 26.0 : (w * 0.032).clamp(28.0, 38.0),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.10),
                                  blurRadius: 4,
                                  offset: const Offset(0, 1),
                                ),
                              ],
                            ),
                            child: Icon(
                              Icons.chevron_right,
                              color: CartzyColors.navy,
                              size: isMobile ? 15.0 : (w * 0.018).clamp(16.0, 22.0),
                            ),
                          ),
                        ),
                      ),
                    ],
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
    final bg        = dark ? CartzyColors.navy : CartzyColors.surface;
    final textColor = dark ? Colors.white : CartzyColors.navy;
    final subColor  = dark ? Colors.white.withValues(alpha: 0.7) : CartzyColors.gray;
    final tagBg     = dark ? Colors.white.withValues(alpha: 0.12) : CartzyColors.roseSoft;
    final tagText   = dark ? Colors.white : CartzyColors.burgundy;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(16),
        border: dark ? null : Border.all(color: CartzyColors.border),
        boxShadow: dark
            ? null
            : [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(color: tagBg, borderRadius: BorderRadius.circular(20)),
            child: Text(
              tag,
              style: GoogleFonts.dmSans(fontSize: 9, fontWeight: FontWeight.w700, color: tagText, letterSpacing: 0.6),
            ),
          ),
          const SizedBox(height: 10),
          Text(title, style: GoogleFonts.playfairDisplay(fontSize: 15, fontWeight: FontWeight.w700, color: textColor)),
          const SizedBox(height: 4),
          Text(description, style: GoogleFonts.dmSans(fontSize: 12, color: subColor, height: 1.4)),
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
  CategoryIconItem(label: 'Underwear & Sleepwear', icon: Icons.bedtime_outlined),
  CategoryIconItem(label: 'Baby & Maternity', icon: Icons.child_friendly_outlined),
  CategoryIconItem(label: 'Bags & Luggage', icon: Icons.luggage_outlined),
  CategoryIconItem(label: 'Home & Living', icon: Icons.chair_outlined),
  CategoryIconItem(label: 'Beauty & Health', icon: Icons.spa_outlined),
  CategoryIconItem(label: 'Sports & Outdoors', icon: Icons.sports_soccer_outlined),
  CategoryIconItem(label: 'Home Textiles', icon: Icons.texture_outlined),
  CategoryIconItem(label: 'Cell Phones & Accessories', icon: Icons.smartphone_outlined),
  CategoryIconItem(label: 'Electronics', icon: Icons.devices_outlined),
  CategoryIconItem(label: 'Tools & Home Improvement', icon: Icons.build_outlined),
  CategoryIconItem(label: 'Toys & Games', icon: Icons.sports_esports_outlined),
  CategoryIconItem(label: 'Pet Supplies', icon: Icons.pets_outlined),
  CategoryIconItem(label: 'Appliances', icon: Icons.kitchen_outlined),
  CategoryIconItem(label: 'Office & School Supplies', icon: Icons.school_outlined),
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
                      color: selected ? CartzyColors.navy : CartzyColors.surface,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: selected ? CartzyColors.navy : CartzyColors.border,
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

class _CategoryCard {
  final String title;
  final String subtitle;
  final IconData icon;
  const _CategoryCard({required this.title, required this.subtitle, required this.icon});
}

const _categoryCards = [
  _CategoryCard(title: 'Women Clothing', subtitle: 'Everyday looks, your way.', icon: Icons.checkroom_outlined),
  _CategoryCard(title: 'Home & Living', subtitle: 'Thoughtful finds for your space.', icon: Icons.chair_outlined),
  _CategoryCard(title: 'Electronics', subtitle: 'Gear that works as hard as you.', icon: Icons.devices_outlined),
  _CategoryCard(title: 'Beauty & Health', subtitle: 'Glow up your routine.', icon: Icons.spa_outlined),
];

class CategoryCardGrid extends StatelessWidget {
  final ValueChanged<String>? onCategoryTap;

  const CategoryCardGrid({super.key, this.onCategoryTap});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // "Browse all categories" pill button
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: GestureDetector(
            onTap: () => onCategoryTap?.call('All'),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
              decoration: BoxDecoration(
                color: CartzyColors.surface,
                borderRadius: BorderRadius.circular(50),
                border: Border.all(color: CartzyColors.rose, width: 1.3),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'Browse all categories',
                    style: GoogleFonts.dmSans(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: CartzyColors.burgundy,
                    ),
                  ),
                  const SizedBox(width: 6),
                  Container(
                    width: 22,
                    height: 22,
                    decoration: BoxDecoration(
                      color: CartzyColors.roseSoft,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Icon(Icons.north_east, size: 12, color: CartzyColors.burgundy),
                  ),
                ],
              ),
            ),
          ),
        ),
        const SizedBox(height: 14),

        // 2-column grid
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 0.84,
            ),
            itemCount: _categoryCards.length,
            itemBuilder: (context, index) {
              final card = _categoryCards[index];
              return _CategoryCardItem(
                card: card,
                onTap: () => onCategoryTap?.call(card.title),
              );
            },
          ),
        ),
      ],
    );
  }
}

class _CategoryCardItem extends StatelessWidget {
  final _CategoryCard card;
  final VoidCallback onTap;

  const _CategoryCardItem({required this.card, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: CartzyColors.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: CartzyColors.rose.withValues(alpha: 0.55), width: 1.3),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image / icon area
            Expanded(
              child: Container(
                width: double.infinity,
                decoration: BoxDecoration(
                  color: CartzyColors.creamSection,
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(14)),
                ),
                child: Center(
                  child: Icon(
                    card.icon,
                    size: 58,
                    color: CartzyColors.navy.withValues(alpha: 0.25),
                  ),
                ),
              ),
            ),

            // Text + arrow
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 10, 8, 12),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
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
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: CartzyColors.navy,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          card.subtitle,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            color: CartzyColors.gray,
                            height: 1.3,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    width: 26,
                    height: 26,
                    decoration: BoxDecoration(
                      color: CartzyColors.roseSoft,
                      borderRadius: BorderRadius.circular(7),
                      border: Border.all(color: CartzyColors.rose.withValues(alpha: 0.4)),
                    ),
                    child: const Icon(Icons.north_east, size: 13, color: CartzyColors.burgundy),
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
// FLASH DEALS
// ============================================================

class FlashDeal {
  final String name;
  final String price;
  final int discountPercent;
  final String? imageUrl;

  const FlashDeal({
    required this.name,
    required this.price,
    required this.discountPercent,
    this.imageUrl,
  });
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
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: CartzyColors.roseSoft,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.bolt, color: CartzyColors.burgundy, size: 14),
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
              const SizedBox(width: 10),
              _timeBlock(h),
              _colon(),
              _timeBlock(m),
              _colon(),
              _timeBlock(s),
              const Spacer(),
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
      child: Text(value, style: GoogleFonts.dmSans(fontSize: 11, fontWeight: FontWeight.w700, color: Colors.white)),
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
        border: Border.all(color: CartzyColors.border),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.04), blurRadius: 8, offset: const Offset(0, 2))],
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
                    imageUrl: (deal.imageUrl != null && deal.imageUrl!.isNotEmpty)
                        ? deal.imageUrl
                        : CartzyProductImages.getImage(deal.name),
                    fit: BoxFit.cover,
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                Positioned(
                  top: 4,
                  right: 4,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                    decoration: BoxDecoration(
                      color: CartzyColors.burgundy,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      '-${deal.discountPercent}%',
                      style: GoogleFonts.dmSans(fontSize: 9, fontWeight: FontWeight.w700, color: Colors.white),
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
            style: GoogleFonts.dmSans(fontSize: 12, fontWeight: FontWeight.w600, color: CartzyColors.navy),
          ),
          const SizedBox(height: 2),
          Text(
            deal.price,
            style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w700, color: CartzyColors.burgundy),
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
  final String? imageUrl;
  final bool hasFreeShipping;

  const DiscoverProduct({
    required this.name,
    required this.price,
    required this.discountPercent,
    required this.badge,
    required this.rating,
    required this.soldCount,
    this.imageUrl,
    this.hasFreeShipping = false,
  });
}

const List<DiscoverProduct> discoverProducts = [
  DiscoverProduct(
    name: 'Smart Fitness Tracker Watch with Blood Oxygen & Heart Rate Monitor IP68',
    price: '₱1,299',
    discountPercent: 35,
    badge: 'Official',
    rating: 4.9,
    soldCount: '3.4k sold',
    hasFreeShipping: true,
  ),
  DiscoverProduct(
    name: 'Minimalist Matte Chronograph Watch Waterproof Luxury Unisex',
    price: '₱459',
    discountPercent: 40,
    badge: 'Preferred',
    rating: 4.8,
    soldCount: '1.8k sold',
    hasFreeShipping: true,
  ),
  DiscoverProduct(
    name: 'ANC Pro Wireless Noise Cancelling Earphones Deep Bass Bluetooth 5.3',
    price: '₱890',
    discountPercent: 52,
    badge: 'Official',
    rating: 5.0,
    soldCount: '8.9k sold',
    hasFreeShipping: true,
  ),
  DiscoverProduct(
    name: 'Retro Colorblock Sneaker Lightweight Running Walking Breathable Shoes',
    price: '₱650',
    discountPercent: 0,
    badge: 'Preferred',
    rating: 4.7,
    soldCount: '920 sold',
    hasFreeShipping: false,
  ),
  DiscoverProduct(
    name: 'Ceramic Aesthetic Coffee Mug & Saucer Set Luxury Minimalist Cup',
    price: '₱280',
    discountPercent: 20,
    badge: 'Official',
    rating: 4.9,
    soldCount: '540 sold',
    hasFreeShipping: false,
  ),
  DiscoverProduct(
    name: 'Instant Print Camera Retro Vintage Pocket Edition',
    price: '₱3,499',
    discountPercent: 30,
    badge: 'Preferred',
    rating: 4.8,
    soldCount: '2.1k sold',
    hasFreeShipping: true,
  ),
];

class DiscoverProductCard extends StatelessWidget {
  final DiscoverProduct product;
  final VoidCallback onTap;
  final VoidCallback? onAddToCart;

  const DiscoverProductCard({
    super.key,
    required this.product,
    required this.onTap,
    this.onAddToCart,
  });

  @override
  Widget build(BuildContext context) {
    return ProductCard(
      name: product.name,
      price: product.price,
      discountPercent: product.discountPercent > 0 ? product.discountPercent : null,
      badge: product.badge,
      rating: product.rating,
      soldCount: product.soldCount,
      hasFreeShipping: product.hasFreeShipping,
      imageUrl: (product.imageUrl != null && product.imageUrl!.isNotEmpty)
          ? product.imageUrl
          : CartzyProductImages.getImage(product.name),
      onTap: onTap,
      onAddToCart: onAddToCart,
    );
  }
}

class DailyDiscoverSection extends StatelessWidget {
  final VoidCallback onBrowseCategories;
  final void Function(DiscoverProduct product) onProductTap;
  final void Function(DiscoverProduct product)? onAddToCart;

  const DailyDiscoverSection({
    super.key,
    required this.onBrowseCategories,
    required this.onProductTap,
    this.onAddToCart,
  });

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
                          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3.5),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFBF4F1),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: const Color(0xFFECDED9), width: 1),
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
                    onTap: onBrowseCategories,
                    borderRadius: BorderRadius.circular(8),
                    child: Container(
                      padding: EdgeInsets.symmetric(
                        horizontal: isNarrow ? 10 : 14,
                        vertical: isNarrow ? 6 : 8,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: const Color(0xFFECDED9), width: 1.2),
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
                          const Icon(Icons.arrow_forward, size: 12, color: Color(0xFF5A4449)),
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
          height: 340,
          child: ListView.separated(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            scrollDirection: Axis.horizontal,
            itemCount: discoverProducts.length,
            separatorBuilder: (_, __) => const SizedBox(width: 14),
            itemBuilder: (context, index) {
              final p = discoverProducts[index];
              return SizedBox(
                width: 205,
                child: DiscoverProductCard(
                  product: p,
                  onTap: () => onProductTap(p),
                  onAddToCart: () => onAddToCart?.call(p),
                ),
              );
            },
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

  const SectionTitle({super.key, required this.title, this.actionText, this.onAction});

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
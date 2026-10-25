import 'package:flutter/material.dart';
import 'package:cartzy/products/product_list_screen.dart';

import 'api_service.dart';

/// Products and categories from the website's database, via the Laravel API.
class CatalogService {
  /// Catalog responses are reused for a short while: the home screen asks for
  /// the same product list from several sections, and going back to a screen
  /// shouldn't wait on the server again. Pull-to-refresh calls [clearCache].
  static const _cacheFor = Duration(minutes: 2);
  static final _cache = <String, (DateTime, Future<Map<String, dynamic>>)>{};

  static void clearCache() => _cache.clear();

  static Future<Map<String, dynamic>> _get(String endpoint) {
    final hit = _cache[endpoint];
    if (hit != null && DateTime.now().difference(hit.$1) < _cacheFor) {
      return hit.$2;
    }
    final request = ApiService.get(endpoint);
    _cache[endpoint] = (DateTime.now(), request);
    // Don't keep failures; the next caller should try again.
    request.then((_) {}, onError: (_) {
      if (identical(_cache[endpoint]?.$2, request)) _cache.remove(endpoint);
    });
    return request;
  }

  /// Formats a peso amount the way the app shows prices, e.g. 1299 -> "₱1,299".
  static String peso(num value) {
    final whole = value.round().toString();
    final grouped = whole.replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (_) => ',');
    return '₱$grouped';
  }

  static String _sold(int sold) =>
      sold >= 1000 ? '${(sold / 1000).toStringAsFixed(1)}k sold' : '$sold sold';

  static ProductItem productFromJson(Map<String, dynamic> json) {
    final price = (json['price'] as num?) ?? 0;
    final regular = (json['regular_price'] as num?) ?? price;
    final discount = regular > price && regular > 0 ? ((1 - price / regular) * 100).round() : 0;
    final seller = (json['seller'] as Map?)?['name']?.toString() ?? 'Official';
    final inStock = (json['in_stock'] as bool?) ?? true;
    final rawBadge = json['badge']?.toString().trim();
    final badge = (rawBadge != null && rawBadge.isNotEmpty) ? rawBadge : '';

    return ProductItem(
      id: json['id'] as int?,
      name: json['name']?.toString() ?? '',
      category: (json['category'] as Map?)?['name']?.toString() ?? 'General',
      price: peso(price),
      originalPrice: discount > 0 ? peso(regular) : null,
      discountPercent: discount,
      badge: badge,
      rating: (json['rating'] as num?)?.toDouble() ?? 4.8,
      soldCount: _sold((json['sold'] as num?)?.toInt() ?? 0),
      imageUrl: json['image']?.toString(),
      sellerName: seller,
      inStock: inStock,
      stock: (json['stock'] as num?)?.toInt(),
      hasFreeShipping: true,
    );
  }

  /// [category] is a category name such as "Shoes"; null or "All" returns every product.
  static Future<List<ProductItem>> fetchProducts({
    String? category,
    String? query,
    int perPage = 50,
    bool fetchAll = false,
  }) async {
    final params = <String, String>{
      'per_page': '$perPage',
      if (category != null && category != 'All' && category != 'Flash Deals') 'category': category,
      if (query != null && query.trim().isNotEmpty) 'q': query.trim(),
    };
    final response = await _get('/products?${Uri(queryParameters: params).query}');
    final items = (response['products'] as List? ?? const [])
        .map((item) => productFromJson(Map<String, dynamic>.from(item as Map)))
        .toList();

    if (fetchAll && response['meta'] is Map) {
      final lastPage = (response['meta']['last_page'] as num?)?.toInt() ?? 1;
      for (int page = 2; page <= lastPage; page++) {
        final pageParams = Map<String, String>.from(params)..['page'] = '$page';
        try {
          final nextRes = await _get('/products?${Uri(queryParameters: pageParams).query}');
          final nextItems = (nextRes['products'] as List? ?? const [])
              .map((item) => productFromJson(Map<String, dynamic>.from(item as Map)));
          items.addAll(nextItems);
        } catch (_) {}
      }
    }

    return category == 'Flash Deals' ? items.where((p) => p.discountPercent > 0).toList() : items;
  }

  static Future<List<String>> fetchCategoryNames() async {
    final response = await _get('/categories');
    return (response['categories'] as List? ?? const [])
        .map((category) => (category as Map)['name'].toString())
        .toList();
  }

  static Future<List<HeroSlideItem>> fetchHeroSlides() async {
    try {
      final response = await _get('/hero-slides');
      final list = (response['slides'] as List? ?? const [])
          .map((item) => HeroSlideItem.fromJson(Map<String, dynamic>.from(item as Map)))
          .toList();
      if (list.isNotEmpty) return list;
    } catch (_) {}
    return defaultHeroSlides;
  }

  static Future<Map<String, dynamic>?> fetchProductDetails(int id) async {
    try {
      final response = await _get('/products/$id');
      if (response['product'] is Map) {
        return Map<String, dynamic>.from(response['product'] as Map);
      }
    } catch (_) {}
    return null;
  }
}

class HeroSlideItem {
  final int? id;
  final String key;
  final String label;
  final String title;
  final String accent;
  final String description;
  final String cta;
  final String href;
  final String alt;
  final String? imageUrl;
  final String? imagePath;
  final int position;

  const HeroSlideItem({
    this.id,
    required this.key,
    required this.label,
    required this.title,
    required this.accent,
    required this.description,
    required this.cta,
    required this.href,
    this.alt = '',
    this.imageUrl,
    this.imagePath,
    this.position = 0,
  });

  Color get accentColor {
    switch (key.toLowerCase()) {
      case 'halloween':
        return const Color(0xFFD35400);
      case 'tech':
        return const Color(0xFF2E6171);
      case 'beauty':
        return const Color(0xFFB5628F);
      case 'jewelry':
        return const Color(0xFFB8860B);
      case 'coffee':
        return const Color(0xFF6B4226);
      case 'travel':
        return const Color(0xFF8B6F5C);
      case 'fitness':
        return const Color(0xFF5B7F5E);
      case 'style':
      case 'home':
      default:
        return const Color(0xFF8B4513);
    }
  }

  String get targetCategory {
    final lowerHref = href.toLowerCase();
    if (lowerHref.contains('electronics')) return 'Electronics';
    if (lowerHref.contains('shoes')) return 'Shoes';
    if (lowerHref.contains('beauty-health') || lowerHref.contains('beauty')) return 'Beauty & Health';
    if (lowerHref.contains('jewelry-accessories') || lowerHref.contains('jewelry')) return 'Jewelry & Accessories';
    if (lowerHref.contains('appliances')) return 'Appliances';
    if (lowerHref.contains('bags-luggage') || lowerHref.contains('bags')) return 'Bags & Luggage';
    if (lowerHref.contains('sports-outdoors') || lowerHref.contains('fitness')) return 'Sports & Outdoors';
    if (key == 'home') return 'Home & Living';
    if (key == 'halloween') return 'General';
    return label;
  }

  String get localAssetPath {
    if (imagePath != null && imagePath!.isNotEmpty) {
      final filename = imagePath!.split('/').last;
      return 'assets/$filename';
    }
    return 'assets/cartzy-$key.jpg';
  }

  factory HeroSlideItem.fromJson(Map<String, dynamic> json) {
    return HeroSlideItem(
      id: json['id'] as int?,
      key: json['key']?.toString() ?? '',
      label: json['label']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      accent: json['accent']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      cta: json['cta']?.toString() ?? 'EXPLORE',
      href: json['href']?.toString() ?? '',
      alt: json['alt']?.toString() ?? '',
      imageUrl: json['image_url']?.toString() ?? json['image']?.toString(),
      imagePath: json['image_path']?.toString(),
      position: (json['position'] as num?)?.toInt() ?? 0,
    );
  }
}

const List<HeroSlideItem> defaultHeroSlides = [
  HeroSlideItem(
    key: 'halloween',
    label: 'Halloween picks',
    title: 'Spooky season.',
    accent: 'Fang-tastic finds.',
    description: 'A little spooky, a lot to love. Bring home the Halloween spirit with festive touches and everyday favorites from Cartzy.',
    cta: 'Explore the marketplace',
    href: '#daily-discover-section',
    alt: 'Glowing Halloween pumpkins with a witch hat, a smiling ceramic ghost, candies, and autumn leaves',
    imagePath: 'images/banners/cartzy-halloween-web.png',
  ),
  HeroSlideItem(
    key: 'home',
    label: 'Home & living',
    title: 'Good finds.',
    accent: 'Great everyday living.',
    description: 'Make room for the things you love. Discover home essentials, fresh styles, and everyday favorites, all on Cartzy.',
    cta: 'Explore the marketplace',
    href: '#daily-discover-section',
    alt: 'An oak lounge chair with a soft ivory cushion beside a walnut table and ceramic vase',
    imagePath: 'images/banners/cartzy-home.jpg',
  ),
  HeroSlideItem(
    key: 'tech',
    label: 'Tech & audio',
    title: 'Tune into more.',
    accent: 'Make it your moment.',
    description: 'From your morning playlist to your evening wind-down, find tech that fits the way you live.',
    cta: 'Discover tech & audio',
    href: '/category/electronics',
    alt: 'Charcoal wireless headphones and lavender earbuds on a pale stone display',
    imagePath: 'images/banners/cartzy-tech.jpg',
  ),
  HeroSlideItem(
    key: 'style',
    label: 'Everyday style',
    title: 'Go your own way.',
    accent: 'Find your everyday fit.',
    description: 'Easy layers, fresh kicks, and finishing touches. Meet the pieces that make every day feel a little more you.',
    cta: 'Find your next pair',
    href: '/category/shoes',
    alt: 'Cream sneakers, a folded beige shirt, and a muted lavender crossbody bag',
    imagePath: 'images/banners/cartzy-style.jpg',
  ),
  HeroSlideItem(
    key: 'beauty',
    label: 'Beauty & self-care',
    title: 'A little me-time.',
    accent: 'A softer kind of glow.',
    description: 'Slow down and make space for yourself. Discover beauty essentials for your everyday rituals.',
    cta: 'Explore beauty',
    href: '/category/beauty-health',
    alt: 'Blush skincare bottles with rose-gold details and a lavender cosmetic pouch',
    imagePath: 'images/banners/cartzy-beauty.jpg',
  ),
  HeroSlideItem(
    key: 'jewelry',
    label: 'Jewelry & accessories',
    title: 'Little details.',
    accent: 'Your kind of sparkle.',
    description: 'The finishing touches that feel like you. Find jewelry and accessories for everyday moments.',
    cta: 'Discover accessories',
    href: '/category/jewelry-accessories',
    alt: 'Rose-gold hoops, a delicate necklace, and a watch on an ivory and blush display',
    imagePath: 'images/banners/cartzy-jewelry.jpg',
  ),
  HeroSlideItem(
    key: 'coffee',
    label: 'Coffee & home',
    title: 'Stay a little longer.',
    accent: 'Savor the everyday.',
    description: 'From your first cup to your favorite corner, discover thoughtful upgrades for a home you love.',
    cta: 'Shop home appliances',
    href: '/category/appliances',
    alt: 'An ivory espresso machine with rose-gold details beside a ceramic cappuccino cup',
    imagePath: 'images/banners/cartzy-coffee.jpg',
  ),
  HeroSlideItem(
    key: 'travel',
    label: 'Bags & travel',
    title: 'Pack a little joy.',
    accent: 'Go somewhere new.',
    description: 'Weekend escapes or everyday adventures. Find bags and travel essentials ready to go with you.',
    cta: 'Explore bags & travel',
    href: '/category/bags-luggage',
    alt: 'A blush cabin suitcase, taupe weekender bag, and ivory travel organizer',
    imagePath: 'images/banners/cartzy-travel.jpg',
  ),
  HeroSlideItem(
    key: 'fitness',
    label: 'Movement & wellness',
    title: 'Move at your pace.',
    accent: 'Feel a little better.',
    description: 'A morning stretch, a fresh start, a moment for you. Find essentials for the way you like to move.',
    cta: 'Find your fitness essentials',
    href: '/category/sports-outdoors',
    alt: 'A lavender yoga mat, blush dumbbells, and an ivory water bottle with a rose-gold cap',
    imagePath: 'images/banners/cartzy-fitness.jpg',
  ),
];

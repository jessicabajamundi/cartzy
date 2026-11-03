import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// A product the buyer opened, with enough details to show it again
/// and to reopen its details page.
class RecentlyViewedProduct {
  final int? productId;
  final String name;
  final String category;
  final String price;
  final String? originalPrice;
  final String? imageUrl;
  final String? sellerName;
  final String? badge;
  final double? rating;
  final String? soldCount;
  final DateTime viewedAt;

  const RecentlyViewedProduct({
    required this.productId,
    required this.name,
    required this.category,
    required this.price,
    required this.viewedAt,
    this.originalPrice,
    this.imageUrl,
    this.sellerName,
    this.badge,
    this.rating,
    this.soldCount,
  });

  /// Same product = same id, or same name when there is no id.
  bool isSameAs(RecentlyViewedProduct other) => productId != null
      ? productId == other.productId
      : name.trim().toLowerCase() == other.name.trim().toLowerCase();

  Map<String, dynamic> toJson() => {
        'productId': productId,
        'name': name,
        'category': category,
        'price': price,
        'originalPrice': originalPrice,
        'imageUrl': imageUrl,
        'sellerName': sellerName,
        'badge': badge,
        'rating': rating,
        'soldCount': soldCount,
        'viewedAt': viewedAt.toIso8601String(),
      };

  factory RecentlyViewedProduct.fromJson(Map<String, dynamic> json) =>
      RecentlyViewedProduct(
        productId: (json['productId'] as num?)?.toInt(),
        name: json['name']?.toString() ?? '',
        category: json['category']?.toString() ?? '',
        price: json['price']?.toString() ?? '0',
        originalPrice: json['originalPrice']?.toString(),
        imageUrl: json['imageUrl']?.toString(),
        sellerName: json['sellerName']?.toString(),
        badge: json['badge']?.toString(),
        rating: (json['rating'] as num?)?.toDouble(),
        soldCount: json['soldCount']?.toString(),
        viewedAt: DateTime.tryParse(json['viewedAt']?.toString() ?? '') ??
            DateTime.now(),
      );
}

/// Keeps the last products the buyer opened, newest first, on the device.
class RecentlyViewedService {
  static const _storageKey = 'cartzy_recently_viewed';
  static const maxItems = 30;

  static final ValueNotifier<List<RecentlyViewedProduct>> items =
      ValueNotifier(const []);

  static bool _loaded = false;

  static Future<void> load() async {
    if (_loaded) return;
    try {
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString(_storageKey);
      if (raw != null) {
        final saved = (jsonDecode(raw) as List)
            .map((e) => RecentlyViewedProduct.fromJson(
                Map<String, dynamic>.from(e as Map)))
            .where((p) => p.name.isNotEmpty)
            .toList();
        // Keep anything recorded while the saved list was still loading.
        final merged = [...items.value];
        for (final p in saved) {
          if (!merged.any((m) => m.isSameAs(p))) merged.add(p);
        }
        items.value = merged.take(maxItems).toList();
      }
    } catch (e) {
      debugPrint('Could not load recently viewed: $e');
    }
    _loaded = true;
  }

  static Future<void> record(RecentlyViewedProduct product) async {
    if (product.name.trim().isEmpty) return;
    await load();
    final next = [
      product,
      ...items.value.where((p) => !p.isSameAs(product)),
    ].take(maxItems).toList();
    items.value = next;
    await _save();
  }

  static Future<void> remove(RecentlyViewedProduct product) async {
    items.value = items.value.where((p) => !p.isSameAs(product)).toList();
    await _save();
  }

  /// Puts back an earlier list exactly as it was (used by "Undo").
  static Future<void> restore(List<RecentlyViewedProduct> list) async {
    items.value = List.unmodifiable(list);
    await _save();
  }

  static Future<void> clear() async {
    items.value = const [];
    await _save();
  }

  static Future<void> _save() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(
        _storageKey,
        jsonEncode(items.value.map((p) => p.toJson()).toList()),
      );
    } catch (e) {
      debugPrint('Could not save recently viewed: $e');
    }
  }
}

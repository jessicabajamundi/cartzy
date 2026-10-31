import 'package:flutter/foundation.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';

class WishlistItem {
  final int id;
  final String ref;
  final int? productId;
  final String name;
  final double price;
  final String image;
  final String variation;
  final bool inStock;
  final double rating;
  final int soldCount;
  final String sellerName;
  final DateTime? createdAt;

  const WishlistItem({
    required this.id,
    required this.ref,
    this.productId,
    required this.name,
    required this.price,
    required this.image,
    required this.variation,
    required this.inStock,
    required this.rating,
    required this.soldCount,
    required this.sellerName,
    this.createdAt,
  });

  factory WishlistItem.fromJson(Map<String, dynamic> json) {
    final rawPrice = json['price'];
    double parsedPrice = 0.0;
    if (rawPrice is num) {
      parsedPrice = rawPrice.toDouble();
    } else if (rawPrice is String) {
      parsedPrice = double.tryParse(rawPrice.replaceAll(RegExp(r'[^0-9.]'), '')) ?? 0.0;
    }

    final rawRating = json['rating'];
    double parsedRating = 5.0;
    if (rawRating is num) {
      parsedRating = rawRating.toDouble();
    }

    final rawSold = json['sold_count'];
    int parsedSold = 0;
    if (rawSold is num) {
      parsedSold = rawSold.toInt();
    }

    return WishlistItem(
      id: json['id'] as int? ?? 0,
      ref: json['ref']?.toString() ?? '',
      productId: json['product_id'] as int?,
      name: json['name']?.toString() ?? '',
      price: parsedPrice,
      image: json['image']?.toString() ?? '',
      variation: json['variation']?.toString() ?? 'Standard',
      inStock: json['in_stock'] == true,
      rating: parsedRating,
      soldCount: parsedSold,
      sellerName: json['seller_name']?.toString() ?? 'Cartzy Seller',
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
    );
  }
}

class WishlistService {
  /// Global set of wishlisted item references (e.g. product IDs) for instant O(1) checks.
  static final ValueNotifier<Set<String>> wishlistRefsNotifier =
      ValueNotifier<Set<String>>({});

  /// Global item count notifier for badges.
  static final ValueNotifier<int> countNotifier = ValueNotifier<int>(0);

  static bool _hasLoadedRefs = false;

  /// Check whether a reference (usually product ID as string) is wishlisted.
  static bool isWishlisted(String? ref) {
    if (ref == null || ref.isEmpty) return false;
    return wishlistRefsNotifier.value.contains(ref);
  }

  /// Initial or background fetch of all wishlisted reference IDs.
  static Future<Set<String>> fetchWishlistRefs({bool force = false}) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) {
      wishlistRefsNotifier.value = {};
      countNotifier.value = 0;
      return {};
    }

    if (_hasLoadedRefs && !force) {
      return wishlistRefsNotifier.value;
    }

    try {
      final res = await ApiService.get('/wishlist/refs', token: token);
      final rawRefs = res['refs'] as List? ?? [];
      final refs = rawRefs.map((r) => r.toString()).toSet();
      wishlistRefsNotifier.value = refs;
      countNotifier.value = res['count'] as int? ?? refs.length;
      _hasLoadedRefs = true;
      return refs;
    } catch (e) {
      debugPrint('[WishlistService] fetchWishlistRefs error: $e');
      return wishlistRefsNotifier.value;
    }
  }

  /// Fetch full list of wishlist items for the authenticated user.
  static Future<List<WishlistItem>> fetchWishlist() async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) {
      wishlistRefsNotifier.value = {};
      countNotifier.value = 0;
      return [];
    }

    try {
      final res = await ApiService.get('/wishlist', token: token);
      final rawItems = res['items'] as List? ?? [];
      final items = rawItems
          .whereType<Map<String, dynamic>>()
          .map((json) => WishlistItem.fromJson(json))
          .toList();

      final rawRefs = res['refs'] as List? ?? [];
      final refs = rawRefs.map((r) => r.toString()).toSet();
      wishlistRefsNotifier.value = refs;
      countNotifier.value = items.length;
      _hasLoadedRefs = true;
      return items;
    } catch (e) {
      debugPrint('[WishlistService] fetchWishlist error: $e');
      rethrow;
    }
  }

  /// Toggle item wishlist state in the database.
  /// Returns `true` if added, `false` if removed.
  static Future<bool> toggleWishlist({
    required String ref,
    required String name,
    required double price,
    String? image,
    String? variation,
  }) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) {
      throw Exception('Please sign in to save items to your wishlist.');
    }

    try {
      final res = await ApiService.post(
        '/wishlist/toggle',
        {
          'ref': ref,
          'name': name,
          'price': price,
          'image': image ?? '',
          'variation': variation ?? 'Standard',
        },
        token: token,
      );

      final isWished = res['wished'] == true;
      final rawRefs = res['refs'] as List? ?? [];
      final refs = rawRefs.map((r) => r.toString()).toSet();

      wishlistRefsNotifier.value = refs;
      countNotifier.value = res['count'] as int? ?? refs.length;

      return isWished;
    } catch (e) {
      debugPrint('[WishlistService] toggleWishlist error: $e');
      rethrow;
    }
  }

  /// Remove item from database by reference.
  static Future<void> removeFromWishlist(String ref) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) return;

    try {
      final res = await ApiService.delete('/wishlist/$ref', token: token);
      final rawRefs = res['refs'] as List? ?? [];
      final refs = rawRefs.map((r) => r.toString()).toSet();

      wishlistRefsNotifier.value = refs;
      countNotifier.value = res['count'] as int? ?? refs.length;
    } catch (e) {
      debugPrint('[WishlistService] removeFromWishlist error: $e');
      rethrow;
    }
  }

  /// Clear local state on logout.
  static void clear() {
    wishlistRefsNotifier.value = {};
    countNotifier.value = 0;
    _hasLoadedRefs = false;
  }
}

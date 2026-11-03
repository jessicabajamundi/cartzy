import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';

class FollowedStore {
  final String id;
  final String name;
  final String slug;
  final String? logo;
  final bool isOfficial;
  final String location;
  final double rating;
  final int followersCount;
  final int productsCount;
  final DateTime followedAt;

  const FollowedStore({
    required this.id,
    required this.name,
    required this.slug,
    this.logo,
    this.isOfficial = true,
    this.location = 'Philippines',
    this.rating = 4.8,
    this.followersCount = 120,
    this.productsCount = 12,
    required this.followedAt,
  });

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'slug': slug,
        'logo': logo,
        'is_official': isOfficial,
        'location': location,
        'rating': rating,
        'followers_count': followersCount,
        'products_count': productsCount,
        'followed_at': followedAt.toIso8601String(),
      };

  factory FollowedStore.fromJson(Map<String, dynamic> json) => FollowedStore(
        id: json['id']?.toString() ?? '',
        name: json['name']?.toString() ?? 'Store',
        slug: json['slug']?.toString() ?? '',
        logo: json['logo']?.toString(),
        isOfficial: json['is_official'] as bool? ?? true,
        location: json['location']?.toString() ?? 'Philippines',
        rating: (json['rating'] as num?)?.toDouble() ?? 4.8,
        followersCount: (json['followers_count'] as num?)?.toInt() ?? 120,
        productsCount: (json['products_count'] as num?)?.toInt() ?? 12,
        followedAt: DateTime.tryParse(json['followed_at']?.toString() ?? '') ??
            DateTime.now(),
      );
}

class FollowStoreService extends ChangeNotifier {
  static final FollowStoreService instance = FollowStoreService._();
  FollowStoreService._() {
    load();
  }

  final List<FollowedStore> _stores = [];

  List<FollowedStore> get followedStores => List.unmodifiable(_stores);
  int get count => _stores.length;

  bool isFollowing(String identifier) {
    if (identifier.trim().isEmpty) return false;
    final lower = identifier.trim().toLowerCase();
    return _stores.any((s) =>
        s.id.toLowerCase() == lower ||
        s.slug.toLowerCase() == lower ||
        s.name.toLowerCase() == lower);
  }

  String _prefsKey() {
    final uid = AuthService.currentUser?['id']?.toString() ?? 'guest';
    return 'cartzy_followed_stores_$uid';
  }

  Future<void> load() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final key = _prefsKey();
      final raw = prefs.getString(key);
      if (raw != null && raw.isNotEmpty) {
        final List decoded = jsonDecode(raw) as List;
        _stores.clear();
        for (final item in decoded) {
          if (item is Map) {
            _stores.add(
                FollowedStore.fromJson(Map<String, dynamic>.from(item)));
          }
        }
        notifyListeners();
      }
    } catch (_) {}
  }

  Future<void> followStore(FollowedStore store) async {
    final key = store.slug.isNotEmpty ? store.slug : store.name;
    if (isFollowing(key)) return;
    _stores.insert(0, store);
    await _persist();
    notifyListeners();

    // Sync to remote API in background
    _syncRemoteFollow(store.slug.isNotEmpty ? store.slug : store.id, true);
  }

  Future<void> unfollowStore(String identifier) async {
    final lower = identifier.trim().toLowerCase();
    _stores.removeWhere((s) =>
        s.id.toLowerCase() == lower ||
        s.slug.toLowerCase() == lower ||
        s.name.toLowerCase() == lower);
    await _persist();
    notifyListeners();

    // Sync to remote API in background
    _syncRemoteFollow(identifier, false);
  }

  Future<bool> toggleFollow(FollowedStore store) async {
    final key = store.slug.isNotEmpty ? store.slug : store.name;
    if (isFollowing(key)) {
      await unfollowStore(key);
      return false;
    } else {
      await followStore(store);
      return true;
    }
  }

  Future<void> _persist() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final key = _prefsKey();
      final encoded = jsonEncode(_stores.map((s) => s.toJson()).toList());
      await prefs.setString(key, encoded);
    } catch (_) {}
  }

  Future<void> _syncRemoteFollow(String slug, bool follow) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty || slug.isEmpty) return;
    try {
      await ApiService.post(
        '/stores/$slug/follow',
        {},
        token: token,
      );
    } catch (_) {}
  }
}

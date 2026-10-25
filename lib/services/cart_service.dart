import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:cartzy/utils/product_images.dart';

class CartItem {
  final String id;
  final String name;
  final double price;
  final String? originalPrice;
  final String? image;
  final String? category;
  final String? variation;
  int quantity;
  bool selected;

  CartItem({
    required this.id,
    required this.name,
    required this.price,
    this.originalPrice,
    this.image = '',
    this.category = '',
    this.variation = '',
    this.quantity = 1,
    this.selected = true,
  });

  String get safeName => name.isNotEmpty ? name : 'Product';
  String get safeImage => image ?? '';
  String get safeCategory => category ?? '';
  String get safeVariation {
    if (variation != null && variation!.isNotEmpty) return variation!;
    if (category != null && category!.isNotEmpty) return category!;
    return 'Standard';
  }

  double get subtotal =>
      (price.isNaN || price.isInfinite ? 0.0 : price) *
      (quantity <= 0 ? 1 : quantity);

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'price': price,
        'originalPrice': originalPrice,
        'image': image,
        'category': category,
        'variation': variation,
        'quantity': quantity,
        'selected': selected,
      };

  factory CartItem.fromJson(Map<String, dynamic> json) => CartItem(
        id: json['id']?.toString() ?? '',
        name: json['name']?.toString() ?? '',
        price: (json['price'] as num?)?.toDouble() ?? 0.0,
        originalPrice: json['originalPrice']?.toString(),
        image: json['image']?.toString() ?? '',
        category: json['category']?.toString() ?? '',
        variation: json['variation']?.toString() ?? '',
        quantity: (json['quantity'] as num?)?.toInt() ?? 1,
        selected: json['selected'] as bool? ?? true,
      );
}

class CartService extends ChangeNotifier {
  static final CartService instance = CartService._internal();

  CartService._internal() {
    _restore();
  }

  final List<CartItem> _items = [];

  /// The cart is kept on the device so it survives app restarts.
  static const _storageKey = 'cart_items';
  bool _restored = false;

  Future<void> _restore() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString(_storageKey);
      if (raw != null) {
        final saved = (jsonDecode(raw) as List)
            .map((e) => CartItem.fromJson(Map<String, dynamic>.from(e as Map)));
        // Keep anything added while the saved cart was still loading.
        _items.insertAll(0, saved);
      }
    } catch (e) {
      debugPrint('Could not restore cart: $e');
    }
    _restored = true;
    notifyListeners();
  }

  Future<void> _save() async {
    // Saving before the restore finishes would overwrite the saved cart.
    if (!_restored) return;
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(
          _storageKey, jsonEncode(_items.map((i) => i.toJson()).toList()));
    } catch (e) {
      debugPrint('Could not save cart: $e');
    }
  }

  /// Every cart change goes through here, so this is where it gets saved.
  @override
  void notifyListeners() {
    super.notifyListeners();
    _save();
  }

  List<CartItem> get items => List.unmodifiable(_items);

  List<CartItem> get selectedItems =>
      items.where((item) => item.selected == true).toList();

  int get totalItemCount =>
      items.fold<int>(0, (sum, item) => sum + item.quantity);

  int get selectedItemCount =>
      selectedItems.fold<int>(0, (sum, item) => sum + item.quantity);

  double get selectedTotal =>
      selectedItems.fold<double>(0.0, (sum, item) => sum + item.subtotal);

  bool get isAllSelected =>
      _items.isNotEmpty && _items.every((item) => item.selected);

  bool? get selectAllState {
    if (_items.isEmpty) return false;
    final selectedCount = _items.where((i) => i.selected).length;
    if (selectedCount == 0) return false;
    if (selectedCount == _items.length) return true;
    return null; // Indeterminate (dash)
  }

  static double parsePrice(dynamic price) {
    if (price == null) return 0.0;
    if (price is num) return price.toDouble();
    if (price is String) {
      final cleaned = price.replaceAll(RegExp(r'[^0-9.]'), '');
      return double.tryParse(cleaned) ?? 0.0;
    }
    return 0.0;
  }

  static String formatPrice(double price) {
    final fixed = price.toStringAsFixed(2);
    final parts = fixed.split('.');
    final intPart = parts[0].replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]},',
    );
    return '₱$intPart.${parts[1]}';
  }

  void addItem({
    required String name,
    required dynamic price,
    String? originalPrice,
    String image = '',
    String category = '',
    String variation = '',
    int quantity = 1,
  }) {
    final parsedPrice = parsePrice(price);
    final effectiveImage = image.isNotEmpty
        ? image
        : CartzyProductImages.getImage(name, category);

    final existingIndex = _items.indexWhere(
      (item) => item.name.trim().toLowerCase() == name.trim().toLowerCase(),
    );

    if (existingIndex >= 0) {
      _items[existingIndex].quantity += quantity;
    } else {
      _items.add(
        CartItem(
          id: DateTime.now().millisecondsSinceEpoch.toString(),
          name: name,
          price: parsedPrice,
          originalPrice: originalPrice,
          image: effectiveImage,
          category: category,
          variation: variation,
          quantity: quantity,
          selected: true,
        ),
      );
    }
    notifyListeners();
  }

  void removeItem(int index) {
    if (index >= 0 && index < _items.length) {
      _items.removeAt(index);
      notifyListeners();
    }
  }

  void removeItemById(String id) {
    _items.removeWhere((item) => item.id == id);
    notifyListeners();
  }

  void increaseQuantity(int index) {
    if (index >= 0 && index < _items.length) {
      _items[index].quantity++;
      notifyListeners();
    }
  }

  void decreaseQuantity(int index) {
    if (index >= 0 && index < _items.length) {
      if (_items[index].quantity > 1) {
        _items[index].quantity--;
      } else {
        _items.removeAt(index);
      }
      notifyListeners();
    }
  }

  void toggleItemSelection(int index, bool? selected) {
    if (index >= 0 && index < _items.length) {
      _items[index].selected = selected ?? false;
      notifyListeners();
    }
  }

  void toggleSelectAll(bool? selectAll) {
    final target = selectAll ?? false;
    for (final item in _items) {
      item.selected = target;
    }
    notifyListeners();
  }

  void clearSelected() {
    _items.removeWhere((item) => item.selected);
    notifyListeners();
  }

  void clearAll() {
    _items.clear();
    notifyListeners();
  }
}

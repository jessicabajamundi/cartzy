import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/catalog_service.dart';
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

  /// Database ids, used to look up the real stock.
  int? productId;
  int? variantId;

  /// Units available in the database for this item's variant.
  /// null = not checked yet, 0 = out of stock.
  int? stock;

  /// Store / Seller name
  final String? store;

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
    this.productId,
    this.variantId,
    this.stock,
    this.store,
  });

  String get safeName => name.isNotEmpty ? name : 'Product';
  String get safeImage => image ?? '';
  String get safeCategory => category ?? '';
  String get safeVariation {
    if (variation != null && variation!.isNotEmpty) return variation!;
    if (category != null && category!.isNotEmpty) return category!;
    return 'Standard';
  }

  bool get isOutOfStock => stock != null && stock! <= 0;

  /// True when the quantity already equals the available stock.
  bool get atStockLimit => stock != null && quantity >= stock!;

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
        'productId': productId,
        'variantId': variantId,
        'stock': stock,
        'store': store,
      };

  factory CartItem.fromJson(Map<String, dynamic> json) => CartItem(
        id: json['cart_key']?.toString() ?? (json['id']?.toString() ?? ''),
        name: json['name']?.toString() ?? '',
        price: (json['price'] as num?)?.toDouble() ?? 0.0,
        originalPrice: json['originalPrice']?.toString() ?? json['original_price']?.toString(),
        image: json['image']?.toString() ?? '',
        category: json['category']?.toString() ?? '',
        variation: json['variation']?.toString() ?? '',
        quantity: (json['quantity'] as num?)?.toInt() ?? 1,
        selected: json['selected'] as bool? ?? true,
        productId: (json['productId'] as num?)?.toInt() ?? (json['product_id'] as num?)?.toInt(),
        variantId: (json['variantId'] as num?)?.toInt() ?? (json['variant_id'] as num?)?.toInt(),
        stock: (json['stock'] as num?)?.toInt(),
        store: json['store']?.toString(),
      );
}

/// What happened when something was added to the cart.
class AddToCartResult {
  final int requested;
  final int added;

  /// Quantity of this item in the cart after adding.
  final int inCart;

  /// Stock available in the database, when known.
  final int? stock;

  const AddToCartResult({
    required this.requested,
    required this.added,
    required this.inCart,
    required this.stock,
  });

  /// Fewer were added than asked for, because of the stock limit.
  bool get limited => added < requested;

  /// A short message for a snackbar.
  String message(String name) {
    if (!limited) return '$name added to cart!';
    if (stock == 0) return 'Sorry, $name is out of stock.';
    if (added == 0) {
      return 'You already have all $inCart available of $name in your cart.';
    }
    return 'Only $added more added — $name has $stock in stock and your cart now has $inCart.';
  }
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

    if (AuthService.isLoggedIn) {
      fetchCartFromServer(force: true);
    }
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

  bool _isLoadingFromServer = false;
  bool get isLoadingFromServer => _isLoadingFromServer;

  /// Loads the saved cart from the Laravel API for the logged-in buyer (synced with web).
  Future<void> fetchCartFromServer({bool force = false}) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty || !AuthService.isLoggedIn) {
      return;
    }

    _isLoadingFromServer = true;
    try {
      final res = await ApiService.get('/cart', token: token);
      final rawList = res['items'] as List? ?? [];
      final serverItems = rawList
          .whereType<Map>()
          .map((m) => CartItem.fromJson(Map<String, dynamic>.from(m)))
          .toList();

      _items.clear();
      _items.addAll(serverItems);
      _restored = true;
      _save();
      super.notifyListeners();
    } catch (e) {
      debugPrint('[CartService] fetchCartFromServer error: $e');
    } finally {
      _isLoadingFromServer = false;
    }
  }

  /// Syncs any local offline cart items into the server cart.
  Future<void> syncLocalCartToServer() async {
    final token = AuthService.token;
    if (token == null || token.isEmpty || !AuthService.isLoggedIn || _items.isEmpty) {
      return;
    }
    try {
      final payload = {
        'items': _items.map((i) => i.toJson()).toList(),
      };
      final res = await ApiService.post('/cart/sync', payload, token: token);
      final rawList = res['items'] as List? ?? [];
      final serverItems = rawList
          .whereType<Map>()
          .map((m) => CartItem.fromJson(Map<String, dynamic>.from(m)))
          .toList();

      if (serverItems.isNotEmpty) {
        _items.clear();
        _items.addAll(serverItems);
        _restored = true;
        _save();
        super.notifyListeners();
      }
    } catch (e) {
      debugPrint('[CartService] syncLocalCartToServer error: $e');
    }
  }

  Future<void> _syncAddToServer({
    required String name,
    required double price,
    required String image,
    required String variation,
    required int quantity,
    int? productId,
    int? variantId,
  }) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty || !AuthService.isLoggedIn) return;
    try {
      final res = await ApiService.post('/cart/add', {
        'id': productId ?? variantId ?? DateTime.now().millisecondsSinceEpoch,
        'product_id': productId,
        'variant_id': variantId,
        'name': name,
        'price': price,
        'image': image,
        'variation': variation,
        'quantity': quantity,
      }, token: token);
      final rawList = res['items'] as List? ?? [];
      if (rawList.isNotEmpty) {
        final serverItems = rawList
            .whereType<Map>()
            .map((m) => CartItem.fromJson(Map<String, dynamic>.from(m)))
            .toList();
        _items.clear();
        _items.addAll(serverItems);
        _save();
        super.notifyListeners();
      }
    } catch (e) {
      debugPrint('[CartService] _syncAddToServer error: $e');
    }
  }

  Future<void> _syncQuantityToServer(CartItem item) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty || !AuthService.isLoggedIn) return;
    try {
      await ApiService.put('/cart/items/${Uri.encodeComponent(item.id)}', {
        'quantity': item.quantity,
      }, token: token);
    } catch (e) {
      debugPrint('[CartService] _syncQuantityToServer error: $e');
    }
  }

  Future<void> _syncRemoveFromServer(String id) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty || !AuthService.isLoggedIn) return;
    try {
      await ApiService.delete('/cart/items/${Uri.encodeComponent(id)}', token: token);
    } catch (e) {
      debugPrint('[CartService] _syncRemoveFromServer error: $e');
    }
  }

  Future<void> _syncSelectionToServer(CartItem item) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty || !AuthService.isLoggedIn) return;
    try {
      await ApiService.put('/cart/items/${Uri.encodeComponent(item.id)}/select', {
        'selected': item.selected,
      }, token: token);
    } catch (e) {
      debugPrint('[CartService] _syncSelectionToServer error: $e');
    }
  }

  Future<void> _syncSelectAllToServer() async {
    final token = AuthService.token;
    if (token == null || token.isEmpty || !AuthService.isLoggedIn) return;
    try {
      final selectedKeys = selectedItems.map((i) => i.id).toList();
      await ApiService.post('/cart/selection', {
        'selected_items': selectedKeys,
      }, token: token);
    } catch (e) {
      debugPrint('[CartService] _syncSelectAllToServer error: $e');
    }
  }

  Future<void> _syncClearServer() async {
    final token = AuthService.token;
    if (token == null || token.isEmpty || !AuthService.isLoggedIn) return;
    try {
      await ApiService.delete('/cart/clear', token: token);
    } catch (e) {
      debugPrint('[CartService] _syncClearServer error: $e');
    }
  }

  /// Items that will be checked out: ticked and not out of stock.
  List<CartItem> get selectedItems =>
      _items.where((item) => item.selected && !item.isOutOfStock).toList();

  /// Total number of distinct products in the cart (for badges and counters).
  int get totalItemCount => _items.length;

  /// Total number of selected products in the cart.
  int get selectedItemCount => selectedItems.length;

  /// Sum of all item quantities in the cart.
  int get totalUnits =>
      _items.fold<int>(0, (sum, item) => sum + item.quantity);

  /// Sum of quantities for selected items.
  int get selectedUnits =>
      selectedItems.fold<int>(0, (sum, item) => sum + item.quantity);

  double get selectedTotal =>
      selectedItems.fold<double>(0.0, (sum, item) => sum + item.subtotal);

  Iterable<CartItem> get _selectable => _items.where((i) => !i.isOutOfStock);

  bool get isAllSelected =>
      _selectable.isNotEmpty && _selectable.every((item) => item.selected);

  bool? get selectAllState {
    final selectable = _selectable.toList();
    if (selectable.isEmpty) return false;
    final selectedCount = selectable.where((i) => i.selected).length;
    if (selectedCount == 0) return false;
    if (selectedCount == selectable.length) return true;
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

  static String _norm(String? s) {
    final v = (s ?? '').trim().toLowerCase();
    return v == 'standard' ? '' : v;
  }

  /// Finds the cart line for a product/variant, or -1.
  int _indexOf({
    required String name,
    required String variation,
    int? productId,
    int? variantId,
  }) {
    // Exact variant.
    if (variantId != null) {
      final i = _items.indexWhere((item) => item.variantId == variantId);
      if (i >= 0) return i;
    }
    if (productId != null) {
      // Quick add from a product list (no option picked): add to the line
      // already in the cart for this product.
      if (variantId == null && _norm(variation).isEmpty) {
        final i = _items.indexWhere((item) => item.productId == productId);
        if (i >= 0) return i;
      }
      final i = _items.indexWhere((item) =>
          item.productId == productId &&
          (item.variantId == null || variantId == null) &&
          _norm(item.variation) == _norm(variation));
      if (i >= 0) return i;
    }
    // Older items saved without ids.
    return _items.indexWhere((item) =>
        item.productId == null &&
        _norm(item.name) == _norm(name) &&
        _norm(item.variation) == _norm(variation));
  }

  /// How many of this product/variant are already in the cart.
  int quantityInCart({int? productId, int? variantId, String? name, String variation = ''}) {
    final i = _indexOf(
      name: name ?? '',
      variation: variation,
      productId: productId,
      variantId: variantId,
    );
    return i >= 0 ? _items[i].quantity : 0;
  }

  /// Adds to the cart without ever going over the stock in the database.
  ///
  /// [stock] is the variant's stock when the caller knows it (product page).
  /// Quick-add buttons pass only [productId]; the stock is then checked
  /// against the database right after, and the quantity trimmed if needed.
  AddToCartResult addItem({
    required String name,
    required dynamic price,
    String? originalPrice,
    String image = '',
    String category = '',
    String variation = '',
    int quantity = 1,
    int? productId,
    int? variantId,
    int? stock,
  }) {
    final parsedPrice = parsePrice(price);
    final effectiveImage = image.isNotEmpty
        ? image
        : CartzyProductImages.getImage(name, category);

    final index = _indexOf(
      name: name,
      variation: variation,
      productId: productId,
      variantId: variantId,
    );
    final existing = index >= 0 ? _items[index] : null;
    final current = existing?.quantity ?? 0;
    final limit = stock ?? existing?.stock;
    final wanted = quantity < 1 ? 1 : quantity;
    final target = limit == null ? current + wanted : (current + wanted).clamp(0, limit);
    final added = (target - current).clamp(0, wanted);

    if (existing != null) {
      existing.quantity = current + added;
      existing.productId ??= productId;
      existing.variantId ??= variantId;
      if (stock != null) existing.stock = stock;
      if (added > 0) existing.selected = true;
    } else if (added > 0) {
      _items.add(
        CartItem(
          id: DateTime.now().microsecondsSinceEpoch.toString(),
          name: name,
          price: parsedPrice,
          originalPrice: originalPrice,
          image: effectiveImage,
          category: category,
          variation: variation,
          quantity: added,
          selected: true,
          productId: productId,
          variantId: variantId,
          stock: stock,
        ),
      );
    }

    if (added > 0 || stock != null) notifyListeners();

    if (added > 0) {
      _syncAddToServer(
        name: name,
        price: parsedPrice,
        image: effectiveImage,
        variation: variation,
        quantity: added,
        productId: productId,
        variantId: variantId,
      );
    }

    // Stock not known yet: check it against the database in the background.
    final line = existing ?? (added > 0 ? _items.last : null);
    if (line != null && stock == null) refreshStock(only: [line]);

    return AddToCartResult(
      requested: wanted,
      added: added,
      inCart: current + added,
      stock: limit,
    );
  }

  void removeItem(int index) {
    if (index >= 0 && index < _items.length) {
      final removed = _items.removeAt(index);
      notifyListeners();
      _syncRemoveFromServer(removed.id);
    }
  }

  /// Puts a removed item back at its old position (used by "Undo").
  void insertItem(int index, CartItem item) {
    if (_items.any((existing) => existing.id == item.id)) return;
    _items.insert(index.clamp(0, _items.length), item);
    notifyListeners();
    _syncAddToServer(
      name: item.name,
      price: item.price,
      image: item.image ?? '',
      variation: item.variation ?? '',
      quantity: item.quantity,
      productId: item.productId,
      variantId: item.variantId,
    );
  }

  void removeItemById(String id) {
    _items.removeWhere((item) => item.id == id);
    notifyListeners();
    _syncRemoveFromServer(id);
  }

  /// Adds one. Returns false when the stock limit is already reached.
  bool increaseQuantity(int index) {
    if (index < 0 || index >= _items.length) return false;
    final item = _items[index];
    if (item.atStockLimit) return false;
    item.quantity++;
    notifyListeners();
    _syncQuantityToServer(item);
    return true;
  }

  void decreaseQuantity(int index) {
    if (index >= 0 && index < _items.length) {
      final item = _items[index];
      if (item.quantity > 1) {
        item.quantity--;
        notifyListeners();
        _syncQuantityToServer(item);
      } else {
        _items.removeAt(index);
        notifyListeners();
        _syncRemoveFromServer(item.id);
      }
    }
  }

  /// Sets an explicit quantity, clamped between 1 and available stock (if known).
  /// Returns the clamped quantity actually applied, or -1 if the index is invalid.
  int setQuantity(int index, int quantity) {
    if (index < 0 || index >= _items.length) return -1;
    final item = _items[index];
    final maxAllowed =
        (item.stock != null && item.stock! > 0) ? item.stock! : null;
    int target = quantity < 1 ? 1 : quantity;
    if (maxAllowed != null && target > maxAllowed) {
      target = maxAllowed;
    }
    item.quantity = target;
    notifyListeners();
    _syncQuantityToServer(item);
    return target;
  }

  void toggleItemSelection(int index, bool? selected) {
    if (index >= 0 && index < _items.length) {
      final item = _items[index];
      if (item.isOutOfStock && selected == true) return;
      item.selected = selected ?? false;
      notifyListeners();
      _syncSelectionToServer(item);
    }
  }

  void toggleSelectAll(bool? selectAll) {
    final target = selectAll ?? false;
    for (final item in _items) {
      item.selected = target && !item.isOutOfStock;
    }
    notifyListeners();
    _syncSelectAllToServer();
  }

  /// Removes the items that were just checked out.
  void clearSelected() {
    final done = selectedItems.toSet();
    _items.removeWhere(done.contains);
    notifyListeners();
    for (final item in done) {
      _syncRemoveFromServer(item.id);
    }
  }

  void clearAll() {
    _items.clear();
    notifyListeners();
    _syncClearServer();
  }

  // ============================================================
  // STOCK CHECK AGAINST THE DATABASE
  // ============================================================

  Future<List<String>>? _refreshing;

  /// Loads the live stock of every cart item (or just [only]) from the
  /// database. Quantities above the stock are lowered to it, and items
  /// with no stock are unticked. Returns a message for each change.
  Future<List<String>> refreshStock({List<CartItem>? only}) {
    if (only == null) {
      return _refreshing ??=
          _refresh(_items.toList()).whenComplete(() => _refreshing = null);
    }
    return _refresh(only);
  }

  Future<List<String>> _refresh(List<CartItem> targets) async {
    final notes = <String>[];
    if (targets.isEmpty) return notes;

    // Items saved before ids were stored: find the product by its name.
    for (final item in targets.where((i) => i.productId == null)) {
      try {
        final found = await CatalogService.fetchProducts(query: item.name, perPage: 10);
        for (final p in found) {
          if (p.id != null && _norm(p.name) == _norm(item.name)) {
            item.productId = p.id;
            break;
          }
        }
      } catch (_) {}
    }

    final ids = targets.map((i) => i.productId).whereType<int>().toSet();
    final details = <int, Map<String, dynamic>>{};
    await Future.wait(ids.map((id) async {
      final d = await CatalogService.fetchProductDetails(id, fresh: true);
      if (d != null) details[id] = d;
    }));

    var changed = false;
    for (final item in targets) {
      final d = details[item.productId];
      if (d == null) continue; // couldn't reach the server; keep as is

      final variants = (d['variants'] as List? ?? const [])
          .map((v) => Map<String, dynamic>.from(v as Map))
          .toList();
      final variant = _matchVariant(item, variants);

      final int stock;
      if (variant != null) {
        item.variantId = (variant['id'] as num?)?.toInt() ?? item.variantId;
        stock = (variant['stock'] as num?)?.toInt() ?? 0;
      } else {
        stock = variants.fold<int>(
            0, (sum, v) => sum + ((v['stock'] as num?)?.toInt() ?? 0));
      }

      if (item.stock != stock) changed = true;
      item.stock = stock;

      if (stock <= 0) {
        if (item.selected) {
          item.selected = false;
          changed = true;
        }
        notes.add('${item.safeName} is now out of stock.');
      } else if (item.quantity > stock) {
        item.quantity = stock;
        changed = true;
        notes.add('${item.safeName}: only $stock left, so your quantity was updated.');
      }
    }

    if (_mergeDuplicateVariants()) changed = true;
    if (changed) notifyListeners();
    return notes;
  }

  Map<String, dynamic>? _matchVariant(
      CartItem item, List<Map<String, dynamic>> variants) {
    if (variants.isEmpty) return null;
    if (item.variantId != null) {
      for (final v in variants) {
        if ((v['id'] as num?)?.toInt() == item.variantId) return v;
      }
    }
    final wanted = _norm(item.variation);
    if (wanted.isNotEmpty) {
      for (final v in variants) {
        final name = _norm(v['name']?.toString());
        final combo = _norm([v['color'], v['size']]
            .where((e) => e != null && e.toString().trim().isNotEmpty)
            .join(' / '));
        if (wanted == name || wanted == combo) return v;
      }
    }
    return variants.length == 1 ? variants.first : null;
  }

  /// Two lines for the same variant (e.g. added before ids were known)
  /// become one, still capped at the stock.
  bool _mergeDuplicateVariants() {
    var merged = false;
    for (var i = 0; i < _items.length; i++) {
      final a = _items[i];
      if (a.variantId == null) continue;
      for (var j = _items.length - 1; j > i; j--) {
        final b = _items[j];
        if (b.variantId != a.variantId) continue;
        a.quantity += b.quantity;
        if (a.stock != null && a.stock! > 0 && a.quantity > a.stock!) {
          a.quantity = a.stock!;
        }
        _items.removeAt(j);
        merged = true;
      }
    }
    return merged;
  }
}

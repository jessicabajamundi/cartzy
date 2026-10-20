import 'package:flutter/foundation.dart';
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
}

class CartService extends ChangeNotifier {
  static final CartService instance = CartService._internal();

  CartService._internal() {
    _seedInitialItems();
  }

  final List<CartItem> _items = [];

  List<CartItem> get items {
    // If the cart has old seed items from previous session, upgrade to the 5 design items
    if (_items.any((item) => item.id == 'seed-1' || item.id == 'seed-2')) {
      _items.clear();
      _seedInitialItems();
    }
    return List.unmodifiable(_items);
  }

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

  void _seedInitialItems() {
    _items.addAll([
      CartItem(
        id: 'item-1',
        name: 'ANC Pro Wireless Earphones BT5.3',
        price: 890.00,
        variation: 'Black',
        category: 'Electronics',
        image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&q=80',
        quantity: 1,
        selected: false,
      ),
      CartItem(
        id: 'item-2',
        name: 'Minimalist Chronograph Watch',
        price: 459.00,
        variation: 'Black',
        category: 'Accessories',
        image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&q=80',
        quantity: 1,
        selected: false,
      ),
      CartItem(
        id: 'item-3',
        name: 'Modern 3-Seater Scandinavian Sofa',
        price: 12500.00,
        variation: 'Grey Velvet',
        category: 'Furniture',
        image: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&q=80',
        quantity: 1,
        selected: false,
      ),
      CartItem(
        id: 'item-4',
        name: 'Vacuum Insulated Steel Tumbler 32oz',
        price: 480.00,
        variation: 'Midnight Blue',
        category: 'Home & Living',
        image: 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&q=80',
        quantity: 1,
        selected: false,
      ),
      CartItem(
        id: 'item-5',
        name: 'Logitech Zone True Wireless Earbuds',
        price: 899.00,
        variation: 'Graphite',
        category: 'Electronics',
        image: 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=500&q=80',
        quantity: 1,
        selected: true,
      ),
    ]);
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

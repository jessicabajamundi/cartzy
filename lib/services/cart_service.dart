import 'package:flutter/foundation.dart';
import 'package:cartzy/utils/product_images.dart';

class CartItem {
  final String id;
  final String name;
  final double price;
  final String? originalPrice;
  final String image;
  final String category;
  int quantity;
  bool selected;

  CartItem({
    required this.id,
    required this.name,
    required this.price,
    this.originalPrice,
    this.image = '',
    this.category = '',
    this.quantity = 1,
    this.selected = true,
  });

  double get subtotal => price * quantity;
}

class CartService extends ChangeNotifier {
  static final CartService instance = CartService._internal();

  CartService._internal() {
    _seedInitialItems();
  }

  final List<CartItem> _items = [];

  List<CartItem> get items => List.unmodifiable(_items);

  List<CartItem> get selectedItems =>
      _items.where((item) => item.selected).toList();

  int get totalItemCount =>
      _items.fold<int>(0, (sum, item) => sum + item.quantity);

  int get selectedItemCount =>
      selectedItems.fold<int>(0, (sum, item) => sum + item.quantity);

  double get selectedTotal =>
      selectedItems.fold<double>(0.0, (sum, item) => sum + item.subtotal);

  bool get isAllSelected =>
      _items.isNotEmpty && _items.every((item) => item.selected);

  void _seedInitialItems() {
    _items.addAll([
      CartItem(
        id: 'seed-1',
        name: 'Smart Fitness Tracker Watch with Blood Oxygen',
        price: 1299.00,
        originalPrice: '₱1,999',
        category: 'Electronics',
        image: CartzyProductImages.getImage('Smart Fitness Tracker Watch with Blood Oxygen', 'Electronics'),
        quantity: 1,
        selected: true,
      ),
      CartItem(
        id: 'seed-2',
        name: 'ANC Pro Wireless Noise Cancelling Earphones',
        price: 890.00,
        originalPrice: '₱1,850',
        category: 'Electronics',
        image: CartzyProductImages.getImage('ANC Pro Wireless Noise Cancelling Earphones', 'Electronics'),
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

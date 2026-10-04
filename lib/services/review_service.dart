import 'package:flutter/foundation.dart';
import 'package:cartzy/utils/product_images.dart';

class Order {
  final String id;
  final String productName;
  String status;
  final String price;
  final int quantity;
  final String image;
  bool hasReviewed;
  double? userRating;
  String? userReview;
  List<String> userTags;
  final DateTime orderDate;

  Order({
    required this.id,
    required this.productName,
    required this.status,
    required this.price,
    required this.quantity,
    this.image = '',
    this.hasReviewed = false,
    this.userRating,
    this.userReview,
    this.userTags = const [],
    DateTime? orderDate,
  }) : orderDate = orderDate ?? DateTime.now();

  String get effectiveImage =>
      image.isNotEmpty ? image : CartzyProductImages.getImage(productName);
}

class ProductReview {
  final String id;
  final String productName;
  final String userName;
  final double rating;
  final String comment;
  final List<String> tags;
  final DateTime date;
  final bool isAnonymous;

  ProductReview({
    required this.id,
    required this.productName,
    required this.userName,
    required this.rating,
    required this.comment,
    this.tags = const [],
    DateTime? date,
    this.isAnonymous = false,
  }) : date = date ?? DateTime.now();

  String get displayName => isAnonymous ? 'Anonymous Buyer' : userName;
}

class ReviewService extends ChangeNotifier {
  static final ReviewService instance = ReviewService._internal();

  ReviewService._internal() {
    _seedOrders();
    _seedReviews();
  }

  final List<Order> _orders = [];
  final List<ProductReview> _reviews = [];

  List<Order> get orders => List.unmodifiable(_orders);
  List<ProductReview> get allReviews => List.unmodifiable(_reviews);

  List<Order> getOrdersByStatus(String status) {
    return _orders.where((o) => o.status == status).toList();
  }

  int get toReviewCount =>
      _orders.where((o) => o.status == 'To Review' && !o.hasReviewed).length;

  List<ProductReview> getReviewsForProduct(String productName) {
    final cleanTarget = productName.trim().toLowerCase();
    final matching = _reviews.where((r) {
      final cleanName = r.productName.trim().toLowerCase();
      return cleanName == cleanTarget ||
          cleanName.contains(cleanTarget) ||
          cleanTarget.contains(cleanName);
    }).toList();

    // If no direct matching reviews, return general sample reviews for lively UI
    if (matching.isEmpty) {
      return [
        ProductReview(
          id: 'def-1',
          productName: productName,
          userName: 'Maria Santos',
          rating: 5.0,
          comment: 'Super fast delivery and the item arrived in perfect condition! Highly recommended seller.',
          tags: ['Fast Shipping', 'Great Quality', 'Well Packaged'],
          date: DateTime.now().subtract(const Duration(days: 2)),
        ),
        ProductReview(
          id: 'def-2',
          productName: productName,
          userName: 'Juan Dela Cruz',
          rating: 4.8,
          comment: 'Good value for money. Quality matches the description accurately.',
          tags: ['True to Description', 'Great Value'],
          date: DateTime.now().subtract(const Duration(days: 5)),
        ),
      ];
    }
    return matching;
  }

  double getAverageRating(String productName) {
    final list = getReviewsForProduct(productName);
    if (list.isEmpty) return 4.9;
    final total = list.fold<double>(0.0, (sum, r) => sum + r.rating);
    return double.parse((total / list.length).toStringAsFixed(1));
  }

  void _seedOrders() {
    _orders.addAll([
      Order(
        id: 'ord-101',
        productName: 'Wireless Bluetooth Headphones Over-Ear 40H Battery',
        status: 'To Pay',
        price: '₱1,920',
        quantity: 1,
        orderDate: DateTime.now().subtract(const Duration(hours: 3)),
      ),
      Order(
        id: 'ord-102',
        productName: 'Pro Running Shoes Breathable Mesh Anti-Slip Sole',
        status: 'To Ship',
        price: '₱2,470',
        quantity: 1,
        orderDate: DateTime.now().subtract(const Duration(days: 1)),
      ),
      Order(
        id: 'ord-103',
        productName: 'Smart Fitness Tracker Watch with Blood Oxygen',
        status: 'To Receive',
        price: '₱1,299',
        quantity: 1,
        orderDate: DateTime.now().subtract(const Duration(days: 2)),
      ),
      Order(
        id: 'ord-104',
        productName: 'ANC Pro Wireless Noise Cancelling Earphones',
        status: 'To Review',
        price: '₱890',
        quantity: 1,
        hasReviewed: false,
        orderDate: DateTime.now().subtract(const Duration(days: 3)),
      ),
      Order(
        id: 'ord-105',
        productName: 'Minimalist Matte Chronograph Watch Waterproof',
        status: 'To Review',
        price: '₱459',
        quantity: 1,
        hasReviewed: false,
        orderDate: DateTime.now().subtract(const Duration(days: 4)),
      ),
      Order(
        id: 'ord-106',
        productName: 'Ceramic Aesthetic Coffee Mug & Saucer Set',
        status: 'To Review',
        price: '₱280',
        quantity: 2,
        hasReviewed: true,
        userRating: 5.0,
        userReview: 'The ceramic quality is top-notch! Packaged safely with bubble wrap.',
        userTags: ['Well Packaged', 'Great Quality'],
        orderDate: DateTime.now().subtract(const Duration(days: 7)),
      ),
    ]);
  }

  void _seedReviews() {
    _reviews.addAll([
      ProductReview(
        id: 'rev-1',
        productName: 'ANC Pro Wireless Noise Cancelling Earphones',
        userName: 'Andrea Cruz',
        rating: 5.0,
        comment: 'The active noise cancellation really works well! Battery lasts for days on a single charge.',
        tags: ['Great Quality', 'Fast Shipping'],
        date: DateTime.now().subtract(const Duration(days: 1)),
      ),
      ProductReview(
        id: 'rev-2',
        productName: 'Smart Fitness Tracker Watch with Blood Oxygen',
        userName: 'Mark Bautista',
        rating: 5.0,
        comment: 'Accurate step counting and heart rate monitor. The display is very bright even in sunlight.',
        tags: ['True to Description', 'Great Value'],
        date: DateTime.now().subtract(const Duration(days: 3)),
      ),
      ProductReview(
        id: 'rev-3',
        productName: 'Ceramic Aesthetic Coffee Mug & Saucer Set',
        userName: 'Tiffany L.',
        rating: 5.0,
        comment: 'The ceramic quality is top-notch! Packaged safely with bubble wrap.',
        tags: ['Well Packaged', 'Great Quality'],
        date: DateTime.now().subtract(const Duration(days: 7)),
      ),
    ]);
  }

  void submitReview({
    String? orderId,
    required String productName,
    required double rating,
    required String comment,
    List<String> tags = const [],
    bool isAnonymous = false,
    String userName = 'Buyer',
  }) {
    // 1. Add to reviews list
    final newReview = ProductReview(
      id: 'rev-',
      productName: productName,
      userName: userName,
      rating: rating,
      comment: comment,
      tags: tags,
      date: DateTime.now(),
      isAnonymous: isAnonymous,
    );
    _reviews.insert(0, newReview);

    // 2. If orderId provided or matches order, update the order
    if (orderId != null && orderId.isNotEmpty) {
      final orderIndex = _orders.indexWhere((o) => o.id == orderId);
      if (orderIndex >= 0) {
        final order = _orders[orderIndex];
        order.hasReviewed = true;
        order.userRating = rating;
        order.userReview = comment;
        order.userTags = tags;
      }
    } else {
      final orderIndex = _orders.indexWhere(
        (o) => o.productName.toLowerCase() == productName.toLowerCase() && !o.hasReviewed,
      );
      if (orderIndex >= 0) {
        final order = _orders[orderIndex];
        order.hasReviewed = true;
        order.userRating = rating;
        order.userReview = comment;
        order.userTags = tags;
      }
    }

    notifyListeners();
  }

  void confirmReceipt(String orderId) {
    final index = _orders.indexWhere((o) => o.id == orderId);
    if (index >= 0) {
      _orders[index].status = 'To Review';
      notifyListeners();
    }
  }

  void payOrder(String orderId) {
    final index = _orders.indexWhere((o) => o.id == orderId);
    if (index >= 0) {
      _orders[index].status = 'To Ship';
      notifyListeners();
    }
  }

  int _orderCounter = 200;

  void addOrderFromCheckout({
    required String productName,
    required String price,
    required int quantity,
    String paymentMethod = 'COD',
    String image = '',
  }) {
    _orderCounter++;
    // COD → buyer still needs to pay, so status is 'To Pay'
    // GCash → already paid at checkout, so status is 'To Ship'
    final initialStatus = paymentMethod == 'GCash' ? 'To Ship' : 'To Pay';
    final effectiveImage = image.isNotEmpty
        ? image
        : CartzyProductImages.getImage(productName);

    _orders.insert(
      0,
      Order(
        id: 'ord-$_orderCounter',
        productName: productName,
        status: initialStatus,
        price: price,
        quantity: quantity,
        image: effectiveImage,
      ),
    );
    notifyListeners();
  }
}

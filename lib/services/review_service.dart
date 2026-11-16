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

  ReviewService._internal();

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

    return matching;
  }

  double getAverageRating(String productName) {
    final list = getReviewsForProduct(productName);
    if (list.isEmpty) return 0.0;
    final total = list.fold<double>(0.0, (sum, r) => sum + r.rating);
    return double.parse((total / list.length).toStringAsFixed(1));
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
        (o) =>
            o.productName.toLowerCase() == productName.toLowerCase() &&
            !o.hasReviewed,
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
    final effectiveImage =
        image.isNotEmpty ? image : CartzyProductImages.getImage(productName);

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

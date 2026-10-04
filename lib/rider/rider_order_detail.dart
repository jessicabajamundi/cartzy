// ============================================================
// RIDER ORDER MODEL
// ============================================================
//
// Shared by every rider screen that lists or acts on an order:
// pickup dashboard, delivery dashboard, pickup/deliver process,
// and delivery history. Matches the enriched response from
// GET /api/orders/available and GET /api/orders/assigned
// (joins in buyer_name, buyer_phone, items_summary).

class RiderOrder {
  final int id;
  final String status; // to_pay / to_ship / to_receive / completed / cancelled
  final String buyerName;
  final String buyerPhone;
  final String deliveryAddress;
  final String itemsSummary;
  final double totalAmount;
  final String createdAt;

  RiderOrder({
    required this.id,
    required this.status,
    required this.buyerName,
    required this.buyerPhone,
    required this.deliveryAddress,
    required this.itemsSummary,
    required this.totalAmount,
    required this.createdAt,
  });

  factory RiderOrder.fromJson(Map<String, dynamic> json) {
    return RiderOrder(
      id: int.tryParse(json['id']?.toString() ?? '') ?? 0,
      status: json['status']?.toString() ?? '',
      buyerName: json['buyer_name']?.toString() ?? 'Buyer',
      buyerPhone: json['buyer_phone']?.toString() ?? '',
      deliveryAddress: json['delivery_address']?.toString() ?? 'No address provided',
      itemsSummary: json['items_summary']?.toString() ?? 'Items unavailable',
      totalAmount: double.tryParse(json['total_amount']?.toString() ?? '') ?? 0,
      createdAt: json['created_at']?.toString() ?? '',
    );
  }

  String get itemName => itemsSummary;
  String get personName => buyerName;
  String get address => deliveryAddress;
}
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';

/// One parcel in a Last Mile rider's queue, from GET /api/rider/deliveries.
class RiderDelivery {
  final int id;
  final String trackingCode;
  final String? orderReference;

  /// assigned, out_for_delivery, delivered or failed
  final String status;
  final String? recipientName;
  final String? recipientPhone;
  final String? recipientAddress;
  final String? sellerName;
  final int itemsCount;
  final String? itemsSummary;
  final bool isCod;
  final double codAmount;
  final int attempts;

  const RiderDelivery({
    required this.id,
    required this.trackingCode,
    required this.status,
    this.orderReference,
    this.recipientName,
    this.recipientPhone,
    this.recipientAddress,
    this.sellerName,
    this.itemsCount = 0,
    this.itemsSummary,
    this.isCod = false,
    this.codAmount = 0,
    this.attempts = 0,
  });

  bool get isOutForDelivery => status == 'out_for_delivery';

  factory RiderDelivery.fromJson(Map<String, dynamic> json) => RiderDelivery(
        id: (json['id'] as num?)?.toInt() ?? 0,
        trackingCode: _str(json['tracking_code']) ?? '',
        orderReference: _str(json['order_reference']),
        status: _str(json['status']) ?? 'assigned',
        recipientName: _str(json['recipient_name']),
        recipientPhone: _str(json['recipient_phone']),
        recipientAddress: _str(json['recipient_address']),
        sellerName: _str(json['seller_name']),
        itemsCount: (json['items_count'] as num?)?.toInt() ?? 0,
        itemsSummary: _str(json['items_summary']),
        isCod: json['is_cod'] == true,
        codAmount: (json['cod_amount'] as num?)?.toDouble() ?? 0,
        attempts: (json['attempts'] as num?)?.toInt() ?? 0,
      );
}

class RiderDeliveryQueue {
  final List<RiderDelivery> deliveries;
  final double codToCollect;
  final int deliveredToday;

  const RiderDeliveryQueue({required this.deliveries, this.codToCollect = 0, this.deliveredToday = 0});

  factory RiderDeliveryQueue.fromJson(Map<String, dynamic> json) => RiderDeliveryQueue(
        deliveries: (json['deliveries'] as List? ?? const [])
            .map((e) => RiderDelivery.fromJson(Map<String, dynamic>.from(e as Map)))
            .toList(),
        codToCollect: (json['cod_to_collect'] as num?)?.toDouble() ?? 0,
        deliveredToday: (json['delivered_today'] as num?)?.toInt() ?? 0,
      );
}

/// Every step is saved on the server, so the hub, seller and buyer see it.
class RiderDeliveryService {
  static Future<RiderDeliveryQueue> fetchQueue() async =>
      RiderDeliveryQueue.fromJson(await ApiService.get('/rider/deliveries', token: AuthService.token));

  /// Leaving the hub with the parcel: saves "out for delivery". Same as scanning
  /// the waybill and accepting it.
  static Future<String> startDelivery(RiderDelivery delivery) async {
    final response = await ApiService.post('/rider/parcels/accept', {'code': delivery.trackingCode}, token: AuthService.token);
    return response['message']?.toString() ?? 'Out for delivery.';
  }

  /// [code] is the scanned or typed waybill code, checked by the server.
  static Future<String> markDelivered(RiderDelivery delivery, {required String code, String? receivedBy, String? note}) async {
    final response = await ApiService.post(
      '/rider/deliveries/${delivery.id}/deliver',
      {
        'code': code,
        if (receivedBy != null && receivedBy.trim().isNotEmpty) 'received_by': receivedBy.trim(),
        if (note != null && note.trim().isNotEmpty) 'note': note.trim(),
      },
      token: AuthService.token,
    );
    return response['message']?.toString() ?? 'Delivered.';
  }

  static Future<String> markFailed(RiderDelivery delivery, String reason) async {
    final response = await ApiService.post('/rider/deliveries/${delivery.id}/fail', {'reason': reason}, token: AuthService.token);
    return response['message']?.toString() ?? 'Marked as not delivered.';
  }
}

String? _str(Object? value) {
  final text = value?.toString().trim() ?? '';
  return text.isEmpty ? null : text;
}

import 'api_service.dart';
import 'auth_service.dart';

/// Buyer orders from the Cartzy API — the same cards, rules and actions as
/// the website's "Orders" tab.
class BuyerOrderItem {
  final int id;
  final String name;
  final String variation;
  final int qty;
  final double price;
  final String image;
  final int? rating;
  final String? comment;

  const BuyerOrderItem({
    required this.id,
    required this.name,
    required this.variation,
    required this.qty,
    required this.price,
    required this.image,
    this.rating,
    this.comment,
  });

  factory BuyerOrderItem.fromJson(Map<String, dynamic> json) => BuyerOrderItem(
        id: _int(json['id']),
        name: json['name']?.toString() ?? '',
        variation: json['variation']?.toString() ?? '',
        qty: _int(json['qty']),
        price: _double(json['price']),
        image: json['image']?.toString() ?? '',
        rating: json['rating'] == null ? null : _int(json['rating']),
        comment: json['comment']?.toString(),
      );
}

class OrderReport {
  final int id;
  final String status;
  final String subject;
  final String? adminNotes;

  const OrderReport({
    required this.id,
    required this.status,
    required this.subject,
    this.adminNotes,
  });

  bool get isOpen => status == 'open';

  factory OrderReport.fromJson(Map<String, dynamic> json) => OrderReport(
        id: _int(json['id']),
        status: json['status']?.toString() ?? '',
        subject: json['subject']?.toString() ?? '',
        adminNotes: json['admin_notes']?.toString(),
      );
}

class BuyerOrder {
  /// Order reference, e.g. "CTZ-20261027-0001". Several stores can share one.
  final String reference;
  final int sellerOrderId;
  final String? trackingCode;
  final bool canCancel;

  /// null, 'pending', 'approved' or 'rejected'.
  final String? cancellationStatus;
  final OrderReport? report;
  final bool canReport;
  final String store;

  /// One of [BuyerOrder.statuses].
  final String status;
  final String date;
  final double total;
  final String payment;
  final List<BuyerOrderItem> items;

  const BuyerOrder({
    required this.reference,
    required this.sellerOrderId,
    required this.trackingCode,
    required this.canCancel,
    required this.cancellationStatus,
    required this.report,
    required this.canReport,
    required this.store,
    required this.status,
    required this.date,
    required this.total,
    required this.payment,
    required this.items,
  });

  /// Website filter order and labels.
  static const statuses = {
    'to_pay': 'To Pay',
    'to_ship': 'To Ship',
    'to_receive': 'To Receive',
    'completed': 'Completed',
    'cancelled': 'Cancelled',
  };

  String get statusLabel => statuses[status] ?? status;

  factory BuyerOrder.fromJson(Map<String, dynamic> json) => BuyerOrder(
        reference: json['id']?.toString() ?? '',
        sellerOrderId: _int(json['seller_order_id']),
        trackingCode: json['tracking_code']?.toString(),
        canCancel: json['can_cancel'] == true,
        cancellationStatus: json['cancellation_status']?.toString(),
        report: json['report'] is Map
            ? OrderReport.fromJson(Map<String, dynamic>.from(json['report'] as Map))
            : null,
        canReport: json['can_report'] == true,
        store: json['store']?.toString() ?? 'Cartzy Seller',
        status: json['status']?.toString() ?? 'to_ship',
        date: json['date']?.toString() ?? '',
        total: _double(json['total']),
        payment: json['payment']?.toString() ?? '',
        items: (json['items'] as List? ?? const [])
            .map((i) => BuyerOrderItem.fromJson(Map<String, dynamic>.from(i as Map)))
            .toList(),
      );
}

class ReportOption {
  final String key;
  final String label;
  final bool needsPhotos;

  const ReportOption(this.key, this.label, {this.needsPhotos = false});
}

class BuyerOrdersData {
  final List<BuyerOrder> orders;
  final Map<String, int> counts;
  final List<String> cancellationReasons;
  final List<ReportOption> reportCategories;
  final List<ReportOption> reportResolutions;
  final String contactPhone;

  const BuyerOrdersData({
    required this.orders,
    required this.counts,
    required this.cancellationReasons,
    required this.reportCategories,
    required this.reportResolutions,
    required this.contactPhone,
  });

  factory BuyerOrdersData.fromJson(Map<String, dynamic> json) {
    List<Map<String, dynamic>> maps(dynamic list) =>
        (list as List? ?? const []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
    final counts = json['counts'];
    return BuyerOrdersData(
      orders: maps(json['orders']).map(BuyerOrder.fromJson).toList(),
      counts: counts is Map ? counts.map((k, v) => MapEntry(k.toString(), _int(v))) : const {},
      cancellationReasons:
          (json['cancellation_reasons'] as List? ?? const []).map((e) => e.toString()).toList(),
      reportCategories: maps(json['report_categories'])
          .map((c) => ReportOption(c['key'].toString(), c['label'].toString(),
              needsPhotos: c['needs_photos'] == true))
          .toList(),
      reportResolutions: maps(json['report_resolutions'])
          .map((c) => ReportOption(c['key'].toString(), c['label'].toString()))
          .toList(),
      contactPhone: json['contact_phone']?.toString() ?? '',
    );
  }
}

class OrderService {
  static Future<BuyerOrdersData> fetchOrders() async {
    final response = await ApiService.get('/orders', token: AuthService.token);
    return BuyerOrdersData.fromJson(response);
  }

  /// The delivery timeline shown by "Track order" (see OrderTracking on the server).
  static Future<Map<String, dynamic>> fetchTracking(int sellerOrderId) async {
    final response =
        await ApiService.get('/orders/$sellerOrderId/tracking', token: AuthService.token);
    return Map<String, dynamic>.from(response['tracking'] as Map? ?? const {});
  }

  /// Returns the server's message; the seller still has to approve it.
  static Future<String> requestCancellation(
    int sellerOrderId, {
    required String reason,
    String? details,
  }) async {
    final response = await ApiService.post(
      '/orders/$sellerOrderId/cancel',
      {'reason': reason, if (details != null && details.isNotEmpty) 'reason_details': details},
      token: AuthService.token,
    );
    return response['message']?.toString() ?? 'Cancellation request sent.';
  }

  static Future<String> pay(String reference) async {
    final response = await ApiService.post(
      '/orders/pay/${Uri.encodeComponent(reference)}',
      {},
      token: AuthService.token,
    );
    return response['message']?.toString() ?? 'Payment recorded.';
  }

  static Future<String> reportProblem(
    int sellerOrderId, {
    required String category,
    required String resolution,
    required String description,
    required String contactPhone,
    List<int> itemIds = const [],
    List<String> photoPaths = const [],
  }) async {
    final response = await ApiService.postMultipart(
      '/orders/$sellerOrderId/report',
      {
        'category': category,
        'requested_resolution': resolution,
        'description': description,
        'contact_phone': contactPhone,
        for (var i = 0; i < itemIds.length; i++) 'items[$i]': '${itemIds[i]}',
      },
      files: {
        for (var i = 0; i < photoPaths.length; i++) 'photos[$i]': photoPaths[i],
      },
      token: AuthService.token,
    );
    return response['message']?.toString() ?? 'Report sent.';
  }

  static Future<String> review(int orderItemId, {required int rating, String? comment}) async {
    final response = await ApiService.post(
      '/reviews',
      {
        'order_item_id': orderItemId,
        'rating': rating,
        if (comment != null && comment.isNotEmpty) 'comment': comment,
      },
      token: AuthService.token,
    );
    return response['message']?.toString() ?? 'Thanks! Your review has been saved.';
  }
}

int _int(dynamic value) => value is num ? value.toInt() : int.tryParse('$value') ?? 0;

double _double(dynamic value) => value is num ? value.toDouble() : double.tryParse('$value') ?? 0;

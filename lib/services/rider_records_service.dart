import 'package:cartzy/rider/rider_order_detail.dart';
import 'package:cartzy/services/api_service.dart';

class RiderRecords {
  final List<RiderOrder> orders;
  final String? hubName;
  final double feePerDelivery;

  const RiderRecords(
      {required this.orders, this.hubName, this.feePerDelivery = 0});
}

class RiderRecordsService {
  static Future<RiderRecords> fetch(String token) async {
    final orders = <RiderOrder>[];
    var page = 1;
    var lastPage = 1;
    String? hubName;
    double fee = 0;
    do {
      final data = await ApiService.get('/rider/completed-records?page=$page',
          token: token);
      orders.addAll((data['records'] as List? ?? []).map(
          (row) => RiderOrder.fromJson(Map<String, dynamic>.from(row as Map))));
      hubName = data['hub_name']?.toString();
      fee = (data['fee_per_delivery'] as num?)?.toDouble() ?? 0;
      lastPage = ((data['meta'] as Map?)?['last_page'] as num?)?.toInt() ?? 1;
      page++;
    } while (page <= lastPage);
    return RiderRecords(orders: orders, hubName: hubName, feePerDelivery: fee);
  }
}

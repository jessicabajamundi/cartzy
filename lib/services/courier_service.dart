import 'package:cartzy/rider/rider_home_address.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';

/// The First Mile courier's dashboard, straight from GET /api/courier/dashboard.
/// Every value is real; missing ones stay null and lists may be empty.
class CourierDashboardData {
  final CourierProfile courier;
  final bool onShift;
  final DateTime? shiftSince;
  final int pickupsToDo;
  final int parcelsToCollect;
  final int inVehicleCount;
  final int collectedToday;
  final int handedToHubToday;
  final List<CourierPickup> pickups;
  final List<CourierParcel> inVehicle;
  final List<CourierParcel> handedToHub;

  const CourierDashboardData({
    required this.courier,
    required this.onShift,
    required this.shiftSince,
    required this.pickupsToDo,
    required this.parcelsToCollect,
    required this.inVehicleCount,
    required this.collectedToday,
    required this.handedToHubToday,
    required this.pickups,
    required this.inVehicle,
    required this.handedToHub,
  });

  factory CourierDashboardData.fromJson(Map<String, dynamic> json) {
    final shift = Map<String, dynamic>.from(json['shift'] as Map? ?? const {});
    final today = Map<String, dynamic>.from(json['today'] as Map? ?? const {});
    List<Map<String, dynamic>> list(String key) =>
        (json[key] as List? ?? const []).map((e) => Map<String, dynamic>.from(e as Map)).toList();

    return CourierDashboardData(
      courier: CourierProfile.fromJson(Map<String, dynamic>.from(json['courier'] as Map? ?? const {})),
      onShift: shift['on_shift'] == true,
      shiftSince: _date(shift['since']),
      pickupsToDo: _int(today['pickups_to_do']),
      parcelsToCollect: _int(today['parcels_to_collect']),
      inVehicleCount: _int(today['in_vehicle']),
      collectedToday: _int(today['collected_today']),
      handedToHubToday: _int(today['handed_to_hub_today']),
      pickups: list('pickups').map(CourierPickup.fromJson).toList(),
      inVehicle: list('in_vehicle').map(CourierParcel.fromJson).toList(),
      handedToHub: list('handed_to_hub').map(CourierParcel.fromJson).toList(),
    );
  }
}

class CourierProfile {
  final String name;
  final String? phone;
  final String? email;
  final String? courierTypeLabel;
  final String? vehicleLabel;
  final String? plateNo;
  final String? area;
  final String? hub;
  final String? status;
  final bool isActive;
  final String? memberSince;
  final HomeAddress? homeAddress;

  const CourierProfile({
    required this.name,
    this.phone,
    this.email,
    this.courierTypeLabel,
    this.vehicleLabel,
    this.plateNo,
    this.area,
    this.hub,
    this.status,
    this.isActive = false,
    this.memberSince,
    this.homeAddress,
  });

  factory CourierProfile.fromJson(Map<String, dynamic> json) => CourierProfile(
        name: _str(json['name']) ?? '',
        phone: _str(json['phone']),
        email: _str(json['email']),
        courierTypeLabel: _str(json['courier_type_label']),
        vehicleLabel: _str(json['vehicle_label']),
        plateNo: _str(json['plate_no']),
        area: _str(json['area']),
        hub: _str(json['hub']),
        status: _str(json['status']),
        isActive: json['is_active'] == true,
        memberSince: _str(json['member_since']),
        homeAddress: HomeAddress.fromJson(json['home_address']),
      );

  String get firstName => name.trim().split(RegExp(r'\s+')).first;
}

class CourierPickup {
  final int id;
  final String? sellerName;
  final String? sellerPhone;
  final String? address;
  final DateTime? pickupDate;
  final String? pickupTime;
  final int parcelCount;
  final int parcelsWaiting;
  final double? weightKg;
  final String? notes;

  const CourierPickup({
    required this.id,
    this.sellerName,
    this.sellerPhone,
    this.address,
    this.pickupDate,
    this.pickupTime,
    this.parcelCount = 0,
    this.parcelsWaiting = 0,
    this.weightKg,
    this.notes,
  });

  factory CourierPickup.fromJson(Map<String, dynamic> json) => CourierPickup(
        id: _int(json['id']),
        sellerName: _str(json['seller_name']),
        sellerPhone: _str(json['seller_phone']),
        address: _str(json['address']),
        pickupDate: _date(json['pickup_date']),
        pickupTime: _str(json['pickup_time']),
        parcelCount: _int(json['parcel_count']),
        parcelsWaiting: _int(json['parcels_waiting']),
        weightKg: (json['weight_kg'] as num?)?.toDouble(),
        notes: _str(json['notes']),
      );
}

class CourierParcel {
  final String trackingCode;
  final String? orderReference;
  final String? sellerName;
  final int itemsCount;
  final DateTime? at;

  const CourierParcel({
    required this.trackingCode,
    this.orderReference,
    this.sellerName,
    this.itemsCount = 0,
    this.at,
  });

  factory CourierParcel.fromJson(Map<String, dynamic> json) => CourierParcel(
        trackingCode: _str(json['tracking_code']) ?? '',
        orderReference: _str(json['order_reference']),
        sellerName: _str(json['seller_name']),
        itemsCount: _int(json['items_count']),
        at: _date(json['at']),
      );
}

class CourierService {
  static Future<CourierDashboardData> fetchDashboard() async {
    final response = await ApiService.get('/courier/dashboard', token: AuthService.token);
    return CourierDashboardData.fromJson(response);
  }
}

String? _str(Object? value) {
  final text = value?.toString().trim() ?? '';
  return text.isEmpty ? null : text;
}

int _int(Object? value) => (value as num?)?.toInt() ?? int.tryParse(value?.toString() ?? '') ?? 0;

DateTime? _date(Object? value) => value == null ? null : DateTime.tryParse(value.toString())?.toLocal();

/// "8:05 AM"
String courierTime(DateTime t) {
  final hour = t.hour % 12 == 0 ? 12 : t.hour % 12;
  return '$hour:${t.minute.toString().padLeft(2, '0')} ${t.hour < 12 ? 'AM' : 'PM'}';
}

/// "Today", "Tomorrow", "Yesterday" or "Nov 12".
String courierDay(DateTime d, {DateTime? now}) {
  final today = _dateOnly(now ?? DateTime.now());
  final day = _dateOnly(d);
  final diff = day.difference(today).inDays;
  if (diff == 0) return 'Today';
  if (diff == 1) return 'Tomorrow';
  if (diff == -1) return 'Yesterday';
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  return '${months[d.month - 1]} ${d.day}';
}

DateTime _dateOnly(DateTime d) => DateTime(d.year, d.month, d.day);

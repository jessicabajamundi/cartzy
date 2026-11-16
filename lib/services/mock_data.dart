import 'package:cartzy/rider/rider_order_detail.dart';
import 'package:cartzy/rider/rider_chat_list_screen.dart';
import 'package:cartzy/rider/rider_chat_screen.dart';

class MockData {
  static final List<RiderOrder> availablePickups = [];

  static final List<RiderOrder> activeDeliveries = [];

  static final List<RiderOrder> completedDeliveries = [];

  static int completedDeliveryCount = 0;
  static double flatFeePerDelivery = 0.0;
  static double get totalEarnings => completedDeliveryCount * flatFeePerDelivery;

  static final List<ChatThread> chatThreads = [];

  static final Map<int, List<ChatMessage>> chatMessages = {};

  static final List<Map<String, dynamic>> addresses = [];
}

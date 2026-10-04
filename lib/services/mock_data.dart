import 'package:cartzy/rider/rider_order_detail.dart';
import 'package:cartzy/rider/rider_chat_list_screen.dart';
import 'package:cartzy/rider/rider_chat_screen.dart';

class MockData {
  // ------------------------------------------------------------
  // RIDER ORDERS
  // ------------------------------------------------------------

  static final List<RiderOrder> availablePickups = [
    RiderOrder(
      id: 1042,
      status: 'to_ship',
      buyerName: 'Maria Santos',
      buyerPhone: '+63 917 123 4567',
      deliveryAddress: 'Unit 402, Sunset Towers, BGC, Taguig City',
      itemsSummary: '2x Organic Ground Coffee (500g), 1x French Press',
      totalAmount: 1250.00,
      createdAt: '2026-10-04 09:30 AM',
    ),
    RiderOrder(
      id: 1043,
      status: 'to_ship',
      buyerName: 'Juan Dela Cruz',
      buyerPhone: '+63 918 987 6543',
      deliveryAddress: 'Block 12 Lot 5, San Isidro St., Pasig City',
      itemsSummary: '1x Wireless Bluetooth Earbuds (Black)',
      totalAmount: 899.00,
      createdAt: '2026-10-04 10:15 AM',
    ),
    RiderOrder(
      id: 1044,
      status: 'to_ship',
      buyerName: 'Angelica Reyes',
      buyerPhone: '+63 920 555 1212',
      deliveryAddress: '24 Orchid Lane, Ayala Alabang, Muntinlupa',
      itemsSummary: '3x Ceramic Coffee Mug Set, 1x Pour Over Kettle',
      totalAmount: 2150.00,
      createdAt: '2026-10-04 11:00 AM',
    ),
  ];

  static final List<RiderOrder> activeDeliveries = [
    RiderOrder(
      id: 1039,
      status: 'to_receive',
      buyerName: 'Carlos Mendoza',
      buyerPhone: '+63 908 333 4455',
      deliveryAddress: '15 Jupiter St., Bel-Air, Makati City',
      itemsSummary: '1x Stainless Steel Bento Lunchbox',
      totalAmount: 649.00,
      createdAt: '2026-10-04 08:45 AM',
    ),
    RiderOrder(
      id: 1040,
      status: 'to_receive',
      buyerName: 'Patricia Tan',
      buyerPhone: '+63 919 444 8899',
      deliveryAddress: '88 Emerald Ave, Ortigas Center, Pasig City',
      itemsSummary: '2x Linen Kitchen Apron, 1x Oven Mitts Pair',
      totalAmount: 820.00,
      createdAt: '2026-10-04 09:10 AM',
    ),
  ];

  static final List<RiderOrder> completedDeliveries = [
    RiderOrder(
      id: 1035,
      status: 'completed',
      buyerName: 'Elena Gomez',
      buyerPhone: '+63 922 111 2233',
      deliveryAddress: '74 Katipunan Ave, Quezon City',
      itemsSummary: '1x Double Wall Glass Tumbler 450ml',
      totalAmount: 499.00,
      createdAt: '2026-10-03 02:15 PM',
    ),
    RiderOrder(
      id: 1036,
      status: 'completed',
      buyerName: 'Roberto Lim',
      buyerPhone: '+63 915 777 9988',
      deliveryAddress: 'Unit 12B, Pioneer Woodlands, Mandaluyong City',
      itemsSummary: '4x Ceramic Coasters, 1x Table Runner',
      totalAmount: 780.00,
      createdAt: '2026-10-03 04:00 PM',
    ),
    RiderOrder(
      id: 1037,
      status: 'completed',
      buyerName: 'Sofia Bautista',
      buyerPhone: '+63 917 888 3322',
      deliveryAddress: '33 Maginhawa St., UP Village, Quezon City',
      itemsSummary: '2x Pour Over Coffee Dripper',
      totalAmount: 950.00,
      createdAt: '2026-10-03 05:45 PM',
    ),
  ];

  // ------------------------------------------------------------
  // RIDER EARNINGS
  // ------------------------------------------------------------

  static int completedDeliveryCount = 28;
  static double flatFeePerDelivery = 85.00;
  static double get totalEarnings => completedDeliveryCount * flatFeePerDelivery;

  // ------------------------------------------------------------
  // CHAT THREADS & MESSAGES
  // ------------------------------------------------------------

  static final List<ChatThread> chatThreads = [
    ChatThread(
      userId: 201,
      name: 'Maria Santos',
      role: 'Buyer',
      lastMessage: 'Hi! Are you nearby the building already?',
      lastMessageAt: '10:45 AM',
    ),
    ChatThread(
      userId: 202,
      name: 'Juan Dela Cruz',
      role: 'Buyer',
      lastMessage: 'Please leave the parcel with the guard if I am not around.',
      lastMessageAt: 'Yesterday',
    ),
    ChatThread(
      userId: 203,
      name: 'Cartzy Dispatch Hub',
      role: 'Support',
      lastMessage: 'Route updated for today. Safe travels!',
      lastMessageAt: 'Yesterday',
    ),
  ];

  static final Map<int, List<ChatMessage>> chatMessages = {
    201: [
      ChatMessage(
        id: 1,
        senderId: 201,
        receiverId: 1,
        body: 'Hello rider! Looking forward to the delivery.',
        createdAt: '10:30 AM',
      ),
      ChatMessage(
        id: 2,
        senderId: 1,
        receiverId: 201,
        body: 'Good morning ma\'am! I have picked up your order and I am on my way.',
        createdAt: '10:35 AM',
      ),
      ChatMessage(
        id: 3,
        senderId: 201,
        receiverId: 1,
        body: 'Hi! Are you nearby the building already?',
        createdAt: '10:45 AM',
      ),
    ],
    202: [
      ChatMessage(
        id: 4,
        senderId: 202,
        receiverId: 1,
        body: 'Please leave the parcel with the guard if I am not around.',
        createdAt: '03:15 PM',
      ),
      ChatMessage(
        id: 5,
        senderId: 1,
        receiverId: 202,
        body: 'Noted sir! Will take a photo upon dropoff.',
        createdAt: '03:18 PM',
      ),
    ],
  };

  // ------------------------------------------------------------
  // BUYER SAVED ADDRESSES
  // ------------------------------------------------------------

  static final List<Map<String, dynamic>> addresses = [
    {
      'id': 1,
      'user_id': 1,
      'label': 'Home',
      'name': 'Tiffany Leonardo',
      'phone': '+63 917 123 4567',
      'house_number': '124',
      'street': 'Rizal Street, Brgy. San Antonio',
      'barangay': 'San Antonio',
      'municipality': 'Pasig City',
      'province': 'Metro Manila',
      'region': 'National Capital Region (NCR)',
      'postal_code': '1600',
      'is_default': true,
    },
    {
      'id': 2,
      'user_id': 1,
      'label': 'Office',
      'name': 'Tiffany Leonardo',
      'phone': '+63 917 123 4567',
      'house_number': 'Floor 18, High Street Corporate Plaza',
      'street': '26th Street corner 9th Avenue, BGC',
      'barangay': 'Fort Bonifacio',
      'municipality': 'Taguig City',
      'province': 'Metro Manila',
      'region': 'National Capital Region (NCR)',
      'postal_code': '1634',
      'is_default': false,
    },
  ];
}

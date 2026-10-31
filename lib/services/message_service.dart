import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';

class ConversationThread {
  final int id;
  final String type; // 'inquiry' or 'order'
  final String key;
  final String store;
  final int? sellerId;
  final int? productId;
  final String? productName;
  final String? productImage;
  final String? orderReference;
  final int? orderId;
  final String? avatar;
  final String lastMessage;
  final String? lastMessageAt;
  final String time;
  final int unreadCount;

  ConversationThread({
    required this.id,
    required this.type,
    required this.key,
    required this.store,
    this.sellerId,
    this.productId,
    this.productName,
    this.productImage,
    this.orderReference,
    this.orderId,
    this.avatar,
    required this.lastMessage,
    this.lastMessageAt,
    required this.time,
    required this.unreadCount,
  });

  factory ConversationThread.fromJson(Map<String, dynamic> json) {
    return ConversationThread(
      id: json['id'] as int? ?? 0,
      type: json['type']?.toString() ?? 'inquiry',
      key: json['key']?.toString() ?? '',
      store: json['store']?.toString() ?? 'Store',
      sellerId: json['seller_id'] as int?,
      productId: json['product_id'] as int?,
      productName: json['product_name']?.toString(),
      productImage: json['product_image']?.toString(),
      orderReference: json['order_reference']?.toString(),
      orderId: json['order_id'] as int?,
      avatar: json['avatar']?.toString(),
      lastMessage: json['last_message']?.toString() ?? '',
      lastMessageAt: json['last_message_at']?.toString(),
      time: json['time']?.toString() ?? '',
      unreadCount: json['unread_count'] as int? ?? 0,
    );
  }
}

class ChatMessage {
  final int id;
  final String body;
  final int senderId;
  final String senderName;
  final bool isMe;
  final String? createdAt;
  final String time;
  final String date;
  final String? deliveredAt;
  final String? readAt;
  final String status; // 'sent', 'delivered', 'read'

  ChatMessage({
    required this.id,
    required this.body,
    required this.senderId,
    required this.senderName,
    required this.isMe,
    this.createdAt,
    required this.time,
    required this.date,
    this.deliveredAt,
    this.readAt,
    required this.status,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: json['id'] as int? ?? 0,
      body: json['body']?.toString() ?? '',
      senderId: json['sender_id'] as int? ?? 0,
      senderName: json['sender_name']?.toString() ?? '',
      isMe: json['is_me'] as bool? ?? false,
      createdAt: json['created_at']?.toString(),
      time: json['time']?.toString() ?? '',
      date: json['date']?.toString() ?? '',
      deliveredAt: json['delivered_at']?.toString(),
      readAt: json['read_at']?.toString(),
      status: json['status']?.toString() ?? 'sent',
    );
  }
}

class MessageService {
  /// Fetch all conversation threads for current user
  static Future<List<ConversationThread>> fetchConversations() async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) return [];

    try {
      final res = await ApiService.get('/messages', token: token);
      final rawList = res['conversations'] as List? ?? [];
      return rawList
          .map((item) => ConversationThread.fromJson(Map<String, dynamic>.from(item as Map)))
          .toList();
    } catch (_) {
      return [];
    }
  }

  /// Get total unread count across all conversations
  static Future<int> fetchUnreadCount() async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) return 0;

    try {
      final res = await ApiService.get('/messages/unread-count', token: token);
      return res['unread_total'] as int? ?? 0;
    } catch (_) {
      return 0;
    }
  }

  /// Fetch full message history for a single thread
  static Future<Map<String, dynamic>> fetchThread({
    required String type,
    required int id,
  }) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) {
      return {'messages': <ChatMessage>[], 'thread': null};
    }

    try {
      final res = await ApiService.get(
        '/messages/thread?type=$type&id=$id',
        token: token,
      );
      final rawMsgs = res['messages'] as List? ?? [];
      final messages = rawMsgs
          .map((m) => ChatMessage.fromJson(Map<String, dynamic>.from(m as Map)))
          .toList();

      return {
        'messages': messages,
        'thread': res['thread'] is Map ? Map<String, dynamic>.from(res['thread'] as Map) : null,
      };
    } catch (_) {
      return {'messages': <ChatMessage>[], 'thread': null};
    }
  }

  /// Send message to an existing thread
  static Future<ChatMessage?> sendMessage({
    required String type,
    required int id,
    required String message,
  }) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) return null;

    try {
      final res = await ApiService.post(
        '/messages/send',
        {
          'type': type,
          'id': id,
          'message': message,
        },
        token: token,
      );

      if (res['message'] is Map) {
        return ChatMessage.fromJson(Map<String, dynamic>.from(res['message'] as Map));
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  /// Start or retrieve inquiry for a product or seller
  static Future<Map<String, dynamic>?> inquire({
    int? productId,
    int? sellerId,
    String? message,
  }) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) return null;

    try {
      final res = await ApiService.post(
        '/messages/inquire',
        {
          if (productId != null) 'product_id': productId,
          if (sellerId != null) 'seller_id': sellerId,
          if (message != null && message.trim().isNotEmpty) 'message': message.trim(),
        },
        token: token,
      );

      final rawMsgs = res['messages'] as List? ?? [];
      final messages = rawMsgs
          .map((m) => ChatMessage.fromJson(Map<String, dynamic>.from(m as Map)))
          .toList();

      return {
        'messages': messages,
        'thread': res['thread'] is Map ? Map<String, dynamic>.from(res['thread'] as Map) : null,
      };
    } catch (_) {
      return null;
    }
  }
}

import 'package:flutter/foundation.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';

class AppNotificationItem {
  final int id;
  final String type;
  final String title;
  final String message;
  final String tag; // 'orders', 'promos', 'system'
  final bool isRead;
  final String? link;
  final String? orderReference;
  final String time;
  final DateTime? createdAt;

  const AppNotificationItem({
    required this.id,
    required this.type,
    required this.title,
    required this.message,
    required this.tag,
    required this.isRead,
    this.link,
    this.orderReference,
    required this.time,
    this.createdAt,
  });

  factory AppNotificationItem.fromJson(Map<String, dynamic> json) {
    return AppNotificationItem(
      id: json['id'] as int? ?? 0,
      type: json['type']?.toString() ?? 'order',
      title: json['title']?.toString() ?? '',
      message: (json['message'] ?? json['body'])?.toString() ?? '',
      tag: json['tag']?.toString() ?? 'orders',
      isRead: (json['is_read'] ?? json['read']) == true,
      link: json['link']?.toString(),
      orderReference: json['order_reference']?.toString(),
      time: json['time']?.toString() ?? '',
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
    );
  }

  AppNotificationItem copyWith({
    bool? isRead,
  }) {
    return AppNotificationItem(
      id: id,
      type: type,
      title: title,
      message: message,
      tag: tag,
      isRead: isRead ?? this.isRead,
      link: link,
      orderReference: orderReference,
      time: time,
      createdAt: createdAt,
    );
  }
}

class NotificationData {
  final List<AppNotificationItem> notifications;
  final int unreadCount;
  final Map<String, int> counts;

  const NotificationData({
    required this.notifications,
    required this.unreadCount,
    required this.counts,
  });
}

class NotificationService {
  /// Global unread counter notifier for real-time badge updates across the app.
  static final ValueNotifier<int> unreadCountNotifier = ValueNotifier<int>(0);

  /// Fetch all active notifications for the current authenticated user.
  static Future<NotificationData> fetchNotifications() async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) {
      unreadCountNotifier.value = 0;
      return const NotificationData(
        notifications: [],
        unreadCount: 0,
        counts: {'all': 0, 'orders': 0, 'promos': 0, 'system': 0},
      );
    }

    try {
      final res = await ApiService.get('/notifications', token: token);
      final rawList = res['notifications'] as List? ?? [];
      final notifications = rawList
          .whereType<Map<String, dynamic>>()
          .map((item) => AppNotificationItem.fromJson(item))
          .toList();

      final unreadCount = res['unread_count'] as int? ??
          notifications.where((n) => !n.isRead).length;

      final rawCounts = res['counts'] as Map<String, dynamic>? ?? {};
      final counts = {
        'all': rawCounts['all'] as int? ?? notifications.length,
        'orders': rawCounts['orders'] as int? ??
            notifications.where((n) => n.tag == 'orders').length,
        'promos': rawCounts['promos'] as int? ??
            notifications.where((n) => n.tag == 'promos').length,
        'system': rawCounts['system'] as int? ??
            notifications.where((n) => n.tag == 'system').length,
      };

      unreadCountNotifier.value = unreadCount;

      return NotificationData(
        notifications: notifications,
        unreadCount: unreadCount,
        counts: counts,
      );
    } catch (e) {
      debugPrint('[NotificationService] fetchNotifications error: $e');
      rethrow;
    }
  }

  /// Quickly check unread count without loading full list.
  static Future<int> fetchUnreadCount() async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) {
      unreadCountNotifier.value = 0;
      return 0;
    }

    try {
      final res = await ApiService.get('/notifications/unread-count', token: token);
      final count = res['unread_count'] as int? ?? 0;
      unreadCountNotifier.value = count;
      return count;
    } catch (_) {
      return unreadCountNotifier.value;
    }
  }

  /// Mark a single notification as read.
  static Future<int> markAsRead(int id) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) return 0;

    try {
      final res = await ApiService.post('/notifications/$id/read', {}, token: token);
      final count = res['unread_count'] as int? ?? 0;
      unreadCountNotifier.value = count;
      return count;
    } catch (_) {
      final current = unreadCountNotifier.value;
      if (current > 0) {
        unreadCountNotifier.value = current - 1;
      }
      return unreadCountNotifier.value;
    }
  }

  /// Mark all notifications as read.
  static Future<void> markAllAsRead() async {
    final token = AuthService.token;
    unreadCountNotifier.value = 0;
    if (token == null || token.isEmpty) return;

    try {
      await ApiService.post('/notifications/read-all', {}, token: token);
    } catch (e) {
      debugPrint('[NotificationService] markAllAsRead error: $e');
    }
  }

  /// Archive or delete a notification.
  static Future<void> deleteNotification(int id) async {
    final token = AuthService.token;
    if (token == null || token.isEmpty) return;

    try {
      final res = await ApiService.delete('/notifications/$id', token: token);
      final count = res['unread_count'] as int?;
      if (count != null) {
        unreadCountNotifier.value = count;
      }
    } catch (e) {
      debugPrint('[NotificationService] deleteNotification error: $e');
    }
  }
}

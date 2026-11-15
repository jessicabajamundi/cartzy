import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/notification_service.dart';
import 'package:cartzy/buyer/notifications_screen.dart';

/// Handles system-level notifications in the mobile notification bar & tray.
class LocalNotificationService {
  static final FlutterLocalNotificationsPlugin _plugin =
      FlutterLocalNotificationsPlugin();

  static final GlobalKey<NavigatorState> navigatorKey =
      GlobalKey<NavigatorState>();

  static const String _channelId = 'cartzy_notifications';
  static const String _channelName = 'Cartzy Updates';
  static const String _channelDescription =
      'Order updates, delivery alerts, promos, and account notifications';

  static bool _initialized = false;
  static Timer? _pollTimer;
  static bool _isChecking = false;

  static Future<void> init() async {
    if (_initialized || kIsWeb) return;

    const androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    const darwinSettings = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    const initSettings = InitializationSettings(
      android: androidSettings,
      iOS: darwinSettings,
      macOS: darwinSettings,
    );

    await _plugin.initialize(
      settings: initSettings,
      onDidReceiveNotificationResponse: _onNotificationTapped,
    );

    // Create high-importance channel so notifications appear in the status bar & tray.
    const androidChannel = AndroidNotificationChannel(
      _channelId,
      _channelName,
      description: _channelDescription,
      importance: Importance.high,
      playSound: true,
      enableVibration: true,
      showBadge: true,
    );

    final androidPlugin = _plugin.resolvePlatformSpecificImplementation<
        AndroidFlutterLocalNotificationsPlugin>();
    if (androidPlugin != null) {
      await androidPlugin.createNotificationChannel(androidChannel);
      await androidPlugin.requestNotificationsPermission();
    }

    final iosPlugin = _plugin.resolvePlatformSpecificImplementation<
        IOSFlutterLocalNotificationsPlugin>();
    if (iosPlugin != null) {
      await iosPlugin.requestPermissions(
        alert: true,
        badge: true,
        sound: true,
      );
    }

    _initialized = true;
  }

  static void _onNotificationTapped(NotificationResponse response) {
    final nav = navigatorKey.currentState;
    if (nav == null || !AuthService.isAuthenticated) return;

    final payloadId = int.tryParse(response.payload ?? '');
    if (payloadId != null && payloadId > 0) {
      NotificationService.markAsRead(payloadId);
    }

    nav.push(
      MaterialPageRoute(
        builder: (ctx) => NotificationsScreen(
          onBack: () => Navigator.of(ctx).pop(),
        ),
      ),
    ).then((_) => NotificationService.fetchUnreadCount());
  }

  /// Displays a notification in the device's notification bar and notification tray.
  static Future<void> show({
    required int id,
    required String title,
    required String body,
    String? payload,
  }) async {
    if (kIsWeb) return;
    if (!_initialized) {
      await init();
    }

    final androidDetails = AndroidNotificationDetails(
      _channelId,
      _channelName,
      channelDescription: _channelDescription,
      importance: Importance.high,
      priority: Priority.high,
      playSound: true,
      enableVibration: true,
      icon: '@mipmap/ic_launcher',
      styleInformation: BigTextStyleInformation(body),
    );

    const darwinDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );

    final details = NotificationDetails(
      android: androidDetails,
      iOS: darwinDetails,
      macOS: darwinDetails,
    );

    await _plugin.show(
      id: id,
      title: title,
      body: body,
      notificationDetails: details,
      payload: payload,
    );
  }

  /// Starts periodic polling for new server notifications while logged in.
  static void startPolling({Duration interval = const Duration(seconds: 30)}) {
    _pollTimer?.cancel();
    if (!AuthService.isAuthenticated) return;

    // Run an immediate check first, then poll periodically.
    checkForNewNotifications();
    _pollTimer = Timer.periodic(interval, (_) {
      if (AuthService.isAuthenticated) {
        checkForNewNotifications();
      } else {
        stopPolling();
      }
    });
  }

  static void stopPolling() {
    _pollTimer?.cancel();
    _pollTimer = null;
  }

  /// Checks server notifications and pushes unseen unread items to the system tray.
  static Future<void> checkForNewNotifications() async {
    if (_isChecking || !AuthService.isAuthenticated) return;
    _isChecking = true;

    try {
      final data = await NotificationService.fetchNotifications();
      final userId = AuthService.currentUser?['id']?.toString() ?? 'default';
      final prefs = await SharedPreferences.getInstance();
      final prefKey = 'cartzy_last_notified_id_$userId';
      final lastSeenId = prefs.getInt(prefKey);

      final unread = data.notifications.where((n) => !n.isRead).toList();
      if (unread.isEmpty) {
        if (lastSeenId == null && data.notifications.isNotEmpty) {
          final maxId = data.notifications
              .map((n) => n.id)
              .reduce((a, b) => a > b ? a : b);
          await prefs.setInt(prefKey, maxId);
        }
        return;
      }

      List<AppNotificationItem> toNotify;
      if (lastSeenId == null) {
        // First time checking on this device: show up to 3 most recent unread items.
        unread.sort((a, b) => b.id.compareTo(a.id));
        toNotify = unread.take(3).toList().reversed.toList();
      } else {
        toNotify = unread.where((n) => n.id > lastSeenId).toList()
          ..sort((a, b) => a.id.compareTo(b.id));
      }

      int maxNotifiedId = lastSeenId ?? 0;
      for (final item in toNotify) {
        await show(
          id: item.id,
          title: item.title.isNotEmpty ? item.title : 'Cartzy',
          body: item.message,
          payload: item.id.toString(),
        );
        if (item.id > maxNotifiedId) {
          maxNotifiedId = item.id;
        }
      }

      final highestUnreadId =
          unread.map((n) => n.id).reduce((a, b) => a > b ? a : b);
      final nextSavedId =
          highestUnreadId > maxNotifiedId ? highestUnreadId : maxNotifiedId;
      if (nextSavedId > (lastSeenId ?? 0)) {
        await prefs.setInt(prefKey, nextSavedId);
      }
    } catch (e) {
      debugPrint('[LocalNotificationService] checkForNewNotifications error: $e');
    } finally {
      _isChecking = false;
    }
  }
}

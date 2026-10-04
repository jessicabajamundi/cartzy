import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/mock_data.dart';
import 'rider_chat_screen.dart';

// ============================================================
// CHAT — list of conversations
// ============================================================

class ChatThread {
  final int userId;
  final String name;
  final String role;
  final String lastMessage;
  final String lastMessageAt;

  ChatThread({
    required this.userId,
    required this.name,
    required this.role,
    required this.lastMessage,
    required this.lastMessageAt,
  });

  factory ChatThread.fromJson(Map<String, dynamic> json) {
    return ChatThread(
      userId: int.tryParse(json['user_id']?.toString() ?? '') ?? 0,
      name: json['name']?.toString() ?? 'User',
      role: json['role']?.toString() ?? '',
      lastMessage: json['last_message']?.toString() ?? '',
      lastMessageAt: json['last_message_at']?.toString() ?? '',
    );
  }
}

class RiderChatListScreen extends StatefulWidget {
  final String token;
  final int myUserId;
  final VoidCallback onBack;

  const RiderChatListScreen({
    super.key,
    required this.token,
    required this.myUserId,
    required this.onBack,
  });

  @override
  State<RiderChatListScreen> createState() => _RiderChatListScreenState();
}

class _RiderChatListScreenState extends State<RiderChatListScreen> {
  List<ChatThread> _threads = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    await Future.delayed(const Duration(milliseconds: 200));

    if (!mounted) return;
    setState(() {
      _threads = List.from(MockData.chatThreads);
      _isLoading = false;
    });
  }

  void _openThread(ChatThread thread) {
    Navigator.of(context)
        .push(
          MaterialPageRoute(
            builder: (context) => RiderChatScreen(
              token: widget.token,
              myUserId: widget.myUserId,
              otherUserId: thread.userId,
              otherUserName: thread.name,
            ),
          ),
        )
        .then((_) => _load());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
          onPressed: widget.onBack,
        ),
        title: const Text(
          'Messages',
          style: TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: CartzyColors.navy),
            onPressed: _load,
          ),
        ],
      ),
      body: SafeArea(child: _buildBody()),
    );
  }

  Widget _buildBody() {
    if (_isLoading) return const Center(child: CircularProgressIndicator());

    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, color: Colors.redAccent, size: 40),
              const SizedBox(height: 12),
              Text(_error!, textAlign: TextAlign.center, style: const TextStyle(color: CartzyColors.gray)),
              const SizedBox(height: 16),
              ElevatedButton(onPressed: _load, child: const Text('Try Again')),
            ],
          ),
        ),
      );
    }

    if (_threads.isEmpty) {
      return RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          padding: const EdgeInsets.only(top: 100),
          children: const [
            Center(
              child: Column(
                children: [
                  Icon(Icons.chat_bubble_outline, size: 44, color: CartzyColors.gray),
                  SizedBox(height: 12),
                  Text('No conversations yet.', style: TextStyle(color: CartzyColors.gray)),
                ],
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(vertical: 8),
        itemCount: _threads.length,
        separatorBuilder: (context, index) => const Divider(height: 1, color: CartzyColors.border),
        itemBuilder: (context, index) {
          final thread = _threads[index];
          return ListTile(
            leading: CircleAvatar(
              backgroundColor: CartzyColors.coral.withValues(alpha: 0.15),
              child: Text(
                thread.name.isNotEmpty ? thread.name[0].toUpperCase() : '?',
                style: const TextStyle(color: CartzyColors.coral, fontWeight: FontWeight.bold),
              ),
            ),
            title: Text(thread.name, style: const TextStyle(fontWeight: FontWeight.w600, color: CartzyColors.navy)),
            subtitle: Text(
              thread.lastMessage,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: CartzyColors.gray),
            ),
            onTap: () => _openThread(thread),
          );
        },
      ),
    );
  }
}
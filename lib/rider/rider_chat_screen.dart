import 'dart:async';

import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/mock_data.dart';

// ============================================================
// CHAT — one conversation
// ============================================================

class ChatMessage {
  final int id;
  final int senderId;
  final int receiverId;
  final String body;
  final String createdAt;

  ChatMessage({
    required this.id,
    required this.senderId,
    required this.receiverId,
    required this.body,
    required this.createdAt,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: int.tryParse(json['id']?.toString() ?? '') ?? 0,
      senderId: int.tryParse(json['sender_id']?.toString() ?? '') ?? 0,
      receiverId: int.tryParse(json['receiver_id']?.toString() ?? '') ?? 0,
      body: json['body']?.toString() ?? '',
      createdAt: json['created_at']?.toString() ?? '',
    );
  }
}

class RiderChatScreen extends StatefulWidget {
  final String token;
  final int myUserId;
  final int otherUserId;
  final String otherUserName;

  const RiderChatScreen({
    super.key,
    required this.token,
    required this.myUserId,
    required this.otherUserId,
    required this.otherUserName,
  });

  @override
  State<RiderChatScreen> createState() => _RiderChatScreenState();
}

class _RiderChatScreenState extends State<RiderChatScreen> {
  final _messageController = TextEditingController();
  final _scrollController = ScrollController();

  List<ChatMessage> _messages = [];
  bool _isLoading = true;
  bool _isSending = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _load({bool silent = false}) async {
    if (!silent) setState(() => _isLoading = true);

    await Future.delayed(const Duration(milliseconds: 200));

    if (!mounted) return;

    final existing = MockData.chatMessages[widget.otherUserId] ?? [
      ChatMessage(
        id: 1,
        senderId: widget.otherUserId,
        receiverId: widget.myUserId,
        body: 'Hello! I will be waiting for the delivery.',
        createdAt: 'Just now',
      ),
    ];

    setState(() {
      _messages = List.from(existing);
      _isLoading = false;
    });

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.jumpTo(_scrollController.position.maxScrollExtent);
      }
    });
  }

  Future<void> _send() async {
    final text = _messageController.text.trim();
    if (text.isEmpty || _isSending) return;

    setState(() => _isSending = true);
    _messageController.clear();

    await Future.delayed(const Duration(milliseconds: 200));

    final newMsg = ChatMessage(
      id: DateTime.now().millisecondsSinceEpoch,
      senderId: widget.myUserId,
      receiverId: widget.otherUserId,
      body: text,
      createdAt: 'Just now',
    );

    MockData.chatMessages.putIfAbsent(widget.otherUserId, () => []).add(newMsg);

    if (!mounted) return;
    setState(() {
      _messages = List.from(MockData.chatMessages[widget.otherUserId]!);
      _isSending = false;
    });

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        title: Text(
          widget.otherUserName,
          style: const TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold),
        ),
      ),
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _messages.isEmpty
                      ? const Center(
                          child: Text('Say hello 👋', style: TextStyle(color: CartzyColors.gray)),
                        )
                      : ListView.builder(
                          controller: _scrollController,
                          padding: const EdgeInsets.all(16),
                          itemCount: _messages.length,
                          itemBuilder: (context, index) {
                            final message = _messages[index];
                            final isMine = message.senderId == widget.myUserId;
                            return Align(
                              alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
                              child: Container(
                                margin: const EdgeInsets.only(bottom: 10),
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.7),
                                decoration: BoxDecoration(
                                  color: isMine ? CartzyColors.navy : CartzyColors.surface,
                                  borderRadius: BorderRadius.circular(14),
                                  border: isMine ? null : Border.all(color: CartzyColors.border),
                                ),
                                child: Text(
                                  message.body,
                                  style: TextStyle(color: isMine ? Colors.white : CartzyColors.navy),
                                ),
                              ),
                            );
                          },
                        ),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _messageController,
                      textInputAction: TextInputAction.send,
                      onSubmitted: (_) => _send(),
                      decoration: InputDecoration(
                        hintText: 'Type a message...',
                        filled: true,
                        fillColor: CartzyColors.surface,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(24),
                          borderSide: const BorderSide(color: CartzyColors.border),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(24),
                          borderSide: const BorderSide(color: CartzyColors.border),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  IconButton(
                    icon: const Icon(Icons.send, color: CartzyColors.coral),
                    onPressed: _isSending ? null : _send,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
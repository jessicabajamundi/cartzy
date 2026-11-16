import 'package:flutter/material.dart';
import 'rider_chat_list_screen.dart';

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

class RiderChatScreen extends StatelessWidget {
  final String token;
  final int myUserId;
  final int otherUserId;
  final String otherUserName;
  const RiderChatScreen(
      {super.key,
      required this.token,
      required this.myUserId,
      required this.otherUserId,
      required this.otherUserName});
  @override
  Widget build(BuildContext context) => RiderChatListScreen(
      token: token,
      myUserId: myUserId,
      onBack: () => Navigator.of(context).maybePop());
}

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'rider_common_app_bar.dart';

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
  String? _selectedChat; // null = none, 'dispatch' = Cartzy Central Dispatch
  final TextEditingController _msgInputController = TextEditingController();
  final ScrollController _chatScroll = ScrollController();

  final List<Map<String, dynamic>> _dispatchMessages = [
    {
      'sender': 'Cartzy Central Dispatch',
      'isMe': false,
      'text': 'Welcome to Metro South Sector command. Route assignments updated for today.',
      'time': '08:00 AM',
    },
    {
      'sender': 'Cartzy Central Dispatch',
      'isMe': false,
      'text': 'All couriers please confirm merchant pickups before 12:00 PM.',
      'time': '09:15 AM',
    },
  ];

  @override
  void dispose() {
    _msgInputController.dispose();
    _chatScroll.dispose();
    super.dispose();
  }

  void _sendMessage() {
    final text = _msgInputController.text.trim();
    if (text.isEmpty) return;

    setState(() {
      _dispatchMessages.add({
        'sender': 'Me',
        'isMe': true,
        'text': text,
        'time': 'Just now',
      });
      _msgInputController.clear();
    });

    Future.delayed(const Duration(milliseconds: 100), () {
      if (_chatScroll.hasClients) {
        _chatScroll.animateTo(
          _chatScroll.position.maxScrollExtent,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOut,
        );
      }
    });

    // Auto-reply from dispatch
    Future.delayed(const Duration(seconds: 1), () {
      if (!mounted) return;
      setState(() {
        _dispatchMessages.add({
          'sender': 'Cartzy Central Dispatch',
          'isMe': false,
          'text': 'Received. Central dispatch has noted your update. Safe ride!',
          'time': 'Just now',
        });
      });
      Future.delayed(const Duration(milliseconds: 100), () {
        if (_chatScroll.hasClients) {
          _chatScroll.animateTo(
            _chatScroll.position.maxScrollExtent,
            duration: const Duration(milliseconds: 250),
            curve: Curves.easeOut,
          );
        }
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F5),
      body: Column(
        children: [
          RiderCommonAppBar(
            title: 'Chat & Messages',
            sector: 'Metro South Sector',
            onBack: widget.onBack,
          ),
          Expanded(
            child: LayoutBuilder(
              builder: (context, constraints) {
                final isMobile = constraints.maxWidth < 750;

                if (isMobile) {
                  if (_selectedChat != null) {
                    return _buildConversationPane(isMobile: true);
                  }
                  return _buildSidebarPane(isMobile: true);
                }

                return Padding(
                  padding: const EdgeInsets.all(20),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Left Sidebar: Messages & Inquiries
                      SizedBox(
                        width: 340,
                        child: _buildSidebarPane(isMobile: false),
                      ),
                      const SizedBox(width: 16),
                      // Right Content: Empty state or conversation
                      Expanded(
                        child: _buildConversationPane(isMobile: false),
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSidebarPane({required bool isMobile}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Title & Subtitle
          Text(
            'Messages & Inquiries',
            style: GoogleFonts.cormorantGaramond(
              fontSize: 20,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF261E30),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            'Coordinate directly with customers and hub dispatchers',
            style: GoogleFonts.dmSans(
              fontSize: 12,
              color: const Color(0xFF8A7780),
            ),
          ),

          const SizedBox(height: 18),

          // Central Dispatch Tile
          InkWell(
            onTap: () => setState(() => _selectedChat = 'dispatch'),
            borderRadius: BorderRadius.circular(12),
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: _selectedChat == 'dispatch'
                    ? const Color(0xFFFAF7F5)
                    : Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: _selectedChat == 'dispatch'
                      ? const Color(0xFF554B62)
                      : const Color(0xFFEFE8EA),
                ),
              ),
              child: Row(
                children: [
                  Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: const Color(0xFF554B62),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.storefront_outlined,
                      color: Colors.white,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Cartzy Central Dispatch',
                          style: GoogleFonts.dmSans(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF261E30),
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Dispatch notifications & route updates',
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            color: const Color(0xFF8A7780),
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF3ECEF),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      'Hub',
                      style: GoogleFonts.dmSans(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF554B62),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 20),

          // Customer delivery threads section
          Container(
            padding: const EdgeInsets.symmetric(vertical: 8),
            decoration: const BoxDecoration(
              border: Border(
                top: BorderSide(color: Color(0xFFEFE8EA)),
                bottom: BorderSide(color: Color(0xFFEFE8EA)),
              ),
            ),
            child: Text(
              'CUSTOMER DELIVERY THREADS',
              style: GoogleFonts.dmSans(
                fontSize: 10,
                fontWeight: FontWeight.w800,
                letterSpacing: 0.6,
                color: const Color(0xFF8A7780),
              ),
            ),
          ),

          const SizedBox(height: 24),

          // Empty thread notice
          Center(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Text(
                'No active delivery orders currently assigned.',
                textAlign: TextAlign.center,
                style: GoogleFonts.dmSans(
                  fontSize: 12.5,
                  color: const Color(0xFFBAABB0),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildConversationPane({required bool isMobile}) {
    if (_selectedChat == null) {
      // Empty selection state (exact match to screenshot)
      return Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFEFE8EA)),
        ),
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: const BoxDecoration(
                    color: Color(0xFFF3ECEF),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.chat_bubble_outline_rounded,
                    color: Color(0xFF865561),
                    size: 22,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Select a Conversation',
                  style: GoogleFonts.cormorantGaramond(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF261E30),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Choose a customer order thread from the sidebar or reach out to Cartzy Logistics Central Dispatch.',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.dmSans(
                    fontSize: 13,
                    color: const Color(0xFF8A7780),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    // Active Chat Conversation
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      child: Column(
        children: [
          // Chat Header
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
            decoration: const BoxDecoration(
              border: Border(bottom: BorderSide(color: Color(0xFFEFE8EA))),
            ),
            child: Row(
              children: [
                if (isMobile)
                  IconButton(
                    icon: const Icon(Icons.arrow_back, size: 18),
                    onPressed: () => setState(() => _selectedChat = null),
                  ),
                Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: const Color(0xFF554B62),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.storefront, color: Colors.white, size: 16),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Cartzy Central Dispatch',
                        style: GoogleFonts.dmSans(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF261E30),
                        ),
                      ),
                      Text(
                        'Metro South Sorting & Fulfillment Hub',
                        style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFF8A7780)),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Messages List
          Expanded(
            child: ListView.builder(
              controller: _chatScroll,
              padding: const EdgeInsets.all(18),
              itemCount: _dispatchMessages.length,
              itemBuilder: (context, index) {
                final msg = _dispatchMessages[index];
                final isMe = msg['isMe'] as bool;

                return Align(
                  alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    constraints: BoxConstraints(
                      maxWidth: MediaQuery.of(context).size.width * (isMobile ? 0.75 : 0.5),
                    ),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: isMe ? const Color(0xFF46333D) : const Color(0xFFFAF7F5),
                      borderRadius: BorderRadius.circular(12),
                      border: isMe ? null : Border.all(color: const Color(0xFFEFE8EA)),
                    ),
                    child: Column(
                      crossAxisAlignment:
                          isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                      children: [
                        Text(
                          msg['text'] as String,
                          style: GoogleFonts.dmSans(
                            fontSize: 13,
                            color: isMe ? Colors.white : const Color(0xFF261E30),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          msg['time'] as String,
                          style: GoogleFonts.dmSans(
                            fontSize: 10,
                            color: isMe ? Colors.white70 : const Color(0xFF8A7780),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),

          // Input Bar
          Container(
            padding: const EdgeInsets.all(12),
            decoration: const BoxDecoration(
              border: Border(top: BorderSide(color: Color(0xFFEFE8EA))),
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _msgInputController,
                    onSubmitted: (_) => _sendMessage(),
                    decoration: InputDecoration(
                      hintText: 'Type your message to Central Dispatch...',
                      hintStyle: GoogleFonts.dmSans(fontSize: 12.5, color: const Color(0xFFBAABB0)),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(20),
                        borderSide: const BorderSide(color: Color(0xFFEFE8EA)),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(20),
                        borderSide: const BorderSide(color: Color(0xFFEFE8EA)),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(20),
                        borderSide: const BorderSide(color: Color(0xFF46333D)),
                      ),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      isDense: true,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  icon: const Icon(Icons.send_rounded, color: Color(0xFF46333D)),
                  onPressed: _sendMessage,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
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
  final VoidCallback onLogout;

  const RiderChatListScreen({
    super.key,
    required this.token,
    required this.myUserId,
    required this.onBack,
    required this.onLogout,
  });

  @override
  State<RiderChatListScreen> createState() => _RiderChatListScreenState();
}

class _RiderChatListScreenState extends State<RiderChatListScreen> {
  String? _selectedChat = 'jessica'; // 'dispatch' = Cartzy Central Dispatch
  final TextEditingController _msgInputController = TextEditingController();
  final ScrollController _chatScroll = ScrollController();
  final Map<String, List<Map<String, dynamic>>> _customerMessages = {};

  static const List<Map<String, String>> _customerThreads = [
    {
      'id': 'jessica',
      'initials': 'JE',
      'name': 'Jessica Pambago',
      'order': 'ORD-2026-96349',
      'store': 'Logitech Official Store',
      'preview': 'Start coordinating delivery with customer',
    },
    {
      'id': 'eng',
      'initials': 'EN',
      'name': 'Engr. Roberto Dela Cruz',
      'order': 'ORD-MTR-102',
      'store': 'TechZone Official Mall',
      'preview': 'Start coordinating delivery with customer',
    },
    {
      'id': 'patricia',
      'initials': 'DR',
      'name': 'Dra. Patricia Evangelista',
      'order': 'ORD-MTR-103',
      'store': 'TechZone Official Mall',
      'preview': 'Start coordinating delivery with customer',
    },
    {
      'id': 'jerome',
      'initials': 'JE',
      'name': 'Jerome Villafuerte',
      'order': 'ORD-MTR-091',
      'store': 'TechZone Official Mall',
      'preview': 'Start coordinating delivery with customer',
    },
    {
      'id': 'lisa',
      'initials': 'LI',
      'name': 'Lisa Soberano Tan',
      'order': 'ORD-MTR-092',
      'store': 'TechZone Official Mall',
      'preview': 'Start coordinating delivery with customer',
    },
    {
      'id': 'ferdinand',
      'initials': 'CA',
      'name': 'Capt. Ferdinand Cruz',
      'order': 'ORD-MTR-093',
      'store': 'TechZone Official Mall',
      'preview': 'Start coordinating delivery with customer',
    },
    {
      'id': 'juan',
      'initials': 'JU',
      'name': 'Juan Dela Cruz',
      'order': 'ORD-LOG-1000',
      'store': 'TechZone Official Mall',
      'preview': 'Start coordinating delivery with customer',
    },
  ];

  List<Map<String, dynamic>> get _activeMessages {
    if (_selectedChat == 'dispatch') return _dispatchMessages;
    if (_selectedChat == null) return const [];
    return _customerMessages.putIfAbsent(_selectedChat!, () => []);
  }

  Map<String, String>? get _activeThread {
    for (final thread in _customerThreads) {
      if (thread['id'] == _selectedChat) return thread;
    }
    return null;
  }

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

    final messages = _activeMessages;
    final isDispatchChat = _selectedChat == 'dispatch';
    final recipient = _selectedChat == 'dispatch'
        ? 'Cartzy Central Dispatch'
        : (_activeThread?['name'] ?? 'Customer');

    setState(() {
      messages.add({
        'sender': 'Me', 'isMe': true, 'text': text, 'time': 'Just now',
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
        messages.add({
          'sender': recipient,
          'isMe': false,
          'text': isDispatchChat
              ? 'Received. Central dispatch has noted your update. Safe ride!'
              : 'Thanks for the update. I will be ready for the delivery.',
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
            sector: 'Metro South (Laguna & NCR)',
            onBack: widget.onBack,
            onLogout: widget.onLogout,
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
                        width: 270,
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
        borderRadius: BorderRadius.circular(isMobile ? 0 : 14),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      padding: EdgeInsets.all(isMobile ? 14 : 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Messages & Inquiries',
            style: GoogleFonts.cormorantGaramond(
              fontSize: isMobile ? 19 : 17,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF261E30),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            'Coordinate directly with customers and hub dispatchers',
            style: GoogleFonts.dmSans(
              fontSize: 10,
              color: const Color(0xFF8A7780),
            ),
          ),
          const SizedBox(height: 12),
          InkWell(
            onTap: () => setState(() => _selectedChat = 'dispatch'),
            borderRadius: BorderRadius.circular(10),
            child: Container(
              padding: const EdgeInsets.all(9),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: _selectedChat == 'dispatch'
                      ? const Color(0xFFE3D8DC)
                      : const Color(0xFFEFE8EA),
                ),
              ),
              child: Row(
                children: [
                  Container(
                    width: 30,
                    height: 30,
                    decoration: BoxDecoration(
                      color: const Color(0xFF554B62),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.storefront_outlined,
                      color: Colors.white,
                      size: 17,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Cartzy Central Dispatch',
                          style: GoogleFonts.dmSans(
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF261E30),
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Dispatch notifications & route updates',
                          style: GoogleFonts.dmSans(
                            fontSize: 9,
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
                        fontSize: 8,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF554B62),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(vertical: 6),
            decoration: const BoxDecoration(
              border: Border(
                top: BorderSide(color: Color(0xFFEFE8EA)),
                bottom: BorderSide(color: Color(0xFFEFE8EA)),
              ),
            ),
            child: Text(
              'CUSTOMER DELIVERY THREADS',
              style: GoogleFonts.dmSans(
                fontSize: 8,
                fontWeight: FontWeight.w800,
                letterSpacing: 0.6,
                color: const Color(0xFF8A7780),
              ),
            ),
          ),

          Expanded(
            child: ListView.separated(
              padding: const EdgeInsets.only(top: 4),
              itemCount: _customerThreads.length,
              separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFEFE8EA)),
              itemBuilder: (context, index) {
                final thread = _customerThreads[index];
                final id = thread['id']!;
                final selected = _selectedChat == id;
                final messages = _customerMessages[id] ?? const [];
                final preview = messages.isEmpty
                    ? thread['preview']!
                    : messages.last['text']?.toString() ?? thread['preview']!;

                return InkWell(
                  onTap: () => setState(() => _selectedChat = id),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 9),
                    decoration: BoxDecoration(
                      color: selected ? const Color(0xFFFAF7F8) : Colors.white,
                      border: Border(
                        left: BorderSide(
                          color: selected ? const Color(0xFFC18476) : Colors.transparent,
                          width: 2,
                        ),
                      ),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 28,
                          height: 28,
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            color: const Color(0xFFB88378),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            thread['initials']!,
                            style: GoogleFonts.dmSans(
                              color: Colors.white,
                              fontSize: 9,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                thread['name']!,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: GoogleFonts.dmSans(
                                  fontSize: 9,
                                  fontWeight: FontWeight.w700,
                                  color: const Color(0xFF261E30),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${thread['order']} • ${thread['store']}',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: GoogleFonts.dmSans(
                                  fontSize: 8,
                                  color: const Color(0xFF8A7780),
                                ),
                              ),
                              Text(
                                preview,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: GoogleFonts.dmSans(
                                  fontSize: 8,
                                  color: const Color(0xFF554B62),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildConversationPane({required bool isMobile}) {
    final isDispatch = _selectedChat == 'dispatch';
    final thread = _activeThread;
    final title = isDispatch ? 'Cartzy Central Dispatch' : thread?['name'] ?? 'Customer';
    final detail = isDispatch
        ? 'Metro South Sorting & Fulfillment Hub'
        : '${thread?['order'] ?? ''} • ${thread?['store'] ?? ''}';
    final messages = _activeMessages;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(isMobile ? 0 : 14),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      child: Column(
        children: [
          Padding(
            padding: EdgeInsets.fromLTRB(isMobile ? 8 : 12, 8, 10, 8),
            child: Row(
              children: [
                if (isMobile)
                  IconButton(
                    visualDensity: VisualDensity.compact,
                    icon: const Icon(Icons.arrow_back, size: 18),
                    onPressed: () => setState(() => _selectedChat = null),
                  ),
                Container(
                  width: 30,
                  height: 30,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: isDispatch ? const Color(0xFF625774) : const Color(0xFFB88378),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: isDispatch
                      ? const Icon(Icons.storefront_outlined, color: Colors.white, size: 17)
                      : Text(
                          thread?['initials'] ?? 'CU',
                          style: GoogleFonts.dmSans(
                            fontSize: 9,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                          ),
                        ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.dmSans(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF261E30),
                        ),
                      ),
                      Text(
                        detail,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.dmSans(fontSize: 8, color: const Color(0xFF8A7780)),
                      ),
                    ],
                  ),
                ),
                if (!isDispatch)
                  TextButton.icon(
                    onPressed: () => ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Calling $title...')),
                    ),
                    icon: const Icon(Icons.call_outlined, size: 13),
                    label: Text('Call', style: GoogleFonts.dmSans(fontSize: 10)),
                    style: TextButton.styleFrom(foregroundColor: const Color(0xFF9B635A)),
                  ),
              ],
            ),
          ),
          if (!isDispatch)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: const BoxDecoration(
                border: Border(
                  top: BorderSide(color: Color(0xFFEFE8EA)),
                  bottom: BorderSide(color: Color(0xFFEFE8EA)),
                ),
              ),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _statusChip(Icons.local_shipping_outlined, 'On the way', const Color(0xFFD59131)),
                    _statusChip(Icons.location_on_outlined, 'Arrived outside', const Color(0xFFCB668A)),
                    _statusChip(Icons.payments_outlined, 'Prepare COD cash', const Color(0xFF57A66D)),
                    _statusChip(Icons.inventory_2_outlined, 'Package handed over', const Color(0xFFD59131)),
                  ],
                ),
              ),
            ),
          Expanded(
            child: messages.isEmpty
                ? Center(
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.chat_bubble_outline, size: 22, color: Color(0xFFD6D5DF)),
                          const SizedBox(height: 8),
                          Text(
                            'No previous messages with $title.',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.dmSans(fontSize: 9, color: const Color(0xFF9A91A7)),
                          ),
                          const SizedBox(height: 5),
                          Text(
                            'Send an ETA update or ask about building/gate instructions.',
                            textAlign: TextAlign.center,
                            style: GoogleFonts.dmSans(fontSize: 9, color: const Color(0xFF9A91A7)),
                          ),
                        ],
                      ),
                    ),
                  )
                : ListView.builder(
                    controller: _chatScroll,
                    padding: const EdgeInsets.all(14),
                    itemCount: messages.length,
                    itemBuilder: (context, index) {
                      final msg = messages[index];
                      final isMe = msg['isMe'] as bool;
                      return Align(
                        alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                        child: Container(
                          margin: const EdgeInsets.only(bottom: 8),
                          constraints: BoxConstraints(
                            maxWidth: MediaQuery.of(context).size.width * (isMobile ? 0.75 : 0.5),
                          ),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: isMe ? const Color(0xFF46333D) : const Color(0xFFFAF7F5),
                            borderRadius: BorderRadius.circular(12),
                            border: isMe ? null : Border.all(color: const Color(0xFFEFE8EA)),
                          ),
                          child: Column(
                            crossAxisAlignment: isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
                            children: [
                              Text(
                                msg['text'] as String,
                                style: GoogleFonts.dmSans(
                                  fontSize: 12,
                                  color: isMe ? Colors.white : const Color(0xFF261E30),
                                ),
                              ),
                              const SizedBox(height: 3),
                              Text(
                                msg['time'] as String,
                                style: GoogleFonts.dmSans(
                                  fontSize: 9,
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
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 7),
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
                      hintText: isDispatch ? 'Message Central Dispatch...' : 'Write a message to $title...',
                      hintStyle: GoogleFonts.dmSans(fontSize: 9, color: const Color(0xFFBAABB0)),
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
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
                      isDense: true,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  visualDensity: VisualDensity.compact,
                  icon: const Icon(Icons.send_rounded, size: 17, color: Color(0xFF46333D)),
                  onPressed: _sendMessage,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _statusChip(IconData icon, String label, Color color) {
    return Container(
      margin: const EdgeInsets.only(right: 5),
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: const Color(0xFFEFE8EA)),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 9, color: color),
          const SizedBox(width: 3),
          Text(label, style: GoogleFonts.dmSans(fontSize: 8, color: const Color(0xFF554B62))),
        ],
      ),
    );
  }
}

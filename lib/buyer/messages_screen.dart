import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

// ============================================================
// MESSAGES SCREEN  — Buyer inbox / seller chat
// ============================================================

class MessagesScreen extends StatefulWidget {
  final VoidCallback onBack;

  const MessagesScreen({super.key, required this.onBack});

  @override
  State<MessagesScreen> createState() => _MessagesScreenState();
}

class _MessagesScreenState extends State<MessagesScreen> {
  final TextEditingController _searchController = TextEditingController();
  final TextEditingController _replyController  = TextEditingController();

  // Mock conversations – in a real app these come from a service
  final List<Map<String, dynamic>> _conversations = [];
  Map<String, dynamic>? _selectedConversation;
  String _searchQuery = '';

  List<Map<String, dynamic>> get _filtered => _conversations
      .where((c) => (c['store'] as String)
          .toLowerCase()
          .contains(_searchQuery.toLowerCase()))
      .toList();

  // ── Left Panel ──────────────────────────────────────────────
  Widget _leftPanel(bool isWide) {
    return Container(
      width: isWide ? 300 : double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(
          right: isWide
              ? BorderSide(color: CartzyColors.border)
              : BorderSide.none,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Section header
          Padding(
            padding: const EdgeInsets.fromLTRB(18, 18, 18, 10),
            child: Text(
              'Conversations (${_filtered.length})',
              style: GoogleFonts.dmSans(
                fontSize: 13.5,
                fontWeight: FontWeight.w600,
                color: CartzyColors.text,
              ),
            ),
          ),

          // Search bar
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14),
            child: Row(
              children: [
                Expanded(
                  child: Container(
                    height: 38,
                    decoration: BoxDecoration(
                      color: CartzyColors.background,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: CartzyColors.border),
                    ),
                    child: TextField(
                      controller: _searchController,
                      style: GoogleFonts.dmSans(fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'Search store or order…',
                        hintStyle: GoogleFonts.dmSans(
                          fontSize: 13,
                          color: CartzyColors.gray,
                        ),
                        border: InputBorder.none,
                        enabledBorder: InputBorder.none,
                        focusedBorder: InputBorder.none,
                        errorBorder: InputBorder.none,
                        disabledBorder: InputBorder.none,
                        filled: false,
                        contentPadding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 9),
                        isDense: true,
                      ),
                      onChanged: (v) => setState(() => _searchQuery = v),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  height: 38,
                  padding: const EdgeInsets.symmetric(horizontal: 14),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: CartzyColors.border),
                  ),
                  child: Center(
                    child: Text(
                      'Find',
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                        color: CartzyColors.text,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 14),

          // Conversation list / empty
          Expanded(
            child: _filtered.isEmpty
                ? Padding(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 18, vertical: 12),
                    child: Text(
                      'Your conversations will appear here after you place an order.',
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        color: CartzyColors.gray,
                        height: 1.5,
                      ),
                    ),
                  )
                : ListView.separated(
                    itemCount: _filtered.length,
                    separatorBuilder: (_, __) =>
                        Divider(height: 1, color: CartzyColors.border),
                    itemBuilder: (context, i) {
                      final c = _filtered[i];
                      final isSelected = _selectedConversation == c;
                      return InkWell(
                        onTap: () =>
                            setState(() => _selectedConversation = c),
                        child: Container(
                          color: isSelected
                              ? CartzyColors.background
                              : Colors.transparent,
                          padding: const EdgeInsets.symmetric(
                              horizontal: 18, vertical: 12),
                          child: Row(
                            children: [
                              CircleAvatar(
                                radius: 20,
                                backgroundColor:
                                    const Color(0xFFF0EBF4),
                                child: Text(
                                  (c['store'] as String)[0].toUpperCase(),
                                  style: GoogleFonts.dmSans(
                                    color: CartzyColors.navy,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment:
                                      CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      c['store'] ?? '',
                                      style: GoogleFonts.dmSans(
                                        fontWeight: FontWeight.w600,
                                        fontSize: 13.5,
                                        color: CartzyColors.text,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      c['lastMessage'] ?? '',
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: GoogleFonts.dmSans(
                                        fontSize: 12,
                                        color: CartzyColors.gray,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              Text(
                                c['time'] ?? '',
                                style: GoogleFonts.dmSans(
                                  fontSize: 11,
                                  color: CartzyColors.gray,
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

  // ── Right Panel ─────────────────────────────────────────────
  Widget _rightPanel() {
    if (_selectedConversation == null) {
      return Expanded(
        child: Container(
          color: Colors.white,
          child: Center(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  'Your conversations start here',
                  style: GoogleFonts.dmSans(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: CartzyColors.text,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Choose an order conversation to read\nmessages and send a reply.',
                  style: GoogleFonts.dmSans(
                    fontSize: 13,
                    color: CartzyColors.gray,
                    height: 1.5,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ),
      );
    }

    final messages =
        (_selectedConversation!['messages'] as List?)?.cast<Map>() ?? [];

    return Expanded(
      child: Column(
        children: [
          // Chat header
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
            decoration: BoxDecoration(
              color: Colors.white,
              border:
                  Border(bottom: BorderSide(color: CartzyColors.border)),
            ),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 18,
                  backgroundColor: const Color(0xFFF0EBF4),
                  child: Text(
                    (_selectedConversation!['store'] as String)[0]
                        .toUpperCase(),
                    style: GoogleFonts.dmSans(
                      color: CartzyColors.navy,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Text(
                  _selectedConversation!['store'] ?? '',
                  style: GoogleFonts.dmSans(
                    fontWeight: FontWeight.w600,
                    fontSize: 14,
                    color: CartzyColors.text,
                  ),
                ),
              ],
            ),
          ),

          // Messages
          Expanded(
            child: messages.isEmpty
                ? Center(
                    child: Text(
                      'No messages yet.',
                      style: GoogleFonts.dmSans(
                          color: CartzyColors.gray, fontSize: 13),
                    ),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(20),
                    itemCount: messages.length,
                    itemBuilder: (context, i) {
                      final m = messages[i];
                      final isMe = m['isMe'] == true;
                      return Align(
                        alignment: isMe
                            ? Alignment.centerRight
                            : Alignment.centerLeft,
                        child: Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 10),
                          constraints: const BoxConstraints(maxWidth: 320),
                          decoration: BoxDecoration(
                            color: isMe
                                ? CartzyColors.navy
                                : CartzyColors.background,
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: Text(
                            m['text'] ?? '',
                            style: GoogleFonts.dmSans(
                              fontSize: 13,
                              color: isMe
                                  ? Colors.white
                                  : CartzyColors.text,
                            ),
                          ),
                        ),
                      );
                    },
                  ),
          ),

          // Reply bar
          Container(
            padding:
                const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(top: BorderSide(color: CartzyColors.border)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Container(
                    decoration: BoxDecoration(
                      color: CartzyColors.background,
                      borderRadius: BorderRadius.circular(22),
                      border: Border.all(color: CartzyColors.border),
                    ),
                    child: TextField(
                      controller: _replyController,
                      style: GoogleFonts.dmSans(fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'Write a reply…',
                        hintStyle: GoogleFonts.dmSans(
                            color: CartzyColors.gray, fontSize: 13),
                        border: InputBorder.none,
                        enabledBorder: InputBorder.none,
                        focusedBorder: InputBorder.none,
                        errorBorder: InputBorder.none,
                        disabledBorder: InputBorder.none,
                        filled: false,
                        contentPadding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 10),
                        isDense: true,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                GestureDetector(
                  onTap: () {
                    if (_replyController.text.trim().isEmpty) return;
                    setState(() {
                      (_selectedConversation!['messages'] as List?)?.add({
                        'text': _replyController.text.trim(),
                        'isMe': true,
                      });
                      _replyController.clear();
                    });
                  },
                  child: Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: CartzyColors.navy,
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.send_rounded,
                        color: Colors.white, size: 18),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── Build ───────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final isWide = MediaQuery.of(context).size.width > 700;

    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Column(
          children: [
            // ── Page Header ──────────────────────────────────
            Container(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 18),
              decoration: BoxDecoration(
                color: CartzyColors.background,
                border:
                    Border(bottom: BorderSide(color: CartzyColors.border)),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  GestureDetector(
                    onTap: widget.onBack,
                    child: Container(
                      margin: const EdgeInsets.only(top: 2, right: 14),
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: CartzyColors.border),
                      ),
                      child: const Icon(Icons.arrow_back,
                          size: 18, color: CartzyColors.navy),
                    ),
                  ),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Messages',
                          style: GoogleFonts.dmSans(
                            fontSize: 26,
                            fontWeight: FontWeight.w800,
                            color: CartzyColors.text,
                            height: 1.1,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Talk to your seller about your order.',
                          style: GoogleFonts.dmSans(
                            fontSize: 12.5,
                            color: CartzyColors.gray,
                          ),
                        ),
                      ],
                    ),
                  ),
                  // Action chips
                  Row(
                    children: [
                      _chip('Back to My Orders', onTap: widget.onBack),
                      const SizedBox(width: 8),
                      _chip('Refresh inbox',
                          onTap: () => setState(() {})),
                    ],
                  ),
                ],
              ),
            ),

            // ── Body ─────────────────────────────────────────
            Expanded(
              child: Container(
                margin: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: CartzyColors.border),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.03),
                      blurRadius: 16,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(18),
                  child: isWide
                      ? Row(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            _leftPanel(true),
                            _rightPanel(),
                          ],
                        )
                      : _selectedConversation == null
                          ? _leftPanel(false)
                          : Column(
                              children: [_rightPanel()],
                            ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _chip(String label, {required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 7),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: CartzyColors.border),
        ),
        child: Text(
          label,
          style: GoogleFonts.dmSans(
            fontSize: 12,
            fontWeight: FontWeight.w500,
            color: CartzyColors.text,
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _searchController.dispose();
    _replyController.dispose();
    super.dispose();
  }
}

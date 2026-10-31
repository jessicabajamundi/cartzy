import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/message_service.dart';

// ============================================================
// MESSAGES SCREEN — Fully functional buyer inbox & seller chat
// ============================================================

class MessagesScreen extends StatefulWidget {
  final VoidCallback onBack;
  final int? initialThreadId;
  final String? initialThreadType;
  final int? initialProductId;
  final String? initialStoreName;

  const MessagesScreen({
    super.key,
    required this.onBack,
    this.initialThreadId,
    this.initialThreadType,
    this.initialProductId,
    this.initialStoreName,
  });

  @override
  State<MessagesScreen> createState() => _MessagesScreenState();
}

class _MessagesScreenState extends State<MessagesScreen> {
  final TextEditingController _searchController = TextEditingController();
  final TextEditingController _replyController = TextEditingController();
  final ScrollController _messagesScrollController = ScrollController();

  List<ConversationThread> _conversations = [];
  ConversationThread? _selectedThread;
  List<ChatMessage> _messages = [];

  bool _isLoadingConversations = true;
  bool _isLoadingMessages = false;
  bool _isSending = false;
  bool _filterUnreadOnly = false;
  String _searchQuery = '';

  Timer? _pollingTimer;

  List<ConversationThread> get _filteredConversations {
    return _conversations.where((c) {
      if (_filterUnreadOnly && c.unreadCount == 0) return false;
      if (_searchQuery.trim().isEmpty) return true;
      final q = _searchQuery.toLowerCase();
      final storeMatch = c.store.toLowerCase().contains(q);
      final productMatch = (c.productName ?? '').toLowerCase().contains(q);
      final orderMatch = (c.orderReference ?? '').toLowerCase().contains(q);
      final msgMatch = c.lastMessage.toLowerCase().contains(q);
      return storeMatch || productMatch || orderMatch || msgMatch;
    }).toList();
  }

  @override
  void initState() {
    super.initState();
    _loadConversations();
    _startPolling();
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _searchController.dispose();
    _replyController.dispose();
    _messagesScrollController.dispose();
    super.dispose();
  }

  void _startPolling() {
    _pollingTimer = Timer.periodic(const Duration(seconds: 4), (_) {
      if (!mounted) return;
      if (_selectedThread != null) {
        _refreshActiveThreadSilently();
      } else {
        _loadConversationsSilently();
      }
    });
  }

  Future<void> _loadConversations() async {
    setState(() => _isLoadingConversations = true);
    await _loadConversationsSilently();
    if (mounted) {
      setState(() => _isLoadingConversations = false);
      _checkInitialTarget();
    }
  }

  Future<void> _loadConversationsSilently() async {
    final list = await MessageService.fetchConversations();
    if (mounted) {
      setState(() {
        _conversations = list;
      });
    }
  }

  Future<void> _checkInitialTarget() async {
    if (widget.initialProductId != null) {
      // Inquire about product
      setState(() => _isLoadingMessages = true);
      final res = await MessageService.inquire(productId: widget.initialProductId);
      if (mounted && res != null && res['thread'] != null) {
        final t = res['thread'] as Map<String, dynamic>;
        final thread = ConversationThread(
          id: t['id'] as int? ?? 0,
          type: t['type']?.toString() ?? 'inquiry',
          key: 'inquiry-${t['id']}',
          store: t['store']?.toString() ?? widget.initialStoreName ?? 'Store',
          productId: widget.initialProductId,
          productName: t['product_name']?.toString(),
          productImage: t['product_image']?.toString(),
          avatar: t['avatar']?.toString(),
          lastMessage: '',
          time: 'Just now',
          unreadCount: 0,
        );
        setState(() {
          _selectedThread = thread;
          _messages = (res['messages'] as List<ChatMessage>?) ?? [];
          _isLoadingMessages = false;
        });
        _scrollToBottom();
      } else if (mounted) {
        setState(() => _isLoadingMessages = false);
      }
    } else if (widget.initialThreadId != null) {
      final match = _conversations.firstWhere(
        (c) =>
            c.id == widget.initialThreadId &&
            c.type == (widget.initialThreadType ?? c.type),
        orElse: () => _conversations.isNotEmpty
            ? _conversations.first
            : ConversationThread(
                id: widget.initialThreadId!,
                type: widget.initialThreadType ?? 'inquiry',
                key: '${widget.initialThreadType}-${widget.initialThreadId}',
                store: 'Store',
                lastMessage: '',
                time: '',
                unreadCount: 0,
              ),
      );
      _selectThread(match);
    }
  }

  Future<void> _selectThread(ConversationThread thread) async {
    setState(() {
      _selectedThread = thread;
      _isLoadingMessages = true;
      _messages = [];
    });

    final res = await MessageService.fetchThread(
      type: thread.type,
      id: thread.id,
    );

    if (mounted) {
      setState(() {
        _messages = (res['messages'] as List<ChatMessage>?) ?? [];
        _isLoadingMessages = false;
      });
      // Mark read in local thread counter
      final idx = _conversations.indexWhere((c) => c.key == thread.key);
      if (idx != -1) {
        final current = _conversations[idx];
        _conversations[idx] = ConversationThread(
          id: current.id,
          type: current.type,
          key: current.key,
          store: current.store,
          sellerId: current.sellerId,
          productId: current.productId,
          productName: current.productName,
          productImage: current.productImage,
          orderReference: current.orderReference,
          orderId: current.orderId,
          avatar: current.avatar,
          lastMessage: current.lastMessage,
          lastMessageAt: current.lastMessageAt,
          time: current.time,
          unreadCount: 0,
        );
      }
      _scrollToBottom();
    }
  }

  Future<void> _refreshActiveThreadSilently() async {
    if (_selectedThread == null) return;
    final res = await MessageService.fetchThread(
      type: _selectedThread!.type,
      id: _selectedThread!.id,
    );
    if (mounted) {
      final newMsgs = (res['messages'] as List<ChatMessage>?) ?? [];
      if (newMsgs.length != _messages.length ||
          (newMsgs.isNotEmpty && _messages.isNotEmpty && newMsgs.last.id != _messages.last.id)) {
        setState(() {
          _messages = newMsgs;
        });
        _scrollToBottom();
      }
    }
  }

  Future<void> _sendMessage() async {
    final text = _replyController.text.trim();
    if (text.isEmpty || _selectedThread == null || _isSending) return;

    _replyController.clear();
    setState(() => _isSending = true);

    // Optimistic UI insertion
    final tempMsg = ChatMessage(
      id: DateTime.now().millisecondsSinceEpoch,
      body: text,
      senderId: 0,
      senderName: 'You',
      isMe: true,
      time: 'Just now',
      date: 'Today',
      status: 'sending',
    );

    setState(() {
      _messages.add(tempMsg);
    });
    _scrollToBottom();

    final sent = await MessageService.sendMessage(
      type: _selectedThread!.type,
      id: _selectedThread!.id,
      message: text,
    );

    if (mounted) {
      setState(() {
        _isSending = false;
        if (sent != null) {
          final idx = _messages.indexWhere((m) => m.id == tempMsg.id);
          if (idx != -1) {
            _messages[idx] = sent;
          }
        }
      });
      _scrollToBottom();
      _loadConversationsSilently();
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_messagesScrollController.hasClients) {
        _messagesScrollController.animateTo(
          _messagesScrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOut,
        );
      }
    });
  }

  // ────────────────────────────────────────────────────────────
  // BUILD
  // ────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final isWide = MediaQuery.of(context).size.width > 700;

    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Column(
          children: [
            // Page Header
            _buildPageHeader(),

            // Body
            Expanded(
              child: isWide ? _buildWideLayout() : _buildMobileLayout(),
            ),
          ],
        ),
      ),
    );
  }

  // ────────────────────────────────────────────────────────────
  // PAGE HEADER
  // ────────────────────────────────────────────────────────────
  Widget _buildPageHeader() {
    final isChatOpen = _selectedThread != null;

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        border: Border(
          bottom: BorderSide(color: CartzyColors.border.withValues(alpha: 0.8)),
        ),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 6,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          // Back button
          GestureDetector(
            onTap: () {
              if (isChatOpen) {
                setState(() => _selectedThread = null);
              } else {
                widget.onBack();
              }
            },
            child: Container(
              padding: const EdgeInsets.all(7),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: CartzyColors.border),
              ),
              child: const Icon(
                Icons.arrow_back_ios_new_rounded,
                size: 16,
                color: CartzyColors.navy,
              ),
            ),
          ),
          const SizedBox(width: 12),

          // Header title & info
          if (isChatOpen) ...[
            _buildAvatar(_selectedThread!.avatar, _selectedThread!.store, 36),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    _selectedThread!.store,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.dmSans(
                      fontSize: 14.5,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.navy,
                    ),
                  ),
                  Text(
                    _selectedThread!.type == 'order'
                        ? (_selectedThread!.orderReference ?? 'Order conversation')
                        : (_selectedThread!.productName ?? 'Store inquiry'),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.dmSans(
                      fontSize: 11.5,
                      color: CartzyColors.grayMid,
                    ),
                  ),
                ],
              ),
            ),
          ] else ...[
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'MESSAGES',
                    style: GoogleFonts.dmSans(
                      fontSize: 10.5,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 1.2,
                      color: CartzyColors.rose700,
                    ),
                  ),
                  Text(
                    'Store Conversations',
                    style: GoogleFonts.playfairDisplay(
                      fontSize: 17,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.navy,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  // ────────────────────────────────────────────────────────────
  // MOBILE VIEW
  // ────────────────────────────────────────────────────────────
  Widget _buildMobileLayout() {
    if (_selectedThread != null) {
      return _buildChatPanel();
    }
    return _buildConversationListPanel();
  }

  // ────────────────────────────────────────────────────────────
  // WIDE VIEW (TABLET / DESKTOP)
  // ────────────────────────────────────────────────────────────
  Widget _buildWideLayout() {
    return Row(
      children: [
        SizedBox(
          width: 340,
          child: _buildConversationListPanel(),
        ),
        const VerticalDivider(width: 1, color: CartzyColors.border),
        Expanded(
          child: _selectedThread != null
              ? _buildChatPanel()
              : _buildEmptySelectPrompt(),
        ),
      ],
    );
  }

  // ────────────────────────────────────────────────────────────
  // CONVERSATION LIST PANEL
  // ────────────────────────────────────────────────────────────
  Widget _buildConversationListPanel() {
    return Column(
      children: [
        // Search & Filter Bar
        Container(
          padding: const EdgeInsets.fromLTRB(14, 12, 14, 10),
          color: CartzyColors.surface,
          child: Column(
            children: [
              Container(
                height: 38,
                decoration: BoxDecoration(
                  color: const Color(0xFFF4F2F5),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: CartzyColors.border),
                ),
                child: Row(
                  children: [
                    const SizedBox(width: 12),
                    const Icon(
                      Icons.search_rounded,
                      size: 18,
                      color: CartzyColors.brandMuted,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: _searchController,
                        onChanged: (v) => setState(() => _searchQuery = v),
                        style: GoogleFonts.dmSans(
                          fontSize: 13,
                          color: CartzyColors.navy,
                        ),
                        decoration: InputDecoration(
                          hintText: 'Search store, order, or item...',
                          hintStyle: GoogleFonts.dmSans(
                            fontSize: 12.5,
                            color: CartzyColors.grayMid,
                          ),
                          border: InputBorder.none,
                          isDense: true,
                          contentPadding:
                              const EdgeInsets.symmetric(vertical: 9),
                        ),
                      ),
                    ),
                    if (_searchQuery.isNotEmpty)
                      IconButton(
                        icon: const Icon(
                          Icons.close_rounded,
                          size: 16,
                          color: CartzyColors.grayMid,
                        ),
                        onPressed: () {
                          _searchController.clear();
                          setState(() => _searchQuery = '');
                        },
                        splashRadius: 16,
                      )
                    else
                      const SizedBox(width: 8),
                  ],
                ),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Text(
                    '${_filteredConversations.length} conversation${_filteredConversations.length == 1 ? '' : 's'}',
                    style: GoogleFonts.dmSans(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                      color: CartzyColors.grayMid,
                    ),
                  ),
                  const Spacer(),
                  InkWell(
                    borderRadius: BorderRadius.circular(12),
                    onTap: () => setState(() => _filterUnreadOnly = !_filterUnreadOnly),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 3.5),
                      decoration: BoxDecoration(
                        color: _filterUnreadOnly
                            ? CartzyColors.rose50Warm
                            : Colors.transparent,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: _filterUnreadOnly
                              ? CartzyColors.rose200
                              : CartzyColors.border,
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            _filterUnreadOnly
                                ? Icons.mark_email_unread_rounded
                                : Icons.mark_email_unread_outlined,
                            size: 13,
                            color: _filterUnreadOnly
                                ? CartzyColors.rose700
                                : CartzyColors.grayMid,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            'Unread only',
                            style: GoogleFonts.dmSans(
                              fontSize: 11,
                              fontWeight: _filterUnreadOnly
                                  ? FontWeight.bold
                                  : FontWeight.w500,
                              color: _filterUnreadOnly
                                  ? CartzyColors.rose700
                                  : CartzyColors.grayMid,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),

        // List View
        Expanded(
          child: _isLoadingConversations
              ? const Center(
                  child: CircularProgressIndicator(
                    color: CartzyColors.brandDark,
                  ),
                )
              : _filteredConversations.isEmpty
                  ? _buildEmptyConversations()
                  : RefreshIndicator(
                      color: CartzyColors.brandDark,
                      onRefresh: _loadConversations,
                      child: ListView.separated(
                        itemCount: _filteredConversations.length,
                        separatorBuilder: (_, __) => Divider(
                          height: 1,
                          indent: 68,
                          color: CartzyColors.border.withValues(alpha: 0.5),
                        ),
                        itemBuilder: (context, index) {
                          final thread = _filteredConversations[index];
                          final isSelected = _selectedThread?.key == thread.key;

                          return InkWell(
                            onTap: () => _selectThread(thread),
                            child: Container(
                              color: isSelected
                                  ? const Color(0xFFF1EEF5)
                                  : (thread.unreadCount > 0
                                      ? const Color(0xFFFFF9F8)
                                      : Colors.transparent),
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 14, vertical: 12),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Avatar
                                  _buildAvatar(thread.avatar, thread.store, 44),
                                  const SizedBox(width: 12),

                                  // Thread info
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          mainAxisAlignment:
                                              MainAxisAlignment.spaceBetween,
                                          children: [
                                            Expanded(
                                              child: Text(
                                                thread.store,
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                                style: GoogleFonts.dmSans(
                                                  fontSize: 13.5,
                                                  fontWeight: thread.unreadCount > 0
                                                      ? FontWeight.bold
                                                      : FontWeight.w600,
                                                  color: CartzyColors.navy,
                                                ),
                                              ),
                                            ),
                                            const SizedBox(width: 6),
                                            Text(
                                              thread.time,
                                              style: GoogleFonts.dmSans(
                                                fontSize: 11,
                                                color: thread.unreadCount > 0
                                                    ? CartzyColors.rose700
                                                    : CartzyColors.grayMid,
                                                fontWeight: thread.unreadCount > 0
                                                    ? FontWeight.bold
                                                    : FontWeight.normal,
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 2),

                                        // Subtitle (Order ref or Product name)
                                        Row(
                                          children: [
                                            Icon(
                                              thread.type == 'order'
                                                  ? Icons.receipt_long_rounded
                                                  : Icons.inventory_2_outlined,
                                              size: 13,
                                              color: CartzyColors.rose700,
                                            ),
                                            const SizedBox(width: 4),
                                            Expanded(
                                              child: Text(
                                                thread.type == 'order'
                                                    ? (thread.orderReference ?? 'Order')
                                                    : (thread.productName ?? 'Store inquiry'),
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                                style: GoogleFonts.dmSans(
                                                  fontSize: 11.5,
                                                  fontWeight: FontWeight.w500,
                                                  color: CartzyColors.brandDeep,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 4),

                                        // Preview & unread badge
                                        Row(
                                          children: [
                                            Expanded(
                                              child: Text(
                                                thread.lastMessage,
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                                style: GoogleFonts.dmSans(
                                                  fontSize: 12.5,
                                                  fontWeight: thread.unreadCount > 0
                                                      ? FontWeight.w600
                                                      : FontWeight.normal,
                                                  color: thread.unreadCount > 0
                                                      ? CartzyColors.navy
                                                      : CartzyColors.grayMid,
                                                ),
                                              ),
                                            ),
                                            if (thread.unreadCount > 0) ...[
                                              const SizedBox(width: 6),
                                              Container(
                                                padding:
                                                    const EdgeInsets.symmetric(
                                                        horizontal: 6,
                                                        vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: CartzyColors.rose700,
                                                  borderRadius:
                                                      BorderRadius.circular(10),
                                                ),
                                                child: Text(
                                                  '${thread.unreadCount}',
                                                  style: GoogleFonts.dmSans(
                                                    color: Colors.white,
                                                    fontSize: 10.5,
                                                    fontWeight: FontWeight.bold,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ],
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
        ),
      ],
    );
  }

  // ────────────────────────────────────────────────────────────
  // CHAT PANEL
  // ────────────────────────────────────────────────────────────
  Widget _buildChatPanel() {
    return Column(
      children: [
        // Product context banner (if inquiry with product)
        if (_selectedThread?.productName != null) ...[
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: const Color(0xFFF7F5F8),
              border: Border(
                bottom: BorderSide(
                    color: CartzyColors.border.withValues(alpha: 0.6)),
              ),
            ),
            child: Row(
              children: [
                if (_selectedThread?.productImage != null)
                  ClipRRect(
                    borderRadius: BorderRadius.circular(6),
                    child: Image.network(
                      _selectedThread!.productImage!,
                      width: 32,
                      height: 32,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => const Icon(
                        Icons.image_not_supported_rounded,
                        size: 20,
                        color: CartzyColors.grayMid,
                      ),
                    ),
                  )
                else
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: CartzyColors.brand100,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Icon(
                      Icons.inventory_2_outlined,
                      size: 16,
                      color: CartzyColors.brandDark,
                    ),
                  ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Product Inquiry',
                        style: GoogleFonts.dmSans(
                          fontSize: 10.5,
                          fontWeight: FontWeight.bold,
                          color: CartzyColors.rose700,
                        ),
                      ),
                      Text(
                        _selectedThread!.productName!,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: CartzyColors.navy,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],

        // Messages list
        Expanded(
          child: _isLoadingMessages
              ? const Center(
                  child: CircularProgressIndicator(
                    color: CartzyColors.brandDark,
                  ),
                )
              : _messages.isEmpty
                  ? _buildEmptyChatMessages()
                  : ListView.builder(
                      controller: _messagesScrollController,
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 14),
                      itemCount: _messages.length,
                      itemBuilder: (context, index) {
                        final msg = _messages[index];
                        final showDateHeader = index == 0 ||
                            msg.date != _messages[index - 1].date;

                        return Column(
                          children: [
                            if (showDateHeader && msg.date.isNotEmpty)
                              Padding(
                                padding:
                                    const EdgeInsets.symmetric(vertical: 12),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 10, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFEFECEF),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Text(
                                    msg.date,
                                    style: GoogleFonts.dmSans(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w500,
                                      color: CartzyColors.grayMid,
                                    ),
                                  ),
                                ),
                              ),
                            _buildChatBubble(msg),
                          ],
                        );
                      },
                    ),
        ),

        // Message Input Field
        _buildChatInput(),
      ],
    );
  }

  // ────────────────────────────────────────────────────────────
  // CHAT BUBBLE
  // ────────────────────────────────────────────────────────────
  Widget _buildChatBubble(ChatMessage msg) {
    final isMe = msg.isMe;

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Align(
        alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
        child: ConstrainedBox(
          constraints: BoxConstraints(
            maxWidth: MediaQuery.of(context).size.width * 0.76,
          ),
          child: Container(
            padding: const EdgeInsets.fromLTRB(14, 9, 14, 7),
            decoration: BoxDecoration(
              color: isMe ? CartzyColors.navy : const Color(0xFFF1EFF4),
              borderRadius: BorderRadius.only(
                topLeft: const Radius.circular(16),
                topRight: const Radius.circular(16),
                bottomLeft: Radius.circular(isMe ? 16 : 4),
                bottomRight: Radius.circular(isMe ? 4 : 16),
              ),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x0A000000),
                  blurRadius: 4,
                  offset: Offset(0, 1),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment:
                  isMe ? CrossAxisAlignment.end : CrossAxisAlignment.start,
              children: [
                Text(
                  msg.body,
                  style: GoogleFonts.dmSans(
                    fontSize: 13.5,
                    color: isMe ? Colors.white : CartzyColors.navy,
                    height: 1.35,
                  ),
                ),
                const SizedBox(height: 3),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      msg.time,
                      style: GoogleFonts.dmSans(
                        fontSize: 10,
                        color: isMe
                            ? Colors.white.withValues(alpha: 0.7)
                            : CartzyColors.grayMid,
                      ),
                    ),
                    if (isMe) ...[
                      const SizedBox(width: 4),
                      Icon(
                        msg.status == 'read'
                            ? Icons.done_all_rounded
                            : (msg.status == 'delivered'
                                ? Icons.done_all_rounded
                                : Icons.done_rounded),
                        size: 13,
                        color: msg.status == 'read'
                            ? CartzyColors.rose200
                            : Colors.white.withValues(alpha: 0.7),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ────────────────────────────────────────────────────────────
  // CHAT INPUT
  // ────────────────────────────────────────────────────────────
  Widget _buildChatInput() {
    return Container(
      padding: const EdgeInsets.fromLTRB(12, 8, 12, 10),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        border: Border(
          top: BorderSide(color: CartzyColors.border.withValues(alpha: 0.8)),
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Expanded(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14),
              decoration: BoxDecoration(
                color: const Color(0xFFF4F2F5),
                borderRadius: BorderRadius.circular(22),
                border: Border.all(color: CartzyColors.border),
              ),
              child: TextField(
                controller: _replyController,
                minLines: 1,
                maxLines: 4,
                textCapitalization: TextCapitalization.sentences,
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  color: CartzyColors.navy,
                ),
                decoration: InputDecoration(
                  hintText: 'Type a message to ${_selectedThread?.store ?? 'store'}...',
                  hintStyle: GoogleFonts.dmSans(
                    fontSize: 13,
                    color: CartzyColors.grayMid,
                  ),
                  border: InputBorder.none,
                  isDense: true,
                  contentPadding: const EdgeInsets.symmetric(vertical: 10),
                ),
                onSubmitted: (_) => _sendMessage(),
              ),
            ),
          ),
          const SizedBox(width: 8),
          GestureDetector(
            onTap: _sendMessage,
            child: Container(
              width: 40,
              height: 40,
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [CartzyColors.brandDark, CartzyColors.rose700],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.send_rounded,
                color: Colors.white,
                size: 18,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ────────────────────────────────────────────────────────────
  // EMPTY STATES & AVATARS
  // ────────────────────────────────────────────────────────────
  Widget _buildAvatar(String? url, String name, double size) {
    if (url != null && url.isNotEmpty) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(size / 2),
        child: Image.network(
          url,
          width: size,
          height: size,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _buildFallbackAvatar(name, size),
        ),
      );
    }
    return _buildFallbackAvatar(name, size);
  }

  Widget _buildFallbackAvatar(String name, double size) {
    final initials = name.trim().split(' ').map((p) => p.isNotEmpty ? p[0] : '').take(2).join();

    return Container(
      width: size,
      height: size,
      decoration: const BoxDecoration(
        shape: BoxShape.circle,
        gradient: LinearGradient(
          colors: [CartzyColors.brand50Lavender, CartzyColors.rose50Warm],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      alignment: Alignment.center,
      child: Text(
        initials.isNotEmpty ? initials.toUpperCase() : 'S',
        style: GoogleFonts.dmSans(
          fontSize: size * 0.38,
          fontWeight: FontWeight.bold,
          color: CartzyColors.brandDark,
        ),
      ),
    );
  }

  Widget _buildEmptyConversations() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: const BoxDecoration(
                color: Color(0xFFF1EFF4),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.chat_bubble_outline_rounded,
                size: 30,
                color: CartzyColors.brandDeep,
              ),
            ),
            const SizedBox(height: 14),
            Text(
              'No conversations yet',
              style: GoogleFonts.playfairDisplay(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'When you chat with sellers from product pages or ask about your orders, your messages will appear here.',
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(
                fontSize: 12.5,
                color: CartzyColors.grayMid,
                height: 1.4,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyChatMessages() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 52,
              height: 52,
              decoration: const BoxDecoration(
                color: Color(0xFFF1EFF4),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.waving_hand_rounded,
                size: 26,
                color: CartzyColors.rose700,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'Say hello to ${_selectedThread?.store ?? 'the store'}!',
              style: GoogleFonts.playfairDisplay(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              'Ask questions about product availability, shipping, or your order.',
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(
                fontSize: 12.5,
                color: CartzyColors.grayMid,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptySelectPrompt() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(
            Icons.forum_outlined,
            size: 48,
            color: CartzyColors.brandMuted,
          ),
          const SizedBox(height: 12),
          Text(
            'Select a conversation',
            style: GoogleFonts.playfairDisplay(
              fontSize: 17,
              fontWeight: FontWeight.bold,
              color: CartzyColors.navy,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Choose an inquiry or order on the left to start chatting.',
            style: GoogleFonts.dmSans(
              fontSize: 13,
              color: CartzyColors.grayMid,
            ),
          ),
        ],
      ),
    );
  }
}

import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/message_service.dart';
import 'package:cartzy/services/follow_store_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/buyer/messages_screen.dart';
import 'package:cartzy/products/official_store_screen.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';

class InboxScreen extends StatefulWidget {
  final VoidCallback? onBack;
  final VoidCallback? onHomeClick;
  final VoidCallback? onCartClick;
  final VoidCallback? onSaleClick;
  final VoidCallback? onProfileClick;
  final bool isGuest;
  final int initialTab; // 0 = Messages, 1 = Followed Stores

  const InboxScreen({
    super.key,
    this.onBack,
    this.onHomeClick,
    this.onCartClick,
    this.onSaleClick,
    this.onProfileClick,
    this.isGuest = false,
    this.initialTab = 0,
  });

  @override
  State<InboxScreen> createState() => _InboxScreenState();
}

class _InboxScreenState extends State<InboxScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabController;
  final TextEditingController _searchController = TextEditingController();

  List<ConversationThread> _conversations = [];
  bool _isLoadingConversations = true;
  String _searchQuery = '';
  bool _filterUnreadOnly = false;
  Timer? _pollingTimer;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: 2,
      vsync: this,
      initialIndex: widget.initialTab.clamp(0, 1),
    );
    _tabController.addListener(() {
      if (mounted) setState(() {});
    });

    FollowStoreService.instance.addListener(_onFollowedStoresChanged);
    _loadConversations();
    _startPolling();
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    _tabController.dispose();
    _searchController.dispose();
    FollowStoreService.instance.removeListener(_onFollowedStoresChanged);
    super.dispose();
  }

  void _onFollowedStoresChanged() {
    if (mounted) setState(() {});
  }

  void _startPolling() {
    _pollingTimer = Timer.periodic(const Duration(seconds: 8), (_) {
      if (mounted && AuthService.isAuthenticated) {
        _loadConversations(silent: true);
      }
    });
  }

  Future<void> _loadConversations({bool silent = false}) async {
    if (!AuthService.isAuthenticated) {
      if (mounted) {
        setState(() {
          _conversations = [];
          _isLoadingConversations = false;
        });
      }
      return;
    }

    if (!silent && mounted) {
      setState(() => _isLoadingConversations = true);
    }

    try {
      final list = await MessageService.fetchConversations();
      if (mounted) {
        setState(() {
          _conversations = list;
          _isLoadingConversations = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoadingConversations = false);
      }
    }
  }

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

  List<FollowedStore> get _filteredFollowedStores {
    final stores = FollowStoreService.instance.followedStores;
    if (_searchQuery.trim().isEmpty) return stores;
    final q = _searchQuery.toLowerCase();
    return stores.where((s) {
      return s.name.toLowerCase().contains(q) ||
          s.slug.toLowerCase().contains(q) ||
          s.location.toLowerCase().contains(q);
    }).toList();
  }

  void _openConversation(ConversationThread thread) {
    Navigator.of(context)
        .push(
      MaterialPageRoute(
        builder: (_) => MessagesScreen(
          onBack: () => Navigator.of(context).pop(),
          initialThreadId: thread.id,
          initialThreadType: thread.type,
          initialStoreName: thread.store,
        ),
      ),
    )
        .then((_) {
      _loadConversations(silent: true);
    });
  }

  void _openStoreChat(FollowedStore store) {
    final sellerId = int.tryParse(store.id);
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MessagesScreen(
          onBack: () => Navigator.of(context).pop(),
          initialSellerId: sellerId,
          initialStoreName: store.name,
          initialStoreAvatar: store.logo,
        ),
      ),
    );
  }

  void _openStoreFront(FollowedStore store) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => OfficialStoreScreen(
          sellerSlug: store.slug.isNotEmpty ? store.slug : store.id,
          sellerName: store.name,
          sellerLogoUrl: store.logo,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final unreadTotal = MessageService.unreadCountNotifier.value;
    final followedTotal = FollowStoreService.instance.count;

    return Scaffold(
      backgroundColor: const Color(0xFFF9F6F3),
      body: SafeArea(
        child: Column(
          children: [
            // ── Top Header Bar ──
            _buildHeader(unreadTotal),

            // ── Segmented Tabs (Messages vs Followed Stores) ──
            _buildTabSelector(unreadTotal, followedTotal),

            // ── Search & Filter Bar ──
            _buildSearchBar(),

            // ── Tab Views ──
            Expanded(
              child: TabBarView(
                controller: _tabController,
                children: [
                  _buildMessagesTab(),
                  _buildFollowedStoresTab(),
                ],
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: _buildBottomNav(),
    );
  }

  Widget _buildHeader(int unreadTotal) {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 10),
      color: const Color(0xFFF9F6F3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              Text(
                'Inbox',
                style: GoogleFonts.playfairDisplay(
                  fontSize: 26,
                  fontWeight: FontWeight.bold,
                  color: CartzyColors.burgundy,
                ),
              ),
              if (unreadTotal > 0) ...[
                const SizedBox(width: 8),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                  decoration: BoxDecoration(
                    color: CartzyColors.burgundy,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '$unreadTotal unread',
                    style: GoogleFonts.dmSans(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ],
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded,
                color: CartzyColors.burgundy, size: 22),
            tooltip: 'Refresh',
            onPressed: () {
              _loadConversations();
              FollowStoreService.instance.load();
            },
          ),
        ],
      ),
    );
  }

  Widget _buildTabSelector(int unreadTotal, int followedTotal) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: const Color(0xFFECE4DE),
        borderRadius: BorderRadius.circular(16),
      ),
      child: TabBar(
        controller: _tabController,
        dividerColor: Colors.transparent,
        indicatorSize: TabBarIndicatorSize.tab,
        indicator: BoxDecoration(
          color: CartzyColors.burgundy,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: CartzyColors.burgundy.withValues(alpha: 0.25),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        labelColor: Colors.white,
        unselectedLabelColor: const Color(0xFF6B6063),
        labelStyle: GoogleFonts.dmSans(
          fontSize: 13.5,
          fontWeight: FontWeight.w700,
        ),
        unselectedLabelStyle: GoogleFonts.dmSans(
          fontSize: 13.5,
          fontWeight: FontWeight.w600,
        ),
        tabs: [
          Tab(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.chat_bubble_outline_rounded, size: 16),
                const SizedBox(width: 6),
                const Text('Messages'),
                if (unreadTotal > 0) ...[
                  const SizedBox(width: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 6, vertical: 1.5),
                    decoration: const BoxDecoration(
                      color: CartzyColors.cream,
                      shape: BoxShape.circle,
                    ),
                    child: Text(
                      '$unreadTotal',
                      style: GoogleFonts.dmSans(
                        color: CartzyColors.burgundy,
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
          Tab(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.storefront_outlined, size: 16),
                const SizedBox(width: 6),
                const Text('Followed Stores'),
                if (followedTotal > 0) ...[
                  const SizedBox(width: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 6, vertical: 1.5),
                    decoration: BoxDecoration(
                      color: _tabController.index == 1
                          ? CartzyColors.cream
                          : CartzyColors.burgundy.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '$followedTotal',
                      style: GoogleFonts.dmSans(
                        color: _tabController.index == 1
                            ? CartzyColors.burgundy
                            : CartzyColors.burgundy,
                        fontSize: 10.5,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchBar() {
    final isMessages = _tabController.index == 0;
    final hint = isMessages
        ? 'Search conversations or stores...'
        : 'Search followed stores...';

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 8, 20, 10),
      child: Row(
        children: [
          Expanded(
            child: Container(
              height: 42,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE8DED8), width: 1.2),
              ),
              child: TextField(
                controller: _searchController,
                onChanged: (val) => setState(() => _searchQuery = val),
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  color: CartzyColors.navy,
                ),
                decoration: InputDecoration(
                  hintText: hint,
                  hintStyle: GoogleFonts.dmSans(
                    fontSize: 13,
                    color: Colors.grey[500],
                  ),
                  prefixIcon: const Icon(Icons.search_rounded,
                      size: 20, color: CartzyColors.burgundy),
                  suffixIcon: _searchQuery.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.close_rounded, size: 18),
                          onPressed: () {
                            _searchController.clear();
                            setState(() => _searchQuery = '');
                          },
                        )
                      : null,
                  border: InputBorder.none,
                  contentPadding: const EdgeInsets.symmetric(vertical: 10),
                ),
              ),
            ),
          ),
          if (isMessages) ...[
            const SizedBox(width: 8),
            GestureDetector(
              onTap: () =>
                  setState(() => _filterUnreadOnly = !_filterUnreadOnly),
              child: Container(
                height: 42,
                padding: const EdgeInsets.symmetric(horizontal: 12),
                decoration: BoxDecoration(
                  color: _filterUnreadOnly
                      ? CartzyColors.burgundy
                      : Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: _filterUnreadOnly
                        ? CartzyColors.burgundy
                        : const Color(0xFFE8DED8),
                    width: 1.2,
                  ),
                ),
                child: Center(
                  child: Text(
                    'Unread',
                    style: GoogleFonts.dmSans(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w700,
                      color: _filterUnreadOnly
                          ? Colors.white
                          : CartzyColors.navy,
                    ),
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  // ── TAB 1: MESSAGES LIST ──
  Widget _buildMessagesTab() {
    if (!AuthService.isAuthenticated) {
      return _buildGuestState(
        icon: Icons.chat_bubble_outline_rounded,
        title: 'Sign in to message sellers',
        subtitle:
            'Log in to chat directly with official sellers, inquire about items, and track order updates.',
      );
    }

    if (_isLoadingConversations) {
      return const Center(
        child: CircularProgressIndicator(color: CartzyColors.burgundy),
      );
    }

    final threads = _filteredConversations;

    if (threads.isEmpty) {
      return _buildEmptyState(
        icon: Icons.chat_outlined,
        title: _searchQuery.isNotEmpty
            ? 'No conversations found'
            : 'No messages yet',
        subtitle: _searchQuery.isNotEmpty
            ? 'No messages match "$_searchQuery". Try another keyword.'
            : 'When you chat with a seller from their store or product listing, your conversations will appear here.',
      );
    }

    return RefreshIndicator(
      color: CartzyColors.burgundy,
      onRefresh: () => _loadConversations(),
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
        itemCount: threads.length,
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (ctx, i) {
          final thread = threads[i];
          final isUnread = thread.unreadCount > 0;

          return Material(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            child: InkWell(
              onTap: () => _openConversation(thread),
              borderRadius: BorderRadius.circular(18),
              child: Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(
                    color: isUnread
                        ? CartzyColors.burgundy.withValues(alpha: 0.3)
                        : const Color(0xFFF0EBE7),
                    width: isUnread ? 1.5 : 1.0,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.02),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Store Avatar / Initials
                    _buildStoreAvatar(
                      thread.store,
                      avatarUrl: thread.avatar,
                      size: 48,
                    ),
                    const SizedBox(width: 14),

                    // Conversation Details
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment:
                                MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Row(
                                  children: [
                                    Flexible(
                                      child: Text(
                                        thread.store,
                                        style: GoogleFonts.dmSans(
                                          fontSize: 15,
                                          fontWeight: isUnread
                                              ? FontWeight.bold
                                              : FontWeight.w700,
                                          color: CartzyColors.navy,
                                        ),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                    const SizedBox(width: 5),
                                    _buildOfficialBadge(),
                                  ],
                                ),
                              ),
                              Text(
                                thread.time,
                                style: GoogleFonts.dmSans(
                                  fontSize: 11.5,
                                  color: isUnread
                                      ? CartzyColors.burgundy
                                      : Colors.grey[500],
                                  fontWeight: isUnread
                                      ? FontWeight.w700
                                      : FontWeight.w500,
                                ),
                              ),
                            ],
                          ),

                          if (thread.productName != null &&
                              thread.productName!.isNotEmpty) ...[
                            const SizedBox(height: 3),
                            Text(
                              'Product: ${thread.productName}',
                              style: GoogleFonts.dmSans(
                                fontSize: 11.5,
                                color: CartzyColors.burgundy,
                                fontWeight: FontWeight.w600,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],

                          const SizedBox(height: 4),

                          // Last message snippet
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  thread.lastMessage.isNotEmpty
                                      ? thread.lastMessage
                                      : 'Tap to chat with seller...',
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13,
                                    color: isUnread
                                        ? const Color(0xFF231F20)
                                        : Colors.grey[600],
                                    fontWeight: isUnread
                                        ? FontWeight.w600
                                        : FontWeight.w400,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              if (isUnread) ...[
                                const SizedBox(width: 8),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 7, vertical: 2),
                                  decoration: const BoxDecoration(
                                    color: CartzyColors.burgundy,
                                    shape: BoxShape.circle,
                                  ),
                                  child: Text(
                                    '${thread.unreadCount}',
                                    style: GoogleFonts.dmSans(
                                      color: Colors.white,
                                      fontSize: 11,
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
            ),
          );
        },
      ),
    );
  }

  // ── TAB 2: FOLLOWED STORES ──
  Widget _buildFollowedStoresTab() {
    final stores = _filteredFollowedStores;

    if (stores.isEmpty) {
      return _buildEmptyState(
        icon: Icons.storefront_outlined,
        title: _searchQuery.isNotEmpty
            ? 'No stores found'
            : 'No followed stores yet',
        subtitle: _searchQuery.isNotEmpty
            ? 'No followed stores match "$_searchQuery".'
            : 'Follow your favorite official stores to receive exclusive deals, new product arrivals, and instant chat access.',
        actionLabel: 'Discover Stores & Deals',
        onAction: widget.onSaleClick,
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
      itemCount: stores.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (ctx, i) {
        final store = stores[i];

        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: const Color(0xFFF0EBE7), width: 1.2),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.02),
                blurRadius: 8,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Column(
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  // Store Logo
                  _buildStoreAvatar(
                    store.name,
                    avatarUrl: store.logo,
                    size: 52,
                  ),
                  const SizedBox(width: 14),

                  // Store Info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Flexible(
                              child: Text(
                                store.name,
                                style: GoogleFonts.dmSans(
                                  fontSize: 15.5,
                                  fontWeight: FontWeight.w700,
                                  color: CartzyColors.navy,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            const SizedBox(width: 5),
                            _buildOfficialBadge(),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            const Icon(Icons.star_rounded,
                                size: 15, color: Color(0xFFF59E0B)),
                            const SizedBox(width: 3),
                            Text(
                              '${store.rating.toStringAsFixed(1)} ★',
                              style: GoogleFonts.dmSans(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF231F20),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              '•  ${store.followersCount} followers',
                              style: GoogleFonts.dmSans(
                                fontSize: 12,
                                color: Colors.grey[600],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),
                        Row(
                          children: [
                            const Icon(Icons.location_on_rounded,
                                size: 13, color: Colors.redAccent),
                            const SizedBox(width: 2),
                            Text(
                              store.location,
                              style: GoogleFonts.dmSans(
                                fontSize: 11.5,
                                color: Colors.grey[600],
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              const Divider(height: 1, color: Color(0xFFF3ECE8)),
              const SizedBox(height: 10),

              // Action Buttons Row
              Row(
                children: [
                  // Visit Store Button
                  Expanded(
                    flex: 5,
                    child: ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: CartzyColors.burgundy,
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(vertical: 9),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      onPressed: () => _openStoreFront(store),
                      icon: const Icon(Icons.storefront_rounded, size: 16),
                      label: Text(
                        'Visit Store',
                        style: GoogleFonts.dmSans(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // Chat Button
                  Expanded(
                    flex: 4,
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: CartzyColors.navy,
                        side: const BorderSide(
                            color: Color(0xFFDED4CE), width: 1.2),
                        padding: const EdgeInsets.symmetric(vertical: 9),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      onPressed: () => _openStoreChat(store),
                      icon: const Icon(Icons.chat_bubble_outline_rounded,
                          size: 15, color: CartzyColors.burgundy),
                      label: Text(
                        'Chat',
                        style: GoogleFonts.dmSans(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // Unfollow Button
                  IconButton(
                    style: IconButton.styleFrom(
                      backgroundColor: const Color(0xFFF5EFEB),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      padding: const EdgeInsets.all(8),
                    ),
                    icon: const Icon(Icons.check_rounded,
                        color: CartzyColors.burgundy, size: 18),
                    tooltip: 'Following',
                    onPressed: () {
                      FollowStoreService.instance.unfollowStore(
                          store.slug.isNotEmpty ? store.slug : store.id);
                      showCartzyFlash(
                        context,
                        'Unfollowed ${store.name}.',
                      );
                    },
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildStoreAvatar(String storeName,
      {String? avatarUrl, double size = 48}) {
    if (avatarUrl != null &&
        avatarUrl.isNotEmpty &&
        (avatarUrl.startsWith('http://') || avatarUrl.startsWith('https://'))) {
      return Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
          color: CartzyColors.cream,
          shape: BoxShape.circle,
          border: Border.all(color: const Color(0xFFEFE6E0), width: 1.5),
        ),
        child: ClipOval(
          child: Image.network(
            avatarUrl,
            width: size,
            height: size,
            fit: BoxFit.cover,
            errorBuilder: (_, __, ___) => _buildInitials(storeName, size),
          ),
        ),
      );
    }

    return _buildInitials(storeName, size);
  }

  Widget _buildInitials(String storeName, double size) {
    final words = storeName.trim().split(' ').where((w) => w.isNotEmpty).toList();
    final initials = words.length >= 2
        ? '${words[0][0]}${words[1][0]}'.toUpperCase()
        : storeName.substring(0, storeName.length >= 2 ? 2 : 1).toUpperCase();

    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: CartzyColors.cream,
        shape: BoxShape.circle,
        border: Border.all(color: const Color(0xFFEFE6E0), width: 1.5),
      ),
      child: Center(
        child: Text(
          initials,
          style: GoogleFonts.dmSans(
            color: CartzyColors.burgundy,
            fontSize: size * 0.35,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }

  Widget _buildOfficialBadge() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
      decoration: BoxDecoration(
        color: const Color(0xFF8B3A3A),
        borderRadius: BorderRadius.circular(4),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.verified_rounded, size: 9, color: Colors.white),
          const SizedBox(width: 2.5),
          Text(
            'Official',
            style: GoogleFonts.dmSans(
              color: Colors.white,
              fontSize: 9.5,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState({
    required IconData icon,
    required String title,
    required String subtitle,
    String? actionLabel,
    VoidCallback? onAction,
  }) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 36),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(22),
              decoration: BoxDecoration(
                color: CartzyColors.cream,
                shape: BoxShape.circle,
                border: Border.all(
                  color: CartzyColors.burgundy.withValues(alpha: 0.15),
                  width: 2,
                ),
              ),
              child: Icon(icon, size: 44, color: CartzyColors.burgundy),
            ),
            const SizedBox(height: 16),
            Text(
              title,
              style: GoogleFonts.playfairDisplay(
                fontSize: 19,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              subtitle,
              style: GoogleFonts.dmSans(
                fontSize: 13,
                color: Colors.grey[600],
                height: 1.4,
              ),
              textAlign: TextAlign.center,
            ),
            if (actionLabel != null && onAction != null) ...[
              const SizedBox(height: 20),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: CartzyColors.burgundy,
                  foregroundColor: Colors.white,
                  padding:
                      const EdgeInsets.symmetric(horizontal: 22, vertical: 11),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                  ),
                  elevation: 0,
                ),
                onPressed: onAction,
                child: Text(
                  actionLabel,
                  style: GoogleFonts.dmSans(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildGuestState({
    required IconData icon,
    required String title,
    required String subtitle,
  }) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 36),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(22),
              decoration: const BoxDecoration(
                color: CartzyColors.cream,
                shape: BoxShape.circle,
              ),
              child: Icon(icon, size: 44, color: CartzyColors.burgundy),
            ),
            const SizedBox(height: 16),
            Text(
              title,
              style: GoogleFonts.playfairDisplay(
                fontSize: 19,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              subtitle,
              style: GoogleFonts.dmSans(
                fontSize: 13,
                color: Colors.grey[600],
                height: 1.4,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  // ── BOTTOM NAVIGATION BAR (Home, Cart, Sale, Inbox, Profile) ──
  Widget _buildBottomNav() {
    return SafeArea(
      child: Container(
        margin: const EdgeInsets.fromLTRB(16, 8, 16, 12),
        decoration: const ShapeDecoration(
          color: CartzyColors.surface,
          shape: StadiumBorder(
            side: BorderSide(color: CartzyColors.border),
          ),
          shadows: [
            BoxShadow(
              color: Color(0x148B3A3A),
              blurRadius: 16,
              offset: Offset(0, 4),
            ),
          ],
        ),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
        child: Row(
          children: [
            _InboxBottomNavItem(
              icon: Icons.home_rounded,
              label: 'Home',
              selected: false,
              onClick: widget.onHomeClick ?? widget.onBack ?? () {},
            ),
            _InboxBottomNavItem(
              icon: Icons.shopping_cart_outlined,
              label: 'Cart',
              selected: false,
              onClick: widget.onCartClick ?? () {},
            ),
            _InboxBottomNavItem(
              icon: Icons.local_offer_outlined,
              label: 'Sale',
              selected: false,
              isSale: true,
              onClick: widget.onSaleClick ?? () {},
            ),
            _InboxBottomNavItem(
              icon: Icons.mail_rounded,
              label: 'Inbox',
              selected: true,
              onClick: () {},
            ),
            _InboxBottomNavItem(
              icon: Icons.person_outline_rounded,
              label: 'Profile',
              selected: false,
              onClick: widget.onProfileClick ?? () {},
            ),
          ],
        ),
      ),
    );
  }
}

class _InboxBottomNavItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final bool isSale;
  final VoidCallback onClick;

  const _InboxBottomNavItem({
    required this.icon,
    required this.label,
    required this.selected,
    this.isSale = false,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    final color = isSale
        ? CartzyColors.burgundy
        : selected
            ? CartzyColors.burgundy
            : CartzyColors.gray;

    return Expanded(
      child: InkWell(
        onTap: onClick,
        borderRadius: BorderRadius.circular(20),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, color: color, size: 22),
              const SizedBox(height: 3),
              Text(
                label,
                style: GoogleFonts.dmSans(
                  fontSize: 11,
                  color: color,
                  fontWeight:
                      selected || isSale ? FontWeight.w600 : FontWeight.w400,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 2),
              AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                height: 2,
                width: selected ? 16 : 0,
                decoration: BoxDecoration(
                  color: CartzyColors.burgundy,
                  borderRadius: BorderRadius.circular(1),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

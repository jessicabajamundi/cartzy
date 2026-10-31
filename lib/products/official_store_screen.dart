import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';
import 'package:cartzy/widgets/product_card.dart';
import 'package:cartzy/products/product_details_screen.dart';
import 'package:cartzy/buyer/messages_screen.dart';
import 'package:cartzy/guest/guest_account_screen.dart';

/// Official Store / Seller Storefront Screen matching Cartzy's store web page.
class OfficialStoreScreen extends StatefulWidget {
  final int? sellerId;
  final String? sellerSlug;
  final String sellerName;
  final String? sellerLogoUrl;

  const OfficialStoreScreen({
    super.key,
    this.sellerId,
    this.sellerSlug,
    required this.sellerName,
    this.sellerLogoUrl,
  });

  @override
  State<OfficialStoreScreen> createState() => _OfficialStoreScreenState();
}

class _OfficialStoreScreenState extends State<OfficialStoreScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  bool _isLoading = true;
  String? _errorMessage;

  // Store data
  Map<String, dynamic> _seller = {};
  Map<String, dynamic> _stats = {};
  List<Map<String, dynamic>> _categories = [];
  List<Map<String, dynamic>> _vouchers = [];
  List<Map<String, dynamic>> _products = [];
  List<Map<String, dynamic>> _reviews = [];
  bool _isFollowing = false;
  int _followersCount = 0;

  // Filter & Search states for Products Tab
  final TextEditingController _searchController = TextEditingController();
  String _selectedCategorySlug = '';
  String _selectedSort = 'latest'; // latest, popular, rating, price_asc, price_desc

  // Filter state for Reviews Tab
  int? _selectedRatingFilter; // null = all, 5, 4, 3, 2, 1

  final Map<String, String> _sortLabels = {
    'latest': 'Newest',
    'popular': 'Best selling',
    'rating': 'Top rated',
    'price_asc': 'Price: Low to High',
    'price_desc': 'Price: High to Low',
  };

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadStoreData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  String get _identifier {
    if (widget.sellerSlug != null && widget.sellerSlug!.isNotEmpty) {
      return widget.sellerSlug!;
    }
    if (widget.sellerId != null && widget.sellerId! > 0) {
      return widget.sellerId.toString();
    }
    return widget.sellerName.toLowerCase().replaceAll(RegExp(r'[^a-z0-9]+'), '-');
  }

  Future<void> _loadStoreData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final queryParams = <String, String>{
        if (_searchController.text.trim().isNotEmpty)
          'q': _searchController.text.trim(),
        if (_selectedCategorySlug.isNotEmpty)
          'category': _selectedCategorySlug,
        'sort': _selectedSort,
        if (_selectedRatingFilter != null)
          'rating': _selectedRatingFilter.toString(),
      };

      final queryString = queryParams.entries
          .map((e) => '${Uri.encodeComponent(e.key)}=${Uri.encodeComponent(e.value)}')
          .join('&');

      final endpoint = '/stores/$_identifier${queryString.isNotEmpty ? '?$queryString' : ''}';
      final response = await ApiService.get(endpoint, token: AuthService.token);

      if (!mounted) return;

      final sellerData = (response['seller'] is Map)
          ? Map<String, dynamic>.from(response['seller'])
          : <String, dynamic>{};

      final statsData = (response['stats'] is Map)
          ? Map<String, dynamic>.from(response['stats'])
          : <String, dynamic>{};

      final rawCategories = (response['categories'] is List)
          ? List<Map<String, dynamic>>.from(
              (response['categories'] as List).map((c) => Map<String, dynamic>.from(c)))
          : <Map<String, dynamic>>[];

      final rawVouchers = (response['vouchers'] is List)
          ? List<Map<String, dynamic>>.from(
              (response['vouchers'] as List).map((v) => Map<String, dynamic>.from(v)))
          : <Map<String, dynamic>>[];

      final rawProducts = (response['products'] is List)
          ? List<Map<String, dynamic>>.from(
              (response['products'] as List).map((p) => Map<String, dynamic>.from(p)))
          : <Map<String, dynamic>>[];

      final rawReviews = (response['reviews'] is List)
          ? List<Map<String, dynamic>>.from(
              (response['reviews'] as List).map((r) => Map<String, dynamic>.from(r)))
          : <Map<String, dynamic>>[];

      setState(() {
        _seller = sellerData;
        _stats = statsData;
        _categories = rawCategories;
        _vouchers = rawVouchers;
        _products = rawProducts;
        _reviews = rawReviews;
        _isFollowing = response['is_following'] == true;
        _followersCount = (statsData['followers'] as num?)?.toInt() ?? 0;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString().replaceAll('Exception:', '').trim();
      });
    }
  }

  Future<void> _toggleFollow() async {
    if (!AuthService.isAuthenticated) {
      GuestAccountScreen.open(
        context,
        onLogin: () => Navigator.of(context).pushNamed('/login'),
        onSignUp: () => Navigator.of(context).pushNamed('/signup'),
      );
      return;
    }

    final newFollowState = !_isFollowing;
    setState(() {
      _isFollowing = newFollowState;
      _followersCount += newFollowState ? 1 : -1;
      if (_followersCount < 0) _followersCount = 0;
    });

    try {
      final res = await ApiService.post(
        '/stores/$_identifier/follow',
        {},
        token: AuthService.token,
      );

      if (mounted) {
        if (res['is_following'] != null) {
          setState(() {
            _isFollowing = res['is_following'] == true;
            if (res['followers'] != null) {
              _followersCount = (res['followers'] as num).toInt();
            }
          });
        }
        showCartzyFlash(
          context,
          _isFollowing
              ? 'You are now following $_effectiveSellerName!'
              : 'You unfollowed $_effectiveSellerName.',
        );
      }
    } catch (e) {
      // Revert on error
      if (mounted) {
        setState(() {
          _isFollowing = !newFollowState;
          _followersCount += newFollowState ? -1 : 1;
        });
        showCartzyFlash(context, 'Could not update follow status.');
      }
    }
  }

  void _openChatWithSeller() {
    if (!AuthService.isAuthenticated) {
      GuestAccountScreen.open(
        context,
        onLogin: () => Navigator.of(context).pushNamed('/login'),
        onSignUp: () => Navigator.of(context).pushNamed('/signup'),
      );
      return;
    }

    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MessagesScreen(
          onBack: () => Navigator.of(context).pop(),
          initialStoreName: _effectiveSellerName,
        ),
      ),
    );
  }

  void _shareStore() {
    final slug = _seller['slug']?.toString() ?? _identifier;
    final url = 'https://cartzyy.shop/store/$slug';
    Clipboard.setData(ClipboardData(text: url));
    showCartzyFlash(context, 'Store link copied to clipboard!');
  }

  String get _effectiveSellerName {
    final name = _seller['name']?.toString();
    if (name != null && name.trim().isNotEmpty) return name.trim();
    return widget.sellerName;
  }

  String? get _effectiveLogoUrl {
    final logo = _seller['logo_url']?.toString();
    if (logo != null && logo.trim().isNotEmpty) {
      return ApiService.normalizeImageUrl(logo);
    }
    if (widget.sellerLogoUrl != null && widget.sellerLogoUrl!.trim().isNotEmpty) {
      return ApiService.normalizeImageUrl(widget.sellerLogoUrl);
    }
    return null;
  }

  String? get _effectiveBannerUrl {
    final banner = _seller['banner_url']?.toString();
    if (banner != null && banner.trim().isNotEmpty) {
      return ApiService.normalizeImageUrl(banner);
    }
    return null;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F5),
      body: _isLoading && _seller.isEmpty
          ? const Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(CartzyColors.burgundy),
              ),
            )
          : _errorMessage != null && _seller.isEmpty
              ? _buildErrorView()
              : _buildStoreContent(),
    );
  }

  Widget _buildErrorView() {
    return SafeArea(
      child: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.store_mall_directory_outlined, size: 64, color: CartzyColors.gray),
              const SizedBox(height: 16),
              Text(
                'Store Unavailable',
                style: GoogleFonts.dmSans(fontSize: 18, fontWeight: FontWeight.bold, color: CartzyColors.navy),
              ),
              const SizedBox(height: 8),
              Text(
                _errorMessage ?? 'Could not load store details.',
                textAlign: TextAlign.center,
                style: GoogleFonts.dmSans(fontSize: 14, color: CartzyColors.gray),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: _loadStoreData,
                style: ElevatedButton.styleFrom(
                  backgroundColor: CartzyColors.burgundy,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                child: const Text('Try Again'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStoreContent() {
    final banner = _effectiveBannerUrl;
    final logo = _effectiveLogoUrl;
    final name = _effectiveSellerName;
    final location = _seller['location']?.toString();

    final ratingVal = (_stats['rating'] as num?)?.toDouble();
    final reviewTotal = (_stats['reviews'] as num?)?.toInt() ?? 0;
    final productsTotal = (_stats['products'] as num?)?.toInt() ?? _products.length;
    final soldTotal = (_stats['sold'] as num?)?.toInt() ?? 0;
    final joinedText = _seller['joined_ago']?.toString() ?? _seller['joined']?.toString() ?? 'Active seller';

    return NestedScrollView(
      headerSliverBuilder: (context, innerBoxIsScrolled) {
        return [
          // Sliver Hero Header with Banner and Profile
          SliverAppBar(
            expandedHeight: 260,
            pinned: true,
            backgroundColor: CartzyColors.burgundy,
            elevation: innerBoxIsScrolled ? 2 : 0,
            leading: Padding(
              padding: const EdgeInsets.all(8.0),
              child: CircleAvatar(
                backgroundColor: Colors.black.withValues(alpha: 0.35),
                child: IconButton(
                  icon: const Icon(Icons.arrow_back, color: Colors.white, size: 20),
                  onPressed: () => Navigator.of(context).pop(),
                ),
              ),
            ),
            actions: [
              Padding(
                padding: const EdgeInsets.only(right: 8.0),
                child: CircleAvatar(
                  backgroundColor: Colors.black.withValues(alpha: 0.35),
                  child: IconButton(
                    icon: const Icon(Icons.share_outlined, color: Colors.white, size: 20),
                    onPressed: _shareStore,
                  ),
                ),
              ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Stack(
                fit: StackFit.expand,
                children: [
                  // Banner Image or Fallback Gradient
                  if (banner != null && banner.isNotEmpty)
                    Image.network(
                      banner,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => _buildFallbackBanner(),
                    )
                  else
                    _buildFallbackBanner(),

                  // Dark gradient overlay
                  Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [
                          Colors.black.withValues(alpha: 0.4),
                          Colors.black.withValues(alpha: 0.75),
                        ],
                      ),
                    ),
                  ),

                  // Store Info Overlay
                  Positioned(
                    left: 16,
                    right: 16,
                    bottom: 16,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            // Logo Avatar
                            Container(
                              width: 60,
                              height: 60,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                border: Border.all(color: Colors.white, width: 2),
                                color: Colors.white,
                              ),
                              clipBehavior: Clip.antiAlias,
                              child: logo != null && logo.isNotEmpty
                                  ? Image.network(
                                      logo,
                                      fit: BoxFit.cover,
                                      errorBuilder: (_, __, ___) => _buildInitialAvatar(name),
                                    )
                                  : _buildInitialAvatar(name),
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    name,
                                    style: GoogleFonts.dmSans(
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.white,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 4),
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: CartzyColors.coral,
                                          borderRadius: BorderRadius.circular(4),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            const Icon(Icons.verified, size: 12, color: Colors.white),
                                            const SizedBox(width: 3),
                                            Text(
                                              'Official Store',
                                              style: GoogleFonts.dmSans(
                                                fontSize: 10,
                                                fontWeight: FontWeight.bold,
                                                color: Colors.white,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      if (location != null && location.isNotEmpty) ...[
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Text(
                                            '📍 $location',
                                            style: GoogleFonts.dmSans(
                                              fontSize: 11,
                                              color: Colors.white70,
                                            ),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
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

                        const SizedBox(height: 14),

                        // Action Buttons: Follow & Chat
                        Row(
                          children: [
                            // Follow Button
                            Expanded(
                              child: SizedBox(
                                height: 38,
                                child: ElevatedButton.icon(
                                  onPressed: _toggleFollow,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: _isFollowing ? Colors.white24 : CartzyColors.coral,
                                    foregroundColor: Colors.white,
                                    elevation: 0,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(10),
                                      side: _isFollowing
                                          ? const BorderSide(color: Colors.white70)
                                          : BorderSide.none,
                                    ),
                                  ),
                                  icon: Icon(
                                    _isFollowing ? Icons.check : Icons.add,
                                    size: 16,
                                  ),
                                  label: Text(
                                    _isFollowing ? 'Following' : 'Follow',
                                    style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.bold),
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            // Chat Button
                            Expanded(
                              child: SizedBox(
                                height: 38,
                                child: OutlinedButton.icon(
                                  onPressed: _openChatWithSeller,
                                  style: OutlinedButton.styleFrom(
                                    backgroundColor: Colors.white.withValues(alpha: 0.15),
                                    foregroundColor: Colors.white,
                                    side: const BorderSide(color: Colors.white),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                  ),
                                  icon: const Icon(Icons.chat_bubble_outline, size: 16),
                                  label: Text(
                                    'Chat',
                                    style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.bold),
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Stats Bar Sliver
          SliverToBoxAdapter(
            child: Container(
              color: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _buildStatItem('Followers', _followersCount.toString()),
                  _buildStatDivider(),
                  _buildStatItem('Rating', ratingVal != null ? '${ratingVal.toStringAsFixed(1)} ★' : '—'),
                  _buildStatDivider(),
                  _buildStatItem('Products', productsTotal.toString()),
                  _buildStatDivider(),
                  _buildStatItem('Sold', soldTotal.toString()),
                ],
              ),
            ),
          ),

          // Shop Vouchers Section (if available)
          if (_vouchers.isNotEmpty)
            SliverToBoxAdapter(
              child: Container(
                margin: const EdgeInsets.only(top: 8),
                padding: const EdgeInsets.symmetric(vertical: 12),
                color: Colors.white,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Row(
                        children: [
                          const Icon(Icons.confirmation_num_outlined, size: 16, color: CartzyColors.burgundy),
                          const SizedBox(width: 6),
                          Text(
                            'Shop Vouchers',
                            style: GoogleFonts.dmSans(fontSize: 13.5, fontWeight: FontWeight.bold, color: CartzyColors.navy),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 10),
                    SizedBox(
                      height: 80,
                      child: ListView.separated(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        scrollDirection: Axis.horizontal,
                        itemCount: _vouchers.length,
                        separatorBuilder: (_, __) => const SizedBox(width: 10),
                        itemBuilder: (context, index) {
                          final v = _vouchers[index];
                          final headline = v['headline']?.toString() ?? 'DISCOUNT';
                          final terms = v['terms']?.toString() ?? 'Voucher';
                          final code = v['code']?.toString() ?? '';
                          return Container(
                            width: 180,
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFFFFF7ED), Color(0xFFFFEDD5)],
                              ),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: const Color(0xFFFDBA74)),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(
                                  headline,
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13,
                                    fontWeight: FontWeight.bold,
                                    color: const Color(0xFFC2410C),
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  terms,
                                  style: GoogleFonts.dmSans(fontSize: 10, color: const Color(0xFF7C2D12)),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const Spacer(),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      code,
                                      style: GoogleFonts.dmMono(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: CartzyColors.navy,
                                      ),
                                    ),
                                    InkWell(
                                      onTap: () {
                                        Clipboard.setData(ClipboardData(text: code));
                                        showCartzyFlash(context, 'Voucher code $code copied!');
                                      },
                                      child: Text(
                                        'COPY',
                                        style: GoogleFonts.dmSans(
                                          fontSize: 10.5,
                                          fontWeight: FontWeight.bold,
                                          color: CartzyColors.coral,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),
            ),

          // Tab Bar Sliver (Persistent Header)
          SliverPersistentHeader(
            pinned: true,
            delegate: _SliverTabBarDelegate(
              TabBar(
                controller: _tabController,
                indicatorColor: CartzyColors.burgundy,
                indicatorWeight: 3,
                labelColor: CartzyColors.burgundy,
                unselectedLabelColor: CartzyColors.gray,
                labelStyle: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.bold),
                unselectedLabelStyle: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.normal),
                tabs: [
                  Tab(text: 'Products ($productsTotal)'),
                  Tab(text: 'Reviews ($reviewTotal)'),
                  const Tab(text: 'About'),
                ],
              ),
            ),
          ),
        ];
      },
      body: TabBarView(
        controller: _tabController,
        children: [
          // ── TAB 1: PRODUCTS ──
          _buildProductsTab(),

          // ── TAB 2: REVIEWS ──
          _buildReviewsTab(),

          // ── TAB 3: ABOUT ──
          _buildAboutTab(joinedText, location),
        ],
      ),
    );
  }

  Widget _buildFallbackBanner() {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [CartzyColors.burgundy, CartzyColors.navy],
        ),
      ),
    );
  }

  Widget _buildInitialAvatar(String name) {
    final initial = name.isNotEmpty ? name[0].toUpperCase() : 'S';
    return Container(
      color: CartzyColors.navy,
      child: Center(
        child: Text(
          initial,
          style: GoogleFonts.dmSans(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
      ),
    );
  }

  Widget _buildStatItem(String label, String value) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          value,
          style: GoogleFonts.dmSans(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: CartzyColors.navy,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: GoogleFonts.dmSans(
            fontSize: 11,
            color: CartzyColors.gray,
          ),
        ),
      ],
    );
  }

  Widget _buildStatDivider() {
    return Container(
      height: 20,
      width: 1,
      color: const Color(0xFFE2E8F0),
    );
  }

  // ==========================================
  // TAB 1: PRODUCTS
  // ==========================================
  Widget _buildProductsTab() {
    return RefreshIndicator(
      onRefresh: _loadStoreData,
      color: CartzyColors.burgundy,
      child: CustomScrollView(
        slivers: [
          // Search & Filter Bar
          SliverToBoxAdapter(
            child: Container(
              padding: const EdgeInsets.all(12),
              color: Colors.white,
              child: Column(
                children: [
                  // Search Box
                  Container(
                    height: 42,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: TextField(
                      controller: _searchController,
                      textInputAction: TextInputAction.search,
                      onSubmitted: (_) => _loadStoreData(),
                      decoration: InputDecoration(
                        hintText: 'Search in $_effectiveSellerName...',
                        hintStyle: GoogleFonts.dmSans(fontSize: 13, color: CartzyColors.gray),
                        prefixIcon: const Icon(Icons.search, size: 20, color: CartzyColors.gray),
                        suffixIcon: _searchController.text.isNotEmpty
                            ? IconButton(
                                icon: const Icon(Icons.clear, size: 18),
                                onPressed: () {
                                  _searchController.clear();
                                  _loadStoreData();
                                },
                              )
                            : null,
                        border: InputBorder.none,
                        contentPadding: const EdgeInsets.symmetric(vertical: 10),
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),

                  // Category Chips & Sort Selector
                  Row(
                    children: [
                      // Sort Selector Dropdown
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<String>(
                            value: _selectedSort,
                            isDense: true,
                            icon: const Icon(Icons.keyboard_arrow_down, size: 18, color: CartzyColors.navy),
                            style: GoogleFonts.dmSans(fontSize: 12, fontWeight: FontWeight.w600, color: CartzyColors.navy),
                            items: _sortLabels.entries.map((e) {
                              return DropdownMenuItem(
                                value: e.key,
                                child: Text(e.value),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                setState(() => _selectedSort = val);
                                _loadStoreData();
                              }
                            },
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),

                      // Category Pills
                      Expanded(
                        child: SingleChildScrollView(
                          scrollDirection: Axis.horizontal,
                          child: Row(
                            children: [
                              _buildCategoryChip('All', ''),
                              ..._categories.map((c) => _buildCategoryChip(c['name']?.toString() ?? '', c['slug']?.toString() ?? '')),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          const SliverToBoxAdapter(child: SizedBox(height: 10)),

          // Products Grid
          if (_products.isEmpty)
            SliverFillRemaining(
              hasScrollBody: false,
              child: Center(
                child: Padding(
                  padding: const EdgeInsets.all(32.0),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.inventory_2_outlined, size: 54, color: CartzyColors.gray),
                      const SizedBox(height: 12),
                      Text(
                        'No products found',
                        style: GoogleFonts.dmSans(fontSize: 16, fontWeight: FontWeight.bold, color: CartzyColors.navy),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _searchController.text.isNotEmpty || _selectedCategorySlug.isNotEmpty
                            ? 'Try clearing filters to see all store products.'
                            : '$_effectiveSellerName has not listed products yet.',
                        textAlign: TextAlign.center,
                        style: GoogleFonts.dmSans(fontSize: 13, color: CartzyColors.gray),
                      ),
                    ],
                  ),
                ),
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.symmetric(horizontal: 14),
              sliver: SliverGrid(
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  crossAxisSpacing: 12,
                  mainAxisSpacing: 12,
                  childAspectRatio: 0.62,
                ),
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final item = _products[index];
                    final price = (item['price'] != null)
                        ? '₱${(item['price'] as num).toStringAsFixed(2)}'
                        : '₱0.00';
                    final regularPrice = (item['regular_price'] != null)
                        ? '₱${(item['regular_price'] as num).toStringAsFixed(2)}'
                        : null;
                    final rating = (item['rating'] as num?)?.toDouble() ?? 4.8;
                    final sold = (item['sold'] != null) ? '${item['sold']} sold' : '0 sold';
                    final inStock = item['in_stock'] != false;
                    final image = item['image']?.toString();

                    return ProductCard(
                      productId: item['id'] as int?,
                      name: item['name']?.toString() ?? 'Product',
                      price: price,
                      originalPrice: regularPrice,
                      imageUrl: image != null ? ApiService.normalizeImageUrl(image) : null,
                      rating: rating,
                      soldCount: sold,
                      sellerName: _effectiveSellerName,
                      inStock: inStock,
                      badge: item['badge']?.toString(),
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => ProductDetailsScreen(
                              productId: item['id'] as int?,
                              name: item['name']?.toString() ?? '',
                              category: item['category']?['name']?.toString() ?? '',
                              price: price,
                              originalPrice: regularPrice,
                              imageUrl: image != null ? ApiService.normalizeImageUrl(image) : null,
                              sellerName: _effectiveSellerName,
                              rating: rating,
                              soldCount: sold,
                              onBack: () => Navigator.of(context).pop(),
                            ),
                          ),
                        );
                      },
                    );
                  },
                  childCount: _products.length,
                ),
              ),
            ),

          const SliverToBoxAdapter(child: SizedBox(height: 32)),
        ],
      ),
    );
  }

  Widget _buildCategoryChip(String label, String slug) {
    final isSelected = _selectedCategorySlug == slug;
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: ChoiceChip(
        label: Text(label),
        selected: isSelected,
        selectedColor: CartzyColors.burgundy,
        backgroundColor: const Color(0xFFF1F5F9),
        labelStyle: GoogleFonts.dmSans(
          fontSize: 12,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          color: isSelected ? Colors.white : CartzyColors.navy,
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        side: BorderSide.none,
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
        onSelected: (selected) {
          setState(() {
            _selectedCategorySlug = selected ? slug : '';
          });
          _loadStoreData();
        },
      ),
    );
  }

  // ==========================================
  // TAB 2: REVIEWS
  // ==========================================
  Widget _buildReviewsTab() {
    final ratingVal = (_stats['rating'] as num?)?.toDouble();
    final reviewTotal = (_stats['reviews'] as num?)?.toInt() ?? 0;
    final breakdown = (_stats['breakdown'] is Map)
        ? Map<String, dynamic>.from(_stats['breakdown'])
        : <String, dynamic>{};

    return RefreshIndicator(
      onRefresh: _loadStoreData,
      color: CartzyColors.burgundy,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Rating Summary Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFEDE4E7)),
            ),
            child: Row(
              children: [
                // Big score
                Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      ratingVal != null ? ratingVal.toStringAsFixed(1) : '—',
                      style: GoogleFonts.dmSans(
                        fontSize: 32,
                        fontWeight: FontWeight.bold,
                        color: CartzyColors.navy,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: List.generate(5, (index) {
                        final starFilled = ratingVal != null && (ratingVal >= index + 1);
                        return Icon(
                          starFilled ? Icons.star : Icons.star_border,
                          size: 16,
                          color: const Color(0xFFF59E0B),
                        );
                      }),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '$reviewTotal reviews',
                      style: GoogleFonts.dmSans(fontSize: 11, color: CartzyColors.gray),
                    ),
                  ],
                ),
                const SizedBox(width: 20),
                // Star breakdown bars
                Expanded(
                  child: Column(
                    children: [5, 4, 3, 2, 1].map((star) {
                      final count = (breakdown[star.toString()] as num?)?.toInt() ?? 0;
                      final ratio = reviewTotal > 0 ? count / reviewTotal : 0.0;
                      return Padding(
                        padding: const EdgeInsets.symmetric(vertical: 2),
                        child: Row(
                          children: [
                            Text(
                              '$star★',
                              style: GoogleFonts.dmSans(fontSize: 11, color: CartzyColors.gray),
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(3),
                                child: LinearProgressIndicator(
                                  value: ratio,
                                  backgroundColor: const Color(0xFFF1F5F9),
                                  valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFFF59E0B)),
                                  minHeight: 6,
                                ),
                              ),
                            ),
                            const SizedBox(width: 6),
                            Text(
                              '$count',
                              style: GoogleFonts.dmSans(fontSize: 10, color: CartzyColors.gray),
                            ),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 12),

          // Star Filter Chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildStarFilterChip('All', null),
                ...[5, 4, 3, 2, 1].map((s) => _buildStarFilterChip('$s ★', s)),
              ],
            ),
          ),

          const SizedBox(height: 14),

          // Reviews List
          if (_reviews.isEmpty)
            Container(
              padding: const EdgeInsets.symmetric(vertical: 40),
              alignment: Alignment.center,
              child: Column(
                children: [
                  const Icon(Icons.rate_review_outlined, size: 48, color: CartzyColors.gray),
                  const SizedBox(height: 8),
                  Text(
                    'No reviews yet',
                    style: GoogleFonts.dmSans(fontSize: 14, color: CartzyColors.gray),
                  ),
                ],
              ),
            )
          else
            ..._reviews.map((r) => _buildReviewCard(r)),

          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _buildStarFilterChip(String label, int? star) {
    final isSelected = _selectedRatingFilter == star;
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        selectedColor: CartzyColors.burgundy,
        backgroundColor: Colors.white,
        labelStyle: GoogleFonts.dmSans(
          fontSize: 12,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          color: isSelected ? Colors.white : CartzyColors.navy,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: BorderSide(
            color: isSelected ? CartzyColors.burgundy : const Color(0xFFE2E8F0),
          ),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
        onSelected: (selected) {
          setState(() {
            _selectedRatingFilter = selected ? star : null;
          });
          _loadStoreData();
        },
      ),
    );
  }

  Widget _buildReviewCard(Map<String, dynamic> r) {
    final userName = r['user_name']?.toString() ?? 'Buyer';
    final rating = (r['rating'] as num?)?.toInt() ?? 5;
    final date = r['created_at']?.toString() ?? '';
    final comment = r['comment']?.toString();
    final reply = r['seller_reply']?.toString();
    final productName = r['product_name']?.toString();

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFEDE4E7)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 16,
                backgroundColor: CartzyColors.burgundy.withValues(alpha: 0.1),
                child: Text(
                  userName.isNotEmpty ? userName[0].toUpperCase() : 'B',
                  style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.bold, color: CartzyColors.burgundy),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      userName,
                      style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.bold, color: CartzyColors.navy),
                    ),
                    Text(
                      date,
                      style: GoogleFonts.dmSans(fontSize: 10.5, color: CartzyColors.gray),
                    ),
                  ],
                ),
              ),
              Row(
                children: List.generate(5, (i) {
                  return Icon(
                    i < rating ? Icons.star : Icons.star_border,
                    size: 14,
                    color: const Color(0xFFF59E0B),
                  );
                }),
              ),
            ],
          ),
          if (productName != null && productName.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(6),
              ),
              child: Text(
                'Product: $productName',
                style: GoogleFonts.dmSans(fontSize: 11, color: CartzyColors.gray),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
          if (comment != null && comment.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(
              comment,
              style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF334155), height: 1.35),
            ),
          ],
          if (reply != null && reply.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xFFFFFBEB),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFFDE68A)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Seller Reply:',
                    style: GoogleFonts.dmSans(fontSize: 11, fontWeight: FontWeight.bold, color: const Color(0xFF92400E)),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    reply,
                    style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF78350F)),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  // ==========================================
  // TAB 3: ABOUT
  // ==========================================
  Widget _buildAboutTab(String joinedText, String? location) {
    final desc = _seller['description']?.toString();
    final name = _effectiveSellerName;
    final ratingVal = (_stats['rating'] as num?)?.toDouble();
    final reviewTotal = (_stats['reviews'] as num?)?.toInt() ?? 0;
    final productsTotal = (_stats['products'] as num?)?.toInt() ?? _products.length;
    final soldTotal = (_stats['sold'] as num?)?.toInt() ?? 0;

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Store Description
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: const Color(0xFFEDE4E7)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'About $name',
                style: GoogleFonts.dmSans(fontSize: 16, fontWeight: FontWeight.bold, color: CartzyColors.navy),
              ),
              const SizedBox(height: 8),
              Text(
                desc != null && desc.trim().isNotEmpty
                    ? desc.trim()
                    : 'This seller has not added a store description yet.',
                style: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFF475569), height: 1.45),
              ),
            ],
          ),
        ),

        const SizedBox(height: 14),

        // Information Grid
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: const Color(0xFFEDE4E7)),
          ),
          child: Column(
            children: [
              _buildAboutRow(Icons.calendar_month_outlined, 'Member since', _seller['joined']?.toString() ?? joinedText),
              const Divider(height: 20),
              if (location != null && location.isNotEmpty) ...[
                _buildAboutRow(Icons.local_shipping_outlined, 'Ships from', location),
                const Divider(height: 20),
              ],
              _buildAboutRow(Icons.people_outline, 'Followers', _followersCount.toString()),
              const Divider(height: 20),
              _buildAboutRow(Icons.star_outline, 'Store rating', ratingVal != null ? '${ratingVal.toStringAsFixed(1)} / 5 ($reviewTotal reviews)' : 'No ratings yet'),
              const Divider(height: 20),
              _buildAboutRow(Icons.inventory_2_outlined, 'Active listings', '$productsTotal items'),
              const Divider(height: 20),
              _buildAboutRow(Icons.shopping_bag_outlined, 'Items sold', '$soldTotal sold'),
            ],
          ),
        ),

        const SizedBox(height: 14),

        // Chat Support Callout
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: const Color(0xFFF1F5F9),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Row(
            children: [
              const Icon(Icons.help_outline, color: CartzyColors.navy, size: 22),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Have questions about products or shipping? Use Chat to talk directly with the seller.',
                  style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF334155)),
                ),
              ),
            ],
          ),
        ),

        const SizedBox(height: 32),
      ],
    );
  }

  Widget _buildAboutRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, size: 18, color: CartzyColors.burgundy),
        const SizedBox(width: 10),
        Text(
          label,
          style: GoogleFonts.dmSans(fontSize: 13, color: CartzyColors.gray),
        ),
        const Spacer(),
        Text(
          value,
          style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.bold, color: CartzyColors.navy),
        ),
      ],
    );
  }
}

/// Helper delegate for pinned TabBar
class _SliverTabBarDelegate extends SliverPersistentHeaderDelegate {
  final TabBar _tabBar;

  _SliverTabBarDelegate(this._tabBar);

  @override
  double get minExtent => _tabBar.preferredSize.height;

  @override
  double get maxExtent => _tabBar.preferredSize.height;

  @override
  Widget build(BuildContext context, double shrinkOffset, bool overlapsContent) {
    return Container(
      color: Colors.white,
      child: _tabBar,
    );
  }

  @override
  bool shouldRebuild(_SliverTabBarDelegate oldDelegate) {
    return false;
  }
}

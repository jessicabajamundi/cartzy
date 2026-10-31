import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/catalog_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/widgets/product_card.dart';
import 'package:cartzy/products/product_details_screen.dart';
import 'package:cartzy/guest/guest_cart_screen.dart';
import 'package:cartzy/guest/guest_account_screen.dart';
import 'package:cartzy/buyer/account_settings_screen.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/products/product_list_screen.dart';

enum SearchSortOption {
  relevance,
  latest,
  topSales,
  priceLowHigh,
  priceHighLow,
}

class SearchScreen extends StatefulWidget {
  final String? initialQuery;
  final bool autoFocus;
  final bool isLoggedIn;
  final String? userName;
  final String? userEmail;
  final VoidCallback? onLogin;
  final VoidCallback? onSignUp;
  final VoidCallback? onLogout;

  const SearchScreen({
    super.key,
    this.initialQuery,
    this.autoFocus = true,
    this.isLoggedIn = false,
    this.userName,
    this.userEmail,
    this.onLogin,
    this.onSignUp,
    this.onLogout,
  });

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  static const String _recentSearchesKey = 'cartzy_recent_searches';
  static const int _maxRecentSearches = 10;

  final TextEditingController _searchController = TextEditingController();
  final FocusNode _focusNode = FocusNode();

  List<String> _recentSearches = [];
  List<ProductItem> _allCatalogProducts = [];
  List<ProductItem> _searchResults = [];
  List<String> _suggestions = [];

  bool _isLoading = false;
  bool _hasSearched = false;
  String _activeQuery = '';
  SearchSortOption _currentSort = SearchSortOption.relevance;

  // Auto-sliding bottom navigator
  bool _isNavVisible = true;
  Timer? _scrollStopTimer;

  static const List<String> _trendingSearches = [
    'Linen Maxi Dress',
    'Wireless Noise-Canceling Earbuds',
    'Leather Crossbody Bag',
    'Minimalist Quartz Watch',
    'Ceramic Aesthetic Mug',
    'Casual Canvas Sneakers',
    'Hydrating Facial Serum',
    'Oversized Cotton Tee',
    'Scented Soy Candle',
    'Mechanical Keyboard',
  ];

  static const List<Map<String, dynamic>> _discoveryCategories = [
    {'name': 'Women\'s Fashion', 'icon': Icons.checkroom_rounded},
    {'name': 'Men\'s Fashion', 'icon': Icons.dry_cleaning_rounded},
    {'name': 'Shoes', 'icon': Icons.roller_skating_outlined},
    {'name': 'Bags & Luggage', 'icon': Icons.backpack_outlined},
    {'name': 'Electronics', 'icon': Icons.headphones_rounded},
    {'name': 'Home & Living', 'icon': Icons.home_outlined},
    {'name': 'Beauty', 'icon': Icons.spa_outlined},
    {'name': 'Flash Deals', 'icon': Icons.bolt_rounded},
  ];

  @override
  void initState() {
    super.initState();
    _loadRecentSearches();
    _loadCatalogForSuggestions();

    if (widget.initialQuery != null && widget.initialQuery!.trim().isNotEmpty) {
      _searchController.text = widget.initialQuery!.trim();
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _performSearch(widget.initialQuery!.trim());
      });
    }
  }

  @override
  void dispose() {
    _scrollStopTimer?.cancel();
    _searchController.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  Future<void> _loadRecentSearches() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final list = prefs.getStringList(_recentSearchesKey) ?? [];
      if (mounted) {
        setState(() => _recentSearches = list);
      }
    } catch (_) {}
  }

  Future<void> _saveRecentSearch(String query) async {
    final trimmed = query.trim();
    if (trimmed.isEmpty) return;
    try {
      final prefs = await SharedPreferences.getInstance();
      final list = List<String>.from(_recentSearches);
      list.removeWhere((item) => item.toLowerCase() == trimmed.toLowerCase());
      list.insert(0, trimmed);
      if (list.length > _maxRecentSearches) {
        list.removeRange(_maxRecentSearches, list.length);
      }
      await prefs.setStringList(_recentSearchesKey, list);
      if (mounted) {
        setState(() => _recentSearches = list);
      }
    } catch (_) {}
  }

  Future<void> _removeRecentSearch(String query) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final list = List<String>.from(_recentSearches);
      list.removeWhere((item) => item.toLowerCase() == query.toLowerCase());
      await prefs.setStringList(_recentSearchesKey, list);
      if (mounted) {
        setState(() => _recentSearches = list);
      }
    } catch (_) {}
  }

  Future<void> _clearAllRecentSearches() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_recentSearchesKey);
      if (mounted) {
        setState(() => _recentSearches = []);
      }
    } catch (_) {}
  }

  Future<void> _loadCatalogForSuggestions() async {
    try {
      final products = await CatalogService.fetchProducts(perPage: 50);
      if (mounted) {
        setState(() => _allCatalogProducts = products);
      }
    } catch (_) {}
  }

  void _onQueryChanged(String query) {
    setState(() {
      if (query.trim().isEmpty) {
        _suggestions = [];
        if (!_hasSearched) {
          _activeQuery = '';
        }
      } else {
        final q = query.trim().toLowerCase();
        final matchedNames = _allCatalogProducts
            .where((p) => p.name.toLowerCase().contains(q))
            .map((p) => p.name)
            .take(5)
            .toList();

        final matchedTrends = _trendingSearches
            .where((t) => t.toLowerCase().contains(q) && !matchedNames.contains(t))
            .take(3)
            .toList();

        _suggestions = [...matchedNames, ...matchedTrends];
      }
    });
  }

  Future<void> _performSearch(String query) async {
    final cleanQuery = query.trim();
    if (cleanQuery.isEmpty) return;

    _focusNode.unfocus();
    _searchController.text = cleanQuery;
    _saveRecentSearch(cleanQuery);

    setState(() {
      _isLoading = true;
      _hasSearched = true;
      _activeQuery = cleanQuery;
      _suggestions = [];
    });

    try {
      final results = await CatalogService.fetchProducts(query: cleanQuery);
      if (mounted) {
        setState(() {
          _searchResults = results;
          _isLoading = false;
        });
        _applySort(_currentSort);
      }
    } catch (e) {
      if (mounted) {
        // Fallback filter from local catalog
        final localMatches = _allCatalogProducts.where((p) {
          final q = cleanQuery.toLowerCase();
          return p.name.toLowerCase().contains(q) ||
              p.category.toLowerCase().contains(q) ||
              (p.sellerName != null && p.sellerName!.toLowerCase().contains(q));
        }).toList();

        setState(() {
          _searchResults = localMatches;
          _isLoading = false;
        });
        _applySort(_currentSort);
      }
    }
  }

  void _clearSearch() {
    _searchController.clear();
    setState(() {
      _hasSearched = false;
      _activeQuery = '';
      _searchResults = [];
      _suggestions = [];
    });
    _focusNode.requestFocus();
  }

  void _applySort(SearchSortOption sort) {
    setState(() {
      _currentSort = sort;
      switch (sort) {
        case SearchSortOption.relevance:
          // Default server order
          break;
        case SearchSortOption.latest:
          _searchResults.sort((a, b) => (b.id ?? 0).compareTo(a.id ?? 0));
          break;
        case SearchSortOption.topSales:
          _searchResults.sort((a, b) => _parseSoldCount(b.soldCount)
              .compareTo(_parseSoldCount(a.soldCount)));
          break;
        case SearchSortOption.priceLowHigh:
          _searchResults.sort(
              (a, b) => _parsePrice(a.price).compareTo(_parsePrice(b.price)));
          break;
        case SearchSortOption.priceHighLow:
          _searchResults.sort(
              (a, b) => _parsePrice(b.price).compareTo(_parsePrice(a.price)));
          break;
      }
    });
  }

  double _parsePrice(String price) {
    final clean = price.replaceAll('₱', '').replaceAll(',', '').trim();
    return double.tryParse(clean) ?? 0.0;
  }

  int _parseSoldCount(String sold) {
    final lower =
        sold.toLowerCase().replaceAll('sold', '').replaceAll('+', '').trim();
    if (lower.endsWith('k')) {
      final num = double.tryParse(lower.replaceAll('k', '').trim()) ?? 0;
      return (num * 1000).toInt();
    }
    return int.tryParse(lower) ?? 0;
  }

  void _openDetails(ProductItem product) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => ProductDetailsScreen(
          productId: product.id,
          name: product.name,
          category: product.category,
          price: product.price,
          originalPrice: product.originalPrice,
          imageUrl: product.effectiveImageUrl,
          sellerName: product.sellerName,
          badge: product.badge,
          rating: product.rating,
          soldCount: product.soldCount,
          onBack: () => Navigator.of(context).pop(),
        ),
      ),
    );
  }

  void _handleAddToCart(ProductItem product) {
    if (!AuthService.isAuthenticated && !widget.isLoggedIn) {
      GuestCartScreen.open(context);
      return;
    }
    CartService.instance.addItem(
      name: product.name,
      price: product.price,
      originalPrice: product.originalPrice,
      category: product.category,
      image: product.effectiveImageUrl,
    );
    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('${product.name} added to cart!'),
        backgroundColor: CartzyColors.navy,
        duration: const Duration(seconds: 3),
        action: SnackBarAction(
          label: 'VIEW CART',
          textColor: CartzyColors.rose,
          onPressed: _openCart,
        ),
      ),
    );
  }

  void _openCart() {
    if (widget.isLoggedIn || AuthService.isAuthenticated) {
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => CartScreen(
            onBack: () => Navigator.of(context).pop(),
            onCheckout: () {
              Navigator.of(context).pop();
            },
          ),
        ),
      );
    } else {
      GuestCartScreen.open(context);
    }
  }

  void _openProfile() {
    if (widget.isLoggedIn || AuthService.isAuthenticated) {
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => AccountSettingsScreen(
            isLoggedIn: true,
            userName: widget.userName ?? 'Buyer',
            userEmail: widget.userEmail ?? '',
            onBack: () => Navigator.of(context).pop(),
            onLogout: widget.onLogout ?? () => Navigator.of(context).pop(),
          ),
        ),
      );
    } else {
      GuestAccountScreen.open(
        context,
        onLogin: widget.onLogin ?? () => Navigator.of(context).pushNamed('/login'),
        onSignUp: widget.onSignUp ?? () => Navigator.of(context).pushNamed('/signup'),
      );
    }
  }

  void _openSale() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => const ProductListScreen(
          initialCategory: 'Flash Deals',
          title: 'Sale & Flash Deals',
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      body: SafeArea(
        child: Stack(
          children: [
            Column(
              children: [
                // ─────────────────────────────────────────────────────────
                // STICKY TOP SEARCH APP BAR
                // ─────────────────────────────────────────────────────────
                _buildSearchAppBar(),

                // ─────────────────────────────────────────────────────────
                // DYNAMIC BODY
                // ─────────────────────────────────────────────────────────
                Expanded(
                  child: NotificationListener<ScrollNotification>(
                    onNotification: (scrollInfo) {
                      if (scrollInfo is ScrollUpdateNotification) {
                        if (scrollInfo.metrics.pixels <= 20) {
                          if (!_isNavVisible) setState(() => _isNavVisible = true);
                        } else if (_isNavVisible) {
                          setState(() => _isNavVisible = false);
                        }
                        _scrollStopTimer?.cancel();
                        _scrollStopTimer = Timer(const Duration(milliseconds: 300), () {
                          if (mounted && !_isNavVisible) {
                            setState(() => _isNavVisible = true);
                          }
                        });
                      }
                      return false;
                    },
                    child: _buildSearchBody(),
                  ),
                ),
              ],
            ),

            // ─────────────────────────────────────────────────────────
            // BOTTOM NAV BAR (Home, Search, Cart, Sale, Profile)
            // ─────────────────────────────────────────────────────────
            Positioned(
              left: 0,
              right: 0,
              bottom: 0,
              child: AnimatedSlide(
                offset: _isNavVisible ? Offset.zero : const Offset(0, 1.8),
                duration: const Duration(milliseconds: 260),
                curve: Curves.easeInOutCubic,
                child: AnimatedOpacity(
                  opacity: _isNavVisible ? 1.0 : 0.0,
                  duration: const Duration(milliseconds: 200),
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
                    padding:
                        const EdgeInsets.symmetric(horizontal: 6, vertical: 8),
                    child: Row(
                      children: [
                        _SearchBottomNavItem(
                          icon: Icons.home_rounded,
                          label: 'Home',
                          selected: false,
                          onClick: () => Navigator.of(context).maybePop(),
                        ),
                        _SearchBottomNavItem(
                          icon: Icons.shopping_cart_outlined,
                          label: 'Cart',
                          selected: false,
                          onClick: _openCart,
                        ),
                        _SearchBottomNavItem(
                          icon: Icons.local_offer_outlined,
                          label: 'Sale',
                          selected: false,
                          isSale: true,
                          onClick: _openSale,
                        ),
                        _SearchBottomNavItem(
                          icon: Icons.person_outline_rounded,
                          label: 'Profile',
                          selected: false,
                          onClick: _openProfile,
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────────
  // SEARCH APP BAR
  // ─────────────────────────────────────────────────────────────────
  Widget _buildSearchAppBar() {
    return Container(
      padding: const EdgeInsets.fromLTRB(8, 8, 12, 10),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        border: Border(
          bottom: BorderSide(color: CartzyColors.border.withValues(alpha: 0.7)),
        ),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 8,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(
              Icons.arrow_back_ios_new_rounded,
              size: 20,
              color: CartzyColors.navy,
            ),
            onPressed: () => Navigator.of(context).maybePop(),
            splashRadius: 22,
          ),
          Expanded(
            child: Container(
              height: 42,
              decoration: BoxDecoration(
                color: const Color(0xFFF3F1F4),
                borderRadius: BorderRadius.circular(24),
                border: Border.all(
                  color: _focusNode.hasFocus
                      ? CartzyColors.brandDark
                      : const Color(0xFFE5E0E8),
                  width: 1.2,
                ),
              ),
              child: Row(
                children: [
                  const SizedBox(width: 12),
                  const Icon(
                    Icons.search_rounded,
                    color: CartzyColors.brandDeep,
                    size: 20,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: TextField(
                      controller: _searchController,
                      focusNode: _focusNode,
                      autofocus: widget.autoFocus,
                      textInputAction: TextInputAction.search,
                      onChanged: _onQueryChanged,
                      onSubmitted: _performSearch,
                      style: GoogleFonts.dmSans(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w500,
                        color: CartzyColors.navy,
                      ),
                      decoration: InputDecoration(
                        isDense: true,
                        contentPadding: const EdgeInsets.symmetric(vertical: 10),
                        hintText: 'Search dresses, bags, electronics...',
                        hintStyle: GoogleFonts.dmSans(
                          fontSize: 13,
                          color: CartzyColors.grayMid,
                        ),
                        border: InputBorder.none,
                      ),
                    ),
                  ),
                  if (_searchController.text.isNotEmpty)
                    IconButton(
                      icon: const Icon(
                        Icons.close_rounded,
                        color: CartzyColors.grayMid,
                        size: 18,
                      ),
                      onPressed: _clearSearch,
                      splashRadius: 18,
                    )
                  else
                    const SizedBox(width: 8),
                ],
              ),
            ),
          ),
          const SizedBox(width: 10),
          GestureDetector(
            onTap: () => _performSearch(_searchController.text),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [CartzyColors.brandDark, CartzyColors.brandDeep],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(20),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x20564B68),
                    blurRadius: 6,
                    offset: Offset(0, 2),
                  ),
                ],
              ),
              child: Text(
                'Search',
                style: GoogleFonts.dmSans(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────────
  // BODY SWITCHER
  // ─────────────────────────────────────────────────────────────────
  Widget _buildSearchBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          color: CartzyColors.brandDark,
        ),
      );
    }

    // 1. Suggestions list while typing
    if (_suggestions.isNotEmpty && !_hasSearched) {
      return _buildSuggestionsList();
    }

    // 2. Active search results
    if (_hasSearched) {
      return _buildSearchResults();
    }

    // 3. Initial discovery view (History, Trending, Categories)
    return _buildDiscoveryView();
  }

  // ─────────────────────────────────────────────────────────────────
  // SUGGESTIONS LIST (LIVE TYPEAHEAD)
  // ─────────────────────────────────────────────────────────────────
  Widget _buildSuggestionsList() {
    return ListView.separated(
      padding: const EdgeInsets.symmetric(vertical: 8),
      itemCount: _suggestions.length,
      separatorBuilder: (_, __) => Divider(
        height: 1,
        indent: 48,
        color: CartzyColors.border.withValues(alpha: 0.5),
      ),
      itemBuilder: (context, index) {
        final suggestion = _suggestions[index];
        return ListTile(
          dense: true,
          contentPadding: const EdgeInsets.symmetric(horizontal: 16),
          leading: const Icon(
            Icons.search_rounded,
            size: 19,
            color: CartzyColors.brandMuted,
          ),
          title: Text(
            suggestion,
            style: GoogleFonts.dmSans(
              fontSize: 13.5,
              fontWeight: FontWeight.w500,
              color: CartzyColors.navy,
            ),
          ),
          trailing: IconButton(
            icon: const Icon(
              Icons.north_west_rounded,
              size: 16,
              color: CartzyColors.grayMid,
            ),
            onPressed: () {
              _searchController.text = suggestion;
              _searchController.selection = TextSelection.fromPosition(
                TextPosition(offset: suggestion.length),
              );
              _onQueryChanged(suggestion);
            },
            splashRadius: 18,
          ),
          onTap: () => _performSearch(suggestion),
        );
      },
    );
  }

  // ─────────────────────────────────────────────────────────────────
  // DISCOVERY VIEW (RECENT + TRENDING + CATEGORIES)
  // ─────────────────────────────────────────────────────────────────
  Widget _buildDiscoveryView() {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 90),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── RECENT SEARCHES ──
          if (_recentSearches.isNotEmpty) ...[
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(
                      Icons.history_rounded,
                      size: 19,
                      color: CartzyColors.brandDark,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Recent Searches',
                      style: GoogleFonts.playfairDisplay(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: CartzyColors.navy,
                      ),
                    ),
                  ],
                ),
                GestureDetector(
                  onTap: _clearAllRecentSearches,
                  child: Row(
                    children: [
                      const Icon(
                        Icons.delete_outline_rounded,
                        size: 16,
                        color: CartzyColors.grayMid,
                      ),
                      const SizedBox(width: 3),
                      Text(
                        'Clear',
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          color: CartzyColors.grayMid,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _recentSearches.map((term) {
                return Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFF6F4F7),
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(
                      color: CartzyColors.border.withValues(alpha: 0.8),
                    ),
                  ),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(18),
                    onTap: () => _performSearch(term),
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(12, 6, 6, 6),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            term,
                            style: GoogleFonts.dmSans(
                              fontSize: 12.5,
                              color: CartzyColors.navy,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(width: 4),
                          GestureDetector(
                            onTap: () => _removeRecentSearch(term),
                            child: const Padding(
                              padding: EdgeInsets.all(2.0),
                              child: Icon(
                                Icons.close_rounded,
                                size: 14,
                                color: CartzyColors.grayMid,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 24),
          ],

          // ── TRENDING SEARCHES ──
          Row(
            children: [
              const Icon(
                Icons.local_fire_department_rounded,
                size: 20,
                color: CartzyColors.rose700,
              ),
              const SizedBox(width: 6),
              Text(
                'Trending on Cartzy',
                style: GoogleFonts.playfairDisplay(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: CartzyColors.navy,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: List.generate(_trendingSearches.length, (index) {
              final term = _trendingSearches[index];
              final isTopThree = index < 3;
              return InkWell(
                borderRadius: BorderRadius.circular(16),
                onTap: () => _performSearch(term),
                child: Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 6.5),
                  decoration: BoxDecoration(
                    color: isTopThree
                        ? const Color(0xFFFDF6F5)
                        : CartzyColors.surface,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isTopThree
                          ? CartzyColors.rose200
                          : CartzyColors.border,
                      width: 1,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 17,
                        height: 17,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: isTopThree
                              ? (index == 0
                                  ? CartzyColors.rose700
                                  : index == 1
                                      ? CartzyColors.rose500
                                      : CartzyColors.brandDeep)
                              : const Color(0xFFDFDBE5),
                        ),
                        alignment: Alignment.center,
                        child: Text(
                          '${index + 1}',
                          style: GoogleFonts.dmSans(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: isTopThree ? Colors.white : CartzyColors.navy,
                          ),
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        term,
                        style: GoogleFonts.dmSans(
                          fontSize: 12.5,
                          fontWeight:
                              isTopThree ? FontWeight.w600 : FontWeight.w500,
                          color: isTopThree
                              ? CartzyColors.rose900
                              : CartzyColors.navy,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }),
          ),
          const SizedBox(height: 28),

          // ── EXPLORE CATEGORIES ──
          Row(
            children: [
              const Icon(
                Icons.category_outlined,
                size: 19,
                color: CartzyColors.brandDark,
              ),
              const SizedBox(width: 6),
              Text(
                'Explore Categories',
                style: GoogleFonts.playfairDisplay(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: CartzyColors.navy,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _discoveryCategories.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 4,
              childAspectRatio: 0.85,
              crossAxisSpacing: 10,
              mainAxisSpacing: 12,
            ),
            itemBuilder: (context, index) {
              final cat = _discoveryCategories[index];
              return InkWell(
                borderRadius: BorderRadius.circular(12),
                onTap: () => _performSearch(cat['name'] as String),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [
                            CartzyColors.brand50Lavender,
                            CartzyColors.rose50Warm
                          ],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        shape: BoxShape.circle,
                        border: Border.all(color: CartzyColors.border),
                        boxShadow: const [
                          BoxShadow(
                            color: Color(0x0C564B68),
                            blurRadius: 6,
                            offset: Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Icon(
                        cat['icon'] as IconData,
                        color: CartzyColors.brandDark,
                        size: 22,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      cat['name'] as String,
                      textAlign: TextAlign.center,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.dmSans(
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                        color: CartzyColors.navy,
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────────
  // SEARCH RESULTS VIEW WITH SORT BAR & 2-COLUMN GRID
  // ─────────────────────────────────────────────────────────────────
  Widget _buildSearchResults() {
    return Column(
      children: [
        // ── SORT FILTER BAR ──
        _buildSortBar(),

        // ── RESULTS COUNT / SUMMARY ──
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          color: CartzyColors.background,
          child: Row(
            children: [
              Text(
                'Results for "$_activeQuery"',
                style: GoogleFonts.dmSans(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w600,
                  color: CartzyColors.navy,
                ),
              ),
              const SizedBox(width: 6),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                decoration: BoxDecoration(
                  color: CartzyColors.brand100,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '${_searchResults.length}',
                  style: GoogleFonts.dmSans(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.brandDark,
                  ),
                ),
              ),
              const Spacer(),
              InkWell(
                onTap: _clearSearch,
                child: Text(
                  'Clear filter',
                  style: GoogleFonts.dmSans(
                    fontSize: 12,
                    color: CartzyColors.rose700,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
            ],
          ),
        ),

        // ── PRODUCT GRID OR EMPTY STATE ──
        Expanded(
          child: _searchResults.isEmpty
              ? _buildEmptyResultsState()
              : LayoutBuilder(
                  builder: (context, constraints) {
                    final cardWidth = (constraints.maxWidth - 32 - 10) / 2;
                    final cardHeight =
                        cardWidth + ProductCard.bodyHeight(context);

                    return RefreshIndicator(
                      color: CartzyColors.brandDark,
                      onRefresh: () => _performSearch(_activeQuery),
                      child: GridView.builder(
                        padding: const EdgeInsets.fromLTRB(16, 4, 16, 90),
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 2,
                          crossAxisSpacing: 10,
                          mainAxisSpacing: 12,
                          childAspectRatio: cardWidth / cardHeight,
                        ),
                        itemCount: _searchResults.length,
                        itemBuilder: (context, index) {
                          final product = _searchResults[index];
                          return ProductCard(
                            name: product.name,
                            price: product.price,
                            originalPrice: product.originalPrice,
                            discountPercent: product.discountPercent,
                            badge: product.badge,
                            rating: product.rating,
                            soldCount: product.soldCount,
                            imageUrl: product.effectiveImageUrl,
                            sellerName: product.sellerName,
                            inStock: product.inStock,
                            stock: product.stock,
                            hasFreeShipping: product.hasFreeShipping,
                            onTap: () => _openDetails(product),
                            onAddToCart: () => _handleAddToCart(product),
                          );
                        },
                      ),
                    );
                  },
                ),
        ),
      ],
    );
  }

  // ─────────────────────────────────────────────────────────────────
  // SORT BAR (Relevance, Latest, Top Sales, Price Low/High)
  // ─────────────────────────────────────────────────────────────────
  Widget _buildSortBar() {
    return Container(
      height: 44,
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        border: Border(
          bottom: BorderSide(
            color: CartzyColors.border.withValues(alpha: 0.6),
          ),
        ),
      ),
      child: Row(
        children: [
          _buildSortTab(
            label: 'Relevance',
            isSelected: _currentSort == SearchSortOption.relevance,
            onTap: () => _applySort(SearchSortOption.relevance),
          ),
          _buildSortTab(
            label: 'Latest',
            isSelected: _currentSort == SearchSortOption.latest,
            onTap: () => _applySort(SearchSortOption.latest),
          ),
          _buildSortTab(
            label: 'Top Sales',
            isSelected: _currentSort == SearchSortOption.topSales,
            onTap: () => _applySort(SearchSortOption.topSales),
          ),
          _buildPriceSortTab(),
        ],
      ),
    );
  }

  Widget _buildSortTab({
    required String label,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: InkWell(
        onTap: onTap,
        child: Container(
          alignment: Alignment.center,
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: isSelected ? CartzyColors.rose700 : Colors.transparent,
                width: 2.2,
              ),
            ),
          ),
          child: Text(
            label,
            style: GoogleFonts.dmSans(
              fontSize: 12.5,
              fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
              color: isSelected ? CartzyColors.rose700 : CartzyColors.navy,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPriceSortTab() {
    final isPriceActive = _currentSort == SearchSortOption.priceLowHigh ||
        _currentSort == SearchSortOption.priceHighLow;

    return Expanded(
      child: InkWell(
        onTap: () {
          if (_currentSort == SearchSortOption.priceLowHigh) {
            _applySort(SearchSortOption.priceHighLow);
          } else {
            _applySort(SearchSortOption.priceLowHigh);
          }
        },
        child: Container(
          alignment: Alignment.center,
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: isPriceActive ? CartzyColors.rose700 : Colors.transparent,
                width: 2.2,
              ),
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                'Price',
                style: GoogleFonts.dmSans(
                  fontSize: 12.5,
                  fontWeight:
                      isPriceActive ? FontWeight.w700 : FontWeight.w500,
                  color: isPriceActive ? CartzyColors.rose700 : CartzyColors.navy,
                ),
              ),
              const SizedBox(width: 2),
              Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.arrow_drop_up_rounded,
                    size: 14,
                    color: _currentSort == SearchSortOption.priceLowHigh
                        ? CartzyColors.rose700
                        : CartzyColors.grayLight,
                  ),
                  Icon(
                    Icons.arrow_drop_down_rounded,
                    size: 14,
                    color: _currentSort == SearchSortOption.priceHighLow
                        ? CartzyColors.rose700
                        : CartzyColors.grayLight,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────────
  // EMPTY RESULTS STATE
  // ─────────────────────────────────────────────────────────────────
  Widget _buildEmptyResultsState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: const BoxDecoration(
                color: CartzyColors.brand100,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.search_off_rounded,
                size: 40,
                color: CartzyColors.brandDeep,
              ),
            ),
            const SizedBox(height: 18),
            Text(
              'No items found',
              style: GoogleFonts.playfairDisplay(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'We couldn\'t find any items matching "$_activeQuery". Try checking for spelling or searching for another keyword.',
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(
                fontSize: 13,
                color: CartzyColors.grayMid,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: _clearSearch,
              style: ElevatedButton.styleFrom(
                backgroundColor: CartzyColors.brandDark,
                foregroundColor: Colors.white,
                shape: const StadiumBorder(),
                padding:
                    const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                elevation: 0,
              ),
              child: Text(
                'Explore Trending Items',
                style: GoogleFonts.dmSans(
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────
// BOTTOM NAV ITEM (SEARCH SCREEN)
// ─────────────────────────────────────────────────────────────────
class _SearchBottomNavItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final bool isSale;
  final VoidCallback onClick;

  const _SearchBottomNavItem({
    required this.icon,
    required this.label,
    required this.selected,
    this.isSale = false,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    final color = isSale
        ? (selected ? CartzyColors.rose : CartzyColors.roseDark)
        : (selected ? CartzyColors.navy : CartzyColors.grayLight);

    return Expanded(
      child: InkWell(
        onTap: onClick,
        borderRadius: BorderRadius.circular(20),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, size: 20, color: color),
              const SizedBox(height: 2),
              Text(
                label,
                style: GoogleFonts.dmSans(
                  fontSize: 10.5,
                  fontWeight: selected ? FontWeight.bold : FontWeight.w500,
                  color: color,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

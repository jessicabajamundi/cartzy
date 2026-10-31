import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/home_sections.dart';
import 'package:cartzy/services/catalog_service.dart';
import 'package:cartzy/products/product_list_screen.dart';

class AllCategoriesScreen extends StatefulWidget {
  final VoidCallback? onBack;

  const AllCategoriesScreen({super.key, this.onBack});

  @override
  State<AllCategoriesScreen> createState() => _AllCategoriesScreenState();
}

class _AllCategoriesScreenState extends State<AllCategoriesScreen> {
  final TextEditingController _searchController = TextEditingController();
  List<CategoryIconItem> _allCategories = List.from(categoryIcons);
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _loadLiveCategories();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadLiveCategories() async {
    try {
      final liveNames = await CatalogService.fetchCategoryNames();
      if (liveNames.isNotEmpty && mounted) {
        final existingMap = {for (var item in categoryIcons) item.label.toLowerCase(): item.icon};
        final merged = liveNames.map((name) {
          final icon = existingMap[name.toLowerCase()] ?? Icons.grid_view_outlined;
          return CategoryIconItem(label: name, icon: icon);
        }).toList();

        setState(() {
          _allCategories = merged;
        });
      }
    } catch (_) {}
  }

  List<CategoryIconItem> get _filteredCategories {
    if (_searchQuery.trim().isEmpty) return _allCategories;
    final q = _searchQuery.toLowerCase().trim();
    return _allCategories.where((c) => c.label.toLowerCase().contains(q)).toList();
  }

  void _openCategory(String categoryName) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => ProductListScreen(
          initialCategory: categoryName == 'General' ? 'All' : categoryName,
          title: categoryName,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final list = _filteredCategories;

    return Scaffold(
      backgroundColor: const Color(0xFFFBF8F7),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, size: 18, color: CartzyColors.navy),
          onPressed: widget.onBack ?? () => Navigator.of(context).pop(),
        ),
        title: Text(
          'All Categories',
          style: GoogleFonts.playfairDisplay(
            color: CartzyColors.navy,
            fontSize: 20,
            fontWeight: FontWeight.w700,
          ),
        ),
        centerTitle: true,
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(color: CartzyColors.border, height: 1),
        ),
      ),
      body: Column(
        children: [
          // ── Search & Header Section ──
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 14),
            child: Column(
              children: [
                // Search Input
                Container(
                  height: 44,
                  decoration: BoxDecoration(
                    color: const Color(0xFFF5EFEB),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFEADFD9)),
                  ),
                  child: TextField(
                    controller: _searchController,
                    onChanged: (val) => setState(() => _searchQuery = val),
                    style: GoogleFonts.dmSans(fontSize: 14, color: CartzyColors.navy),
                    decoration: InputDecoration(
                      hintText: 'Search categories...',
                      hintStyle: GoogleFonts.dmSans(fontSize: 13.5, color: CartzyColors.gray),
                      prefixIcon: const Icon(Icons.search, size: 20, color: CartzyColors.burgundy),
                      suffixIcon: _searchQuery.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear, size: 18, color: CartzyColors.gray),
                              onPressed: () {
                                _searchController.clear();
                                setState(() => _searchQuery = '');
                              },
                            )
                          : null,
                      filled: false,
                      fillColor: Colors.transparent,
                      border: InputBorder.none,
                      enabledBorder: InputBorder.none,
                      focusedBorder: InputBorder.none,
                      errorBorder: InputBorder.none,
                      disabledBorder: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                  ),
                ),
              ],
            ),
          ),

          // ── Quick All Products Banner ──
          if (_searchQuery.isEmpty)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 4),
              child: GestureDetector(
                onTap: () => _openCategory('All'),
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF2C1E21), Color(0xFF5A2A33)],
                      begin: Alignment.centerLeft,
                      end: Alignment.centerRight,
                    ),
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.08),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.15),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(Icons.apps_rounded, color: Colors.white, size: 20),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'View Everything',
                              style: GoogleFonts.playfairDisplay(
                                color: Colors.white,
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Browse all products in our catalog',
                              style: GoogleFonts.dmSans(
                                color: Colors.white.withValues(alpha: 0.8),
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white, size: 14),
                    ],
                  ),
                ),
              ),
            ),

          // ── Categories Grid ──
          Expanded(
            child: list.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.search_off_rounded, size: 48, color: CartzyColors.grayLight),
                        const SizedBox(height: 12),
                        Text(
                          'No categories found for "$_searchQuery"',
                          style: GoogleFonts.dmSans(
                            color: CartzyColors.gray,
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  )
                : LayoutBuilder(
                    builder: (context, constraints) {
                      final isTablet = constraints.maxWidth >= 600;
                      final crossAxisCount = isTablet ? 3 : 2;

                      return GridView.builder(
                        padding: const EdgeInsets.all(16),
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: crossAxisCount,
                          crossAxisSpacing: 12,
                          mainAxisSpacing: 12,
                          mainAxisExtent: 44 +
                              MediaQuery.textScalerOf(context).scale(56),
                        ),
                        itemCount: list.length,
                        itemBuilder: (context, index) {
                          final item = list[index];
                          return _CategoryTile(
                            item: item,
                            onTap: () => _openCategory(item.label),
                          );
                        },
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

class _CategoryTile extends StatelessWidget {
  final CategoryIconItem item;
  final VoidCallback onTap;

  const _CategoryTile({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(16),
      elevation: 0,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        splashColor: CartzyColors.roseSoft,
        highlightColor: const Color(0xFFF9F2F0),
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFEDE2DF), width: 1.1),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.02),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          child: Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: const Color(0xFFFBF2EE),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFF0DFDA), width: 1),
                ),
                child: Icon(
                  item.icon,
                  size: 22,
                  color: CartzyColors.burgundy,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      item.label,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: CartzyColors.navy,
                        height: 1.2,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Flexible(
                          child: Text(
                            'Explore',
                            style: GoogleFonts.dmSans(
                              fontSize: 11,
                              fontWeight: FontWeight.w500,
                              color: const Color(0xFF9E7766),
                            ),
                          ),
                        ),
                        const SizedBox(width: 2),
                        const Icon(
                          Icons.arrow_forward_ios_rounded,
                          size: 8,
                          color: Color(0xFF9E7766),
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
    );
  }
}

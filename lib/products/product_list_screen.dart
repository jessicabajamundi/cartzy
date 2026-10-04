import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/product_card.dart';
import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/utils/product_images.dart';
import 'product_details_screen.dart';

class ProductItem {
  final String name;
  final String category;
  final String price;
  final String? originalPrice;
  final int discountPercent;
  final String badge;
  final double rating;
  final String soldCount;
  final String? imageUrl;

  const ProductItem({
    required this.name,
    required this.category,
    required this.price,
    this.originalPrice,
    required this.discountPercent,
    required this.badge,
    required this.rating,
    required this.soldCount,
    this.imageUrl,
  });

  String get effectiveImageUrl => (imageUrl != null && imageUrl!.isNotEmpty)
      ? imageUrl!
      : CartzyProductImages.getImage(name, category);
}

// ─── All products ─────────────────────────────────────────────────────────────
const List<ProductItem> allCatalogProducts = [
  // Electronics
  ProductItem(name: 'Smart Fitness Tracker Watch with Blood Oxygen', category: 'Electronics', price: '₱1,299', originalPrice: '₱1,999', discountPercent: 35, badge: 'Official', rating: 4.9, soldCount: '3.4k sold'),
  ProductItem(name: 'ANC Pro Wireless Noise Cancelling Earphones', category: 'Electronics', price: '₱890', originalPrice: '₱1,850', discountPercent: 52, badge: 'Official', rating: 5.0, soldCount: '8.9k sold'),
  ProductItem(name: 'Instant Print Camera Retro Vintage Pocket Edition', category: 'Electronics', price: '₱3,499', originalPrice: '₱4,999', discountPercent: 30, badge: 'Preferred', rating: 4.8, soldCount: '2.1k sold'),
  ProductItem(name: 'Smart Watch Ultra GPS Cellular 49mm', category: 'Electronics', price: '₱3,299', originalPrice: '₱9,399', discountPercent: 65, badge: 'Official', rating: 4.8, soldCount: '12.1k sold'),
  ProductItem(name: 'Wireless Bluetooth Headphones Over-Ear 40H Battery', category: 'Electronics', price: '₱1,920', originalPrice: '₱3,840', discountPercent: 50, badge: 'Preferred', rating: 4.6, soldCount: '5.3k sold'),
  ProductItem(name: '65W Fast Charger GaN USB-C and USB-A Dual Port', category: 'Electronics', price: '₱599', originalPrice: '₱999', discountPercent: 40, badge: 'Official', rating: 4.8, soldCount: '22k sold'),
  ProductItem(name: 'Portable Bluetooth Speaker 360 Surround IPX7', category: 'Electronics', price: '₱1,150', originalPrice: '₱1,800', discountPercent: 36, badge: 'Preferred', rating: 4.6, soldCount: '4.1k sold'),
  ProductItem(name: 'Mechanical RGB Gaming Keyboard TKL Compact', category: 'Electronics', price: '₱2,299', originalPrice: '₱3,500', discountPercent: 34, badge: 'Official', rating: 4.7, soldCount: '1.9k sold'),
  ProductItem(name: '4K Action Camera Waterproof 30m Wide Angle', category: 'Electronics', price: '₱4,499', originalPrice: '₱6,999', discountPercent: 36, badge: 'Official', rating: 4.8, soldCount: '2.3k sold'),
  ProductItem(name: 'Tablet 10.4 inch 2K Display 8GB RAM 256GB', category: 'Electronics', price: '₱8,999', originalPrice: '₱12,999', discountPercent: 31, badge: 'Official', rating: 4.7, soldCount: '980 sold'),

  // Women's
  ProductItem(name: 'Floral Wrap Midi Dress Bohemian Summer', category: "Women's", price: '₱599', originalPrice: '₱899', discountPercent: 33, badge: 'Preferred', rating: 4.7, soldCount: '4.2k sold'),
  ProductItem(name: 'High-Waist Slim Fit Denim Jeans Stretch Fabric', category: "Women's", price: '₱599', originalPrice: '₱899', discountPercent: 33, badge: 'Preferred', rating: 4.6, soldCount: '6.7k sold'),
  ProductItem(name: 'Leather Crossbody Bag Mini Flap with Gold Chain', category: "Women's", price: '₱799', originalPrice: '₱1,299', discountPercent: 38, badge: 'Official', rating: 4.7, soldCount: '2.4k sold'),
  ProductItem(name: 'Oversized Linen Shirt Breathable Summer Casual', category: "Women's", price: '₱349', originalPrice: '₱499', discountPercent: 30, badge: 'Preferred', rating: 4.5, soldCount: '3.1k sold'),
  ProductItem(name: 'Block Heel Ankle Strap Sandals Elegant Formal', category: "Women's", price: '₱849', originalPrice: '₱1,399', discountPercent: 39, badge: 'Official', rating: 4.6, soldCount: '1.5k sold'),
  ProductItem(name: 'Ribbed Knit Bodycon Dress Long Sleeve', category: "Women's", price: '₱499', originalPrice: '₱749', discountPercent: 33, badge: 'Preferred', rating: 4.8, soldCount: '2.9k sold'),
  ProductItem(name: 'Canvas Tote Bag Large Printed Eco Reusable', category: "Women's", price: '₱249', originalPrice: '₱399', discountPercent: 38, badge: 'Official', rating: 4.5, soldCount: '8.7k sold'),

  // Men's
  ProductItem(name: 'Slim Fit Cotton Oxford Shirt Business Casual', category: "Men's", price: '₱499', originalPrice: '₱799', discountPercent: 38, badge: 'Official', rating: 4.6, soldCount: '3.2k sold'),
  ProductItem(name: 'Cargo Pants Multi-Pocket Tactical Outdoor', category: "Men's", price: '₱649', originalPrice: '₱999', discountPercent: 35, badge: 'Preferred', rating: 4.7, soldCount: '2.8k sold'),
  ProductItem(name: 'Minimalist Leather Bifold Wallet RFID Blocking', category: "Men's", price: '₱349', originalPrice: '₱599', discountPercent: 42, badge: 'Official', rating: 4.8, soldCount: '9.1k sold'),
  ProductItem(name: 'Classic Polo Shirt Breathable Pique Cotton', category: "Men's", price: '₱299', originalPrice: '₱449', discountPercent: 33, badge: 'Preferred', rating: 4.5, soldCount: '5.4k sold'),
  ProductItem(name: 'Canvas Sneakers Low-Top Vulcanized Daily Wear', category: "Men's", price: '₱549', originalPrice: '₱849', discountPercent: 35, badge: 'Official', rating: 4.6, soldCount: '4.0k sold'),
  ProductItem(name: 'Waterproof Windbreaker Jacket Packable Hoodie', category: "Men's", price: '₱999', originalPrice: '₱1,599', discountPercent: 38, badge: 'Official', rating: 4.8, soldCount: '1.6k sold'),

  // Sports
  ProductItem(name: 'Retro Colorblock Sneaker Lightweight Running', category: 'Sports', price: '₱650', originalPrice: '₱812', discountPercent: 20, badge: 'Preferred', rating: 4.7, soldCount: '920 sold'),
  ProductItem(name: 'Pro Running Shoes Breathable Mesh Anti-Slip Sole', category: 'Sports', price: '₱2,470', originalPrice: '₱4,490', discountPercent: 45, badge: 'Preferred', rating: 4.7, soldCount: '3.6k sold'),
  ProductItem(name: 'Yoga Mat 6mm Non-Slip TPE Eco-Friendly with Strap', category: 'Sports', price: '₱799', originalPrice: '₱1,299', discountPercent: 38, badge: 'Official', rating: 4.8, soldCount: '7.3k sold'),
  ProductItem(name: 'Resistance Bands Set 5-Level Heavy Duty Loop', category: 'Sports', price: '₱349', originalPrice: '₱599', discountPercent: 42, badge: 'Preferred', rating: 4.7, soldCount: '4.5k sold'),
  ProductItem(name: 'Stainless Steel Insulated Water Bottle 1L BPA-Free', category: 'Sports', price: '₱549', originalPrice: '₱849', discountPercent: 35, badge: 'Official', rating: 4.9, soldCount: '20.1k sold'),
  ProductItem(name: 'Jump Rope Speed Cable Skipping Adjustable Ball Bearing', category: 'Sports', price: '₱229', originalPrice: '₱399', discountPercent: 43, badge: 'Preferred', rating: 4.6, soldCount: '6.2k sold'),
  ProductItem(name: 'Dumbbell Set Adjustable 20kg Home Gym Rack', category: 'Sports', price: '₱2,999', originalPrice: '₱4,999', discountPercent: 40, badge: 'Official', rating: 4.9, soldCount: '1.4k sold'),
  ProductItem(name: 'Sports Compression Shorts Quick Dry 5-Inch Inseam', category: 'Sports', price: '₱349', originalPrice: '₱549', discountPercent: 36, badge: 'Preferred', rating: 4.5, soldCount: '3.8k sold'),

  // Home & Garden
  ProductItem(name: 'Ceramic Aesthetic Coffee Mug and Saucer Set', category: 'Home & Garden', price: '₱280', originalPrice: '₱400', discountPercent: 30, badge: 'Official', rating: 4.9, soldCount: '540 sold'),
  ProductItem(name: 'Aromatherapy Diffuser 500ml LED Night Light Timer', category: 'Home & Garden', price: '₱599', originalPrice: '₱999', discountPercent: 40, badge: 'Official', rating: 4.8, soldCount: '8.9k sold'),
  ProductItem(name: 'Non-Stick Granite Frying Pan 28cm Induction Ready', category: 'Home & Garden', price: '₱849', originalPrice: '₱1,299', discountPercent: 35, badge: 'Official', rating: 4.9, soldCount: '11.3k sold'),
  ProductItem(name: 'Blackout Curtains Thermal Insulated 140x240cm Pair', category: 'Home & Garden', price: '₱1,099', originalPrice: '₱1,799', discountPercent: 39, badge: 'Preferred', rating: 4.6, soldCount: '1.7k sold'),
  ProductItem(name: 'Bamboo Bedsheet Set 4-Piece Queen Size Cooling', category: 'Home & Garden', price: '₱1,299', originalPrice: '₱1,999', discountPercent: 35, badge: 'Official', rating: 4.8, soldCount: '5.2k sold'),
  ProductItem(name: 'Digital Kitchen Food Scale 5kg Precision 1g LCD', category: 'Home & Garden', price: '₱299', originalPrice: '₱499', discountPercent: 40, badge: 'Official', rating: 4.8, soldCount: '3.8k sold'),
  ProductItem(name: 'Garden Pruning Shears Stainless Steel Ergonomic', category: 'Home & Garden', price: '₱349', originalPrice: '₱549', discountPercent: 36, badge: 'Preferred', rating: 4.7, soldCount: '2.1k sold'),
  ProductItem(name: 'Solar Garden Lights Outdoor LED Path Lamp Set of 8', category: 'Home & Garden', price: '₱599', originalPrice: '₱899', discountPercent: 33, badge: 'Official', rating: 4.6, soldCount: '3.5k sold'),

  // Health & Beauty
  ProductItem(name: 'Premium Multivitamin Daily Pack 30-day Supply', category: 'Health & Beauty', price: '₱549', originalPrice: '₱1,220', discountPercent: 55, badge: 'Official', rating: 4.9, soldCount: '14.2k sold'),
  ProductItem(name: 'Hyaluronic Acid Serum 2% B5 Intense Hydration', category: 'Health & Beauty', price: '₱399', originalPrice: '₱649', discountPercent: 38, badge: 'Official', rating: 4.9, soldCount: '31k sold'),
  ProductItem(name: 'Sunscreen SPF 50+ PA++++ Lightweight No White Cast', category: 'Health & Beauty', price: '₱349', originalPrice: '₱549', discountPercent: 36, badge: 'Preferred', rating: 4.8, soldCount: '18.4k sold'),
  ProductItem(name: 'Vitamin C Brightening Face Wash Gentle Foam 150ml', category: 'Health & Beauty', price: '₱199', originalPrice: '₱299', discountPercent: 33, badge: 'Official', rating: 4.7, soldCount: '9.6k sold'),
  ProductItem(name: 'Electric Facial Cleanser Silicone Sonic Brush Waterproof', category: 'Health & Beauty', price: '₱699', originalPrice: '₱1,199', discountPercent: 42, badge: 'Official', rating: 4.8, soldCount: '6.3k sold'),
  ProductItem(name: 'Collagen Peptide Powder 500g Unflavored Keto-Friendly', category: 'Health & Beauty', price: '₱899', originalPrice: '₱1,499', discountPercent: 40, badge: 'Official', rating: 4.7, soldCount: '4.9k sold'),
  ProductItem(name: 'Digital Blood Pressure Monitor Upper Arm FDA-Approved', category: 'Health & Beauty', price: '₱1,299', originalPrice: '₱1,999', discountPercent: 35, badge: 'Official', rating: 4.9, soldCount: '7.2k sold'),

  // Kids & Baby
  ProductItem(name: 'Wooden Educational Puzzle Alphabet Numbers 3y+', category: 'Kids & Baby', price: '₱349', originalPrice: '₱549', discountPercent: 36, badge: 'Official', rating: 4.8, soldCount: '5.1k sold'),
  ProductItem(name: 'Soft Plush Animal Stuffed Toy Bear 40cm', category: 'Kids & Baby', price: '₱249', originalPrice: '₱399', discountPercent: 38, badge: 'Preferred', rating: 4.7, soldCount: '9.3k sold'),
  ProductItem(name: 'Baby Bottle Set 3-pack BPA-Free Anti-Colic 260ml', category: 'Kids & Baby', price: '₱399', originalPrice: '₱649', discountPercent: 38, badge: 'Official', rating: 4.9, soldCount: '12.4k sold'),
  ProductItem(name: 'Kids School Backpack Waterproof Ergonomic Reflective', category: 'Kids & Baby', price: '₱549', originalPrice: '₱899', discountPercent: 39, badge: 'Official', rating: 4.7, soldCount: '3.8k sold'),
  ProductItem(name: 'Toddler Sneakers Non-Slip Soft Sole Learning Shoes', category: 'Kids & Baby', price: '₱449', originalPrice: '₱699', discountPercent: 36, badge: 'Preferred', rating: 4.6, soldCount: '2.6k sold'),
  ProductItem(name: 'Kids Building Blocks STEM Toy Set 120 Pieces', category: 'Kids & Baby', price: '₱699', originalPrice: '₱1,099', discountPercent: 36, badge: 'Official', rating: 4.8, soldCount: '4.7k sold'),

  // Pet Supplies
  ProductItem(name: 'Orthopedic Dog Bed Memory Foam Washable Cover L', category: 'Pet Supplies', price: '₱1,199', originalPrice: '₱1,799', discountPercent: 33, badge: 'Official', rating: 4.8, soldCount: '2.3k sold'),
  ProductItem(name: 'Automatic Cat Feeder Wifi 5L Voice Record Timer', category: 'Pet Supplies', price: '₱1,499', originalPrice: '₱2,499', discountPercent: 40, badge: 'Official', rating: 4.7, soldCount: '1.6k sold'),
  ProductItem(name: 'Retractable Dog Leash 5m Heavy Duty Anti-Slip', category: 'Pet Supplies', price: '₱299', originalPrice: '₱499', discountPercent: 40, badge: 'Preferred', rating: 4.6, soldCount: '7.8k sold'),
  ProductItem(name: 'Cat Scratching Post Tower Sisal Rope Plush Perch', category: 'Pet Supplies', price: '₱849', originalPrice: '₱1,299', discountPercent: 35, badge: 'Official', rating: 4.8, soldCount: '3.1k sold'),
  ProductItem(name: 'Pet Grooming Slicker Brush Self-Cleaning Button', category: 'Pet Supplies', price: '₱249', originalPrice: '₱399', discountPercent: 38, badge: 'Preferred', rating: 4.7, soldCount: '11.5k sold'),
  ProductItem(name: 'Dog Harness No-Pull Reflective Adjustable XS-XL', category: 'Pet Supplies', price: '₱399', originalPrice: '₱649', discountPercent: 38, badge: 'Official', rating: 4.8, soldCount: '5.9k sold'),

  // Books & Media
  ProductItem(name: 'Atomic Habits Revised Edition James Clear Hardcover', category: 'Books & Media', price: '₱549', originalPrice: '₱799', discountPercent: 31, badge: 'Official', rating: 4.9, soldCount: '8.7k sold'),
  ProductItem(name: 'Wireless Noise-Cancelling Earbuds for Audiobooks', category: 'Books & Media', price: '₱799', originalPrice: '₱1,299', discountPercent: 38, badge: 'Preferred', rating: 4.6, soldCount: '1.4k sold'),
  ProductItem(name: 'Kindle-compatible Adjustable Book Stand Foldable', category: 'Books & Media', price: '₱299', originalPrice: '₱499', discountPercent: 40, badge: 'Official', rating: 4.7, soldCount: '4.2k sold'),
  ProductItem(name: 'Rich Dad Poor Dad 25th Anniversary Edition', category: 'Books & Media', price: '₱399', originalPrice: '₱599', discountPercent: 33, badge: 'Official', rating: 4.8, soldCount: '6.3k sold'),
  ProductItem(name: 'Manga Box Set Collection 10 Volumes Complete', category: 'Books & Media', price: '₱1,299', originalPrice: '₱1,999', discountPercent: 35, badge: 'Preferred', rating: 4.9, soldCount: '2.8k sold'),

  // Food & Gourmet
  ProductItem(name: 'Premium Cold Brew Coffee Concentrate 500ml Pack of 3', category: 'Food & Gourmet', price: '₱549', originalPrice: '₱849', discountPercent: 35, badge: 'Official', rating: 4.8, soldCount: '3.6k sold'),
  ProductItem(name: 'Organic Honey Raw Unfiltered Wildflower 500g Jar', category: 'Food & Gourmet', price: '₱399', originalPrice: '₱599', discountPercent: 33, badge: 'Official', rating: 4.9, soldCount: '7.1k sold'),
  ProductItem(name: 'Matcha Powder Ceremonial Grade Japan 100g Tin', category: 'Food & Gourmet', price: '₱499', originalPrice: '₱799', discountPercent: 38, badge: 'Official', rating: 4.7, soldCount: '4.5k sold'),
  ProductItem(name: 'Dark Chocolate Assorted Gift Box 24 Pieces Belgium', category: 'Food & Gourmet', price: '₱649', originalPrice: '₱999', discountPercent: 35, badge: 'Preferred', rating: 4.8, soldCount: '2.2k sold'),
  ProductItem(name: 'Gourmet Pasta Variety Pack 6 Types Imported Italy', category: 'Food & Gourmet', price: '₱599', originalPrice: '₱949', discountPercent: 37, badge: 'Official', rating: 4.6, soldCount: '1.9k sold'),

  // Jewelry & Watches
  ProductItem(name: 'Polarized UV400 Sunglasses Unisex Classic Frame', category: 'Jewelry & Watches', price: '₱820', originalPrice: '₱2,732', discountPercent: 70, badge: 'Official', rating: 4.5, soldCount: '7.8k sold'),
  ProductItem(name: 'Minimalist Matte Chronograph Watch Waterproof', category: 'Jewelry & Watches', price: '₱459', originalPrice: '₱765', discountPercent: 40, badge: 'Preferred', rating: 4.8, soldCount: '1.8k sold'),
  ProductItem(name: 'Sterling Silver Dainty Necklace Pendant Choker', category: 'Jewelry & Watches', price: '₱399', originalPrice: '₱699', discountPercent: 43, badge: 'Official', rating: 4.8, soldCount: '6.4k sold'),
  ProductItem(name: 'Gold-Plated Hoop Earrings Set 5 Pairs Hypoallergenic', category: 'Jewelry & Watches', price: '₱249', originalPrice: '₱449', discountPercent: 45, badge: 'Preferred', rating: 4.7, soldCount: '11.2k sold'),
  ProductItem(name: 'Charm Bracelet Stainless Steel Adjustable Boho', category: 'Jewelry & Watches', price: '₱199', originalPrice: '₱349', discountPercent: 43, badge: 'Official', rating: 4.6, soldCount: '8.9k sold'),
  ProductItem(name: 'Analog Wristwatch Genuine Leather Strap Rose Gold', category: 'Jewelry & Watches', price: '₱1,299', originalPrice: '₱2,199', discountPercent: 41, badge: 'Official', rating: 4.9, soldCount: '3.1k sold'),
];

class ProductListScreen extends StatefulWidget {
  final String? initialCategory;
  final String? title;
  final VoidCallback? onBack;

  const ProductListScreen({
    super.key,
    this.initialCategory,
    this.title,
    this.onBack,
  });

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  final TextEditingController _searchController = TextEditingController();
  late String _selectedCategory;

  final List<String> _categories = [
    'All',
    'Flash Deals',
    'Electronics',
    "Women's",
    "Men's",
    'Sports',
    'Home & Garden',
    'Health & Beauty',
    'Kids & Baby',
    'Pet Supplies',
    'Books & Media',
    'Food & Gourmet',
    'Jewelry & Watches',
  ];

  @override
  void initState() {
    super.initState();
    _selectedCategory = widget.initialCategory ?? 'All';
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  List<ProductItem> get _filteredProducts {
    final query = _searchController.text.trim().toLowerCase();
    return allCatalogProducts.where((p) {
      final bool matchesCategory;
      if (_selectedCategory == 'All') {
        matchesCategory = true;
      } else if (_selectedCategory == 'Flash Deals') {
        matchesCategory = p.discountPercent >= 30;
      } else {
        matchesCategory = p.category == _selectedCategory;
      }
      final matchesQuery = query.isEmpty || p.name.toLowerCase().contains(query);
      return matchesCategory && matchesQuery;
    }).toList();
  }

  void _navigateToCart(BuildContext context) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => CartScreen(
          onBack: () => Navigator.of(context).pop(),
          onCheckout: () {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => CheckoutScreen(
                  onBack: () => Navigator.of(context).pop(),
                  onOrderPlaced: () {
                    Navigator.of(context).popUntil((route) => route.isFirst);
                  },
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  void _openDetails(ProductItem product) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => ProductDetailsScreen(
          name: product.name,
          category: product.category,
          price: product.price,
          originalPrice: product.originalPrice,
          imageUrl: product.effectiveImageUrl,
          onBack: () => Navigator.of(context).pop(),
          onAddToCart: () {
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
                  textColor: CartzyColors.coral,
                  onPressed: () => _navigateToCart(context),
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filteredProducts;
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
          onPressed: widget.onBack ?? () => Navigator.of(context).maybePop(),
        ),
        title: Text(
          widget.title ?? 'Products',
          style: const TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold),
        ),
        actions: [
          Center(
            child: Text(
              '${filtered.length} items',
              style: const TextStyle(color: CartzyColors.gray, fontSize: 13),
            ),
          ),
          const SizedBox(width: 4),
          ListenableBuilder(
            listenable: CartService.instance,
            builder: (context, _) {
              final count = CartService.instance.totalItemCount;
              return Stack(
                alignment: Alignment.center,
                children: [
                  IconButton(
                    icon: const Icon(Icons.shopping_cart_outlined, color: CartzyColors.navy),
                    tooltip: 'View Cart',
                    onPressed: () => _navigateToCart(context),
                  ),
                  if (count > 0)
                    Positioned(
                      right: 6,
                      top: 6,
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: const BoxDecoration(
                          color: CartzyColors.coral,
                          shape: BoxShape.circle,
                        ),
                        constraints: const BoxConstraints(minWidth: 18, minHeight: 18),
                        child: Text(
                          count > 99 ? '99+' : '$count',
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                ],
              );
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: Column(
        children: [
          // Search bar
          Container(
            color: CartzyColors.surface,
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
            child: TextField(
              controller: _searchController,
              onChanged: (_) => setState(() {}),
              decoration: InputDecoration(
                hintText: 'Search products...',
                hintStyle: const TextStyle(color: CartzyColors.gray, fontSize: 14),
                prefixIcon: const Icon(Icons.search, color: CartzyColors.gray, size: 20),
                filled: true,
                fillColor: CartzyColors.background,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
          ),
          // Category chips
          SizedBox(
            height: 48,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
              itemCount: _categories.length,
              itemBuilder: (context, index) {
                final cat = _categories[index];
                final isSelected = cat == _selectedCategory;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: ChoiceChip(
                    label: Text(
                      cat,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: isSelected ? Colors.white : CartzyColors.navy,
                      ),
                    ),
                    selected: isSelected,
                    selectedColor: CartzyColors.coral,
                    backgroundColor: CartzyColors.surface,
                    showCheckmark: false,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(20),
                      side: BorderSide(color: isSelected ? CartzyColors.coral : CartzyColors.border),
                    ),
                    onSelected: (val) {
                      if (val) setState(() => _selectedCategory = cat);
                    },
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 4),
          // Product grid
          Expanded(
            child: filtered.isEmpty
                ? const Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.search_off, size: 48, color: CartzyColors.gray),
                        SizedBox(height: 12),
                        Text('No products found', style: TextStyle(color: CartzyColors.gray, fontSize: 15)),
                      ],
                    ),
                  )
                : GridView.builder(
                    padding: const EdgeInsets.all(16),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      mainAxisSpacing: 14,
                      crossAxisSpacing: 14,
                      childAspectRatio: 0.68,
                    ),
                    itemCount: filtered.length,
                    itemBuilder: (context, index) {
                      final product = filtered[index];
                      return ProductCard(
                        name: product.name,
                        price: product.price,
                        originalPrice: product.originalPrice,
                        discountPercent: product.discountPercent,
                        badge: product.badge,
                        rating: product.rating,
                        soldCount: product.soldCount,
                        imageUrl: product.effectiveImageUrl,
                        onTap: () => _openDetails(product),
                        onAddToCart: () {
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
                                textColor: CartzyColors.coral,
                                onPressed: () => _navigateToCart(context),
                              ),
                            ),
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

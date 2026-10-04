
import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/review_service.dart';
import 'package:cartzy/widgets/write_review_dialog.dart';
import 'package:cartzy/widgets/pay_now_dialog.dart';
import 'package:cartzy/widgets/cartzy_product_image.dart';

// ============================================================
// BUYER PROFILE SCREEN
// ============================================================

class BuyerProfileScreen extends StatefulWidget {
  final String userName;
  final String userEmail;
  final VoidCallback onBack;
  final VoidCallback onLogout;

  // NEW
  final VoidCallback onEditProfile;

  const BuyerProfileScreen({
    super.key,
    this.userName = 'Guest User',
    this.userEmail = 'guest@cartzy.com',
    required this.onBack,
    required this.onLogout,
    required this.onEditProfile,
  });

  @override
  State<BuyerProfileScreen> createState() => _BuyerProfileScreenState();
}

class _BuyerProfileScreenState extends State<BuyerProfileScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  static const List<String> _tabs = [
    'To Pay',
    'To Ship',
    'To Receive',
    'To Review',
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: _tabs.length,
      vsync: this,
    );
    ReviewService.instance.addListener(_onReviewServiceChanged);
  }

  @override
  void dispose() {
    ReviewService.instance.removeListener(_onReviewServiceChanged);
    _tabController.dispose();
    super.dispose();
  }

  void _onReviewServiceChanged() {
    if (mounted) setState(() {});
  }

  List<Order> _ordersForStatus(String status) {
    return ReviewService.instance.getOrdersByStatus(status);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,

      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,

        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back,
            color: CartzyColors.navy,
          ),
          onPressed: widget.onBack,
        ),

        title: const Text(
          'My Profile',
          style: TextStyle(
            color: CartzyColors.navy,
            fontWeight: FontWeight.bold,
            fontSize: 18,
          ),
        ),

        actions: [
          IconButton(
            icon: const Icon(
              Icons.logout,
              color: CartzyColors.coral,
            ),
            onPressed: widget.onLogout,
          ),
        ],
      ),

      body: Column(
        children: [
          // PROFILE HEADER
          Container(
            width: double.infinity,
            color: CartzyColors.surface,
            padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
            child: Row(
              children: [
                const CircleAvatar(
                  radius: 34,
                  backgroundColor: CartzyColors.background,
                  child: Icon(
                    Icons.person,
                    size: 38,
                    color: CartzyColors.coral,
                  ),
                ),

                const SizedBox(width: 16),

                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        widget.userName,
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: CartzyColors.navy,
                        ),
                      ),

                      const SizedBox(height: 4),

                      Text(
                        widget.userEmail,
                        style: const TextStyle(
                          fontSize: 13,
                          color: CartzyColors.gray,
                        ),
                      ),
                    ],
                  ),
                ),

                // EDIT PROFILE
                IconButton(
                  icon: const Icon(
                    Icons.edit_outlined,
                    color: CartzyColors.navy,
                  ),
                  onPressed: widget.onEditProfile,
                ),
              ],
            ),
          ),

          // MY ORDERS
          Container(
            width: double.infinity,
            color: CartzyColors.surface,
            padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
            child: const Text(
              'My Orders',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: CartzyColors.navy,
              ),
            ),
          ),

          Container(
            color: CartzyColors.surface,
            child: TabBar(
              controller: _tabController,
              isScrollable: true,
              labelColor: CartzyColors.coral,
              unselectedLabelColor: CartzyColors.gray,
              indicatorColor: CartzyColors.coral,
              indicatorWeight: 3,
              tabs: _tabs.map((tab) {
                if (tab == 'To Review') {
                  final count = ReviewService.instance.toReviewCount;
                  return Tab(
                    text: count > 0 ? 'To Review ($count)' : 'To Review',
                  );
                }
                return Tab(text: tab);
              }).toList(),
            ),
          ),

          const Divider(
            height: 1,
            color: CartzyColors.border,
          ),

          Expanded(
            child: TabBarView(
              controller: _tabController,
              children: _tabs.map((status) {
                final orders = _ordersForStatus(status);

                if (orders.isEmpty) {
                  return _EmptyOrdersView(status: status);
                }

                return ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: orders.length,
                  separatorBuilder: (_, __) =>
                      const SizedBox(height: 12),
                  itemBuilder: (context, index) {
                    return _OrderCard(
                      order: orders[index],
                      userName: widget.userName,
                    );
                  },
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }
}

// ============================================================
// EMPTY STATE
// ============================================================

class _EmptyOrdersView extends StatelessWidget {
  final String status;

  const _EmptyOrdersView({
    required this.status,
  });

  IconData get _icon {
    switch (status) {
      case 'To Pay':
        return Icons.payment_outlined;
      case 'To Ship':
        return Icons.inventory_2_outlined;
      case 'To Receive':
        return Icons.local_shipping_outlined;
      case 'To Review':
        return Icons.rate_review_outlined;
      default:
        return Icons.shopping_bag_outlined;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            _icon,
            size: 56,
            color: CartzyColors.border,
          ),
          const SizedBox(height: 12),
          Text(
            'No orders $status',
            style: const TextStyle(
              fontSize: 14,
              color: CartzyColors.gray,
            ),
          ),
        ],
      ),
    );
  }
}

// ============================================================
// ORDER CARD
// ============================================================

class _OrderCard extends StatelessWidget {
  final Order order;
  final String userName;

  const _OrderCard({
    required this.order,
    this.userName = 'Buyer',
  });

  Color get _statusColor {
    switch (order.status) {
      case 'To Pay':
        return const Color(0xFFD32F2F);
      case 'To Ship':
        return CartzyColors.gold;
      case 'To Receive':
        return const Color(0xFF2AA89C);
      case 'To Review':
        return CartzyColors.coral;
      default:
        return CartzyColors.gray;
    }
  }

  String get _actionLabel {
    switch (order.status) {
      case 'To Pay':
        return 'Pay Now';
      case 'To Ship':
        return 'Track Order';
      case 'To Receive':
        return 'Confirm Receipt';
      case 'To Review':
        return order.hasReviewed ? 'Edit Review' : 'Rate Product';
      default:
        return 'View';
    }
  }

  void _handleAction(BuildContext context) {
    switch (order.status) {
      case 'To Review':
        WriteReviewDialog.show(
          context,
          productName: order.productName,
          orderId: order.id,
          initialRating: order.userRating ?? 5.0,
          initialComment: order.userReview,
          initialTags: order.userTags,
          userName: userName,
        );
        break;

      case 'To Receive':
        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: const Text('Confirm Item Receipt?'),
            content: Text(
              'Have you received "${order.productName}" in good condition? '
              'This will mark your order as complete and ready for review.',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(ctx).pop(),
                child: const Text('Not Yet', style: TextStyle(color: CartzyColors.gray)),
              ),
              TextButton(
                onPressed: () {
                  Navigator.of(ctx).pop();
                  ReviewService.instance.confirmReceipt(order.id);

                  // Offer to rate immediately
                  showDialog(
                    context: context,
                    builder: (rateCtx) => AlertDialog(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      title: const Row(
                        children: [
                          Icon(Icons.star, color: CartzyColors.gold),
                          SizedBox(width: 8),
                          Text('Rate Product'),
                        ],
                      ),
                      content: const Text(
                        'Your order is completed! Would you like to share a review now to help other buyers?',
                      ),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.of(rateCtx).pop(),
                          child: const Text('Later', style: TextStyle(color: CartzyColors.gray)),
                        ),
                        TextButton(
                          onPressed: () {
                            Navigator.of(rateCtx).pop();
                            WriteReviewDialog.show(
                              context,
                              productName: order.productName,
                              orderId: order.id,
                              userName: userName,
                            );
                          },
                          child: const Text(
                            'Rate Now',
                            style: TextStyle(
                              color: CartzyColors.coral,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                },
                child: const Text(
                  'Yes, Confirm',
                  style: TextStyle(
                    color: CartzyColors.coral,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
        );
        break;

      case 'To Pay':
        PayNowDialog.show(context, order: order);
        break;

      case 'To Ship':
        showDialog(
          context: context,
          builder: (ctx) => AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: const Row(
              children: [
                Icon(Icons.local_shipping, color: CartzyColors.coral),
                SizedBox(width: 8),
                Text('Shipment Tracking'),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  order.productName,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                ),
                const SizedBox(height: 12),
                const Row(
                  children: [
                    Icon(Icons.check_circle, color: Colors.green, size: 18),
                    SizedBox(width: 8),
                    Text('Parcel packed & verified', style: TextStyle(fontSize: 12)),
                  ],
                ),
                const SizedBox(height: 8),
                const Row(
                  children: [
                    Icon(Icons.radio_button_checked, color: CartzyColors.coral, size: 18),
                    SizedBox(width: 8),
                    Text('In Transit - Assigned to Rider', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                  ],
                ),
                const SizedBox(height: 8),
                const Row(
                  children: [
                    Icon(Icons.radio_button_unchecked, color: CartzyColors.gray, size: 18),
                    SizedBox(width: 8),
                    Text('Estimated Delivery: Tomorrow', style: TextStyle(fontSize: 12, color: CartzyColors.gray)),
                  ],
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(ctx).pop(),
                child: const Text('Close', style: TextStyle(color: CartzyColors.coral)),
              ),
            ],
          ),
        );
        break;

      default:
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: CartzyColors.border,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  color: CartzyColors.background,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: CartzyProductImage(
                  imageUrl: order.effectiveImage,
                  width: 56,
                  height: 56,
                  fit: BoxFit.cover,
                  borderRadius: BorderRadius.circular(10),
                  fallbackIcon: Icons.shopping_bag_outlined,
                ),
              ),

              const SizedBox(width: 12),

              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      order.productName,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: CartzyColors.navy,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Qty: ${order.quantity}',
                      style: const TextStyle(
                        fontSize: 12,
                        color: CartzyColors.gray,
                      ),
                    ),
                  ],
                ),
              ),

              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: _statusColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  order.hasReviewed && order.status == 'To Review'
                      ? 'Reviewed'
                      : order.status,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: _statusColor,
                  ),
                ),
              ),
            ],
          ),

          // User review preview if already reviewed
          if (order.hasReviewed && order.userRating != null) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: CartzyColors.background,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: CartzyColors.border),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Row(
                        children: List.generate(5, (starIdx) {
                          return Icon(
                            starIdx < order.userRating!.round()
                                ? Icons.star
                                : Icons.star_border,
                            size: 14,
                            color: CartzyColors.gold,
                          );
                        }),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        '${order.userRating!.toStringAsFixed(1)} ★',
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: CartzyColors.navy,
                        ),
                      ),
                      const Spacer(),
                      const Text(
                        'Your Review',
                        style: TextStyle(
                          fontSize: 11,
                          color: CartzyColors.gray,
                        ),
                      ),
                    ],
                  ),
                  if (order.userReview != null && order.userReview!.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      order.userReview!,
                      style: const TextStyle(
                        fontSize: 12,
                        color: CartzyColors.text,
                        fontStyle: FontStyle.italic,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],

          const Divider(
            height: 20,
            color: CartzyColors.border,
          ),

          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                order.price,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: CartzyColors.navy,
                ),
              ),
              if (order.status == 'To Review' && !order.hasReviewed)
                ElevatedButton.icon(
                  onPressed: () => _handleAction(context),
                  icon: const Icon(Icons.star_rate_rounded, size: 16),
                  label: Text(_actionLabel),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CartzyColors.coral,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                )
              else if (order.status == 'To Pay')
                ElevatedButton.icon(
                  onPressed: () => _handleAction(context),
                  icon: const Icon(Icons.payment, size: 16),
                  label: Text(_actionLabel),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFD32F2F),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                )
              else
                OutlinedButton(
                  onPressed: () => _handleAction(context),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: CartzyColors.coral,
                    side: const BorderSide(
                      color: CartzyColors.coral,
                    ),
                  ),
                  child: Text(_actionLabel),
                ),
            ],
          ),
        ],
      ),
    );
  }
}
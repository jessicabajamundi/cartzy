
import 'package:flutter/material.dart';
import 'cartzy_colors.dart';

// ============================================================
// ORDER DATA
// ============================================================

class Order {
  final String productName;
  final String status;
  final String price;
  final int quantity;

  const Order({
    required this.productName,
    required this.status,
    required this.price,
    required this.quantity,
  });
}

const List<Order> _sampleOrders = [
  Order(
    productName: 'Wireless Earbuds',
    status: 'To Pay',
    price: '\$29.99',
    quantity: 1,
  ),
  Order(
    productName: 'Running Shoes',
    status: 'To Ship',
    price: '\$49.99',
    quantity: 1,
  ),
  Order(
    productName: 'Smart Watch',
    status: 'To Receive',
    price: '\$59.99',
    quantity: 1,
  ),
  Order(
    productName: 'Backpack',
    status: 'To Review',
    price: '\$34.99',
    quantity: 2,
  ),
];

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
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  List<Order> _ordersForStatus(String status) {
    return _sampleOrders
        .where((order) => order.status == status)
        .toList();
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
              tabs: _tabs.map((tab) => Tab(text: tab)).toList(),
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

  const _OrderCard({
    required this.order,
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
        return 'Rate Product';
      default:
        return 'View';
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
                child: const Icon(
                  Icons.shopping_bag_outlined,
                  color: CartzyColors.coral,
                ),
              ),

              const SizedBox(width: 12),

              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      order.productName,
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
                  order.status,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: _statusColor,
                  ),
                ),
              ),
            ],
          ),

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
              OutlinedButton(
                onPressed: () {},
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
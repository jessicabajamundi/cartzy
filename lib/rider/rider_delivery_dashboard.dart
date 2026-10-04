import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/mock_data.dart';
import 'rider_order_detail.dart';
import 'rider_order_card.dart';
import 'rider_delivery_process.dart';

// ============================================================
// ITEMS FOR DELIVERY — orders this rider has picked up and
// still needs to deliver (status = to_receive)
// ============================================================

class RiderDeliveryDashboard extends StatefulWidget {
  final String token;
  final VoidCallback onBack;

  const RiderDeliveryDashboard({
    super.key,
    required this.token,
    required this.onBack,
  });

  @override
  State<RiderDeliveryDashboard> createState() => _RiderDeliveryDashboardState();
}

class _RiderDeliveryDashboardState extends State<RiderDeliveryDashboard> {
  List<RiderOrder> _orders = [];
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    await Future.delayed(const Duration(milliseconds: 250));

    if (!mounted) return;
    setState(() {
      _orders = List.from(MockData.activeDeliveries);
      _isLoading = false;
    });
  }

  Future<void> _openDeliver(RiderOrder order) async {
    final delivered = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (context) => RiderDeliverProcess(order: order, token: widget.token),
      ),
    );
    if (delivered == true) _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
          onPressed: widget.onBack,
        ),
        title: const Text(
          'Items for Delivery',
          style: TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: CartzyColors.navy),
            onPressed: _load,
          ),
        ],
      ),
      body: SafeArea(child: _buildBody()),
    );
  }

  Widget _buildBody() {
    if (_isLoading) return const Center(child: CircularProgressIndicator());

    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline, color: Colors.redAccent, size: 40),
              const SizedBox(height: 12),
              Text(_error!, textAlign: TextAlign.center, style: const TextStyle(color: CartzyColors.gray)),
              const SizedBox(height: 16),
              ElevatedButton(onPressed: _load, child: const Text('Try Again')),
            ],
          ),
        ),
      );
    }

    if (_orders.isEmpty) {
      return RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          padding: const EdgeInsets.only(top: 100),
          children: const [
            Center(
              child: Column(
                children: [
                  Icon(Icons.local_shipping_outlined, size: 44, color: CartzyColors.gray),
                  SizedBox(height: 12),
                  Text('Nothing out for delivery right now.', style: TextStyle(color: CartzyColors.gray)),
                ],
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.separated(
        padding: const EdgeInsets.all(20),
        itemCount: _orders.length,
        separatorBuilder: (context, index) => const SizedBox(height: 14),
        itemBuilder: (context, index) {
          final order = _orders[index];
          return RiderOrderCard(
            order: order,
            badgeLabel: 'DELIVER',
            badgeIcon: Icons.local_shipping_outlined,
            actionLabel: 'Start Delivery',
            onAction: () => _openDeliver(order),
          );
        },
      ),
    );
  }
}
import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/mock_data.dart';

// ============================================================
// PROFIT / EARNINGS DASHBOARD
// ============================================================
//
// Flat fee per completed delivery until a real commission model
// exists (see GET /api/orders/earnings on the backend).

class RiderEarningsScreen extends StatefulWidget {
  final String token;
  final VoidCallback onBack;

  const RiderEarningsScreen({
    super.key,
    required this.token,
    required this.onBack,
  });

  @override
  State<RiderEarningsScreen> createState() => _RiderEarningsScreenState();
}

class _RiderEarningsScreenState extends State<RiderEarningsScreen> {
  bool _isLoading = true;
  String? _error;

  int _completedDeliveries = 0;
  double _flatFeePerDelivery = 0;
  double _totalEarnings = 0;

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
      _completedDeliveries = MockData.completedDeliveryCount;
      _flatFeePerDelivery = MockData.flatFeePerDelivery;
      _totalEarnings = MockData.totalEarnings;
      _isLoading = false;
    });
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
          'Earnings',
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

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: CartzyColors.navy,
              borderRadius: BorderRadius.circular(18),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Total Earnings',
                  style: TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 8),
                Text(
                  '₱${_totalEarnings.toStringAsFixed(2)}',
                  style: const TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: _statCard(
                  icon: Icons.local_shipping_outlined,
                  label: 'Completed Deliveries',
                  value: '$_completedDeliveries',
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: _statCard(
                  icon: Icons.payments_outlined,
                  label: 'Fee per Delivery',
                  value: '₱${_flatFeePerDelivery.toStringAsFixed(2)}',
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _statCard({required IconData icon, required String label, required String value}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: CartzyColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: CartzyColors.coral, size: 22),
          const SizedBox(height: 10),
          Text(value, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
          const SizedBox(height: 4),
          Text(label, style: const TextStyle(fontSize: 12, color: CartzyColors.gray)),
        ],
      ),
    );
  }
}
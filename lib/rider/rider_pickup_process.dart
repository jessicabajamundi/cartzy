import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/mock_data.dart';
import 'rider_order_detail.dart';

// ============================================================
// PICKUP PROCESS
// ============================================================
//
// Claims the order (assigns this rider) and advances it to
// 'to_receive' (out for delivery). Returns true via Navigator.pop
// so the pickup dashboard knows to refresh its list.

class RiderPickupProcess extends StatefulWidget {
  final RiderOrder order;
  final String token;

  const RiderPickupProcess({super.key, required this.order, required this.token});

  @override
  State<RiderPickupProcess> createState() => _RiderPickupProcessState();
}

class _RiderPickupProcessState extends State<RiderPickupProcess> {
  bool _isSubmitting = false;
  String? _error;

  Future<void> _confirmPickup() async {
    setState(() {
      _isSubmitting = true;
      _error = null;
    });

    await Future.delayed(const Duration(milliseconds: 400));

    // Update mock lists
    MockData.availablePickups.removeWhere((o) => o.id == widget.order.id);
    MockData.activeDeliveries.insert(
      0,
      RiderOrder(
        id: widget.order.id,
        status: 'to_receive',
        buyerName: widget.order.buyerName,
        buyerPhone: widget.order.buyerPhone,
        deliveryAddress: widget.order.deliveryAddress,
        itemsSummary: widget.order.itemsSummary,
        totalAmount: widget.order.totalAmount,
        createdAt: widget.order.createdAt,
      ),
    );

    if (!mounted) return;
    Navigator.of(context).pop(true);
  }

  @override
  Widget build(BuildContext context) {
    final order = widget.order;

    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        title: const Text(
          'Pickup Order',
          style: TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold),
        ),
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: CartzyColors.surface,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: CartzyColors.border),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      order.itemsSummary,
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: CartzyColors.navy),
                    ),
                    const SizedBox(height: 10),
                    _detailRow('Order #', '${order.id}'),
                    _detailRow('Seller pickup at', order.deliveryAddress),
                    _detailRow('Buyer', order.buyerName),
                    _detailRow('Contact', order.buyerPhone),
                    _detailRow('Order Total', '₱${order.totalAmount.toStringAsFixed(2)}'),
                  ],
                ),
              ),
              const Spacer(),
              if (_error != null) ...[
                Text(_error!, style: const TextStyle(color: CartzyColors.error, fontSize: 13)),
                const SizedBox(height: 10),
              ],
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CartzyColors.navy,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: _isSubmitting ? null : _confirmPickup,
                  child: _isSubmitting
                      ? const SizedBox(
                          height: 18,
                          width: 18,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('Confirm Pickup', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _detailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(top: 8),
      child: RichText(
        text: TextSpan(
          style: const TextStyle(fontSize: 13, color: CartzyColors.navy),
          children: [
            TextSpan(text: '$label  ', style: const TextStyle(color: CartzyColors.gray, fontWeight: FontWeight.w600)),
            TextSpan(text: value),
          ],
        ),
      ),
    );
  }
}
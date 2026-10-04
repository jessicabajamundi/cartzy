import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/auth/buyer_registration.dart'; // reuses PrimaryButton
import 'package:cartzy/widgets/barcode_scanner_page.dart';
import 'package:cartzy/services/mock_data.dart';
import 'rider_order_detail.dart';

class RiderDeliverProcess extends StatefulWidget {
  final RiderOrder order;
  final String? token;

  const RiderDeliverProcess({super.key, required this.order, this.token});

  @override
  State<RiderDeliverProcess> createState() => _RiderDeliverProcessState();
}

typedef RiderDeliveryProcess = RiderDeliverProcess;

class _RiderDeliverProcessState extends State<RiderDeliverProcess> {
  int _step = 1;
  String? _scannedCode;
  bool _isCompleting = false;

  Future<void> _scanItem() async {
    final result = await Navigator.of(context).push<String>(
      MaterialPageRoute(
        builder: (context) => const BarcodeScannerPage(title: 'Scan Item to Confirm Handoff'),
      ),
    );
    if (result != null) {
      setState(() {
        _scannedCode = result;
        _step = 3;
      });
    }
  }

  Future<void> _completeDelivery() async {
    setState(() => _isCompleting = true);
    await Future.delayed(const Duration(milliseconds: 400));

    // Update mock lists
    MockData.activeDeliveries.removeWhere((o) => o.id == widget.order.id);
    MockData.completedDeliveries.insert(
      0,
      RiderOrder(
        id: widget.order.id,
        status: 'completed',
        buyerName: widget.order.buyerName,
        buyerPhone: widget.order.buyerPhone,
        deliveryAddress: widget.order.deliveryAddress,
        itemsSummary: widget.order.itemsSummary,
        totalAmount: widget.order.totalAmount,
        createdAt: widget.order.createdAt,
      ),
    );
    MockData.completedDeliveryCount += 1;

    if (!mounted) return;
    setState(() => _isCompleting = false);
    _showDeliveryCompleteDialog();
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
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text(
          'Deliver Order',
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
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: CartzyColors.surface,
                  borderRadius: BorderRadius.circular(14),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 3)),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      widget.order.itemName,
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: CartzyColors.navy),
                    ),
                    const SizedBox(height: 4),
                    Text('Buyer: ${widget.order.personName}', style: const TextStyle(fontSize: 13, color: CartzyColors.gray)),
                    Text('Location: ${widget.order.address}', style: const TextStyle(fontSize: 13, color: CartzyColors.gray)),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              if (_step == 1) ..._buildInfoStep(
                title: 'Step 1: Head to buyer location',
                description: "Navigate to the buyer's delivery address.",
                icon: Icons.directions_bike,
                buttonText: "I've Arrived",
                onNext: () => setState(() => _step = 2),
              ),

              if (_step == 2) ..._buildScanStep(),

              if (_step == 3) ..._buildInfoStep(
                title: 'Step 3: Hand over the item',
                description: 'Confirm the item has been handed to the buyer.',
                icon: Icons.inventory_2_outlined,
                buttonText: 'Item Handed Over',
                onNext: () => setState(() => _step = 4),
              ),

              if (_step == 4) ..._buildInfoStep(
                title: 'Step 4: Confirm delivery',
                description: 'This completes the order and adds it to your earnings.',
                icon: Icons.check_circle_outline,
                buttonText: _isCompleting ? 'Completing...' : 'Mark as Delivered',
                onNext: _isCompleting ? () {} : _completeDelivery,
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showDeliveryCompleteDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Delivery Complete', style: TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold)),
        content: const Text('The order has been scanned and marked as delivered.'),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              Navigator.of(context).pop(true);
            },
            child: const Text('OK', style: TextStyle(color: CartzyColors.coral, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  List<Widget> _buildInfoStep({
    required String title,
    required String description,
    required IconData icon,
    required String buttonText,
    required VoidCallback onNext,
  }) {
    return [
      Icon(icon, size: 64, color: CartzyColors.coral),
      const SizedBox(height: 16),
      Text(title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: CartzyColors.navy)),
      const SizedBox(height: 8),
      Text(description, style: const TextStyle(fontSize: 13, color: CartzyColors.gray)),
      const Spacer(),
      PrimaryButton(text: buttonText, color: CartzyColors.navy, onClick: onNext),
    ];
  }

  List<Widget> _buildScanStep() {
    return [
      const Icon(Icons.qr_code_scanner, size: 64, color: CartzyColors.coral),
      const SizedBox(height: 16),
      const Text(
        'Step 2: Scan to confirm handoff',
        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: CartzyColors.navy),
      ),
      const SizedBox(height: 8),
      const Text(
        "Scan the item's barcode or QR code before handing it to the buyer.",
        style: TextStyle(fontSize: 13, color: CartzyColors.gray),
      ),
      const SizedBox(height: 20),
      if (_scannedCode != null)
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: CartzyColors.coral.withValues(alpha: 0.08),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: CartzyColors.coral, width: 1.2),
          ),
          child: Row(
            children: [
              const Icon(Icons.check_circle, color: CartzyColors.coral, size: 20),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Scanned: $_scannedCode',
                  style: const TextStyle(fontSize: 13, color: CartzyColors.navy, fontWeight: FontWeight.w600),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ),
      const Spacer(),
      PrimaryButton(
        text: 'Open Scanner',
        color: CartzyColors.navy,
        onClick: _scanItem,
      ),
    ];
  }
}

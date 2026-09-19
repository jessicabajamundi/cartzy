import 'package:flutter/material.dart';

import 'cartzy_colors.dart';
import 'buyer_registration.dart'; // reuses PrimaryButton
import 'rider_dashboard.dart';
import 'barcode_scanner_page.dart';

class RiderPickupProcess extends StatefulWidget {
  final RiderOrder order;

  const RiderPickupProcess({super.key, required this.order});

  @override
  State<RiderPickupProcess> createState() => _RiderPickupProcessState();
}

class _RiderPickupProcessState extends State<RiderPickupProcess> {
  int _step = 1;
  String? _scannedCode;

  Future<void> _scanItem() async {
    final result = await Navigator.of(context).push<String>(
      MaterialPageRoute(
        builder: (context) => const BarcodeScannerPage(title: 'Scan Item to Pick Up'),
      ),
    );
    if (result != null) {
      setState(() {
        _scannedCode = result;
        _step = 3;
      });
    }
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
                    Text('Seller: ${widget.order.personName}', style: const TextStyle(fontSize: 13, color: CartzyColors.gray)),
                    Text('Location: ${widget.order.address}', style: const TextStyle(fontSize: 13, color: CartzyColors.gray)),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              if (_step == 1) ..._buildInfoStep(
                title: 'Step 1: Head to seller location',
                description: 'Navigate to the seller and confirm your arrival.',
                icon: Icons.directions_bike,
                buttonText: "I've Arrived",
                onNext: () => setState(() => _step = 2),
              ),

              if (_step == 2) ..._buildScanStep(),

              if (_step == 3) ..._buildInfoStep(
                title: 'Step 3: Confirm item with seller',
                description: 'Double-check the item matches the order before accepting it.',
                icon: Icons.inventory_2_outlined,
                buttonText: 'Item Confirmed',
                onNext: () => setState(() => _step = 4),
              ),

              if (_step == 4) ..._buildInfoStep(
                title: 'Step 4: Mark as picked up',
                description: 'This will notify the buyer that their item is on the way.',
                icon: Icons.check_circle_outline,
                buttonText: 'Mark as Picked Up',
                onNext: _showPickupCompleteDialog,
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showPickupCompleteDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Pickup Complete', style: TextStyle(color: CartzyColors.navy, fontWeight: FontWeight.bold)),
        content: const Text('The item has been scanned and marked as picked up.'),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              Navigator.of(context).pop();
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
        'Step 2: Scan the item',
        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: CartzyColors.navy),
      ),
      const SizedBox(height: 8),
      const Text(
        "Scan the item's barcode or QR code to confirm it's the correct package.",
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
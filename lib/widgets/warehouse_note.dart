import 'package:flutter/material.dart';

/// Bulk orders: once a buyer orders more than [threshold] pieces of one item,
/// the seller ships it straight from their own warehouse instead of through
/// Cartzy logistics. Keep in sync with App\Support\WarehouseShipping on the web.
class WarehouseShipping {
  WarehouseShipping._();

  static const int threshold = 100;

  static const String note =
      "Orders of more than 100 pieces ship directly from the seller's warehouse instead of through Cartzy logistics.";

  static bool applies(int quantity) => quantity > threshold;
}

/// Amber note shown under an item whose quantity is over 100.
class WarehouseShippingNote extends StatelessWidget {
  const WarehouseShippingNote({super.key, this.compact = false});

  /// Smaller text and padding for tight spots such as cart rows.
  final bool compact;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      liveRegion: true,
      child: Container(
        width: double.infinity,
        padding: EdgeInsets.symmetric(
          horizontal: compact ? 10 : 12,
          vertical: compact ? 7 : 9,
        ),
        decoration: BoxDecoration(
          color: const Color(0xFFFFF8EB),
          borderRadius: BorderRadius.circular(compact ? 9 : 12),
          border: Border.all(color: const Color(0xFFF1D9A8)),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(
              Icons.warehouse_outlined,
              size: compact ? 15 : 18,
              color: const Color(0xFF7A4E0E),
            ),
            SizedBox(width: compact ? 6 : 8),
            Expanded(
              child: Text(
                WarehouseShipping.note,
                style: TextStyle(
                  fontSize: compact ? 11 : 12.5,
                  fontWeight: FontWeight.w600,
                  height: 1.4,
                  color: const Color(0xFF7A4E0E),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

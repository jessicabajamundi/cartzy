import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'rider_order_detail.dart';

// ============================================================
// SHARED ORDER CARD
// ============================================================
//
// Used by the pickup dashboard, delivery dashboard and delivery
// history screens so they all look consistent.

class RiderOrderCard extends StatelessWidget {
  final RiderOrder order;
  final String badgeLabel;
  final IconData badgeIcon;
  final String? actionLabel;
  final VoidCallback? onAction;

  const RiderOrderCard({
    super.key,
    required this.order,
    required this.badgeLabel,
    required this.badgeIcon,
    this.actionLabel,
    this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: CartzyColors.surface,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(badgeIcon, color: CartzyColors.coral, size: 20),
              const SizedBox(width: 8),
              Text(
                badgeLabel,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: CartzyColors.coral,
                  letterSpacing: 0.6,
                ),
              ),
              const Spacer(),
              Text(
                '#${order.id}',
                style: const TextStyle(fontSize: 12, color: CartzyColors.gray),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            order.itemsSummary,
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.bold,
              color: CartzyColors.navy,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            '${order.buyerName}${order.buyerPhone.isNotEmpty ? ' · ${order.buyerPhone}' : ''}',
            style: const TextStyle(fontSize: 13, color: CartzyColors.gray),
          ),
          Text(
            order.deliveryAddress,
            style: const TextStyle(fontSize: 13, color: CartzyColors.gray),
          ),
          const SizedBox(height: 6),
          Text(
            '₱${order.totalAmount.toStringAsFixed(2)}',
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: CartzyColors.navy,
            ),
          ),
          if (actionLabel != null) ...[
            const SizedBox(height: 14),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: CartzyColors.navy,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                onPressed: onAction,
                child: Text(
                  actionLabel!,
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
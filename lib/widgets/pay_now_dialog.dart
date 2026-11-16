import 'package:flutter/material.dart';
import 'package:cartzy/services/review_service.dart';

/// Compatibility view for legacy local orders; it never simulates a payment.
class PayNowDialog extends StatelessWidget {
  final Order order;
  const PayNowDialog({super.key, required this.order});

  static Future<void> show(BuildContext context, {required Order order}) =>
      showModalBottomSheet<void>(
          context: context, builder: (_) => PayNowDialog(order: order));

  @override
  Widget build(BuildContext context) => SafeArea(
          child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Payment unavailable here',
                  style: Theme.of(context).textTheme.titleLarge),
              const SizedBox(height: 12),
              const Text(
                  'Open your server-backed orders and use the seller payment details there. This legacy order view cannot confirm or transfer payment.'),
              const SizedBox(height: 16),
              TextButton(
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Text('Close')),
            ]),
      ));
}

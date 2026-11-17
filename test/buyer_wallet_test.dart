import 'package:cartzy/buyer/wallet_screen.dart';
import 'package:cartzy/theme/cartzy_theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  for (final dark in [false, true]) {
    testWidgets('wallet shows spendable credit separately from pending refunds, dark=$dark', (tester) async {
      await tester.pumpWidget(MaterialApp(
        theme: dark ? CartzyTheme.darkTheme : CartzyTheme.lightTheme,
        home: WalletScreen(loadWallet: () async => {
          'balance_minor': 15000, 'pending_refunds_minor': 5000,
          'transactions': [
            {'amount_minor': 50000, 'description': 'Refund for ORD-1', 'order_reference': 'ORD-1', 'created_at': 'Nov 17, 2026 · 10:35 AM'},
            {'amount_minor': -35000, 'description': 'Payment for ORD-2', 'order_reference': 'ORD-2', 'created_at': 'Nov 17, 2026 · 10:42 AM'},
          ],
        }),
      ));
      await tester.pump();
      expect(find.text('₱150.00'), findsOneWidget);
      expect(find.text('Pending approved refunds: ₱50.00'), findsOneWidget);
      expect(find.text('+₱500.00'), findsOneWidget);
      expect(find.text('−₱350.00'), findsOneWidget);
      expect(find.textContaining('No cash-out'), findsOneWidget);
      expect(tester.takeException(), isNull);
      await tester.pumpWidget(const SizedBox.shrink());
    });
  }

  testWidgets('wallet updates after refund and retains confirmed balance when offline', (tester) async {
    var requests = 0;
    await tester.pumpWidget(MaterialApp(home: WalletScreen(loadWallet: () async {
      requests++;
      if (requests == 3) throw Exception('offline');
      return {'balance_minor': requests == 1 ? 0 : 50000, 'pending_refunds_minor': 0, 'transactions': []};
    })));
    await tester.pump();
    expect(find.text('₱0.00'), findsOneWidget);
    await tester.pump(const Duration(seconds: 10));
    await tester.pump();
    expect(find.text('₱500.00'), findsOneWidget);
    await tester.pump(const Duration(seconds: 10));
    await tester.pump();
    expect(find.text('₱500.00'), findsOneWidget);
    expect(find.textContaining('Could not refresh'), findsOneWidget);
    await tester.pumpWidget(const SizedBox.shrink());
  });
}

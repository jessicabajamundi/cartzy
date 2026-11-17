import 'dart:async';
import 'package:flutter/material.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/wallet_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

class WalletCard extends StatefulWidget {
  final Future<Map<String, dynamic>> Function()? loadWallet;
  const WalletCard({super.key, this.loadWallet});
  @override
  State<WalletCard> createState() => _WalletCardState();
}

class _WalletCardState extends State<WalletCard> {
  Map<String, dynamic>? _wallet;
  Timer? _timer;
  bool _busy = false;
  @override
  void initState() {
    super.initState();
    _load();
    _timer = Timer.periodic(const Duration(seconds: 10), (_) => _load());
  }
  Future<void> _load() async {
    if (_busy || (widget.loadWallet == null && (AuthService.token ?? '').isEmpty)) return;
    _busy = true;
    try {
      final data = await (widget.loadWallet ?? WalletService.fetch)();
      if (mounted) setState(() => _wallet = data);
    } catch (_) { /* Retain the last confirmed balance. */ }
    finally { _busy = false; }
  }
  @override
  void dispose() { _timer?.cancel(); super.dispose(); }
  @override
  Widget build(BuildContext context) => Card(
    margin: EdgeInsets.zero,
    child: ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
      leading: const Icon(Icons.account_balance_wallet_outlined),
      title: const Text('Cartzy Wallet', style: TextStyle(fontWeight: FontWeight.bold)),
      subtitle: Text(_wallet == null ? 'Refund credit · No cash-out' : '${WalletService.money(_wallet!['balance_minor'] as num)} available · No cash-out'),
      trailing: const Icon(Icons.chevron_right),
      onTap: () async {
        await Navigator.of(context).push(MaterialPageRoute(builder: (_) => WalletScreen(loadWallet: widget.loadWallet)));
        _load();
      },
    ),
  );
}

class WalletScreen extends StatefulWidget {
  final Future<Map<String, dynamic>> Function()? loadWallet;
  const WalletScreen({super.key, this.loadWallet});
  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> {
  Map<String, dynamic>? _wallet;
  String? _error;
  Timer? _timer;
  bool _busy = false;
  @override
  void initState() {
    super.initState();
    _load();
    _timer = Timer.periodic(const Duration(seconds: 10), (_) => _load());
  }
  Future<void> _load() async {
    if (_busy) return;
    _busy = true;
    try {
      final wallet = await (widget.loadWallet ?? WalletService.fetch)();
      if (mounted) setState(() { _wallet = wallet; _error = null; });
    } catch (_) {
      if (mounted) setState(() => _error = 'Could not refresh your wallet. Pull down to retry.');
    } finally { _busy = false; }
  }
  @override
  void dispose() { _timer?.cancel(); super.dispose(); }
  @override
  Widget build(BuildContext context) {
    final entries = (_wallet?['transactions'] as List? ?? []).whereType<Map>();
    return Scaffold(
      backgroundColor: CartzyColors.elevatedOf(context, light: CartzyColors.background),
      appBar: AppBar(title: const Text('Cartzy Wallet')),
      body: RefreshIndicator(onRefresh: _load, child: ListView(
        physics: const AlwaysScrollableScrollPhysics(), padding: const EdgeInsets.all(20),
        children: [
          if (_error != null) Text(_error!, semanticsLabel: _error),
          if (_wallet == null && _error == null) const Center(child: CircularProgressIndicator()),
          const Text('Available balance'),
          Text(_wallet == null ? '—' : WalletService.money(_wallet!['balance_minor'] as num),
            style: const TextStyle(fontSize: 34, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          const Text('Spend refunded credit on any product in Cartzy. No cash-out or top-ups.'),
          const SizedBox(height: 20),
          Text('Pending approved refunds: ${WalletService.money((_wallet?['pending_refunds_minor'] as num?) ?? 0)}'),
          const Text('Pending refunds are not available to spend yet.'),
          const SizedBox(height: 24),
          const Text('Recent transactions', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
          const Text('Latest 50 entries · Philippine time'),
          if (_wallet != null && entries.isEmpty) const Padding(padding: EdgeInsets.symmetric(vertical: 24),
            child: Text('Your completed refunds and wallet payments will appear here.')),
          for (final entry in entries) ListTile(contentPadding: EdgeInsets.zero,
            leading: Icon((entry['amount_minor'] as num) > 0 ? Icons.add_circle_outline : Icons.shopping_bag_outlined),
            title: Text('${entry['description']}'),
            subtitle: Text('${entry['order_reference'] ?? ''}\n${entry['created_at']}'),
            trailing: Text('${(entry['amount_minor'] as num) > 0 ? '+' : '−'}${WalletService.money((entry['amount_minor'] as num).abs())}'),
          ),
        ],
      )),
    );
  }
}

import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
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
    } catch (_) {
      /* Retain the last confirmed balance. */
    } finally {
      _busy = false;
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Card(
        margin: EdgeInsets.zero,
        child: ListTile(
          contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
          leading: const Icon(Icons.account_balance_wallet_outlined),
          title: const Text('Cartzy Wallet', style: TextStyle(fontWeight: FontWeight.bold)),
          subtitle: Text(_wallet == null
              ? 'Refund credit · No cash-out'
              : '${WalletService.money(_wallet!['balance_minor'] as num)} available · No cash-out'),
          trailing: const Icon(Icons.chevron_right),
          onTap: () async {
            await Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => WalletScreen(loadWallet: widget.loadWallet)));
            _load();
          },
        ),
      );
}

class WalletScreen extends StatefulWidget {
  final Future<Map<String, dynamic>> Function()? loadWallet;
  final Future<List<Map<String, dynamic>>> Function()? loadPendingRefunds;

  const WalletScreen({super.key, this.loadWallet, this.loadPendingRefunds});
  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> {
  static const _rose = Color(0xFFD8A7B8);
  static const _ink = Color(0xFF261E30);

  Map<String, dynamic>? _wallet;
  List<Map<String, dynamic>> _pendingRefunds = const [];
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
      List<Map<String, dynamic>> pending = _pendingRefunds;
      try {
        pending = await (widget.loadPendingRefunds ?? WalletService.fetchPendingRefunds)();
      } catch (_) {}
      if (mounted) {
        setState(() {
          _wallet = wallet;
          _pendingRefunds = pending;
          _error = null;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _error = 'Could not refresh your wallet. Pull down to retry.');
    } finally {
      _busy = false;
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  void _openTransactionHistoryModal(List<Map> entries) {
    showDialog<void>(
      context: context,
      builder: (ctx) {
        final isDark = Theme.of(ctx).brightness == Brightness.dark;
        return Dialog(
          backgroundColor: CartzyColors.surfaceOf(ctx, light: Colors.white),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          insetPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 24),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 500, maxHeight: 540),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 18, 12, 12),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'CARTZY WALLET',
                              style: GoogleFonts.dmSans(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w800,
                                letterSpacing: 0.9,
                                color: isDark ? _rose : const Color(0xFF865561),
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Transaction History',
                              style: GoogleFonts.dmSans(
                                fontSize: 18,
                                fontWeight: FontWeight.w800,
                                color: CartzyColors.textOf(ctx, light: _ink),
                              ),
                            ),
                            Text(
                              'Latest 50 entries · Philippine time',
                              style: GoogleFonts.dmSans(
                                fontSize: 12,
                                color: CartzyColors.mutedOf(ctx, light: const Color(0xFF7A6E72)),
                              ),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        onPressed: () => Navigator.of(ctx).pop(),
                        icon: const Icon(Icons.close_rounded),
                      ),
                    ],
                  ),
                ),
                const Divider(height: 1),
                Flexible(
                  child: entries.isEmpty
                      ? Padding(
                          padding: const EdgeInsets.all(24),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.receipt_long_outlined,
                                  size: 36,
                                  color: CartzyColors.mutedOf(ctx, light: const Color(0xFF865561))),
                              const SizedBox(height: 10),
                              Text(
                                'No transactions yet',
                                style: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w700),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Your completed refunds and wallet payments will appear here.',
                                textAlign: TextAlign.center,
                                style: GoogleFonts.dmSans(
                                  fontSize: 12.5,
                                  color: CartzyColors.mutedOf(ctx, light: const Color(0xFF7A6E72)),
                                ),
                              ),
                            ],
                          ),
                        )
                      : ListView.separated(
                          shrinkWrap: true,
                          padding: const EdgeInsets.all(16),
                          itemCount: entries.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 10),
                          itemBuilder: (_, i) => _buildTransactionTile(ctx, entries[i]),
                        ),
                ),
                const Divider(height: 1),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  child: Align(
                    alignment: Alignment.centerRight,
                    child: TextButton(
                      onPressed: () => Navigator.of(ctx).pop(),
                      child: const Text('Close'),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _openPendingRefundsModal() {
    showDialog<void>(
      context: context,
      builder: (ctx) {
        final isDark = Theme.of(ctx).brightness == Brightness.dark;
        return Dialog(
          backgroundColor: CartzyColors.surfaceOf(ctx, light: Colors.white),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          insetPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 24),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 500, maxHeight: 540),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 18, 12, 12),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'RETURNS & REFUNDS',
                              style: GoogleFonts.dmSans(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w800,
                                letterSpacing: 0.9,
                                color: isDark ? _rose : const Color(0xFF865561),
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Pending Refund Requests',
                              style: GoogleFonts.dmSans(
                                fontSize: 18,
                                fontWeight: FontWeight.w800,
                                color: CartzyColors.textOf(ctx, light: _ink),
                              ),
                            ),
                            Text(
                              'Active return and refund cases linked to your orders.',
                              style: GoogleFonts.dmSans(
                                fontSize: 12,
                                color: CartzyColors.mutedOf(ctx, light: const Color(0xFF7A6E72)),
                              ),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        onPressed: () => Navigator.of(ctx).pop(),
                        icon: const Icon(Icons.close_rounded),
                      ),
                    ],
                  ),
                ),
                const Divider(height: 1),
                Flexible(
                  child: _pendingRefunds.isEmpty
                      ? Padding(
                          padding: const EdgeInsets.all(24),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.assignment_return_outlined,
                                  size: 36,
                                  color: CartzyColors.mutedOf(ctx, light: const Color(0xFF865561))),
                              const SizedBox(height: 10),
                              Text(
                                'No pending refund requests',
                                style: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w700),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'When you submit a return or refund request, its progress will appear here.',
                                textAlign: TextAlign.center,
                                style: GoogleFonts.dmSans(
                                  fontSize: 12.5,
                                  color: CartzyColors.mutedOf(ctx, light: const Color(0xFF7A6E72)),
                                ),
                              ),
                            ],
                          ),
                        )
                      : ListView.separated(
                          shrinkWrap: true,
                          padding: const EdgeInsets.all(16),
                          itemCount: _pendingRefunds.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 10),
                          itemBuilder: (_, i) {
                            final item = _pendingRefunds[i];
                            final ref = item['order_reference']?.toString() ?? 'Case #${item['id']}';
                            final status = (item['status'] ?? 'pending')
                                .toString()
                                .replaceAll('_', ' ')
                                .toUpperCase();
                            final amount = (item['amount_minor'] as num?) ?? 0;
                            return Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: CartzyColors.elevatedOf(ctx, light: const Color(0xFFFAF9FB)),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: CartzyColors.borderOf(ctx, light: const Color(0xFFE8E0E4)),
                                ),
                              ),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(ref,
                                            style: GoogleFonts.dmSans(
                                                fontSize: 13.5, fontWeight: FontWeight.w700)),
                                        const SizedBox(height: 2),
                                        Text(status,
                                            style: GoogleFonts.dmSans(
                                              fontSize: 11.5,
                                              fontWeight: FontWeight.w600,
                                              color: CartzyColors.mutedOf(
                                                  ctx, light: const Color(0xFF7A6E72)),
                                            )),
                                      ],
                                    ),
                                  ),
                                  Text(
                                    WalletService.money(amount),
                                    style: GoogleFonts.dmSans(
                                      fontSize: 13.5,
                                      fontWeight: FontWeight.w800,
                                      color: isDark ? const Color(0xFF34D399) : const Color(0xFF047857),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                ),
                const Divider(height: 1),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  child: Align(
                    alignment: Alignment.centerRight,
                    child: TextButton(
                      onPressed: () => Navigator.of(ctx).pop(),
                      child: const Text('Close'),
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildTransactionTile(BuildContext context, Map entry) {
    final amount = (entry['amount_minor'] as num?) ?? 0;
    final isCredit = amount > 0;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final amountColor = isCredit
        ? (isDark ? const Color(0xFF34D399) : const Color(0xFF047857))
        : (isDark ? const Color(0xFFFB7185) : const Color(0xFFBE123C));

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: CartzyColors.surfaceOf(context, light: Colors.white),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: CartzyColors.borderOf(context, light: const Color(0xFFECE3E8)),
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: amountColor.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              isCredit ? Icons.add_circle_outline : Icons.shopping_bag_outlined,
              size: 18,
              color: amountColor,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '${entry['description']}',
                  style: GoogleFonts.dmSans(fontSize: 13.5, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 2),
                Text(
                  '${entry['order_reference'] ?? ''} · ${entry['created_at']}',
                  style: GoogleFonts.dmSans(
                    fontSize: 11.5,
                    color: CartzyColors.mutedOf(context, light: const Color(0xFF7A6E72)),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Text(
            '${isCredit ? '+' : '−'}${WalletService.money(amount.abs())}',
            style: GoogleFonts.dmSans(
              fontSize: 13.5,
              fontWeight: FontWeight.w800,
              color: amountColor,
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final entries = (_wallet?['transactions'] as List? ?? []).whereType<Map>().toList();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: CartzyColors.elevatedOf(context, light: CartzyColors.background),
      appBar: AppBar(title: const Text('Cartzy Wallet')),
      body: RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20),
          children: [
            if (_error != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Text(_error!, semanticsLabel: _error),
              ),
            if (_wallet == null && _error == null)
              const Padding(
                padding: EdgeInsets.only(bottom: 16),
                child: Center(child: CircularProgressIndicator()),
              ),
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: CartzyColors.surfaceOf(context, light: Colors.white),
                borderRadius: BorderRadius.circular(18),
                border: Border.all(
                  color: CartzyColors.borderOf(context, light: const Color(0xFFECE3E8)),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Available balance',
                        style: GoogleFonts.dmSans(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
                          color: CartzyColors.mutedOf(context, light: const Color(0xFF7A6E72)),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: _rose.withValues(alpha: 0.16),
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text(
                          'Store Credit Only',
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: isDark ? _rose : const Color(0xFF865561),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    _wallet == null ? '—' : WalletService.money(_wallet!['balance_minor'] as num),
                    style: GoogleFonts.dmSans(
                      fontSize: 34,
                      fontWeight: FontWeight.w800,
                      color: CartzyColors.textOf(context, light: _ink),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Spend refunded credit on any product in Cartzy. No cash-out or top-ups.',
                    style: GoogleFonts.dmSans(
                      fontSize: 12.5,
                      color: CartzyColors.mutedOf(context, light: const Color(0xFF7A6E72)),
                    ),
                  ),
                  const SizedBox(height: 14),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: CartzyColors.elevatedOf(context, light: const Color(0xFFFAF5F7)),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Pending approved refunds: ${WalletService.money((_wallet?['pending_refunds_minor'] as num?) ?? 0)}',
                          style: GoogleFonts.dmSans(fontSize: 12.5, fontWeight: FontWeight.w700),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Pending refunds are not available to spend yet.',
                          style: GoogleFonts.dmSans(
                            fontSize: 11.5,
                            color: CartzyColors.mutedOf(context, light: const Color(0xFF7A6E72)),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),
                  Wrap(
                    spacing: 10,
                    runSpacing: 10,
                    children: [
                      OutlinedButton.icon(
                        onPressed: () => _openTransactionHistoryModal(entries),
                        icon: const Icon(Icons.history_rounded, size: 17),
                        label: Text('Transaction History (${entries.length})'),
                        style: OutlinedButton.styleFrom(
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                      FilledButton.icon(
                        onPressed: _openPendingRefundsModal,
                        icon: const Icon(Icons.assignment_return_outlined, size: 17),
                        label: Text(_pendingRefunds.isEmpty
                            ? 'View Pending Refund Requests'
                            : 'View Pending Refund Requests (${_pendingRefunds.length})'),
                        style: FilledButton.styleFrom(
                          backgroundColor: _rose,
                          foregroundColor: const Color(0xFF1F1726),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 22),
            Text(
              'Recent transactions',
              style: GoogleFonts.dmSans(fontWeight: FontWeight.w800, fontSize: 17),
            ),
            Text(
              'Latest 50 entries · Philippine time',
              style: GoogleFonts.dmSans(
                fontSize: 12,
                color: CartzyColors.mutedOf(context, light: const Color(0xFF7A6E72)),
              ),
            ),
            const SizedBox(height: 12),
            if (_wallet != null && entries.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 20),
                child: Text('Your completed refunds and wallet payments will appear here.'),
              ),
            for (final entry in entries) ...[
              _buildTransactionTile(context, entry),
              const SizedBox(height: 10),
            ],
          ],
        ),
      ),
    );
  }
}

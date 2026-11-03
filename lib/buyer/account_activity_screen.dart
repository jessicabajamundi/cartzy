import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

/// One entry from GET /account/activity (the same log the website shows).
class ActivityEntry {
  final String event;
  final String group;
  final String title;
  final String? description;
  final String channel;
  final String device;
  final String? ipAddress;
  final DateTime createdAt;

  const ActivityEntry({
    required this.event,
    required this.group,
    required this.title,
    required this.description,
    required this.channel,
    required this.device,
    required this.ipAddress,
    required this.createdAt,
  });

  factory ActivityEntry.fromJson(Map<String, dynamic> json) => ActivityEntry(
        event: json['event']?.toString() ?? '',
        group: json['group']?.toString() ?? 'account',
        title: json['title']?.toString() ?? 'Activity',
        description: json['description']?.toString(),
        channel: json['channel']?.toString() ?? 'web',
        device: json['device']?.toString() ?? '',
        ipAddress: json['ip_address']?.toString(),
        createdAt: DateTime.tryParse(json['created_at']?.toString() ?? '')?.toLocal() ?? DateTime.now(),
      );
}

/// Sign-ins, account changes and orders from both the website and the app.
class AccountActivityScreen extends StatefulWidget {
  const AccountActivityScreen({super.key});

  @override
  State<AccountActivityScreen> createState() => _AccountActivityScreenState();
}

class _AccountActivityScreenState extends State<AccountActivityScreen> {
  static const _filters = {
    'all': 'All',
    'security': 'Sign-ins & security',
    'account': 'Account changes',
    'orders': 'Orders',
  };
  static const _months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

  final List<ActivityEntry> _entries = [];
  String _filter = 'all';
  int? _nextPage = 1;
  bool _loading = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load(reset: true);
  }

  Future<void> _load({bool reset = false}) async {
    if (_loading || (!reset && _nextPage == null)) return;
    setState(() {
      _loading = true;
      _error = null;
      if (reset) {
        _entries.clear();
        _nextPage = 1;
      }
    });
    try {
      final group = _filter == 'all' ? '' : '&group=$_filter';
      final data = await ApiService.get('/account/activity?page=$_nextPage$group', token: AuthService.token);
      final rows = (data['activity'] as List? ?? [])
          .map((row) => ActivityEntry.fromJson(Map<String, dynamic>.from(row as Map)));
      if (!mounted) return;
      setState(() {
        _entries.addAll(rows);
        _nextPage = data['next_page'] is int ? data['next_page'] as int : null;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _error = e is ApiException ? e.message : 'Could not load your activity. Please try again.';
      });
    }
  }

  void _setFilter(String filter) {
    if (filter == _filter) return;
    _filter = filter;
    _load(reset: true);
  }

  String _dayLabel(DateTime date) {
    final today = DateTime.now();
    final day = DateTime(date.year, date.month, date.day);
    final diff = DateTime(today.year, today.month, today.day).difference(day).inDays;
    if (diff == 0) return 'Today';
    if (diff == 1) return 'Yesterday';
    return '${_months[date.month - 1]} ${date.day}, ${date.year}';
  }

  String _time(DateTime date) {
    final hour = date.hour % 12 == 0 ? 12 : date.hour % 12;
    return '$hour:${date.minute.toString().padLeft(2, '0')} ${date.hour < 12 ? 'AM' : 'PM'}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.brand50,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: CartzyColors.brandDark,
        elevation: 0,
        title: Text(
          'Account activity',
          style: GoogleFonts.cormorantGaramond(fontSize: 24, fontWeight: FontWeight.w700, color: CartzyColors.brand900),
        ),
      ),
      body: Column(
        children: [
          _buildFilters(),
          Expanded(
            child: RefreshIndicator(
              color: CartzyColors.rose700,
              onRefresh: () => _load(reset: true),
              child: _buildList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilters() {
    return Container(
      color: Colors.white,
      height: 56,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        children: [
          for (final entry in _filters.entries)
            Padding(
              padding: const EdgeInsets.only(right: 8),
              child: ChoiceChip(
                label: Text(entry.value),
                selected: _filter == entry.key,
                onSelected: (_) => _setFilter(entry.key),
                showCheckmark: false,
                selectedColor: CartzyColors.brandDark,
                backgroundColor: Colors.white,
                side: BorderSide(color: _filter == entry.key ? CartzyColors.brandDark : CartzyColors.brand200, width: 1.5),
                shape: const StadiumBorder(),
                labelStyle: GoogleFonts.lato(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: _filter == entry.key ? Colors.white : CartzyColors.brandDark,
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildList() {
    if (_entries.isEmpty) {
      return ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          const SizedBox(height: 80),
          if (_loading)
            const Center(child: CircularProgressIndicator(color: CartzyColors.rose700))
          else
            _buildMessage(
              icon: _error != null ? Icons.wifi_off_rounded : Icons.history_rounded,
              title: _error != null ? 'Something went wrong' : 'No activity yet',
              body: _error ?? 'Your sign-ins and account changes will show up here.',
            ),
        ],
      );
    }

    // Day headers between entries, then a "load more" footer.
    final items = <Widget>[];
    String? lastDay;
    for (final entry in _entries) {
      final day = _dayLabel(entry.createdAt);
      if (day != lastDay) {
        items.add(Padding(
          padding: const EdgeInsets.fromLTRB(20, 20, 20, 8),
          child: Text(
            day.toUpperCase(),
            style: GoogleFonts.lato(fontSize: 11.5, fontWeight: FontWeight.w700, letterSpacing: 1.6, color: CartzyColors.rose700),
          ),
        ));
        lastDay = day;
      }
      items.add(_buildEntry(entry));
    }
    if (_nextPage != null || _error != null) {
      items.add(Padding(
        padding: const EdgeInsets.all(20),
        child: Center(
          child: _loading
              ? const CircularProgressIndicator(color: CartzyColors.rose700)
              : OutlinedButton(
                  key: const ValueKey('activity-load-more'),
                  onPressed: _load,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: CartzyColors.brandDark,
                    side: const BorderSide(color: CartzyColors.brand200, width: 1.5),
                    shape: const StadiumBorder(),
                    padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 12),
                  ),
                  child: Text(_error != null ? 'Try again' : 'Show older activity', style: GoogleFonts.lato(fontWeight: FontWeight.w700)),
                ),
        ),
      ));
    }
    items.add(const SizedBox(height: 24));

    return ListView(physics: const AlwaysScrollableScrollPhysics(), children: items);
  }

  Widget _buildEntry(ActivityEntry entry) {
    final alert = entry.event == 'failed_login';
    final (IconData icon, Color fg, Color bg) = switch (entry.group) {
      _ when alert => (Icons.gpp_maybe_outlined, CartzyColors.error, const Color(0xFFFDE4E4)),
      'security' => (Icons.shield_outlined, CartzyColors.brandDark, CartzyColors.brand100),
      'orders' => (Icons.shopping_bag_outlined, const Color(0xFF1E7E5A), const Color(0xFFEEF6F1)),
      _ => (Icons.person_outline_rounded, CartzyColors.rose700, CartzyColors.rose50Warm),
    };

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: alert ? const Color(0xFFFFF6F6) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: CartzyColors.brand200),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(12)),
            child: Icon(icon, size: 19, color: fg),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Text(entry.title,
                          style: GoogleFonts.lato(fontSize: 14.5, fontWeight: FontWeight.w700, color: CartzyColors.brand900)),
                    ),
                    const SizedBox(width: 8),
                    Text(_time(entry.createdAt),
                        style: GoogleFonts.lato(fontSize: 12, fontWeight: FontWeight.w700, color: CartzyColors.grayMid)),
                  ],
                ),
                if (entry.description != null && entry.description!.isNotEmpty) ...[
                  const SizedBox(height: 3),
                  Text(entry.description!, style: GoogleFonts.lato(fontSize: 13, color: CartzyColors.brandDark)),
                ],
                const SizedBox(height: 7),
                Wrap(
                  spacing: 6,
                  runSpacing: 4,
                  crossAxisAlignment: WrapCrossAlignment.center,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(color: CartzyColors.brand100, borderRadius: BorderRadius.circular(20)),
                      child: Text(entry.channel == 'mobile' ? 'Mobile app' : 'Website',
                          style: GoogleFonts.lato(fontSize: 11, fontWeight: FontWeight.w700, color: CartzyColors.brandDark)),
                    ),
                    Text(
                      [entry.device, if (entry.ipAddress != null) 'IP ${entry.ipAddress}'].where((s) => s.isNotEmpty).join(' · '),
                      style: GoogleFonts.lato(fontSize: 11.5, color: CartzyColors.grayMid),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMessage({required IconData icon, required String title, required String body}) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 32),
      child: Column(
        children: [
          Container(
            width: 72,
            height: 72,
            decoration: BoxDecoration(
              gradient: const LinearGradient(colors: [CartzyColors.brand100, CartzyColors.rose50Warm]),
              borderRadius: BorderRadius.circular(22),
            ),
            child: Icon(icon, size: 32, color: CartzyColors.rose700),
          ),
          const SizedBox(height: 14),
          Text(title, style: GoogleFonts.cormorantGaramond(fontSize: 24, fontWeight: FontWeight.w700, color: CartzyColors.brand900)),
          const SizedBox(height: 6),
          Text(body, textAlign: TextAlign.center, style: GoogleFonts.lato(fontSize: 13.5, color: CartzyColors.grayMid)),
          if (_error != null) ...[
            const SizedBox(height: 16),
            OutlinedButton(onPressed: () => _load(reset: true), child: const Text('Try again')),
          ],
        ],
      ),
    );
  }
}

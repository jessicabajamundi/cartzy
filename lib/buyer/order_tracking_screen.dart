import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/services/api_service.dart' show ApiException;
import 'package:cartzy/services/order_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/realtime_order_map.dart';

// ============================================================
// ORDER TRACKING — "Delivery updates" for one order, the same
// timeline the website shows under "Track order".
// ============================================================

class OrderTrackingScreen extends StatefulWidget {
  final int sellerOrderId;
  final String reference;

  /// Loads the timeline; defaults to the Cartzy API.
  final Future<Map<String, dynamic>> Function(int sellerOrderId)? loadTracking;

  const OrderTrackingScreen({
    super.key,
    required this.sellerOrderId,
    required this.reference,
    this.loadTracking,
  });

  @override
  State<OrderTrackingScreen> createState() => _OrderTrackingScreenState();
}

class _OrderTrackingScreenState extends State<OrderTrackingScreen> {
  static const _ink = Color(0xFF261E30);
  static const _muted = Color(0xFF756A7C);
  static const _brand = Color(0xFF564B68);
  static const _rose = Color(0xFFC08B7F);

  Map<String, dynamic>? _tracking;
  String? _error;
  bool _loading = true;

  Timer? _livePollTimer;
  String? _lastStatus;
  int _lastDoneCount = 0;
  String? _lastRecordedUpdate;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _livePollTimer?.cancel();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = _tracking == null;
      _error = null;
    });
    try {
      final tracking =
          await (widget.loadTracking ?? OrderService.fetchTracking)(widget.sellerOrderId);
      if (!mounted) return;

      final steps = (tracking['steps'] as List? ?? const []);
      final doneCount = steps.where((s) => s is Map && s['done'] == true).length;

      _lastStatus = tracking['current_status']?.toString();
      _lastRecordedUpdate = tracking['last_update']?.toString();
      _lastDoneCount = doneCount;

      setState(() {
        _tracking = tracking;
        _loading = false;
      });

      _startRealtimePolling();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _error = e is ApiException
            ? e.message
            : "Couldn't reach the Cartzy server. Check your connection and try again.";
      });
    }
  }

  void _startRealtimePolling() {
    _livePollTimer?.cancel();
    // Poll every 3 seconds for live tracking updates
    _livePollTimer = Timer.periodic(const Duration(seconds: 3), (_) => _pollSilentUpdate());
  }

  Future<void> _pollSilentUpdate() async {
    if (!mounted) return;
    try {
      final tracking =
          await (widget.loadTracking ?? OrderService.fetchTracking)(widget.sellerOrderId);
      if (!mounted) return;

      final newStatus = tracking['current_status']?.toString();
      final newUpdate = tracking['last_update']?.toString();
      final steps = (tracking['steps'] as List? ?? const []);
      final doneCount = steps.where((s) => s is Map && s['done'] == true).length;

      final hasChanged = (_lastStatus != null && newStatus != _lastStatus) ||
          (_lastDoneCount > 0 && doneCount > _lastDoneCount) ||
          (_lastRecordedUpdate != null && newUpdate != null && newUpdate != _lastRecordedUpdate);

      if (hasChanged) {
        _lastStatus = newStatus;
        _lastDoneCount = doneCount;
        _lastRecordedUpdate = newUpdate;

        setState(() {
          _tracking = tracking;
        });

        _notifyUpdate(newStatus ?? 'Status updated', tracking);
      } else {
        // Silently update live telemetry coordinates if map is moving
        if (mounted && _tracking != null && tracking['map'] != null) {
          setState(() {
            _tracking!['map'] = tracking['map'];
          });
        }
      }
    } catch (_) {
      // Background poll failure is silent
    }
  }

  void _notifyUpdate(String newStatus, Map<String, dynamic> data) {
    if (!mounted) return;
    final steps = (data['steps'] as List? ?? const []);
    Map? latestStep;
    for (int i = steps.length - 1; i >= 0; i--) {
      if (steps[i] is Map && steps[i]['done'] == true) {
        latestStep = steps[i] as Map;
        break;
      }
    }
    final detail = latestStep?['detail']?.toString();

    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(7),
              decoration: const BoxDecoration(
                color: Color(0xFF10B981),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.check, color: Colors.white, size: 18),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'Delivery Status Updated',
                    style: GoogleFonts.dmSans(
                      fontWeight: FontWeight.w800,
                      fontSize: 13,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '$newStatus${detail != null ? ' · $detail' : ''}',
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      color: Colors.white.withValues(alpha: 0.9),
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ],
        ),
        backgroundColor: const Color(0xFF261E30),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        duration: const Duration(seconds: 4),
      ),
    );
  }

  String _text(String key, [String fallback = '—']) {
    final value = _tracking?[key];
    if (value == null || '$value'.isEmpty) return fallback;
    return '$value';
  }

  List<Map<String, dynamic>> _list(String key) => (_tracking?[key] as List? ?? const [])
      .map((e) => Map<String, dynamic>.from(e as Map))
      .toList();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.background,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('DELIVERY UPDATES',
                style: GoogleFonts.dmSans(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 1.4,
                    color: const Color(0xFF9E7766))),
            Text('Tracking ${widget.reference}',
                style: GoogleFonts.dmSans(fontSize: 17, fontWeight: FontWeight.w800, color: _ink)),
          ],
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: _brand, strokeWidth: 2.4))
          : RefreshIndicator(
              color: _brand,
              onRefresh: _load,
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 28),
                children: _tracking == null ? [_errorCard()] : _content(),
              ),
            ),
    );
  }

  Widget _errorCard() {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: _cardDecoration(),
      child: Column(
        children: [
          const Icon(Icons.cloud_off_outlined, size: 34, color: Color(0xFF9E7766)),
          const SizedBox(height: 10),
          Text(_error ?? 'Tracking is not available yet.',
              textAlign: TextAlign.center,
              style: GoogleFonts.dmSans(fontSize: 13.5, color: _muted, height: 1.4)),
          const SizedBox(height: 14),
          TextButton(onPressed: _load, child: const Text('Try again')),
        ],
      ),
    );
  }

  BoxDecoration _cardDecoration() => BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFECE3E5), width: 1.1),
      );

  List<Widget> _content() {
    final steps = _list('steps');
    final history = _list('history');
    final attention = _tracking?['attention'];
    final attempts = _tracking?['attempts'];

    return [
      // Summary (dark band, like the website)
      Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          gradient: const LinearGradient(colors: [Color(0xFF3F3550), _brand]),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Wrap(
          spacing: 24,
          runSpacing: 14,
          children: [
            _summary('Order', _text('order')),
            _summary('Tracking no.', _text('tracking_no')),
            _summary('Estimated arrival', _text('eta'), highlight: true),
          ],
        ),
      ),
      const SizedBox(height: 14),

      // Realtime OpenStreetMap Component
      RealtimeOrderMap(
        orderReference: widget.reference,
        status: _text('current_status'),
        mapData: _tracking?['map'] is Map
            ? Map<String, dynamic>.from(_tracking!['map'] as Map)
            : null,
        height: 350,
      ),
      const SizedBox(height: 14),

      Container(
        padding: const EdgeInsets.all(18),
        decoration: _cardDecoration(),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text.rich(
              TextSpan(children: [
                const TextSpan(text: 'Courier: '),
                TextSpan(
                    text: _text('courier'),
                    style: const TextStyle(fontWeight: FontWeight.w700, color: _ink)),
              ]),
              style: GoogleFonts.dmSans(fontSize: 13, color: _muted),
            ),
            const SizedBox(height: 14),
            _detail('Latest confirmed stage', _text('current_status')),
            _detail('Last recorded update · Philippine time', _text('last_update')),
            _detail('Assigned rider', _text('rider')),
            _detail('Delivery area', _text('area')),
            _detail('Delivery attempts',
                attempts == null || attempts == 0 ? 'No attempt recorded' : '$attempts'),
            if (attention != null && '$attention'.isNotEmpty)
              Container(
                width: double.infinity,
                margin: const EdgeInsets.only(top: 6),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFFBEB),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFFDE68A)),
                ),
                child: Text('$attention',
                    style: GoogleFonts.dmSans(
                        fontSize: 12.5, height: 1.4, color: const Color(0xFF92400E))),
              ),
            const SizedBox(height: 10),
            Text(
              'Realtime OpenStreetMap GPS tracking active. Courier location updates continuously along the delivery route.',
              style: GoogleFonts.dmSans(fontSize: 11.5, height: 1.45, color: const Color(0xFF9E929E)),
            ),
          ],
        ),
      ),
      const SizedBox(height: 14),

      // Timeline
      Container(
        padding: const EdgeInsets.fromLTRB(18, 18, 18, 8),
        decoration: _cardDecoration(),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Delivery timeline',
                style: GoogleFonts.dmSans(fontSize: 15, fontWeight: FontWeight.w800, color: _ink)),
            const SizedBox(height: 16),
            for (var i = 0; i < steps.length; i++)
              _timelineStep(steps[i], isLast: i == steps.length - 1),
          ],
        ),
      ),
      const SizedBox(height: 14),

      // Detailed activity
      Container(
        decoration: _cardDecoration(),
        child: Theme(
          data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
          child: ExpansionTile(
            tilePadding: const EdgeInsets.symmetric(horizontal: 18),
            childrenPadding: const EdgeInsets.fromLTRB(18, 0, 18, 16),
            expandedCrossAxisAlignment: CrossAxisAlignment.start,
            title: Text('Detailed activity · ${history.length} recorded updates',
                style: GoogleFonts.dmSans(fontSize: 13.5, fontWeight: FontWeight.w700, color: _ink)),
            children: [
              Text(
                'Newest first. Repeated scans and delivery attempts are shown separately. All times are Philippine time.',
                style: GoogleFonts.dmSans(fontSize: 11.5, height: 1.4, color: const Color(0xFF9E929E)),
              ),
              const SizedBox(height: 10),
              if (history.isEmpty)
                Text('No courier scans recorded yet.',
                    style: GoogleFonts.dmSans(fontSize: 12.5, color: _muted))
              else
                for (final event in history)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('${event['label']}',
                            style: GoogleFonts.dmSans(
                                fontSize: 13, fontWeight: FontWeight.w700, color: _ink)),
                        Text(
                          [
                            '${event['time']}',
                            if (event['attempt'] is num && (event['attempt'] as num) > 1)
                              'Attempt ${event['attempt']}',
                          ].join(' · '),
                          style: GoogleFonts.dmSans(fontSize: 12, color: _muted),
                        ),
                        if (event['note'] != null && '${event['note']}'.isNotEmpty)
                          Text('${event['note']}',
                              style: GoogleFonts.dmSans(fontSize: 12, color: _muted)),
                      ],
                    ),
                  ),
            ],
          ),
        ),
      ),
    ];
  }

  Widget _summary(String label, String value, {bool highlight = false}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(label.toUpperCase(),
            style: GoogleFonts.dmSans(
                fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: 0.8, color: Colors.white60)),
        const SizedBox(height: 2),
        Text(value,
            style: GoogleFonts.dmSans(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: highlight ? const Color(0xFFE8C9C0) : Colors.white)),
      ],
    );
  }

  Widget _detail(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: GoogleFonts.dmSans(fontSize: 11.5, color: const Color(0xFF9E929E))),
          Text(value, style: GoogleFonts.dmSans(fontSize: 13.5, fontWeight: FontWeight.w700, color: _ink)),
        ],
      ),
    );
  }

  Widget _timelineStep(Map<String, dynamic> step, {required bool isLast}) {
    final done = step['done'] == true;
    final current = step['current'] == true;
    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SizedBox(
            width: 32,
            child: Column(
              children: [
                Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(
                    color: current ? _rose : (done ? _brand : const Color(0xFFE5E7EB)),
                    shape: BoxShape.circle,
                    border: current
                        ? Border.all(color: _rose.withValues(alpha: 0.3), width: 4)
                        : null,
                  ),
                  child: Icon(done ? Icons.check : Icons.circle,
                      size: done ? 15 : 6, color: done ? Colors.white : const Color(0xFF9CA3AF)),
                ),
                if (!isLast)
                  Expanded(
                    child: Container(
                      width: 2,
                      color: done && !current ? _rose : const Color(0xFFE5E7EB),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.only(bottom: 18, top: 3),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Wrap(
                    spacing: 6,
                    crossAxisAlignment: WrapCrossAlignment.center,
                    children: [
                      Text('${step['label']}',
                          style: GoogleFonts.dmSans(
                              fontSize: 13.5,
                              fontWeight: FontWeight.w700,
                              color: done ? _ink : const Color(0xFF6B7280))),
                      if (current)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                          decoration: BoxDecoration(
                            color: _rose.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text('Latest confirmed',
                              style: GoogleFonts.dmSans(
                                  fontSize: 10, fontWeight: FontWeight.w700, color: const Color(0xFF8C5A50))),
                        ),
                    ],
                  ),
                  if (step['detail'] != null)
                    Text('${step['detail']}', style: GoogleFonts.dmSans(fontSize: 12, color: _muted)),
                  Text('${step['time']}',
                      style: GoogleFonts.dmSans(
                          fontSize: 11, fontWeight: FontWeight.w600, color: const Color(0xFF8C5A50))),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

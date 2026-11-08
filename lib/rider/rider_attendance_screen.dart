import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

// ============================================================
// RIDER TIME IN / TIME OUT
// ============================================================
//
// The hub shows a QR code on its "Time in / time out" page that
// changes every 25 seconds. Scanning it here calls
// POST /api/rider/attendance/scan: the first scan times the rider
// in, the next one times them out. Photos or screenshots of an
// old code are refused by the server.

class RiderAttendanceScreen extends StatefulWidget {
  final String token;

  const RiderAttendanceScreen({super.key, required this.token});

  @override
  State<RiderAttendanceScreen> createState() => _RiderAttendanceScreenState();
}

class _RiderAttendanceScreenState extends State<RiderAttendanceScreen> {
  static const _codePrefix = 'CARTZY-ATT.';

  final MobileScannerController _scanner = MobileScannerController(
    formats: const [BarcodeFormat.qrCode],
  );

  bool _loading = true;
  bool _submitting = false;
  bool _onShift = false;
  DateTime? _shiftStartedAt;
  int? _currentShiftId;
  List<Map<String, dynamic>> _recent = [];

  /// Result of the last scan, shown in place of the camera until "Scan again".
  String? _resultMessage;
  bool _resultIsError = false;

  @override
  void initState() {
    super.initState();
    _loadStatus();
  }

  @override
  void dispose() {
    _scanner.dispose();
    super.dispose();
  }

  Future<void> _loadStatus() async {
    try {
      final data = await ApiService.get('/rider/attendance', token: widget.token);
      if (!mounted) return;
      final current = data['current'];
      setState(() {
        _onShift = data['on_shift'] == true;
        _shiftStartedAt = current is Map ? DateTime.tryParse('${current['time_in_at']}')?.toLocal() : null;
        _currentShiftId = current is Map ? (current['id'] as num?)?.toInt() : null;
        _recent = List<Map<String, dynamic>>.from(
          (data['recent'] as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)),
        );
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _resultMessage = 'Could not load your shift: $e';
        _resultIsError = true;
      });
    }
  }

  Future<void> _onDetect(BarcodeCapture capture) async {
    if (_submitting || _resultMessage != null) return;
    final code = capture.barcodes
        .map((b) => b.rawValue ?? '')
        .firstWhere((value) => value.startsWith(_codePrefix), orElse: () => '');
    if (code.isEmpty) return; // Not a hub code (e.g. a parcel barcode): keep looking.

    setState(() => _submitting = true);
    await _scanner.stop();

    try {
      final data = await ApiService.post('/rider/attendance/scan', {'code': code}, token: widget.token);
      if (!mounted) return;
      setState(() {
        _resultMessage = '${data['message'] ?? 'Recorded.'}';
        _resultIsError = false;
      });
      await _loadStatus();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _resultMessage = e is ApiException ? e.message : 'Could not reach the server. Check your connection and try again.';
        _resultIsError = true;
      });
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  Future<void> _scanAgain() async {
    setState(() => _resultMessage = null);
    await _scanner.start();
  }

  String _time(DateTime? time) {
    if (time == null) return '—';
    final hour = time.hour % 12 == 0 ? 12 : time.hour % 12;
    final minute = time.minute.toString().padLeft(2, '0');
    return '$hour:$minute ${time.hour < 12 ? 'AM' : 'PM'}';
  }

  String _date(DateTime time) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return '${months[time.month - 1]} ${time.day}';
  }

  String _duration(int minutes) => '${minutes ~/ 60}h ${(minutes % 60).toString().padLeft(2, '0')}m';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFBF9F8),
      appBar: AppBar(
        backgroundColor: CartzyColors.brandDark,
        foregroundColor: Colors.white,
        elevation: 0,
        title: Text('Time in / time out', style: GoogleFonts.dmSans(fontSize: 16, fontWeight: FontWeight.w700)),
        actions: [
          IconButton(
            tooltip: 'Flashlight',
            icon: const Icon(Icons.flash_on),
            onPressed: () => _scanner.toggleTorch(),
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadStatus,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _buildStatusCard(),
                  const SizedBox(height: 16),
                  _buildScannerCard(),
                  const SizedBox(height: 20),
                  _buildRecentShifts(),
                ],
              ),
            ),
    );
  }

  Widget _buildStatusCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: _onShift
              ? const [Color(0xFF2F6B49), Color(0xFF3F8A60)]
              : const [Color(0xFF4C3E56), Color(0xFF7A5458)],
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Icon(_onShift ? Icons.timer_outlined : Icons.timer_off_outlined, color: Colors.white, size: 30),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _onShift ? 'You are on shift' : 'You are off shift',
                  style: GoogleFonts.dmSans(fontSize: 16, fontWeight: FontWeight.w800, color: Colors.white),
                ),
                const SizedBox(height: 3),
                Text(
                  _onShift
                      ? 'Timed in at ${_time(_shiftStartedAt)}. Scan the hub code again to time out.'
                      : 'Scan the QR code on your hub\'s screen to time in.',
                  style: GoogleFonts.dmSans(fontSize: 12, color: Colors.white.withValues(alpha: 0.9)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildScannerCard() {
    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: SizedBox(
        height: 320,
        child: _resultMessage != null
            ? _buildResult()
            : Stack(
                fit: StackFit.expand,
                children: [
                  MobileScanner(controller: _scanner, onDetect: _onDetect),
                  IgnorePointer(
                    child: Center(
                      child: Container(
                        width: 220,
                        height: 220,
                        decoration: BoxDecoration(
                          border: Border.all(color: CartzyColors.rose400, width: 3),
                          borderRadius: BorderRadius.circular(18),
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    left: 12,
                    right: 12,
                    bottom: 12,
                    child: Text(
                      _submitting
                          ? 'Checking the code…'
                          : 'Point your camera at the QR code on the hub screen to ${_onShift ? 'time out' : 'time in'}',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                        shadows: const [Shadow(blurRadius: 6, color: Colors.black)],
                      ),
                    ),
                  ),
                  if (_submitting) const Center(child: CircularProgressIndicator(color: Colors.white)),
                ],
              ),
      ),
    );
  }

  Widget _buildResult() {
    final color = _resultIsError ? const Color(0xFFB4233F) : const Color(0xFF2F6B49);
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(_resultIsError ? Icons.error_outline : Icons.check_circle_outline, color: color, size: 56),
          const SizedBox(height: 12),
          Text(
            _resultMessage!,
            textAlign: TextAlign.center,
            style: GoogleFonts.dmSans(fontSize: 14, fontWeight: FontWeight.w700, color: color),
          ),
          const SizedBox(height: 18),
          OutlinedButton.icon(
            onPressed: _scanAgain,
            icon: const Icon(Icons.qr_code_scanner),
            label: Text(_resultIsError ? 'Try again' : 'Scan again', style: GoogleFonts.dmSans(fontWeight: FontWeight.w700)),
            style: OutlinedButton.styleFrom(
              foregroundColor: CartzyColors.brandDark,
              side: const BorderSide(color: CartzyColors.brand200),
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecentShifts() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Recent shifts', style: GoogleFonts.dmSans(fontSize: 15, fontWeight: FontWeight.w800, color: CartzyColors.brand900)),
        const SizedBox(height: 8),
        if (_recent.isEmpty)
          Text('No shifts yet.', style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF8A7F91)))
        else
          ..._recent.map((shift) {
            final timeIn = DateTime.tryParse('${shift['time_in_at']}')?.toLocal();
            final timeOut = shift['time_out_at'] == null ? null : DateTime.tryParse('${shift['time_out_at']}')?.toLocal();
            final isCurrent = (shift['id'] as num?)?.toInt() == _currentShiftId;
            return Container(
              margin: const EdgeInsets.only(bottom: 8),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                border: Border.all(color: const Color(0xFFEDE5E0)),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      '${timeIn == null ? '' : _date(timeIn)} · ${_time(timeIn)} – ${timeOut != null ? _time(timeOut) : (isCurrent ? 'on shift' : 'no time out')}',
                      style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w600, color: const Color(0xFF352B3D)),
                    ),
                  ),
                  Text(
                    _duration((shift['worked_minutes'] as num?)?.toInt() ?? 0),
                    style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w800, color: CartzyColors.brandDark),
                  ),
                ],
              ),
            );
          }),
      ],
    );
  }
}

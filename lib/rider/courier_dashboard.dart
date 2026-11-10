import 'package:cartzy/rider/rider_home_address.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/buyer/notifications_screen.dart';
import 'package:cartzy/rider/rider_attendance_screen.dart';
import 'package:cartzy/rider/rider_parcel_scan.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/app_update_service.dart';
import 'package:cartzy/services/courier_service.dart';
import 'package:cartzy/services/notification_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/patch_notes_dialog.dart';

const _ink = Color(0xFF261E30);
const _muted = Color(0xFF8A7780);
const _line = Color(0xFFEFE8EA);
const _green = Color(0xFF2E7D32);

/// Dashboard for First Mile couriers (car, van or truck): they collect parcels
/// from sellers and bring them to the hub.
///
/// Everything shown comes from the server. When there is nothing yet, the
/// screen says so instead of showing sample numbers.
class CourierDashboard extends StatefulWidget {
  final String token;
  final VoidCallback onLogout;

  /// Replaces the server call in tests.
  final Future<CourierDashboardData> Function()? loadDashboard;

  const CourierDashboard({
    super.key,
    required this.token,
    required this.onLogout,
    this.loadDashboard,
  });

  @override
  State<CourierDashboard> createState() => _CourierDashboardState();
}

class _CourierDashboardState extends State<CourierDashboard> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  CourierDashboardData? _data;
  String? _error;
  bool _loading = true;
  int _tab = 0;

  @override
  void initState() {
    super.initState();
    _load();
    WidgetsBinding.instance.addPostFrameCallback((_) => NotificationService.fetchUnreadCount());
  }

  Future<void> _load() async {
    setState(() {
      _loading = _data == null;
      _error = null;
    });
    try {
      final data = await (widget.loadDashboard ?? CourierService.fetchDashboard)();
      if (mounted) setState(() => _data = data);
    } on ApiException catch (e) {
      if (mounted) setState(() => _error = e.message);
    } catch (_) {
      if (mounted) setState(() => _error = "Couldn't reach the server. Check your connection and try again.");
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  // ── Actions ─────────────────────────────────────────────────
  Future<void> _scanParcel() async {
    await RiderParcelScan.scanAndReport(context);
    if (mounted) _load();
  }

  Future<void> _openAttendance() async {
    await Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => RiderAttendanceScreen(token: widget.token)),
    );
    if (mounted) _load();
  }

  void _openNotifications() {
    Navigator.of(context)
        .push(MaterialPageRoute(
          builder: (routeContext) => NotificationsScreen(
            onBack: () => Navigator.of(routeContext).pop(),
            openLinkedScreens: false,
          ),
        ))
        .then((_) => NotificationService.fetchUnreadCount());
  }

  void _showPatchNotes() {
    final (version, notes) = PatchNotesDialog.latestRiderNotes(AppUpdateService.currentAppVersion);
    PatchNotesDialog.showPatchNotes(context, version: version, highlights: notes);
  }

  void _closeMenuThen(VoidCallback action) {
    if (_scaffoldKey.currentState?.isDrawerOpen ?? false) _scaffoldKey.currentState!.closeDrawer();
    action();
  }

  // ── Layout ──────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: CartzyColors.background,
      drawer: _buildMenu(),
      bottomNavigationBar: _buildBottomNav(),
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            _buildHeader(),
            Expanded(child: _buildBody()),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return SizedBox(
      height: 60,
      child: Row(
        children: [
          IconButton(
            tooltip: 'Menu',
            icon: const Icon(Icons.menu_rounded, color: _ink, size: 26),
            onPressed: () => _scaffoldKey.currentState?.openDrawer(),
          ),
          Expanded(
            child: Text(
              'cartzy',
              textAlign: TextAlign.center,
              style: GoogleFonts.poppins(fontSize: 24, fontWeight: FontWeight.w800, letterSpacing: -0.5, color: _ink),
            ),
          ),
          ValueListenableBuilder<int>(
            valueListenable: NotificationService.unreadCountNotifier,
            builder: (context, unread, _) => IconButton(
              tooltip: unread > 0 ? 'Notifications, $unread unread' : 'Notifications',
              onPressed: _openNotifications,
              icon: Badge(
                isLabelVisible: unread > 0,
                label: Text(unread > 99 ? '99+' : '$unread'),
                backgroundColor: const Color(0xFFD1495B),
                child: const Icon(Icons.notifications_none_rounded, color: _ink, size: 26),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return const Center(child: CircularProgressIndicator(color: CartzyColors.burgundy));
    }
    final data = _data;
    if (data == null) {
      return _Message(
        icon: Icons.cloud_off_rounded,
        title: "Can't load your dashboard",
        body: _error ?? 'Something went wrong.',
        action: TextButton.icon(onPressed: _load, icon: const Icon(Icons.refresh_rounded), label: const Text('Try again')),
      );
    }

    final pages = [_homeTab, _pickupsTab, _historyTab, _profileTab];
    return RefreshIndicator(
      color: CartzyColors.burgundy,
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
        children: [
          if (_error != null) _ErrorBanner(message: _error!, onRetry: _load),
          ...pages[_tab](data),
        ],
      ),
    );
  }

  // ── Home ────────────────────────────────────────────────────
  List<Widget> _homeTab(CourierDashboardData d) {
    final c = d.courier;
    final vehicle = [c.vehicleLabel, c.plateNo].whereType<String>().join(' · ');

    return [
      // Who and what
      Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: const LinearGradient(colors: [Color(0xFF4C4054), Color(0xFF7A5560)], begin: Alignment.topLeft, end: Alignment.bottomRight),
          borderRadius: BorderRadius.circular(22),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              c.firstName.isEmpty ? 'Welcome' : 'Hi, ${c.firstName}',
              style: GoogleFonts.playfairDisplay(fontSize: 26, fontWeight: FontWeight.w700, color: Colors.white),
            ),
            const SizedBox(height: 10),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                if (c.courierTypeLabel != null) _Chip('${c.courierTypeLabel} courier', Icons.local_shipping_outlined),
                if (vehicle.isNotEmpty) _Chip(vehicle, Icons.directions_car_outlined),
                if (c.hub != null) _Chip(c.hub!, Icons.warehouse_outlined),
              ],
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: _scanParcel,
                icon: const Icon(Icons.qr_code_scanner_rounded, size: 20),
                label: Text('Scan Parcel', style: GoogleFonts.dmSans(fontWeight: FontWeight.w700, fontSize: 15)),
                style: FilledButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: _ink,
                  minimumSize: const Size.fromHeight(50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
            ),
          ],
        ),
      ),
      const SizedBox(height: 14),

      // Shift
      _Card(
        onTap: _openAttendance,
        child: Row(
          children: [
            CircleAvatar(
              radius: 22,
              backgroundColor: (d.onShift ? _green : _muted).withValues(alpha: 0.12),
              child: Icon(d.onShift ? Icons.timer_outlined : Icons.timer_off_outlined, color: d.onShift ? _green : _muted),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(d.onShift ? 'On shift' : 'Not on shift', style: _title),
                  const SizedBox(height: 2),
                  Text(
                    d.onShift
                        ? (d.shiftSince != null ? 'Timed in at ${courierTime(d.shiftSince!)}' : 'Timed in')
                        : 'Scan the QR code at your hub to time in',
                    style: _sub,
                  ),
                ],
              ),
            ),
            Text(d.onShift ? 'Time out' : 'Time in', style: _link),
          ],
        ),
      ),
      const SizedBox(height: 14),

      // Today
      IntrinsicHeight(
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(child: _Stat(label: 'To pick up', value: d.pickupsToDo, sub: d.parcelsToCollect == 1 ? '1 parcel' : '${d.parcelsToCollect} parcels')),
            const SizedBox(width: 10),
            Expanded(child: _Stat(label: 'In vehicle', value: d.inVehicleCount, sub: 'for the hub')),
            const SizedBox(width: 10),
            Expanded(child: _Stat(label: 'At hub today', value: d.handedToHubToday, sub: '${d.collectedToday} collected')),
          ],
        ),
      ),
      const SizedBox(height: 22),

      _SectionTitle('Next pickups', actionLabel: d.pickups.length > 2 ? 'See all' : null, onAction: () => setState(() => _tab = 1)),
      if (d.pickups.isEmpty)
        const _Empty(icon: Icons.inventory_2_outlined, text: 'No pickups assigned to you. When your hub assigns one, it shows up here.')
      else
        ...d.pickups.take(2).map((p) => _PickupCard(pickup: p)),
      const SizedBox(height: 18),

      const _SectionTitle('In your vehicle'),
      if (d.inVehicle.isEmpty)
        const _Empty(icon: Icons.local_shipping_outlined, text: 'Nothing in your vehicle. Parcels you accept at a seller show up here until the hub receives them.')
      else
        ...d.inVehicle.map((p) => _ParcelRow(parcel: p, when: p.at != null ? 'Picked up ${courierDay(p.at!)}, ${courierTime(p.at!)}' : null)),
    ];
  }

  // ── Pickups ─────────────────────────────────────────────────
  List<Widget> _pickupsTab(CourierDashboardData d) => [
        const _PageTitle('Pickups', 'Sellers waiting for you, earliest first.'),
        if (d.pickups.isEmpty)
          const _Empty(icon: Icons.inventory_2_outlined, text: 'No pickups assigned to you right now.')
        else
          ...d.pickups.map((p) => _PickupCard(pickup: p, detailed: true)),
      ];

  // ── History ─────────────────────────────────────────────────
  List<Widget> _historyTab(CourierDashboardData d) => [
        const _PageTitle('History', 'Parcels you brought to the hub, newest first.'),
        if (d.handedToHub.isEmpty)
          const _Empty(icon: Icons.history_rounded, text: "You haven't brought any parcels to the hub yet.")
        else
          ...d.handedToHub.map((p) => _ParcelRow(parcel: p, when: p.at != null ? 'At the hub ${courierDay(p.at!)}, ${courierTime(p.at!)}' : null)),
      ];

  // ── Profile ─────────────────────────────────────────────────
  List<Widget> _profileTab(CourierDashboardData d) {
    final c = d.courier;
    return [
      const _PageTitle('Profile', 'Your courier details on file with your hub.'),
      _Card(
        child: Column(
          children: [
            _InfoRow('Name', c.name),
            _InfoRow('Email', c.email),
            _InfoRow('Phone', c.phone),
            _InfoRow('Courier type', c.courierTypeLabel),
            _InfoRow('Vehicle', c.vehicleLabel),
            _InfoRow('Plate number', c.plateNo),
            _InfoRow('Hub', c.hub),
            _InfoRow('Area', c.area),
            _InfoRow('Status', c.status == null ? null : '${c.status![0].toUpperCase()}${c.status!.substring(1)}${c.isActive ? '' : ' · inactive'}'),
            _InfoRow('Member since', c.memberSince),
          ],
        ),
      ),
      const SizedBox(height: 8),
      Text('To change these details, contact your hub.', style: _sub),
      const SizedBox(height: 16),
      _Card(
        child: Row(
          children: [
            const Icon(Icons.home_outlined, color: CartzyColors.brandDeep),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Home address', style: _title),
                  const SizedBox(height: 2),
                  Text(
                    c.homeAddress?.label ?? 'Not added yet. Add it so your hub can send you pickups near home.',
                    style: _sub,
                  ),
                ],
              ),
            ),
            TextButton(
              onPressed: () async {
                final saved = await showHomeAddressSheet(context, initial: c.homeAddress);
                if (saved != null && mounted) _load();
              },
              child: Text(c.homeAddress == null ? 'Add' : 'Change', style: GoogleFonts.dmSans(fontWeight: FontWeight.w700)),
            ),
          ],
        ),
      ),
      const SizedBox(height: 12),
      _Card(
        onTap: _showPatchNotes,
        child: Row(
          children: [
            const Icon(Icons.auto_awesome_outlined, color: CartzyColors.brandDeep),
            const SizedBox(width: 12),
            Expanded(child: Text("What's new for riders & couriers", style: _title)),
            const Icon(Icons.chevron_right_rounded, color: _muted),
          ],
        ),
      ),
      const SizedBox(height: 12),
      OutlinedButton.icon(
        onPressed: widget.onLogout,
        icon: const Icon(Icons.logout_rounded, size: 18),
        label: Text('Log out', style: GoogleFonts.dmSans(fontWeight: FontWeight.w700)),
        style: OutlinedButton.styleFrom(
          foregroundColor: const Color(0xFFD32F2F),
          side: const BorderSide(color: Color(0xFFF5C6C6)),
          minimumSize: const Size.fromHeight(48),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        ),
      ),
    ];
  }

  // ── Menu and nav ────────────────────────────────────────────
  Widget _buildMenu() {
    final name = _data?.courier.name;
    Widget item(IconData icon, String label, VoidCallback onTap, {Color color = _ink}) => ListTile(
          leading: Icon(icon, color: color == _ink ? CartzyColors.brandDeep : color),
          title: Text(label, style: GoogleFonts.dmSans(fontWeight: FontWeight.w600, color: color)),
          onTap: () => _closeMenuThen(onTap),
        );

    return Drawer(
      backgroundColor: Colors.white,
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 18, 20, 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('cartzy', style: GoogleFonts.poppins(fontSize: 22, fontWeight: FontWeight.w800, color: _ink)),
                  if (name != null) Text(name, style: _sub),
                ],
              ),
            ),
            const Divider(height: 1, color: _line),
            item(Icons.qr_code_scanner_rounded, 'Scan parcel', _scanParcel),
            item(Icons.badge_outlined, 'Time in / time out', _openAttendance),
            item(Icons.notifications_none_rounded, 'Notifications', _openNotifications),
            item(Icons.auto_awesome_outlined, "What's new", _showPatchNotes),
            const Spacer(),
            const Divider(height: 1, color: _line),
            item(Icons.logout_rounded, 'Log out', widget.onLogout, color: const Color(0xFFD32F2F)),
          ],
        ),
      ),
    );
  }

  Widget _buildBottomNav() {
    const leftItems = [
      (Icons.home_rounded, 'Home', 0),
      (Icons.inventory_2_outlined, 'Pickups', 1),
    ];
    const rightItems = [
      (Icons.history_rounded, 'History', 2),
      (Icons.person_outline_rounded, 'Profile', 3),
    ];

    Widget navItem(IconData icon, String label, int index) {
      final selected = _tab == index;
      return Expanded(
        child: Semantics(
          button: true,
          selected: selected,
          label: label,
          child: InkWell(
            borderRadius: BorderRadius.circular(8),
            onTap: () => setState(() => _tab = index),
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(icon, size: 22, color: selected ? CartzyColors.burgundy : CartzyColors.gray),
                  const SizedBox(height: 3),
                  Text(
                    label,
                    style: GoogleFonts.dmSans(
                      fontSize: 11,
                      fontWeight: selected ? FontWeight.w600 : FontWeight.w400,
                      color: selected ? CartzyColors.burgundy : CartzyColors.gray,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 2),
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    height: 2,
                    width: selected ? 16 : 0,
                    color: CartzyColors.burgundy,
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    return SafeArea(
      top: false,
      child: Container(
        margin: const EdgeInsets.fromLTRB(16, 4, 16, 12),
        height: 64,
        child: Stack(
          clipBehavior: Clip.none,
          alignment: Alignment.bottomCenter,
          children: [
            // Nav bar background pill
            Container(
              height: 56,
              decoration: const ShapeDecoration(
                color: CartzyColors.surface,
                shape: StadiumBorder(side: BorderSide(color: CartzyColors.border)),
                shadows: [
                  BoxShadow(color: Color(0x148B3A3A), blurRadius: 16, offset: Offset(0, 4)),
                ],
              ),
              padding: const EdgeInsets.symmetric(horizontal: 8),
              child: Row(
                children: [
                  for (final it in leftItems) navItem(it.$1, it.$2, it.$3),
                  // Centered gap for GCash-style floating scanner button
                  const SizedBox(width: 58),
                  for (final it in rightItems) navItem(it.$1, it.$2, it.$3),
                ],
              ),
            ),

            // GCash-style floating elevated circular scanner button
            Positioned(
              top: 0,
              child: Semantics(
                button: true,
                label: 'Scan parcel waybill barcode or QR',
                child: GestureDetector(
                  onTap: () {
                    HapticFeedback.lightImpact();
                    _scanParcel();
                  },
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 48,
                        height: 48,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          gradient: const LinearGradient(
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                            colors: [
                              Color(0xFF8B3A3A),
                              Color(0xFF6B2525),
                            ],
                          ),
                          border: Border.all(color: Colors.white, width: 2.5),
                          boxShadow: const [
                            BoxShadow(
                              color: Color(0x408B3A3A),
                              blurRadius: 10,
                              offset: Offset(0, 4),
                            ),
                          ],
                        ),
                        child: const Center(
                          child: Icon(
                            Icons.qr_code_scanner_rounded,
                            color: Colors.white,
                            size: 24,
                          ),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Scan',
                        style: GoogleFonts.dmSans(
                          fontSize: 10,
                          fontWeight: FontWeight.w700,
                          color: CartzyColors.burgundy,
                          letterSpacing: 0.2,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Shared pieces ──────────────────────────────────────────────

TextStyle get _title => GoogleFonts.dmSans(fontSize: 14.5, fontWeight: FontWeight.w700, color: _ink);
TextStyle get _sub => GoogleFonts.dmSans(fontSize: 12.5, color: _muted);
TextStyle get _link => GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w700, color: CartzyColors.brandDeep);

class _Card extends StatelessWidget {
  final Widget child;
  final VoidCallback? onTap;

  const _Card({required this.child, this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: const BorderSide(color: _line)),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Padding(padding: const EdgeInsets.all(16), child: child),
      ),
    );
  }
}

class _Chip extends StatelessWidget {
  final String text;
  final IconData icon;

  const _Chip(this.text, this.icon);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.16), borderRadius: BorderRadius.circular(20)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: Colors.white),
          const SizedBox(width: 5),
          Flexible(child: Text(text, overflow: TextOverflow.ellipsis, style: GoogleFonts.dmSans(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.white))),
        ],
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  final String label;
  final int value;
  final String sub;

  const _Stat({required this.label, required this.value, required this.sub});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(12, 12, 10, 12),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: _line)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, maxLines: 2, style: GoogleFonts.dmSans(fontSize: 11.5, fontWeight: FontWeight.w600, color: _muted)),
          const SizedBox(height: 6),
          Text('$value', style: GoogleFonts.playfairDisplay(fontSize: 26, fontWeight: FontWeight.w700, color: _ink)),
          Text(sub, maxLines: 1, overflow: TextOverflow.ellipsis, style: GoogleFonts.dmSans(fontSize: 11, color: _muted)),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  final String text;
  final String? actionLabel;
  final VoidCallback? onAction;

  const _SectionTitle(this.text, {this.actionLabel, this.onAction});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        children: [
          Expanded(child: Text(text, style: GoogleFonts.playfairDisplay(fontSize: 19, fontWeight: FontWeight.w700, color: _ink))),
          if (actionLabel != null) TextButton(onPressed: onAction, child: Text(actionLabel!, style: _link)),
        ],
      ),
    );
  }
}

class _PageTitle extends StatelessWidget {
  final String title;
  final String subtitle;

  const _PageTitle(this.title, this.subtitle);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(2, 6, 2, 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: GoogleFonts.playfairDisplay(fontSize: 26, fontWeight: FontWeight.w700, color: _ink)),
          const SizedBox(height: 2),
          Text(subtitle, style: _sub),
        ],
      ),
    );
  }
}

class _PickupCard extends StatelessWidget {
  final CourierPickup pickup;
  final bool detailed;

  const _PickupCard({required this.pickup, this.detailed = false});

  @override
  Widget build(BuildContext context) {
    final p = pickup;
    final when = [if (p.pickupDate != null) courierDay(p.pickupDate!), p.pickupTime].whereType<String>().join(' · ');
    final parcels = p.parcelsWaiting == 1 ? '1 parcel waiting' : '${p.parcelsWaiting} parcels waiting';

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: _Card(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(child: Text(p.sellerName ?? 'Seller', style: _title)),
                if (when.isNotEmpty)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                    decoration: BoxDecoration(color: CartzyColors.brand100, borderRadius: BorderRadius.circular(20)),
                    child: Text(when, style: GoogleFonts.dmSans(fontSize: 11.5, fontWeight: FontWeight.w700, color: CartzyColors.brandDark)),
                  ),
              ],
            ),
            if (p.address != null) ...[
              const SizedBox(height: 8),
              _IconLine(Icons.place_outlined, p.address!),
            ],
            const SizedBox(height: 6),
            _IconLine(Icons.inventory_2_outlined, [parcels, if (p.weightKg != null && p.weightKg! > 0) '${_kg(p.weightKg!)} kg'].join(' · ')),
            if (detailed && p.sellerPhone != null) ...[
              const SizedBox(height: 6),
              _IconLine(Icons.phone_outlined, p.sellerPhone!, selectable: true),
            ],
            if (detailed && p.notes != null) ...[
              const SizedBox(height: 6),
              _IconLine(Icons.sticky_note_2_outlined, p.notes!),
            ],
          ],
        ),
      ),
    );
  }

  static String _kg(double kg) => kg == kg.roundToDouble() ? kg.toStringAsFixed(0) : kg.toStringAsFixed(1);
}

class _IconLine extends StatelessWidget {
  final IconData icon;
  final String text;
  final bool selectable;

  const _IconLine(this.icon, this.text, {this.selectable = false});

  @override
  Widget build(BuildContext context) {
    final style = GoogleFonts.dmSans(fontSize: 13, height: 1.35, color: const Color(0xFF4F4552));
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 16, color: _muted),
        const SizedBox(width: 8),
        Expanded(child: selectable ? SelectableText(text, style: style) : Text(text, style: style)),
      ],
    );
  }
}

class _ParcelRow extends StatelessWidget {
  final CourierParcel parcel;
  final String? when;

  const _ParcelRow({required this.parcel, this.when});

  @override
  Widget build(BuildContext context) {
    final p = parcel;
    final details = [p.sellerName, if (p.itemsCount > 0) p.itemsCount == 1 ? '1 item' : '${p.itemsCount} items'].whereType<String>().join(' · ');
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: _Card(
        child: Row(
          children: [
            const CircleAvatar(
              radius: 20,
              backgroundColor: CartzyColors.brand100,
              child: Icon(Icons.inventory_2_outlined, size: 20, color: CartzyColors.brandDeep),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(p.trackingCode, style: _title),
                  if (details.isNotEmpty) Text(details, style: _sub),
                  if (when != null) Text(when!, style: _sub),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final String label;
  final String? value;

  const _InfoRow(this.label, this.value);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 7),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 110, child: Text(label, style: _sub)),
          Expanded(
            child: Text(
              value ?? 'Not set',
              style: GoogleFonts.dmSans(
                fontSize: 13.5,
                fontWeight: value == null ? FontWeight.w400 : FontWeight.w600,
                fontStyle: value == null ? FontStyle.italic : FontStyle.normal,
                color: value == null ? _muted : _ink,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _Empty extends StatelessWidget {
  final IconData icon;
  final String text;

  const _Empty({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 22),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.6),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _line),
      ),
      child: Column(
        children: [
          Icon(icon, size: 30, color: CartzyColors.brand400),
          const SizedBox(height: 8),
          Text(text, textAlign: TextAlign.center, style: GoogleFonts.dmSans(fontSize: 13, height: 1.4, color: _muted)),
        ],
      ),
    );
  }
}

class _Message extends StatelessWidget {
  final IconData icon;
  final String title;
  final String body;
  final Widget action;

  const _Message({required this.icon, required this.title, required this.body, required this.action});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 44, color: CartzyColors.brand400),
            const SizedBox(height: 12),
            Text(title, textAlign: TextAlign.center, style: GoogleFonts.playfairDisplay(fontSize: 20, fontWeight: FontWeight.w700, color: _ink)),
            const SizedBox(height: 6),
            Text(body, textAlign: TextAlign.center, style: _sub),
            const SizedBox(height: 12),
            action,
          ],
        ),
      ),
    );
  }
}

class _ErrorBanner extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;

  const _ErrorBanner({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.fromLTRB(14, 6, 6, 6),
      decoration: BoxDecoration(color: const Color(0xFFFDECEC), borderRadius: BorderRadius.circular(12)),
      child: Row(
        children: [
          Expanded(child: Text("Couldn't refresh: $message", style: GoogleFonts.dmSans(fontSize: 12.5, color: const Color(0xFFB3261E)))),
          TextButton(onPressed: onRetry, child: const Text('Retry')),
        ],
      ),
    );
  }
}

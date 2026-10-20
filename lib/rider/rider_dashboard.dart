import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/barcode_scanner_page.dart';
import 'package:cartzy/widgets/cartzy_logo.dart';
import 'package:cartzy/services/mock_data.dart';
import 'rider_pickup_dashboard.dart';
import 'rider_delivery_dashboard.dart';
import 'rider_delivery_history_screen.dart';
import 'rider_earnings_dashboard.dart';
import 'rider_chat_list_screen.dart';
import 'rider_account_screen.dart';

// ============================================================
// RIDER COMMAND CENTER — reference design from screenshot
// ============================================================

class RiderDashboard extends StatefulWidget {
  final String token;
  final int userId;
  final String userName;
  final VoidCallback onLogout;

  const RiderDashboard({
    super.key,
    required this.token,
    required this.userId,
    required this.userName,
    required this.onLogout,
  });

  @override
  State<RiderDashboard> createState() => _RiderDashboardState();
}

class _RiderDashboardState extends State<RiderDashboard> {
  bool _isOnline = true;
  bool _showWelcomeBanner = true;
  String _selectedNav = 'Dashboard';

  // Sample pickup requests matching reference
  final List<Map<String, dynamic>> _pickupRequests = [
    {
      'id': 'pk-01',
      'mall': 'Tech Zone Official Mall',
      'parcels': '6 PARCELS (8.5 kg)',
      'time': '2:00 PM - 4:00 PM',
      'address': '123 Tech Hub Bldg., Brgy. San Antonio, Pasig City',
      'note':
          'Note: High-value electronics. Sealed in bubble wrap security bags.',
      'accepted': false,
    },
    {
      'id': 'pk-02',
      'mall': 'Tech Zone Official Mall',
      'parcels': '4 PARCELS (3.2 kg)',
      'time': '4:30 PM - 5:30 PM',
      'address': '45 Commerce Center, Alabang, Muntinlupa City',
      'note':
          'Note: Contact warehouse manager Kuya Ben at 0917-888-9900.',
      'accepted': false,
    },
  ];

  String get _displayName =>
      widget.userName.isNotEmpty && widget.userName != 'Rider'
          ? widget.userName
          : 'Tiffany Leonardo';

  String get _initials {
    final parts = _displayName.trim().split(RegExp(r'\s+'));
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    } else if (parts.isNotEmpty && parts[0].isNotEmpty) {
      return parts[0].substring(0, parts[0].length >= 2 ? 2 : 1).toUpperCase();
    }
    return 'TL';
  }

  void _push(Widget screen) {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (context) => screen),
    );
  }

  void _openBarcodeScanner(String title) {
    _push(BarcodeScannerPage(title: title));
  }

  void _acceptPickup(int index) {
    setState(() {
      _pickupRequests[index]['accepted'] = true;
    });
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          'Accepted pickup from ${_pickupRequests[index]['mall']}!',
          style: GoogleFonts.dmSans(color: Colors.white),
        ),
        backgroundColor: const Color(0xFF2E7D32),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  // ── Top Bar ─────────────────────────────────────────────────
  Widget _buildTopBar(bool isMobile) {
    return Container(
      height: 64,
      padding: EdgeInsets.symmetric(horizontal: isMobile ? 8 : 20),
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(
          bottom: BorderSide(color: Color(0xFFEFE8EA), width: 1.1),
        ),
      ),
      child: Row(
        children: [
          if (isMobile)
            Builder(
              builder: (ctx) => IconButton(
                icon: const Icon(Icons.menu, color: Color(0xFF261E30)),
                onPressed: () => Scaffold.of(ctx).openDrawer(),
              ),
            ),

          // Cartzy Courier Logo
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const CartzyLogo(height: 30),
              const SizedBox(width: 6),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF4C4054), Color(0xFF6E4A56)],
                    begin: Alignment.centerLeft,
                    end: Alignment.centerRight,
                  ),
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF4C4054).withValues(alpha: 0.3),
                      blurRadius: 4,
                      offset: const Offset(0, 1.5),
                    ),
                  ],
                ),
                child: Text(
                  'COURIER',
                  style: GoogleFonts.dmSans(
                    fontSize: 9.5,
                    fontWeight: FontWeight.w800,
                    color: Colors.white,
                    letterSpacing: 0.9,
                  ),
                ),
              ),
            ],
          ),

          // Title & Sector (desktop only)
          if (!isMobile) ...[
            const SizedBox(width: 20),
            const SizedBox(
              height: 24,
              child: VerticalDivider(
                color: Color(0xFFE5DCDD),
                thickness: 1,
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Row(
                children: [
                  Flexible(
                    child: Text(
                      'Rider Command Center',
                      style: GoogleFonts.playfairDisplay(
                        fontSize: 16.5,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF261E30),
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Flexible(
                    child: Text(
                      '|  Sector: Metro South Sector',
                      style: GoogleFonts.dmSans(
                        fontSize: 12.5,
                        color: const Color(0xFF8A7780),
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
          ],

          // Spacer on mobile to push buttons to the right
          if (isMobile) const Spacer(),

          // Scan Waybill Barcode button
          InkWell(
            onTap: () => _openBarcodeScanner('Scan Waybill Barcode'),
            borderRadius: BorderRadius.circular(20),
            child: Container(
              padding: EdgeInsets.symmetric(
                horizontal: isMobile ? 10 : 14,
                vertical: 8,
              ),
              decoration: BoxDecoration(
                color: const Color(0xFF554B62),
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF554B62).withValues(alpha: 0.2),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.qr_code_scanner,
                    size: 14,
                    color: Colors.white,
                  ),
                  if (!isMobile) ...[
                    const SizedBox(width: 7),
                    Text(
                      'Scan Waybill Barcode',
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),

          // Online / Offline status badge (desktop only)
          if (!isMobile) ...[
            const SizedBox(width: 12),
            InkWell(
              onTap: () {
                setState(() => _isOnline = !_isOnline);
              },
              borderRadius: BorderRadius.circular(20),
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                decoration: BoxDecoration(
                  color: _isOnline
                      ? const Color(0xFFE8F5E9)
                      : const Color(0xFFF3ECEE),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: _isOnline
                        ? const Color(0xFFC8E6C9)
                        : const Color(0xFFE2D4D6),
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 7,
                      height: 7,
                      decoration: BoxDecoration(
                        color: _isOnline
                            ? const Color(0xFF2E7D32)
                            : const Color(0xFF9E8A92),
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 7),
                    Text(
                      _isOnline ? 'Online (Accepting Jobs)' : 'Offline',
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: _isOnline
                            ? const Color(0xFF2E7D32)
                            : const Color(0xFF756A7C),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  // ── Sidebar ─────────────────────────────────────────────────
  Widget _buildSidebar() {
    return Container(
      width: 235,
      decoration: const BoxDecoration(
        color: Color(0xFFFAF7F8),
        border: Border(
          right: BorderSide(color: Color(0xFFEFE6E8), width: 1.1),
        ),
      ),
      child: Column(
        children: [
          // Rider Profile Mini Card
          Container(
            margin: const EdgeInsets.fromLTRB(14, 16, 14, 10),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFEDE2E4)),
            ),
            child: Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: const Color(0xFF554B62),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Center(
                    child: Text(
                      _initials,
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              _displayName,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.dmSans(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF261E30),
                              ),
                            ),
                          ),
                          const SizedBox(width: 4),
                          const Text(
                            '★ 5.0',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFFF59E0B),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'car - 123ABC',
                        style: GoogleFonts.dmSans(
                          fontSize: 11,
                          color: const Color(0xFF8A7780),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Working Status Row
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 8),
            child: Row(
              children: [
                Text(
                  'WORKING STATUS:',
                  style: GoogleFonts.dmSans(
                    fontSize: 9.5,
                    fontWeight: FontWeight.w700,
                    letterSpacing: 0.8,
                    color: const Color(0xFF8A7680),
                  ),
                ),
                const Spacer(),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE8F5E9),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 5,
                        height: 5,
                        decoration: const BoxDecoration(
                          color: Color(0xFF2E7D32),
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'Online',
                        style: GoogleFonts.dmSans(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF2E7D32),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 10),

          // Navigation Links
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 10),
              children: [
                _sidebarItem(
                  icon: Icons.dashboard_outlined,
                  title: 'Dashboard',
                  isSelected: _selectedNav == 'Dashboard',
                  onTap: () => setState(() => _selectedNav = 'Dashboard'),
                ),
                _sidebarItem(
                  icon: Icons.inventory_2_outlined,
                  title: 'Items for Pickup',
                  isSelected: _selectedNav == 'Items for Pickup',
                  badge: '${MockData.availablePickups.length}',
                  badgeColor: const Color(0xFFD97850),
                  onTap: () {
                    setState(() => _selectedNav = 'Items for Pickup');
                    _push(
                      RiderPickupDashboard(
                        token: widget.token,
                        onBack: () => Navigator.of(context).pop(),
                      ),
                    );
                  },
                ),
                _sidebarItem(
                  icon: Icons.local_shipping_outlined,
                  title: 'Items for Delivery',
                  isSelected: _selectedNav == 'Items for Delivery',
                  onTap: () {
                    setState(() => _selectedNav = 'Items for Delivery');
                    _push(
                      RiderDeliveryDashboard(
                        token: widget.token,
                        onBack: () => Navigator.of(context).pop(),
                      ),
                    );
                  },
                ),
                _sidebarItem(
                  icon: Icons.payments_outlined,
                  title: 'Rider Earnings & Commissions',
                  isSelected: _selectedNav == 'Rider Earnings & Commissions',
                  onTap: () {
                    setState(() => _selectedNav = 'Rider Earnings & Commissions');
                    _push(
                      RiderEarningsScreen(
                        token: widget.token,
                        onBack: () => Navigator.of(context).pop(),
                      ),
                    );
                  },
                ),
                _sidebarItem(
                  icon: Icons.history_outlined,
                  title: 'Delivery History',
                  isSelected: _selectedNav == 'Delivery History',
                  onTap: () {
                    setState(() => _selectedNav = 'Delivery History');
                    _push(
                      RiderDeliveryHistoryScreen(
                        token: widget.token,
                        onBack: () => Navigator.of(context).pop(),
                        onLogout: widget.onLogout,
                      ),
                    );
                  },
                ),
                _sidebarItem(
                  icon: Icons.chat_bubble_outline,
                  title: 'Chat & Messages',
                  isSelected: _selectedNav == 'Chat & Messages',
                  onTap: () {
                    setState(() => _selectedNav = 'Chat & Messages');
                    _push(
                      RiderChatListScreen(
                        token: widget.token,
                        myUserId: widget.userId,
                        onBack: () => Navigator.of(context).pop(),
                        onLogout: widget.onLogout,
                      ),
                    );
                  },
                ),
                _sidebarItem(
                  icon: Icons.person_outline,
                  title: 'Account Management',
                  isSelected: _selectedNav == 'Account Management',
                  onTap: () {
                    setState(() => _selectedNav = 'Account Management');
                    _push(
                      RiderAccountScreen(
                        userId: widget.userId,
                        onBack: () => Navigator.of(context).pop(),
                        onLogout: widget.onLogout,
                      ),
                    );
                  },
                ),
              ],
            ),
          ),

          // Logout Button
          Padding(
            padding: const EdgeInsets.all(14),
            child: InkWell(
              onTap: widget.onLogout,
              borderRadius: BorderRadius.circular(10),
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFE5D5D8)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.logout,
                      size: 15,
                      color: Color(0xFF865561),
                    ),
                    const SizedBox(width: 7),
                    Text(
                      'Log Out',
                      style: GoogleFonts.dmSans(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFF865561),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _sidebarItem({
    required IconData icon,
    required String title,
    bool isSelected = false,
    String? badge,
    Color? badgeColor,
    required VoidCallback onTap,
  }) {
    var isHovered = false;
    return StatefulBuilder(
      builder: (context, setHoverState) {
        final highlighted = isSelected || isHovered;
        return MouseRegion(
          onEnter: (_) => setHoverState(() => isHovered = true),
          onExit: (_) => setHoverState(() => isHovered = false),
          child: Container(
            margin: const EdgeInsets.only(bottom: 4),
            decoration: BoxDecoration(
              gradient: highlighted
                  ? const LinearGradient(
                      colors: [Color(0xFF5A4A69), Color(0xFF936762)],
                    )
                  : null,
              color: highlighted ? null : Colors.transparent,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Material(
              color: Colors.transparent,
              borderRadius: BorderRadius.circular(10),
              child: InkWell(
                onTap: onTap,
                borderRadius: BorderRadius.circular(10),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  child: Row(
                    children: [
                      Icon(
                        icon,
                        size: 18,
                        color: highlighted ? Colors.white : const Color(0xFF756A7C),
                      ),
                      const SizedBox(width: 11),
                      Expanded(
                        child: Text(
                          title,
                          style: GoogleFonts.dmSans(
                            fontSize: 12.5,
                            fontWeight: highlighted ? FontWeight.w600 : FontWeight.w500,
                            color: highlighted ? Colors.white : const Color(0xFF2C2436),
                          ),
                        ),
                      ),
                      if (badge != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: badgeColor ?? const Color(0xFFE07B39),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            badge,
                            style: const TextStyle(
                              fontSize: 10.5,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  // ── Welcome Banner ──────────────────────────────────────────
  Widget _buildWelcomeAlert() {
    if (!_showWelcomeBanner) return const SizedBox.shrink();

    return Container(
      margin: const EdgeInsets.only(bottom: 18),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 11),
      decoration: BoxDecoration(
        color: const Color(0xFFE8F5E9),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFC8E6C9)),
      ),
      child: Row(
        children: [
          const Icon(
            Icons.check_circle_outline,
            size: 18,
            color: Color(0xFF2E7D32),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'Welcome back, $_displayName!',
              style: GoogleFonts.dmSans(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: const Color(0xFF2E7D32),
              ),
            ),
          ),
          InkWell(
            onTap: () => setState(() => _showWelcomeBanner = false),
            child: const Icon(
              Icons.close,
              size: 16,
              color: Color(0xFF2E7D32),
            ),
          ),
        ],
      ),
    );
  }

  // ── Hero Banner ─────────────────────────────────────────────
  Widget _buildHeroBanner() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isNarrow = constraints.maxWidth < 500;

        return Container(
          margin: const EdgeInsets.only(bottom: 20),
          padding: EdgeInsets.all(isNarrow ? 18 : 24),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                Color(0xFF4C3E56),
                Color(0xFF684E5E),
                Color(0xFF7A5458),
              ],
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF4C3E56).withValues(alpha: 0.25),
                blurRadius: 18,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Rider Avatar Initials
                  Container(
                    width: isNarrow ? 48 : 58,
                    height: isNarrow ? 48 : 58,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.16),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: Colors.white.withValues(alpha: 0.25),
                        width: 1.2,
                      ),
                    ),
                    child: Center(
                      child: Text(
                        _initials,
                        style: GoogleFonts.dmSans(
                          fontSize: isNarrow ? 17 : 20,
                          fontWeight: FontWeight.w800,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),

                  SizedBox(width: isNarrow ? 12 : 18),

                  // Name and Details
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Tag badges
                        Wrap(
                          spacing: 8,
                          runSpacing: 4,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.18),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                'EXPRESS RIDER',
                                style: GoogleFonts.dmSans(
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 0.6,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                            Text(
                              '• Metro South Sector',
                              style: GoogleFonts.dmSans(
                                fontSize: 11.5,
                                color: Colors.white.withValues(alpha: 0.8),
                              ),
                            ),
                          ],
                        ),

                        const SizedBox(height: 6),

                        // Name
                        Text(
                          _displayName,
                          style: GoogleFonts.playfairDisplay(
                            fontSize: isNarrow ? 20 : 24,
                            fontWeight: FontWeight.w700,
                            color: Colors.white,
                            letterSpacing: -0.3,
                          ),
                          overflow: TextOverflow.ellipsis,
                          maxLines: 1,
                        ),

                        const SizedBox(height: 4),

                        // Vehicle & Rating
                        Wrap(
                          spacing: 6,
                          runSpacing: 4,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          children: [
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(
                                  Icons.directions_car,
                                  size: 14,
                                  color: Color(0xFFFDE68A),
                                ),
                                const SizedBox(width: 5),
                                Text(
                                  'car (123ABC)',
                                  style: GoogleFonts.dmSans(
                                    fontSize: 12,
                                    color:
                                        Colors.white.withValues(alpha: 0.9),
                                  ),
                                ),
                              ],
                            ),
                            const Text('•',
                                style: TextStyle(color: Colors.white70)),
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(
                                  Icons.star,
                                  size: 14,
                                  color: Color(0xFFF59E0B),
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  '5.00 Rating',
                                  style: GoogleFonts.dmSans(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: const Color(0xFFFDE68A),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  // Scan Parcel button (desktop: inline, mobile: hidden here)
                  if (!isNarrow) ...[
                    const SizedBox(width: 16),
                    InkWell(
                      onTap: () => _openBarcodeScanner('Scan Parcel'),
                      borderRadius: BorderRadius.circular(10),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 18, vertical: 11),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(10),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.12),
                              blurRadius: 8,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(
                              Icons.qr_code_scanner,
                              size: 16,
                              color: Color(0xFF4C3E56),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              'Scan Parcel',
                              style: GoogleFonts.dmSans(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF4C3E56),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ],
              ),

              // Scan Parcel button (mobile: full-width below)
              if (isNarrow) ...[
                const SizedBox(height: 14),
                InkWell(
                  onTap: () => _openBarcodeScanner('Scan Parcel'),
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.12),
                          blurRadius: 8,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.qr_code_scanner,
                          size: 16,
                          color: Color(0xFF4C3E56),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          'Scan Parcel',
                          style: GoogleFonts.dmSans(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF4C3E56),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  // ── 4 KPI Metric Cards ──────────────────────────────────────
  Widget _buildKpiRow() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isWide = constraints.maxWidth >= 760;

        final cards = [
          _kpiCard(
            title: 'ITEMS FOR PICKUP',
            value: '${MockData.availablePickups.length}',
            actionText: 'View seller requests →',
            icon: Icons.file_upload_outlined,
            iconBg: const Color(0xFFFCEFE9),
            iconColor: const Color(0xFFC07357),
            onTap: () => _push(
              RiderPickupDashboard(
                token: widget.token,
                onBack: () => Navigator.of(context).pop(),
              ),
            ),
          ),
          _kpiCard(
            title: 'ACTIVE DELIVERY QUEUE',
            value: '0',
            actionText: 'View route parcels →',
            icon: Icons.local_shipping_outlined,
            iconBg: const Color(0xFFF0EBF4),
            iconColor: const Color(0xFF6E5E7E),
            onTap: () => _push(
              RiderDeliveryDashboard(
                token: widget.token,
                onBack: () => Navigator.of(context).pop(),
              ),
            ),
          ),
          _kpiCard(
            title: 'DELIVERED TODAY',
            value: '0',
            subtext: 'Verified with recipient',
            subtextColor: const Color(0xFF2E7D32),
            icon: Icons.check,
            iconBg: const Color(0xFFE8F5E9),
            iconColor: const Color(0xFF2E7D32),
          ),
          _kpiCard(
            title: "TODAY'S PROFIT",
            value: '₱0.00',
            subtext: 'COD Remittance: ₱0.00',
            icon: Icons.attach_money,
            iconBg: const Color(0xFFFFF7ED),
            iconColor: const Color(0xFFD97706),
            onTap: () => _push(
              RiderEarningsScreen(
                token: widget.token,
                onBack: () => Navigator.of(context).pop(),
              ),
            ),
          ),
        ];

        if (isWide) {
          return Row(
            children: cards
                .map((c) => Expanded(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 5),
                        child: c,
                      ),
                    ))
                .toList(),
          );
        } else {
          return Column(
            children: cards
                .map((c) => Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: c,
                    ))
                .toList(),
          );
        }
      },
    );
  }

  Widget _kpiCard({
    required String title,
    required String value,
    String? actionText,
    String? subtext,
    Color? subtextColor,
    required IconData icon,
    required Color iconBg,
    required Color iconColor,
    VoidCallback? onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFECE3E5)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.02),
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
                Expanded(
                  child: Text(
                    title,
                    style: GoogleFonts.dmSans(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 0.8,
                      color: const Color(0xFF8A7780),
                    ),
                  ),
                ),
                Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(
                    color: iconBg,
                    shape: BoxShape.circle,
                  ),
                  child: Icon(icon, size: 16, color: iconColor),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              value,
              style: GoogleFonts.playfairDisplay(
                fontSize: 26,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF261E30),
              ),
            ),
            const SizedBox(height: 8),
            if (actionText != null)
              Text(
                actionText,
                style: GoogleFonts.dmSans(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF8A7780),
                ),
              )
            else if (subtext != null)
              Text(
                subtext,
                style: GoogleFonts.dmSans(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w500,
                  color: subtextColor ?? const Color(0xFF8A7780),
                ),
              ),
          ],
        ),
      ),
    );
  }

  // ── Section 1: Today's Delivery Route ───────────────────────
  Widget _buildDeliveryRouteSection() {
    return Container(
      margin: const EdgeInsets.only(top: 22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFECE3E5)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Padding(
            padding: const EdgeInsets.fromLTRB(18, 16, 18, 14),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(7),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF7F2F3),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(
                    Icons.local_shipping_outlined,
                    size: 18,
                    color: Color(0xFF6E5E7E),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        "Today's Delivery Route",
                        style: GoogleFonts.playfairDisplay(
                          fontSize: 15.5,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF261E30),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Parcels scheduled for direct doorstep delivery',
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          color: const Color(0xFF8A7780),
                        ),
                      ),
                    ],
                  ),
                ),
                GestureDetector(
                  onTap: () => _push(
                    RiderDeliveryDashboard(
                      token: widget.token,
                      onBack: () => Navigator.of(context).pop(),
                    ),
                  ),
                  child: Text(
                    'View All (0) →',
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF865561),
                    ),
                  ),
                ),
              ],
            ),
          ),

          const Divider(height: 1, thickness: 1, color: Color(0xFFF3ECEE)),

          // Route Clear Empty State
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
            child: Center(
              child: Column(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF5F0F2),
                      shape: BoxShape.circle,
                      border: Border.all(color: const Color(0xFFE5DCDD)),
                    ),
                    child: const Icon(
                      Icons.check,
                      size: 22,
                      color: Color(0xFF9E8A92),
                    ),
                  ),
                  const SizedBox(height: 14),
                  Text(
                    'Your delivery route is clear!',
                    style: GoogleFonts.playfairDisplay(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF261E30),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Check the pickup dashboard to collect new packages from merchants.',
                    style: GoogleFonts.dmSans(
                      fontSize: 12.5,
                      color: const Color(0xFF8A7780),
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Section 2: Seller Pickup Requests ───────────────────────
  Widget _buildPickupRequestsSection() {
    return Container(
      margin: const EdgeInsets.only(top: 22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFECE3E5)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Padding(
            padding: const EdgeInsets.fromLTRB(18, 16, 18, 14),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(7),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF7F2F3),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(
                    Icons.file_upload_outlined,
                    size: 18,
                    color: Color(0xFFC07357),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Seller Pickup Requests',
                        style: GoogleFonts.playfairDisplay(
                          fontSize: 15.5,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF261E30),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Merchants waiting for courier collection',
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          color: const Color(0xFF8A7780),
                        ),
                      ),
                    ],
                  ),
                ),
                GestureDetector(
                  onTap: () => _push(
                    RiderPickupDashboard(
                      token: widget.token,
                      onBack: () => Navigator.of(context).pop(),
                    ),
                  ),
                  child: Text(
                    'View All Pickups →',
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF865561),
                    ),
                  ),
                ),
              ],
            ),
          ),

          const Divider(height: 1, thickness: 1, color: Color(0xFFF3ECEE)),

          // Pickup Request Cards
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _pickupRequests.length,
            separatorBuilder: (_, __) => const Divider(
              height: 1,
              thickness: 1,
              color: Color(0xFFF5EFEF),
            ),
            itemBuilder: (context, index) {
              final req = _pickupRequests[index];
              final accepted = req['accepted'] == true;

              return Padding(
                padding: const EdgeInsets.all(18),
                child: LayoutBuilder(
                  builder: (context, itemConstraints) {
                    final isNarrow = itemConstraints.maxWidth < 480;

                    final infoColumn = Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Wrap(
                          crossAxisAlignment: WrapCrossAlignment.center,
                          spacing: 8,
                          runSpacing: 4,
                          children: [
                            Text(
                              req['mall'] as String,
                              style: GoogleFonts.dmSans(
                                fontSize: 13.5,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF261E30),
                              ),
                            ),
                            // Parcels badge
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFEF3C7),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                req['parcels'] as String,
                                style: GoogleFonts.dmSans(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w700,
                                  color: const Color(0xFF92400E),
                                ),
                              ),
                            ),
                            // Time badge
                            Text(
                              req['time'] as String,
                              style: GoogleFonts.dmSans(
                                fontSize: 11.5,
                                color: const Color(0xFF8A7780),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Icon(
                              Icons.location_on_outlined,
                              size: 14,
                              color: Color(0xFF865561),
                            ),
                            const SizedBox(width: 5),
                            Expanded(
                              child: Text(
                                req['address'] as String,
                                style: GoogleFonts.dmSans(
                                  fontSize: 12,
                                  color: const Color(0xFF5A4D54),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          req['note'] as String,
                          style: GoogleFonts.dmSans(
                            fontSize: 11.5,
                            fontStyle: FontStyle.italic,
                            color: const Color(0xFF8A7780),
                          ),
                        ),
                      ],
                    );

                    final actionButton = InkWell(
                      onTap: accepted ? null : () => _acceptPickup(index),
                      borderRadius: BorderRadius.circular(10),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 9,
                        ),
                        decoration: BoxDecoration(
                          color: accepted
                              ? const Color(0xFF2E7D32)
                              : const Color(0xFF865561),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(
                              Icons.check,
                              size: 14,
                              color: Colors.white,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              accepted ? 'Accepted' : 'Accept Pickup',
                              style: GoogleFonts.dmSans(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: Colors.white,
                              ),
                            ),
                          ],
                        ),
                      ),
                    );

                    if (isNarrow) {
                      return Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          infoColumn,
                          const SizedBox(height: 12),
                          actionButton,
                        ],
                      );
                    }

                    return Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(child: infoColumn),
                        const SizedBox(width: 14),
                        actionButton,
                      ],
                    );
                  },
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isMobile = constraints.maxWidth < 900;

        return Scaffold(
          backgroundColor: CartzyColors.background,
          drawer: isMobile ? Drawer(child: _buildSidebar()) : null,
          body: SafeArea(
            child: Column(
              children: [
                _buildTopBar(isMobile),
                Expanded(
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (!isMobile) _buildSidebar(),
                      Expanded(
                        child: SingleChildScrollView(
                          padding: const EdgeInsets.all(22),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildWelcomeAlert(),
                              _buildHeroBanner(),
                              _buildKpiRow(),
                              _buildDeliveryRouteSection(),
                              _buildPickupRequestsSection(),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}


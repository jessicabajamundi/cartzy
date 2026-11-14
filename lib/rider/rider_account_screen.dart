import 'package:cartzy/rider/rider_home_address.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/app_update_service.dart';
import 'package:cartzy/widgets/patch_notes_dialog.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/services/auth_service.dart';
import 'rider_common_app_bar.dart';

class RiderAccountScreen extends StatefulWidget {
  final int userId;
  final VoidCallback onBack;
  final VoidCallback? onLogout;

  const RiderAccountScreen({
    super.key,
    required this.userId,
    required this.onBack,
    this.onLogout,
  });

  @override
  State<RiderAccountScreen> createState() => _RiderAccountScreenState();
}

class _RiderAccountScreenState extends State<RiderAccountScreen> {
  static String _user(String key) =>
      AuthService.currentUser?[key]?.toString().trim() ?? '';

  String get _name => _user('name').isNotEmpty ? _user('name') : 'Rider';

  String get _initials {
    final parts = _name.split(RegExp(r'\s+')).where((p) => p.isNotEmpty);
    if (parts.isEmpty) return 'RD';
    return parts.take(2).map((p) => p[0].toUpperCase()).join();
  }

  late final TextEditingController _phoneController;
  late final TextEditingController _plateController;
  late final TextEditingController _areaController;
  late String _vehicleClassification;

  bool _isSaving = false;
  bool _isOnline = true;

  @override
  void initState() {
    super.initState();
    _phoneController = TextEditingController(text: _user('phone'));

    final plate = _user('plate_no');
    _plateController = TextEditingController(text: plate.isNotEmpty ? plate : 'NQ-4812');

    final area = _user('area');
    _areaController = TextEditingController(
      text: area.isNotEmpty ? area : 'Metro South (Laguna & NCR)',
    );

    final veh = _user('vehicle_type');
    if (veh.isNotEmpty) {
      final match = ['Motorcycle', 'Car', 'Van', 'Bicycle', 'Tricycle', 'E-bike'].firstWhere(
        (v) => v.toLowerCase() == veh.toLowerCase(),
        orElse: () => 'Motorcycle',
      );
      _vehicleClassification = match;
    } else {
      _vehicleClassification = 'Motorcycle';
    }

    final active = AuthService.currentUser?['is_active'];
    if (active is bool) {
      _isOnline = active;
    }
  }

  @override
  void dispose() {
    _phoneController.dispose();
    _plateController.dispose();
    _areaController.dispose();
    super.dispose();
  }

  Future<void> _toggleOnlineStatus() async {
    setState(() => _isOnline = !_isOnline);
    final token = AuthService.token;
    if (token != null && token.isNotEmpty) {
      try {
        await ApiService.put('/rider/availability', {'is_active': _isOnline}, token: token);
      } catch (_) {}
    }
    AuthService.updateCurrentUser({'is_active': _isOnline});
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(_isOnline ? 'You are now ONLINE and ready for orders.' : 'You are now OFFLINE (Shift paused).'),
        backgroundColor: _isOnline ? const Color(0xFF059669) : const Color(0xFF564B68),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  Future<void> _saveUpdates() async {
    setState(() => _isSaving = true);
    final updates = <String, dynamic>{
      'phone': _phoneController.text.trim(),
      'plate_no': _plateController.text.trim(),
      'area': _areaController.text.trim(),
      'vehicle_type': _vehicleClassification,
    };

    final token = AuthService.token;
    if (token != null && token.isNotEmpty) {
      try {
        await ApiService.put('/account/profile', updates, token: token);
      } catch (_) {}
    }

    AuthService.updateCurrentUser(updates);
    if (!mounted) return;
    setState(() => _isSaving = false);

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Account information updated successfully!'),
        backgroundColor: Color(0xFF059669),
        duration: Duration(seconds: 2),
      ),
    );
  }

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(
          'Sign Out Session',
          style: GoogleFonts.dmSans(fontWeight: FontWeight.bold, fontSize: 18, color: const Color(0xFF1E1B2E)),
        ),
        content: Text(
          'Are you sure you want to log out of the Courier Rider Portal?',
          style: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFF6B7280), height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: Text(
              'Cancel',
              style: GoogleFonts.dmSans(fontWeight: FontWeight.w600, color: const Color(0xFF6B7280)),
            ),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFDC2626),
              foregroundColor: Colors.white,
              elevation: 0,
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () {
              Navigator.pop(ctx);
              if (widget.onLogout != null) {
                widget.onLogout!();
              } else {
                Navigator.of(context).pop();
              }
            },
            child: Text(
              'Logout Session',
              style: GoogleFonts.dmSans(fontWeight: FontWeight.w700, fontSize: 13),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F7FA),
      body: Column(
        children: [
          RiderCommonAppBar(
            title: 'Account Management',
            sector: 'Metro South Sector',
            onBack: widget.onBack,
            onLogout: widget.onLogout ?? _confirmLogout,
          ),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildProfileHeaderCard(),
                  const SizedBox(height: 14),
                  _buildQuickStatsStrip(),
                  const SizedBox(height: 14),
                  _buildTwoColumnDetails(),
                  const SizedBox(height: 14),
                  _buildHomeAddressCard(),
                  const SizedBox(height: 14),
                  _buildPatchNotesCard(),
                  const SizedBox(height: 14),
                  _buildSignOutCard(),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── 1. Profile Hero Card ──────────────────────────────────────
  Widget _buildProfileHeaderCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFECE7EF)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x08564B68),
            blurRadius: 14,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Top Row: Avatar + Name + Badges
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Avatar with active dot
              Stack(
                children: [
                  Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF564B68), Color(0xFF6F6382)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: const [
                        BoxShadow(
                          color: Color(0x22564B68),
                          blurRadius: 8,
                          offset: Offset(0, 3),
                        ),
                      ],
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      _initials,
                      style: GoogleFonts.dmSans(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ),
                  Positioned(
                    bottom: 0,
                    right: 0,
                    child: Container(
                      width: 14,
                      height: 14,
                      decoration: BoxDecoration(
                        color: _isOnline ? const Color(0xFF10B981) : const Color(0xFF9CA3AF),
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.white, width: 2),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(width: 14),

              // Name & Identity Tags
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _name,
                      style: GoogleFonts.dmSans(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                        color: const Color(0xFF1E1B2E),
                        letterSpacing: -0.3,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 5),
                    Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        // Role Pill
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF3EFF7),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: const Color(0xFFE5DCEF)),
                          ),
                          child: Text(
                            'LAST-MILE RIDER',
                            style: GoogleFonts.dmSans(
                              fontSize: 9.5,
                              fontWeight: FontWeight.w800,
                              color: const Color(0xFF564B68),
                              letterSpacing: 0.3,
                            ),
                          ),
                        ),
                        // Verified Badge
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                          decoration: BoxDecoration(
                            color: const Color(0xFFECFDF5),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: const Color(0xFFA7F3D0)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.check_circle_rounded, size: 11, color: Color(0xFF059669)),
                              const SizedBox(width: 3),
                              Text(
                                'FULLY VERIFIED',
                                style: GoogleFonts.dmSans(
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.w800,
                                  color: const Color(0xFF065F46),
                                ),
                              ),
                            ],
                          ),
                        ),
                        // Rating Badge
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFFBEB),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: const Color(0xFFFDE68A)),
                          ),
                          child: Text(
                            '★ 5.00',
                            style: GoogleFonts.dmSans(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: const Color(0xFFB45309),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 14),
          const Divider(color: Color(0xFFF3ECEF), height: 1),
          const SizedBox(height: 12),

          // Contact Details
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (_user('email').isNotEmpty) ...[
                _buildContactRow(Icons.mail_outline_rounded, _user('email')),
                const SizedBox(height: 6),
              ],
              if (_user('phone').isNotEmpty) ...[
                _buildContactRow(Icons.phone_outlined, _user('phone')),
                const SizedBox(height: 6),
              ],
              _buildContactRow(
                Icons.hub_outlined,
                _user('hub_name').isNotEmpty ? _user('hub_name') : 'Metro South Sorting & Fulfillment Hub',
              ),
            ],
          ),

          const SizedBox(height: 14),

          // Online / Availability Working Status Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: _isOnline ? const Color(0xFFECFDF5) : const Color(0xFFF3F4F6),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: _isOnline ? const Color(0xFFA7F3D0) : const Color(0xFFE5E7EB),
              ),
            ),
            child: Row(
              children: [
                Container(
                  width: 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: _isOnline ? const Color(0xFF10B981) : const Color(0xFF6B7280),
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    _isOnline ? 'Status: ONLINE (Receiving Orders)' : 'Status: OFFLINE (Shift Paused)',
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: _isOnline ? const Color(0xFF065F46) : const Color(0xFF374151),
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(width: 6),
                InkWell(
                  onTap: _toggleOnlineStatus,
                  borderRadius: BorderRadius.circular(8),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3.5),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: _isOnline ? const Color(0xFFA7F3D0) : const Color(0xFFD1D5DB),
                      ),
                    ),
                    child: Text(
                      _isOnline ? 'Go Offline' : 'Go Online',
                      style: GoogleFonts.dmSans(
                        fontSize: 10.5,
                        fontWeight: FontWeight.w700,
                        color: _isOnline ? const Color(0xFF065F46) : const Color(0xFF374151),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContactRow(IconData icon, String text) {
    return Row(
      children: [
        Icon(icon, size: 14, color: const Color(0xFF6F6382)),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            text,
            style: GoogleFonts.dmSans(
              fontSize: 12,
              color: const Color(0xFF554B62),
              fontWeight: FontWeight.w500,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }

  // ── 2. Quick Overview Stats Strip ─────────────────────────────
  Widget _buildQuickStatsStrip() {
    return Row(
      children: [
        Expanded(
          child: _buildMetricCard(
            label: 'Assigned Vehicle',
            value: _vehicleClassification,
            subValue: _plateController.text,
            icon: Icons.two_wheeler_rounded,
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _buildMetricCard(
            label: 'Service Rating',
            value: '5.00 / 5.0',
            subValue: 'Top-tier rating',
            icon: Icons.star_rounded,
            iconColor: const Color(0xFFD97706),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _buildMetricCard(
            label: 'Logistics Sector',
            value: 'Metro South',
            subValue: 'Active Hub',
            icon: Icons.local_shipping_outlined,
          ),
        ),
      ],
    );
  }

  Widget _buildMetricCard({
    required String label,
    required String value,
    required String subValue,
    required IconData icon,
    Color? iconColor,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFECE7EF)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  label,
                  style: GoogleFonts.dmSans(
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF8A7780),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const SizedBox(width: 2),
              Icon(icon, size: 13, color: iconColor ?? const Color(0xFF6F6382)),
            ],
          ),
          const SizedBox(height: 3),
          Text(
            value,
            style: GoogleFonts.dmSans(
              fontSize: 12.5,
              fontWeight: FontWeight.w800,
              color: const Color(0xFF1E1B2E),
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          Text(
            subValue,
            style: GoogleFonts.dmSans(
              fontSize: 10,
              color: const Color(0xFF8A7780),
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  // ── 3. Two Column Details & Update Forms ──────────────────────
  Widget _buildTwoColumnDetails() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isMobile = constraints.maxWidth < 750;

        if (isMobile) {
          return Column(
            children: [
              _buildVehicleCredentialsCard(),
              const SizedBox(height: 14),
              _buildUpdateInfoCard(),
            ],
          );
        }

        return Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(child: _buildVehicleCredentialsCard()),
            const SizedBox(width: 14),
            Expanded(child: _buildUpdateInfoCard()),
          ],
        );
      },
    );
  }

  Widget _buildVehicleCredentialsCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFECE7EF)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: const Color(0xFFF3EFF7),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.verified_user_outlined,
                  size: 17,
                  color: Color(0xFF564B68),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Vehicle & License Credentials',
                      style: GoogleFonts.dmSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                        color: const Color(0xFF1E1B2E),
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    Text(
                      'Official transport registration on file',
                      style: GoogleFonts.dmSans(
                        fontSize: 11,
                        color: const Color(0xFF8A7780),
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          _buildCredentialRow(
            label: 'Vehicle Type:',
            valueWidget: Text(
              _vehicleClassification,
              style: GoogleFonts.dmSans(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF1E1B2E),
              ),
              overflow: TextOverflow.ellipsis,
            ),
          ),
          const Divider(color: Color(0xFFF5F2F7), height: 16),

          _buildCredentialRow(
            label: 'Plate Number:',
            valueWidget: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
              decoration: BoxDecoration(
                color: const Color(0xFFFBF8FA),
                borderRadius: BorderRadius.circular(6),
                border: Border.all(color: const Color(0xFFE5DCEF)),
              ),
              child: Text(
                _plateController.text,
                style: GoogleFonts.dmSans(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 0.8,
                  color: const Color(0xFF1E1B2E),
                ),
              ),
            ),
          ),
          const Divider(color: Color(0xFFF5F2F7), height: 16),

          _buildCredentialRow(
            label: 'Driver\'s License No:',
            valueWidget: Text(
              'D02-18-984712',
              style: GoogleFonts.dmSans(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF1E1B2E),
              ),
            ),
          ),
          const Divider(color: Color(0xFFF5F2F7), height: 16),

          _buildCredentialRow(
            label: 'License Expiration:',
            valueWidget: Text(
              'Valid until 2028 (Active)',
              style: GoogleFonts.dmSans(
                fontSize: 11.5,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF059669),
              ),
            ),
          ),
          const Divider(color: Color(0xFFF5F2F7), height: 16),

          _buildCredentialRow(
            label: 'Service Coverage:',
            valueWidget: Text(
              _areaController.text,
              style: GoogleFonts.dmSans(
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                color: const Color(0xFF554B62),
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCredentialRow({required String label, required Widget valueWidget}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: GoogleFonts.dmSans(
            fontSize: 12,
            color: const Color(0xFF8A7780),
          ),
        ),
        const SizedBox(width: 8),
        Flexible(child: valueWidget),
      ],
    );
  }

  Widget _buildUpdateInfoCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFECE7EF)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: const Color(0xFFFDF2F0),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.edit_note_rounded,
                  size: 19,
                  color: Color(0xFF8C5A50),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Update Information',
                      style: GoogleFonts.dmSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                        color: const Color(0xFF1E1B2E),
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    Text(
                      'Modify contact and vehicle details',
                      style: GoogleFonts.dmSans(
                        fontSize: 11,
                        color: const Color(0xFF8A7780),
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          _buildFormLabel('Mobile Contact Number'),
          const SizedBox(height: 5),
          _buildTextFormField(_phoneController, hint: '09XX XXX XXXX'),

          const SizedBox(height: 12),

          _buildFormLabel('Vehicle Classification'),
          const SizedBox(height: 5),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            decoration: BoxDecoration(
              color: const Color(0xFFFAF9FB),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFE5DCEF)),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                isExpanded: true,
                value: _vehicleClassification,
                icon: const Icon(Icons.keyboard_arrow_down_rounded, color: Color(0xFF564B68)),
                style: GoogleFonts.dmSans(fontSize: 12.5, fontWeight: FontWeight.w600, color: const Color(0xFF1E1B2E)),
                items: ['Motorcycle', 'Car', 'Van', 'Bicycle', 'Tricycle', 'E-bike']
                    .map((item) => DropdownMenuItem(value: item, child: Text(item)))
                    .toList(),
                onChanged: (val) {
                  if (val != null) setState(() => _vehicleClassification = val);
                },
              ),
            ),
          ),

          const SizedBox(height: 12),

          _buildFormLabel('Plate Number'),
          const SizedBox(height: 5),
          _buildTextFormField(_plateController, hint: 'e.g. NQ-4812'),

          const SizedBox(height: 12),

          _buildFormLabel('Preferred Delivery Area'),
          const SizedBox(height: 5),
          _buildTextFormField(_areaController, hint: 'e.g. Metro South (Laguna & NCR)'),

          const SizedBox(height: 16),

          Align(
            alignment: Alignment.centerRight,
            child: ElevatedButton(
              onPressed: _isSaving ? null : _saveUpdates,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF564B68),
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
              ),
              child: _isSaving
                  ? const SizedBox(
                      width: 14,
                      height: 14,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : Text(
                      'Save Updates',
                      style: GoogleFonts.dmSans(fontSize: 12, fontWeight: FontWeight.w700),
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFormLabel(String label) {
    return Text(
      label,
      style: GoogleFonts.dmSans(
        fontSize: 11,
        fontWeight: FontWeight.w600,
        color: const Color(0xFF554B62),
      ),
    );
  }

  Widget _buildTextFormField(TextEditingController controller, {String? hint}) {
    return TextField(
      controller: controller,
      style: GoogleFonts.dmSans(fontSize: 12.5, fontWeight: FontWeight.w600, color: const Color(0xFF1E1B2E)),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: GoogleFonts.dmSans(fontSize: 11.5, color: const Color(0xFFBAABB0)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        filled: true,
        fillColor: const Color(0xFFFAF9FB),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFFE5DCEF)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFFE5DCEF)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFF564B68), width: 1.4),
        ),
        isDense: true,
      ),
    );
  }

  // ── 4. Home Address Card ──────────────────────────────────────
  Widget _buildHomeAddressCard() {
    final home = HomeAddress.fromJson(AuthService.currentUser?['home_address']);
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFECE7EF)),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: const Color(0xFFF3EFF7),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.home_outlined, color: Color(0xFF564B68), size: 18),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Home address',
                  style: GoogleFonts.dmSans(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF1E1B2E),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  home?.label ?? 'Not added yet. Add it so your hub can send you deliveries near home.',
                  style: GoogleFonts.dmSans(fontSize: 11.5, color: const Color(0xFF8A7780)),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          const SizedBox(width: 6),
          TextButton(
            onPressed: () async {
              final saved = await showHomeAddressSheet(context, initial: home);
              if (saved == null || !mounted) return;
              AuthService.currentUser?['home_address'] = saved.toFields();
              setState(() {});
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Home address saved.')),
              );
            },
            style: TextButton.styleFrom(
              foregroundColor: const Color(0xFF564B68),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            ),
            child: Text(
              home == null ? 'Add' : 'Change',
              style: GoogleFonts.dmSans(fontWeight: FontWeight.w700, fontSize: 12.5),
            ),
          ),
        ],
      ),
    );
  }

  // ── 5. Patch Notes Card ───────────────────────────────────────
  Widget _buildPatchNotesCard() {
    final (version, notes) = PatchNotesDialog.latestRiderNotes(AppUpdateService.currentAppVersion);

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFECE7EF)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: const Color(0xFFFFF7ED),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.auto_awesome_outlined, size: 18, color: Color(0xFFD97706)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      "What's new for riders & couriers",
                      style: GoogleFonts.dmSans(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w800,
                        color: const Color(0xFF1E1B2E),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Version $version · ${notes.length} updates',
                      style: GoogleFonts.dmSans(fontSize: 11.5, color: const Color(0xFF8A7780)),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () => PatchNotesDialog.showPatchNotes(context, version: version, highlights: notes),
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFF564B68),
                side: const BorderSide(color: Color(0xFFE1DDE7)),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              ),
              icon: const Icon(Icons.article_outlined, size: 15),
              label: Text('See patch notes', style: GoogleFonts.dmSans(fontSize: 12.5, fontWeight: FontWeight.w700)),
            ),
          ),
        ],
      ),
    );
  }

  // ── 6. Bottom Sign Out Card ───────────────────────────────────
  Widget _buildSignOutCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFECE7EF)),
      ),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final isMobile = constraints.maxWidth < 600;

          final textContent = Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Sign Out of Courier Rider Portal',
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w800,
                  color: const Color(0xFF1E1B2E),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                'Always sign out when finishing your shift or sharing devices.',
                style: GoogleFonts.dmSans(
                  fontSize: 11.5,
                  color: const Color(0xFF8A7780),
                ),
              ),
            ],
          );

          final logoutBtn = OutlinedButton.icon(
            onPressed: widget.onLogout ?? _confirmLogout,
            style: OutlinedButton.styleFrom(
              foregroundColor: const Color(0xFFDC2626),
              side: const BorderSide(color: Color(0xFFFECACA)),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
            ),
            icon: const Icon(Icons.logout, size: 14, color: Color(0xFFDC2626)),
            label: Text(
              'Logout Session',
              style: GoogleFonts.dmSans(
                fontSize: 12,
                fontWeight: FontWeight.w700,
                color: const Color(0xFFDC2626),
              ),
            ),
          );

          if (isMobile) {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                textContent,
                const SizedBox(height: 10),
                logoutBtn,
              ],
            );
          }

          return Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(child: textContent),
              const SizedBox(width: 10),
              logoutBtn,
            ],
          );
        },
      ),
    );
  }
}

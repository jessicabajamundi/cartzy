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
  // The signed-in rider's own details.
  static String _user(String key) =>
      AuthService.currentUser?[key]?.toString().trim() ?? '';

  String get _name => _user('name').isNotEmpty ? _user('name') : 'Rider';

  String get _initials {
    final parts = _name.split(RegExp(r'\s+')).where((p) => p.isNotEmpty);
    return parts.take(2).map((p) => p[0].toUpperCase()).join();
  }

  final _phoneController = TextEditingController(text: _user('phone'));
  final _plateController = TextEditingController(text: '123ABC');
  final _areaController = TextEditingController(text: 'Metro South (Laguna & NCR)');
  String _vehicleClassification = 'Motorcycle';

  bool _isSaving = false;

  @override
  void dispose() {
    _phoneController.dispose();
    _plateController.dispose();
    _areaController.dispose();
    super.dispose();
  }

  void _saveUpdates() async {
    setState(() => _isSaving = true);
    await Future.delayed(const Duration(milliseconds: 500));
    if (!mounted) return;
    setState(() => _isSaving = false);
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Account information updated successfully!'),
        backgroundColor: Color(0xFF2E7D32),
      ),
    );
  }

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Sign Out Session',
          style: GoogleFonts.cormorantGaramond(fontWeight: FontWeight.bold, fontSize: 20),
        ),
        content: Text(
          'Are you sure you want to log out of the Courier Rider Portal?',
          style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF554B62)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFD32F2F),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () {
              Navigator.pop(ctx);
              if (widget.onLogout != null) {
                widget.onLogout!();
              } else {
                Navigator.of(context).pop();
              }
            },
            child: const Text('Logout Session'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F5),
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
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildProfileHeaderCard(),
                  const SizedBox(height: 20),
                  _buildTwoColumnDetails(),
                  const SizedBox(height: 20),
                  _buildSignOutCard(),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Profile Header Card ──────────────────────────────────────
  Widget _buildProfileHeaderCard() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      clipBehavior: Clip.antiAlias,
      child: Stack(
        children: [
          // Faint watermark profile icon on right
          Positioned(
            right: -20,
            bottom: -30,
            child: Icon(
              Icons.account_circle,
              size: 160,
              color: const Color(0xFFF7F3F5).withValues(alpha: 0.8),
            ),
          ),

          Padding(
            padding: const EdgeInsets.all(22),
            child: LayoutBuilder(
              builder: (context, constraints) {
                final isMobile = constraints.maxWidth < 650;

                final avatarWithStatus = Stack(
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF5A495E), Color(0xFF382A3A)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      alignment: Alignment.center,
                      child: Text(
                        _initials,
                        style: GoogleFonts.dmSans(
                          fontSize: 22,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
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
                          color: const Color(0xFF2E7D32),
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 2),
                        ),
                      ),
                    ),
                  ],
                );

                final detailsCol = Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Name & Badges
                    Wrap(
                      crossAxisAlignment: WrapCrossAlignment.center,
                      spacing: 10,
                      runSpacing: 6,
                      children: [
                        Text(
                          _name,
                          style: GoogleFonts.cormorantGaramond(
                            fontSize: 24,
                            fontWeight: FontWeight.w700,
                            color: const Color(0xFF261E30),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEAF6ED),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: const Color(0xFFC7E7CE)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.check, size: 12, color: Color(0xFF2E7D32)),
                              const SizedBox(width: 4),
                              Text(
                                'FULLY VERIFIED COURIER',
                                style: GoogleFonts.dmSans(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                  color: const Color(0xFF2E7D32),
                                ),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFF9EE),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: const Color(0xFFFDE6C5)),
                          ),
                          child: Text(
                            '★ 5.00',
                            style: GoogleFonts.dmSans(
                              fontSize: 10.5,
                              fontWeight: FontWeight.w700,
                              color: const Color(0xFFB45309),
                            ),
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 8),

                    // Contact & Hub line
                    Wrap(
                      spacing: 8,
                      runSpacing: 4,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        if (_user('email').isNotEmpty) ...[
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.email_outlined, size: 13, color: Color(0xFF8A7780)),
                            const SizedBox(width: 4),
                            Flexible(
                              child: Text(
                                _user('email'),
                                style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF554B62)),
                              ),
                            ),
                          ],
                        ),
                        const Text('•', style: TextStyle(color: Color(0xFFBAABB0))),
                        ],
                        if (_user('phone').isNotEmpty) ...[
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.phone_outlined, size: 13, color: Color(0xFF8A7780)),
                            const SizedBox(width: 4),
                            Flexible(
                              child: Text(
                                _user('phone'),
                                style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF554B62)),
                              ),
                            ),
                          ],
                        ),
                        const Text('•', style: TextStyle(color: Color(0xFFBAABB0))),
                        ],
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.storefront_outlined, size: 13, color: Color(0xFF8A7780)),
                            const SizedBox(width: 4),
                            Flexible(
                              child: Text(
                                'Metro South Sorting & Fulfillment Hub',
                                style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF554B62)),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),

                    const SizedBox(height: 12),

                    // Online Status Button/Pill
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: const Color(0xFF00875A),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 6,
                            height: 6,
                            decoration: const BoxDecoration(
                              color: Colors.white,
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 6),
                          Flexible(
                            child: Text(
                              'Status: ONLINE (Receiving Orders)',
                              style: GoogleFonts.dmSans(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: Colors.white,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                );

                if (isMobile) {
                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      avatarWithStatus,
                      const SizedBox(height: 14),
                      detailsCol,
                    ],
                  );
                }

                return Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    avatarWithStatus,
                    const SizedBox(width: 18),
                    Expanded(child: detailsCol),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  // ── Two Column Credentials & Update Details ─────────────────
  Widget _buildTwoColumnDetails() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isMobile = constraints.maxWidth < 800;

        if (isMobile) {
          return Column(
            children: [
              _buildVehicleCredentialsCard(),
              const SizedBox(height: 16),
              _buildUpdateInfoCard(),
            ],
          );
        }

        return Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(child: _buildVehicleCredentialsCard()),
            const SizedBox(width: 16),
            Expanded(child: _buildUpdateInfoCard()),
          ],
        );
      },
    );
  }

  // Left: Vehicle & License Credentials
  Widget _buildVehicleCredentialsCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: const Color(0xFFFDF2EE),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(
                  Icons.badge_outlined,
                  size: 16,
                  color: Color(0xFFD97850),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Vehicle & License Credentials',
                    style: GoogleFonts.cormorantGaramond(
                      fontSize: 17,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF261E30),
                    ),
                  ),
                  Text(
                    'Official transport registration on file',
                    style: GoogleFonts.dmSans(
                      fontSize: 11.5,
                      color: const Color(0xFF8A7780),
                    ),
                  ),
                ],
              ),
              ),
            ],
          ),

          const SizedBox(height: 20),

          _buildCredentialRow(
            label: 'Vehicle Type:',
            valueWidget: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.directions_car, size: 14, color: Color(0xFFD97850)),
                const SizedBox(width: 4),
                Text(
                  'car',
                  style: GoogleFonts.dmSans(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF261E30),
                  ),
                ),
              ],
            ),
          ),
          const Divider(color: Color(0xFFF3ECEF), height: 16),

          _buildCredentialRow(
            label: 'Plate Number:',
            valueWidget: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              decoration: BoxDecoration(
                color: const Color(0xFFFBF8F9),
                borderRadius: BorderRadius.circular(4),
                border: Border.all(color: const Color(0xFFEFE8EA)),
              ),
              child: Text(
                '123ABC',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF261E30),
                ),
              ),
            ),
          ),
          const Divider(color: Color(0xFFF3ECEF), height: 16),

          _buildCredentialRow(
            label: 'Driver\'s License No:',
            valueWidget: Text(
              'D02-18-984712',
              style: GoogleFonts.dmSans(
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF261E30),
              ),
            ),
          ),
          const Divider(color: Color(0xFFF3ECEF), height: 16),

          _buildCredentialRow(
            label: 'License Expiration:',
            valueWidget: Text(
              'Valid until 2028 (Active)',
              style: GoogleFonts.dmSans(
                fontSize: 12.5,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF2E7D32),
              ),
            ),
          ),
          const Divider(color: Color(0xFFF3ECEF), height: 16),

          _buildCredentialRow(
            label: 'Service Coverage:',
            valueWidget: Text(
              'Metro South (Laguna & NCR)',
              style: GoogleFonts.dmSans(
                fontSize: 12.5,
                fontWeight: FontWeight.w500,
                color: const Color(0xFF554B62),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCredentialRow({required String label, required Widget valueWidget}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              label,
              style: GoogleFonts.dmSans(
                fontSize: 12.5,
                color: const Color(0xFF8A7780),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Flexible(child: valueWidget),
        ],
      ),
    );
  }

  // Right: Update Information
  Widget _buildUpdateInfoCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFEFE8EA)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: const Color(0xFFF3ECEF),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(
                  Icons.edit_outlined,
                  size: 16,
                  color: Color(0xFF554B62),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Update Information',
                    style: GoogleFonts.cormorantGaramond(
                      fontSize: 17,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF261E30),
                    ),
                  ),
                  Text(
                    'Modify phone number and vehicle details',
                    style: GoogleFonts.dmSans(
                      fontSize: 11.5,
                      color: const Color(0xFF8A7780),
                    ),
                  ),
                ],
              ),
              ),
            ],
          ),

          const SizedBox(height: 20),

          // Mobile Contact Number
          _buildFormLabel('Mobile Contact Number'),
          const SizedBox(height: 6),
          _buildTextFormField(_phoneController),

          const SizedBox(height: 14),

          // Vehicle Classification Dropdown
          _buildFormLabel('Vehicle Classification'),
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: const Color(0xFFE5DCDD)),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                isExpanded: true,
                value: _vehicleClassification,
                icon: const Icon(Icons.keyboard_arrow_down, color: Color(0xFF554B62)),
                style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF261E30)),
                items: ['Motorcycle', 'Car', 'Van', 'Bicycle']
                    .map((item) => DropdownMenuItem(value: item, child: Text(item)))
                    .toList(),
                onChanged: (val) {
                  if (val != null) setState(() => _vehicleClassification = val);
                },
              ),
            ),
          ),

          const SizedBox(height: 14),

          // Plate Number
          _buildFormLabel('Plate Number'),
          const SizedBox(height: 6),
          _buildTextFormField(_plateController),

          const SizedBox(height: 14),

          // Preferred Delivery Area
          _buildFormLabel('Preferred Delivery Area'),
          const SizedBox(height: 6),
          _buildTextFormField(_areaController, hint: 'e.g. Metro South (Laguna & NCR)'),

          const SizedBox(height: 18),

          Align(
            alignment: Alignment.centerRight,
            child: ElevatedButton(
              onPressed: _isSaving ? null : _saveUpdates,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF46333D),
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
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
                      style: GoogleFonts.dmSans(fontSize: 12.5, fontWeight: FontWeight.w700),
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
        fontSize: 11.5,
        fontWeight: FontWeight.w600,
        color: const Color(0xFF554B62),
      ),
    );
  }

  Widget _buildTextFormField(TextEditingController controller, {String? hint}) {
    return TextField(
      controller: controller,
      style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF261E30)),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFFBAABB0)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Color(0xFFE5DCDD)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Color(0xFFE5DCDD)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Color(0xFF46333D)),
        ),
        isDense: true,
      ),
    );
  }

  // ── Bottom Sign Out Card ─────────────────────────────────────
  Widget _buildSignOutCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFEFE8EA)),
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
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF261E30),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                'Always sign out when finishing your shift or sharing devices.',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  color: const Color(0xFF8A7780),
                ),
              ),
            ],
          );

          final logoutBtn = OutlinedButton.icon(
            onPressed: widget.onLogout ?? _confirmLogout,
            style: OutlinedButton.styleFrom(
              foregroundColor: const Color(0xFFD32F2F),
              side: const BorderSide(color: Color(0xFFF5C6C6)),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            ),
            icon: const Icon(Icons.logout, size: 14, color: Color(0xFFD32F2F)),
            label: Text(
              'Logout Session',
              style: GoogleFonts.dmSans(
                fontSize: 12.5,
                fontWeight: FontWeight.w600,
                color: const Color(0xFFD32F2F),
              ),
            ),
          );

          if (isMobile) {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                textContent,
                const SizedBox(height: 12),
                logoutBtn,
              ],
            );
          }

          return Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              textContent,
              logoutBtn,
            ],
          );
        },
      ),
    );
  }
}
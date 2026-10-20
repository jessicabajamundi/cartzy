import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';

class AccountSettingsScreen extends StatefulWidget {
  final String userName;
  final String userEmail;
  final String userPhone;
  final VoidCallback onBack;
  final VoidCallback onLogout;
  final ValueChanged<Map<String, dynamic>>? onProfileUpdated;

  const AccountSettingsScreen({
    super.key,
    required this.userName,
    required this.userEmail,
    this.userPhone = '09171234567',
    required this.onBack,
    required this.onLogout,
    this.onProfileUpdated,
  });

  @override
  State<AccountSettingsScreen> createState() => _AccountSettingsScreenState();
}

class _AccountSettingsScreenState extends State<AccountSettingsScreen> {
  int _activeTabIndex = 0; // 0: Personal details, 1: Security, 2: Delivery addresses

  // Personal Details Controllers
  late TextEditingController _nameController;
  late TextEditingController _phoneController;
  late TextEditingController _emailController;
  late TextEditingController _idNumberController;

  String _selectedSex = 'Female';
  final String _birthday = '14/10/2000';
  String _selectedIdType = 'National ID';
  String? _idFileName;
  String? _profilePhotoName;

  // Security Controllers
  final TextEditingController _currentPasswordController = TextEditingController();
  final TextEditingController _newPasswordController = TextEditingController();
  final TextEditingController _confirmPasswordController = TextEditingController();
  final TextEditingController _confirmSessionPasswordController = TextEditingController();

  // Saved Addresses
  final List<Map<String, String>> _savedAddresses = [];

  // Avatar image url
  final String _avatarUrl = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200';

  @override
  void initState() {
    super.initState();
    final initialName = widget.userName.isNotEmpty && widget.userName != 'Buyer'
        ? widget.userName
        : 'Leonardo, Tiffany Joy O.';
    final initialEmail = widget.userEmail.isNotEmpty && widget.userEmail != 'guest@cartzy.com'
        ? widget.userEmail
        : 'tiffany.leonardolspu@gmail.com';

    _nameController = TextEditingController(text: initialName);
    _phoneController = TextEditingController(text: widget.userPhone.isNotEmpty ? widget.userPhone : '09171234567');
    _emailController = TextEditingController(text: initialEmail);
    _idNumberController = TextEditingController();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _idNumberController.dispose();
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    _confirmSessionPasswordController.dispose();
    super.dispose();
  }

  Future<void> _pickProfilePhoto() async {
    try {
      final picker = ImagePicker();
      final XFile? photo = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (photo != null) {
        setState(() {
          _profilePhotoName = photo.name;
        });
        if (mounted) showCartzyFlash(context, 'Profile photo updated!');
      }
    } catch (_) {
      setState(() {
        _profilePhotoName = 'avatar_uploaded.png';
      });
      if (mounted) showCartzyFlash(context, 'Profile photo updated!');
    }
  }

  Future<void> _pickIdDocument() async {
    try {
      final picker = ImagePicker();
      final XFile? file = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (file != null) {
        setState(() {
          _idFileName = file.name;
        });
        if (mounted) showCartzyFlash(context, 'ID Document selected: ${file.name}');
      }
    } catch (_) {
      setState(() {
        _idFileName = 'national_id.jpg';
      });
      if (mounted) showCartzyFlash(context, 'ID Document uploaded!');
    }
  }

  void _savePersonalDetails() {
    showCartzyFlash(context, 'Profile details saved successfully!');
    widget.onProfileUpdated?.call({
      'name': _nameController.text.trim(),
      'phone': _phoneController.text.trim(),
      'sex': _selectedSex,
      'birthday': _birthday,
    });
  }

  void _showAddAddressDialog() {
    final streetController = TextEditingController();
    final cityController = TextEditingController(text: 'Pasig City, Metro Manila');
    final labelController = TextEditingController(text: 'Home');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Add Delivery Address',
          style: GoogleFonts.playfairDisplay(fontSize: 18, fontWeight: FontWeight.bold, color: const Color(0xFF231F20)),
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: labelController,
                decoration: const InputDecoration(labelText: 'Address Label (e.g. Home, Work)'),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: streetController,
                decoration: const InputDecoration(labelText: 'House / Unit & Street Name'),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: cityController,
                decoration: const InputDecoration(labelText: 'City / Municipality & Province'),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel', style: TextStyle(color: Color(0xFF7A6E72))),
          ),
          ElevatedButton(
            onPressed: () {
              if (streetController.text.trim().isNotEmpty) {
                setState(() {
                  _savedAddresses.add({
                    'label': labelController.text.trim(),
                    'street': streetController.text.trim(),
                    'city': cityController.text.trim(),
                  });
                });
                Navigator.pop(ctx);
                showCartzyFlash(context, 'New address added!');
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4A3E4D),
              foregroundColor: Colors.white,
            ),
            child: const Text('Save Address'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFBF8F9),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ── Top Header Row ──
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Back Button
                        GestureDetector(
                          onTap: widget.onBack,
                          child: Padding(
                            padding: const EdgeInsets.only(bottom: 12),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.arrow_back, size: 16, color: Color(0xFF865561)),
                                const SizedBox(width: 6),
                                Text(
                                  'Back to dashboard',
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                    color: const Color(0xFF865561),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        Text(
                          'YOUR ACCOUNT, YOUR WAY',
                          style: GoogleFonts.dmSans(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 1.2,
                            color: const Color(0xFFA35D6A),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Row(
                          children: [
                            Text(
                              'Account Settings',
                              style: GoogleFonts.dmSans(
                                fontSize: 26,
                                fontWeight: FontWeight.w800,
                                color: const Color(0xFF231F20),
                                letterSpacing: -0.4,
                              ),
                            ),
                            Text(
                              '.',
                              style: GoogleFonts.dmSans(
                                fontSize: 28,
                                fontWeight: FontWeight.w900,
                                color: const Color(0xFF865561),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'A few personal touches. A smoother shopping experience.',
                          style: GoogleFonts.dmSans(
                            fontSize: 13,
                            color: const Color(0xFF7A6E72),
                          ),
                        ),
                      ],
                    ),
                  ),
                  // Shield security icon container
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF3E7E9),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Icon(
                      Icons.shield_outlined,
                      color: Color(0xFF865561),
                      size: 22,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // ── Tab Navigation Pills (Screenshot 2: 01, 02, 03) ──
              _buildTabPills(),
              const SizedBox(height: 24),

              // ── Tab Content ──
              if (_activeTabIndex == 0) _buildPersonalDetailsTab(),
              if (_activeTabIndex == 1) _buildSecurityTab(),
              if (_activeTabIndex == 2) _buildAddressesTab(),
              const SizedBox(height: 30),
            ],
          ),
        ),
      ),
    );
  }

  // ============================================================
  // TAB NAVIGATION PILLS (01 Personal details | 02 Security | 03 Delivery addresses)
  // ============================================================

  Widget _buildTabPills() {
    return LayoutBuilder(
      builder: (context, constraints) {
        return SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              _tabPill(
                index: 0,
                icon: Icons.person_outline,
                title: 'Personal details',
                stepNum: '01',
              ),
              const SizedBox(width: 10),
              _tabPill(
                index: 1,
                icon: Icons.lock_outline,
                title: 'Security',
                stepNum: '02',
              ),
              const SizedBox(width: 10),
              _tabPill(
                index: 2,
                icon: Icons.location_on_outlined,
                title: 'Delivery addresses',
                stepNum: '03',
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _tabPill({
    required int index,
    required IconData icon,
    required String title,
    required String stepNum,
  }) {
    final isActive = _activeTabIndex == index;
    return GestureDetector(
      onTap: () => setState(() => _activeTabIndex = index),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: isActive ? Colors.white : const Color(0xFFF3EDEE),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isActive ? const Color(0xFFE5D7D7) : Colors.transparent,
            width: 1.2,
          ),
          boxShadow: isActive
              ? [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  )
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 17,
              color: isActive ? const Color(0xFF231F20) : const Color(0xFF7A6E72),
            ),
            const SizedBox(width: 8),
            Text(
              title,
              style: GoogleFonts.dmSans(
                fontSize: 13,
                fontWeight: isActive ? FontWeight.w700 : FontWeight.w500,
                color: isActive ? const Color(0xFF231F20) : const Color(0xFF7A6E72),
              ),
            ),
            const SizedBox(width: 12),
            Text(
              stepNum,
              style: GoogleFonts.dmSans(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: isActive ? const Color(0xFF865561) : const Color(0xFFAAA0A2),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ============================================================
  // TAB 1: PERSONAL DETAILS (Screenshot 2)
  // ============================================================

  Widget _buildPersonalDetailsTab() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFEADBD9)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Section Eyebrow
          Text(
            '01 / PROFILE',
            style: GoogleFonts.dmSans(
              fontSize: 10.5,
              fontWeight: FontWeight.w700,
              letterSpacing: 1.0,
              color: const Color(0xFFA35D6A),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Profile information',
            style: GoogleFonts.dmSans(
              fontSize: 18,
              fontWeight: FontWeight.w800,
              color: const Color(0xFF231F20),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            'Make yourself at home. Keep your name and contact details up to date.',
            style: GoogleFonts.dmSans(fontSize: 12.5, color: const Color(0xFF7A6E72)),
          ),
          const SizedBox(height: 20),

          // ── Profile Photo Section ──
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFFDFBFB),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFF0E5E7)),
            ),
            child: Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.network(
                    _avatarUrl,
                    width: 52,
                    height: 52,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      width: 52,
                      height: 52,
                      color: const Color(0xFF865561),
                      child: const Icon(Icons.person, color: Colors.white),
                    ),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Profile photo',
                        style: GoogleFonts.dmSans(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF231F20),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          GestureDetector(
                            onTap: _pickProfilePhoto,
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(color: const Color(0xFFD0C3C5)),
                              ),
                              child: Text(
                                'Choose File',
                                style: GoogleFonts.dmSans(
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w600,
                                  color: const Color(0xFF231F20),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              _profilePhotoName ?? 'No file chosen',
                              style: GoogleFonts.dmSans(fontSize: 11.5, color: const Color(0xFF7A6E72)),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'JPG or PNG, up to 2 MB.',
                        style: GoogleFonts.dmSans(fontSize: 11, color: const Color(0xFFAAA0A2)),
                      ),
                    ],
                  ),
                ),
                GestureDetector(
                  onTap: _pickProfilePhoto,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFE5D7D7)),
                    ),
                    child: Text(
                      'Upload photo',
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFF231F20),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),

          // ── Full Name & Phone Number ──
          _buildInputLabel('Full name'),
          _buildTextField(controller: _nameController, hint: 'Full name'),
          const SizedBox(height: 14),

          _buildInputLabel('Phone number'),
          _buildTextField(controller: _phoneController, hint: 'e.g. 09171234567', keyboardType: TextInputType.phone),
          const SizedBox(height: 14),

          // ── Email address (read-only with Google connected note) ──
          _buildInputLabel('Email address'),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: const Color(0xFFF7F2F3),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFE5D7D7)),
            ),
            child: Text(
              _emailController.text,
              style: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFF5A5255)),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Connected to your Google account.',
            style: GoogleFonts.dmSans(fontSize: 11.5, color: const Color(0xFFAAA0A2)),
          ),
          const SizedBox(height: 14),

          // ── Sex & Birthday ──
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildInputLabel('Sex'),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFE5D7D7)),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          value: _selectedSex,
                          isExpanded: true,
                          items: ['Female', 'Male', 'Prefer not to say'].map((s) {
                            return DropdownMenuItem(value: s, child: Text(s, style: GoogleFonts.dmSans(fontSize: 13.5)));
                          }).toList(),
                          onChanged: (v) {
                            if (v != null) setState(() => _selectedSex = v);
                          },
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildInputLabel('Birthday'),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFE5D7D7)),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              _birthday,
                              style: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFF231F20)),
                            ),
                          ),
                          const Icon(Icons.calendar_today_outlined, size: 16, color: Color(0xFF865561)),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            'You must be at least 18 years old.',
            style: GoogleFonts.dmSans(fontSize: 11.5, color: const Color(0xFFAAA0A2)),
          ),
          const SizedBox(height: 24),

          // ── IDENTITY VERIFICATION SECTION (Screenshot 2) ──
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'IDENTITY VERIFICATION',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 0.8,
                  color: const Color(0xFF231F20),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFF7F2F3),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  'Unverified',
                  style: GoogleFonts.dmSans(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF7A6E72),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            'Upload a clear photo or copy of a valid government ID (National ID, Driver\'s License, Passport, etc.) to verify your account.',
            style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72), height: 1.4),
          ),
          const SizedBox(height: 14),

          // ID Type & ID Number
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildInputLabel('ID Type'),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFE5D7D7)),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          value: _selectedIdType,
                          isExpanded: true,
                          items: ['National ID', 'Driver\'s License', 'Passport', 'UMID', 'Postal ID'].map((t) {
                            return DropdownMenuItem(value: t, child: Text(t, style: GoogleFonts.dmSans(fontSize: 13)));
                          }).toList(),
                          onChanged: (v) {
                            if (v != null) setState(() => _selectedIdType = v);
                          },
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildInputLabel('ID Number (Optional)'),
                    _buildTextField(controller: _idNumberController, hint: 'e.g. 1234-5678-9012'),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Upload ID Document
          _buildInputLabel('Upload ID Document'),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFE5D7D7)),
            ),
            child: Row(
              children: [
                GestureDetector(
                  onTap: _pickIdDocument,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF7F2F3),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFD0C3C5)),
                    ),
                    child: Text(
                      'Choose File',
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFF231F20),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    _idFileName ?? 'No file chosen',
                    style: GoogleFonts.dmSans(fontSize: 12.5, color: const Color(0xFF7A6E72)),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          // ── Bottom Save Changes Bar ──
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  'Keep your details up to date for easy deliveries.',
                  style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFFAAA0A2)),
                ),
              ),
              const SizedBox(width: 12),
              GestureDetector(
                onTap: _savePersonalDetails,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 12),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF4A3E4D), Color(0xFF865561)],
                      begin: Alignment.centerLeft,
                      end: Alignment.centerRight,
                    ),
                    borderRadius: BorderRadius.circular(10),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF865561).withValues(alpha: 0.25),
                        offset: const Offset(0, 4),
                        blurRadius: 8,
                      ),
                    ],
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Save changes',
                        style: GoogleFonts.dmSans(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w600,
                          color: Colors.white,
                        ),
                      ),
                      const SizedBox(width: 6),
                      const Icon(Icons.arrow_forward, size: 14, color: Colors.white),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ============================================================
  // TAB 2: SECURITY (Screenshot 3)
  // ============================================================

  Widget _buildSecurityTab() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFEADBD9)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            '02 / SECURITY',
            style: GoogleFonts.dmSans(
              fontSize: 10.5,
              fontWeight: FontWeight.w700,
              letterSpacing: 1.0,
              color: const Color(0xFFA35D6A),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Security',
            style: GoogleFonts.dmSans(
              fontSize: 18,
              fontWeight: FontWeight.w800,
              color: const Color(0xFF231F20),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            'A little peace of mind. Manage your password, verification, and signed-in devices.',
            style: GoogleFonts.dmSans(fontSize: 12.5, color: const Color(0xFF7A6E72)),
          ),
          const SizedBox(height: 20),

          // ── Email Verification Status ──
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFFDFBFB),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFF0E5E7)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Email verification',
                      style: GoogleFonts.dmSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF231F20),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE8F5E9),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        'Verified',
                        style: GoogleFonts.dmSans(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF2E7D32),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  _emailController.text,
                  style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF5A5255)),
                ),
                const SizedBox(height: 8),
                Text(
                  'You can sign in with Google. If you have not set a password, sign out and choose Forgot password on the login screen to set one before using these controls.',
                  style: GoogleFonts.dmSans(fontSize: 11.5, color: const Color(0xFF7A6E72), height: 1.4),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // ── Change Password ──
          Text(
            'Change password',
            style: GoogleFonts.dmSans(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF231F20),
            ),
          ),
          const SizedBox(height: 10),
          _buildInputLabel('Current password'),
          _buildTextField(controller: _currentPasswordController, hint: 'Current password', obscure: true),
          const SizedBox(height: 12),

          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildInputLabel('New password'),
                    _buildTextField(controller: _newPasswordController, hint: 'New password', obscure: true),
                  ],
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildInputLabel('Confirm new password'),
                    _buildTextField(controller: _confirmPasswordController, hint: 'Confirm password', obscure: true),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            'Use at least 8 characters.',
            style: GoogleFonts.dmSans(fontSize: 11.5, color: const Color(0xFFAAA0A2)),
          ),
          const SizedBox(height: 12),
          GestureDetector(
            onTap: () {
              if (_newPasswordController.text.length < 8) {
                showCartzyFlash(context, 'Password must be at least 8 characters.');
              } else if (_newPasswordController.text != _confirmPasswordController.text) {
                showCartzyFlash(context, 'New passwords do not match.');
              } else {
                showCartzyFlash(context, 'Password updated successfully!');
                _currentPasswordController.clear();
                _newPasswordController.clear();
                _confirmPasswordController.clear();
              }
            },
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 11),
              decoration: BoxDecoration(
                color: const Color(0xFF4A3E4D),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                'Update password',
                style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w600, color: Colors.white),
              ),
            ),
          ),
          const SizedBox(height: 24),

          // ── Other Devices ──
          Text(
            'Other devices',
            style: GoogleFonts.dmSans(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: const Color(0xFF231F20),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Sign out of your other sessions. You will stay signed in on this device.',
            style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFF7A6E72)),
          ),
          const SizedBox(height: 10),
          _buildInputLabel('Confirm your password'),
          _buildTextField(controller: _confirmSessionPasswordController, hint: 'Confirm password', obscure: true),
          const SizedBox(height: 12),
          GestureDetector(
            onTap: () {
              showCartzyFlash(context, 'Signed out of other devices.');
              _confirmSessionPasswordController.clear();
            },
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 11),
              decoration: BoxDecoration(
                color: const Color(0xFF4A3E4D),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                'Sign out other devices',
                style: GoogleFonts.dmSans(fontSize: 13, fontWeight: FontWeight.w600, color: Colors.white),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // TAB 3: DELIVERY ADDRESSES (Screenshot 3)
  // ============================================================

  Widget _buildAddressesTab() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFEADBD9)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Saved addresses',
            style: GoogleFonts.dmSans(
              fontSize: 18,
              fontWeight: FontWeight.w800,
              color: const Color(0xFF231F20),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            'Add delivery locations and choose the one to use by default.',
            style: GoogleFonts.dmSans(fontSize: 12.5, color: const Color(0xFF7A6E72)),
          ),
          const SizedBox(height: 20),

          if (_savedAddresses.isEmpty)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
              decoration: BoxDecoration(
                color: const Color(0xFFFAF7F8),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFF0E5E7)),
              ),
              alignment: Alignment.center,
              child: Text(
                'No saved addresses yet. Add your first delivery address below.',
                style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF7A6E72)),
                textAlign: TextAlign.center,
              ),
            )
          else
            ..._savedAddresses.map((addr) => Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFAF7F8),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE5D7D7)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.location_on_outlined, color: Color(0xFF865561)),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(addr['label'] ?? 'Home', style: GoogleFonts.dmSans(fontWeight: FontWeight.w700, fontSize: 13.5)),
                            Text(addr['street'] ?? '', style: GoogleFonts.dmSans(fontSize: 12.5, color: const Color(0xFF7A6E72))),
                            Text(addr['city'] ?? '', style: GoogleFonts.dmSans(fontSize: 12, color: const Color(0xFFAAA0A2))),
                          ],
                        ),
                      ),
                    ],
                  ),
                )),

          const SizedBox(height: 16),
          GestureDetector(
            onTap: _showAddAddressDialog,
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFD0C3C5), style: BorderStyle.solid),
              ),
              alignment: Alignment.center,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.add, size: 16, color: Color(0xFF865561)),
                  const SizedBox(width: 6),
                  Text(
                    '+ Add a new address',
                    style: GoogleFonts.dmSans(
                      fontSize: 13.5,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFF865561),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Helper Widgets ──

  Widget _buildInputLabel(String label) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Text(
        label,
        style: GoogleFonts.dmSans(
          fontSize: 13,
          fontWeight: FontWeight.w600,
          color: const Color(0xFF231F20),
        ),
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    bool obscure = false,
    TextInputType keyboardType = TextInputType.text,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE5D7D7)),
      ),
      child: TextField(
        controller: controller,
        obscureText: obscure,
        keyboardType: keyboardType,
        style: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFF231F20)),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: GoogleFonts.dmSans(fontSize: 13.5, color: const Color(0xFFAAA0A2)),
          border: InputBorder.none,
          enabledBorder: InputBorder.none,
          focusedBorder: InputBorder.none,
          errorBorder: InputBorder.none,
          disabledBorder: InputBorder.none,
          filled: false,
          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        ),
      ),
    );
  }
}

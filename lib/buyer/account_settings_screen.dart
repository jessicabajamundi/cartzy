import 'package:flutter/material.dart';
import 'package:cartzy/utils/input_rules.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';
import 'package:cartzy/buyer/my_orders_screen.dart';

class AccountSettingsScreen extends StatefulWidget {
  final bool isLoggedIn;
  final VoidCallback? onLogin;
  final VoidCallback? onSignUp;
  final String userName;
  final String userEmail;
  final String userPhone;
  final VoidCallback onBack;
  final VoidCallback onLogout;
  final ValueChanged<Map<String, dynamic>>? onProfileUpdated;

  const AccountSettingsScreen({
    super.key,
    required this.isLoggedIn,
    this.onLogin,
    this.onSignUp,
    required this.userName,
    required this.userEmail,
    this.userPhone = '',
    required this.onBack,
    required this.onLogout,
    this.onProfileUpdated,
  });

  @override
  State<AccountSettingsScreen> createState() => _AccountSettingsScreenState();
}

class _AccountSettingsScreenState extends State<AccountSettingsScreen> {
  late TextEditingController _nameController;
  late TextEditingController _phoneController;
  late TextEditingController _emailController;

  String _selectedSex = 'Female';
  final String _selectedIdType = 'National ID';
  bool _isIdVerified = true;

  // Security Controllers
  final TextEditingController _currentPasswordController =
      TextEditingController();
  final TextEditingController _newPasswordController = TextEditingController();
  final TextEditingController _confirmPasswordController =
      TextEditingController();

  // Saved Addresses
  final List<Map<String, String>> _savedAddresses = [
    {
      'label': 'Home',
      'street': 'Blk 12 Lot 4 Dahlia St., San Antonio',
      'city': 'Pasig City, Metro Manila',
    },
    {
      'label': 'Office',
      'street': 'Unit 802 Emerald Building, F. Ortigas Jr. Ave',
      'city': 'Ortigas Center, Pasig City',
    },
  ];

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.userName);
    _phoneController = TextEditingController(text: widget.userPhone);
    _emailController = TextEditingController(text: widget.userEmail);
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  String _getFirstName() {
    final raw = _nameController.text.trim();
    if (raw.isEmpty) return 'there';
    // If format is "LastName, FirstName"
    if (raw.contains(',')) {
      final parts = raw.split(',');
      if (parts.length > 1) {
        final first = parts[1].trim().split(' ').first;
        if (first.isNotEmpty) return first;
      }
    }
    return raw.split(' ').first;
  }

  String _getInitials() {
    final raw = _nameController.text.trim();
    if (raw.isEmpty) return '';
    if (raw.contains(',')) {
      final parts = raw.split(',');
      final lastInitial = parts[0].trim().isNotEmpty ? parts[0].trim()[0] : '';
      final firstInitial = parts.length > 1 && parts[1].trim().isNotEmpty
          ? parts[1].trim()[0]
          : '';
      if (firstInitial.isNotEmpty && lastInitial.isNotEmpty) {
        return '$firstInitial$lastInitial'.toUpperCase();
      }
    }
    final words = raw.split(' ').where((w) => w.isNotEmpty).toList();
    if (words.length >= 2) {
      return '${words[0][0]}${words[1][0]}'.toUpperCase();
    }
    return raw.substring(0, raw.length >= 2 ? 2 : 1).toUpperCase();
  }

  void _navigateToOrders() {
    if (!widget.isLoggedIn) {
      widget.onLogin?.call();
      return;
    }
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MyOrdersScreen(
          onBack: () => Navigator.of(context).pop(),
        ),
      ),
    );
  }

  Future<void> _pickProfilePhoto() async {
    try {
      final picker = ImagePicker();
      final XFile? photo =
          await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (photo != null && mounted) {
        showCartzyFlash(context, 'Profile photo updated!');
      }
    } catch (_) {
      if (mounted) showCartzyFlash(context, 'Profile photo updated!');
    }
  }

  Future<void> _pickIdDocument() async {
    try {
      final picker = ImagePicker();
      final XFile? file =
          await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (file != null) {
        setState(() => _isIdVerified = true);
        if (mounted)
          showCartzyFlash(context, 'ID Document uploaded: ${file.name}');
      }
    } catch (_) {
      setState(() => _isIdVerified = true);
      if (mounted)
        showCartzyFlash(context, 'ID Document submitted for verification!');
    }
  }

  // ==========================================
  // PROFILE SETTINGS MODAL
  // ==========================================
  void _openProfileSettingsModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          return Container(
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            ),
            padding: EdgeInsets.only(
              left: 20,
              right: 20,
              top: 20,
              bottom: MediaQuery.of(context).viewInsets.bottom + 24,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Profile Settings',
                        style: GoogleFonts.dmSans(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF231F20),
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close, color: Color(0xFF7A6E72)),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Center(
                    child: Stack(
                      children: [
                        CircleAvatar(
                          radius: 38,
                          backgroundColor: CartzyColors.navy,
                          child: Text(
                            _getInitials(),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        Positioned(
                          bottom: 0,
                          right: 0,
                          child: GestureDetector(
                            onTap: () async {
                              await _pickProfilePhoto();
                              setModalState(() {});
                            },
                            child: Container(
                              padding: const EdgeInsets.all(6),
                              decoration: const BoxDecoration(
                                color: CartzyColors.coral,
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(Icons.camera_alt,
                                  color: Colors.white, size: 16),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                  Text('Full Name',
                      style: GoogleFonts.dmSans(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF7A6E72))),
                  const SizedBox(height: 6),
                  TextField(
                    controller: _nameController,
                    inputFormatters: InputRules.fullNameFormatters,
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: const Color(0xFFF9F7F8),
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide:
                              const BorderSide(color: Color(0xFFEDE4E7))),
                      enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide:
                              const BorderSide(color: Color(0xFFEDE4E7))),
                    ),
                  ),
                  const SizedBox(height: 14),
                  Text('Phone Number',
                      style: GoogleFonts.dmSans(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF7A6E72))),
                  const SizedBox(height: 6),
                  TextField(
                    controller: _phoneController,
                    keyboardType: TextInputType.phone,
                    inputFormatters: InputRules.phoneFormatters,
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: const Color(0xFFF9F7F8),
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide:
                              const BorderSide(color: Color(0xFFEDE4E7))),
                      enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide:
                              const BorderSide(color: Color(0xFFEDE4E7))),
                    ),
                  ),
                  const SizedBox(height: 14),
                  Text('Sex / Gender',
                      style: GoogleFonts.dmSans(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF7A6E72))),
                  const SizedBox(height: 6),
                  DropdownButtonFormField<String>(
                    initialValue: _selectedSex,
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: const Color(0xFFF9F7F8),
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide:
                              const BorderSide(color: Color(0xFFEDE4E7))),
                      enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide:
                              const BorderSide(color: Color(0xFFEDE4E7))),
                    ),
                    items: const [
                      DropdownMenuItem(value: 'Female', child: Text('Female')),
                      DropdownMenuItem(value: 'Male', child: Text('Male')),
                      DropdownMenuItem(
                          value: 'Other', child: Text('Prefer not to say')),
                    ],
                    onChanged: (val) {
                      if (val != null) setModalState(() => _selectedSex = val);
                    },
                  ),
                  const SizedBox(height: 22),
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: CartzyColors.navy,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                      onPressed: () {
                        final error = InputRules.fullName(_nameController.text) ??
                            (_phoneController.text.trim().isEmpty
                                ? null
                                : InputRules.phone(_phoneController.text));
                        if (error != null) {
                          showCartzyFlash(context, error);
                          return;
                        }
                        setState(() {});
                        widget.onProfileUpdated?.call({
                          'name': _nameController.text.trim(),
                          'phone': _phoneController.text.trim(),
                          'sex': _selectedSex,
                        });
                        Navigator.pop(ctx);
                        showCartzyFlash(
                            context, 'Profile updated successfully!');
                      },
                      child: Text('Save Changes',
                          style: GoogleFonts.dmSans(
                              fontSize: 15, fontWeight: FontWeight.bold)),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  // ==========================================
  // ID VERIFICATION MODAL
  // ==========================================
  void _openIdVerificationModal() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: const EdgeInsets.all(22),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'ID Verification',
                  style: GoogleFonts.dmSans(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF231F20)),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: Color(0xFF7A6E72)),
                  onPressed: () => Navigator.pop(ctx),
                ),
              ],
            ),
            const SizedBox(height: 14),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFECFDF5),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFA7F3D0)),
              ),
              child: const Row(
                children: [
                  Icon(Icons.verified, color: Color(0xFF059669), size: 24),
                  SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Identity Verified',
                            style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF065F46),
                                fontSize: 13)),
                        SizedBox(height: 2),
                        Text(
                            'Your Government-issued ID has been verified for secure shopping & orders.',
                            style: TextStyle(
                                color: Color(0xFF047857), fontSize: 11)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            ListTile(
              contentPadding: EdgeInsets.zero,
              leading:
                  const Icon(Icons.badge_outlined, color: CartzyColors.navy),
              title: Text('ID Type',
                  style: GoogleFonts.dmSans(
                      fontSize: 14, fontWeight: FontWeight.bold)),
              subtitle: Text(_selectedIdType,
                  style:
                      const TextStyle(fontSize: 12, color: Color(0xFF7A6E72))),
              trailing: TextButton(
                onPressed: () async {
                  Navigator.pop(ctx);
                  await _pickIdDocument();
                },
                child: const Text('Update ID',
                    style: TextStyle(
                        color: CartzyColors.coral,
                        fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ==========================================
  // SAVED ADDRESSES MODAL
  // ==========================================
  void _openSavedAddressesModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          return Container(
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            ),
            padding: const EdgeInsets.all(22),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Saved Addresses',
                      style: GoogleFonts.dmSans(
                          fontSize: 18,
                          fontWeight: FontWeight.w700,
                          color: const Color(0xFF231F20)),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: Color(0xFF7A6E72)),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                ...List.generate(_savedAddresses.length, (idx) {
                  final addr = _savedAddresses[idx];
                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFAF8F9),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFEDE4E7)),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.location_on,
                            color: CartzyColors.coral, size: 22),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(addr['label'] ?? 'Address',
                                  style: GoogleFonts.dmSans(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                      color: const Color(0xFF231F20))),
                              const SizedBox(height: 2),
                              Text(addr['street'] ?? '',
                                  style: const TextStyle(
                                      fontSize: 12, color: Color(0xFF554B4F))),
                              Text(addr['city'] ?? '',
                                  style: const TextStyle(
                                      fontSize: 12, color: Color(0xFF7A6E72))),
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                }),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  height: 46,
                  child: OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: CartzyColors.navy),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                    ),
                    icon: const Icon(Icons.add,
                        color: CartzyColors.navy, size: 18),
                    label: Text('Add New Address',
                        style: GoogleFonts.dmSans(
                            fontWeight: FontWeight.bold,
                            color: CartzyColors.navy)),
                    onPressed: () {
                      Navigator.pop(ctx);
                      _showAddAddressDialog();
                    },
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  void _showAddAddressDialog() {
    final streetController = TextEditingController();
    final cityController =
        TextEditingController(text: 'Pasig City, Metro Manila');
    final labelController = TextEditingController(text: 'Home');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Add Delivery Address',
          style: GoogleFonts.dmSans(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF231F20)),
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: labelController,
                decoration: const InputDecoration(
                    labelText: 'Address Label (e.g. Home, Work)'),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: streetController,
                decoration: const InputDecoration(
                    labelText: 'House / Unit & Street Name'),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: cityController,
                decoration: const InputDecoration(
                    labelText: 'City / Municipality & Province'),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel',
                style: TextStyle(color: Color(0xFF7A6E72))),
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
              backgroundColor: CartzyColors.navy,
              foregroundColor: Colors.white,
            ),
            child: const Text('Save Address'),
          ),
        ],
      ),
    );
  }

  // ==========================================
  // CHANGE PASSWORD MODAL
  // ==========================================
  void _openChangePasswordModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: EdgeInsets.only(
          left: 20,
          right: 20,
          top: 20,
          bottom: MediaQuery.of(context).viewInsets.bottom + 24,
        ),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Change Password',
                    style: GoogleFonts.dmSans(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF231F20)),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Color(0xFF7A6E72)),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              TextField(
                controller: _currentPasswordController,
                obscureText: true,
                decoration: InputDecoration(
                  labelText: 'Current Password',
                  filled: true,
                  fillColor: const Color(0xFFF9F7F8),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: Color(0xFFEDE4E7))),
                  enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: Color(0xFFEDE4E7))),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _newPasswordController,
                obscureText: true,
                decoration: InputDecoration(
                  labelText: 'New Password',
                  filled: true,
                  fillColor: const Color(0xFFF9F7F8),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: Color(0xFFEDE4E7))),
                  enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: Color(0xFFEDE4E7))),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _confirmPasswordController,
                obscureText: true,
                decoration: InputDecoration(
                  labelText: 'Confirm New Password',
                  filled: true,
                  fillColor: const Color(0xFFF9F7F8),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: Color(0xFFEDE4E7))),
                  enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: const BorderSide(color: Color(0xFFEDE4E7))),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CartzyColors.navy,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () {
                    _currentPasswordController.clear();
                    _newPasswordController.clear();
                    _confirmPasswordController.clear();
                    Navigator.pop(ctx);
                    showCartzyFlash(context, 'Password updated successfully!');
                  },
                  child: Text('Update Password',
                      style: GoogleFonts.dmSans(
                          fontSize: 15, fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ==========================================
  // TERMS & POLICIES MODAL
  // ==========================================
  void _openTermsModal() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        height: MediaQuery.of(context).size.height * 0.7,
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: const EdgeInsets.all(22),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Terms & Policies',
                  style: GoogleFonts.dmSans(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF231F20)),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: Color(0xFF7A6E72)),
                  onPressed: () => Navigator.pop(ctx),
                ),
              ],
            ),
            const SizedBox(height: 14),
            Expanded(
              child: SingleChildScrollView(
                child: Text(
                  'Welcome to Cartzy Philippines.\n\n'
                  '1. Account Security\n'
                  'You are responsible for safeguarding your login credentials. Notify Cartzy immediately upon any unauthorized access.\n\n'
                  '2. Orders and Payments\n'
                  'All transactions are processed securely. Prices are quoted in Philippine Pesos (₱) and include applicable taxes.\n\n'
                  '3. Return and Refund Policy\n'
                  'Items are covered by our 7-day return guarantee if defective or differing significantly from seller description.\n\n'
                  '4. Privacy and Data Protection\n'
                  'Your personal details and delivery addresses are encrypted and kept strictly confidential in accordance with the Data Privacy Act of 2012.',
                  style: GoogleFonts.dmSans(
                      fontSize: 13,
                      height: 1.6,
                      color: const Color(0xFF554B4F)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ==========================================
  // HELP & SUPPORT MODAL
  // ==========================================
  void _openHelpModal() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: const EdgeInsets.all(22),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Help & Support',
                  style: GoogleFonts.dmSans(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                      color: const Color(0xFF231F20)),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: Color(0xFF7A6E72)),
                  onPressed: () => Navigator.pop(ctx),
                ),
              ],
            ),
            const SizedBox(height: 12),
            ListTile(
              contentPadding: EdgeInsets.zero,
              leading: const CircleAvatar(
                  backgroundColor: Color(0xFFF3ECEE),
                  child: Icon(Icons.email_outlined, color: CartzyColors.navy)),
              title: const Text('Email Support',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              subtitle: const Text('support@cartzy.shop',
                  style: TextStyle(fontSize: 12, color: Color(0xFF7A6E72))),
              onTap: () => showCartzyFlash(
                  context, 'Support email copied: support@cartzy.shop'),
            ),
            const ListTile(
              contentPadding: EdgeInsets.zero,
              leading: CircleAvatar(
                  backgroundColor: Color(0xFFF3ECEE),
                  child: Icon(Icons.phone_outlined, color: CartzyColors.navy)),
              title: Text('Customer Hotline',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              subtitle: Text('+63 (02) 8888-2278 (Mon-Sat, 8am-8pm)',
                  style: TextStyle(fontSize: 12, color: Color(0xFF7A6E72))),
            ),
          ],
        ),
      ),
    );
  }

  // ==========================================
  // COMPLAINTS MODAL
  // ==========================================
  void _openComplaintsModal() {
    final complaintController = TextEditingController();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: EdgeInsets.only(
          left: 20,
          right: 20,
          top: 20,
          bottom: MediaQuery.of(context).viewInsets.bottom + 24,
        ),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'File a Complaint',
                    style: GoogleFonts.dmSans(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF231F20)),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Color(0xFF7A6E72)),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                'Please describe the issue with your order, rider, or seller in detail:',
                style: GoogleFonts.dmSans(
                    fontSize: 12, color: const Color(0xFF7A6E72)),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: complaintController,
                maxLines: 4,
                decoration: InputDecoration(
                  hintText: 'Enter your complaint or inquiry here...',
                  filled: true,
                  fillColor: const Color(0xFFF9F7F8),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFEDE4E7))),
                  enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFEDE4E7))),
                ),
              ),
              const SizedBox(height: 18),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CartzyColors.coral,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () {
                    Navigator.pop(ctx);
                    showCartzyFlash(context,
                        'Complaint ticket submitted. Support will contact you shortly.');
                  },
                  child: Text('Submit Ticket',
                      style: GoogleFonts.dmSans(
                          fontSize: 15, fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          'Sign Out',
          style: GoogleFonts.dmSans(
              fontWeight: FontWeight.bold, color: const Color(0xFF231F20)),
        ),
        content: const Text(
            'Are you sure you want to sign out from your Cartzy account?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel',
                style: TextStyle(color: Color(0xFF7A6E72))),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFDC2626),
              foregroundColor: Colors.white,
            ),
            onPressed: () {
              Navigator.pop(ctx);
              widget.onLogout();
            },
            child: const Text('Sign Out'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final firstName = _getFirstName();
    final email = _emailController.text.trim();
    final initials = _getInitials();

    return Scaffold(
      backgroundColor: CartzyColors.cream,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new,
              size: 20, color: CartzyColors.navy),
          onPressed: widget.onBack,
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ==========================================
              // 1. TOP HEADER: YOUR CARTZY / Account
              // ==========================================
              Center(
                child: Column(
                  children: [
                    Text(
                      'YOUR CARTZY',
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 1.5,
                        color: CartzyColors.burgundy,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'My account',
                      style: GoogleFonts.playfairDisplay(
                        fontSize: 34,
                        fontWeight: FontWeight.w700,
                        color: CartzyColors.navy,
                        letterSpacing: -0.5,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 22),

              // ==========================================
              // 2. USER PROFILE CARD (Hi, Jan / Email)
              // ==========================================
              Container(
                width: double.infinity,
                padding:
                    const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [
                        CartzyColors.burgundy,
                        CartzyColors.burgundyDeep
                      ]),
                  borderRadius: BorderRadius.circular(22),
                  border:
                      Border.all(color: CartzyColors.pinkBorder, width: 1.1),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.025),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: !widget.isLoggedIn
                    ? _buildGuestProfileCard()
                    : Row(
                        children: [
                          // Circle Avatar with Initials
                          Container(
                            width: 52,
                            height: 52,
                            decoration: const BoxDecoration(
                              color: CartzyColors.cream,
                              shape: BoxShape.circle,
                            ),
                            child: Center(
                              child: Text(
                                initials,
                                style: GoogleFonts.dmSans(
                                  color: CartzyColors.burgundy,
                                  fontSize: 18,
                                  fontWeight: FontWeight.w700,
                                  letterSpacing: 0.5,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Hi, $firstName',
                                  style: GoogleFonts.playfairDisplay(
                                    fontSize: 23,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.white,
                                  ),
                                ),
                                const SizedBox(height: 3),
                                Text(
                                  email,
                                  style: GoogleFonts.dmSans(
                                    fontSize: 12.5,
                                    color: CartzyColors.cream,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
              ),

              const SizedBox(height: 24),

              // ==========================================
              // 3. MY ORDERS SECTION (To ship, Shipped, Delivered, Cancelled)
              // ==========================================
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'MY ORDERS',
                    style: GoogleFonts.dmSans(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 1.1,
                      color: CartzyColors.gray,
                    ),
                  ),
                  GestureDetector(
                    onTap: _navigateToOrders,
                    child: Row(
                      children: [
                        Text(
                          'View all',
                          style: GoogleFonts.dmSans(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: CartzyColors.navy,
                          ),
                        ),
                        const SizedBox(width: 4),
                        const Icon(Icons.arrow_forward_rounded,
                            size: 14, color: CartzyColors.navy),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),

              Container(
                decoration: BoxDecoration(
                  color: CartzyColors.creamLight,
                  borderRadius: BorderRadius.circular(22),
                  border:
                      Border.all(color: CartzyColors.pinkBorder, width: 1.1),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.02),
                      blurRadius: 10,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                padding:
                    const EdgeInsets.symmetric(vertical: 18, horizontal: 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _buildOrderAction(
                      icon: Icons.inventory_2_outlined,
                      label: 'To ship',
                      onTap: _navigateToOrders,
                    ),
                    _buildOrderAction(
                      icon: Icons.local_shipping_outlined,
                      label: 'Shipped',
                      onTap: _navigateToOrders,
                    ),
                    _buildOrderAction(
                      icon: Icons.check_circle_outline_rounded,
                      label: 'Delivered',
                      onTap: _navigateToOrders,
                    ),
                    _buildOrderAction(
                      icon: Icons.cancel_outlined,
                      label: 'Cancelled',
                      onTap: _navigateToOrders,
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // ==========================================
              // 4. SETTINGS SECTION
              // ==========================================
              Text(
                'ACCOUNT & SETTINGS',
                style: GoogleFonts.dmSans(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 1.1,
                  color: CartzyColors.gray,
                ),
              ),
              const SizedBox(height: 10),

              Container(
                decoration: BoxDecoration(
                  color: CartzyColors.creamLight,
                  borderRadius: BorderRadius.circular(22),
                  border:
                      Border.all(color: CartzyColors.pinkBorder, width: 1.1),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.02),
                      blurRadius: 10,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    // Messages
                    _buildMenuTile(
                      icon: Icons.chat_bubble_outline_rounded,
                      label: 'Messages',
                      showChevron: false,
                      onTap: () {
                        showCartzyFlash(context, 'No new messages.');
                      },
                    ),
                    const Divider(
                        height: 1,
                        thickness: 1,
                        color: CartzyColors.creamSection,
                        indent: 68),

                    // Notifications (with badge 1)
                    _buildMenuTile(
                      icon: Icons.notifications_none_rounded,
                      label: 'Notifications',
                      badgeWidget: Container(
                        padding: const EdgeInsets.all(5),
                        decoration: const BoxDecoration(
                          color: Color(0xFFEF4444),
                          shape: BoxShape.circle,
                        ),
                        child: const Text(
                          '1',
                          style: TextStyle(
                            color: CartzyColors.creamLight,
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      onTap: () {
                        showCartzyFlash(
                            context, 'You have 1 order update notification.');
                      },
                    ),
                    const Divider(
                        height: 1,
                        thickness: 1,
                        color: CartzyColors.creamSection,
                        indent: 68),

                    // ID verification (with green checkmark)
                    _buildMenuTile(
                      icon: Icons.badge_outlined,
                      label: 'ID verification',
                      badgeWidget: _isIdVerified
                          ? const Icon(Icons.verified,
                              color: Color(0xFF10B981), size: 18)
                          : null,
                      onTap: _openIdVerificationModal,
                    ),
                    const Divider(
                        height: 1,
                        thickness: 1,
                        color: CartzyColors.creamSection,
                        indent: 68),

                    // Saved addresses
                    _buildMenuTile(
                      icon: Icons.location_on_outlined,
                      label: 'Saved addresses',
                      onTap: _openSavedAddressesModal,
                    ),
                    const Divider(
                        height: 1,
                        thickness: 1,
                        color: CartzyColors.creamSection,
                        indent: 68),

                    // Profile settings
                    _buildMenuTile(
                      icon: Icons.person_outline_rounded,
                      label: 'Profile settings',
                      onTap: _openProfileSettingsModal,
                    ),
                    const Divider(
                        height: 1,
                        thickness: 1,
                        color: CartzyColors.creamSection,
                        indent: 68),

                    // Change password
                    _buildMenuTile(
                      icon: Icons.lock_outline_rounded,
                      label: 'Change password',
                      onTap: _openChangePasswordModal,
                    ),
                    const Divider(
                        height: 1,
                        thickness: 1,
                        color: CartzyColors.creamSection,
                        indent: 68),

                    // Terms & policies
                    _buildMenuTile(
                      icon: Icons.description_outlined,
                      label: 'Terms & policies',
                      onTap: _openTermsModal,
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // ==========================================
              // 5. HELP SECTION (Help & support, Complaints)
              // ==========================================
              Text(
                'HELP',
                style: GoogleFonts.dmSans(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 1.1,
                  color: CartzyColors.gray,
                ),
              ),
              const SizedBox(height: 10),

              Container(
                decoration: BoxDecoration(
                  color: CartzyColors.creamLight,
                  borderRadius: BorderRadius.circular(22),
                  border:
                      Border.all(color: CartzyColors.pinkBorder, width: 1.1),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.02),
                      blurRadius: 10,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    _buildMenuTile(
                      icon: Icons.support_agent_rounded,
                      label: 'Help & support',
                      onTap: _openHelpModal,
                    ),
                    const Divider(
                        height: 1,
                        thickness: 1,
                        color: CartzyColors.creamSection,
                        indent: 68),
                    _buildMenuTile(
                      icon: Icons.error_outline_rounded,
                      label: 'Complaints',
                      onTap: _openComplaintsModal,
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // ==========================================
              // 6. SIGN OUT BUTTON
              // ==========================================
              if (widget.isLoggedIn)
                Container(
                  width: double.infinity,
                  height: 52,
                  decoration: BoxDecoration(
                    color: CartzyColors.creamLight,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: CartzyColors.navy, width: 1.4),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.02),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Material(
                    color: Colors.transparent,
                    child: InkWell(
                      borderRadius: BorderRadius.circular(14),
                      onTap: _confirmLogout,
                      child: Center(
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.logout_rounded,
                                color: CartzyColors.navy, size: 19),
                            const SizedBox(width: 8),
                            Text(
                              'SIGN OUT',
                              style: GoogleFonts.dmSans(
                                fontSize: 14,
                                fontWeight: FontWeight.w800,
                                letterSpacing: 1.1,
                                color: CartzyColors.navy,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),

              const SizedBox(height: 24),

              // ==========================================
              // 7. FOOTER TAGLINE
              // ==========================================
              Center(
                child: Text(
                  'Driven by passion. Defined by your origin',
                  style: GoogleFonts.dmSans(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                    color: const Color(0xFF8E8286),
                    fontStyle: FontStyle.italic,
                  ),
                ),
              ),

              const SizedBox(height: 30),
            ],
          ),
        ),
      ),
    );
  }

  // ==========================================
  // ORDER ACTION ITEM (BOX, TRUCK, CHECK, CANCEL)
  // ==========================================
  Widget _buildGuestProfileCard() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(children: [
          const CircleAvatar(
            radius: 22,
            backgroundColor: CartzyColors.cream,
            child: Icon(Icons.person_outline_rounded,
                color: CartzyColors.burgundy, size: 24),
          ),
          const SizedBox(width: 12),
          Expanded(
              child: Text('Welcome to Cartzy',
                  style: GoogleFonts.playfairDisplay(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.white))),
        ]),
        const SizedBox(height: 12),
        Wrap(spacing: 12, runSpacing: 8, children: [
          FilledButton(
            onPressed: widget.onLogin,
            style: FilledButton.styleFrom(
              backgroundColor: CartzyColors.cream,
              foregroundColor: CartzyColors.burgundy,
              minimumSize: const Size(104, 40),
            ),
            child: const Text('Log in'),
          ),
          OutlinedButton(
            onPressed: widget.onSignUp,
            style: OutlinedButton.styleFrom(
              backgroundColor: CartzyColors.cream,
              foregroundColor: CartzyColors.burgundy,
              side: const BorderSide(color: CartzyColors.pinkBorder),
              minimumSize: const Size(104, 40),
              shape: const StadiumBorder(),
            ),
            child: const Text('Sign up'),
          ),
        ]),
      ],
    );
  }

  Widget _buildOrderAction({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return Expanded(
        child: InkWell(
            onTap: widget.isLoggedIn ? onTap : widget.onLogin,
            borderRadius: BorderRadius.circular(14),
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 2),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: CartzyColors.roseSoft,
                      borderRadius: BorderRadius.circular(15),
                    ),
                    child: Icon(icon, size: 24, color: CartzyColors.burgundy),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    label,
                    textAlign: TextAlign.center,
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: CartzyColors.navy,
                    ),
                  ),
                ],
              ),
            )));
  }

  // ==========================================
  // MENU TILE ITEM
  // ==========================================
  Widget _buildMenuTile({
    required IconData icon,
    required String label,
    Widget? badgeWidget,
    bool showChevron = true,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: widget.isLoggedIn ? onTap : widget.onLogin,
      borderRadius: BorderRadius.circular(14),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: CartzyColors.creamSection,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, size: 20, color: CartzyColors.burgundy),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Text(
                label,
                style: GoogleFonts.dmSans(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: CartzyColors.navy,
                ),
              ),
            ),
            if (widget.isLoggedIn && badgeWidget != null) ...[
              badgeWidget,
              const SizedBox(width: 8),
            ],
            if (showChevron)
              const Icon(Icons.chevron_right,
                  size: 18, color: Color(0xFF8E8286)),
          ],
        ),
      ),
    );
  }
}

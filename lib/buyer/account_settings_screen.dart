import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'dart:io';
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/profile_photo_editor_screen.dart';
import 'package:cartzy/buyer/messages_screen.dart';
import 'package:cartzy/buyer/notifications_screen.dart';
import 'package:cartzy/services/notification_service.dart';
import 'package:cartzy/services/message_service.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';
import 'package:cartzy/buyer/my_orders_screen.dart';
import 'package:cartzy/cart/cart_screen.dart';
import 'package:cartzy/checkout/checkout_screen.dart';
import 'package:cartzy/products/product_list_screen.dart';
import 'package:cartzy/buyer/edit_buyer_profile_screen.dart';
import 'package:cartzy/address/my_addresses_screen.dart';
import 'package:cartzy/address/add_address_screen.dart';

import 'package:cartzy/services/auth_service.dart';
import 'change_password_screen.dart';
import 'package:cartzy/buyer/inbox_screen.dart';
import 'package:cartzy/buyer/account_activity_screen.dart';
import 'package:cartzy/buddy/buddy_chat_screen.dart';
import 'package:cartzy/widgets/buddy_bear.dart';
import 'package:cartzy/buyer/terms_policies_screen.dart';

class AccountSettingsScreen extends StatefulWidget {
  final bool isLoggedIn;
  final VoidCallback? onLogin;
  final VoidCallback? onSignUp;
  final String userName;
  final String userEmail;
  final String userPhone;
  final String? userAvatar;
  final VoidCallback onBack;
  final VoidCallback? onHomeClick;
  final VoidCallback? onCartClick;
  final VoidCallback? onSaleClick;
  final VoidCallback? onInboxClick;
  final VoidCallback? onAddressesClick;
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
    this.userAvatar,
    required this.onBack,
    this.onHomeClick,
    this.onCartClick,
    this.onSaleClick,
    this.onInboxClick,
    this.onAddressesClick,
    required this.onLogout,
    this.onProfileUpdated,
  });

  @override
  State<AccountSettingsScreen> createState() => _AccountSettingsScreenState();
}

class _AccountSettingsScreenState extends State<AccountSettingsScreen> {
  late TextEditingController _nameController;
  String? _profileImagePath;
  bool _isNavVisible = true;
  Timer? _scrollStopTimer;

  bool _onScrollNotification(ScrollNotification notification) {
    // Only the page's own up/down scrolling by the user moves the nav, not
    // the auto-sliding hero banner or other sideways carousels inside it.
    if (notification.depth != 0 ||
        notification.metrics.axis != Axis.vertical) {
      return false;
    }

    if (notification.metrics.pixels <= 20) {
      if (!_isNavVisible) {
        setState(() => _isNavVisible = true);
      }
      return false;
    }

    if (notification is ScrollStartNotification) {
      if (_isNavVisible) {
        setState(() => _isNavVisible = false);
      }
    } else if (notification is ScrollUpdateNotification) {
      final delta = notification.scrollDelta ?? 0;
      if (delta.abs() > 1.0) {
        if (_isNavVisible) {
          setState(() => _isNavVisible = false);
        }
        _scrollStopTimer?.cancel();
        _scrollStopTimer = Timer(const Duration(milliseconds: 400), () {
          if (mounted && !_isNavVisible) {
            setState(() => _isNavVisible = true);
          }
        });
      }
    } else if (notification is ScrollEndNotification) {
      _scrollStopTimer?.cancel();
      if (!_isNavVisible) {
        setState(() => _isNavVisible = true);
      }
    }
    return false;
  }
  late TextEditingController _phoneController;
  late TextEditingController _emailController;

  final String _selectedIdType = 'National ID';
  bool _isIdVerified = true;
  int _unreadMessagesCount = 0;

  Future<void> _loadUnreadMessagesCount() async {
    if (!widget.isLoggedIn) return;
    final count = await MessageService.fetchUnreadCount();
    if (mounted) {
      setState(() => _unreadMessagesCount = count);
    }
  }

  // Security Controllers
  final TextEditingController _currentPasswordController =
      TextEditingController();
  final TextEditingController _newPasswordController = TextEditingController();
  final TextEditingController _confirmPasswordController =
      TextEditingController();


  void _openAddressesScreen() {
    if (widget.onAddressesClick != null) {
      widget.onAddressesClick!();
    } else {
      final currentUserId = int.tryParse(AuthService.currentUser?['id']?.toString() ?? '') ?? 0;
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => MyAddressesScreen(
            userId: currentUserId,
            onBack: () => Navigator.of(context).pop(),
            onAddAddress: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => AddAddressScreen(
                    userId: currentUserId,
                    onBack: () => Navigator.of(context).pop(),
                    onSaved: () {
                      Navigator.of(context).pop();
                                      },
                  ),
                ),
              );
            },
          ),
        ),
      );
    }
  }

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.userName);
    _phoneController = TextEditingController(text: widget.userPhone);
    _emailController = TextEditingController(text: widget.userEmail);
    _profileImagePath = AuthService.profilePhoto({'avatar': widget.userAvatar});
    _loadProfileImage();
    _loadUnreadMessagesCount();
    NotificationService.fetchUnreadCount();
  }

  Future<void> _loadProfileImage() async {
    if (_profileImagePath != null && _profileImagePath!.isNotEmpty) {
      return;
    }
    final currentAvatar = AuthService.profilePhoto(AuthService.currentUser);
    if (currentAvatar != null && currentAvatar.isNotEmpty) {
      if (mounted) setState(() => _profileImagePath = currentAvatar);
      return;
    }
    final email = widget.userEmail.isNotEmpty
        ? widget.userEmail
        : (AuthService.currentUser?['email']?.toString() ?? '');
    if (email.isNotEmpty) {
      try {
        final prefs = await SharedPreferences.getInstance();
        final saved = prefs.getString('cartzy_avatar_$email');
        if (saved != null && saved.isNotEmpty && mounted) {
          setState(() => _profileImagePath = saved);
          final updated = Map<String, dynamic>.from(AuthService.currentUser ?? {});
          updated['avatar'] = saved;
          AuthService.setSession(user: updated, token: AuthService.token);
        }
      } catch (_) {}
    }
  }

  Future<void> _pickProfileImage(ImageSource source) async {
    try {
      final picker = ImagePicker();
      final picked = await picker.pickImage(
        source: source,
        maxWidth: 1600,
        maxHeight: 1600,
        imageQuality: 95,
      );
      if (picked == null) return;
      if (!mounted) return;

      // Navigate to interactive crop/angle editor
      final croppedPath = await Navigator.of(context).push<String>(
        MaterialPageRoute(
          builder: (_) => ProfilePhotoEditorScreen(
            imageFile: File(picked.path),
          ),
        ),
      );

      if (croppedPath == null || !mounted) return;

      setState(() {
        _profileImagePath = croppedPath;
      });

      final email = widget.userEmail.isNotEmpty
          ? widget.userEmail
          : (AuthService.currentUser?['email']?.toString() ?? '');
      if (email.isNotEmpty) {
        try {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('cartzy_avatar_$email', croppedPath);
        } catch (_) {}
      }

      final updated = Map<String, dynamic>.from(AuthService.currentUser ?? {});
      updated['avatar'] = croppedPath;
      AuthService.setSession(user: updated, token: AuthService.token);
      widget.onProfileUpdated?.call(updated);

      if (mounted) {
        showCartzyFlash(context, 'Profile picture updated successfully');
      }
    } catch (e) {
      if (mounted) {
        showCartzyFlash(context, 'Failed to update profile picture: $e');
      }
    }
  }

  Future<void> _editCurrentProfileImage() async {
    if (_profileImagePath == null || _profileImagePath!.isEmpty) return;
    final file = File(_profileImagePath!);
    if (!file.existsSync()) {
      showCartzyFlash(context, 'Image file not found on device.');
      return;
    }

    final croppedPath = await Navigator.of(context).push<String>(
      MaterialPageRoute(
        builder: (_) => ProfilePhotoEditorScreen(
          imageFile: file,
        ),
      ),
    );

    if (croppedPath == null || !mounted) return;

    setState(() {
      _profileImagePath = croppedPath;
    });

    final email = widget.userEmail.isNotEmpty
        ? widget.userEmail
        : (AuthService.currentUser?['email']?.toString() ?? '');
    if (email.isNotEmpty) {
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('cartzy_avatar_$email', croppedPath);
      } catch (_) {}
    }

    final updated = Map<String, dynamic>.from(AuthService.currentUser ?? {});
    updated['avatar'] = croppedPath;
    AuthService.setSession(user: updated, token: AuthService.token);
    widget.onProfileUpdated?.call(updated);

    if (mounted) {
      showCartzyFlash(context, 'Profile picture updated successfully');
    }
  }

  Future<void> _removeProfileImage() async {
    setState(() {
      _profileImagePath = null;
    });

    final email = widget.userEmail.isNotEmpty
        ? widget.userEmail
        : (AuthService.currentUser?['email']?.toString() ?? '');
    if (email.isNotEmpty) {
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.remove('cartzy_avatar_$email');
      } catch (_) {}
    }

    final updated = Map<String, dynamic>.from(AuthService.currentUser ?? {});
    updated.remove('avatar');
    AuthService.setSession(user: updated, token: AuthService.token);
    widget.onProfileUpdated?.call(updated);

    if (mounted) {
      showCartzyFlash(context, 'Profile picture removed');
    }
  }

  void _showImagePickerSheet() {
    if (!widget.isLoggedIn) {
      widget.onLogin?.call();
      return;
    }
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Profile Photo',
                  style: GoogleFonts.playfairDisplay(
                    fontSize: 19,
                    fontWeight: FontWeight.bold,
                    color: CartzyColors.burgundy,
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close_rounded, size: 22, color: Colors.grey),
                  onPressed: () => Navigator.pop(ctx),
                ),
              ],
            ),
            const SizedBox(height: 12),
            ListTile(
              contentPadding: EdgeInsets.zero,
              leading: Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: CartzyColors.cream,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.camera_alt_rounded, color: CartzyColors.burgundy, size: 22),
              ),
              title: Text(
                'Take a photo',
                style: GoogleFonts.dmSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF231F20),
                ),
              ),
              subtitle: Text(
                'Use camera to capture a new picture',
                style: GoogleFonts.dmSans(fontSize: 12.5, color: Colors.grey[600]),
              ),
              onTap: () {
                Navigator.pop(ctx);
                _pickProfileImage(ImageSource.camera);
              },
            ),
            const SizedBox(height: 6),
            ListTile(
              contentPadding: EdgeInsets.zero,
              leading: Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: CartzyColors.cream,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.photo_library_rounded, color: CartzyColors.burgundy, size: 22),
              ),
              title: Text(
                'Choose from gallery',
                style: GoogleFonts.dmSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF231F20),
                ),
              ),
              subtitle: Text(
                'Select an image from device gallery',
                style: GoogleFonts.dmSans(fontSize: 12.5, color: Colors.grey[600]),
              ),
              onTap: () {
                Navigator.pop(ctx);
                _pickProfileImage(ImageSource.gallery);
              },
            ),
            if (_profileImagePath != null && _profileImagePath!.isNotEmpty) ...[
              const SizedBox(height: 6),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: CartzyColors.cream,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.crop_rotate_rounded, color: CartzyColors.burgundy, size: 22),
                ),
                title: Text(
                  'Crop & adjust current photo',
                  style: GoogleFonts.dmSans(
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF231F20),
                  ),
                ),
                subtitle: Text(
                  'Reposition, rotate, or zoom existing photo',
                  style: GoogleFonts.dmSans(fontSize: 12.5, color: Colors.grey[600]),
                ),
                onTap: () {
                  Navigator.pop(ctx);
                  _editCurrentProfileImage();
                },
              ),
              const Divider(height: 20),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.red.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.delete_outline_rounded, color: Colors.red, size: 22),
                ),
                title: Text(
                  'Remove photo',
                  style: GoogleFonts.dmSans(
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                    color: Colors.red,
                  ),
                ),
                subtitle: Text(
                  'Revert to initials avatar',
                  style: GoogleFonts.dmSans(fontSize: 12.5, color: Colors.grey[600]),
                ),
                onTap: () {
                  Navigator.pop(ctx);
                  _removeProfileImage();
                },
              ),
            ],
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  Widget _buildAvatarWidget(String initials, {double size = 56}) {
    final hasImage = _profileImagePath != null && _profileImagePath!.isNotEmpty;
    ImageProvider? imageProvider;
    if (hasImage) {
      if (_profileImagePath!.startsWith('http://') || _profileImagePath!.startsWith('https://')) {
        imageProvider = NetworkImage(_profileImagePath!);
      } else {
        final file = File(_profileImagePath!);
        if (file.existsSync()) {
          imageProvider = FileImage(file);
        }
      }
    }

    return GestureDetector(
      onTap: _showImagePickerSheet,
      behavior: HitTestBehavior.opaque,
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          Container(
            width: size,
            height: size,
            decoration: BoxDecoration(
              color: CartzyColors.cream,
              shape: BoxShape.circle,
              border: Border.all(color: Colors.white, width: 2),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.12),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: ClipOval(
              child: imageProvider != null
                  ? Image(
                      image: imageProvider,
                      width: size,
                      height: size,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => _buildInitialsFallback(initials, size),
                    )
                  : _buildInitialsFallback(initials, size),
            ),
          ),
          Positioned(
            right: -2,
            bottom: -2,
            child: Container(
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(
                color: CartzyColors.cream,
                shape: BoxShape.circle,
                border: Border.all(color: CartzyColors.burgundy, width: 1.5),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.18),
                    blurRadius: 3,
                  ),
                ],
              ),
              child: const Icon(
                Icons.camera_alt_rounded,
                size: 13,
                color: CartzyColors.burgundy,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInitialsFallback(String initials, double size) {
    return Center(
      child: Text(
        initials,
        style: GoogleFonts.dmSans(
          color: CartzyColors.burgundy,
          fontSize: size * 0.36,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.5,
        ),
      ),
    );
  }

  @override
  void dispose() {
    _scrollStopTimer?.cancel();
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

  void _navigateToOrders([String filter = 'all']) {
    if (!widget.isLoggedIn) {
      widget.onLogin?.call();
      return;
    }
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MyOrdersScreen(
          initialFilter: filter,
          onBack: () => Navigator.of(context).pop(),
          // "Start shopping" / "Buy again" go back to the shop.
          onStartShopping: () => Navigator.of(context).popUntil((route) => route.isFirst),
        ),
      ),
    );
  }

  Future<void> _pickIdDocument() async {
    try {
      final picker = ImagePicker();
      final XFile? file =
          await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (file != null) {
        setState(() => _isIdVerified = true);
        if (mounted) {
          showCartzyFlash(context, 'ID Document uploaded: ${file.name}');
        }
      }
    } catch (_) {
      setState(() => _isIdVerified = true);
      if (mounted) {
        showCartzyFlash(context, 'ID Document submitted for verification!');
      }
    }
  }

  // ==========================================
  // PROFILE SETTINGS SCREEN
  // ==========================================
  void _openProfileSettingsModal() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => ProfileSettingsScreen(
          initialName: _nameController.text.trim(),
          initialEmail: _emailController.text.trim(),
          initialPhone: _phoneController.text.trim(),
          initialAvatar: _profileImagePath,
          onBack: () => Navigator.of(context).pop(),
          onProfileUpdated: (updated) {
            setState(() {
              if (updated['name'] != null) _nameController.text = updated['name'];
              if (updated['phone'] != null) _phoneController.text = updated['phone'];
              if (updated.containsKey('avatar')) _profileImagePath = updated['avatar'];
            });
            widget.onProfileUpdated?.call(updated);
          },
        ),
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
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const TermsPoliciesScreen()),
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
              leading: Container(
                width: 40,
                height: 40,
                decoration: const BoxDecoration(
                  color: Color(0xFFF3ECEE),
                  shape: BoxShape.circle,
                ),
                child: const Center(
                  child: BuddyBear(size: 30, mood: BuddyMood.happy),
                ),
              ),
              title: const Text('Chat with Cozy (AI Buddy)',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              subtitle: const Text('Instant answers about orders, items & tips',
                  style: TextStyle(fontSize: 12, color: Color(0xFF7A6E72))),
              trailing: const Icon(Icons.arrow_forward_ios_rounded,
                  size: 14, color: Color(0xFF7A6E72)),
              onTap: () {
                Navigator.pop(ctx);
                BuddyChatScreen.showPopup(context);
              },
            ),
            const Divider(height: 16),
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

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (didPop) return;
        (widget.onHomeClick ?? widget.onBack).call();
      },
      child: Scaffold(
        backgroundColor: CartzyColors.cream,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        toolbarHeight: 76,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new,
              size: 20, color: CartzyColors.navy),
          onPressed: widget.onHomeClick ?? widget.onBack,
        ),
        title: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'YOUR CARTZY',
              style: GoogleFonts.dmSans(
                fontSize: 11.5,
                fontWeight: FontWeight.w700,
                letterSpacing: 1.5,
                color: CartzyColors.burgundy,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              'My account',
              style: GoogleFonts.playfairDisplay(
                fontSize: 26,
                fontWeight: FontWeight.w700,
                color: CartzyColors.navy,
                letterSpacing: -0.5,
              ),
            ),
          ],
        ),
        actions: const [
          SizedBox(width: 48),
        ],
      ),
      body: SafeArea(
        child: Stack(
          children: [
            NotificationListener<ScrollNotification>(
              onNotification: _onScrollNotification,
              child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [

              // ==========================================
              // 2. USER PROFILE CARD (Hi, Jan / Email)
              // ==========================================
              GestureDetector(
                onTap: widget.isLoggedIn ? _openProfileSettingsModal : null,
                child: Container(
                width: double.infinity,
                padding:
                    const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
                // Same soft cream-to-blush look as the Cozy card.
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [
                      Color(0xFFFFF7F2),
                      Color(0xFFFDECEE),
                    ],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(22),
                  border: Border.all(color: CartzyColors.rose200, width: 1.2),
                  boxShadow: [
                    BoxShadow(
                      color: CartzyColors.burgundy.withValues(alpha: 0.05),
                      blurRadius: 14,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: !widget.isLoggedIn
                    ? _buildGuestProfileCard()
                    : Row(
                        children: [
                          // Circle Avatar with Profile Image / Initials + Camera Badge
                          _buildAvatarWidget(initials, size: 54),
                          const SizedBox(width: 16),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Hi, $firstName',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: GoogleFonts.playfairDisplay(
                                    fontSize: 23,
                                    fontWeight: FontWeight.w800,
                                    color: CartzyColors.brand900,
                                  ),
                                ),
                                const SizedBox(height: 3),
                                Text(
                                  email,
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w500,
                                    color: CartzyColors.brandDark,
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
                    onTap: () => _navigateToOrders('all'),
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
                      onTap: () => _navigateToOrders('to_ship'),
                    ),
                    _buildOrderAction(
                      icon: Icons.local_shipping_outlined,
                      label: 'Shipped',
                      onTap: () => _navigateToOrders('to_receive'),
                    ),
                    _buildOrderAction(
                      icon: Icons.check_circle_outline_rounded,
                      label: 'Delivered',
                      onTap: () => _navigateToOrders('completed'),
                    ),
                    _buildOrderAction(
                      icon: Icons.cancel_outlined,
                      label: 'Cancelled',
                      onTap: () => _navigateToOrders('cancelled'),
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
                      showChevron: true,
                      badgeWidget: _unreadMessagesCount > 0
                          ? Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 7, vertical: 2),
                              decoration: BoxDecoration(
                                color: CartzyColors.rose700,
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Text(
                                '$_unreadMessagesCount',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            )
                          : null,
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (_) => MessagesScreen(
                              onBack: () {
                                Navigator.of(context).pop();
                                _loadUnreadMessagesCount();
    NotificationService.fetchUnreadCount();
                              },
                            ),
                          ),
                        );
                      },
                    ),
                    const Divider(
                        height: 1,
                        thickness: 1,
                        color: CartzyColors.creamSection,
                        indent: 68),

                    // Notifications (with real database badge & tap navigation)
                    ValueListenableBuilder<int>(
                      valueListenable: NotificationService.unreadCountNotifier,
                      builder: (context, unreadCount, _) {
                        return _buildMenuTile(
                          icon: Icons.notifications_none_rounded,
                          label: 'Notifications',
                          badgeWidget: unreadCount > 0
                              ? Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 6, vertical: 2),
                                  decoration: const BoxDecoration(
                                    color: Color(0xFFEF4444),
                                    borderRadius:
                                        BorderRadius.all(Radius.circular(10)),
                                  ),
                                  child: Text(
                                    unreadCount > 99
                                        ? '99+'
                                        : unreadCount.toString(),
                                    style: const TextStyle(
                                      color: CartzyColors.creamLight,
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                )
                              : null,
                          onTap: () {
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => NotificationsScreen(
                                  onBack: () => Navigator.of(context).pop(),
                                ),
                              ),
                            ).then((_) => NotificationService.fetchUnreadCount());
                          },
                        );
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
                      onTap: _openAddressesScreen,
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
                      onTap: () => Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const ChangePasswordScreen()),
                      ),
                    ),
                    const Divider(
                        height: 1,
                        thickness: 1,
                        color: CartzyColors.creamSection,
                        indent: 68),

                    // Account activity
                    _buildMenuTile(
                      icon: Icons.history_rounded,
                      label: 'Account activity',
                      onTap: () => Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const AccountActivityScreen()),
                      ),
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
              // 5. HELP SECTION (Cozy AI, Help & support, Complaints)
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

              // Featured Cozy AI Shopping Buddy Card
              InkWell(
                key: const ValueKey('profile-cozy-card'),
                onTap: BuddyLauncher.summon,
                borderRadius: BorderRadius.circular(22),
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [
                        Color(0xFFFFF7F2),
                        Color(0xFFFDECEE),
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(22),
                    border: Border.all(color: CartzyColors.rose200, width: 1.2),
                    boxShadow: [
                      BoxShadow(
                        color: CartzyColors.burgundy.withValues(alpha: 0.05),
                        blurRadius: 14,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      // Cozy bear avatar
                      Container(
                        width: 54,
                        height: 54,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          shape: BoxShape.circle,
                          border: Border.all(color: CartzyColors.pinkBorder, width: 1.5),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.04),
                              blurRadius: 6,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: const Center(
                          child: IgnorePointer(
                            child: BuddyBear(
                              size: 42,
                              mood: BuddyMood.wink,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Wrap(
                              crossAxisAlignment: WrapCrossAlignment.center,
                              spacing: 6,
                              runSpacing: 2,
                              children: [
                                Text(
                                  'Cozy',
                                  style: GoogleFonts.playfairDisplay(
                                    fontSize: 17,
                                    fontWeight: FontWeight.w700,
                                    color: CartzyColors.navy,
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: CartzyColors.rose50Warm,
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(color: CartzyColors.rose200, width: 0.8),
                                  ),
                                  child: Text(
                                    'AI Buddy',
                                    style: GoogleFonts.dmSans(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w700,
                                      color: CartzyColors.burgundy,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 3),
                            Text(
                              'Need shopping advice, order tracking, or recommendations?',
                              style: GoogleFonts.dmSans(
                                fontSize: 11.5,
                                color: CartzyColors.brandDeep,
                                height: 1.3,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),
                      FilledButton(
                        key: const ValueKey('profile-cozy-chat-btn'),
                        onPressed: () => BuddyChatScreen.showPopup(context),
                        style: FilledButton.styleFrom(
                          backgroundColor: CartzyColors.burgundy,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                          minimumSize: Size.zero,
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                          elevation: 0,
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.chat_bubble_outline_rounded, size: 14),
                            const SizedBox(width: 4),
                            Text(
                              'Chat',
                              style: GoogleFonts.dmSans(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 12),

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
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: _confirmLogout,
                    icon: const Icon(Icons.logout_rounded, size: 19),
                    label: const Text('Log out'),
                    style: OutlinedButton.styleFrom(
                      backgroundColor: CartzyColors.rose50Warm,
                      foregroundColor: CartzyColors.rose700,
                      minimumSize: const Size(0, 52),
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                      side: const BorderSide(color: CartzyColors.rose200),
                      shape: const StadiumBorder(),
                      textStyle: GoogleFonts.dmSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ),
              const SizedBox(height: 24),
              Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    'A little joy in every find.',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.playfairDisplay(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: CartzyColors.brandDark,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Made for your everyday favorites.',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      color: CartzyColors.brandDeep,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 50),
            ],
          ),
        ),
      ),
            // ── Cozy Bear Floating Companion ──
            ValueListenableBuilder<int>(
              valueListenable: BuddyLauncher.summonTrigger,
              builder: (context, _, __) =>
                  const BuddyLauncher(aboveNav: false),
            ),
          ],
        ),
      ),
      bottomNavigationBar: SafeArea(
        top: false,
        child: AnimatedSlide(
          offset: _isNavVisible ? Offset.zero : const Offset(0, 1.8),
          duration: const Duration(milliseconds: 260),
          curve: Curves.easeInOutCubic,
          child: AnimatedOpacity(
            opacity: _isNavVisible ? 1.0 : 0.0,
            duration: const Duration(milliseconds: 200),
            child: Container(
              margin: const EdgeInsets.fromLTRB(16, 8, 16, 12),
          decoration: const ShapeDecoration(
            color: CartzyColors.surface,
            shape: StadiumBorder(
              side: BorderSide(color: CartzyColors.border),
            ),
            shadows: [
              BoxShadow(
                color: Color(0x148B3A3A),
                blurRadius: 16,
                offset: Offset(0, 4),
              ),
            ],
          ),
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
          child: Row(
            children: [
              _ProfileBottomNavItem(
                icon: Icons.home_rounded,
                label: 'Home',
                selected: false,
                onClick: widget.onHomeClick ?? widget.onBack,
              ),
              _ProfileBottomNavItem(
                icon: Icons.shopping_cart_outlined,
                label: 'Cart',
                selected: false,
                onClick: () {
                  if (widget.onCartClick != null) {
                    widget.onCartClick!();
                  } else {
                    Navigator.of(context).pushReplacement(
                      MaterialPageRoute(
                        builder: (_) => CartScreen(
                          onBack: widget.onHomeClick ?? widget.onBack,
                          onHomeClick: widget.onHomeClick ?? widget.onBack,
                          onSaleClick: widget.onSaleClick,
                          onProfileClick: () => Navigator.of(context).pop(),
                          onLogout: widget.onLogout,
                          onCheckout: () {
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (_) => CheckoutScreen(
                                  onBack: () => Navigator.of(context).pop(),
                                  onOrderPlaced: () {
                                    Navigator.of(context).popUntil((route) => route.isFirst);
                                  },
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                    );
                  }
                },
              ),
              _ProfileBottomNavItem(
                icon: Icons.local_offer_outlined,
                label: 'Sale',
                selected: false,
                isSale: true,
                onClick: () {
                  if (widget.onSaleClick != null) {
                    widget.onSaleClick!();
                  } else {
                    Navigator.of(context).pushReplacement(
                      MaterialPageRoute(
                        builder: (_) => ProductListScreen(
                          initialCategory: 'Flash Deals',
                          title: 'Sale & Flash Deals',
                          onHomeClick: widget.onHomeClick ?? widget.onBack,
                          onBack: widget.onHomeClick ?? widget.onBack,
                          onCartClick: widget.onCartClick,
                          onSaleClick: widget.onSaleClick,
                          onProfileClick: () => Navigator.of(context).pop(),
                        ),
                      ),
                    );
                  }
                },
              ),
              _ProfileBottomNavItem(
                icon: Icons.mail_outline_rounded,
                label: 'Inbox',
                selected: false,
                onClick: () {
                  if (widget.onInboxClick != null) {
                    widget.onInboxClick!();
                  } else {
                    Navigator.of(context).pushReplacement(
                      MaterialPageRoute(
                        builder: (_) => InboxScreen(
                          onBack: widget.onHomeClick ?? widget.onBack,
                          onHomeClick: widget.onHomeClick ?? widget.onBack,
                          onCartClick: widget.onCartClick,
                          onSaleClick: widget.onSaleClick,
                          onProfileClick: () => Navigator.of(context).pop(),
                        ),
                      ),
                    );
                  }
                },
              ),
              _ProfileBottomNavItem(
                icon: Icons.person_outline_rounded,
                label: 'Profile',
                selected: true,
                onClick: () {},
              ),
            ],
          ),
        ),
      ),
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
            backgroundColor: Colors.white,
            child: Icon(Icons.person_outline_rounded,
                color: CartzyColors.burgundy, size: 24),
          ),
          const SizedBox(width: 12),
          Expanded(
              child: Text('Welcome to Cartzy',
                  style: GoogleFonts.playfairDisplay(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.navy))),
        ]),
        const SizedBox(height: 12),
        Wrap(spacing: 12, runSpacing: 8, children: [
          FilledButton(
            onPressed: widget.onLogin,
            style: FilledButton.styleFrom(
              backgroundColor: CartzyColors.burgundy,
              foregroundColor: Colors.white,
              minimumSize: const Size(104, 40),
            ),
            child: const Text('Log in'),
          ),
          OutlinedButton(
            onPressed: widget.onSignUp,
            style: OutlinedButton.styleFrom(
              backgroundColor: Colors.white,
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

class _ProfileBottomNavItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final bool isSale;
  final VoidCallback onClick;

  const _ProfileBottomNavItem({
    required this.icon,
    required this.label,
    required this.selected,
    this.isSale = false,
    required this.onClick,
  });

  @override
  Widget build(BuildContext context) {
    final color = isSale
        ? CartzyColors.burgundy
        : selected
            ? CartzyColors.burgundy
            : CartzyColors.gray;

    return Expanded(
      child: InkWell(
        onTap: onClick,
        borderRadius: BorderRadius.circular(8),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, color: color, size: 22),
              const SizedBox(height: 3),
              Text(
                label,
                style: GoogleFonts.dmSans(
                  fontSize: 11,
                  color: color,
                  fontWeight:
                      selected || isSale ? FontWeight.w600 : FontWeight.w400,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 2),
              AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                height: 2,
                width: selected ? 16 : 0,
                decoration: BoxDecoration(
                  color: CartzyColors.burgundy,
                  borderRadius: BorderRadius.circular(1),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

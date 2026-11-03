import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/utils/input_rules.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';
import 'package:cartzy/widgets/profile_photo_editor_screen.dart';

// ============================================================
// PROFILE SETTINGS SCREEN (Cartzy Design)
// Matches the dedicated profile settings screen layout
// ============================================================

class ProfileSettingsScreen extends StatefulWidget {
  final VoidCallback onBack;
  final ValueChanged<Map<String, dynamic>>? onProfileUpdated;
  final String? initialName;
  final String? initialEmail;
  final String? initialPhone;
  final String? initialBirthday;
  final String? initialAvatar;

  const ProfileSettingsScreen({
    super.key,
    required this.onBack,
    this.onProfileUpdated,
    this.initialName,
    this.initialEmail,
    this.initialPhone,
    this.initialBirthday,
    this.initialAvatar,
  });

  @override
  State<ProfileSettingsScreen> createState() => _ProfileSettingsScreenState();
}

// Backwards-compatibility alias
typedef EditBuyerProfileScreen = ProfileSettingsScreen;

class _ProfileSettingsScreenState extends State<ProfileSettingsScreen> {
  late final TextEditingController _nameController;
  late final TextEditingController _emailController;
  late final TextEditingController _phoneController;
  late final TextEditingController _birthdayController;

  bool _isSaving = false;
  String? _profileImagePath;

  @override
  void initState() {
    super.initState();
    final user = AuthService.currentUser ?? {};
    final rawName = (widget.initialName != null && widget.initialName!.isNotEmpty)
        ? widget.initialName!
        : (user['name']?.toString() ?? '');
    final rawEmail = (widget.initialEmail != null && widget.initialEmail!.isNotEmpty)
        ? widget.initialEmail!
        : (user['email']?.toString() ?? '');
    final rawPhone = (widget.initialPhone != null && widget.initialPhone!.isNotEmpty)
        ? widget.initialPhone!
        : (user['phone']?.toString() ?? '');
    final rawBirthday = (widget.initialBirthday != null && widget.initialBirthday!.isNotEmpty)
        ? widget.initialBirthday!
        : (user['birthday']?.toString() ?? '');

    _nameController = TextEditingController(text: rawName);
    _emailController = TextEditingController(text: rawEmail);
    _phoneController = TextEditingController(text: rawPhone);
    _birthdayController = TextEditingController(
      text: rawBirthday.isNotEmpty ? rawBirthday : '2005-09-20',
    );
    _profileImagePath = widget.initialAvatar ?? user['avatar']?.toString();
    _loadProfileImage();
  }

  Future<void> _loadProfileImage() async {
    if (_profileImagePath != null && _profileImagePath!.isNotEmpty) return;
    final email = _emailController.text.trim();
    if (email.isNotEmpty) {
      try {
        final prefs = await SharedPreferences.getInstance();
        final saved = prefs.getString('cartzy_avatar_$email');
        if (saved != null && saved.isNotEmpty && mounted) {
          setState(() => _profileImagePath = saved);
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

      final email = _emailController.text.trim();
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

    final email = _emailController.text.trim();
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

    final email = _emailController.text.trim();
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

  String _getInitials() {
    final raw = _nameController.text.trim();
    if (raw.isEmpty) return 'U';
    final parts = raw.split(' ').where((p) => p.isNotEmpty).toList();
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    }
    return raw.substring(0, raw.length >= 2 ? 2 : 1).toUpperCase();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _birthdayController.dispose();
    super.dispose();
  }

  Future<void> _pickBirthday() async {
    DateTime initial = DateTime.tryParse(_birthdayController.text.trim()) ??
        DateTime(2005, 9, 20);
    if (initial.isAfter(DateTime.now())) initial = DateTime(2005, 9, 20);

    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(1930),
      lastDate: DateTime.now(),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: CartzyColors.burgundy,
              onPrimary: Colors.white,
              onSurface: CartzyColors.navy,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      final formatted =
          '${picked.year.toString().padLeft(4, '0')}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}';
      setState(() {
        _birthdayController.text = formatted;
      });
    }
  }

  void _saveChanges() {
    final nameError = InputRules.fullName(_nameController.text);
    if (nameError != null) {
      showCartzyFlash(context, nameError);
      return;
    }

    final phoneText = _phoneController.text.trim();
    if (phoneText.isNotEmpty) {
      final phoneError = InputRules.phone(phoneText);
      if (phoneError != null) {
        showCartzyFlash(context, phoneError);
        return;
      }
    }

    setState(() => _isSaving = true);

    final updated = Map<String, dynamic>.from(AuthService.currentUser ?? {});
    updated['name'] = _nameController.text.trim();
    updated['phone'] = _phoneController.text.trim();
    updated['birthday'] = _birthdayController.text.trim();
    if (_profileImagePath != null && _profileImagePath!.isNotEmpty) {
      updated['avatar'] = _profileImagePath;
    } else {
      updated.remove('avatar');
    }

    AuthService.setSession(user: updated, token: AuthService.token);
    widget.onProfileUpdated?.call(updated);

    showCartzyFlash(context, 'Profile updated successfully!');
    widget.onBack();
  }

  @override
  Widget build(BuildContext context) {
    const fieldBorderColor = Color(0xFFEDE4E7);
    const textColor = Color(0xFF231F20);
    const labelColor = Color(0xFF231F20);
    const subtitleColor = Color(0xFF8E8286);

    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F5),
      appBar: AppBar(
        backgroundColor: const Color(0xFFFAF7F5),
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: textColor),
          onPressed: widget.onBack,
        ),
        title: Text(
          'Profile settings',
          style: GoogleFonts.dmSans(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: textColor,
          ),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ── Profile Photo Header ──
              Center(
                child: Column(
                  children: [
                    GestureDetector(
                      onTap: _showImagePickerSheet,
                      child: Stack(
                        clipBehavior: Clip.none,
                        children: [
                          Container(
                            width: 86,
                            height: 86,
                            decoration: BoxDecoration(
                              color: CartzyColors.cream,
                              shape: BoxShape.circle,
                              border: Border.all(color: CartzyColors.burgundy, width: 2),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.08),
                                  blurRadius: 10,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: ClipOval(
                              child: (_profileImagePath != null && _profileImagePath!.isNotEmpty)
                                  ? (_profileImagePath!.startsWith('http')
                                      ? Image.network(
                                          _profileImagePath!,
                                          width: 86,
                                          height: 86,
                                          fit: BoxFit.cover,
                                          errorBuilder: (_, __, ___) => Center(
                                            child: Text(
                                              _getInitials(),
                                              style: GoogleFonts.dmSans(
                                                color: CartzyColors.burgundy,
                                                fontSize: 28,
                                                fontWeight: FontWeight.w700,
                                              ),
                                            ),
                                          ),
                                        )
                                      : Image.file(
                                          File(_profileImagePath!),
                                          width: 86,
                                          height: 86,
                                          fit: BoxFit.cover,
                                          errorBuilder: (_, __, ___) => Center(
                                            child: Text(
                                              _getInitials(),
                                              style: GoogleFonts.dmSans(
                                                color: CartzyColors.burgundy,
                                                fontSize: 28,
                                                fontWeight: FontWeight.w700,
                                              ),
                                            ),
                                          ),
                                        ))
                                  : Center(
                                      child: Text(
                                        _getInitials(),
                                        style: GoogleFonts.dmSans(
                                          color: CartzyColors.burgundy,
                                          fontSize: 28,
                                          fontWeight: FontWeight.w700,
                                        ),
                                      ),
                                    ),
                            ),
                          ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: Container(
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color: CartzyColors.burgundy,
                                shape: BoxShape.circle,
                                border: Border.all(color: Colors.white, width: 2),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.2),
                                    blurRadius: 4,
                                  ),
                                ],
                              ),
                              child: const Icon(
                                Icons.camera_alt_rounded,
                                size: 16,
                                color: Colors.white,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 8),
                    GestureDetector(
                      onTap: _showImagePickerSheet,
                      child: Text(
                        _profileImagePath != null ? 'Change photo' : 'Add photo',
                        style: GoogleFonts.dmSans(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w600,
                          color: CartzyColors.burgundy,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 22),

              // ── Full Name ──
              Text(
                'Full name',
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: labelColor,
                ),
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _nameController,
                inputFormatters: InputRules.fullNameFormatters,
                style: GoogleFonts.dmSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w500,
                  color: textColor,
                ),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: textColor, width: 1.5),
                  ),
                ),
              ),

              const SizedBox(height: 18),

              // ── Email Address ──
              Text(
                'Email address',
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: labelColor,
                ),
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _emailController,
                readOnly: true,
                style: GoogleFonts.dmSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w500,
                  color: textColor,
                ),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                ),
              ),
              const SizedBox(height: 6),
              Text(
                'This email is linked to Google and cannot be changed here.',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  color: subtitleColor,
                  height: 1.35,
                ),
              ),

              const SizedBox(height: 18),

              // ── Mobile Number ──
              Text(
                'Mobile number',
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: labelColor,
                ),
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _phoneController,
                keyboardType: TextInputType.phone,
                inputFormatters: InputRules.phoneFormatters,
                style: GoogleFonts.dmSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w500,
                  color: textColor,
                ),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: Colors.white,
                  contentPadding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: fieldBorderColor, width: 1.2),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide:
                        const BorderSide(color: textColor, width: 1.5),
                  ),
                ),
              ),

              const SizedBox(height: 18),

              // ── Birthday ──
              Text(
                'Birthday',
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: labelColor,
                ),
              ),
              const SizedBox(height: 8),
              InkWell(
                onTap: _pickBirthday,
                borderRadius: BorderRadius.circular(14),
                child: IgnorePointer(
                  child: TextFormField(
                    controller: _birthdayController,
                    readOnly: true,
                    style: GoogleFonts.dmSans(
                      fontSize: 15,
                      fontWeight: FontWeight.w500,
                      color: textColor,
                    ),
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: Colors.white,
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 15),
                      suffixIcon: const Padding(
                        padding: EdgeInsets.only(right: 14),
                        child: Icon(
                          Icons.calendar_month_outlined,
                          color: Color(0xFF7A6E72),
                          size: 22,
                        ),
                      ),
                      suffixIconConstraints:
                          const BoxConstraints(minWidth: 40, minHeight: 40),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: const BorderSide(
                            color: fieldBorderColor, width: 1.2),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide: const BorderSide(
                            color: fieldBorderColor, width: 1.2),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(14),
                        borderSide:
                            const BorderSide(color: textColor, width: 1.5),
                      ),
                    ),
                  ),
                ),
              ),

              const SizedBox(height: 32),

              // ── SAVE CHANGES Button ──
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isSaving ? null : _saveChanges,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF231F20),
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: const Color(0xFF7A6E72),
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                  child: Text(
                    'SAVE CHANGES',
                    style: GoogleFonts.dmSans(
                      fontSize: 13.5,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 0.8,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

import 'dart:io';
import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/services/app_update_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/recently_viewed_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/buddy_bear.dart';

/// Lavender and rose gold, the same blend used on the cart screen.
class _MenuPalette {
  static const lavender = CartzyColors.brandDeep;
  static const roseGold = Color(0xFFB76E79);
  static const roseGoldDeep = Color(0xFF9C4A5A);
  static const ink = CartzyColors.brand900;
  static const muted = CartzyColors.grayMid;
  static const line = Color(0xFFEDE6EE);

  static const blend = LinearGradient(
    colors: [lavender, roseGold],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const softBlend = LinearGradient(
    colors: [CartzyColors.brand100, CartzyColors.rose50Warm],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
}

/// Side menu opened from the hamburger button on the buyer home header.
///
/// Every tap closes the drawer first and then runs its action, so the
/// next screen opens on top of the home screen, not on top of the drawer.
class CartzyMenuDrawer extends StatelessWidget {
  final String? userName;
  final ValueNotifier<int> unreadMessages;
  final VoidCallback onMessages;
  final VoidCallback onFlashDeals;
  final VoidCallback onMyOrders;
  final VoidCallback onVouchers;
  final VoidCallback onRecentlyViewed;
  final VoidCallback onWhatsNew;
  final VoidCallback onCozy;
  final VoidCallback onLoginAsRider;
  final VoidCallback onLogout;

  const CartzyMenuDrawer({
    super.key,
    required this.userName,
    required this.unreadMessages,
    required this.onMessages,
    required this.onFlashDeals,
    required this.onMyOrders,
    required this.onVouchers,
    required this.onRecentlyViewed,
    required this.onWhatsNew,
    required this.onCozy,
    required this.onLoginAsRider,
    required this.onLogout,
  });

  void _run(BuildContext context, VoidCallback action) {
    Navigator.of(context).pop(); // close the drawer
    action();
  }

  @override
  Widget build(BuildContext context) {
    return Drawer(
      width: math.min(MediaQuery.sizeOf(context).width * 0.84, 340),
      backgroundColor: CartzyColors.brand50Lavender,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.horizontal(right: Radius.circular(28)),
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        children: [
          _buildHeader(context),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(14, 18, 14, 12),
              children: [
                const _SectionLabel('SHOP'),
                _MenuTile(
                  icon: Icons.bolt_rounded,
                  label: 'Flash Deals',
                  subtitle: 'Limited-time prices',
                  trailing: const _Tag('HOT'),
                  onTap: () => _run(context, onFlashDeals),
                ),
                _MenuTile(
                  icon: Icons.inventory_2_outlined,
                  label: 'My Orders',
                  subtitle: 'Track and manage purchases',
                  onTap: () => _run(context, onMyOrders),
                ),
                _MenuTile(
                  icon: Icons.confirmation_number_outlined,
                  label: 'My Vouchers',
                  subtitle: 'Discounts ready to use',
                  onTap: () => _run(context, onVouchers),
                ),
                ValueListenableBuilder<List<RecentlyViewedProduct>>(
                  valueListenable: RecentlyViewedService.items,
                  builder: (context, items, _) => _MenuTile(
                    icon: Icons.history_rounded,
                    label: 'Recently Viewed',
                    subtitle: 'Products you looked at',
                    trailing: items.isNotEmpty ? _CountBadge(items.length) : null,
                    onTap: () => _run(context, onRecentlyViewed),
                  ),
                ),
                const SizedBox(height: 10),
                const _SectionLabel('CHAT & HELP'),
                ValueListenableBuilder<int>(
                  valueListenable: unreadMessages,
                  builder: (context, count, _) => _MenuTile(
                    icon: Icons.chat_bubble_outline_rounded,
                    label: 'Messages',
                    subtitle: 'Chats with shops',
                    trailing: count > 0 ? _CountBadge(count) : null,
                    onTap: () => _run(context, onMessages),
                  ),
                ),
                _MenuTile(
                  leading: Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      gradient: _MenuPalette.softBlend,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: CartzyColors.rose200),
                    ),
                    alignment: Alignment.center,
                    child: const IgnorePointer(
                      child: BuddyBear(size: 30, mood: BuddyMood.happy),
                    ),
                  ),
                  label: 'Cozy',
                  subtitle: 'Your shopping assistant',
                  onTap: () => _run(context, onCozy),
                ),
                const SizedBox(height: 10),
                const _SectionLabel('ACCOUNT'),
                _MenuTile(
                  icon: Icons.delivery_dining_outlined,
                  label: 'Login as Rider',
                  subtitle: 'Switch to your courier account',
                  onTap: () => _confirmRiderSwitch(context),
                ),
                const SizedBox(height: 10),
                const _SectionLabel('APP'),
                _MenuTile(
                  icon: Icons.new_releases_outlined,
                  label: "What's New",
                  subtitle: 'Latest features and fixes',
                  trailing: const _Tag('v${AppUpdateService.currentAppVersion}'),
                  onTap: () => _run(context, onWhatsNew),
                ),
              ],
            ),
          ),
          _buildFooter(context),
        ],
      ),
    );
  }

  // ── Header ───────────────────────────────────────────────────

  Widget _buildHeader(BuildContext context) {
    final name = (userName ?? '').trim();
    final displayName = name.isEmpty ? 'Cartzy Shopper' : name;
    final email = AuthService.currentUser?['email']?.toString() ?? '';

    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(gradient: _MenuPalette.blend),
      padding: EdgeInsets.fromLTRB(
          20, MediaQuery.paddingOf(context).top + 18, 12, 22),
      child: Row(
        children: [
          _Avatar(name: displayName),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Hello,',
                  style: GoogleFonts.dmSans(
                    fontSize: 12,
                    color: Colors.white.withValues(alpha: 0.8),
                  ),
                ),
                Text(
                  displayName,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: GoogleFonts.playfairDisplay(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                if (email.isNotEmpty)
                  Text(
                    email,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.dmSans(
                      fontSize: 11.5,
                      color: Colors.white.withValues(alpha: 0.8),
                    ),
                  ),
              ],
            ),
          ),
          IconButton(
            tooltip: 'Close menu',
            onPressed: () => Navigator.of(context).pop(),
            icon: const Icon(Icons.close_rounded, color: Colors.white),
          ),
        ],
      ),
    );
  }

  // ── Footer: Log out ──────────────────────────────────────────

  Widget _buildFooter(BuildContext context) {
    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
        child: Material(
          color: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: CartzyColors.rose200),
          ),
          child: InkWell(
            borderRadius: BorderRadius.circular(16),
            onTap: () => _confirmLogout(context),
            child: SizedBox(
              height: 52,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.logout_rounded,
                      size: 19, color: _MenuPalette.roseGoldDeep),
                  const SizedBox(width: 8),
                  Text(
                    'Log out',
                    style: GoogleFonts.dmSans(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: _MenuPalette.roseGoldDeep,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  // ── Confirmations ────────────────────────────────────────────

  Future<void> _confirmLogout(BuildContext context) async {
    final ok = await _showConfirm(
      context,
      icon: Icons.logout_rounded,
      title: 'Log out?',
      message: "You'll need to sign in again to shop and check out.",
      confirmLabel: 'Log out',
    );
    if (ok && context.mounted) _run(context, onLogout);
  }

  Future<void> _confirmRiderSwitch(BuildContext context) async {
    final ok = await _showConfirm(
      context,
      icon: Icons.delivery_dining_outlined,
      title: 'Switch to Rider?',
      message:
          "We'll sign you out of your buyer account so you can log in with your rider account.",
      confirmLabel: 'Continue',
    );
    if (ok && context.mounted) _run(context, onLoginAsRider);
  }

  Future<bool> _showConfirm(
    BuildContext context, {
    required IconData icon,
    required String title,
    required String message,
    required String confirmLabel,
  }) async {
    final result = await showDialog<bool>(
      context: context,
      barrierColor: _MenuPalette.ink.withValues(alpha: 0.35),
      builder: (ctx) => Dialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        insetPadding: const EdgeInsets.symmetric(horizontal: 32),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(24, 28, 24, 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 60,
                height: 60,
                decoration: const BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: _MenuPalette.softBlend,
                ),
                child: Icon(icon, color: _MenuPalette.roseGold, size: 28),
              ),
              const SizedBox(height: 16),
              Text(
                title,
                style: GoogleFonts.playfairDisplay(
                  fontWeight: FontWeight.w700,
                  fontSize: 21,
                  color: _MenuPalette.ink,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                message,
                textAlign: TextAlign.center,
                style: GoogleFonts.dmSans(
                  fontSize: 13.5,
                  height: 1.4,
                  color: _MenuPalette.muted,
                ),
              ),
              const SizedBox(height: 22),
              Row(
                children: [
                  Expanded(
                    child: _DialogButton(
                      label: 'Cancel',
                      filled: false,
                      onTap: () => Navigator.of(ctx).pop(false),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _DialogButton(
                      label: confirmLabel,
                      filled: true,
                      onTap: () => Navigator.of(ctx).pop(true),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
    return result == true;
  }
}


// ──────────────────────────────────────────────────────────────
// PIECES
// ──────────────────────────────────────────────────────────────

class _Avatar extends StatelessWidget {
  final String name;

  const _Avatar({required this.name});

  ImageProvider? _photo() {
    final path = AuthService.profilePhoto(AuthService.currentUser) ?? '';
    if (path.isEmpty) return null;
    if (path.startsWith('http')) return NetworkImage(path);
    try {
      final file = File(path);
      if (file.existsSync()) return FileImage(file);
    } catch (_) {}
    return null;
  }

  @override
  Widget build(BuildContext context) {
    final photo = _photo();
    return Container(
      width: 58,
      height: 58,
      padding: const EdgeInsets.all(2.5),
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: Colors.white.withValues(alpha: 0.35),
      ),
      child: CircleAvatar(
        backgroundColor: Colors.white,
        foregroundImage: photo,
        onForegroundImageError: photo == null ? null : (_, __) {},
        child: Text(
          name.characters.first.toUpperCase(),
          style: GoogleFonts.playfairDisplay(
            fontSize: 22,
            fontWeight: FontWeight.w700,
            color: _MenuPalette.lavender,
          ),
        ),
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  final String text;

  const _SectionLabel(this.text);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(10, 4, 10, 8),
      child: Text(
        text,
        style: GoogleFonts.dmSans(
          fontSize: 10.5,
          fontWeight: FontWeight.w800,
          letterSpacing: 1.4,
          color: _MenuPalette.muted,
        ),
      ),
    );
  }
}

class _MenuTile extends StatelessWidget {
  final IconData? icon;
  final Widget? leading;
  final String label;
  final String subtitle;
  final Widget? trailing;
  final VoidCallback onTap;

  const _MenuTile({
    this.icon,
    this.leading,
    required this.label,
    required this.subtitle,
    required this.onTap,
    this.trailing,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Material(
        color: Colors.white,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: _MenuPalette.line),
        ),
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          splashColor: CartzyColors.rose200.withValues(alpha: 0.4),
          highlightColor: CartzyColors.brand100,
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.fromLTRB(12, 11, 12, 11),
            child: Row(
              children: [
                leading ??
                    Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        gradient: _MenuPalette.blend,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(icon ?? Icons.circle, size: 20, color: Colors.white),
                    ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        label,
                        style: GoogleFonts.dmSans(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: _MenuPalette.ink,
                        ),
                      ),
                      Text(
                        subtitle,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: GoogleFonts.dmSans(
                          fontSize: 11.5,
                          color: _MenuPalette.muted,
                        ),
                      ),
                    ],
                  ),
                ),
                if (trailing != null) ...[
                  trailing!,
                  const SizedBox(width: 6),
                ],
                const Icon(Icons.chevron_right_rounded,
                    size: 20, color: CartzyColors.brand400),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _CountBadge extends StatelessWidget {
  final int count;

  const _CountBadge(this.count);

  @override
  Widget build(BuildContext context) {
    return Container(
      constraints: const BoxConstraints(minWidth: 22),
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        gradient: _MenuPalette.blend,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        count > 99 ? '99+' : '$count',
        textAlign: TextAlign.center,
        style: GoogleFonts.dmSans(
          fontSize: 11,
          fontWeight: FontWeight.w800,
          color: Colors.white,
        ),
      ),
    );
  }
}

class _Tag extends StatelessWidget {
  final String text;

  const _Tag(this.text);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: CartzyColors.rose50Warm,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: CartzyColors.rose200),
      ),
      child: Text(
        text,
        style: GoogleFonts.dmSans(
          fontSize: 9.5,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.6,
          color: _MenuPalette.roseGoldDeep,
        ),
      ),
    );
  }
}

class _DialogButton extends StatelessWidget {
  final String label;
  final bool filled;
  final VoidCallback onTap;

  const _DialogButton({
    required this.label,
    required this.filled,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final radius = BorderRadius.circular(16);
    return DecoratedBox(
      decoration: BoxDecoration(
        borderRadius: radius,
        gradient: filled ? _MenuPalette.blend : null,
        color: filled ? null : CartzyColors.brand100,
      ),
      child: Material(
        type: MaterialType.transparency,
        child: InkWell(
          borderRadius: radius,
          onTap: onTap,
          child: SizedBox(
            height: 46,
            child: Center(
              child: Text(
                label,
                style: GoogleFonts.dmSans(
                  fontSize: 14,
                  fontWeight: FontWeight.w700,
                  color: filled ? Colors.white : CartzyColors.brandDark,
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

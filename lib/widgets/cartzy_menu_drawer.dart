import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/services/app_update_service.dart';
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

/// Side menu opened from the hamburger button on the home header.
///
/// Every tap closes the drawer first and then runs its action, so the
/// next screen opens on top of the home screen, not on top of the drawer.
///
/// With [isGuest], items that need an account (My Orders, My Vouchers,
/// Messages) show a lock and ask the guest to log in, and the footer offers
/// Log in / Create account instead of Log out.
class CartzyMenuDrawer extends StatelessWidget {
  final String? userName;
  final bool isGuest;
  final ValueNotifier<int>? unreadMessages;
  final VoidCallback onMessages;
  final VoidCallback onFlashDeals;
  final VoidCallback onMyOrders;
  final VoidCallback onVouchers;
  final VoidCallback onRecentlyViewed;
  final VoidCallback onWhatsNew;
  final VoidCallback onCozy;
  final VoidCallback onLoginAsRider;
  final VoidCallback? onLogout;
  final VoidCallback? onLogin;
  final VoidCallback? onSignUp;

  const CartzyMenuDrawer({
    super.key,
    this.userName,
    this.isGuest = false,
    this.unreadMessages,
    required this.onMessages,
    required this.onFlashDeals,
    required this.onMyOrders,
    required this.onVouchers,
    required this.onRecentlyViewed,
    required this.onWhatsNew,
    required this.onCozy,
    required this.onLoginAsRider,
    this.onLogout,
    this.onLogin,
    this.onSignUp,
  });

  void _run(BuildContext context, VoidCallback? action) {
    Navigator.of(context).pop(); // close the drawer
    action?.call();
  }

  /// Opens [action], or for a guest asks them to log in first.
  void _member(BuildContext context, VoidCallback action) {
    if (isGuest) {
      _askToLogin(context);
    } else {
      _run(context, action);
    }
  }

  /// Small lock shown to guests on items that need an account.
  Widget? get _lock => isGuest
      ? const Icon(Icons.lock_outline_rounded, size: 16, color: CartzyColors.brand400)
      : null;

  @override
  Widget build(BuildContext context) {
    return Drawer(
      width: math.min(MediaQuery.sizeOf(context).width * 0.78, 310),
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.horizontal(right: Radius.circular(20)),
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        children: [
          _buildHeader(context),
          const Divider(height: 1, thickness: 1, color: _MenuPalette.line),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(vertical: 8),
              children: [
                _MenuTile(
                  icon: Icons.bolt_rounded,
                  label: 'Flash Deals',
                  trailing: const _Tag('HOT'),
                  onTap: () => _run(context, onFlashDeals),
                ),
                _MenuTile(
                  icon: Icons.inventory_2_outlined,
                  label: 'My Orders',
                  trailing: _lock,
                  onTap: () => _member(context, onMyOrders),
                ),
                _MenuTile(
                  icon: Icons.confirmation_number_outlined,
                  label: 'My Vouchers',
                  trailing: _lock,
                  onTap: () => _member(context, onVouchers),
                ),
                ValueListenableBuilder<List<RecentlyViewedProduct>>(
                  valueListenable: RecentlyViewedService.items,
                  builder: (context, items, _) => _MenuTile(
                    icon: Icons.history_rounded,
                    label: 'Recently Viewed',
                    trailing:
                        items.isNotEmpty ? _CountBadge(items.length) : null,
                    onTap: () => _run(context, onRecentlyViewed),
                  ),
                ),
                const _SectionDivider(),
                ValueListenableBuilder<int>(
                  valueListenable: unreadMessages ?? ValueNotifier<int>(0),
                  builder: (context, count, _) => _MenuTile(
                    icon: Icons.chat_bubble_outline_rounded,
                    label: 'Messages',
                    trailing: _lock ?? (count > 0 ? _CountBadge(count) : null),
                    onTap: () => _member(context, onMessages),
                  ),
                ),
                _MenuTile(
                  leading: const IgnorePointer(
                    child: BuddyBear(size: 24, mood: BuddyMood.happy),
                  ),
                  label: 'Cozy',
                  onTap: () => _run(context, onCozy),
                ),
                const _SectionDivider(),
                _MenuTile(
                  icon: Icons.delivery_dining_outlined,
                  label: 'Login as Rider',
                  onTap: () => _confirmRiderSwitch(context),
                ),
                _MenuTile(
                  icon: Icons.new_releases_outlined,
                  label: "What's New",
                  trailing: Text(
                    'v${AppUpdateService.currentAppVersion}',
                    style: GoogleFonts.dmSans(
                      fontSize: 11.5,
                      color: _MenuPalette.muted,
                    ),
                  ),
                  onTap: () => _run(context, onWhatsNew),
                ),
              ],
            ),
          ),
          const Divider(height: 1, thickness: 1, color: _MenuPalette.line),
          _buildFooter(context),
        ],
      ),
    );
  }

  // ── Header ───────────────────────────────────────────────────

  Widget _buildHeader(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(
          20, MediaQuery.paddingOf(context).top + 12, 8, 12),
      child: Row(
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: Image.asset(
              'assets/app_icon.png',
              width: 30,
              height: 30,
              fit: BoxFit.cover,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'Cartzy',
              style: GoogleFonts.playfairDisplay(
                fontSize: 20,
                fontWeight: FontWeight.w800,
                color: _MenuPalette.ink,
              ),
            ),
          ),
          IconButton(
            tooltip: 'Close menu',
            onPressed: () => Navigator.of(context).pop(),
            icon: const Icon(Icons.close_rounded,
                size: 22, color: _MenuPalette.muted),
          ),
        ],
      ),
    );
  }

  // ── Footer: Log out, or Log in / Create account for guests ──

  Widget _buildFooter(BuildContext context) {
    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 6),
        child: isGuest
            ? Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _MenuTile(
                    icon: Icons.login_rounded,
                    label: 'Log in',
                    color: _MenuPalette.roseGoldDeep,
                    onTap: () => _run(context, onLogin),
                  ),
                  _MenuTile(
                    icon: Icons.person_add_alt_outlined,
                    label: 'Create account',
                    onTap: () => _run(context, onSignUp),
                  ),
                ],
              )
            : _MenuTile(
                icon: Icons.logout_rounded,
                label: 'Log out',
                color: _MenuPalette.roseGoldDeep,
                onTap: () => _confirmLogout(context),
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

  Future<void> _askToLogin(BuildContext context) async {
    final ok = await _showConfirm(
      context,
      icon: Icons.lock_outline_rounded,
      title: 'Log in to continue',
      message: 'Log in or create an account to see your orders, vouchers and messages.',
      confirmLabel: 'Log in',
    );
    if (ok && context.mounted) _run(context, onLogin);
  }

  Future<void> _confirmRiderSwitch(BuildContext context) async {
    // A guest has no buyer session to end, so go straight to rider login.
    if (isGuest) {
      _run(context, onLoginAsRider);
      return;
    }
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

class _SectionDivider extends StatelessWidget {
  const _SectionDivider();

  @override
  Widget build(BuildContext context) {
    return const Padding(
      padding: EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      child: Divider(height: 1, thickness: 1, color: _MenuPalette.line),
    );
  }
}

/// One plain row: icon, label, optional badge.
class _MenuTile extends StatelessWidget {
  final IconData? icon;
  final Widget? leading;
  final String label;
  final Widget? trailing;
  final Color color;
  final VoidCallback onTap;

  const _MenuTile({
    this.icon,
    this.leading,
    required this.label,
    required this.onTap,
    this.trailing,
    this.color = _MenuPalette.ink,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      splashColor: CartzyColors.rose200.withValues(alpha: 0.35),
      highlightColor: CartzyColors.brand100,
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 13),
        child: Row(
          children: [
            SizedBox(
              width: 24,
              height: 24,
              child: Center(
                child: leading ??
                    Icon(
                      icon ?? Icons.circle,
                      size: 21,
                      color: color == _MenuPalette.ink
                          ? CartzyColors.brandDeep
                          : color,
                    ),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Text(
                label,
                style: GoogleFonts.dmSans(
                  fontSize: 14.5,
                  fontWeight: FontWeight.w600,
                  color: color,
                ),
              ),
            ),
            if (trailing != null) trailing!,
          ],
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
        color: _MenuPalette.roseGold,
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
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: CartzyColors.rose50Warm,
        borderRadius: BorderRadius.circular(6),
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

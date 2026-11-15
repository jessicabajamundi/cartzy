import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/theme/theme_controller.dart';
import 'change_password_screen.dart';
import 'buyer_account_screen.dart';
import 'reviews_screen.dart';
import 'vouchers_screen.dart';
import 'notifications_screen.dart';
import 'package:cartzy/services/notification_service.dart';

class AccountMenuScreen extends StatelessWidget {
  final VoidCallback onBack;
  final VoidCallback onMyPurchase;
  final VoidCallback onLogout;
  final VoidCallback onAddresses;
  final ValueChanged<String>? onNavigateRole;

  final int userId;
  final String userName;
  final String userEmail;

  final ValueChanged<Map<String, dynamic>>? onProfileUpdated;

  const AccountMenuScreen({
    super.key,
    required this.onBack,
    required this.onMyPurchase,
    required this.onLogout,
    required this.onAddresses,
    this.onNavigateRole,
    required this.userId,
    required this.userName,
    required this.userEmail,
    this.onProfileUpdated,
  });
  // ============================================================
  // GET FIRST LETTER
  // ============================================================

  String _getInitial() {
    final name = userName.trim();

    if (name.isEmpty) {
      return 'B';
    }

    return name.substring(0, 1).toUpperCase();
  }

  // ============================================================
  // BUILD
  // ============================================================

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.bgOf(context),

      // ========================================================
      // APP BAR
      // ========================================================

      appBar: AppBar(
        backgroundColor: CartzyColors.surfaceOf(context),
        elevation: 0,

        leading: IconButton(
          icon: Icon(
            Icons.arrow_back,
            color: CartzyColors.textOf(context),
          ),
          onPressed: onBack,
        ),

        title: Text(
          'My Account',
          style: TextStyle(
            color: CartzyColors.textOf(context),
            fontWeight: FontWeight.bold,
          ),
        ),
      ),

      // ========================================================
      // BODY
      // ========================================================

      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(20),

          children: [
            // ==================================================
            // PROFILE HEADER CARD
            // ==================================================

            Container(
              padding: const EdgeInsets.all(16),

              decoration: BoxDecoration(
                color: CartzyColors.surfaceOf(context),
                border: Border.all(color: CartzyColors.borderOf(context)),

                borderRadius:
                    BorderRadius.circular(14),

                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(
                      alpha: 0.05,
                    ),

                    blurRadius: 10,

                    offset: const Offset(0, 3),
                  ),
                ],
              ),

              child: Row(
                children: [
                  // --------------------------------------------
                  // PROFILE INITIAL
                  // --------------------------------------------

                  CircleAvatar(
                    radius: 26,

                    backgroundColor:
                        CartzyColors.navy,

                    child: Text(
                      _getInitial(),

                      style:
                          const TextStyle(
                        color: Colors.white,

                        fontWeight:
                            FontWeight.bold,

                        fontSize: 18,
                      ),
                    ),
                  ),

                  const SizedBox(
                    width: 14,
                  ),

                  // --------------------------------------------
                  // USER INFORMATION
                  // --------------------------------------------

                  Expanded(
                    child: Column(
                      crossAxisAlignment:
                          CrossAxisAlignment.start,

                      children: [
                        Text(
                          userName.trim().isEmpty
                              ? 'Buyer'
                              : userName,

                          maxLines: 2,

                          overflow:
                              TextOverflow.ellipsis,

                          style:
                              TextStyle(
                            fontSize: 15,

                            fontWeight:
                                FontWeight.bold,

                            color:
                                CartzyColors.textOf(context, light: CartzyColors.navy),
                          ),
                        ),

                        const SizedBox(
                          height: 2,
                        ),

                        Text(
                          'BUYER',

                          style:
                              TextStyle(
                            fontSize: 11,

                            color:
                                CartzyColors.mutedOf(context, light: CartzyColors.gray),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(
              height: 24,
            ),

            // ==================================================
            // MY ACCOUNT
            // ==================================================

            const _MenuSectionLabel(
              'MY ACCOUNT',
            ),

            // ==================================================
            // PROFILE
            // ==================================================

            _MenuItem(
              icon: Icons.person_outline,

              label: 'Profile',

              trailingBadge:
                  'Verify ID',

              onTap: () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) =>
                        BuyerAccountScreen(
                      userId: userId,

                      onBack: () {
                        Navigator.of(
                          context,
                        ).pop();
                      },
                      onProfileUpdated: onProfileUpdated,
                    ),
                  ),
                );
              },
            ),

            // ==================================================
            // BANKS & CARDS
            // ==================================================

            _MenuItem(
              icon:
                  Icons.credit_card_outlined,

              label: 'Banks & Cards',

              onTap: onAddresses,
            ),

            // ==================================================
            // ADDRESSES
            // ==================================================

           _MenuItem(
  icon: Icons.location_on_outlined,
  label: 'Addresses',
  onTap: onAddresses,
),

            // ==================================================
            // CHANGE PASSWORD
            // ==================================================

            _MenuItem(
              icon: Icons.lock_outline,

              label: 'Change Password',

              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const ChangePasswordScreen()),
              ),
            ),

            // ==================================================
            // PRIVACY SETTINGS
            // ==================================================

            _MenuItem(
              icon:
                  Icons.privacy_tip_outlined,

              label: 'Privacy Settings',

              onTap: () =>
                  _comingSoon(context),
            ),

            // ==================================================
            // NOTIFICATION SETTINGS
            // ==================================================

            _MenuItem(
              icon:
                  Icons.notifications_none,

              label:
                  'Notification Settings',

              onTap: () =>
                  _comingSoon(context),
            ),

            const SizedBox(
              height: 20,
            ),

            // ==================================================
            // ROLE ACCESS CONTROL (TC-BUY-030)
            // ==================================================

            const _MenuSectionLabel(
              'ROLE ACCESS CONTROL (TC-BUY-030)',
            ),

            _MenuItem(
              icon: Icons.shield_outlined,
              label: 'Test Other Role Access (403)',
              onTap: () => _showAccessControlTestDialog(context),
            ),

            const SizedBox(
              height: 20,
            ),

            // ==================================================
            // ACTIVITY
            // ==================================================

            const _MenuSectionLabel(
              'ACTIVITY',
            ),

            // ==================================================
            // MY PURCHASE
            // ==================================================

            _MenuItem(
              icon:
                  Icons.receipt_long_outlined,

              label: 'My Purchase',

              onTap:
                  onMyPurchase,
            ),

            _MenuItem(
              icon: Icons.rate_review_outlined,
              label: 'Reviews & Ratings',
              onTap: () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => ReviewsScreen(
                      onBack: () => Navigator.of(context).pop(),
                      onViewOrders: () {
                        Navigator.of(context).pop();
                        onMyPurchase();
                      },
                    ),
                  ),
                );
              },
            ),

            // ==================================================
            // NOTIFICATIONS
            // ==================================================

            ValueListenableBuilder<int>(
              valueListenable: NotificationService.unreadCountNotifier,
              builder: (context, unreadCount, _) {
                return _MenuItem(
                  icon: Icons.notifications_outlined,
                  label: 'Notifications',
                  badgeCount: unreadCount,
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

            // ==================================================
            // MY VOUCHERS
            // ==================================================

            _MenuItem(
              icon: Icons.confirmation_number_outlined,
              label: 'My Vouchers',
              onTap: () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => VouchersScreen(
                      onBack: () => Navigator.of(context).pop(),
                      onUseAtCheckout: () {
                        Navigator.of(context).pop();
                        onMyPurchase();
                      },
                    ),
                  ),
                );
              },
            ),

            // ==================================================
            // MY CARTZY COINS
            // ==================================================

            _MenuItem(
              icon:
                  Icons.monetization_on_outlined,

              label: 'My Cartzy Coins',

              onTap: () =>
                  _comingSoon(context),
            ),

            const SizedBox(
              height: 12,
            ),

            ValueListenableBuilder<ThemeMode>(
              valueListenable: ThemeController.mode,
              builder: (context, _, __) {
                final isDark = ThemeController.isDark(context);
                return Container(
                  decoration: BoxDecoration(
                    color: CartzyColors.surfaceOf(context),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: CartzyColors.borderOf(context)),
                  ),
                  child: SwitchListTile(
                    value: isDark,
                    onChanged: (v) => ThemeController.setDark(v),
                    secondary: Icon(
                      isDark ? Icons.dark_mode_rounded : Icons.light_mode_rounded,
                      color: isDark ? CartzyColors.mutedOf(context, light: CartzyColors.darkAccent) : CartzyColors.textOf(context, light: CartzyColors.navy),
                    ),
                    title: Text(
                      'Dark Mode',
                      style: GoogleFonts.dmSans(
                        fontWeight: FontWeight.w600,
                        fontSize: 14,
                        color: CartzyColors.textOf(context),
                      ),
                    ),
                    activeThumbColor: CartzyColors.darkAccent,
                  ),
                );
              },
            ),

            const SizedBox(
              height: 24,
            ),

            // ==================================================
            // LOGOUT
            // ==================================================

                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: onLogout,
                    icon: const Icon(Icons.logout_rounded, size: 19),
                    label: const Text('Log out'),
                    style: OutlinedButton.styleFrom(
                      backgroundColor: CartzyColors.elevatedOf(context, light: CartzyColors.rose50Warm),
                      foregroundColor: CartzyColors.readableOf(context, light: CartzyColors.rose700, background: CartzyColors.bgOf(context)),
                      minimumSize: const Size(0, 52),
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                      side: BorderSide(color: CartzyColors.borderOf(context, light: CartzyColors.rose200)),
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
                      color: CartzyColors.textOf(context, light: CartzyColors.brandDark),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Made for your everyday favorites.',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      color: CartzyColors.mutedOf(context, light: CartzyColors.brandDeep),
                    ),
                  ),
                ],
              ),
          ],
        ),
      ),
    );
  }

  // ============================================================
  // COMING SOON
  // ============================================================

  void _comingSoon(
    BuildContext context,
  ) {
    ScaffoldMessenger.of(context)
        .showSnackBar(
      const SnackBar(
        content: Text(
          'This section is coming soon',
        ),
      ),
    );
  }

  void _showAccessControlTestDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Icon(Icons.shield_outlined, color: CartzyColors.textOf(context, light: CartzyColors.navy)),
            const SizedBox(width: 8),
            const Expanded(
              child: Text(
                'Role Access Control (TC-BUY-030)',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: const Text(
          'Attempt to open another role\'s dashboard to verify 403 Forbidden access denial:',
          style: TextStyle(fontSize: 13, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              onNavigateRole?.call('sellerDashboard');
            },
            child: Text('Seller Dashboard (403)', style: TextStyle(color: CartzyColors.textOf(context, light: CartzyColors.navy))),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              onNavigateRole?.call('logisticsDashboard');
            },
            child: Text('Logistics Dashboard (403)', style: TextStyle(color: CartzyColors.textOf(context, light: CartzyColors.navy))),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              onNavigateRole?.call('riderDashboard');
            },
            child: Text('Rider Dashboard (403)', style: TextStyle(color: CartzyColors.textOf(context, light: CartzyColors.navy))),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              onNavigateRole?.call('adminDashboard');
            },
            child: Text('Admin Dashboard (403)', style: TextStyle(color: CartzyColors.textOf(context, light: CartzyColors.navy))),
          ),
        ],
      ),
    );
  }
}

// ============================================================
// SECTION LABEL
// ============================================================

class _MenuSectionLabel
    extends StatelessWidget {
  final String text;

  const _MenuSectionLabel(
    this.text,
  );

  @override
  Widget build(
    BuildContext context,
  ) {
    return Padding(
      padding:
          const EdgeInsets.only(
        bottom: 10,
        left: 4,
      ),

      child: Text(
        text,

        style:
            TextStyle(
          fontSize: 11,

          fontWeight:
              FontWeight.bold,

          color:
              CartzyColors.mutedOf(context, light: CartzyColors.gray),

          letterSpacing: 0.6,
        ),
      ),
    );
  }
}

// ============================================================
// MENU ITEM
// ============================================================

class _MenuItem
    extends StatelessWidget {
  final IconData icon;

  final String label;

  final String? trailingBadge;

  final int? badgeCount;

  final VoidCallback onTap;

  const _MenuItem({
    required this.icon,
    required this.label,
    this.trailingBadge,
    this.badgeCount,
    required this.onTap,
  });

  @override
  Widget build(
    BuildContext context,
  ) {
    return InkWell(
      onTap: onTap,

      borderRadius:
          BorderRadius.circular(12),

      child: Container(
        margin:
            const EdgeInsets.only(
          bottom: 8,
        ),

        padding:
            const EdgeInsets.symmetric(
          horizontal: 14,
          vertical: 14,
        ),

        decoration:
            BoxDecoration(
          color:
              CartzyColors.surfaceOf(context),
          border: Border.all(
            color: CartzyColors.borderOf(context),
          ),

          borderRadius:
              BorderRadius.circular(
            12,
          ),

          boxShadow: [
            BoxShadow(
              color:
                  Colors.black.withValues(
                alpha: 0.03,
              ),

              blurRadius: 8,

              offset:
                  const Offset(0, 2),
            ),
          ],
        ),

        child: Row(
          children: [
            // ----------------------------------------------
            // ICON
            // ----------------------------------------------

            Icon(
              icon,

              color:
                  CartzyColors.mutedOf(context, light: CartzyColors.coral),

              size: 20,
            ),

            const SizedBox(
              width: 14,
            ),

            // ----------------------------------------------
            // LABEL
            // ----------------------------------------------

            Expanded(
              child: Text(
                label,

                style:
                    TextStyle(
                  fontSize: 14,

                  color:
                      CartzyColors.textOf(context, light: CartzyColors.text),
                ),
              ),
            ),

            // ----------------------------------------------
            // TRAILING BADGE
            // ----------------------------------------------

            if (trailingBadge != null)
              Container(
                margin:
                    const EdgeInsets.only(
                  right: 8,
                ),

                padding:
                    const EdgeInsets.symmetric(
                  horizontal: 8,
                  vertical: 4,
                ),

                decoration:
                    BoxDecoration(
                  color:
                      CartzyColors.gold
                          .withValues(
                    alpha: 0.2,
                  ),

                  borderRadius:
                      BorderRadius.circular(
                    20,
                  ),
                ),

                child: Text(
                  trailingBadge!,

                  style:
                      TextStyle(
                    fontSize: 10,

                    fontWeight:
                        FontWeight.bold,

                    color:
                        CartzyColors.textOf(context, light: CartzyColors.navy),
                  ),
                ),
              ),

            // ----------------------------------------------
            // NUMBER BADGE
            // ----------------------------------------------

            if (badgeCount != null)
              Container(
                margin:
                    const EdgeInsets.only(
                  right: 8,
                ),

                width: 18,

                height: 18,

                alignment:
                    Alignment.center,

                decoration:
                    const BoxDecoration(
                  color:
                      CartzyColors.navy,

                  shape:
                      BoxShape.circle,
                ),

                child: Text(
                  '$badgeCount',

                  style:
                      const TextStyle(
                    fontSize: 10,

                    color:
                        Colors.white,

                    fontWeight:
                        FontWeight.bold,
                  ),
                ),
              ),

            // ----------------------------------------------
            // ARROW
            // ----------------------------------------------

            Icon(
              Icons.chevron_right,

              color:
                  CartzyColors.mutedOf(context, light: CartzyColors.gray),

              size: 18,
            ),
          ],
        ),
      ),
    );
  }
}

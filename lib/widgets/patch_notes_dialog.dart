import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';

class PatchNotesDialog extends StatelessWidget {
  /// Whether this dialog is showing a prompt to download a newer version,
  /// or showing what was just updated in the installed version.
  final bool isUpdatePrompt;
  final String version;
  final String? currentVersion;
  final List<String> highlights;
  final String? downloadUrl;
  final VoidCallback? onDismiss;
  final VoidCallback? onUpdate;

  const PatchNotesDialog({
    super.key,
    required this.version,
    this.currentVersion,
    this.isUpdatePrompt = false,
    this.highlights = const [],
    this.downloadUrl,
    this.onDismiss,
    this.onUpdate,
  });

  static Future<void> showPatchNotes(
    BuildContext context, {
    required String version,
    List<String>? highlights,
    VoidCallback? onDismiss,
  }) {
    return showDialog(
      context: context,
      barrierDismissible: true,
      builder: (ctx) => PatchNotesDialog(
        version: version,
        isUpdatePrompt: false,
        highlights: highlights ?? defaultHighlights(version),
        onDismiss: () {
          Navigator.of(ctx).pop();
          onDismiss?.call();
        },
      ),
    );
  }

  static Future<void> showUpdateAvailable(
    BuildContext context, {
    required String newVersion,
    required String currentVersion,
    List<String>? highlights,
    required String downloadUrl,
    VoidCallback? onLater,
    VoidCallback? onUpdate,
  }) {
    return showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => PatchNotesDialog(
        version: newVersion,
        currentVersion: currentVersion,
        isUpdatePrompt: true,
        highlights: highlights ?? defaultHighlights(newVersion),
        downloadUrl: downloadUrl,
        onDismiss: () {
          Navigator.of(ctx).pop();
          onLater?.call();
        },
        onUpdate: () {
          Navigator.of(ctx).pop();
          onUpdate?.call();
        },
      ),
    );
  }

  static List<String> defaultHighlights(String version) {
    if (version == '1.2.11' || version == 'v1.2.11') {
      return const [
        'Real Store & Product Ratings: Authentic rating scores and review counts reflect actual buyer feedback across all product cards, search results, and store pages.',
        'Brand Logo in Navigation Menu: The official Cartzy logo wordmark now headers the hamburger drawer.',
        'Two-Column Daily Discover Grid: Discover feed is organized into a clean, balanced two-column grid.',
        'Optimized Checkout Header & Clean Quantity Box: Fixed checkout header text overflow on smaller screens and refined inline quantity field styling.',
      ];
    }
    if (version == '1.2.10' || version == 'v1.2.10') {
      return const [
        'Dedicated Change Password Screen: Update and secure your account in a dedicated full screen with live password strength checklist and visibility toggles.',
        'Direct Inline Quantity Keypad: Type quantity numbers directly in cart and product details without modal pop-ups; keypad opens instantly on tap.',
        'Accurate Cart Item Counter: Cart badge and counters now strictly count unique products in your cart rather than total units.',
        'Real-time Inventory Protection: Quantities are validated and clamped against live stock with clear inventory notifications.',
      ];
    }
    if (version == '1.2.9' || version == 'v1.2.9') {
      return const [
        'Accurate Cart Counter: The cart badge and item counter now strictly count distinct products in your cart rather than multiplying total unit quantities.',
        'Subtotal & Checkout Alignment: Item counters consistently show the number of products selected across cart, wishlist, and checkout.',
        'Bulk Shipping Notification: Orders with 100+ items indicate direct seller warehouse delivery.',
        'Auto-Sliding Category Showcase: Interactive auto-scrolling category carousel on the home feed.',
      ];
    }
    if (version == '1.2.8' || version == 'v1.2.8') {
      return const [
        'Bulk Quantity Warehouse Note: Orders with 100+ items now clearly note that bulk quantities ship directly from seller warehouses.',
        'Auto-Sliding Category Carousel: Find your next favorite now auto-scrolls through categories smoothly.',
        'Guest Drawer Navigation: Hamburger menu is now accessible from the guest home header for effortless browsing.',
        'Quantity Keypad Input: Direct typing for item quantities in both cart and product pages with live inventory limits.',
      ];
    }
    if (version == '1.2.7' || version == 'v1.2.7') {
      return const [
        'Custom Quantity Input: Buyers can now tap the quantity number in the cart and product details to type any desired quantity directly with stock validation.',
        'Cozy Navigation Clearance: Cozy now floats cleanly above the bottom navigation bar without covering any tabs.',
        'Simplified Hamburger Drawer: Removed redundant profile information from the menu header in favor of clean Cartzy branding.',
        'Reliable API Checkout: Streamlined order placement with real-time stock verification and duplicate prevention.',
      ];
    }
    if (version == '1.2.6' || version == 'v1.2.6') {
      return const [
        'Startup Stability Fix: Resolved white screen on startup for signed-in buyers.',
        'Profile Contrast Polish: Darkened profile text and card styling for clear readability.',
        'Avatar Privacy Update: Google profile photos are no longer applied by default without user selection.',
        'Cozy Bear Companion via Menu: Tapping Cozy in the hamburger drawer summons Cozy in her cute interactive bear form on screen.',
      ];
    }
    if (version == '1.2.5' || version == 'v1.2.5') {
      return const [
        'Cozy Bear Companion in Menu: Tapping Cozy in the hamburger drawer now brings out Cozy in her cute interactive bear form on screen instead of jumping directly into the text chat.',
        'Interactive Bear Features: Cozy waves and greets you with an on-screen speech bubble, can be dragged freely, and opens chat only when tapped.',
        'Clean Home Browsing: Cozy is completely hidden until summoned, with no distracting side tabs.',
      ];
    }
    if (version == '1.2.4' || version == 'v1.2.4') {
      return const [
        'Live Inventory & Stock Verification: Real-time database stock checks automatically adjust quantities and notify if items sell out.',
        'Recently Viewed Products: Seamlessly re-discover products you previously viewed from your profile.',
        'Interactive Navigation Drawer: Quick-access hamburger menu for fast browsing across guest and buyer accounts.',
        'Direct Shop Chat & Popup Cozy: Instant 1-on-1 seller messaging and modal Cozy shopping assistant.',
      ];
    }
    if (version == '1.2.3' || version == 'v1.2.3') {
      return const [
        'Direct Shop Chat: Tapping the Chat button beside any seller immediately opens that specific shop chat with zero selection list needed.',
        'Popup Cozy Chatbot: Cozy now opens as a sleek interactive popup bottom sheet without leaving your current screen.',
        'Store & Product Context: Seller chat automatically displays store name, logo, and product preview when inquiring from listings.',
        'Profile Clean-up: Streamlined Help section with direct Cozy card access and clean navigation clearance.',
      ];
    }
    if (version == '1.2.2' || version == 'v1.2.2') {
      return const [
        'Cozy in Profile Help: Cozy the AI shopping buddy is now integrated directly into your Profile HELP section with an interactive featured card.',
        'Clean Home Experience: Floating screen launcher removed so your home dashboard browsing is completely unobstructed.',
        'Safe Navigation Spacing: Generous layout clearance guaranteeing Cozy and all Help options stay comfortably above the bottom navigation bar.',
        'Direct Support Access: Instant one-tap access to chat with Cozy directly from Help & Support modal options.',
      ];
    }
    if (version == '1.2.1' || version == 'v1.2.1') {
      return const [
        'Cozy Chatbot Upgrade: Enhanced AI shopping assistant with quick conversation starters, order status inquiries, and live product suggestions.',
        'Interactive Drag-to-Discard: Freely drag Cozy anywhere on screen or drop into the bottom "X" target to dismiss with haptic feedback.',
        'High-Contrast Send Button: Deep burgundy action button with drop shadow so the send icon is always crystal clear.',
        'New Bottom Nav Inbox: Dedicated Inbox tab (Home, Cart, Sale, Inbox, Profile) for seller messaging and followed stores.',
        'Direct My Orders Filtering: Tapping To ship, Shipped, Delivered, or Cancelled navigates directly to that order stage.',
      ];
    }
    if (version == '1.2.0' || version == 'v1.2.0') {
      return const [
        'New Bottom Nav Inbox: Dedicated Inbox tab (Home, Cart, Sale, Inbox, Profile) for seller chats and followed stores.',
        'Followed Stores Tab: Track, visit, and chat with all your followed sellers in one place.',
        'Draggable Cozy & Discard: Freely drag Cozy anywhere on screen or drag down to the "X" discard zone to dismiss.',
        'Vibrant Chat Send Button: Rich burgundy gradient button with shadow for maximum send icon visibility.',
        'My Orders Stage Filtering: Tapping To ship, Shipped, Delivered, or Cancelled navigates directly to that order stage.',
        'Profile Photo Cropper & Angle Editor: Rotate, scale, crop, and adjust your profile picture with real-time preview.',
      ];
    }
    if (version == '1.1.9' || version == 'v1.1.9') {
      return const [
        'Custom Profile Picture: Upload or snap a photo directly from your account header or profile settings.',
        'Live Address Book & PSGC API: Real-time region, province, city, and barangay cascading matching the web platform.',
        'Independent Section Flow: Smooth direct tab returns and bug-free navigation between Profile, Sale, and Home.',
        'Enhanced Stability: Seamless session persistence and camera integration across all devices.',
      ];
    }
    if (version == '1.1.8' || version == 'v1.1.8') {
      return const [
        'Independent Section Navigation: Seamlessly jump between Home, Cart, Sale, and Profile without nested stacking.',
        'Direct Home Return: Tapping Home from Sale, Cart, or Profile takes you directly back to Home in a single step.',
        'Back Button Predictability: Hardware back button reliably returns you to the Home dashboard before exiting.',
        'Unified Tab Switching: Consistent navigation behavior across both authenticated and guest shopping modes.',
      ];
    }
    if (version == '1.1.7' || version == 'v1.1.7') {
      return const [
        'Google Sign-In Stability: Streamlined native authentication with clear error feedback.',
        'Official Store Action Buttons: Polished Follow and Chat buttons with centered text and zero clipping.',
        'Product Card Proportions: Expanded grid card heights so prices and Add buttons are never cut off.',
        'Wishlist Grid Polish: Optimized card heights for responsive browsing across all screen sizes.',
        'Performance & UI Polish: Smooth navigation and refined layout throughout the store views.',
      ];
    }
    if (version == '1.1.6' || version == 'v1.1.6') {
      return const [
        'Official Storefront: Visit seller stores directly from products with live catalog, reviews, and store info.',
        'Follow & Chat with Sellers: Seamless seller following and real-time messaging from store pages.',
        'Profile Settings Screen: Dedicated full-screen profile management with birthday calendar picker.',
        'Header Message Shortcut: Live unread message indicator on the dashboard header.',
        'Persistent Navigation: Fluid animated navigation bar on Cart and Sale pages.',
      ];
    }
    if (version == '1.1.5' || version == 'v1.1.5') {
      return const [
        'Live Database Wishlist: Real-time sync for saving products with instant database connection.',
        'Header Wishlist Badge: Live red counter badge on the dashboard for saved items.',
        'Full Wishlist Management: Dedicated screen with Add to Cart, Undo removal, and direct product navigation.',
        'Messages Redesign: Soft luxury gradients, refined message bubbles, and smooth responsive layout.',
        'Distraction-Free Search: Full-screen search tab without navigator obstruction.',
      ];
    }
    if (version == '1.1.4' || version == 'v1.1.4') {
      return const [
        'Live Messages: Real-time buyer messaging and product inquiries synced directly with the database.',
        'Database Notifications: Accurate real-time alerts with unread counter, order deep linking, and swipe dismissal.',
        'Interactive Search: Trending search suggestions, typeahead search, and categorized product grid.',
        'Search History & Filters: Instant recent search recall and 4-way product sorting.',
        'Auto-Sliding Navigation: Responsive bottom navigation bar that slides down on scroll.',
      ];
    }
    return const [
      'Interactive Search: Tap the header search bar to discover trending items, explore categories, and get live suggestions.',
      'Search History: Easily view recent searches with one-tap search and single or clear-all removal.',
      'Product Sorting: Sort items by Relevance, Latest, Top Sales, and Price Low/High with responsive layout.',
      'Refined Navigation: Unified 4-tab bottom navigation with auto-slide down on scroll across all main screens.',
      'Account Layout: Refreshed Guest Account screen aligned with Cart and Wishlist styling.',
    ];
  }

  static Future<void> launchUpdateUrl(String url) async {
    const channel = MethodChannel('com.cartzy.app/updater');
    try {
      await channel.invokeMethod('openUrl', {'url': url});
    } catch (_) {
      // Fallback: copy link to clipboard if native intent fails
      await Clipboard.setData(ClipboardData(text: url));
    }
  }

  @override
  Widget build(BuildContext context) {
    // ── Lavender and Rose Gold Merged Palette ──
    const lavenderDark = Color(0xFF564B68);
    const lavenderMedium = Color(0xFF7C6E93);
    const lavenderLight = Color(0xFFFAF7FB);
    const lavenderBorder = Color(0xFFE5DEE8);

    const roseGoldDark = Color(0xFF8C5A50);
    const roseGoldMedium = Color(0xFFC08B7F);
    const roseGoldSoft = Color(0xFFFBF1EE);
    const roseGoldBorder = Color(0xFFE8D4CD);

    return Dialog(
      backgroundColor: Colors.white,
      surfaceTintColor: Colors.transparent,
      elevation: 12,
      insetPadding: const EdgeInsets.symmetric(horizontal: 22, vertical: 24),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(26),
        side: const BorderSide(color: roseGoldBorder, width: 1.2),
      ),
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 420),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // ── Top Gradient Header Banner (Merging Lavender & Rose Gold) ──
              Container(
                padding: const EdgeInsets.fromLTRB(20, 24, 20, 18),
                decoration: const BoxDecoration(
                  borderRadius: BorderRadius.vertical(top: Radius.circular(25)),
                  gradient: LinearGradient(
                    colors: [
                      lavenderDark,
                      Color(0xFF6B5872),
                      roseGoldMedium,
                      roseGoldDark,
                    ],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Column(
                  children: [
                    // Icon badge with merged circular gradient
                    Container(
                      width: 58,
                      height: 58,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: Colors.white.withValues(alpha: 0.15),
                        border: Border.all(
                          color: Colors.white.withValues(alpha: 0.35),
                          width: 1.5,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.15),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Center(
                        child: Icon(
                          isUpdatePrompt
                              ? Icons.system_update_alt_rounded
                              : Icons.auto_awesome_rounded,
                          color: const Color(0xFFFFF0EC),
                          size: 28,
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Version Tag Pill
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.18),
                        borderRadius: BorderRadius.circular(999),
                        border: Border.all(
                          color: Colors.white.withValues(alpha: 0.3),
                          width: 1,
                        ),
                      ),
                      child: Text(
                        isUpdatePrompt
                            ? 'UPDATE AVAILABLE • v$version'
                            : 'PATCH NOTES • v$version',
                        style: GoogleFonts.dmSans(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 1.3,
                          color: Colors.white,
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),

                    // Title
                    Text(
                      isUpdatePrompt ? 'Update Available' : "What's New in Cartzy",
                      textAlign: TextAlign.center,
                      style: GoogleFonts.playfairDisplay(
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                        letterSpacing: -0.3,
                      ),
                    ),
                  ],
                ),
              ),

              // ── Body Content ──
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 18, 20, 20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Subtitle / Intro text
                    Text(
                      isUpdatePrompt
                          ? 'Cartzy v$version is now available with the latest features, performance improvements, and fixes. (You are currently on v${currentVersion ?? '1.1.1'}).'
                          : "Here are the latest updates, fixes, and improvements in this release:",
                      style: GoogleFonts.dmSans(
                        fontSize: 13,
                        color: const Color(0xFF5A525C),
                        height: 1.45,
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Highlights List Container (soft lavender & rose background)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      decoration: BoxDecoration(
                        color: lavenderLight,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: lavenderBorder, width: 1),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: highlights.map((item) {
                          final parts = item.split(':');
                          final hasPrefix = parts.length > 1;
                          final title = hasPrefix ? parts[0].trim() : '';
                          final body = hasPrefix ? parts.sublist(1).join(':').trim() : item;

                          return Padding(
                            padding: const EdgeInsets.symmetric(vertical: 5),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Rose Gold accent dot/icon
                                Container(
                                  margin: const EdgeInsets.only(top: 3),
                                  width: 18,
                                  height: 18,
                                  decoration: BoxDecoration(
                                    color: roseGoldSoft,
                                    shape: BoxShape.circle,
                                    border: Border.all(color: roseGoldBorder),
                                  ),
                                  child: const Icon(
                                    Icons.check_rounded,
                                    size: 12,
                                    color: roseGoldDark,
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: RichText(
                                    text: TextSpan(
                                      style: GoogleFonts.dmSans(
                                        fontSize: 12.5,
                                        color: const Color(0xFF423B47),
                                        height: 1.4,
                                      ),
                                      children: [
                                        if (hasPrefix)
                                          TextSpan(
                                            text: '$title: ',
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w700,
                                              color: lavenderDark,
                                            ),
                                          ),
                                        TextSpan(text: body),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // ── Buttons ──
                    if (isUpdatePrompt) ...[
                      Row(
                        children: [
                          // Later Button (Lavender outlined)
                          Expanded(
                            child: OutlinedButton(
                              onPressed: onDismiss,
                              style: OutlinedButton.styleFrom(
                                foregroundColor: lavenderMedium,
                                side: const BorderSide(color: lavenderBorder, width: 1.2),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14),
                                ),
                                padding: const EdgeInsets.symmetric(vertical: 14),
                              ),
                              child: Text(
                                'LATER',
                                style: GoogleFonts.dmSans(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                  letterSpacing: 0.8,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),

                          // Update Now Button (Merged Lavender & Rose Gold Gradient)
                          Expanded(
                            child: Container(
                              height: 48,
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(14),
                                gradient: const LinearGradient(
                                  colors: [lavenderDark, roseGoldDark],
                                  begin: Alignment.centerLeft,
                                  end: Alignment.centerRight,
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color: roseGoldDark.withValues(alpha: 0.28),
                                    blurRadius: 10,
                                    offset: const Offset(0, 4),
                                  ),
                                ],
                              ),
                              child: ElevatedButton(
                                onPressed: () async {
                                  if (downloadUrl != null && downloadUrl!.isNotEmpty) {
                                    await launchUpdateUrl(downloadUrl!);
                                  }
                                  onUpdate?.call();
                                },
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.transparent,
                                  shadowColor: Colors.transparent,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(14),
                                  ),
                                  padding: EdgeInsets.zero,
                                ),
                                child: Text(
                                  'UPDATE NOW',
                                  style: GoogleFonts.dmSans(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w700,
                                    letterSpacing: 0.8,
                                    color: Colors.white,
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ] else ...[
                      // Patch Notes Dismiss Button
                      Container(
                        height: 48,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(14),
                          gradient: const LinearGradient(
                            colors: [lavenderDark, roseGoldDark],
                            begin: Alignment.centerLeft,
                            end: Alignment.centerRight,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: roseGoldDark.withValues(alpha: 0.25),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: ElevatedButton(
                          onPressed: onDismiss,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.transparent,
                            shadowColor: Colors.transparent,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(14),
                            ),
                            padding: EdgeInsets.zero,
                          ),
                          child: Text(
                            'Got it, explore now!',
                            style: GoogleFonts.dmSans(
                              fontSize: 14,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

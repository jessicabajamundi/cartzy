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

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/widgets/barcode_scanner_page.dart';

class RiderCommonAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final VoidCallback onBack;
  final String? sector;
  final VoidCallback? onLogout;

  const RiderCommonAppBar({
    super.key,
    required this.title,
    required this.onBack,
    this.sector = 'Metro South Sector',
    this.onLogout,
  });

  @override
  Size get preferredSize => const Size.fromHeight(64);

  void _openScanner(BuildContext context) async {
    final code = await Navigator.of(context).push<String>(
      MaterialPageRoute(
        builder: (_) => const BarcodeScannerPage(
          title: 'Scan Waybill Barcode',
        ),
      ),
    );
    if (code != null && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Waybill Scanned: $code'),
          backgroundColor: const Color(0xFF2E7D32),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isMobile = MediaQuery.of(context).size.width < 768;

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(
          bottom: BorderSide(color: Color(0xFFEFE8EA), width: 1.1),
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: SizedBox(
          height: 60,
          child: Padding(
            padding: EdgeInsets.symmetric(horizontal: isMobile ? 10 : 20),
            child: Row(
          children: [
            // Back Button
            InkWell(
              onTap: onBack,
              borderRadius: BorderRadius.circular(8),
              child: Container(
                padding: const EdgeInsets.all(7),
                decoration: BoxDecoration(
                  color: const Color(0xFFFBF8F9),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFFEFE8EA)),
                ),
                child: const Icon(
                  Icons.arrow_back_rounded,
                  size: 18,
                  color: Color(0xFF261E30),
                ),
              ),
            ),
            const SizedBox(width: 12),

            // Title
            Flexible(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Flexible(
                    child: Text(
                      title,
                      style: GoogleFonts.cormorantGaramond(
                        fontSize: isMobile ? 18 : 22,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF261E30),
                        letterSpacing: -0.3,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  if (!isMobile && sector != null) ...[
                    const SizedBox(width: 10),
                    Flexible(
                      child: Text(
                        '|   Sector: $sector',
                        style: GoogleFonts.dmSans(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w500,
                          color: const Color(0xFF8A7780),
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ],
              ),
            ),

            const Spacer(),

            // Scan Waybill Barcode button
            InkWell(
              onTap: () => _openScanner(context),
              borderRadius: BorderRadius.circular(20),
              child: Container(
                padding: EdgeInsets.symmetric(
                  horizontal: isMobile ? 10 : 14,
                  vertical: 7,
                ),
                decoration: BoxDecoration(
                  color: const Color(0xFF46333D),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF46333D).withValues(alpha: 0.2),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.qr_code_scanner,
                      size: 14,
                      color: Colors.white,
                    ),
                    if (!isMobile) ...[
                      const SizedBox(width: 7),
                      Text(
                        'Scan Waybill Barcode',
                        style: GoogleFonts.dmSans(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),

            const SizedBox(width: 10),

            // Online Badge
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFFEAF6ED),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0xFFC7E7CE)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 6,
                    height: 6,
                    decoration: const BoxDecoration(
                      color: Color(0xFF2E7D32),
                      shape: BoxShape.circle,
                    ),
                  ),
                  if (!isMobile) ...[
                    const SizedBox(width: 6),
                    Text(
                      'Online (Accepting Jobs)',
                      style: GoogleFonts.dmSans(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFF2E7D32),
                      ),
                    ),
                  ],
                ],
              ),
            ),

            if (onLogout != null) ...[
              const SizedBox(width: 10),
              IconButton(
                icon: const Icon(
                  Icons.logout_rounded,
                  size: 18,
                  color: Color(0xFF8A7780),
                ),
                tooltip: 'Exit / Logout',
                onPressed: onLogout,
              ),
            ],
          ],
        ),
      ),
    ),
  ),
);
  }
}

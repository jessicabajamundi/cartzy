import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

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

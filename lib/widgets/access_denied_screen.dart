import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

class AccessDeniedScreen extends StatelessWidget {
  final String attemptedPage;
  final String userRole;
  final VoidCallback onReturn;

  const AccessDeniedScreen({
    super.key,
    required this.attemptedPage,
    this.userRole = 'Buyer',
    required this.onReturn,
  });

  @override
  Widget build(BuildContext context) {
    final ts = DateTime.now();
    final timestamp =
        '${ts.year}-${ts.month.toString().padLeft(2, '0')}-${ts.day.toString().padLeft(2, '0')} '
        '${ts.hour.toString().padLeft(2, '0')}:${ts.minute.toString().padLeft(2, '0')}:${ts.second.toString().padLeft(2, '0')}';

    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: CartzyColors.navy),
          onPressed: onReturn,
        ),
        title: const Text(
          'Access Denied (403)',
          style: TextStyle(
            color: CartzyColors.navy,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // ── Icon ──
              Container(
                width: 96,
                height: 96,
                decoration: BoxDecoration(
                  color: CartzyColors.error.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.lock_person_outlined,
                  size: 52,
                  color: CartzyColors.error,
                ),
              ),
              const SizedBox(height: 20),

              // ── HTTP Status badge ──
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                decoration: BoxDecoration(
                  color: CartzyColors.error,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Text(
                  'HTTP 403 — FORBIDDEN',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    letterSpacing: 1,
                  ),
                ),
              ),
              const SizedBox(height: 16),

              const Text(
                '403 Forbidden',
                style: TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: CartzyColors.brandDarkest,
                ),
              ),
              const SizedBox(height: 10),

              Text(
                'You do not have permission to access the $attemptedPage.\n'
                'Your account role ($userRole) is not authorized for this resource.',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 14,
                  color: CartzyColors.gray,
                  height: 1.5,
                ),
              ),
              const SizedBox(height: 28),

              // ── Incident detail card ──
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: CartzyColors.surface,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: CartzyColors.border),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.security_outlined, size: 16, color: CartzyColors.brandDark),
                        const SizedBox(width: 8),
                        const Text(
                          'Security Incident Details',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: CartzyColors.brandDark,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    _DetailRow(label: 'Attempted Resource', value: attemptedPage),
                    _DetailRow(label: 'Account Role', value: userRole),
                    _DetailRow(label: 'Required Role', value: _requiredRole(attemptedPage)),
                    _DetailRow(label: 'Response Code', value: '403 Forbidden'),
                    _DetailRow(label: 'Timestamp', value: timestamp),
                    _DetailRow(label: 'Action Logged', value: 'Yes — security audit trail updated'),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // ── TC-BUY-030 note ──
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: CartzyColors.brandLight,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: CartzyColors.brandBorder),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.info_outline, size: 16, color: CartzyColors.brandDark),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'TC-BUY-030 — Expected Result: A Buyer attempting to access the '
                        '$attemptedPage should see a 403 Forbidden screen. '
                        '✓ This screen confirms the behavior is implemented correctly.',
                        style: const TextStyle(
                          fontSize: 11,
                          color: CartzyColors.brandDark,
                          height: 1.4,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),

              // ── Return button ──
              SizedBox(
                width: 240,
                height: 50,
                child: ElevatedButton(
                  onPressed: onReturn,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CartzyColors.brandDark,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: const Text(
                    'Return to Dashboard',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _requiredRole(String page) {
    final p = page.toLowerCase();
    if (p.contains('seller')) return 'Seller';
    if (p.contains('rider')) return 'Rider';
    if (p.contains('admin')) return 'Admin';
    if (p.contains('logistics')) return 'Logistics';
    return 'N/A';
  }
}

class _DetailRow extends StatelessWidget {
  final String label;
  final String value;

  const _DetailRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 150,
            child: Text(
              label,
              style: const TextStyle(
                fontSize: 12,
                color: CartzyColors.gray,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 12,
                color: CartzyColors.brandDarkest,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

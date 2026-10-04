import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'login_screen.dart';

// ================================================================
// Shown right after tapping Login/Sign Up on the guest screen.
// User picks Buyer / Rider, then lands on a login
// page already set up for that role.
// ================================================================

class AccountTypeSelectionScreen extends StatelessWidget {
  final ValueChanged<AccountType> onSelected;
  final VoidCallback onBackToGuest;

  const AccountTypeSelectionScreen({
    super.key,
    required this.onSelected,
    required this.onBackToGuest,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.background,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back,
            color: CartzyColors.navy,
          ),
          onPressed: onBackToGuest,
        ),
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(
              horizontal: 28,
              vertical: 20,
            ),
            child: ConstrainedBox(
              constraints: const BoxConstraints(
                maxWidth: 450,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'Continue As',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                      color: CartzyColors.navy,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Choose your account type to continue',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 14,
                      color: CartzyColors.gray,
                    ),
                  ),
                  const SizedBox(height: 36),

                  // ==================================================
                  // BUYER
                  // ==================================================

                  _RoleCard(
                    label: 'Buyer',
                    description: 'Shop and order products',
                    icon: Icons.shopping_bag_outlined,
                    onTap: () => onSelected(AccountType.buyer),
                  ),

                  const SizedBox(height: 16),

                  // ==================================================
                  // RIDER
                  // ==================================================

                  _RoleCard(
                    label: 'Rider',
                    description: 'Pick up and deliver orders',
                    icon: Icons.two_wheeler_outlined,
                    onTap: () => onSelected(AccountType.rider),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ================================================================
// ROLE CARD
// ================================================================

class _RoleCard extends StatelessWidget {
  final String label;
  final String description;
  final IconData icon;
  final VoidCallback onTap;

  const _RoleCard({
    required this.label,
    required this.description,
    required this.icon,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: CartzyColors.surface,
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: CartzyColors.border,
            ),
          ),
          child: Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: CartzyColors.coral.withValues(
                    alpha: 0.12,
                  ),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  icon,
                  color: CartzyColors.coral,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment:
                      CrossAxisAlignment.start,
                  children: [
                    Text(
                      label,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: CartzyColors.navy,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      description,
                      style: const TextStyle(
                        fontSize: 12,
                        color: CartzyColors.gray,
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(
                Icons.chevron_right,
                color: CartzyColors.gray,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
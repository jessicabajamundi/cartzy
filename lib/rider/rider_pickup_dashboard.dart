import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'rider_common_app_bar.dart';

class MerchantPickupItem {
  final String id;
  final String storeName;
  final String initials;
  final String phone;
  final String address;
  final int packageCount;
  final double weightKg;
  final String scheduledWindow;
  final String merchantNote;
  String status; // 'PENDING' or 'CONFIRMED'

  MerchantPickupItem({
    required this.id,
    required this.storeName,
    required this.initials,
    required this.phone,
    required this.address,
    required this.packageCount,
    required this.weightKg,
    required this.scheduledWindow,
    required this.merchantNote,
    this.status = 'PENDING',
  });
}

class RiderPickupDashboard extends StatefulWidget {
  final String token;
  final VoidCallback onBack;

  const RiderPickupDashboard({
    super.key,
    required this.token,
    required this.onBack,
  });

  @override
  State<RiderPickupDashboard> createState() => _RiderPickupDashboardState();
}

class _RiderPickupDashboardState extends State<RiderPickupDashboard> {
  String _activeFilter = 'All Pickups'; // 'All Pickups', 'Pending', 'Confirmed'

  final List<MerchantPickupItem> _items = [
    MerchantPickupItem(
      id: 'PK-101',
      storeName: 'TechZone Official Mall',
      initials: 'TE',
      phone: '09171112222',
      address: '123 Tech Hub Bldg, Brgy. San Antonio, Pasig City',
      packageCount: 6,
      weightKg: 8.5,
      scheduledWindow: '2:00 PM - 4:00 PM (Oct 16)',
      merchantNote: 'High-value electronics. Sealed in bubblewrap security bags.',
      status: 'PENDING',
    ),
    MerchantPickupItem(
      id: 'PK-102',
      storeName: 'TechZone Official Mall',
      initials: 'TE',
      phone: '09171112222',
      address: '45 Commerce Center, Alabang, Muntinlupa City',
      packageCount: 4,
      weightKg: 5.2,
      scheduledWindow: '4:30 PM - 5:30 PM (Oct 16)',
      merchantNote: 'Contact warehouse manager Kuya Ben at 0917-888-9900.',
      status: 'CONFIRMED',
    ),
    MerchantPickupItem(
      id: 'PK-103',
      storeName: 'Circuit Hub',
      initials: 'CI',
      phone: '0917-888-0000',
      address: '88 Quezon Blvd, Brgy. Halang, Calamba, Laguna',
      packageCount: 14,
      weightKg: 22.1,
      scheduledWindow: '10:00 AM (Oct 12)',
      merchantNote: 'Boxes are large. Bring extra packaging tape.',
      status: 'PENDING',
    ),
    MerchantPickupItem(
      id: 'PK-104',
      storeName: 'TechZone Official Mall',
      initials: 'TE',
      phone: '09171112222',
      address: '123 Rizal St, Brgy. Poblacion, Santa Cruz, Laguna',
      packageCount: 8,
      weightKg: 12.4,
      scheduledWindow: '2:00 PM (Oct 11)',
      merchantNote: 'Fragile electronics. Handle with care.',
      status: 'PENDING',
    ),
    MerchantPickupItem(
      id: 'PK-105',
      storeName: 'Hearth & Home',
      initials: 'HE',
      phone: '0917-888-0000',
      address: '45 Mabini Ave, Brgy. San Jose, San Pablo, Laguna',
      packageCount: 5,
      weightKg: 3.8,
      scheduledWindow: '3:30 PM (Oct 11)',
      merchantNote: 'Fragile decor items. Keep upright in storage box.',
      status: 'CONFIRMED',
    ),
  ];

  int get _pendingCount => _items.where((e) => e.status == 'PENDING').length;
  int get _confirmedCount => _items.where((e) => e.status == 'CONFIRMED').length;

  List<MerchantPickupItem> get _filteredItems {
    if (_activeFilter.startsWith('Pending')) {
      return _items.where((e) => e.status == 'PENDING').toList();
    }
    if (_activeFilter.startsWith('Confirmed')) {
      return _items.where((e) => e.status == 'CONFIRMED').toList();
    }
    return _items;
  }

  void _callMerchant(MerchantPickupItem item) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            const Icon(Icons.phone_outlined, color: Color(0xFF46333D)),
            const SizedBox(width: 8),
            Text(
              'Call Merchant',
              style: GoogleFonts.cormorantGaramond(fontWeight: FontWeight.bold, fontSize: 20),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(item.storeName, style: GoogleFonts.dmSans(fontWeight: FontWeight.bold, fontSize: 15)),
            const SizedBox(height: 4),
            Text('Number: ${item.phone}', style: GoogleFonts.dmSans(color: const Color(0xFF554B62))),
            const SizedBox(height: 8),
            Text('Pickup location: ${item.address}', style: GoogleFonts.dmSans(fontSize: 12, color: Colors.black54)),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF46333D),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            icon: const Icon(Icons.call, size: 16),
            label: const Text('Dial Now'),
            onPressed: () {
              Navigator.pop(ctx);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Dialing ${item.phone} (${item.storeName})...'),
                  backgroundColor: const Color(0xFF2E7D32),
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  void _confirmPickup(MerchantPickupItem item) {
    setState(() {
      item.status = 'CONFIRMED';
    });
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Pickup for ${item.storeName} confirmed and in transit!'),
        backgroundColor: const Color(0xFF2E7D32),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFAF7F5),
      body: Column(
        children: [
          RiderCommonAppBar(
            title: 'Items for Pickup',
            sector: 'Metro South Sector',
            onBack: widget.onBack,
          ),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeaderAndFilters(),
                  const SizedBox(height: 20),
                  _buildPickupGrid(),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeaderAndFilters() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final isMobile = constraints.maxWidth < 650;

        final titleCol = Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Merchant Pickup Requests',
              style: GoogleFonts.cormorantGaramond(
                fontSize: 26,
                fontWeight: FontWeight.w700,
                color: const Color(0xFF261E30),
                letterSpacing: -0.4,
              ),
            ),
            const SizedBox(height: 3),
            Text(
              'Collect parcels from seller stores and warehouse locations',
              style: GoogleFonts.dmSans(
                fontSize: 13,
                color: const Color(0xFF8A7780),
              ),
            ),
          ],
        );

        final filterRow = Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            _buildFilterPill('All Pickups', isSelected: _activeFilter == 'All Pickups'),
            _buildFilterPill(
              'Pending ($_pendingCount)',
              isSelected: _activeFilter.startsWith('Pending'),
            ),
            _buildFilterPill(
              'Confirmed ($_confirmedCount)',
              isSelected: _activeFilter.startsWith('Confirmed'),
            ),
          ],
        );

        if (isMobile) {
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              titleCol,
              const SizedBox(height: 14),
              filterRow,
            ],
          );
        }

        return Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            titleCol,
            filterRow,
          ],
        );
      },
    );
  }

  Widget _buildFilterPill(String label, {required bool isSelected}) {
    return InkWell(
      onTap: () => setState(() => _activeFilter = label),
      borderRadius: BorderRadius.circular(20),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF46333D) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? const Color(0xFF46333D) : const Color(0xFFE5DCDD),
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: const Color(0xFF46333D).withValues(alpha: 0.2),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Text(
          label,
          style: GoogleFonts.dmSans(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
            color: isSelected ? Colors.white : const Color(0xFF554B62),
          ),
        ),
      ),
    );
  }

  Widget _buildPickupGrid() {
    final items = _filteredItems;

    if (items.isEmpty) {
      return Container(
        width: double.infinity,
        padding: const EdgeInsets.all(40),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFEFE8EA)),
        ),
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.inventory_2_outlined, size: 48, color: Color(0xFFBAABB0)),
              const SizedBox(height: 12),
              Text(
                'No pickup requests found',
                style: GoogleFonts.cormorantGaramond(
                  fontSize: 20,
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF261E30),
                ),
              ),
              const SizedBox(height: 6),
              Text(
                'There are no requests matching the selected filter.',
                style: GoogleFonts.dmSans(fontSize: 13, color: const Color(0xFF8A7780)),
              ),
            ],
          ),
        ),
      );
    }

    return LayoutBuilder(
      builder: (context, constraints) {
        final crossAxisCount = constraints.maxWidth > 900 ? 2 : 1;
        final itemWidth = crossAxisCount == 2
            ? (constraints.maxWidth - 16) / 2
            : constraints.maxWidth;

        return Wrap(
          spacing: 16,
          runSpacing: 16,
          children: items
              .map(
                (item) => SizedBox(
                  width: itemWidth,
                  child: _buildPickupCard(item),
                ),
              )
              .toList(),
        );
      },
    );
  }

  Widget _buildPickupCard(MerchantPickupItem item) {
    final isConfirmed = item.status == 'CONFIRMED';

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFEFE8EA)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Top: Store avatar, name, phone, status badge
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: const Color(0xFF8C5E58),
                  borderRadius: BorderRadius.circular(8),
                ),
                alignment: Alignment.center,
                child: Text(
                  item.initials,
                  style: GoogleFonts.dmSans(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.storeName,
                      style: GoogleFonts.dmSans(
                        fontSize: 14.5,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF261E30),
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      item.phone,
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        color: const Color(0xFF8A7780),
                      ),
                    ),
                  ],
                ),
              ),
              // Status Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                decoration: BoxDecoration(
                  color: isConfirmed
                      ? const Color(0xFFEDF7EE)
                      : const Color(0xFFFFF7E8),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(
                    color: isConfirmed
                        ? const Color(0xFFD1EAD4)
                        : const Color(0xFFFDE6C5),
                  ),
                ),
                child: Text(
                  item.status,
                  style: GoogleFonts.dmSans(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.4,
                    color: isConfirmed
                        ? const Color(0xFF2E7D32)
                        : const Color(0xFFD97706),
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 14),

          // Address
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(
                Icons.location_on_outlined,
                size: 15,
                color: Color(0xFF8A7780),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  item.address,
                  style: GoogleFonts.dmSans(
                    fontSize: 12.5,
                    color: const Color(0xFF554B62),
                    height: 1.3,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 10),

          // Package Count & Weight
          Row(
            children: [
              RichText(
                text: TextSpan(
                  children: [
                    TextSpan(
                      text: 'Package Count: ',
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        color: const Color(0xFF8A7780),
                      ),
                    ),
                    TextSpan(
                      text: '${item.packageCount} parcels',
                      style: GoogleFonts.dmSans(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF261E30),
                      ),
                    ),
                  ],
                ),
              ),
              const Spacer(),
              RichText(
                text: TextSpan(
                  children: [
                    TextSpan(
                      text: 'Weight: ',
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        color: const Color(0xFF8A7780),
                      ),
                    ),
                    TextSpan(
                      text: '${item.weightKg} kg',
                      style: GoogleFonts.dmSans(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF261E30),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 6),

          // Scheduled Window
          Row(
            children: [
              Text(
                'Scheduled Window:',
                style: GoogleFonts.dmSans(
                  fontSize: 12,
                  color: const Color(0xFF8A7780),
                ),
              ),
              const Spacer(),
              Text(
                item.scheduledWindow,
                style: GoogleFonts.dmSans(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF261E30),
                ),
              ),
            ],
          ),

          const SizedBox(height: 12),

          // Merchant Note
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: const Color(0xFFFBF7F4),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: const Color(0xFFF3ECE7)),
            ),
            child: Text(
              'Merchant Note: ${item.merchantNote}',
              style: GoogleFonts.dmSans(
                fontSize: 12,
                color: const Color(0xFF5A454B),
                fontStyle: FontStyle.italic,
              ),
            ),
          ),

          const SizedBox(height: 16),

          // Actions Row
          Row(
            children: [
              // Call Merchant Button
              OutlinedButton.icon(
                onPressed: () => _callMerchant(item),
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF46333D),
                  side: const BorderSide(color: Color(0xFFE5DCDD)),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                ),
                icon: const Icon(Icons.phone_outlined, size: 14),
                label: Text(
                  'Call Merchant',
                  style: GoogleFonts.dmSans(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),

              const Spacer(),

              // Confirm / Collected status
              if (isConfirmed)
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.check,
                      size: 16,
                      color: Color(0xFF2E7D32),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'Collected & In Transit',
                      style: GoogleFonts.dmSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF2E7D32),
                      ),
                    ),
                  ],
                )
              else
                ElevatedButton.icon(
                  onPressed: () => _confirmPickup(item),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF46333D),
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 9,
                    ),
                  ),
                  icon: const Icon(Icons.check, size: 14),
                  label: Text(
                    'Confirm & Pickup',
                    style: GoogleFonts.dmSans(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}
import 'package:flutter/material.dart';

import 'cartzy_colors.dart';
import 'services/api_service.dart';

class MyAddressesScreen extends StatefulWidget {
  final int userId;
  final VoidCallback onBack;
  final VoidCallback onAddAddress;
  final ValueChanged<Map<String, dynamic>>? onAddressSelected;

  const MyAddressesScreen({
    super.key,
    required this.userId,
    required this.onBack,
    required this.onAddAddress,
    this.onAddressSelected,
  });

  @override
  State<MyAddressesScreen> createState() => _MyAddressesScreenState();
}

class _MyAddressesScreenState extends State<MyAddressesScreen> {
  List<Map<String, dynamic>> addresses = [];
  bool isLoading = true;
  String? loadError;

  @override
  void initState() {
    super.initState();
    _loadAddresses();
  }

  // ------------------------------------------------------------
  // LOAD FROM API
  // ------------------------------------------------------------

  Future<void> _loadAddresses() async {
    setState(() {
      isLoading = true;
      loadError = null;
    });

    try {
      final data = await ApiService.getAddresses(widget.userId);
      if (!mounted) return;
      setState(() {
        addresses = data
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
        isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        loadError = 'Failed to load addresses.\n$e';
        isLoading = false;
      });
    }
  }

  // ------------------------------------------------------------
  // DELETE ADDRESS
  // ------------------------------------------------------------

  void _deleteAddress(Map<String, dynamic> address) {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text(
            'Delete Address',
            style: TextStyle(fontWeight: FontWeight.w600),
          ),
          content: Text(
            'Are you sure you want to delete your ${address['label'] ?? ''} address?',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
            TextButton(
              onPressed: () async {
                Navigator.pop(context);
                try {
                  await ApiService.deleteAddress(
                    int.tryParse(address['id'].toString()) ?? 0,
                  );
                  _showMessage('Address deleted.');
                  _loadAddresses();
                } catch (e) {
                  _showMessage('Failed to delete address.\n$e');
                }
              },
              child: const Text(
                'Delete',
                style: TextStyle(color: CartzyColors.error),
              ),
            ),
          ],
        );
      },
    );
  }

  // ------------------------------------------------------------
  // SET DEFAULT
  // ------------------------------------------------------------

  Future<void> _setDefaultAddress(Map<String, dynamic> address) async {
    try {
      await ApiService.setDefaultAddress(
        int.tryParse(address['id'].toString()) ?? 0,
      );
      _showMessage('Default address updated.');
      _loadAddresses();
    } catch (e) {
      _showMessage('Failed to update default address.\n$e');
    }
  }

  // ------------------------------------------------------------
  // EDIT ADDRESS (hooked up once an edit screen exists)
  // ------------------------------------------------------------

  void _editAddress(Map<String, dynamic> address) {
    _showMessage('Edit address will be connected next.');
  }

  void _showMessage(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  // ------------------------------------------------------------
  // ADDRESS CARD
  // ------------------------------------------------------------

  Widget _addressCard(Map<String, dynamic> address) {
    final bool isDefault =
        address['is_default'] == 1 || address['is_default'] == true;

    return GestureDetector(
      onTap: widget.onAddressSelected == null
          ? null
          : () => widget.onAddressSelected!(address),
      child: Container(
        margin: const EdgeInsets.only(bottom: 16),
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isDefault ? CartzyColors.navy : CartzyColors.border,
            width: isDefault ? 1.5 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: CartzyColors.background,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        address['label'] == 'Work'
                            ? Icons.work_outline
                            : address['label'] == 'Other'
                                ? Icons.location_on_outlined
                                : Icons.home_outlined,
                        size: 16,
                        color: CartzyColors.navy,
                      ),
                      const SizedBox(width: 5),
                      Text(
                        address['label'] ?? 'Address',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: CartzyColors.navy,
                        ),
                      ),
                    ],
                  ),
                ),
                const Spacer(),
                if (isDefault)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
                    decoration: BoxDecoration(
                      color: CartzyColors.navy,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Text(
                      'Default',
                      style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 16),
            Text(
              address['name'] ?? '',
              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: CartzyColors.text),
            ),
            const SizedBox(height: 4),
            Text(
              address['phone'] ?? '',
              style: const TextStyle(fontSize: 13, color: CartzyColors.gray),
            ),
            const SizedBox(height: 10),
            Text(
              _formatAddress(address),
              style: const TextStyle(fontSize: 13, height: 1.5, color: CartzyColors.text),
            ),
            const SizedBox(height: 16),
            const Divider(color: CartzyColors.border, height: 1),
            const SizedBox(height: 8),
            Row(
              children: [
                TextButton.icon(
                  onPressed: () => _editAddress(address),
                  icon: const Icon(Icons.edit_outlined, size: 17),
                  label: const Text('Edit'),
                  style: TextButton.styleFrom(foregroundColor: CartzyColors.navy),
                ),
                TextButton.icon(
                  onPressed: () => _deleteAddress(address),
                  icon: const Icon(Icons.delete_outline, size: 17),
                  label: const Text('Delete'),
                  style: TextButton.styleFrom(foregroundColor: CartzyColors.error),
                ),
                const Spacer(),
                if (!isDefault)
                  TextButton(
                    onPressed: () => _setDefaultAddress(address),
                    child: const Text(
                      'Set as default',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                    ),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  String _formatAddress(Map<String, dynamic> address) {
    final parts = [
      address['house_number'],
      address['street'],
      address['barangay'],
      address['municipality'],
      address['province'],
      address['region'],
      address['postal_code'],
    ];
    return parts
        .where((part) => part != null && part.toString().trim().isNotEmpty)
        .join(', ');
  }

  // ------------------------------------------------------------
  // EMPTY STATE
  // ------------------------------------------------------------

  Widget _emptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 30, vertical: 80),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 90,
              height: 90,
              decoration: const BoxDecoration(
                color: CartzyColors.background,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.location_on_outlined, size: 42, color: CartzyColors.navy),
            ),
            const SizedBox(height: 20),
            const Text(
              'No saved addresses',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: CartzyColors.text),
            ),
            const SizedBox(height: 8),
            const Text(
              'Add an address so you can check out faster.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, height: 1.5, color: CartzyColors.gray),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: widget.onAddAddress,
              icon: const Icon(Icons.add, size: 20),
              label: const Text('Add New Address'),
              style: ElevatedButton.styleFrom(
                backgroundColor: CartzyColors.navy,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ------------------------------------------------------------
  // BUILD
  // ------------------------------------------------------------

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,
      appBar: AppBar(
        backgroundColor: CartzyColors.background,
        elevation: 0,
        leading: IconButton(
          onPressed: widget.onBack,
          icon: const Icon(Icons.arrow_back),
        ),
        title: const Text(
          'My Addresses',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
        ),
      ),
      body: SafeArea(
        child: isLoading
            ? const Center(child: CircularProgressIndicator())
            : loadError != null
                ? Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            loadError!,
                            textAlign: TextAlign.center,
                            style: const TextStyle(color: CartzyColors.error),
                          ),
                          const SizedBox(height: 16),
                          ElevatedButton(
                            onPressed: _loadAddresses,
                            child: const Text('Retry'),
                          ),
                        ],
                      ),
                    ),
                  )
                : addresses.isEmpty
                    ? _emptyState()
                    : RefreshIndicator(
                        onRefresh: _loadAddresses,
                        child: ListView(
                          padding: const EdgeInsets.fromLTRB(20, 10, 20, 30),
                          children: [
                            Row(
                              children: [
                                const Expanded(
                                  child: Text(
                                    'Saved Addresses',
                                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: CartzyColors.text),
                                  ),
                                ),
                                TextButton.icon(
                                  onPressed: widget.onAddAddress,
                                  icon: const Icon(Icons.add, size: 18),
                                  label: const Text('Add New'),
                                  style: TextButton.styleFrom(foregroundColor: CartzyColors.navy),
                                ),
                              ],
                            ),
                            const SizedBox(height: 10),
                            ...addresses.map(_addressCard),
                          ],
                        ),
                      ),
      ),
    );
  }
}

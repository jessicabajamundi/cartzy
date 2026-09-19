import 'package:flutter/material.dart';

import 'cartzy_colors.dart';

class MyAddressesScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback onAddAddress;

  const MyAddressesScreen({
    super.key,
    required this.onBack,
    required this.onAddAddress,
  });

  @override
  State<MyAddressesScreen> createState() =>
      _MyAddressesScreenState();
}

class _MyAddressesScreenState extends State<MyAddressesScreen> {
  // ------------------------------------------------------------
  // TEMPORARY ADDRESS DATA
  // ------------------------------------------------------------
  //
  // This is temporary for the UI.
  // Later we will replace this with data from MySQL/API.
  //

  List<Map<String, dynamic>> addresses = [
    {
      'id': 1,
      'label': 'Home',
      'name': 'Tiffany Joy',
      'phone': '09123456789',
      'street': '123 Main Street',
      'barangay': 'Barangay 1',
      'city': 'Lucena City',
      'province': 'Quezon',
      'region': 'CALABARZON',
      'postalCode': '4301',
      'isDefault': true,
    },
  ];

  // ------------------------------------------------------------
  // DELETE ADDRESS
  // ------------------------------------------------------------

  void _deleteAddress(int index) {
    final address = addresses[index];

    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text(
            'Delete Address',
            style: TextStyle(
              fontWeight: FontWeight.w600,
            ),
          ),
          content: Text(
            'Are you sure you want to delete your ${address['label']} address?',
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(context);
              },
              child: const Text('Cancel'),
            ),
            TextButton(
              onPressed: () {
                setState(() {
                  addresses.removeAt(index);
                });

                Navigator.pop(context);

                _showMessage('Address deleted.');
              },
              child: const Text(
                'Delete',
                style: TextStyle(
                  color: CartzyColors.error,
                ),
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

  void _setDefaultAddress(int index) {
    setState(() {
      for (int i = 0; i < addresses.length; i++) {
        addresses[i]['isDefault'] = i == index;
      }
    });

    _showMessage('Default address updated.');
  }

  // ------------------------------------------------------------
  // EDIT ADDRESS
  // ------------------------------------------------------------

  void _editAddress(int index) {
    _showMessage(
      'Edit address will be connected next.',
    );
  }

  // ------------------------------------------------------------
  // MESSAGE
  // ------------------------------------------------------------

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
      ),
    );
  }

  // ------------------------------------------------------------
  // ADDRESS CARD
  // ------------------------------------------------------------

  Widget _addressCard(
    Map<String, dynamic> address,
    int index,
  ) {
    final bool isDefault =
        address['isDefault'] == true;

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: isDefault
              ? CartzyColors.navy
              : CartzyColors.border,
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
        crossAxisAlignment:
            CrossAxisAlignment.start,
        children: [
          // ------------------------------------------------------
          // TOP ROW
          // ------------------------------------------------------

          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 5,
                ),
                decoration: BoxDecoration(
                  color: CartzyColors.background,
                  borderRadius:
                      BorderRadius.circular(8),
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
                  padding:
                      const EdgeInsets.symmetric(
                    horizontal: 9,
                    vertical: 5,
                  ),
                  decoration: BoxDecoration(
                    color: CartzyColors.navy,
                    borderRadius:
                        BorderRadius.circular(8),
                  ),
                  child: const Text(
                    'Default',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
            ],
          ),

          const SizedBox(height: 16),

          // ------------------------------------------------------
          // NAME
          // ------------------------------------------------------

          Text(
            address['name'] ?? '',
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w600,
              color: CartzyColors.text,
            ),
          ),

          const SizedBox(height: 4),

          // ------------------------------------------------------
          // PHONE
          // ------------------------------------------------------

          Text(
            address['phone'] ?? '',
            style: const TextStyle(
              fontSize: 13,
              color: CartzyColors.gray,
            ),
          ),

          const SizedBox(height: 10),

          // ------------------------------------------------------
          // ADDRESS
          // ------------------------------------------------------

          Text(
            _formatAddress(address),
            style: const TextStyle(
              fontSize: 13,
              height: 1.5,
              color: CartzyColors.text,
            ),
          ),

          const SizedBox(height: 16),

          Divider(
            color: CartzyColors.border,
            height: 1,
          ),

          const SizedBox(height: 8),

          // ------------------------------------------------------
          // ACTIONS
          // ------------------------------------------------------

          Row(
            children: [
              TextButton.icon(
                onPressed: () {
                  _editAddress(index);
                },
                icon: const Icon(
                  Icons.edit_outlined,
                  size: 17,
                ),
                label: const Text('Edit'),
                style: TextButton.styleFrom(
                  foregroundColor:
                      CartzyColors.navy,
                ),
              ),

              TextButton.icon(
                onPressed: () {
                  _deleteAddress(index);
                },
                icon: const Icon(
                  Icons.delete_outline,
                  size: 17,
                ),
                label: const Text('Delete'),
                style: TextButton.styleFrom(
                  foregroundColor:
                      CartzyColors.error,
                ),
              ),

              const Spacer(),

              if (!isDefault)
                TextButton(
                  onPressed: () {
                    _setDefaultAddress(index);
                  },
                  child: const Text(
                    'Set as default',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  // ------------------------------------------------------------
  // FORMAT ADDRESS
  // ------------------------------------------------------------

  String _formatAddress(
    Map<String, dynamic> address,
  ) {
    final parts = [
      address['street'],
      address['barangay'],
      address['city'],
      address['province'],
      address['region'],
      address['postalCode'],
    ];

    return parts
        .where(
          (part) =>
              part != null &&
              part.toString().trim().isNotEmpty,
        )
        .join(', ');
  }

  // ------------------------------------------------------------
  // EMPTY STATE
  // ------------------------------------------------------------

  Widget _emptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(
          horizontal: 30,
          vertical: 80,
        ),
        child: Column(
          mainAxisAlignment:
              MainAxisAlignment.center,
          children: [
            Container(
              width: 90,
              height: 90,
              decoration: BoxDecoration(
                color: CartzyColors.background,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.location_on_outlined,
                size: 42,
                color: CartzyColors.navy,
              ),
            ),

            const SizedBox(height: 20),

            const Text(
              'No saved addresses',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: CartzyColors.text,
              ),
            ),

            const SizedBox(height: 8),

            const Text(
              'Add an address so you can check out faster.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                height: 1.5,
                color: CartzyColors.gray,
              ),
            ),

            const SizedBox(height: 24),

            ElevatedButton.icon(
              onPressed: widget.onAddAddress,
              icon: const Icon(
                Icons.add,
                size: 20,
              ),
              label: const Text(
                'Add New Address',
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor:
                    CartzyColors.navy,
                foregroundColor: Colors.white,
                padding:
                    const EdgeInsets.symmetric(
                  horizontal: 22,
                  vertical: 14,
                ),
                shape:
                    RoundedRectangleBorder(
                  borderRadius:
                      BorderRadius.circular(12),
                ),
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

      // ----------------------------------------------------------
      // APP BAR
      // ----------------------------------------------------------

      appBar: AppBar(
        backgroundColor:
            CartzyColors.background,
        elevation: 0,

        leading: IconButton(
          onPressed: widget.onBack,
          icon: const Icon(
            Icons.arrow_back,
          ),
        ),

        title: const Text(
          'My Addresses',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),

      // ----------------------------------------------------------
      // BODY
      // ----------------------------------------------------------

      body: addresses.isEmpty
          ? _emptyState()
          : SafeArea(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(
                  20,
                  10,
                  20,
                  30,
                ),
                children: [
                  // ------------------------------------------------
                  // HEADER
                  // ------------------------------------------------

                  Row(
                    children: [
                      const Expanded(
                        child: Text(
                          'Saved Addresses',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight:
                                FontWeight.w600,
                            color:
                                CartzyColors.text,
                          ),
                        ),
                      ),

                      TextButton.icon(
                        onPressed:
                            widget.onAddAddress,
                        icon: const Icon(
                          Icons.add,
                          size: 18,
                        ),
                        label: const Text(
                          'Add New',
                        ),
                        style:
                            TextButton.styleFrom(
                          foregroundColor:
                              CartzyColors.navy,
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 10),

                  // ------------------------------------------------
                  // ADDRESS LIST
                  // ------------------------------------------------

                  ...List.generate(
                    addresses.length,
                    (index) {
                      return _addressCard(
                        addresses[index],
                        index,
                      );
                    },
                  ),
                ],
              ),
            ),
    );
  }
}
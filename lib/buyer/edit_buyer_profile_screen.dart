import 'package:flutter/material.dart';
import 'package:cartzy/theme/cartzy_colors.dart';

class EditBuyerProfileScreen extends StatefulWidget {
  final VoidCallback onBack;

  const EditBuyerProfileScreen({
    super.key,
    required this.onBack,
  });

  @override
  State<EditBuyerProfileScreen> createState() =>
      _EditBuyerProfileScreenState();
}

class _EditBuyerProfileScreenState
    extends State<EditBuyerProfileScreen> {
  // =========================================================
  // CONTROLLERS
  // =========================================================

  final TextEditingController _nameController =
      TextEditingController(text: 'Tiffany Joy Leonardo');

  final TextEditingController _usernameController =
      TextEditingController(text: 'tiffany');

  final TextEditingController _emailController =
      TextEditingController(text: 'email@example.com');

  final TextEditingController _contactController =
      TextEditingController(text: '09123456789');

  final TextEditingController _passwordController =
      TextEditingController();

  final TextEditingController _houseNumberController =
      TextEditingController();

  // =========================================================
  // ADDRESS
  // =========================================================

  String _selectedProvince = 'Select Province';

  String _selectedMunicipality =
      'Select Municipality/City';

  String _selectedBarangay = 'Select Barangay';

  // =========================================================
  // PASSWORD
  // =========================================================

  bool _obscurePassword = true;

  // =========================================================
  // DISPOSE
  // =========================================================

  @override
  void dispose() {
    _nameController.dispose();
    _usernameController.dispose();
    _emailController.dispose();
    _contactController.dispose();
    _passwordController.dispose();
    _houseNumberController.dispose();

    super.dispose();
  }

  // =========================================================
  // TEXT FIELD
  // =========================================================

  Widget _buildTextField({
    required String label,
    required TextEditingController controller,
    String? hintText,
    TextInputType keyboardType = TextInputType.text,
    bool obscureText = false,
    Widget? suffixIcon,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: CartzyColors.navy,
          ),
        ),

        const SizedBox(height: 8),

        TextFormField(
          controller: controller,
          keyboardType: keyboardType,
          obscureText: obscureText,

          decoration: InputDecoration(
            hintText: hintText,
            suffixIcon: suffixIcon,

            filled: true,
            fillColor: Colors.white,

            contentPadding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical: 15,
            ),

            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(
                color: CartzyColors.border,
              ),
            ),

            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(
                color: CartzyColors.border,
              ),
            ),

            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(
                color: CartzyColors.navy,
                width: 1.5,
              ),
            ),
          ),
        ),

        const SizedBox(height: 18),
      ],
    );
  }

  // =========================================================
  // DROPDOWN
  // =========================================================

  Widget _buildDropdown({
    required String label,
    required String value,
    required List<String> items,
    required ValueChanged<String?> onChanged,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: CartzyColors.navy,
          ),
        ),

        const SizedBox(height: 8),

        DropdownButtonFormField<String>(
          initialValue: value,

          decoration: InputDecoration(
            filled: true,
            fillColor: Colors.white,

            contentPadding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical: 4,
            ),

            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(
                color: CartzyColors.border,
              ),
            ),

            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(
                color: CartzyColors.border,
              ),
            ),

            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(
                color: CartzyColors.navy,
                width: 1.5,
              ),
            ),
          ),

          items: items.map((item) {
            return DropdownMenuItem<String>(
              value: item,
              child: Text(
                item,
                style: const TextStyle(
                  fontSize: 15,
                  color: CartzyColors.navy,
                ),
              ),
            );
          }).toList(),

          onChanged: onChanged,
        ),

        const SizedBox(height: 18),
      ],
    );
  }

  // =========================================================
  // NEUMORPHIC SAVE BUTTON
  // =========================================================

  Widget _buildNeumorphicButton({
    required String text,
    required VoidCallback onPressed,
  }) {
    return Container(
      width: double.infinity,
      height: 52,

      decoration: BoxDecoration(
        color: CartzyColors.navy,

        borderRadius: BorderRadius.circular(12),

        boxShadow: const [
          BoxShadow(
            color: Colors.black26,
            offset: Offset(4, 5),
            blurRadius: 6,
          ),
        ],
      ),

      child: ElevatedButton(
        onPressed: onPressed,

        style: ElevatedButton.styleFrom(
          backgroundColor: CartzyColors.navy,
          foregroundColor: Colors.white,

          elevation: 0,

          shadowColor: Colors.transparent,

          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),

        child: Text(
          text,
          style: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }

  // =========================================================
  // SAVE CHANGES
  // =========================================================

  void _saveChanges() {
    if (_nameController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Please enter your full name.',
          ),
        ),
      );

      return;
    }

    if (_usernameController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Please enter your username.',
          ),
        ),
      );

      return;
    }

    if (_emailController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Please enter your email address.',
          ),
        ),
      );

      return;
    }

    if (_contactController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Please enter your contact number.',
          ),
        ),
      );

      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text(
          'Profile updated successfully!',
        ),
      ),
    );
  }

  // =========================================================
  // BUILD
  // =========================================================

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CartzyColors.background,

      // =====================================================
      // APP BAR
      // =====================================================

      appBar: AppBar(
        backgroundColor: CartzyColors.background,
        elevation: 0,

        leading: IconButton(
          onPressed: widget.onBack,

          icon: const Icon(
            Icons.arrow_back,
            color: CartzyColors.navy,
          ),
        ),

        title: const Text(
          'Edit My Information',
          style: TextStyle(
            color: CartzyColors.navy,
            fontWeight: FontWeight.bold,
            fontSize: 19,
          ),
        ),

        centerTitle: true,
      ),

      // =====================================================
      // BODY
      // =====================================================

      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(
            20,
            10,
            20,
            30,
          ),

          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,

            children: [
              // =================================================
              // PROFILE ICON
              // =================================================

              Center(
                child: Column(
                  children: [
                    Container(
                      width: 90,
                      height: 90,

                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: CartzyColors.navy,

                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.18),
                            offset: const Offset(4, 5),
                            blurRadius: 8,
                          ),
                        ],
                      ),

                      child: const Icon(
                        Icons.person,
                        color: Colors.white,
                        size: 48,
                      ),
                    ),

                    const SizedBox(height: 12),

                    const Text(
                      'Update your account information',
                      style: TextStyle(
                        fontSize: 14,
                        color: CartzyColors.gray,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 30),

              // =================================================
              // ACCOUNT INFORMATION
              // =================================================

              const Text(
                'Account Information',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: CartzyColors.navy,
                ),
              ),

              const SizedBox(height: 18),

              // FULL NAME

              _buildTextField(
                label: 'Full Name',
                controller: _nameController,
                hintText: 'Enter your full name',
              ),

              // USERNAME

              _buildTextField(
                label: 'Username',
                controller: _usernameController,
                hintText: 'Enter your username',
              ),

              // EMAIL

              _buildTextField(
                label: 'Email Address',
                controller: _emailController,
                hintText: 'Enter your email address',
                keyboardType:
                    TextInputType.emailAddress,
              ),

              // CONTACT NUMBER

              _buildTextField(
                label: 'Contact Number',
                controller: _contactController,
                hintText: 'Enter your contact number',
                keyboardType: TextInputType.phone,
              ),

              // PASSWORD

              _buildTextField(
                label: 'Password',
                controller: _passwordController,
                hintText: 'Enter new password',
                obscureText: _obscurePassword,

                suffixIcon: IconButton(
                  onPressed: () {
                    setState(() {
                      _obscurePassword =
                          !_obscurePassword;
                    });
                  },

                  icon: Icon(
                    _obscurePassword
                        ? Icons.visibility_off_outlined
                        : Icons.visibility_outlined,

                    color: CartzyColors.gray,
                  ),
                ),
              ),

              const SizedBox(height: 8),

              // =================================================
              // DELIVERY ADDRESS
              // =================================================

              const Text(
                'Delivery Address',
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: CartzyColors.navy,
                ),
              ),

              const SizedBox(height: 18),

              // HOUSE NUMBER / STREET

              _buildTextField(
                label: 'House Number / Street',
                controller:
                    _houseNumberController,
                hintText:
                    'Enter house number and street',
              ),

              // PROVINCE

              _buildDropdown(
                label: 'Province',

                value: _selectedProvince,

                items: const [
                  'Select Province',
                  'Metro Manila',
                  'Cavite',
                  'Laguna',
                  'Rizal',
                  'Batangas',
                  'Bulacan',
                  'Pampanga',
                  'Cebu',
                  'Davao del Sur',
                ],

                onChanged: (value) {
                  if (value == null) return;

                  setState(() {
                    _selectedProvince = value;

                    _selectedMunicipality =
                        'Select Municipality/City';

                    _selectedBarangay =
                        'Select Barangay';
                  });
                },
              ),

              // MUNICIPALITY / CITY

              _buildDropdown(
                label: 'Municipality / City',

                value: _selectedMunicipality,

                items: const [
                  'Select Municipality/City',
                  'Quezon City',
                  'Manila',
                  'Makati',
                  'Pasig',
                  'Taguig',
                  'Antipolo',
                  'Bacoor',
                  'Dasmarinas',
                  'Calamba',
                ],

                onChanged: (value) {
                  if (value == null) return;

                  setState(() {
                    _selectedMunicipality =
                        value;

                    _selectedBarangay =
                        'Select Barangay';
                  });
                },
              ),

              // BARANGAY

              _buildDropdown(
                label: 'Barangay',

                value: _selectedBarangay,

                items: const [
                  'Select Barangay',
                  'Barangay 1',
                  'Barangay 2',
                  'Barangay 3',
                  'Barangay 4',
                  'Barangay 5',
                  'Barangay 6',
                  'Barangay 7',
                  'Barangay 8',
                  'Barangay 9',
                  'Barangay 10',
                ],

                onChanged: (value) {
                  if (value == null) return;

                  setState(() {
                    _selectedBarangay =
                        value;
                  });
                },
              ),

              const SizedBox(height: 10),

              // =================================================
              // SAVE BUTTON
              // =================================================

              _buildNeumorphicButton(
                text: 'SAVE CHANGES',
                onPressed: _saveChanges,
              ),

              const SizedBox(height: 14),

              // =================================================
              // CANCEL BUTTON
              // =================================================

              SizedBox(
                width: double.infinity,
                height: 52,

                child: OutlinedButton(
                  onPressed: widget.onBack,

                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(
                      color: CartzyColors.navy,
                    ),

                    shape: RoundedRectangleBorder(
                      borderRadius:
                          BorderRadius.circular(12),
                    ),
                  ),

                  child: const Text(
                    'CANCEL',
                    style: TextStyle(
                      color: CartzyColors.navy,
                      fontWeight: FontWeight.bold,
                      fontSize: 15,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
import 'package:flutter/material.dart';

import 'cartzy_colors.dart';
import 'psgc_api.dart';

class AddAddressScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onSaved;

  const AddAddressScreen({
    super.key,
    required this.onBack,
    this.onSaved,
  });

  @override
  State<AddAddressScreen> createState() => _AddAddressScreenState();
}

class _AddAddressScreenState extends State<AddAddressScreen> {
  final TextEditingController _nameController =
      TextEditingController();

  final TextEditingController _phoneController =
      TextEditingController();

  final TextEditingController _streetController =
      TextEditingController();

  final TextEditingController _postalController =
      TextEditingController();

  String _addressLabel = 'Home';

  List<PsgcRegion> _regions = [];
  List<PsgcProvince> _provinces = [];
  List<PsgcCityMunicipality> _cities = [];
  List<PsgcBarangay> _barangays = [];

  PsgcRegion? _selectedRegion;
  PsgcProvince? _selectedProvince;
  PsgcCityMunicipality? _selectedCity;
  PsgcBarangay? _selectedBarangay;

  bool _loadingRegions = false;
  bool _loadingProvinces = false;
  bool _loadingCities = false;
  bool _loadingBarangays = false;

  bool _isDefault = false;

  @override
  void initState() {
    super.initState();
    _loadRegions();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _streetController.dispose();
    _postalController.dispose();
    super.dispose();
  }

  // ------------------------------------------------------------
  // LOAD REGIONS
  // ------------------------------------------------------------

  Future<void> _loadRegions() async {
    setState(() {
      _loadingRegions = true;
    });

    try {
      final regions = await PsgcApi.getRegions();

      if (!mounted) return;

      setState(() {
        _regions = regions;
      });
    } catch (e) {
      _showMessage('Unable to load regions.');
    } finally {
      if (mounted) {
        setState(() {
          _loadingRegions = false;
        });
      }
    }
  }

  // ------------------------------------------------------------
  // LOAD PROVINCES
  // ------------------------------------------------------------

  Future<void> _loadProvinces(String regionCode) async {
    setState(() {
      _loadingProvinces = true;

      _provinces = [];
      _cities = [];
      _barangays = [];

      _selectedProvince = null;
      _selectedCity = null;
      _selectedBarangay = null;
    });

    try {
      final provinces =
          await PsgcApi.getProvincesByRegion(regionCode);

      if (!mounted) return;

      setState(() {
        _provinces = provinces;
      });
    } catch (e) {
      _showMessage('Unable to load provinces.');
    } finally {
      if (mounted) {
        setState(() {
          _loadingProvinces = false;
        });
      }
    }
  }

  // ------------------------------------------------------------
  // LOAD CITIES
  // ------------------------------------------------------------

  Future<void> _loadCities(String provinceCode) async {
    setState(() {
      _loadingCities = true;

      _cities = [];
      _barangays = [];

      _selectedCity = null;
      _selectedBarangay = null;
    });

    try {
      final cities =
          await PsgcApi.getMunicipalities(provinceCode);

      if (!mounted) return;

      setState(() {
        _cities = cities;
      });
    } catch (e) {
      _showMessage('Unable to load cities.');
    } finally {
      if (mounted) {
        setState(() {
          _loadingCities = false;
        });
      }
    }
  }

  // ------------------------------------------------------------
  // LOAD BARANGAYS
  // ------------------------------------------------------------

  Future<void> _loadBarangays(String cityCode) async {
    setState(() {
      _loadingBarangays = true;

      _barangays = [];
      _selectedBarangay = null;
    });

    try {
      final barangays =
          await PsgcApi.getBarangays(cityCode);

      if (!mounted) return;

      setState(() {
        _barangays = barangays;
      });
    } catch (e) {
      _showMessage('Unable to load barangays.');
    } finally {
      if (mounted) {
        setState(() {
          _loadingBarangays = false;
        });
      }
    }
  }

  // ------------------------------------------------------------
  // SAVE
  // ------------------------------------------------------------

  void _saveAddress() {
    if (_nameController.text.trim().isEmpty) {
      _showMessage('Please enter your full name.');
      return;
    }

    if (_phoneController.text.trim().isEmpty) {
      _showMessage('Please enter your phone number.');
      return;
    }

    if (_streetController.text.trim().isEmpty) {
      _showMessage('Please enter your street address.');
      return;
    }

    if (_selectedRegion == null) {
      _showMessage('Please select a region.');
      return;
    }

    if (_selectedProvince == null) {
      _showMessage('Please select a province.');
      return;
    }

    if (_selectedCity == null) {
      _showMessage('Please select a city or municipality.');
      return;
    }

    if (_selectedBarangay == null) {
      _showMessage('Please select a barangay.');
      return;
    }

    if (_postalController.text.trim().isEmpty) {
      _showMessage('Please enter your postal code.');
      return;
    }

    // For now, we are only creating the UI.
    // We will connect this to MySQL/API next.

    widget.onSaved?.call();

    _showMessage('Address saved successfully.');

    Future.delayed(const Duration(milliseconds: 500), () {
      if (mounted) {
        widget.onBack();
      }
    });
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
  // TEXT FIELD
  // ------------------------------------------------------------

  Widget _textField({
    required String label,
    required TextEditingController controller,
    TextInputType? keyboardType,
    String? hint,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: TextField(
        controller: controller,
        keyboardType: keyboardType,
        decoration: InputDecoration(
          labelText: label,
          hintText: hint,
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(
              color: CartzyColors.border,
            ),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(
              color: CartzyColors.border,
            ),
          ),
        ),
      ),
    );
  }

  // ------------------------------------------------------------
  // DROPDOWN
  // ------------------------------------------------------------

  Widget _dropdown<T>({
    required String label,
    required T? value,
    required List<T> items,
    required String Function(T) labelBuilder,
    required ValueChanged<T?> onChanged,
    bool enabled = true,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: DropdownButtonFormField<T>(
        value: value,
        isExpanded: true,
        decoration: InputDecoration(
          labelText: label,
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(
              color: CartzyColors.border,
            ),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(
              color: CartzyColors.border,
            ),
          ),
        ),
        items: items.map((item) {
          return DropdownMenuItem<T>(
            value: item,
            child: Text(labelBuilder(item)),
          );
        }).toList(),
        onChanged: enabled ? onChanged : null,
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
          icon: const Icon(Icons.arrow_back),
          onPressed: widget.onBack,
        ),

        title: const Text(
          'Add New Address',
          style: TextStyle(
            fontWeight: FontWeight.w600,
          ),
        ),
      ),

      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),

          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Address Label',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),

              const SizedBox(height: 8),

              Row(
                children: [
                  _labelButton('Home'),
                  const SizedBox(width: 8),
                  _labelButton('Work'),
                  const SizedBox(width: 8),
                  _labelButton('Other'),
                ],
              ),

              const SizedBox(height: 24),

              _textField(
                label: 'Full Name',
                controller: _nameController,
                hint: 'Enter recipient name',
              ),

              _textField(
                label: 'Phone Number',
                controller: _phoneController,
                keyboardType: TextInputType.phone,
                hint: '09XXXXXXXXX',
              ),

              const SizedBox(height: 4),

              const Text(
                'Location',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                ),
              ),

              const SizedBox(height: 12),

              _dropdown<PsgcRegion>(
                label: 'Region',
                value: _selectedRegion,
                items: _regions,
                labelBuilder: (item) => item.name,
                enabled: !_loadingRegions,
                onChanged: (region) {
                  if (region == null) return;

                  setState(() {
                    _selectedRegion = region;
                  });

                  _loadProvinces(region.code);
                },
              ),

              _dropdown<PsgcProvince>(
                label: 'Province',
                value: _selectedProvince,
                items: _provinces,
                labelBuilder: (item) => item.name,
                enabled:
                    _selectedRegion != null &&
                    !_loadingProvinces,
                onChanged: (province) {
                  if (province == null) return;

                  setState(() {
                    _selectedProvince = province;
                  });

                  _loadCities(province.code);
                },
              ),

              _dropdown<PsgcCityMunicipality>(
                label: 'City / Municipality',
                value: _selectedCity,
                items: _cities,
                labelBuilder: (item) => item.name,
                enabled:
                    _selectedProvince != null &&
                    !_loadingCities,
                onChanged: (city) {
                  if (city == null) return;

                  setState(() {
                    _selectedCity = city;
                  });

                  _loadBarangays(city.code);
                },
              ),

              _dropdown<PsgcBarangay>(
                label: 'Barangay',
                value: _selectedBarangay,
                items: _barangays,
                labelBuilder: (item) => item.name,
                enabled:
                    _selectedCity != null &&
                    !_loadingBarangays,
                onChanged: (barangay) {
                  setState(() {
                    _selectedBarangay = barangay;
                  });
                },
              ),

              _textField(
                label: 'Street Address',
                controller: _streetController,
                hint: 'House / Unit / Street',
              ),

              _textField(
                label: 'Postal Code',
                controller: _postalController,
                keyboardType: TextInputType.number,
                hint: 'e.g. 4301',
              ),

              const SizedBox(height: 4),

              SwitchListTile(
                contentPadding: EdgeInsets.zero,
                title: const Text(
                  'Set as default address',
                  style: TextStyle(
                    fontWeight: FontWeight.w500,
                  ),
                ),
                value: _isDefault,
                onChanged: (value) {
                  setState(() {
                    _isDefault = value;
                  });
                },
              ),

              const SizedBox(height: 20),

              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  onPressed: _saveAddress,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: CartzyColors.navy,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                  child: const Text(
                    'Save Address',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ),

              const SizedBox(height: 30),
            ],
          ),
        ),
      ),
    );
  }

  // ------------------------------------------------------------
  // ADDRESS LABEL BUTTON
  // ------------------------------------------------------------

  Widget _labelButton(String label) {
    final selected = _addressLabel == label;

    return Expanded(
      child: OutlinedButton(
        onPressed: () {
          setState(() {
            _addressLabel = label;
          });
        },
        style: OutlinedButton.styleFrom(
          backgroundColor:
              selected ? CartzyColors.navy : Colors.white,
          foregroundColor:
              selected ? Colors.white : CartzyColors.text,
          side: BorderSide(
            color: selected
                ? CartzyColors.navy
                : CartzyColors.border,
          ),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
        child: Text(label),
      ),
    );
  }
}
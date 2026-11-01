import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:cartzy/utils/input_rules.dart';
import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/psgc_api.dart';

class AddAddressScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onSaved;
  final int userId;
  final Map<String, dynamic>? initialAddress;

  const AddAddressScreen({
    super.key,
    required this.onBack,
    required this.userId,
    this.onSaved,
    this.initialAddress,
  });

  @override
  State<AddAddressScreen> createState() => _AddAddressScreenState();
}

class _AddAddressScreenState extends State<AddAddressScreen> {
  final _formKey = GlobalKey<FormState>();

  final TextEditingController nameController = TextEditingController();
  final TextEditingController phoneController = TextEditingController();
  final TextEditingController houseNumberController = TextEditingController();
  final TextEditingController streetController = TextEditingController();
  final TextEditingController postalController = TextEditingController();

  String selectedLabel = 'Home';
  bool isDefault = false;
  bool isSaving = false;

  // PSGC DATA
  List<PsgcRegion> regions = [];
  List<PsgcProvince> provinces = [];
  List<PsgcCityMunicipality> municipalities = [];
  List<PsgcBarangay> barangays = [];

  PsgcRegion? selectedRegion;
  PsgcProvince? selectedProvince;
  PsgcCityMunicipality? selectedMunicipality;
  PsgcBarangay? selectedBarangay;

  bool loadingRegions = true;
  bool loadingProvinces = false;
  bool loadingMunicipalities = false;
  bool loadingBarangays = false;

  @override
  void initState() {
    super.initState();

    final init = widget.initialAddress;
    if (init != null) {
      nameController.text = init['name']?.toString() ?? '';
      phoneController.text = init['phone']?.toString() ?? '';
      houseNumberController.text = init['house_number']?.toString() ?? '';
      streetController.text = init['street']?.toString() ?? '';
      postalController.text = init['postal_code']?.toString() ?? '';
      selectedLabel = init['label']?.toString() ?? 'Home';
      isDefault = init['is_default'] == true || init['is_default'] == 1;
    } else {
      final user = AuthService.currentUser;
      nameController.text = user?['name']?.toString() ?? '';
      phoneController.text = user?['phone']?.toString() ?? '';
    }

    _loadRegions();
  }

  @override
  void dispose() {
    nameController.dispose();
    phoneController.dispose();
    houseNumberController.dispose();
    streetController.dispose();
    postalController.dispose();
    super.dispose();
  }

  // ==========================================================
  // LOAD REGIONS
  // ==========================================================

  Future<void> _loadRegions() async {
    try {
      final data = await PsgcApi.getRegions();
      if (!mounted) return;

      setState(() {
        regions = data;
        loadingRegions = false;
      });

      // Pre-select if editing
      final init = widget.initialAddress;
      if (init != null && init['region'] != null) {
        final regName = init['region'].toString().toLowerCase();
        final match = regions.firstWhere(
          (r) => r.name.toLowerCase() == regName ||
                 r.code == init['region'] ||
                 (regName.contains('ncr') && r.code == '130000000'),
          orElse: () => regions.first,
        );
        await _onRegionChanged(match, preselectProvince: init['province']?.toString(), preselectCity: init['municipality']?.toString(), preselectBrgy: init['barangay']?.toString());
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        loadingRegions = false;
      });
      _showError('Failed to load regions.\n$e');
    }
  }

  // ==========================================================
  // REGION CHANGED
  // ==========================================================

  Future<void> _onRegionChanged(
    PsgcRegion? region, {
    String? preselectProvince,
    String? preselectCity,
    String? preselectBrgy,
  }) async {
    setState(() {
      selectedRegion = region;
      selectedProvince = null;
      selectedMunicipality = null;
      selectedBarangay = null;
      provinces = [];
      municipalities = [];
      barangays = [];
      loadingProvinces = region != null;
    });

    if (region == null) return;

    final isNcr = region.code == '130000000' || region.name.toUpperCase().contains('NCR');

    if (isNcr) {
      final ncrProvince = PsgcProvince(code: '130000000', name: 'Metro Manila');
      setState(() {
        provinces = [ncrProvince];
        selectedProvince = ncrProvince;
        loadingProvinces = false;
        loadingMunicipalities = true;
      });

      try {
        final cities = await PsgcApi.getMunicipalities('130000000', isNcrRegion: true);
        if (!mounted) return;
        setState(() {
          municipalities = cities;
          loadingMunicipalities = false;
        });

        if (preselectCity != null) {
          final cityMatch = municipalities.where(
            (c) => c.name.toLowerCase() == preselectCity.toLowerCase(),
          ).firstOrNull;
          if (cityMatch != null) {
            await _onMunicipalityChanged(cityMatch, preselectBrgy: preselectBrgy);
          }
        }
      } catch (e) {
        if (!mounted) return;
        setState(() {
          loadingMunicipalities = false;
        });
        _showError('Failed to load Metro Manila cities.\n$e');
      }
      return;
    }

    try {
      final data = await PsgcApi.getProvincesByRegion(region.code);
      if (!mounted) return;

      setState(() {
        provinces = data;
        loadingProvinces = false;
      });

      if (preselectProvince != null) {
        final provMatch = provinces.where(
          (p) => p.name.toLowerCase() == preselectProvince.toLowerCase(),
        ).firstOrNull;
        if (provMatch != null) {
          await _onProvinceChanged(provMatch, preselectCity: preselectCity, preselectBrgy: preselectBrgy);
        }
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        loadingProvinces = false;
      });
      _showError('Failed to load provinces.\n$e');
    }
  }

  // ==========================================================
  // PROVINCE CHANGED
  // ==========================================================

  Future<void> _onProvinceChanged(
    PsgcProvince? province, {
    String? preselectCity,
    String? preselectBrgy,
  }) async {
    setState(() {
      selectedProvince = province;
      selectedMunicipality = null;
      selectedBarangay = null;
      municipalities = [];
      barangays = [];
      loadingMunicipalities = province != null;
    });

    if (province == null) return;

    final isNcr = selectedRegion?.code == '130000000' || province.code == '130000000';

    try {
      final data = await PsgcApi.getMunicipalities(province.code, isNcrRegion: isNcr);
      if (!mounted) return;

      setState(() {
        municipalities = data;
        loadingMunicipalities = false;
      });

      if (preselectCity != null) {
        final cityMatch = municipalities.where(
          (c) => c.name.toLowerCase() == preselectCity.toLowerCase(),
        ).firstOrNull;
        if (cityMatch != null) {
          await _onMunicipalityChanged(cityMatch, preselectBrgy: preselectBrgy);
        }
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        loadingMunicipalities = false;
      });
      _showError('Failed to load cities/municipalities.\n$e');
    }
  }

  // ==========================================================
  // MUNICIPALITY CHANGED
  // ==========================================================

  Future<void> _onMunicipalityChanged(
    PsgcCityMunicipality? municipality, {
    String? preselectBrgy,
  }) async {
    setState(() {
      selectedMunicipality = municipality;
      selectedBarangay = null;
      barangays = [];
      loadingBarangays = municipality != null;
    });

    if (municipality == null) return;

    try {
      final data = await PsgcApi.getBarangays(municipality.code);
      if (!mounted) return;

      setState(() {
        barangays = data;
        loadingBarangays = false;
      });

      if (preselectBrgy != null) {
        final brgyMatch = barangays.where(
          (b) => b.name.toLowerCase() == preselectBrgy.toLowerCase(),
        ).firstOrNull;
        if (brgyMatch != null) {
          setState(() {
            selectedBarangay = brgyMatch;
          });
        }
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        loadingBarangays = false;
      });
      _showError('Failed to load barangays.\n$e');
    }
  }

  // ==========================================================
  // SAVE ADDRESS
  // ==========================================================

  Future<void> _saveAddress() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (selectedRegion == null) {
      _showError('Please select a region.');
      return;
    }

    if (selectedProvince == null) {
      _showError('Please select a province.');
      return;
    }

    if (selectedMunicipality == null) {
      _showError('Please select a city/municipality.');
      return;
    }

    if (selectedBarangay == null) {
      _showError('Please select a barangay.');
      return;
    }

    final currentUid = widget.userId > 0
        ? widget.userId
        : (int.tryParse(AuthService.currentUser?['id']?.toString() ?? '') ?? 0);

    if (currentUid <= 0 && !AuthService.isAuthenticated) {
      _showError('Please log in to save your address.');
      return;
    }

    setState(() {
      isSaving = true;
    });

    try {
      final house = houseNumberController.text.trim();
      final street = streetController.text.trim();
      final line1 = [house, street].where((s) => s.isNotEmpty).join(', ');

      final newAddress = {
        'label': selectedLabel,
        'name': nameController.text.trim(),
        'phone': phoneController.text.trim(),
        'region': selectedRegion?.name ?? '',
        'house_number': house,
        'street': line1.isNotEmpty ? line1 : street,
        'province': selectedProvince?.name ?? '',
        'municipality': selectedMunicipality?.name ?? '',
        'barangay': selectedBarangay?.name ?? '',
        'postal_code': postalController.text.trim(),
        'is_default': isDefault,
      };

      if (widget.initialAddress != null && widget.initialAddress!['id'] != null) {
        await ApiService.updateAddress(widget.initialAddress!['id'] as int, newAddress);
      } else {
        await ApiService.addAddress(newAddress);
      }

      if (!mounted) return;
      setState(() {
        isSaving = false;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(widget.initialAddress != null
              ? 'Address updated successfully.'
              : 'Address saved successfully.'),
          backgroundColor: const Color(0xFF2E7D32),
        ),
      );

      widget.onSaved?.call();
      widget.onBack();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        isSaving = false;
      });
      _showError(e is ApiException ? e.message : 'Failed to save address.\n$e');
    }
  }

  void _showError(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: CartzyColors.error,
      ),
    );
  }

  InputDecoration _inputDecoration(String label, {String? hint}) {
    return InputDecoration(
      labelText: label,
      hintText: hint,
      filled: true,
      fillColor: CartzyColors.surface,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: CartzyColors.border),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: CartzyColors.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: CartzyColors.navy, width: 1.5),
      ),
    );
  }

  InputDecoration _dropdownDecoration(String label) {
    return InputDecoration(
      labelText: label,
      filled: true,
      fillColor: CartzyColors.surface,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: CartzyColors.border),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: CartzyColors.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: CartzyColors.navy, width: 1.5),
      ),
    );
  }

  Widget _labelButton(String label, IconData icon) {
    final selected = selectedLabel == label;
    return Expanded(
      child: GestureDetector(
        onTap: isSaving ? null : () => setState(() => selectedLabel = label),
        child: Container(
          height: 44,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: selected ? CartzyColors.navy : CartzyColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: selected ? CartzyColors.navy : CartzyColors.border,
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 16, color: selected ? Colors.white : CartzyColors.navy),
              const SizedBox(width: 4),
              Text(
                label,
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w600,
                  color: selected ? Colors.white : CartzyColors.text,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isEditing = widget.initialAddress != null;

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (didPop) return;
        widget.onBack();
      },
      child: Scaffold(
        backgroundColor: CartzyColors.background,
        appBar: AppBar(
          backgroundColor: CartzyColors.background,
          elevation: 0,
          leading: IconButton(
            onPressed: isSaving ? null : widget.onBack,
            icon: const Icon(Icons.arrow_back),
            color: CartzyColors.text,
          ),
          title: Text(
            isEditing ? 'Edit Delivery Address' : 'Add Delivery Address',
            style: const TextStyle(fontWeight: FontWeight.w700, color: CartzyColors.text),
          ),
        ),
        body: SafeArea(
          child: Form(
            key: _formKey,
            child: ListView(
              padding: const EdgeInsets.all(20),
              children: [
                const Text(
                  'Address Label',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: CartzyColors.text),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    _labelButton('Home', Icons.home_outlined),
                    const SizedBox(width: 8),
                    _labelButton('Work', Icons.work_outline),
                    const SizedBox(width: 8),
                    _labelButton('School', Icons.school_outlined),
                    const SizedBox(width: 8),
                    _labelButton('Other', Icons.location_on_outlined),
                  ],
                ),
                const SizedBox(height: 24),
                const Text(
                  'Recipient Information',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: CartzyColors.text),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: nameController,
                  decoration: _inputDecoration('Full Name', hint: 'e.g. Juan Dela Cruz'),
                  textInputAction: TextInputAction.next,
                  inputFormatters: InputRules.fullNameFormatters,
                  validator: InputRules.fullName,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: phoneController,
                  keyboardType: TextInputType.phone,
                  decoration: _inputDecoration('Contact Number', hint: '09171234567'),
                  textInputAction: TextInputAction.next,
                  inputFormatters: [
                    FilteringTextInputFormatter.digitsOnly,
                    LengthLimitingTextInputFormatter(11),
                  ],
                  validator: (val) {
                    if (val == null || val.trim().isEmpty) return 'Please enter contact number';
                    if (val.trim().length < 10) return 'Please enter a valid 11-digit phone number';
                    return null;
                  },
                ),
                const SizedBox(height: 24),
                const Text(
                  'Address Details (PSGC API)',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: CartzyColors.text),
                ),
                const SizedBox(height: 12),

                // REGION
                DropdownButtonFormField<PsgcRegion>(
                  initialValue: selectedRegion,
                  decoration: _dropdownDecoration('Region'),
                  isExpanded: true,
                  hint: Text(loadingRegions ? 'Loading regions...' : 'Select region'),
                  items: regions.map((region) {
                    return DropdownMenuItem<PsgcRegion>(
                      value: region,
                      child: Text(region.displayName, overflow: TextOverflow.ellipsis),
                    );
                  }).toList(),
                  onChanged: loadingRegions || isSaving ? null : (r) => _onRegionChanged(r),
                ),
                const SizedBox(height: 12),

                // PROVINCE
                DropdownButtonFormField<PsgcProvince>(
                  initialValue: selectedProvince,
                  decoration: _dropdownDecoration('Province'),
                  isExpanded: true,
                  hint: Text(loadingProvinces ? 'Loading provinces...' : 'Select province'),
                  items: provinces.map((province) {
                    return DropdownMenuItem<PsgcProvince>(
                      value: province,
                      child: Text(province.name, overflow: TextOverflow.ellipsis),
                    );
                  }).toList(),
                  onChanged: selectedRegion == null || loadingProvinces || isSaving
                      ? null
                      : (p) => _onProvinceChanged(p),
                ),
                const SizedBox(height: 12),

                // CITY / MUNICIPALITY
                DropdownButtonFormField<PsgcCityMunicipality>(
                  initialValue: selectedMunicipality,
                  decoration: _dropdownDecoration('City / Municipality'),
                  isExpanded: true,
                  hint: Text(loadingMunicipalities ? 'Loading cities...' : 'Select city/municipality'),
                  items: municipalities.map((municipality) {
                    return DropdownMenuItem<PsgcCityMunicipality>(
                      value: municipality,
                      child: Text(municipality.name, overflow: TextOverflow.ellipsis),
                    );
                  }).toList(),
                  onChanged: selectedProvince == null || loadingMunicipalities || isSaving
                      ? null
                      : (m) => _onMunicipalityChanged(m),
                ),
                const SizedBox(height: 12),

                // BARANGAY
                DropdownButtonFormField<PsgcBarangay>(
                  initialValue: selectedBarangay,
                  decoration: _dropdownDecoration('Barangay'),
                  isExpanded: true,
                  hint: Text(loadingBarangays ? 'Loading barangays...' : 'Select barangay'),
                  items: barangays.map((barangay) {
                    return DropdownMenuItem<PsgcBarangay>(
                      value: barangay,
                      child: Text(barangay.name, overflow: TextOverflow.ellipsis),
                    );
                  }).toList(),
                  onChanged: selectedMunicipality == null || loadingBarangays || isSaving
                      ? null
                      : (b) => setState(() => selectedBarangay = b),
                ),
                const SizedBox(height: 12),

                // POSTAL CODE
                TextFormField(
                  controller: postalController,
                  keyboardType: TextInputType.number,
                  decoration: _inputDecoration('Postal Code', hint: 'e.g. 1600'),
                  textInputAction: TextInputAction.next,
                  inputFormatters: [
                    FilteringTextInputFormatter.digitsOnly,
                    LengthLimitingTextInputFormatter(4),
                  ],
                  validator: (val) {
                    if (val == null || val.trim().isEmpty) return 'Please enter postal code';
                    if (val.trim().length != 4) return 'Postal code must be 4 digits';
                    return null;
                  },
                ),
                const SizedBox(height: 12),

                // HOUSE / UNIT NUMBER
                TextFormField(
                  controller: houseNumberController,
                  decoration: _inputDecoration('House / Unit / Building Number (Optional)', hint: 'e.g. Unit 802, Blk 12 Lot 4'),
                  textInputAction: TextInputAction.next,
                ),
                const SizedBox(height: 12),

                // STREET
                TextFormField(
                  controller: streetController,
                  decoration: _inputDecoration('Street Name', hint: 'e.g. Dahlia St., San Antonio'),
                  textInputAction: TextInputAction.done,
                  validator: (value) {
                    if (value == null || value.trim().isEmpty) {
                      return 'Please enter your street name';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),

                // SET AS DEFAULT CHECKBOX
                CheckboxListTile(
                  value: isDefault,
                  onChanged: isSaving ? null : (val) => setState(() => isDefault = val ?? false),
                  title: const Text('Use as my default delivery address', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                  subtitle: const Text('Automatically selected when placing orders during checkout', style: TextStyle(fontSize: 12, color: CartzyColors.gray)),
                  activeColor: CartzyColors.navy,
                  contentPadding: EdgeInsets.zero,
                  controlAffinity: ListTileControlAffinity.leading,
                ),
                const SizedBox(height: 24),

                // SAVE BUTTON
                SizedBox(
                  height: 50,
                  child: ElevatedButton(
                    onPressed: isSaving ? null : _saveAddress,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: CartzyColors.navy,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: isSaving
                        ? const SizedBox(
                            width: 22,
                            height: 22,
                            child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                          )
                        : Text(
                            isEditing ? 'Update Delivery Address' : 'Save Delivery Address',
                            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                          ),
                  ),
                ),
                const SizedBox(height: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

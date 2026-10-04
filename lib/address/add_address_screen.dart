import 'package:flutter/material.dart';

import 'package:cartzy/services/mock_data.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/psgc_api.dart';

class AddAddressScreen extends StatefulWidget {
  final VoidCallback onBack;
  final VoidCallback? onSaved;
  final int userId;

  const AddAddressScreen({
    super.key,
    required this.onBack,
    required this.userId,
    this.onSaved,
  });

  @override
  State<AddAddressScreen> createState() =>
      _AddAddressScreenState();
}

class _AddAddressScreenState
    extends State<AddAddressScreen> {
  final _formKey = GlobalKey<FormState>();

  final TextEditingController nameController =
      TextEditingController();

  final TextEditingController phoneController =
      TextEditingController();

  final TextEditingController houseNumberController =
      TextEditingController();

  final TextEditingController streetController =
      TextEditingController();

  final TextEditingController postalController =
      TextEditingController();

  String selectedLabel = 'Home';

  bool isDefault = false;
  bool isSaving = false;

  // ==========================================================
  // PSGC DATA
  // ==========================================================

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
    } catch (e) {
      if (!mounted) return;

      setState(() {
        loadingRegions = false;
      });

      _showError(
        'Failed to load regions.\n$e',
      );
    }
  }

  // ==========================================================
  // REGION CHANGED
  // ==========================================================

  Future<void> _onRegionChanged(
    PsgcRegion? region,
  ) async {
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

    try {
      final data =
          await PsgcApi.getProvincesByRegion(
        region.code,
      );

      if (!mounted) return;

      setState(() {
        provinces = data;
        loadingProvinces = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        loadingProvinces = false;
      });

      _showError(
        'Failed to load provinces.\n$e',
      );
    }
  }

  // ==========================================================
  // PROVINCE CHANGED
  // ==========================================================

  Future<void> _onProvinceChanged(
    PsgcProvince? province,
  ) async {
    setState(() {
      selectedProvince = province;

      selectedMunicipality = null;
      selectedBarangay = null;

      municipalities = [];
      barangays = [];

      loadingMunicipalities =
          province != null;
    });

    if (province == null) return;

    try {
      final data =
          await PsgcApi.getMunicipalities(
        province.code,
      );

      if (!mounted) return;

      setState(() {
        municipalities = data;
        loadingMunicipalities = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        loadingMunicipalities = false;
      });

      _showError(
        'Failed to load cities/municipalities.\n$e',
      );
    }
  }

  // ==========================================================
  // MUNICIPALITY CHANGED
  // ==========================================================

  Future<void> _onMunicipalityChanged(
    PsgcCityMunicipality? municipality,
  ) async {
    setState(() {
      selectedMunicipality = municipality;

      selectedBarangay = null;

      barangays = [];

      loadingBarangays =
          municipality != null;
    });

    if (municipality == null) return;

    try {
      final data =
          await PsgcApi.getBarangays(
        municipality.code,
      );

      if (!mounted) return;

      setState(() {
        barangays = data;
        loadingBarangays = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        loadingBarangays = false;
      });

      _showError(
        'Failed to load barangays.\n$e',
      );
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
      _showError(
        'Please select a region.',
      );
      return;
    }

    if (selectedProvince == null) {
      _showError(
        'Please select a province.',
      );
      return;
    }

    if (selectedMunicipality == null) {
      _showError(
        'Please select a city/municipality.',
      );
      return;
    }

    if (selectedBarangay == null) {
      _showError(
        'Please select a barangay.',
      );
      return;
    }

    if (widget.userId <= 0) {
      _showError(
        'Invalid user account. Please log in again.',
      );
      return;
    }

    setState(() {
      isSaving = true;
    });

    try {
      await Future.delayed(const Duration(milliseconds: 300));

      final newAddress = {
        'id': DateTime.now().millisecondsSinceEpoch,
        'user_id': widget.userId,
        'label': selectedLabel,
        'name': nameController.text.trim(),
        'phone': phoneController.text.trim(),
        'region': selectedRegion?.name ?? 'National Capital Region (NCR)',
        'house_number': houseNumberController.text.trim(),
        'street': streetController.text.trim(),
        'province': selectedProvince?.name ?? 'Metro Manila',
        'municipality': selectedMunicipality?.name ?? 'Pasig City',
        'barangay': selectedBarangay?.name ?? 'San Antonio',
        'postal_code': postalController.text.trim(),
        'is_default': isDefault,
      };

      if (isDefault) {
        for (final a in MockData.addresses) {
          a['is_default'] = false;
        }
      }
      MockData.addresses.add(newAddress);

      if (!mounted) return;

      setState(() {
        isSaving = false;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Address saved successfully.'),
        ),
      );

      widget.onSaved?.call();

      widget.onBack();
    } catch (e) {
      if (!mounted) return;

      setState(() {
        isSaving = false;
      });

      _showError(
        'Failed to save address.\n$e',
      );
    }
  }

  // ==========================================================
  // ERROR
  // ==========================================================

  void _showError(
    String message,
  ) {
    if (!mounted) return;

    ScaffoldMessenger.of(context)
        .showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor:
            CartzyColors.error,
      ),
    );
  }

  // ==========================================================
  // INPUT DECORATION
  // ==========================================================

  InputDecoration _inputDecoration(
    String label, {
    String? hint,
  }) {
    return InputDecoration(
      labelText: label,
      hintText: hint,
      filled: true,
      fillColor:
          CartzyColors.surface,
      border: OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(12),
        borderSide:
            const BorderSide(
          color:
              CartzyColors.border,
        ),
      ),
      enabledBorder:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(12),
        borderSide:
            const BorderSide(
          color:
              CartzyColors.border,
        ),
      ),
      focusedBorder:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(12),
        borderSide:
            const BorderSide(
          color:
              CartzyColors.navy,
          width: 1.5,
        ),
      ),
    );
  }

  InputDecoration _dropdownDecoration(
    String label,
  ) {
    return InputDecoration(
      labelText: label,
      filled: true,
      fillColor:
          CartzyColors.surface,
      border: OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(12),
        borderSide:
            const BorderSide(
          color:
              CartzyColors.border,
        ),
      ),
      enabledBorder:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(12),
        borderSide:
            const BorderSide(
          color:
              CartzyColors.border,
        ),
      ),
      focusedBorder:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(12),
        borderSide:
            const BorderSide(
          color:
              CartzyColors.navy,
          width: 1.5,
        ),
      ),
    );
  }

  // ==========================================================
  // LABEL BUTTON
  // ==========================================================

  Widget _labelButton(
    String label,
  ) {
    final selected =
        selectedLabel == label;

    return Expanded(
      child: GestureDetector(
        onTap: isSaving
            ? null
            : () {
                setState(() {
                  selectedLabel =
                      label;
                });
              },
        child: Container(
          height: 44,
          alignment:
              Alignment.center,
          decoration:
              BoxDecoration(
            color: selected
                ? CartzyColors.navy
                : CartzyColors.surface,
            borderRadius:
                BorderRadius.circular(
              12,
            ),
            border:
                Border.all(
              color: selected
                  ? CartzyColors.navy
                  : CartzyColors.border,
            ),
          ),
          child: Text(
            label,
            style: TextStyle(
              fontWeight:
                  FontWeight.w600,
              color: selected
                  ? Colors.white
                  : CartzyColors.text,
            ),
          ),
        ),
      ),
    );
  }

  // ==========================================================
  // BUILD
  // ==========================================================

  @override
  Widget build(
    BuildContext context,
  ) {
    return Scaffold(
      backgroundColor:
          CartzyColors.background,

      appBar: AppBar(
        backgroundColor:
            CartzyColors.background,
        elevation: 0,
        leading: IconButton(
          onPressed:
              isSaving
                  ? null
                  : widget.onBack,
          icon:
              const Icon(
            Icons.arrow_back,
          ),
          color:
              CartzyColors.text,
        ),
        title:
            const Text(
          'Add Address',
          style: TextStyle(
            fontWeight:
                FontWeight.w700,
            color:
                CartzyColors.text,
          ),
        ),
      ),

      body: SafeArea(
        child: Form(
          key: _formKey,
          child: ListView(
            padding:
                const EdgeInsets.all(
              20,
            ),
            children: [
              const Text(
                'Address Label',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight:
                      FontWeight.w700,
                  color:
                      CartzyColors.text,
                ),
              ),

              const SizedBox(
                height: 10,
              ),

              Row(
                children: [
                  _labelButton(
                    'Home',
                  ),
                  const SizedBox(
                    width: 10,
                  ),
                  _labelButton(
                    'Work',
                  ),
                  const SizedBox(
                    width: 10,
                  ),
                  _labelButton(
                    'Other',
                  ),
                ],
              ),

              const SizedBox(
                height: 24,
              ),

              const Text(
                'Contact Information',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight:
                      FontWeight.w700,
                  color:
                      CartzyColors.text,
                ),
              ),

              const SizedBox(
                height: 12,
              ),

              TextFormField(
                controller:
                    nameController,
                decoration:
                    _inputDecoration(
                  'Full Name',
                ),
                textInputAction:
                    TextInputAction.next,
                validator:
                    (value) {
                  if (value == null ||
                      value
                          .trim()
                          .isEmpty) {
                    return 'Please enter your name';
                  }

                  return null;
                },
              ),

              const SizedBox(
                height: 12,
              ),

              TextFormField(
                controller:
                    phoneController,
                keyboardType:
                    TextInputType.phone,
                decoration:
                    _inputDecoration(
                  'Phone Number',
                ),
                textInputAction:
                    TextInputAction.next,
                validator:
                    (value) {
                  if (value == null ||
                      value
                          .trim()
                          .isEmpty) {
                    return 'Please enter your phone number';
                  }

                  return null;
                },
              ),

              const SizedBox(
                height: 24,
              ),

              const Text(
                'Address',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight:
                      FontWeight.w700,
                  color:
                      CartzyColors.text,
                ),
              ),

              const SizedBox(
                height: 12,
              ),

              TextFormField(
                controller:
                    houseNumberController,
                decoration:
                    _inputDecoration(
                  'House / Unit Number',
                  hint: 'e.g. 123',
                ),
                textInputAction:
                    TextInputAction.next,
              ),

              const SizedBox(
                height: 12,
              ),

              TextFormField(
                controller:
                    streetController,
                decoration:
                    _inputDecoration(
                  'Street',
                  hint: 'e.g. Main Street',
                ),
                textInputAction:
                    TextInputAction.next,
                validator:
                    (value) {
                  if (value == null ||
                      value
                          .trim()
                          .isEmpty) {
                    return 'Please enter your street';
                  }

                  return null;
                },
              ),

              const SizedBox(
                height: 12,
              ),

              // REGION
              DropdownButtonFormField<
                  PsgcRegion>(
                initialValue:
                    selectedRegion,
                decoration:
                    _dropdownDecoration(
                  'Region',
                ),
                isExpanded: true,
                hint: Text(
                  loadingRegions
                      ? 'Loading regions...'
                      : 'Select region',
                ),
                items:
                    regions.map(
                  (
                    region,
                  ) {
                    return DropdownMenuItem<
                        PsgcRegion>(
                      value: region,
                      child: Text(
                        region.name,
                        overflow:
                            TextOverflow.ellipsis,
                      ),
                    );
                  },
                ).toList(),
                onChanged:
                    loadingRegions ||
                            isSaving
                        ? null
                        : _onRegionChanged,
              ),

              const SizedBox(
                height: 12,
              ),

              // PROVINCE
              DropdownButtonFormField<
                  PsgcProvince>(
                initialValue:
                    selectedProvince,
                decoration:
                    _dropdownDecoration(
                  'Province',
                ),
                isExpanded: true,
                hint: Text(
                  loadingProvinces
                      ? 'Loading provinces...'
                      : 'Select province',
                ),
                items:
                    provinces.map(
                  (
                    province,
                  ) {
                    return DropdownMenuItem<
                        PsgcProvince>(
                      value: province,
                      child: Text(
                        province.name,
                        overflow:
                            TextOverflow.ellipsis,
                      ),
                    );
                  },
                ).toList(),
                onChanged:
                    selectedRegion ==
                                null ||
                            loadingProvinces ||
                            isSaving
                        ? null
                        : _onProvinceChanged,
              ),

              const SizedBox(
                height: 12,
              ),

              // CITY / MUNICIPALITY
              DropdownButtonFormField<
                  PsgcCityMunicipality>(
                initialValue:
                    selectedMunicipality,
                decoration:
                    _dropdownDecoration(
                  'City / Municipality',
                ),
                isExpanded: true,
                hint: Text(
                  loadingMunicipalities
                      ? 'Loading cities...'
                      : 'Select city/municipality',
                ),
                items:
                    municipalities.map(
                  (
                    municipality,
                  ) {
                    return DropdownMenuItem<
                        PsgcCityMunicipality>(
                      value:
                          municipality,
                      child: Text(
                        municipality.name,
                        overflow:
                            TextOverflow.ellipsis,
                      ),
                    );
                  },
                ).toList(),
                onChanged:
                    selectedProvince ==
                                null ||
                            loadingMunicipalities ||
                            isSaving
                        ? null
                        : _onMunicipalityChanged,
              ),

              const SizedBox(
                height: 12,
              ),

              // BARANGAY
              DropdownButtonFormField<
                  PsgcBarangay>(
                initialValue:
                    selectedBarangay,
                decoration:
                    _dropdownDecoration(
                  'Barangay',
                ),
                isExpanded: true,
                hint: Text(
                  loadingBarangays
                      ? 'Loading barangays...'
                      : 'Select barangay',
                ),
                items:
                    barangays.map(
                  (
                    barangay,
                  ) {
                    return DropdownMenuItem<
                        PsgcBarangay>(
                      value: barangay,
                      child: Text(
                        barangay.name,
                        overflow:
                            TextOverflow.ellipsis,
                      ),
                    );
                  },
                ).toList(),
                onChanged:
                    selectedMunicipality ==
                                null ||
                            loadingBarangays ||
                            isSaving
                        ? null
                        : (
                            value,
                          ) {
                            setState(
                              () {
                                selectedBarangay =
                                    value;
                              },
                            );
                          },
              ),

              const SizedBox(
                height: 12,
              ),

              // POSTAL CODE
              TextFormField(
                controller:
                    postalController,
                keyboardType:
                    TextInputType.number,
                decoration:
                    _inputDecoration(
                  'Postal Code',
                ),
                textInputAction:
                    TextInputAction.done,
                validator:
                    (value) {
                  if (value == null ||
                      value
                          .trim()
                          .isEmpty) {
                    return 'Please enter your postal code';
                  }

                  return null;
                },
              ),

              const SizedBox(
                height: 20,
              ),

              // DEFAULT
              Container(
                decoration:
                    BoxDecoration(
                  color:
                      CartzyColors.surface,
                  borderRadius:
                      BorderRadius.circular(
                    14,
                  ),
                  border:
                      Border.all(
                    color:
                        CartzyColors.border,
                  ),
                ),
                child:
                    SwitchListTile(
                  value: isDefault,
                  onChanged:
                      isSaving
                          ? null
                          : (
                              value,
                            ) {
                              setState(
                                () {
                                  isDefault =
                                      value;
                                },
                              );
                            },
                  title:
                      const Text(
                    'Set as default address',
                    style:
                        TextStyle(
                      fontWeight:
                          FontWeight.w600,
                      color:
                          CartzyColors.text,
                    ),
                  ),
                  subtitle:
                      const Text(
                    'Use this address automatically during checkout.',
                  ),
                  activeThumbColor:
                      CartzyColors.coral,
                ),
              ),

              const SizedBox(
                height: 28,
              ),

              // SAVE BUTTON
              SizedBox(
                height: 52,
                width: double.infinity,
                child:
                    ElevatedButton(
                  onPressed:
                      isSaving
                          ? null
                          : _saveAddress,
                  style:
                      ElevatedButton.styleFrom(
                    backgroundColor:
                        CartzyColors.coral,
                    foregroundColor:
                        Colors.white,
                    disabledBackgroundColor:
                        CartzyColors.border,
                    shape:
                        RoundedRectangleBorder(
                      borderRadius:
                          BorderRadius.circular(
                        14,
                      ),
                    ),
                  ),
                  child: isSaving
                      ? const SizedBox(
                          height: 22,
                          width: 22,
                          child:
                              CircularProgressIndicator(
                            strokeWidth:
                                2.5,
                            valueColor:
                                AlwaysStoppedAnimation<
                                    Color>(
                              Colors.white,
                            ),
                          ),
                        )
                      : const Text(
                          'Save Address',
                          style:
                              TextStyle(
                            fontSize:
                                16,
                            fontWeight:
                                FontWeight.w700,
                          ),
                        ),
                ),
              ),

              const SizedBox(
                height: 30,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
import 'package:flutter/material.dart';

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/services/psgc_api.dart';
import 'package:cartzy/widgets/cartzy_flash_notif.dart';

class BuyerAccountScreen extends StatefulWidget {
  final VoidCallback onBack;
  final int userId;

  // Sends the updated user back to Account Menu / main.dart
  final ValueChanged<Map<String, dynamic>>? onProfileUpdated;

  const BuyerAccountScreen({
    super.key,
    required this.onBack,
    required this.userId,
    this.onProfileUpdated,
  });

  @override
  State<BuyerAccountScreen> createState() =>
      _BuyerAccountScreenState();
}

class _BuyerAccountScreenState
    extends State<BuyerAccountScreen> {
  // ============================================================
  // CONTROLLERS
  // ============================================================

  final TextEditingController _nameController =
      TextEditingController();

  final TextEditingController _middleInitialController =
      TextEditingController();

  final TextEditingController _phoneController =
      TextEditingController();

  final TextEditingController _streetAddressController =
      TextEditingController();

  final TextEditingController _addressController =
      TextEditingController();

  final TextEditingController _regionController =
      TextEditingController();

  final TextEditingController _provinceController =
      TextEditingController();

  final TextEditingController _cityController =
      TextEditingController();

  final TextEditingController _barangayController =
      TextEditingController();

  final TextEditingController _postalCodeController =
      TextEditingController();

  final TextEditingController _idNumberController =
      TextEditingController();

  // ============================================================
  // PROFILE DATA
  // ============================================================

  String _email = '';

  String _selectedGender = 'Other';

  DateTime? _birthday;

  int? _age;

  String _idType = '';

  String _idStatus = '';

  String _idPhoto = '';

  String _idOriginalFilename = '';

  bool _isLoading = true;

  bool _isSaving = false;

  // ============================================================
  // PSGC ADDRESS DATA
  // ============================================================

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

  // ============================================================
  // INIT
  // ============================================================

  @override
  void initState() {
    super.initState();

    _loadProfile();
    _loadRegions();
  }

  // ============================================================
  // DISPOSE
  // ============================================================

  @override
  void dispose() {
    _nameController.dispose();
    _middleInitialController.dispose();
    _phoneController.dispose();
    _streetAddressController.dispose();
    _addressController.dispose();
    _regionController.dispose();
    _provinceController.dispose();
    _cityController.dispose();
    _barangayController.dispose();
    _postalCodeController.dispose();
    _idNumberController.dispose();

    super.dispose();
  }

  // ============================================================
  // HELPER
  // ============================================================

  String _value(dynamic value) {
    if (value == null) {
      return '';
    }

    return value.toString();
  }

  String _getFileName(String path) {
    if (path.trim().isEmpty) {
      return '';
    }

    final cleanPath = path.replaceAll('\\', '/');

    return cleanPath.split('/').last;
  }

  // ============================================================
  // LOAD PROFILE
  // ============================================================

  Future<void> _loadProfile() async {
    try {
      await Future.delayed(const Duration(milliseconds: 200));

      final user = {
        'name': 'Tiffany Leonardo',
        'middle_initial': 'M',
        'email': 'buyer@cartzy.com',
        'phone': '+63 917 123 4567',
        'sex': 'Female',
        'birthday': '2000-05-15',
        'age': 26,
        'street_address': '124 Rizal Street',
        'address': '124 Rizal Street, Brgy. San Antonio, Pasig City',
        'region': 'National Capital Region (NCR)',
        'province': 'Metro Manila',
        'city': 'Pasig City',
        'barangay': 'San Antonio',
        'postal_code': '1600',
        'id_type': 'Passport',
        'id_number': 'P1234567A',
        'id_photo': '',
        'id_original_filename': 'passport_id.jpg',
        'id_status': 'verified',
      };

      if (!mounted) {
        return;
      }

      setState(() {
        _nameController.text =
            _value(user['name']);

        _middleInitialController.text =
            _value(user['middle_initial']);

        _email =
            _value(user['email']);

        _phoneController.text =
            _value(user['phone']);

        // ------------------------------------------
        // GENDER
        // ------------------------------------------

        final gender =
            _value(user['sex']).toLowerCase();

        if (gender == 'male') {
          _selectedGender = 'Male';
        } else if (gender == 'female') {
          _selectedGender = 'Female';
        } else {
          _selectedGender = 'Other';
        }

        // ------------------------------------------
        // BIRTHDAY
        // ------------------------------------------

        final birthday =
            _value(user['birthday']);

        if (birthday.isNotEmpty) {
          _birthday =
              DateTime.tryParse(birthday);
        }

        // ------------------------------------------
        // AGE
        // ------------------------------------------

        if (user['age'] != null) {
          _age = int.tryParse(
            user['age'].toString(),
          );
        }

        // ------------------------------------------
        // ADDRESS
        // ------------------------------------------

        _streetAddressController.text =
            _value(user['street_address']);

        _addressController.text =
            _value(user['address']);

        _regionController.text =
            _value(user['region']);

        _provinceController.text =
            _value(user['province']);

        _cityController.text =
            _value(user['city']);

        _barangayController.text =
            _value(user['barangay']);

        _postalCodeController.text =
            _value(user['postal_code']);

        // ------------------------------------------
        // ID
        // ------------------------------------------

        _idType =
            _value(user['id_type']);

        _idNumberController.text =
            _value(user['id_number']);

        _idPhoto =
            _value(user['id_photo']);

        _idOriginalFilename =
            _value(
          user['id_original_filename'],
        );

        _idStatus =
            _value(user['id_status']);

        _isLoading = false;
      });

      // Once profile data has loaded,
      // match the existing address selections.
      if (_regions.isNotEmpty) {
        await _matchExistingRegion();
      }
    } catch (e) {
      if (!mounted) {
        return;
      }

      setState(() {
        _isLoading = false;
      });

      _showMessage(
        'Failed to load profile: $e',
        isError: true,
      );
    }
  }

  // ============================================================
  // LOAD REGIONS
  // ============================================================

  Future<void> _loadRegions() async {
    setState(() {
      _loadingRegions = true;
    });

    try {
      final regions =
          await PsgcApi.getRegions();

      if (!mounted) {
        return;
      }

      setState(() {
        _regions = regions;
      });

      // If profile already finished loading,
      // match the saved region.
      if (!_isLoading) {
        await _matchExistingRegion();
      }
    } catch (e) {
      if (!mounted) {
        return;
      }

      _showMessage(
        'Failed to load regions: $e',
        isError: true,
      );
    } finally {
      if (mounted) {
        setState(() {
          _loadingRegions = false;
        });
      }
    }
  }

  // ============================================================
  // MATCH EXISTING REGION
  // ============================================================

  Future<void> _matchExistingRegion() async {
    final existingRegion =
        _regionController.text.trim();

    if (existingRegion.isEmpty) {
      return;
    }

    PsgcRegion? match;

    // Exact match first
    for (final region in _regions) {
      if (region.name.toLowerCase() ==
          existingRegion.toLowerCase()) {
        match = region;
        break;
      }
    }

    // Partial match
    if (match == null) {
      for (final region in _regions) {
        if (region.name
                .toLowerCase()
                .contains(
                  existingRegion.toLowerCase(),
                ) ||
            existingRegion
                .toLowerCase()
                .contains(
                  region.name.toLowerCase(),
                )) {
          match = region;
          break;
        }
      }
    }

    if (match == null) {
      return;
    }

    if (!mounted) {
      return;
    }

    setState(() {
      _selectedRegion = match;
    });

    await _loadProvinces(
      match.code,
      selectExisting: true,
    );
  }

  // ============================================================
  // LOAD PROVINCES
  // ============================================================

  Future<void> _loadProvinces(
    String regionCode, {
    bool selectExisting = false,
  }) async {
    if (!mounted) {
      return;
    }

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
          await PsgcApi.getProvincesByRegion(
        regionCode,
      );

      if (!mounted) {
        return;
      }

      setState(() {
        _provinces = provinces;
      });

      if (selectExisting) {
        await _matchExistingProvince();
      }
    } catch (e) {
      if (!mounted) {
        return;
      }

      _showMessage(
        'Failed to load provinces: $e',
        isError: true,
      );
    } finally {
      if (mounted) {
        setState(() {
          _loadingProvinces = false;
        });
      }
    }
  }

  // ============================================================
  // MATCH EXISTING PROVINCE
  // ============================================================

  Future<void> _matchExistingProvince() async {
    final existingProvince =
        _provinceController.text.trim();

    if (existingProvince.isEmpty) {
      return;
    }

    PsgcProvince? match;

    for (final province in _provinces) {
      if (province.name.toLowerCase() ==
          existingProvince.toLowerCase()) {
        match = province;
        break;
      }
    }

    if (match == null) {
      for (final province in _provinces) {
        if (province.name
                .toLowerCase()
                .contains(
                  existingProvince.toLowerCase(),
                ) ||
            existingProvince
                .toLowerCase()
                .contains(
                  province.name.toLowerCase(),
                )) {
          match = province;
          break;
        }
      }
    }

    if (match == null) {
      return;
    }

    if (!mounted) {
      return;
    }

    setState(() {
      _selectedProvince = match;
    });

    await _loadCities(
      match.code,
      selectExisting: true,
    );
  }

  // ============================================================
  // LOAD CITIES
  // ============================================================

  Future<void> _loadCities(
    String provinceCode, {
    bool selectExisting = false,
  }) async {
    if (!mounted) {
      return;
    }

    setState(() {
      _loadingCities = true;

      _cities = [];

      _barangays = [];

      _selectedCity = null;

      _selectedBarangay = null;
    });

    try {
      final cities =
          await PsgcApi.getMunicipalities(
        provinceCode,
      );

      if (!mounted) {
        return;
      }

      setState(() {
        _cities = cities;
      });

      if (selectExisting) {
        await _matchExistingCity();
      }
    } catch (e) {
      if (!mounted) {
        return;
      }

      _showMessage(
        'Failed to load cities: $e',
        isError: true,
      );
    } finally {
      if (mounted) {
        setState(() {
          _loadingCities = false;
        });
      }
    }
  }

  // ============================================================
  // MATCH EXISTING CITY
  // ============================================================

  Future<void> _matchExistingCity() async {
    final existingCity =
        _cityController.text.trim();

    if (existingCity.isEmpty) {
      return;
    }

    PsgcCityMunicipality? match;

    for (final city in _cities) {
      if (city.name.toLowerCase() ==
          existingCity.toLowerCase()) {
        match = city;
        break;
      }
    }

    if (match == null) {
      for (final city in _cities) {
        if (city.name
                .toLowerCase()
                .contains(
                  existingCity.toLowerCase(),
                ) ||
            existingCity
                .toLowerCase()
                .contains(
                  city.name.toLowerCase(),
                )) {
          match = city;
          break;
        }
      }
    }

    if (match == null) {
      return;
    }

    if (!mounted) {
      return;
    }

    setState(() {
      _selectedCity = match;
    });

    await _loadBarangays(
      match.code,
      selectExisting: true,
    );
  }

  // ============================================================
  // LOAD BARANGAYS
  // ============================================================

  Future<void> _loadBarangays(
    String cityCode, {
    bool selectExisting = false,
  }) async {
    if (!mounted) {
      return;
    }

    setState(() {
      _loadingBarangays = true;

      _barangays = [];

      _selectedBarangay = null;
    });

    try {
      final barangays =
          await PsgcApi.getBarangays(
        cityCode,
      );

      if (!mounted) {
        return;
      }

      setState(() {
        _barangays = barangays;
      });

      if (selectExisting) {
        _matchExistingBarangay();
      }
    } catch (e) {
      if (!mounted) {
        return;
      }

      _showMessage(
        'Failed to load barangays: $e',
        isError: true,
      );
    } finally {
      if (mounted) {
        setState(() {
          _loadingBarangays = false;
        });
      }
    }
  }

  // ============================================================
  // MATCH EXISTING BARANGAY
  // ============================================================

  void _matchExistingBarangay() {
    final existingBarangay =
        _barangayController.text.trim();

    if (existingBarangay.isEmpty) {
      return;
    }

    PsgcBarangay? match;

    for (final barangay in _barangays) {
      if (barangay.name.toLowerCase() ==
          existingBarangay.toLowerCase()) {
        match = barangay;
        break;
      }
    }

    if (match == null) {
      for (final barangay in _barangays) {
        if (barangay.name
                .toLowerCase()
                .contains(
                  existingBarangay.toLowerCase(),
                ) ||
            existingBarangay
                .toLowerCase()
                .contains(
                  barangay.name.toLowerCase(),
                )) {
          match = barangay;
          break;
        }
      }
    }

    if (match == null) {
      return;
    }

    if (!mounted) {
      return;
    }

    setState(() {
      _selectedBarangay = match;
    });
  }

  // ============================================================
  // REGION PICKER
  // ============================================================

  Widget _regionPicker() {
    return DropdownButtonFormField<PsgcRegion>(
      initialValue: _selectedRegion,

      isExpanded: true,

      decoration: _pickerDecoration(
        Icons.map_outlined,
        'Select Region',
      ),

      hint: const Text(
        'Select Region',
      ),

      items: _regions.map(
        (region) {
          return DropdownMenuItem<PsgcRegion>(
            value: region,
            child: Text(
              region.name,
              overflow: TextOverflow.ellipsis,
            ),
          );
        },
      ).toList(),

      onChanged:
          _loadingRegions
              ? null
              : (region) async {
                  if (region == null) {
                    return;
                  }

                  setState(() {
                    _selectedRegion =
                        region;

                    _regionController.text =
                        region.name;

                    _provinceController
                        .clear();

                    _cityController.clear();

                    _barangayController
                        .clear();

                    _selectedProvince =
                        null;

                    _selectedCity = null;

                    _selectedBarangay =
                        null;
                  });

                  await _loadProvinces(
                    region.code,
                  );
                },
    );
  }

  // ============================================================
  // PROVINCE PICKER
  // ============================================================

  Widget _provincePicker() {
    return DropdownButtonFormField<PsgcProvince>(
      initialValue: _selectedProvince,

      isExpanded: true,

      decoration: _pickerDecoration(
        Icons.location_city_outlined,
        'Select Province',
      ),

      hint: const Text(
        'Select Province',
      ),

      items: _provinces.map(
        (province) {
          return DropdownMenuItem<PsgcProvince>(
            value: province,
            child: Text(
              province.name,
              overflow: TextOverflow.ellipsis,
            ),
          );
        },
      ).toList(),

      onChanged:
          _selectedRegion == null ||
                  _loadingProvinces
              ? null
              : (province) async {
                  if (province == null) {
                    return;
                  }

                  setState(() {
                    _selectedProvince =
                        province;

                    _provinceController
                            .text =
                        province.name;

                    _cityController.clear();

                    _barangayController
                        .clear();

                    _selectedCity = null;

                    _selectedBarangay =
                        null;
                  });

                  await _loadCities(
                    province.code,
                  );
                },
    );
  }

  // ============================================================
  // CITY / MUNICIPALITY PICKER
  // ============================================================

  Widget _cityPicker() {
    return DropdownButtonFormField<
        PsgcCityMunicipality>(
      initialValue: _selectedCity,

      isExpanded: true,

      decoration: _pickerDecoration(
        Icons.location_city,
        'Select City / Municipality',
      ),

      hint: const Text(
        'Select City / Municipality',
      ),

      items: _cities.map(
        (city) {
          return DropdownMenuItem<
              PsgcCityMunicipality>(
            value: city,
            child: Text(
              city.name,
              overflow:
                  TextOverflow.ellipsis,
            ),
          );
        },
      ).toList(),

      onChanged:
          _selectedProvince == null ||
                  _loadingCities
              ? null
              : (city) async {
                  if (city == null) {
                    return;
                  }

                  setState(() {
                    _selectedCity =
                        city;

                    _cityController.text =
                        city.name;

                    _barangayController
                        .clear();

                    _selectedBarangay =
                        null;
                  });

                  await _loadBarangays(
                    city.code,
                  );
                },
    );
  }

  // ============================================================
  // BARANGAY PICKER
  // ============================================================

  Widget _barangayPicker() {
    return DropdownButtonFormField<
        PsgcBarangay>(
      initialValue: _selectedBarangay,

      isExpanded: true,

      decoration: _pickerDecoration(
        Icons.home_work_outlined,
        'Select Barangay',
      ),

      hint: const Text(
        'Select Barangay',
      ),

      items: _barangays.map(
        (barangay) {
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
          _selectedCity == null ||
                  _loadingBarangays
              ? null
              : (barangay) {
                  if (barangay == null) {
                    return;
                  }

                  setState(() {
                    _selectedBarangay =
                        barangay;

                    _barangayController
                            .text =
                        barangay.name;
                  });
                },
    );
  }

  // ============================================================
  // SAVE PROFILE
  // ============================================================

  Future<void> _saveProfile() async {
    final name =
        _nameController.text.trim();

    if (name.isEmpty) {
      _showMessage(
        'Name is required.',
        isError: true,
      );

      return;
    }

    setState(() {
      _isSaving = true;
    });

    try {
      String birthday = '';

      if (_birthday != null) {
        birthday =
            '${_birthday!.year.toString().padLeft(4, '0')}-'
            '${_birthday!.month.toString().padLeft(2, '0')}-'
            '${_birthday!.day.toString().padLeft(2, '0')}';
      }

      await Future.delayed(const Duration(milliseconds: 300));

      final user = <String, dynamic>{
        'id': widget.userId,
        'name': name,
        'middle_initial': _middleInitialController.text.trim(),
        'sex': _selectedGender,
        'birthday': birthday,
        'age': _age,
        'phone': _phoneController.text.trim(),
        'street_address': _streetAddressController.text.trim(),
        'address': _addressController.text.trim(),
        'region': _regionController.text.trim(),
        'province': _provinceController.text.trim(),
        'city': _cityController.text.trim(),
        'barangay': _barangayController.text.trim(),
        'postal_code': _postalCodeController.text.trim(),
        'email': _email,
        'id_type': _idType,
        'id_number': _idNumberController.text.trim(),
        'id_photo': _idPhoto,
        'id_status': _idStatus,
      };

      widget.onProfileUpdated?.call(user);

      if (!mounted) {
        return;
      }

      setState(() {
        _nameController.text =
            _value(user['name']);

        _middleInitialController.text =
            _value(
          user['middle_initial'],
        );

        _phoneController.text =
            _value(user['phone']);

        _streetAddressController.text =
            _value(
          user['street_address'],
        );

        _addressController.text =
            _value(user['address']);

        _regionController.text =
            _value(user['region']);

        _provinceController.text =
            _value(user['province']);

        _cityController.text =
            _value(user['city']);

        _barangayController.text =
            _value(user['barangay']);

        _postalCodeController.text =
            _value(
          user['postal_code'],
        );
      });

      showCartzyFlash(
        context,
        'Changes saved.',
      );
    } catch (e) {
      if (!mounted) {
        return;
      }

      _showMessage(
        'Failed to save profile: $e',
        isError: true,
      );
    } finally {
      if (mounted) {
        setState(() {
          _isSaving = false;
        });
      }
    }
  }

  // ============================================================
  // BIRTHDAY PICKER
  // ============================================================

  Future<void> _selectBirthday() async {
    final now = DateTime.now();

    final initialDate =
        _birthday ??
        DateTime(
          now.year - 18,
          now.month,
          now.day,
        );

    final picked =
        await showDatePicker(
      context: context,

      initialDate: initialDate,

      firstDate: DateTime(
        1900,
      ),

      lastDate: now,

      builder:
          (context, child) {
        return Theme(
          data: Theme.of(context)
              .copyWith(
            colorScheme:
                const ColorScheme.light(
              primary:
                  CartzyColors.coral,

              onPrimary:
                  Colors.white,

              surface:
                  Colors.white,

              onSurface:
                  CartzyColors.text,
            ),
          ),
          child:
              child!,
        );
      },
    );

    if (picked == null) {
      return;
    }

    final calculatedAge =
        _calculateAge(picked);

    setState(() {
      _birthday = picked;

      _age = calculatedAge;
    });
  }

  int _calculateAge(
    DateTime birthDate,
  ) {
    final today =
        DateTime.now();

    int age =
        today.year -
        birthDate.year;

    if (today.month <
            birthDate.month ||
        (today.month ==
                birthDate.month &&
            today.day <
                birthDate.day)) {
      age--;
    }

    return age;
  }

  // ============================================================
  // MESSAGE
  // ============================================================
  //
  // Used for ERRORS only now — success feedback goes through
  // showCartzyFlash instead (see _saveProfile).

  void _showMessage(
    String message, {
    bool isError = false,
  }) {
    if (!mounted) {
      return;
    }

    ScaffoldMessenger.of(context)
        .showSnackBar(
      SnackBar(
        content: Text(
          message,
        ),

        backgroundColor:
            isError
                ? CartzyColors.error
                : CartzyColors.navy,

        behavior:
            SnackBarBehavior.floating,
      ),
    );
  }

  // ============================================================
  // SECTION TITLE
  // ============================================================

  Widget _sectionTitle(
    String title,
  ) {
    return Padding(
      padding:
          const EdgeInsets.only(
        bottom: 16,
      ),
      child: Text(
        title,

        style: const TextStyle(
          fontSize: 18,
          fontWeight:
              FontWeight.w700,
          color:
              CartzyColors.text,
        ),
      ),
    );
  }

  // ============================================================
  // FIELD LABEL
  // ============================================================

  Widget _fieldLabel(
    String label,
  ) {
    return Padding(
      padding:
          const EdgeInsets.only(
        bottom: 7,
      ),
      child: Text(
        label,

        style: const TextStyle(
          fontSize: 13,
          fontWeight:
              FontWeight.w600,
          color:
              CartzyColors.text,
        ),
      ),
    );
  }

  // ============================================================
  // TEXT FIELD
  // ============================================================

  Widget _textField(
    TextEditingController controller,
    IconData icon, {
    String? hint,
    TextInputType? keyboardType,
    bool enabled = true,
  }) {
    return TextField(
      controller: controller,

      enabled: enabled,

      keyboardType:
          keyboardType,

      style: const TextStyle(
        fontSize: 14,
        color:
            CartzyColors.text,
      ),

      decoration:
          _inputDecoration(
        icon,
        hint ?? '',
      ),
    );
  }

  // ============================================================
  // INPUT DECORATION
  // ============================================================

  InputDecoration _inputDecoration(
    IconData icon,
    String hint,
  ) {
    return InputDecoration(
      hintText: hint,

      hintStyle:
          const TextStyle(
        fontSize: 13,
        color:
            CartzyColors.gray,
      ),

      prefixIcon:
          Icon(
        icon,
        color:
            CartzyColors.coral,
        size: 20,
      ),

      filled: true,

      fillColor:
          CartzyColors.surface,

      contentPadding:
          const EdgeInsets.symmetric(
        horizontal: 16,
        vertical: 16,
      ),

      border:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(
          12,
        ),

        borderSide:
            const BorderSide(
          color:
              CartzyColors.border,
          width: 1.2,
        ),
      ),

      enabledBorder:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(
          12,
        ),

        borderSide:
            const BorderSide(
          color:
              CartzyColors.border,
          width: 1.2,
        ),
      ),

      focusedBorder:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(
          12,
        ),

        borderSide:
            const BorderSide(
          color:
              CartzyColors.coral,
          width: 1.8,
        ),
      ),

      disabledBorder:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(
          12,
        ),

        borderSide:
            const BorderSide(
          color:
              CartzyColors.border,
          width: 1.2,
        ),
      ),
    );
  }

  // ============================================================
  // PICKER DECORATION
  // ============================================================

  InputDecoration _pickerDecoration(
    IconData icon,
    String hint,
  ) {
    return InputDecoration(
      hintText: hint,

      hintStyle:
          const TextStyle(
        fontSize: 13,
        color:
            CartzyColors.gray,
      ),

      prefixIcon:
          Icon(
        icon,
        color:
            CartzyColors.coral,
        size: 20,
      ),

      suffixIcon:
          const Icon(
        Icons.keyboard_arrow_down,
        color:
            CartzyColors.gray,
      ),

      filled: true,

      fillColor:
          CartzyColors.surface,

      contentPadding:
          const EdgeInsets.symmetric(
        horizontal: 16,
        vertical: 16,
      ),

      border:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(
          12,
        ),

        borderSide:
            const BorderSide(
          color:
              CartzyColors.border,
          width: 1.2,
        ),
      ),

      enabledBorder:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(
          12,
        ),

        borderSide:
            const BorderSide(
          color:
              CartzyColors.border,
          width: 1.2,
        ),
      ),

      focusedBorder:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(
          12,
        ),

        borderSide:
            const BorderSide(
          color:
              CartzyColors.coral,
          width: 1.8,
        ),
      ),

      disabledBorder:
          OutlineInputBorder(
        borderRadius:
            BorderRadius.circular(
          12,
        ),

        borderSide:
            const BorderSide(
          color:
              CartzyColors.border,
          width: 1.2,
        ),
      ),
    );
  }

  // ============================================================
  // GENDER SELECTOR
  // ============================================================

  Widget _genderSelector() {
    return Row(
      children: [
        _genderOption(
          'Male',
        ),
        const SizedBox(
          width: 20,
        ),
        _genderOption(
          'Female',
        ),
        const SizedBox(
          width: 20,
        ),
        _genderOption(
          'Other',
        ),
      ],
    );
  }

  Widget _genderOption(
    String value,
  ) {
    return InkWell(
      borderRadius:
          BorderRadius.circular(
        8,
      ),

      onTap: () {
        setState(() {
          _selectedGender =
              value;
        });
      },

      child: Row(
        mainAxisSize:
            MainAxisSize.min,
        children: [
          Radio<String>(
            value: value,

            groupValue:
                _selectedGender,

            activeColor:
                CartzyColors.coral,

            onChanged:
                (newValue) {
              if (newValue ==
                  null) {
                return;
              }

              setState(() {
                _selectedGender =
                    newValue;
              });
            },
          ),

          Text(
            value,

            style:
                const TextStyle(
              fontSize: 13,
              color:
                  CartzyColors.text,
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // DATE DISPLAY
  // ============================================================

  String _formattedBirthday() {
    if (_birthday == null) {
      return 'Select birthday';
    }

    final month =
        _birthday!.month
            .toString()
            .padLeft(2, '0');

    final day =
        _birthday!.day
            .toString()
            .padLeft(2, '0');

    final year =
        _birthday!.year
            .toString();

    return '$month/$day/$year';
  }

  // ============================================================
  // BIRTHDAY FIELD
  // ============================================================

  Widget _birthdayField() {
    return InkWell(
      onTap:
          _selectBirthday,

      borderRadius:
          BorderRadius.circular(
        12,
      ),

      child: InputDecorator(
        decoration:
            _inputDecoration(
          Icons.calendar_today_outlined,
          '',
        ),

        child: Row(
          children: [
            Expanded(
              child: Text(
                _formattedBirthday(),

                style:
                    TextStyle(
                  fontSize: 14,

                  color:
                      _birthday == null
                          ? CartzyColors.gray
                          : CartzyColors.text,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ============================================================
  // AGE FIELD
  // ============================================================

  Widget _ageField() {
    return InputDecorator(
      decoration:
          _inputDecoration(
        Icons.cake_outlined,
        '',
      ),

      child: Text(
        _age == null
            ? 'Age'
            : '${_age!} years old',

        style:
            TextStyle(
          fontSize: 14,

          color:
              _age == null
                  ? CartzyColors.gray
                  : CartzyColors.text,
        ),
      ),
    );
  }

  // ============================================================
  // ID STATUS
  // ============================================================

  String _formattedIdStatus() {
    if (_idStatus.trim().isEmpty) {
      return 'Unverified';
    }

    final status =
        _idStatus.trim();

    return status[0].toUpperCase() +
        status.substring(1);
  }

  // ============================================================
  // ID SECTION
  // ============================================================

  Widget _identityVerification() {
    final filename =
        _idOriginalFilename
                .trim()
                .isNotEmpty
            ? _idOriginalFilename
            : _getFileName(
                _idPhoto,
              );

    return Container(
      width: double.infinity,

      padding:
          const EdgeInsets.all(
        18,
      ),

      decoration:
          BoxDecoration(
        color:
            CartzyColors.surface,

        borderRadius:
            BorderRadius.circular(
          16,
        ),

        border:
            Border.all(
          color:
              CartzyColors.border,
        ),
      ),

      child: Column(
        crossAxisAlignment:
            CrossAxisAlignment.start,

        children: [
          Row(
            children: [
              Container(
                width: 42,
                height: 42,

                decoration:
                    BoxDecoration(
                  color:
                      CartzyColors.coral
                          .withValues(
                    alpha: 0.10,
                  ),

                  borderRadius:
                      BorderRadius.circular(
                    12,
                  ),
                ),

                child: const Icon(
                  Icons
                      .verified_user_outlined,

                  color:
                      CartzyColors.coral,

                  size: 22,
                ),
              ),

              const SizedBox(
                width: 12,
              ),

              const Expanded(
                child: Text(
                  'Identity Verification',

                  style:
                      TextStyle(
                    fontSize: 15,
                    fontWeight:
                        FontWeight.w700,
                    color:
                        CartzyColors.text,
                  ),
                ),
              ),

              Container(
                padding:
                    const EdgeInsets
                        .symmetric(
                  horizontal: 10,
                  vertical: 5,
                ),

                decoration:
                    BoxDecoration(
                  color:
                      _idStatus.toLowerCase() ==
                              'verified'
                          ? Colors.green
                              .withValues(
                              alpha: 0.10,
                            )
                          : CartzyColors.gold
                              .withValues(
                              alpha: 0.15,
                            ),

                  borderRadius:
                      BorderRadius.circular(
                    20,
                  ),
                ),

                child: Text(
                  _formattedIdStatus(),

                  style:
                      TextStyle(
                    fontSize: 11,
                    fontWeight:
                        FontWeight.w600,
                    color:
                        _idStatus.toLowerCase() ==
                                'verified'
                            ? Colors.green
                            : Colors.orange,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(
            height: 20,
          ),

          _idInfoRow(
            'ID Type',
            _idType.isEmpty
                ? 'Not provided'
                : _idType,
          ),

          const SizedBox(
            height: 12,
          ),

          _idInfoRow(
            'ID Number',
            _idNumberController
                    .text
                    .trim()
                    .isEmpty
                ? 'Not provided'
                : _idNumberController
                    .text
                    .trim(),
          ),

          const SizedBox(
            height: 12,
          ),

          _idInfoRow(
            'Uploaded File',
            filename.isEmpty
                ? 'No ID uploaded'
                : filename,
          ),
        ],
      ),
    );
  }

  // ============================================================
  // ID INFO ROW
  // ============================================================

  Widget _idInfoRow(
    String label,
    String value,
  ) {
    return Row(
      crossAxisAlignment:
          CrossAxisAlignment.start,

      children: [
        SizedBox(
          width: 105,

          child: Text(
            label,

            style:
                const TextStyle(
              fontSize: 12,
              color:
                  CartzyColors.gray,
            ),
          ),
        ),

        Expanded(
          child: Text(
            value,

            style:
                const TextStyle(
              fontSize: 13,
              fontWeight:
                  FontWeight.w600,
              color:
                  CartzyColors.text,
            ),
          ),
        ),
      ],
    );
  }

  // ============================================================
  // LOADING SCREEN
  // ============================================================

  Widget _loadingScreen() {
    return Scaffold(
      backgroundColor:
          CartzyColors.background,

      appBar: AppBar(
        backgroundColor:
            CartzyColors.background,

        elevation: 0,

        leading:
            IconButton(
          icon:
              const Icon(
            Icons.arrow_back,
          ),

          color:
              CartzyColors.text,

          onPressed:
              widget.onBack,
        ),

        title:
            const Text(
          'Account Settings',

          style:
              TextStyle(
            color:
                CartzyColors.text,

            fontSize: 18,

            fontWeight:
                FontWeight.w700,
          ),
        ),
      ),

      body:
          const Center(
        child:
            CircularProgressIndicator(
          color:
              CartzyColors.coral,
        ),
      ),
    );
  }

  // ============================================================
  // MAIN BUILD
  // ============================================================

  @override
  Widget build(
    BuildContext context,
  ) {
    if (_isLoading) {
      return _loadingScreen();
    }

    return Scaffold(
      backgroundColor:
          CartzyColors.background,

      appBar: AppBar(
        backgroundColor:
            CartzyColors.background,

        elevation: 0,

        leading:
            IconButton(
          icon:
              const Icon(
            Icons.arrow_back,
          ),

          color:
              CartzyColors.text,

          onPressed:
              widget.onBack,
        ),

        title:
            const Text(
          'Account Settings',

          style:
              TextStyle(
            color:
                CartzyColors.text,

            fontSize: 18,

            fontWeight:
                FontWeight.w700,
          ),
        ),
      ),

      body: SafeArea(
        child: SingleChildScrollView(
          padding:
              const EdgeInsets.fromLTRB(
            20,
            10,
            20,
            40,
          ),

          child: Column(
            crossAxisAlignment:
                CrossAxisAlignment.start,

            children: [
              // ==================================================
              // PERSONAL INFORMATION
              // ==================================================

              _sectionTitle(
                'Personal Information',
              ),

              _fieldLabel(
                'Full Name',
              ),

              _textField(
                _nameController,
                Icons.person_outline,
                hint: 'Full Name',
              ),

              const SizedBox(
                height: 16,
              ),

              _fieldLabel(
                'Middle Initial',
              ),

              _textField(
                _middleInitialController,
                Icons.badge_outlined,
                hint: 'Middle Initial',
              ),

              const SizedBox(
                height: 16,
              ),

              _fieldLabel(
                'Email Address',
              ),

              _textField(
                TextEditingController(
                  text: _email,
                ),
                Icons.email_outlined,
                hint: 'Email Address',
                enabled: false,
              ),

              const SizedBox(
                height: 16,
              ),

              _fieldLabel(
                'Phone Number',
              ),

              _textField(
                _phoneController,
                Icons.phone_outlined,
                hint: 'Phone Number',
                keyboardType:
                    TextInputType.phone,
              ),

              const SizedBox(
                height: 16,
              ),

              _fieldLabel(
                'Gender',
              ),

              _genderSelector(),

              const SizedBox(
                height: 16,
              ),

              _fieldLabel(
                'Birthday',
              ),

              _birthdayField(),

              const SizedBox(
                height: 16,
              ),

              _fieldLabel(
                'Age',
              ),

              _ageField(),

              const SizedBox(
                height: 30,
              ),

              // ==================================================
              // DELIVERY ADDRESS
              // ==================================================

              _sectionTitle(
                'Delivery Address',
              ),

              _fieldLabel(
                'Street / House Number',
              ),

              _textField(
                _streetAddressController,
                Icons.home_outlined,
                hint:
                    'Street / House Number',
              ),

              const SizedBox(
                height: 16,
              ),

              _fieldLabel(
                'Complete Address',
              ),

              _textField(
                _addressController,
                Icons.location_on_outlined,
                hint:
                    'Complete Address',
              ),

              const SizedBox(
                height: 16,
              ),

              // REGION

              _fieldLabel(
                'Region',
              ),

              _regionPicker(),

              const SizedBox(
                height: 16,
              ),

              // PROVINCE

              _fieldLabel(
                'Province',
              ),

              _provincePicker(),

              const SizedBox(
                height: 16,
              ),

              // CITY

              _fieldLabel(
                'City / Municipality',
              ),

              _cityPicker(),

              const SizedBox(
                height: 16,
              ),

              // BARANGAY

              _fieldLabel(
                'Barangay',
              ),

              _barangayPicker(),

              const SizedBox(
                height: 16,
              ),

              // POSTAL CODE

              _fieldLabel(
                'Postal Code',
              ),

              _textField(
                _postalCodeController,
                Icons
                    .markunread_mailbox_outlined,
                hint:
                    'Postal Code',
                keyboardType:
                    TextInputType.number,
              ),

              const SizedBox(
                height: 30,
              ),

              // ==================================================
              // IDENTITY VERIFICATION
              // ==================================================

              _sectionTitle(
                'Identity Verification',
              ),

              _identityVerification(),

              const SizedBox(
                height: 30,
              ),

              // ==================================================
              // SAVE BUTTON
              // ==================================================

              SizedBox(
                width: double.infinity,

                height: 54,

                child:
                    ElevatedButton(
                  onPressed:
                      _isSaving
                          ? null
                          : _saveProfile,

                  style:
                      ElevatedButton.styleFrom(
                    backgroundColor:
                        CartzyColors.coral,

                    foregroundColor:
                        Colors.white,

                    disabledBackgroundColor:
                        CartzyColors.gray
                            .withValues(
                      alpha: 0.35,
                    ),

                    elevation: 0,

                    shape:
                        RoundedRectangleBorder(
                      borderRadius:
                          BorderRadius.circular(
                        14,
                      ),
                    ),
                  ),

                  child:
                      _isSaving
                          ? const SizedBox(
                              width: 22,
                              height: 22,
                              child:
                                  CircularProgressIndicator(
                                strokeWidth:
                                    2.5,
                                color:
                                    Colors.white,
                              ),
                            )
                          : const Text(
                              'Save Changes',

                              style:
                                  TextStyle(
                                fontSize:
                                    15,
                                fontWeight:
                                    FontWeight.w700,
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
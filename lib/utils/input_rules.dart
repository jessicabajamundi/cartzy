import 'package:flutter/services.dart';

/// Typing filters and validators for the name and phone fields used across
/// the app's forms (addresses, profile, checkout). Registration has its own,
/// stricter rules in BuyerRegistrationValidation to match the signup API.
class InputRules {
  /// Letters in any language, spaces, and the punctuation real names use:
  /// "Ma. Clara", "Dela Cruz-Reyes", "O'Neil".
  static final _fullNameCharacters = RegExp(r"[\p{L}\p{M} .'\-]", unicode: true);
  static final _fullName = RegExp(r"^[\p{L}\p{M} .'\-]+$", unicode: true);
  static final _hasLetter = RegExp(r'\p{L}', unicode: true);
  static const fullNameMaxLength = 100;

  /// Philippine mobile number: 09XXXXXXXXX or +639XXXXXXXXX.
  static final _phone = RegExp(r'^(09\d{9}|\+639\d{9})$');

  static List<TextInputFormatter> get fullNameFormatters => [
        FilteringTextInputFormatter.allow(_fullNameCharacters),
        LengthLimitingTextInputFormatter(fullNameMaxLength),
      ];

  static List<TextInputFormatter> get middleInitialFormatters => [
        FilteringTextInputFormatter.allow(RegExp(r'[\p{L}.]', unicode: true)),
        LengthLimitingTextInputFormatter(3),
      ];

  static List<TextInputFormatter> get phoneFormatters => [
        FilteringTextInputFormatter.allow(RegExp(r'[0-9+]')),
        LengthLimitingTextInputFormatter(13),
      ];

  static String? fullName(String? value, {String label = 'name'}) {
    final trimmed = (value ?? '').trim();
    if (trimmed.isEmpty) return 'Please enter your $label.';
    if (!_fullName.hasMatch(trimmed) || !_hasLetter.hasMatch(trimmed)) {
      return 'Use letters only (spaces, periods, hyphens and apostrophes are allowed).';
    }
    if (trimmed.runes.length > fullNameMaxLength) {
      return 'Name must not exceed $fullNameMaxLength characters.';
    }
    return null;
  }

  static String? phone(String? value) {
    final digits = (value ?? '').replaceAll(RegExp(r'[\s-]'), '');
    if (digits.isEmpty) return 'Please enter your phone number.';
    if (!_phone.hasMatch(digits)) {
      return 'Enter a valid mobile number (09XXXXXXXXX or +639XXXXXXXXX).';
    }
    return null;
  }
}

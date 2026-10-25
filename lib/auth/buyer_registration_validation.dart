/// Buyer rules shared by input validation and submit validation.
class BuyerRegistrationValidation {
  static final nameCharacters = RegExp(r'[\p{L}\p{M} ]', unicode: true);
  static final _name = RegExp(r'^(?:\p{L}\p{M}*| )+$', unicode: true);

  static String? name(String value, String label) {
    final trimmed = value.trim();
    if (trimmed.isEmpty) return 'Please enter your $label.';
    if (trimmed.runes.length > 50) return 'Name must not exceed 50 characters.';
    if (!_name.hasMatch(trimmed)) {
      return 'Special characters and numbers are not allowed.';
    }
    return null;
  }

  static String? phone(String value) {
    if (value.isEmpty) return 'Please enter your mobile number.';
    if (!RegExp(r'^[0-9]+$').hasMatch(value)) return 'Please enter digits only.';
    if (value.length != 10) return 'Enter exactly 10 digits after +63.';
    if (!value.startsWith('9')) return 'Mobile number must start with 9 after +63.';
    return null;
  }

  static String phoneForApi(String value) => value.isEmpty ? '' : '0$value';

  static String? email(String value) {
    final trimmed = value.trim();
    if (trimmed.length > 255 ||
        !RegExp(r'^[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)+$').hasMatch(trimmed)) {
      return 'Please enter a valid email address.';
    }
    return null;
  }

  // The mobile API requires eight characters; web buyer registration uses six.
  static String? password(String value) => value.length < 8
      ? 'Password must be at least 8 characters long.'
      : null;
}

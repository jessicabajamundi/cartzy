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

  static const minimumAge = 18;

  static const _months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  /// Parses the "January 05, 2000" format the birthday picker produces.
  static DateTime? parseBirthday(String value) {
    final parts = value.replaceAll(',', '').trim().split(RegExp(r'\s+'));
    if (parts.length != 3) return null;
    final month = _months.indexOf(parts[0]) + 1;
    final day = int.tryParse(parts[1]);
    final year = int.tryParse(parts[2]);
    if (month == 0 || day == null || year == null) return null;
    final date = DateTime(year, month, day);
    // Reject rollovers like February 31.
    if (date.month != month || date.day != day) return null;
    return date;
  }

  static int? ageFromBirthday(String value, {DateTime? today}) {
    final birthDate = parseBirthday(value);
    if (birthDate == null) return null;
    final now = today ?? DateTime.now();
    var age = now.year - birthDate.year;
    if (now.month < birthDate.month ||
        (now.month == birthDate.month && now.day < birthDate.day)) {
      age--;
    }
    return age;
  }

  static String? birthday(String value, {DateTime? today}) {
    if (value.trim().isEmpty) return 'Please enter your birthday.';
    final age = ageFromBirthday(value, today: today);
    if (age == null || age < 0) return 'Please enter a valid birthday.';
    if (age < minimumAge) {
      return 'You must be at least $minimumAge years old to register.';
    }
    return null;
  }

  // The mobile API requires eight characters; web buyer registration uses six.
  static String? password(String value) => value.length < 8
      ? 'Password must be at least 8 characters long.'
      : null;
}

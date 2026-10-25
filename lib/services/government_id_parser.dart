/// Pulls the ID type, ID number, name and birthday out of the text read from
/// a photo of a Philippine government ID. Text recognition is never perfect,
/// so every field is a suggestion the rider checks before submitting.
class GovernmentIdFields {
  final String? idType;
  final String? idNumber;
  final String? firstName;
  final String? lastName;
  final DateTime? birthday;

  const GovernmentIdFields({
    this.idType,
    this.idNumber,
    this.firstName,
    this.lastName,
    this.birthday,
  });

  bool get isEmpty =>
      idNumber == null && firstName == null && lastName == null && birthday == null;
}

class GovernmentIdParser {
  static const philSys = 'PhilSys National ID';
  static const driversLicense = "Driver's License";
  static const umid = 'UMID';
  static const passport = 'Passport';
  static const sss = 'SSS ID';
  static const philHealth = 'PhilHealth ID';
  static const prc = 'PRC ID';
  static const postal = 'Postal ID';
  static const voters = "Voter's ID";
  static const tin = 'TIN ID';

  /// ID types riders can choose, in the order shown.
  static const idTypes = [
    philSys, driversLicense, umid, passport, sss,
    philHealth, prc, postal, voters, tin,
  ];

  /// Headings printed on each card, used to tell which ID was scanned.
  static final _typeKeywords = <String, RegExp>{
    philSys: RegExp(r'PAMBANSANG PAGKAKAKILANLAN|PHILIPPINE IDENTIFICATION|PHILSYS|\bPCN\b'),
    driversLicense: RegExp(r"LAND TRANSPORTATION|DRIVER'?S LICENSE|NON-PROFESSIONAL|\bLTO\b"),
    umid: RegExp(r'UNIFIED MULTI-?PURPOSE|\bUMID\b|\bCRN\b'),
    passport: RegExp(r'PASAPORTE|PASSPORT|^P<PHL', multiLine: true),
    sss: RegExp(r'SOCIAL SECURITY SYSTEM'),
    philHealth: RegExp(r'PHILHEALTH|PHILIPPINE HEALTH INSURANCE'),
    prc: RegExp(r'PROFESSIONAL REGULATION'),
    postal: RegExp(r'POSTAL IDENTITY|PHLPOST|PHILIPPINE POSTAL'),
    voters: RegExp(r'COMMISSION ON ELECTIONS|COMELEC|VOTER'),
    tin: RegExp(r'BUREAU OF INTERNAL REVENUE|TAXPAYER'),
  };

  /// Number format for each ID type.
  static final _numberPatterns = <String, RegExp>{
    philSys: RegExp(r'\b\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}\b'),
    driversLicense: RegExp(r'\b[A-Z]\d{2}[- ]?\d{2}[- ]?\d{6}\b'),
    umid: RegExp(r'\b\d{4}[- ]?\d{7}[- ]?\d\b'),
    passport: RegExp(r'\b[A-Z]{1,2}\d{7}[A-Z]?\b'),
    sss: RegExp(r'\b\d{2}[- ]?\d{7}[- ]?\d\b'),
    philHealth: RegExp(r'\b\d{2}[- ]?\d{9}[- ]?\d\b'),
    prc: RegExp(r'\b\d{7}\b'),
    postal: RegExp(r'\b[A-Z0-9]{12,13}\b'),
    voters: RegExp(r'\b\d{4}-?\d{4}[A-Z]{1,4}\d{5,8}[A-Z]{1,4}\d{4,6}\b'),
    tin: RegExp(r'\b\d{3}[- ]?\d{3}[- ]?\d{3}(?:[- ]?\d{3,5})?\b'),
  };

  static final _lastNameLabel =
      RegExp(r'\b(LAST\s*NAME|SURNAME|APELYIDO)\b(?!\s*,)');
  static final _firstNameLabel =
      RegExp(r'\b(GIVEN\s*NAMES?|FIRST\s*NAME|MGA\s*PANGALAN)\b');
  static final _combinedNameLabel =
      RegExp(r'LAST\s*NAME\s*,\s*FIRST\s*NAME');
  static final _birthdayLabel =
      RegExp(r'DATE\s*OF\s*BIRTH|BIRTH\s*DATE|PETSA\s*NG\s*KAPANGANAKAN|\bDOB\b');
  static final _anyLabel = RegExp(
      r'NAME|APELYIDO|PANGALAN|BIRTH|KAPANGANAKAN|SEX|ADDRESS|TIRAHAN|NATIONALITY|'
      r'EXPIR|AGENCY|BLOOD|HEIGHT|WEIGHT|DATE|NO\.|NUMBER|SIGNATURE|REPUBLIC|REPUBLIKA');

  static const _months = {
    'JAN': 1, 'FEB': 2, 'MAR': 3, 'APR': 4, 'MAY': 5, 'JUN': 6,
    'JUL': 7, 'AUG': 8, 'SEP': 9, 'OCT': 10, 'NOV': 11, 'DEC': 12,
  };

  /// [preferredType] is the ID type the rider picked, if any; it decides
  /// which number format to look for when the card's heading isn't readable.
  static GovernmentIdFields parse(String text, {String? preferredType}) {
    final upper = text.toUpperCase();
    final lines = upper
        .split('\n')
        .map((l) => l.trim())
        .where((l) => l.isNotEmpty)
        .toList();

    final type = _detectType(upper) ?? preferredType;
    final mrz = _parsePassportMrz(lines);

    String? lastName = mrz?.$1;
    String? firstName = mrz?.$2;

    if (lastName == null || firstName == null) {
      final combined = _valueAfter(lines, _combinedNameLabel);
      if (combined != null && combined.contains(',')) {
        final parts = combined.split(',');
        lastName ??= parts[0];
        firstName ??= parts.sublist(1).join(' ');
      }
    }
    lastName ??= _valueAfter(lines, _lastNameLabel);
    firstName ??= _valueAfter(lines, _firstNameLabel);

    return GovernmentIdFields(
      idType: type,
      idNumber: _passportMrzNumber(lines) ?? _findNumber(upper, type),
      lastName: _cleanName(lastName),
      firstName: _cleanName(firstName),
      birthday: _findBirthday(lines),
    );
  }

  static String? _detectType(String upper) {
    for (final entry in _typeKeywords.entries) {
      if (entry.value.hasMatch(upper)) return entry.key;
    }
    return null;
  }

  static String? _findNumber(String upper, String? type) {
    final pattern = _numberPatterns[type];
    if (pattern == null) return null;
    // Dates look like some ID numbers, so skip anything that reads as one.
    for (final match in pattern.allMatches(upper)) {
      final value = match.group(0)!;
      if (_parseDate(value) == null) return value.replaceAll(' ', '-');
    }
    return null;
  }

  /// The value printed on the same line after the label, or on the next line.
  static String? _valueAfter(List<String> lines, RegExp label) {
    for (var i = 0; i < lines.length; i++) {
      final match = label.firstMatch(lines[i]);
      if (match == null) continue;
      final rest = lines[i]
          .substring(match.end)
          .replaceFirst(RegExp(r'^[\s:/.\-]+'), '')
          .trim();
      if (rest.length > 1 && !_anyLabel.hasMatch(rest)) return rest;
      for (var j = i + 1; j < lines.length && j <= i + 2; j++) {
        if (!_anyLabel.hasMatch(lines[j])) return lines[j];
      }
    }
    return null;
  }

  /// Passport machine-readable line: P<PHLDELA<CRUZ<<JUAN<PROTACIO<<<<
  static (String, String)? _parsePassportMrz(List<String> lines) {
    for (final line in lines) {
      final compact = line.replaceAll(' ', '');
      final match = RegExp(r'^P<[A-Z]{3}([A-Z<]+?)<<([A-Z<]+)').firstMatch(compact);
      if (match != null) {
        String clean(String s) => s.replaceAll('<', ' ').trim();
        return (clean(match.group(1)!), clean(match.group(2)!));
      }
    }
    return null;
  }

  /// Passport number: the first 9 characters of the second machine-readable
  /// line, e.g. P1234567A5PHL9001011M...
  static String? _passportMrzNumber(List<String> lines) {
    for (final line in lines) {
      final match = RegExp(r'^([A-Z0-9<]{9})\d[A-Z]{3}\d{6}')
          .firstMatch(line.replaceAll(' ', ''));
      if (match != null) return match.group(1)!.replaceAll('<', '');
    }
    return null;
  }

  static String? _cleanName(String? raw) {
    if (raw == null) return null;
    final cleaned = raw
        .replaceAll(RegExp(r"[^A-ZÑ .'\-]"), ' ')
        .replaceAll(RegExp(r'\s+'), ' ')
        .trim();
    if (cleaned.length < 2) return null;
    // "DELA CRUZ" -> "Dela Cruz"
    return cleaned
        .split(' ')
        .map((w) => w.isEmpty ? w : w[0] + w.substring(1).toLowerCase())
        .join(' ');
  }

  static DateTime? _findBirthday(List<String> lines) {
    // Prefer the date next to a birth-date label; IDs also print issue and
    // expiry dates.
    for (var i = 0; i < lines.length; i++) {
      if (!_birthdayLabel.hasMatch(lines[i])) continue;
      for (var j = i; j < lines.length && j <= i + 2; j++) {
        final date = _firstDate(lines[j]);
        if (date != null) return date;
      }
    }
    // Otherwise the earliest date on the card is the birthday.
    final dates = lines.map(_firstDate).whereType<DateTime>().toList()..sort();
    final now = DateTime.now();
    return dates.where((d) => now.year - d.year >= 18 && now.year - d.year < 100).firstOrNull;
  }

  static final _datePatterns = [
    RegExp(r'\b(\d{4})[/\-.](\d{1,2})[/\-.](\d{1,2})\b'), // 1990/01/31
    RegExp(r'\b(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{4})\b'), // 01/31/1990
    RegExp(r'\b([A-Z]{3})[A-Z]*\.?\s+(\d{1,2}),?\s+(\d{4})\b'), // JANUARY 31, 1990
    RegExp(r'\b(\d{1,2})\s+([A-Z]{3})[A-Z]*\.?\s+(\d{4})\b'), // 31 JAN 1990
  ];

  static DateTime? _firstDate(String line) {
    for (final pattern in _datePatterns) {
      final match = pattern.firstMatch(line);
      if (match != null) {
        final date = _parseDate(match.group(0)!);
        if (date != null) return date;
      }
    }
    return null;
  }

  static DateTime? _parseDate(String value) {
    DateTime? build(int y, int m, int d) {
      if (y < 1900 || m < 1 || m > 12 || d < 1 || d > 31) return null;
      final date = DateTime(y, m, d);
      return date.month == m ? date : null;
    }

    final v = value.toUpperCase();
    var m = _datePatterns[0].firstMatch(v);
    if (m != null && m.group(0) == v) {
      return build(int.parse(m[1]!), int.parse(m[2]!), int.parse(m[3]!));
    }
    m = _datePatterns[1].firstMatch(v);
    if (m != null && m.group(0) == v) {
      // Philippine IDs print month first: MM/DD/YYYY.
      return build(int.parse(m[3]!), int.parse(m[1]!), int.parse(m[2]!));
    }
    m = _datePatterns[2].firstMatch(v);
    if (m != null && _months.containsKey(m[1])) {
      return build(int.parse(m[3]!), _months[m[1]]!, int.parse(m[2]!));
    }
    m = _datePatterns[3].firstMatch(v);
    if (m != null && _months.containsKey(m[2])) {
      return build(int.parse(m[3]!), _months[m[2]]!, int.parse(m[1]!));
    }
    return null;
  }
}

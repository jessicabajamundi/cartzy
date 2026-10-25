import 'package:flutter_test/flutter_test.dart';
import 'package:cartzy/auth/buyer_registration_validation.dart';

void main() {
  test('names accept Unicode letters and spaces, with a 50-character limit', () {
    for (final name in ['A', 'María José', 'Peña', 'Jose\u0301', '李明', 'A' * 50]) {
      expect(BuyerRegistrationValidation.name(name, 'first name'), isNull);
    }
    for (final name in ['', '   ', 'Ana1', 'Ana-Mae', "O'Brien", 'Ana.', '😀', 'A' * 51]) {
      expect(BuyerRegistrationValidation.name(name, 'first name'), isNotNull);
    }
  });

  test('phone uses ten digits in the UI and eleven for the mobile API', () {
    expect(BuyerRegistrationValidation.phone('9171234567'), isNull);
    expect(BuyerRegistrationValidation.phoneForApi('9171234567'), '09171234567');
    for (final phone in ['', '09171234567', '8171234567', '917123456', '917123456x']) {
      expect(BuyerRegistrationValidation.phone(phone), isNotNull);
    }
  });

  test('email trims outer spaces and rejects malformed domains', () {
    expect(BuyerRegistrationValidation.email(' buyer@example.com '), isNull);
    for (final email in ['', 'a@b', 'a@@b.com', 'a@b..com', 'a b@example.com']) {
      expect(BuyerRegistrationValidation.email(email), isNotNull);
    }
  });

  test('buyer password follows API minimum without seller complexity rules', () {
    expect(BuyerRegistrationValidation.password('abcdefg'), isNotNull);
    expect(BuyerRegistrationValidation.password('abcdefgh'), isNull);
  });
}

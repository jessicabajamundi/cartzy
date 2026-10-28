import 'package:cartzy/auth/buyer_registration_validation.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  final today = DateTime(2026, 10, 27);
  String? check(String value) =>
      BuyerRegistrationValidation.birthday(value, today: today);

  test('empty birthday is required', () {
    expect(check(''), 'Please enter your birthday.');
  });

  test('exactly 18 today can register', () {
    expect(check('October 27, 2008'), isNull);
  });

  test('one day short of 18 cannot register', () {
    expect(check('October 28, 2008'),
        'You must be at least 18 years old to register.');
  });

  test('recent birth years are blocked', () {
    expect(check('January 01, 2026'), isNotNull);
  });

  test('future and invalid dates are rejected', () {
    expect(check('December 01, 2026'), 'Please enter a valid birthday.');
    expect(check('February 31, 2000'), 'Please enter a valid birthday.');
  });

  test('picker and ID-scan formats both parse', () {
    expect(check('March 05, 1990'), isNull);
    expect(check('March 5, 1990'), isNull);
  });
}

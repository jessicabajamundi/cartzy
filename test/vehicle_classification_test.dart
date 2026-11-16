import 'package:cartzy/services/auth_service.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('registered light vehicles stay riders despite stale courier assignments', () {
    for (final vehicle in ['motorcycle', 'Tricycle', 'E-bike']) {
      expect(AuthService.isCourierAccount({'vehicle_type': vehicle, 'courier_type': 'first_mile'}), isFalse);
    }
  });

  test('registered heavy vehicles open the courier dashboard', () {
    for (final vehicle in ['Car', 'van', 'truck']) {
      expect(AuthService.isCourierAccount({'vehicle_type': vehicle, 'courier_type': 'last_mile'}), isTrue);
    }
  });

  test('older sessions recognize both courier assignments', () {
    expect(AuthService.isCourierAccount({'courier_type': 'first_mile'}), isTrue);
    expect(AuthService.isCourierAccount({'courier_type': 'linehaul'}), isTrue);
    expect(AuthService.isCourierAccount({'courier_type': 'last_mile'}), isFalse);
    expect(AuthService.isCourierAccount(null), isFalse);
  });
}

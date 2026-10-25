import 'package:cartzy/auth/buyer_registration.dart';
import 'package:cartzy/rider/rider_registration.dart';
import 'package:cartzy/utils/input_rules.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

String _type(List<TextInputFormatter> formatters, String text) {
  var value = TextEditingValue(
      text: text, selection: TextSelection.collapsed(offset: text.length));
  for (final f in formatters) {
    value = f.formatEditUpdate(TextEditingValue.empty, value);
  }
  return value.text;
}

void main() {
  group('InputRules.fullName', () {
    test('typing strips digits and symbols', () {
      expect(_type(InputRules.fullNameFormatters, 'Juan1 Dela@ Cruz#2'),
          'Juan Dela Cruz');
    });

    test('keeps punctuation real names use', () {
      expect(_type(InputRules.fullNameFormatters, "Ma. Clara O'Neil-Reyes"),
          "Ma. Clara O'Neil-Reyes");
      expect(InputRules.fullName("Ma. Clara O'Neil-Reyes"), isNull);
      expect(InputRules.fullName('José Ñuñez'), isNull);
    });

    test('rejects empty, digits and punctuation-only', () {
      expect(InputRules.fullName('  '), isNotNull);
      expect(InputRules.fullName('Juan 2'), isNotNull);
      expect(InputRules.fullName('.-'), isNotNull);
    });
  });

  group('InputRules.phone', () {
    test('typing keeps only digits and +', () {
      expect(_type(InputRules.phoneFormatters, '+63 917-abc-1234567'),
          '+639171234567');
    });

    test('accepts PH mobile formats', () {
      expect(InputRules.phone('09171234567'), isNull);
      expect(InputRules.phone('+639171234567'), isNull);
      expect(InputRules.phone('0917 123 4567'), isNull);
    });

    test('rejects wrong lengths and prefixes', () {
      expect(InputRules.phone(''), isNotNull);
      expect(InputRules.phone('0917123456'), isNotNull);
      expect(InputRules.phone('08171234567'), isNotNull);
    });
  });

  testWidgets('registration name fields block digits and symbols',
      (tester) async {
    GoogleFonts.config.allowRuntimeFetching = false;
    await tester.pumpWidget(MaterialApp(
      home: RiderRegistration(
          onRegistrationSubmitted: () {}, onBackToLogin: () {}),
    ));
    final firstName = find.widgetWithText(RegistrationTextField, 'First name');
    await tester.enterText(
        find.descendant(of: firstName, matching: find.byType(TextField)),
        'Juan123!@');
    await tester.pump();
    final field = tester.widget<RegistrationTextField>(firstName);
    expect(field.controller.text, 'Juan');
  });
}

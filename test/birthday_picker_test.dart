import 'package:cartzy/auth/buyer_registration.dart' show BirthdayWheelPicker;
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('year wheel starts at the year users turn 18', (tester) async {
    await tester.pumpWidget(MaterialApp(
      home: Scaffold(
        body: BirthdayWheelPicker(
          currentBirthday: 'January 01, 2026',
          accent: Colors.red,
          onDismiss: () {},
          onDateSelected: (_) {},
        ),
      ),
    ));

    final latestYear = DateTime.now().year - 18;
    expect(find.text('${DateTime.now().year}'), findsNothing);
    expect(find.text('$latestYear'), findsOneWidget);
    // An under-18 saved birthday is pulled back into the allowed range.
    expect(find.textContaining(', $latestYear'), findsOneWidget);
  });
}

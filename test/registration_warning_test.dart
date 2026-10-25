import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cartzy/auth/buyer_registration.dart';
import 'package:cartzy/auth/buyer_registration_validation.dart';

void main() {
  testWidgets('blocked name input shows a temporary warning and blur validates', (tester) async {
    GoogleFonts.config.allowRuntimeFetching = false;
    final controller = TextEditingController();
    await tester.pumpWidget(MaterialApp(home: Scaffold(body: RegistrationTextField(
      controller: controller,
      label: 'First name *',
      maxLength: 50,
      validator: (value) => BuyerRegistrationValidation.name(value, 'first name'),
      inputFormatters: [FilteringTextInputFormatter.allow(BuyerRegistrationValidation.nameCharacters)],
    ))));
    await tester.enterText(find.byType(TextField), 'Ana1!');
    await tester.pump();
    expect(controller.text, 'Ana');
    expect(find.text('Special characters and numbers are not allowed.'), findsOneWidget);
    await tester.pump(const Duration(milliseconds: 2600));
    expect(find.text('Special characters and numbers are not allowed.'), findsNothing);
    await tester.enterText(find.byType(TextField), '');
    FocusManager.instance.primaryFocus?.unfocus();
    await tester.pump();
    expect(find.text('Please enter your first name.'), findsOneWidget);
    await tester.pumpWidget(const SizedBox());
    controller.dispose();
  });
}

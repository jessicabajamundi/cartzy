import 'package:cartzy/widgets/courier_terms_prompt.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  for (final dark in [false, true]) {
    testWidgets(
        'courier can read the complete policy and agree at its end, dark=$dark',
        (tester) async {
      tester.view.physicalSize = const Size(390, 844);
      tester.view.devicePixelRatio = 1;
      addTearDown(tester.view.reset);
      var agreed = false;
      await tester.pumpWidget(MaterialApp(
        theme: dark ? ThemeData.dark() : ThemeData.light(),
        home: Scaffold(
            body: StatefulBuilder(
                builder: (context, update) => Padding(
                      padding: const EdgeInsets.all(20),
                      child: CourierTermsPrompt(
                          agreed: agreed,
                          showError: true,
                          onChanged: (value) => update(() => agreed = value)),
                    ))),
      ));
      expect(find.textContaining('You must open and agree'), findsOneWidget);
      await tester.runAsync(() async {
        await tester.tap(
            find.text('Cartzy Courier Terms & Conditions and Privacy Policy'));
        await Future<void>.delayed(const Duration(milliseconds: 100));
      });
      await tester.pumpAndSettle();
      expect(find.text('COURIER TERMS & CONDITIONS'), findsOneWidget);
      expect(find.text('12. Changes to Terms'), findsOneWidget);
      expect(find.text('11. Contact and Support'), findsOneWidget);
      expect(find.text('COURIER AGREEMENT'), findsOneWidget);
      expect(agreed, false);
      await tester.scrollUntilVisible(find.text('I Agree'), 500,
          scrollable: find.byType(Scrollable).last);
      await tester.tap(find.text('I Agree'));
      await tester.pumpAndSettle();
      expect(agreed, true);
      expect(find.text('Agreed'), findsOneWidget);
      expect(find.textContaining('You must open and agree'), findsNothing);
      expect(tester.takeException(), isNull);
      await tester.pumpWidget(const SizedBox.shrink());
    });
  }
}

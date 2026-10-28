import 'package:cartzy/auth/buyer_registration.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  for (final width in [320.0, 360.0, 390.0, 412.0, 600.0]) {
    testWidgets('StepIndicator at width $width spans full available width', (tester) async {
      tester.view.physicalSize = Size(width, 800);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(
            body: Padding(
              padding: EdgeInsets.symmetric(horizontal: 20),
              child: StepIndicator(
                currentStep: 2,
                labels: ['Details', 'Sign-in', 'Verify'],
              ),
            ),
          ),
        ),
      );
      await tester.pumpAndSettle();

      final details = tester.getRect(find.text('Details'));
      final verify = tester.getRect(find.text('Verify'));

      expect(details.left, equals(20.0));
      expect(verify.right, equals(width - 20.0));
      expect(tester.takeException(), isNull);
    });
  }

  for (final scale in [1.0, 1.3, 1.5]) {
    testWidgets('StepIndicator at 320 with text scale $scale passes without overflow', (tester) async {
      tester.view.physicalSize = const Size(320, 760);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(
        MaterialApp(
          builder: (context, child) => MediaQuery(
            data: MediaQuery.of(context).copyWith(textScaler: TextScaler.linear(scale)),
            child: child!,
          ),
          home: const Scaffold(
            body: Padding(
              padding: EdgeInsets.symmetric(horizontal: 20),
              child: StepIndicator(
                currentStep: 1,
                labels: ['Details', 'Sign-in', 'Verify'],
              ),
            ),
          ),
        ),
      );
      await tester.pumpAndSettle();
      expect(tester.takeException(), isNull);
    });
  }
}

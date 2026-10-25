import 'package:cartzy/rider/rider_registration.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

void main() {
  testWidgets('rider details fit a small phone with larger text',
      (tester) async {
    GoogleFonts.config.allowRuntimeFetching = false;
    tester.view.physicalSize = const Size(320, 760);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    await tester.pumpWidget(MaterialApp(
      builder: (context, child) => MediaQuery(
        data: MediaQuery.of(context)
            .copyWith(textScaler: const TextScaler.linear(1.3)),
        child: child!,
      ),
      home: RiderRegistration(
          onRegistrationSubmitted: () {}, onBackToLogin: () {}),
    ));
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);
    final first = tester.getTopLeft(find.byType(TextField).at(0));
    final last = tester.getTopLeft(find.byType(TextField).at(1));
    expect(first.dx, last.dx);
    expect(last.dy, greaterThan(first.dy));
    for (var i = 0; i < 8; i++) {
      await tester.drag(
          find.byType(SingleChildScrollView), const Offset(0, -450));
      await tester.pumpAndSettle();
      expect(tester.takeException(), isNull);
    }
  });
}

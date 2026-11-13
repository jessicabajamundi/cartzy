import 'package:cartzy/rider/rider_account_screen.dart';
import 'package:cartzy/widgets/patch_notes_dialog.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  test('rider notes follow the installed version', () {
    expect(PatchNotesDialog.latestRiderNotes('1.2.13').$1, '1.2.13');
    expect(PatchNotesDialog.latestRiderNotes('v1.2.12').$1, '1.2.12');
    // A newer app with no rider changes still shows the latest rider notes.
    expect(PatchNotesDialog.latestRiderNotes('1.2.20').$1, '1.2.18');
    expect(PatchNotesDialog.latestRiderNotes('1.3.0').$1, '1.2.18');
  });

  test('rider notes leave out buyer-only changes', () {
    final all = PatchNotesDialog.riderNotes.values.expand((notes) => notes).join(' ');
    for (final buyerTopic in ['Cozy', 'Daily Discover', 'wishlist', 'Wishlist']) {
      expect(all.contains(buyerTopic), isFalse, reason: buyerTopic);
    }
  });

  testWidgets('rider profile has a patch notes button that opens the rider notes', (tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.625;
    addTearDown(tester.view.reset);

    await tester.pumpWidget(MaterialApp(home: RiderAccountScreen(userId: 7, onBack: () {})));
    await tester.pump();

    final button = find.text('See patch notes');
    await tester.scrollUntilVisible(button, 300, scrollable: find.byType(Scrollable).first);
    expect(find.text("What's new for riders & couriers"), findsOneWidget);

    await tester.tap(button);
    await tester.pump(const Duration(milliseconds: 400));

    expect(find.textContaining('Google Sign-In Stability', findRichText: true), findsOneWidget);
    expect(find.textContaining('Refined Navigation Bar', findRichText: true), findsOneWidget);
    expect(find.textContaining('Cozy', findRichText: true), findsNothing);
  });
}

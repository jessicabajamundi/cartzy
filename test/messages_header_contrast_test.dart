import 'package:cartzy/buyer/messages_screen.dart';
import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  double contrast(Color foreground, Color background) {
    final a = Color.alphaBlend(foreground, background).computeLuminance();
    final b = background.computeLuminance();
    return a > b ? (a + .05) / (b + .05) : (b + .05) / (a + .05);
  }

  testWidgets('message header remains readable when switching to dark mode',
      (tester) async {
    tester.view.physicalSize = const Size(360, 800);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);
    final mode = ValueNotifier(ThemeMode.light);
    addTearDown(mode.dispose);
    await tester.pumpWidget(ValueListenableBuilder<ThemeMode>(
      valueListenable: mode,
      builder: (context, value, _) => MaterialApp(
        theme: ThemeData.light(),
        darkTheme: ThemeData.dark(),
        themeMode: value,
        home: MessagesScreen(
          onBack: () {},
          initialThreadId: 1,
          initialThreadType: 'order',
          initialStoreName: 'Nike Official Store',
        ),
      ),
    ));
    await tester.pump(const Duration(milliseconds: 300));
    final header = find.byKey(const ValueKey('messages-header'));
    LinearGradient gradient() =>
        (tester.widget<Container>(header).decoration as BoxDecoration).gradient!
            as LinearGradient;
    expect(gradient().colors.first, CartzyColors.brand200);

    mode.value = ThemeMode.dark;
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 300));
    final title = tester.widget<Text>(find.descendant(
        of: header, matching: find.text('Nike Official Store')));
    final subtitle = tester.widget<Text>(
        find.descendant(of: header, matching: find.text('Order conversation')));
    for (final background in gradient().colors) {
      expect(
          contrast(title.style!.color!, background), greaterThanOrEqualTo(4.5));
      expect(contrast(subtitle.style!.color!, background),
          greaterThanOrEqualTo(4.5));
    }
    final back = tester.widget<Icon>(find.descendant(
        of: header, matching: find.byIcon(Icons.arrow_back_ios_new_rounded)));
    expect(contrast(back.color!, CartzyColors.darkSurface),
        greaterThanOrEqualTo(4.5));
    expect(tester.takeException(), isNull);
    await tester.pumpWidget(const SizedBox());
  });
}

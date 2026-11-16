import 'package:cartzy/theme/theme_controller.dart';
import 'package:cartzy/widgets/theme_toggle_button.dart';
import 'package:cartzy/widgets/theme_toggle_tile.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  setUp(() {
    SharedPreferences.setMockInitialValues({});
    ThemeController.mode.value = ThemeMode.system;
  });

  tearDown(() => ThemeController.mode.value = ThemeMode.system);

  Widget app() => ValueListenableBuilder<ThemeMode>(
        valueListenable: ThemeController.mode,
        builder: (context, mode, _) => MaterialApp(
          theme: ThemeData.light(),
          darkTheme: ThemeData.dark(),
          themeMode: mode,
          home: Scaffold(
            appBar: AppBar(actions: const [ThemeToggleButton()]),
          ),
        ),
      );

  testWidgets('button switches both ways and restores the saved preference',
      (tester) async {
    ThemeController.mode.value = ThemeMode.light;
    await tester.pumpWidget(app());

    await tester.tap(find.byTooltip('Switch to dark mode'));
    await tester.pumpAndSettle();
    expect(ThemeController.mode.value, ThemeMode.dark);
    expect(find.byTooltip('Switch to light mode'), findsOneWidget);
    expect(find.byIcon(Icons.light_mode_outlined), findsOneWidget);
    final prefs = await SharedPreferences.getInstance();
    expect(prefs.getString('cartzy_theme_mode'), 'dark');

    ThemeController.mode.value = ThemeMode.system;
    await ThemeController.init();
    expect(ThemeController.mode.value, ThemeMode.dark);

    await tester.tap(find.byTooltip('Switch to light mode'));
    await tester.pumpAndSettle();
    expect(ThemeController.mode.value, ThemeMode.light);
    expect(prefs.getString('cartzy_theme_mode'), 'light');
    expect(find.byTooltip('Switch to dark mode'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  testWidgets(
      'profile and drawer switches stay in sync and save the preference',
      (tester) async {
    ThemeController.mode.value = ThemeMode.light;
    await tester.pumpWidget(ValueListenableBuilder<ThemeMode>(
      valueListenable: ThemeController.mode,
      builder: (context, mode, _) => MaterialApp(
        theme: ThemeData.light(),
        darkTheme: ThemeData.dark(),
        themeMode: mode,
        home: const Scaffold(
          body: Column(children: [ThemeToggleTile(), ThemeToggleTile()]),
        ),
      ),
    ));

    await tester.tap(find.byType(SwitchListTile).first);
    await tester.pumpAndSettle();
    expect(
        tester
            .widgetList<SwitchListTile>(find.byType(SwitchListTile))
            .every((tile) => tile.value),
        isTrue);
    final prefs = await SharedPreferences.getInstance();
    expect(prefs.getString('cartzy_theme_mode'), 'dark');

    await tester.tap(find.byType(SwitchListTile).last);
    await tester.pumpAndSettle();
    expect(
        tester
            .widgetList<SwitchListTile>(find.byType(SwitchListTile))
            .every((tile) => !tile.value),
        isTrue);
    expect(prefs.getString('cartzy_theme_mode'), 'light');
    expect(tester.takeException(), isNull);
  });

  testWidgets('system dark mode switches directly to light mode',
      (tester) async {
    tester.platformDispatcher.platformBrightnessTestValue = Brightness.dark;
    addTearDown(tester.platformDispatcher.clearPlatformBrightnessTestValue);
    await tester.pumpWidget(app());
    expect(find.byTooltip('Switch to light mode'), findsOneWidget);

    await tester.tap(find.byTooltip('Switch to light mode'));
    await tester.pumpAndSettle();
    expect(ThemeController.mode.value, ThemeMode.light);
    expect(find.byTooltip('Switch to dark mode'), findsOneWidget);
  });
}

import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/product_card.dart';
import 'package:cartzy/widgets/write_review_dialog.dart';
import 'package:cartzy/widgets/inline_quantity_field.dart';
import 'package:cartzy/theme/cartzy_theme.dart';
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

  testWidgets('dark text remains readable on surfaces and bright badges',
      (tester) async {
    final colors = [
      CartzyColors.navy,
      CartzyColors.rose700,
      CartzyColors.error,
      const Color(0xFF21845A),
      const Color(0xFF4269BD),
      Colors.white.withValues(alpha: .35),
    ];
    final backgrounds = [
      CartzyColors.darkSurface,
      CartzyColors.darkSurfaceElevated,
      const Color(0xFF7A7A7A),
      const Color(0xFFD4A69A),
      const Color(0xFFFFF5F3),
    ];
    for (final brightness in [Brightness.dark, Brightness.light]) {
      await tester.pumpWidget(MaterialApp(
        key: ValueKey(brightness),
        theme: ThemeData(brightness: brightness),
        home: Builder(builder: (context) {
          for (final color in colors) {
            for (final background in backgrounds) {
              final resolved = CartzyColors.readableOf(context,
                  light: color, background: background);
              if (brightness == Brightness.dark) {
                expect(
                    contrast(resolved, background), greaterThanOrEqualTo(4.5));
              } else {
                expect(resolved, color);
              }
            }
          }
          return const SizedBox();
        }),
      ));
    }
  });

  test('dark theme primary and error labels meet text contrast', () {
    const scheme = CartzyTheme.darkColorScheme;
    expect(
        contrast(scheme.onPrimary, scheme.primary), greaterThanOrEqualTo(4.5));
    expect(contrast(scheme.onError, scheme.error), greaterThanOrEqualTo(4.5));
    expect(contrast(scheme.primary, scheme.surface), greaterThanOrEqualTo(4.5));
  });

  testWidgets('quantity stays readable while focused in dark mode',
      (tester) async {
    await tester.pumpWidget(MaterialApp(
      theme: ThemeData.dark(),
      home: Scaffold(
          body: InlineQuantityField(
        quantity: 3,
        onCommitted: (_) {},
        style: const TextStyle(color: CartzyColors.navy, fontSize: 14),
        decoration: const BoxDecoration(color: Colors.white),
      )),
    ));
    final unfocusedField = tester.widget<TextField>(find.byType(TextField));
    expect(contrast(unfocusedField.style!.color!, Colors.white),
        greaterThanOrEqualTo(4.5));
    await tester.tap(find.byType(TextField));
    await tester.pumpAndSettle();
    final container = tester.widget<AnimatedContainer>(find.descendant(
      of: find.byType(InlineQuantityField),
      matching: find.byType(AnimatedContainer),
    ));
    final background = (container.decoration as BoxDecoration).color!;
    final field = tester.widget<TextField>(find.byType(TextField));
    expect(background, CartzyColors.darkSurfaceElevated);
    expect(
        contrast(field.style!.color!, background), greaterThanOrEqualTo(4.5));
    expect(tester.takeException(), isNull);
  });

  testWidgets('product surfaces update when the theme changes', (tester) async {
    final mode = ValueNotifier(ThemeMode.light);
    addTearDown(mode.dispose);
    await tester.pumpWidget(ValueListenableBuilder<ThemeMode>(
      valueListenable: mode,
      builder: (context, value, child) => MaterialApp(
        theme: ThemeData.light(),
        darkTheme: ThemeData.dark(),
        themeMode: value,
        home: Scaffold(
          body: Center(
            child: SizedBox(
              width: 180,
              height: 400,
              child: ProductCard(
                name: 'Test product',
                price: '100',
                imageUrl: '',
                onTap: () {},
              ),
            ),
          ),
        ),
      ),
    ));

    Material card() => tester.widget<Material>(find
        .descendant(
            of: find.byType(ProductCard), matching: find.byType(Material))
        .first);
    ColoredBox photo() => tester.widget<ColoredBox>(find
        .descendant(
            of: find.byType(ProductCard), matching: find.byType(ColoredBox))
        .first);

    expect(card().color, Colors.white);
    expect(photo().color, const Color(0xFFF6F3F5));
    mode.value = ThemeMode.dark;
    await tester.pumpAndSettle();
    expect(card().color, CartzyColors.darkSurface);
    expect(photo().color, CartzyColors.darkSurfaceElevated);
    mode.value = ThemeMode.light;
    await tester.pumpAndSettle();
    expect(card().color, Colors.white);
    expect(photo().color, const Color(0xFFF6F3F5));
  });

  testWidgets('review sheet uses the dark surface', (tester) async {
    await tester.pumpWidget(MaterialApp(
      theme: ThemeData.dark(),
      home:
          const Scaffold(body: WriteReviewDialog(productName: 'Test product')),
    ));
    final surface = tester.widget<Container>(find
        .descendant(
          of: find.byType(WriteReviewDialog),
          matching: find.byType(Container),
        )
        .first);
    expect(
        (surface.decoration as BoxDecoration).color, CartzyColors.darkSurface);
    expect(tester.takeException(), isNull);
  });
}

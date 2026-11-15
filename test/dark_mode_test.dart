import 'package:cartzy/theme/cartzy_colors.dart';
import 'package:cartzy/widgets/product_card.dart';
import 'package:cartzy/widgets/write_review_dialog.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

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

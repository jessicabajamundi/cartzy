import 'package:cartzy/products/product_list_screen.dart';
import 'package:cartzy/services/catalog_service.dart';
import 'package:cartzy/widgets/home_sections.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

ProductItem _deal(String name, {DateTime? endsAt}) => ProductItem(
      name: name,
      category: 'General',
      price: '₱900',
      originalPrice: '₱1,000',
      discountPercent: 10,
      badge: '',
      soldCount: '0 sold',
      rating: null,
      saleEndsAt: endsAt,
    );

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  Future<void> pumpSection(WidgetTester tester, List<ProductItem> deals) async {
    await tester.pumpWidget(MaterialApp(
      home: Scaffold(
        body: FlashDealsSection(loadDeals: () async => deals),
      ),
    ));
    await tester.pump();
  }

  // The countdown is the only place these exact block texts appear.
  bool hasColon() => find.text(':').evaluate().isNotEmpty;

  testWidgets('counts down to the real end time, with days when far away', (tester) async {
    final end = DateTime.now().add(const Duration(days: 13, hours: 2, minutes: 3, seconds: 30));
    await pumpSection(tester, [_deal('Duffel Bag', endsAt: end)]);

    expect(find.text('13d'), findsOneWidget);
    expect(find.text('02'), findsOneWidget);
    expect(find.text('03'), findsOneWidget);
    expect(find.text('Duffel Bag'), findsOneWidget);
    await tester.pumpWidget(const SizedBox());
  });

  testWidgets('hides days under 24 hours', (tester) async {
    final end = DateTime.now().add(const Duration(hours: 5, minutes: 7, seconds: 30));
    await pumpSection(tester, [_deal('Mug', endsAt: end)]);

    expect(find.text('0d'), findsNothing);
    expect(find.text('05'), findsOneWidget);
    expect(find.text('07'), findsOneWidget);
    await tester.pumpWidget(const SizedBox());
  });

  testWidgets('follows the deal that ends soonest', (tester) async {
    final now = DateTime.now();
    await pumpSection(tester, [
      _deal('Later', endsAt: now.add(const Duration(days: 9))),
      _deal('Sooner', endsAt: now.add(const Duration(hours: 4, minutes: 11, seconds: 30))),
    ]);

    expect(find.text('9d'), findsNothing);
    expect(find.text('04'), findsOneWidget);
    expect(find.text('11'), findsOneWidget);
    await tester.pumpWidget(const SizedBox());
  });

  testWidgets('shows no timer and no deals when nothing is on sale', (tester) async {
    await pumpSection(tester, []);

    expect(hasColon(), isFalse);
    expect(find.text('No flash deals at the moment'), findsOneWidget);
    await tester.pumpWidget(const SizedBox());
  });

  testWidgets('shows no made-up timer for a sale without an end date', (tester) async {
    await pumpSection(tester, [_deal('Open sale')]);

    expect(hasColon(), isFalse);
    expect(find.text('Open sale'), findsOneWidget);
    await tester.pumpWidget(const SizedBox());
  });

  testWidgets('drops a sale that has already ended', (tester) async {
    await pumpSection(tester, [
      _deal('Ended', endsAt: DateTime.now().subtract(const Duration(minutes: 1))),
    ]);

    expect(find.text('Ended'), findsNothing);
    expect(hasColon(), isFalse);
    expect(find.text('No flash deals at the moment'), findsOneWidget);
    await tester.pumpWidget(const SizedBox());
  });

  test('reads the end time as an exact moment, whatever the timezone', () {
    final item = CatalogService.productFromJson({
      'name': 'Bag',
      'price': 900,
      'regular_price': 1000,
      'sale_ends_at': '2026-11-20T12:59:00+00:00',
    });
    expect(item.saleEndsAt?.toUtc(), DateTime.utc(2026, 11, 20, 12, 59));

    final regular = CatalogService.productFromJson({'name': 'Mug', 'price': 900, 'sale_ends_at': null});
    expect(regular.saleEndsAt, isNull);
  });
}

import 'package:cartzy/widgets/product_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:google_fonts/google_fonts.dart';

void main() {
  GoogleFonts.config.allowRuntimeFetching = false;

  final cards = <String, ProductCard Function(VoidCallback onAdd)>{
    'discounted, low stock': (onAdd) => ProductCard(
          name: 'Wireless Noise Cancelling Over-Ear Headphones with Extra Long Battery Life',
          price: '₱12,499.00',
          originalPrice: '₱18,999.00',
          discountPercent: 34,
          badge: 'Official',
          rating: 4.9,
          soldCount: '12.4k sold',
          sellerName: 'A Store With A Really Long Official Shop Name',
          stock: 3,
          imageUrl: '',
          onTap: () {},
          onAddToCart: onAdd,
        ),
    'sold out': (onAdd) => ProductCard(
          name: 'Mug',
          price: '₱99',
          inStock: false,
          hasFreeShipping: false,
          onTap: () {},
          onAddToCart: onAdd,
        ),
  };

  // Daily Discover cards are 155 wide; the Sale grid is two columns on a phone.
  for (final width in [155.0, (320 - 42) / 2, (414 - 42) / 2]) {
    for (final scale in [1.0, 1.3]) {
      for (final entry in cards.entries) {
        testWidgets('${entry.key} fits at width $width, text x$scale', (tester) async {
          var added = 0;
          await tester.pumpWidget(MaterialApp(
            home: MediaQuery(
              data: MediaQueryData(textScaler: TextScaler.linear(scale)),
              child: Builder(
                builder: (context) => Center(
                  child: SizedBox(
                    width: width,
                    height: width + ProductCard.bodyHeight(context),
                    child: entry.value(() => added++),
                  ),
                ),
              ),
            ),
          ));
          await tester.pump();
          expect(tester.takeException(), isNull);

          // The round cart button is the last control on the card.
          await tester.tap(find.bySemanticsLabel(RegExp(r'^(Add to cart|Sold out)$')).last);
          expect(added, entry.key == 'sold out' ? 0 : 1);
        });
      }
    }
  }

  testWidgets('shows discount, stock and price details', (tester) async {
    await tester.pumpWidget(MaterialApp(
      home: Center(
        child: SizedBox(
          width: 170,
          height: 320,
          child: cards['discounted, low stock']!(() {}),
        ),
      ),
    ));
    expect(find.text('-34%'), findsOneWidget);
    expect(find.text('Only 3 left'), findsOneWidget);
    expect(find.text('₱18,999.00'), findsOneWidget);
    expect(find.text('₱12,499.00', findRichText: true), findsOneWidget);
    expect(find.byIcon(Icons.verified_rounded), findsOneWidget);
    expect(find.byIcon(Icons.local_shipping_outlined), findsOneWidget);

    await tester.tap(find.byIcon(Icons.favorite_border_rounded));
    await tester.pump();
    expect(find.byIcon(Icons.favorite_rounded), findsOneWidget);
  });
}

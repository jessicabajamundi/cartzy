import 'package:cartzy/services/cart_service.dart';
import 'package:cartzy/widgets/inline_quantity_field.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    SharedPreferences.setMockInitialValues({});
    CartService.instance.clearAll();
  });

  test('setQuantity clamps between 1 and available stock', () async {
    final cart = CartService.instance;
    cart.addItem(
      name: 'Nike Dunk Low SE',
      price: 6995,
      quantity: 1,
      stock: 65,
    );

    expect(cart.items.length, 1);
    expect(cart.items.first.quantity, 1);

    // Set valid quantity
    final set1 = cart.setQuantity(0, 10);
    expect(set1, 10);
    expect(cart.items.first.quantity, 10);

    // Set quantity exceeding stock limit (65)
    final set2 = cart.setQuantity(0, 100);
    expect(set2, 65);
    expect(cart.items.first.quantity, 65);

    // Set quantity below 1 (e.g. 0 or negative)
    final set3 = cart.setQuantity(0, -5);
    expect(set3, 1);
    expect(cart.items.first.quantity, 1);
  });

  test('cart counter counts number of products, not total quantity', () {
    final cart = CartService.instance;
    cart.addItem(
      name: 'Nike Dunk Low SE',
      price: 6995,
      quantity: 65,
    );
    cart.addItem(
      name: 'Air Force 1',
      price: 5495,
      quantity: 10,
    );

    // There are 2 distinct products in the cart, total units is 75
    expect(cart.items.length, 2);
    expect(cart.totalItemCount, 2);
    expect(cart.selectedItemCount, 2);
    expect(cart.totalUnits, 75);
  });

  testWidgets('InlineQuantityField opens keyboard directly without any dialog popup', (tester) async {
    int committedValue = 0;

    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: Center(
            child: InlineQuantityField(
              quantity: 5,
              onCommitted: (val) => committedValue = val,
              style: const TextStyle(fontSize: 14),
            ),
          ),
        ),
      ),
    );

    // Initial state: shows "5" in TextField
    expect(find.text('5'), findsOneWidget);
    expect(find.byType(AlertDialog), findsNothing);
    expect(find.byType(Dialog), findsNothing);

    // Tap the quantity field directly
    await tester.tap(find.byType(InlineQuantityField));
    await tester.pumpAndSettle();

    // Verify still NO dialog or modal opened
    expect(find.byType(AlertDialog), findsNothing);
    expect(find.byType(Dialog), findsNothing);

    // Enter a new quantity
    await tester.enterText(find.byType(TextField), '25');
    await tester.testTextInput.receiveAction(TextInputAction.done);
    await tester.pumpAndSettle();

    // Verify committed without modal
    expect(committedValue, 25);
    expect(find.byType(AlertDialog), findsNothing);
    expect(find.byType(Dialog), findsNothing);
  });
}


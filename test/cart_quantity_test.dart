import 'package:cartzy/services/cart_service.dart';
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
}


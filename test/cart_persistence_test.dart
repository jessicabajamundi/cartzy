import 'package:cartzy/services/cart_service.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  test('cart starts empty and saves changes to the device', () async {
    SharedPreferences.setMockInitialValues({});
    final cart = CartService.instance;
    await Future<void>.delayed(Duration.zero); // let the saved cart load

    expect(cart.items, isEmpty);

    cart.addItem(name: 'Air Fryer', price: '₱2,435', category: 'Appliances');
    cart.addItem(name: 'Meat Grinder', price: 1346);
    cart.removeItem(0);
    await Future<void>.delayed(Duration.zero);

    final saved = (await SharedPreferences.getInstance()).getString('cart_items');
    expect(saved, contains('Meat Grinder'));
    expect(saved, isNot(contains('Air Fryer')));
  });
}

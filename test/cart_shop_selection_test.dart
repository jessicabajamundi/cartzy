import 'dart:convert';

import 'package:cartzy/services/cart_service.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  SharedPreferences.setMockInitialValues({});
  final cart = CartService.instance;

  setUp(() async {
    SharedPreferences.setMockInitialValues({});
    await Future<void>.delayed(Duration.zero);
    cart.clearAll();
    for (final item in [
      CartItem(
          id: 'a',
          name: 'Black watch',
          price: 100,
          store: 'Watch Shop',
          sellerId: 10,
          selected: false),
      CartItem(
          id: 'b',
          name: 'White watch',
          price: 200,
          store: 'Watch Shop',
          sellerId: 20,
          selected: false),
      CartItem(
          id: 'c',
          name: 'Blue watch',
          price: 300,
          store: 'Watch Shop',
          sellerId: 10,
          selected: false),
      CartItem(
          id: 'd',
          name: 'Sold out',
          price: 400,
          store: 'Watch Shop',
          sellerId: 10,
          stock: 0,
          selected: false),
    ]) {
      cart.insertItem(cart.items.length, item);
    }
  });

  test('shop selection isolates sellers and updates totals and partial states',
      () {
    expect(cart.shopGroups.keys, ['seller:10', 'seller:20']);
    expect(cart.shopGroups['seller:10']!.map((i) => i.id), ['a', 'c', 'd']);
    cart.toggleShopSelection('seller:10', true);
    expect(cart.selectedItems.map((i) => i.id), ['a', 'c']);
    expect(cart.selectedTotal, 400);
    expect(cart.shopSelectionState('seller:10'), true);
    expect(cart.shopSelectionState('seller:20'), false);
    expect(cart.selectAllState, null);

    cart.toggleItemSelection(0, false);
    expect(cart.shopSelectionState('seller:10'), null);
    expect(cart.selectedTotal, 300);

    cart.toggleSelectAll(true);
    expect(cart.shopSelectionState('seller:10'), true);
    expect(cart.shopSelectionState('seller:20'), true);
    expect(cart.items.last.selected, false);
    cart.toggleShopSelection('seller:10', false);
    expect(cart.selectedItems.single.id, 'b');
    cart.toggleSelectAll(false);
    expect(cart.selectedItems, isEmpty);
    expect(cart.selectAllState, false);
  });

  test('shop identity and selection survive device serialization', () async {
    cart.toggleShopSelection('seller:10', true);
    await Future<void>.delayed(Duration.zero);
    final prefs = await SharedPreferences.getInstance();
    final saved = (jsonDecode(prefs.getString('cart_items')!) as List)
        .map(
            (json) => CartItem.fromJson(Map<String, dynamic>.from(json as Map)))
        .toList();
    expect(saved.map((i) => i.shopKey),
        ['seller:10', 'seller:20', 'seller:10', 'seller:10']);
    expect(saved.map((i) => i.selected), [true, false, true, false]);
    expect(CartItem.fromJson({'seller_id': 50}).shopKey, 'seller:50');
  });

  test('removing products updates shop groups and selection state', () {
    cart.toggleShopSelection('seller:10', true);
    cart.removeItemById('a');
    expect(cart.shopSelectionState('seller:10'), true);
    cart.removeItemById('c');
    expect(cart.shopSelectionState('seller:10'), false);
    cart.removeItemById('d');
    expect(cart.shopGroups.containsKey('seller:10'), false);
    expect(cart.shopSelectionState('missing'), false);
  });

  test('legacy products use store name with a default for missing shops', () {
    expect(
        CartItem(id: 'x', name: 'X', price: 1).shopKey, 'store:Cartzy Store');
    expect(
        CartItem(id: 'y', name: 'Y', price: 1, store: ' Local Shop ').shopKey,
        'store:Local Shop');
  });
}

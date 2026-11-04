import 'package:cartzy/widgets/warehouse_note.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('warehouse shipping applies only above 100 pieces', () {
    expect(WarehouseShipping.applies(1), isFalse);
    expect(WarehouseShipping.applies(100), isFalse);
    expect(WarehouseShipping.applies(101), isTrue);
    expect(WarehouseShipping.applies(500), isTrue);
  });

  testWidgets('note tells the buyer it ships from the seller warehouse',
      (tester) async {
    await tester.pumpWidget(const MaterialApp(
      home: Scaffold(body: WarehouseShippingNote()),
    ));

    expect(find.textContaining("seller's warehouse"), findsOneWidget);
    expect(find.byIcon(Icons.warehouse_outlined), findsOneWidget);
  });
}

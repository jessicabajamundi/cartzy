import 'package:flutter_test/flutter_test.dart';
import 'package:cartzy/main.dart';

void main() {
  testWidgets('Cartzy app loads successfully', (WidgetTester tester) async {
    await tester.pumpWidget(const CartzyMaterialApp());

    expect(find.text('Cartzy'), findsNothing);
  });
}
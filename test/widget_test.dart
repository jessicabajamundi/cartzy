import 'package:flutter_test/flutter_test.dart';
import 'package:cartzy/main.dart';

void main() {
  testWidgets('Cartzy app loads', (WidgetTester tester) async {
    await tester.pumpWidget(const CartzyApp());

    await tester.pump();

    expect(find.byType(CartzyApp), findsOneWidget);
  });
}
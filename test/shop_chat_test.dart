import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:cartzy/buyer/messages_screen.dart';
import 'package:cartzy/services/auth_service.dart';

void main() {
  setUp(() {
    TestWidgetsFlutterBinding.ensureInitialized();
    SharedPreferences.setMockInitialValues({});
    AuthService.setSession(
      user: {'id': 1, 'name': 'Test Buyer', 'email': 'buyer@test.com'},
      token: 'fake-token',
    );
  });

  testWidgets('MessagesScreen directly opens shop chat when initialStoreName is provided', (tester) async {
    tester.view.physicalSize = const Size(360, 640);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.reset);

    await tester.pumpWidget(
      MaterialApp(
        home: MessagesScreen(
          onBack: () {},
          initialStoreName: 'Bella Chic Boutique',
          initialProductName: 'Floral Summer Dress',
        ),
      ),
    );

    // Initial frame renders immediately with the shop chat open
    await tester.pump(const Duration(milliseconds: 100));

    // Must show shop name in chat header
    expect(find.text('Bella Chic Boutique'), findsWidgets);
    // Must show product context banner and header
    expect(find.text('Floral Summer Dress'), findsWidgets);
    // Must show message reply composer specifically for this shop
    expect(find.text('Type a message to Bella Chic Boutique...'), findsOneWidget);
    // Should NOT show the conversation list title "Store Conversations" on mobile
    expect(find.text('Store Conversations'), findsNothing);
  });
}

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:cartzy/buyer/change_password_screen.dart';
import 'package:cartzy/services/auth_service.dart';
import 'package:cartzy/services/user_account_service.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    SharedPreferences.setMockInitialValues({});
    AuthService.clearSession();
  });

  testWidgets('ChangePasswordScreen renders inputs and checklist', (tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: ChangePasswordScreen(),
      ),
    );

    expect(find.text('Change Password'), findsOneWidget);
    expect(find.text('Current Password'), findsOneWidget);
    expect(find.text('New Password'), findsOneWidget);
    expect(find.text('Confirm New Password'), findsOneWidget);
    expect(find.text('Update Password'), findsOneWidget);
    expect(find.text('Password requirements:'), findsOneWidget);
  });

  testWidgets('ChangePasswordScreen validates mismatched passwords', (tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: ChangePasswordScreen(),
      ),
    );

    // Enter current password
    await tester.enterText(find.byType(TextField).at(0), 'OldPass@123');
    // Enter new password
    await tester.enterText(find.byType(TextField).at(1), 'NewPass@1234');
    // Enter mismatched confirm password
    await tester.enterText(find.byType(TextField).at(2), 'Different@1234');
    await tester.pumpAndSettle();

    expect(find.text('Passwords do not match'), findsOneWidget);

    // Tap Update Password
    final buttonFinder = find.text('Update Password');
    await tester.ensureVisible(buttonFinder);
    await tester.tap(buttonFinder);
    await tester.pumpAndSettle();

    expect(find.text('New password and confirmation do not match.'), findsOneWidget);
  });

  testWidgets('ChangePasswordScreen updates password successfully in demo mode', (tester) async {
    // Set logged in user
    AuthService.setSession(user: {
      'id': 1,
      'email': 'buyer@cartzy.com',
      'name': 'Demo Buyer',
      'role': 'buyer',
    });

    await tester.pumpWidget(
      const MaterialApp(
        home: ChangePasswordScreen(),
      ),
    );

    // Default seeded password for buyer@cartzy.com is '123456'
    await tester.enterText(find.byType(TextField).at(0), '123456');
    await tester.enterText(find.byType(TextField).at(1), 'SecurePass@2026');
    await tester.enterText(find.byType(TextField).at(2), 'SecurePass@2026');
    await tester.pumpAndSettle();

    expect(find.text('Passwords match'), findsOneWidget);

    final buttonFinder = find.text('Update Password');
    await tester.ensureVisible(buttonFinder);
    await tester.tap(buttonFinder);
    await tester.pumpAndSettle();

    // Check account was updated
    final account = UserAccountService.instance.findAccountByEmail('buyer@cartzy.com');
    expect(account?.password, 'SecurePass@2026');
  });
}

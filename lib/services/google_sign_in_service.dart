import 'package:google_sign_in/google_sign_in.dart';

import 'auth_service.dart';

class GoogleSignInService {
  static final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: ['email', 'profile'],
    serverClientId: '460041836799-5np8c0dfg22jjr7sav3cd82uousu9eaq.apps.googleusercontent.com',
  );

  /// Triggers native Google Sign-In and authenticates with the Cartzy Laravel API.
  /// Clears any cached session first so the user is always prompted with the account picker
  /// to choose from any Google account signed in on their device.
  static Future<Map<String, dynamic>?> signIn() async {
    try {
      // Clear previously selected account so the account chooser dialog always opens
      try {
        await _googleSignIn.signOut();
      } catch (_) {}

      final GoogleSignInAccount? account = await _googleSignIn.signIn();
      if (account == null) {
        // User dismissed the Google account picker dialog
        return null;
      }

      return await AuthService.loginWithGoogle(
        email: account.email,
        name: account.displayName,
        googleId: account.id,
        avatar: account.photoUrl,
      );
    } catch (e) {
      rethrow;
    }
  }

  static Future<void> signOut() async {
    try {
      await _googleSignIn.signOut();
    } catch (_) {}
  }
}

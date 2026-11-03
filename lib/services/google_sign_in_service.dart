import 'package:flutter/services.dart';
import 'package:google_sign_in/google_sign_in.dart';

import 'auth_service.dart';

class GoogleSignInService {
  // Standard GoogleSignIn without cross-project serverClientId mismatch.
  // Google Play Services on Android authenticates directly with package name
  // com.example.cartzy and the SHA-1 signing fingerprint.
  static final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: ['email', 'profile'],
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
      );
    } on PlatformException catch (e) {
      final friendly = getReadableErrorMessage(e);
      if (friendly == null) return null; // user dismissed
      throw Exception(friendly);
    } catch (e) {
      final friendly = getReadableErrorMessage(e);
      if (friendly == null) return null;
      throw Exception(friendly);
    }
  }

  /// Translates raw Google Play Services error codes (e.g. "PlatformException(network_error, N1.d: 7: , null, null)")
  /// into clear, actionable messages.
  static String? getReadableErrorMessage(dynamic error) {
    if (error == null) return null;

    final str = error.toString().toLowerCase();

    // User dismissed or canceled
    if (str.contains('sign_in_canceled') ||
        str.contains('canceled') ||
        str.contains('cancelled') ||
        str.contains('12501')) {
      return null;
    }

    // Network / Google Play Services connection refused (Code 7)
    if (str.contains('network_error') ||
        str.contains(': 7:') ||
        str.contains('status: 7') ||
        str.contains('code: 7')) {
      return 'Hindi makakonekta sa Google. Pakisuri ang iyong internet o tiyaking naka-register ang SHA-1 fingerprint sa Firebase/Google Cloud.';
    }

    // Developer / Configuration error (Code 10)
    if (str.contains('developer_error') ||
        str.contains(': 10:') ||
        str.contains('status: 10')) {
      return 'Google Sign-In configuration error: Kailangang idagdag ang SHA-1 fingerprint ng app sa Firebase o Google Cloud Console.';
    }

    return 'Google Sign-In: ${error.toString().replaceFirst('Exception: ', '')}';
  }

  static Future<void> signOut() async {
    try {
      await _googleSignIn.signOut();
    } catch (_) {}
  }
}

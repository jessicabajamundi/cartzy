import 'package:flutter/services.dart';
import 'package:google_sign_in/google_sign_in.dart';

import 'auth_service.dart';

class GoogleSignInService {
  /// App Package Name on Android
  static const String packageName = 'com.example.cartzy';

  /// Permanent Release Keystore SHA-1 (used by GitHub Actions & Release builds)
  static const String releaseSha1 =
      '20:84:2C:D9:D0:4E:43:52:84:6C:91:AB:95:DE:32:19:8E:CF:CC:A7';

  /// Local Development Keystore SHA-1 (used when running locally via flutter run)
  static const String debugSha1 =
      'CF:2C:DE:F2:2A:B6:AB:F8:63:C7:B0:F7:61:90:88:49:0D:45:0B:68';

  /// Web Client ID from Google Cloud project (460041836799)
  static const String webClientId =
      '460041836799-5np8c0dfg22jjr7sav3cd82uousu9eaq.apps.googleusercontent.com';

  static final GoogleSignIn _googleSignInWithServer = GoogleSignIn(
    scopes: ['email', 'profile'],
    serverClientId: webClientId,
  );

  static final GoogleSignIn _googleSignInDirect = GoogleSignIn(
    scopes: ['email', 'profile'],
  );

  /// Triggers native Google Sign-In and authenticates with the Cartzy Laravel API.
  /// Clears any cached session first so the user is always prompted with the account picker.
  /// Automatically tries with serverClientId first, then falls back to direct client if
  /// a project client ID mismatch occurs.
  static Future<Map<String, dynamic>?> signIn() async {
    try {
      try {
        await _googleSignInWithServer.signOut();
      } catch (_) {}

      GoogleSignInAccount? account;
      try {
        account = await _googleSignInWithServer.signIn();
      } on PlatformException catch (e) {
        final str = e.toString().toLowerCase();
        // If developer_error (Code 10) occurs with serverClientId, retry with direct client
        if (str.contains('developer_error') ||
            str.contains(': 10:') ||
            str.contains('status: 10')) {
          try {
            await _googleSignInDirect.signOut();
          } catch (_) {}
          account = await _googleSignInDirect.signIn();
        } else {
          rethrow;
        }
      }

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

  /// Translates raw Google Play Services error codes into clear, actionable messages.
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
      return 'Hindi makakonekta sa Google. Pakisuri ang iyong internet connection.';
    }

    // Developer / Configuration error (Code 10)
    if (str.contains('developer_error') ||
        str.contains(': 10:') ||
        str.contains('status: 10')) {
      return 'Google Sign-In: Kailangang idagdag ang SHA-1 fingerprint sa Google Cloud Console:\n$releaseSha1 (Package: $packageName)';
    }

    return 'Google Sign-In: ${error.toString().replaceFirst('Exception: ', '')}';
  }

  static Future<void> signOut() async {
    try {
      await _googleSignInWithServer.signOut();
    } catch (_) {}
    try {
      await _googleSignInDirect.signOut();
    } catch (_) {}
  }
}

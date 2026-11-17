import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';

class WalletService {
  static Future<Map<String, dynamic>> fetch() async =>
      Map<String, dynamic>.from(await ApiService.get('/wallet', token: AuthService.token));

  static String money(num minor) => '₱${(minor / 100).toStringAsFixed(2)}';
}

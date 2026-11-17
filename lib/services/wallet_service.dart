import 'package:cartzy/services/api_service.dart';
import 'package:cartzy/services/auth_service.dart';

class WalletService {
  static Future<Map<String, dynamic>> fetch() async =>
      Map<String, dynamic>.from(await ApiService.get('/wallet', token: AuthService.token));

  static Future<List<Map<String, dynamic>>> fetchPendingRefunds() async {
    if ((AuthService.token ?? '').isEmpty) return const [];
    final res = await ApiService.get('/returns', token: AuthService.token);
    final raw = (res['data'] as List?) ?? const [];
    const terminal = {'refunded', 'rejected', 'cancelled', 'closed'};
    return raw
        .whereType<Map>()
        .map((e) => Map<String, dynamic>.from(e))
        .where((e) => !terminal.contains((e['status'] ?? '').toString().toLowerCase()))
        .toList();
  }

  static String money(num minor) => '₱${(minor / 100).toStringAsFixed(2)}';
}

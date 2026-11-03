import 'package:flutter_test/flutter_test.dart';

import 'package:cartzy/buyer/account_activity_screen.dart';

void main() {
  test('activity entries parse the API shape and tolerate missing fields', () {
    final entry = ActivityEntry.fromJson({
      'event': 'login',
      'group': 'security',
      'title': 'Signed in',
      'description': 'With password',
      'channel': 'mobile',
      'device': 'Cartzy app on Android',
      'ip_address': '127.0.0.1',
      'created_at': '2026-11-03T08:30:00+00:00',
    });

    expect(entry.title, 'Signed in');
    expect(entry.channel, 'mobile');
    expect(entry.createdAt.toUtc(), DateTime.utc(2026, 11, 3, 8, 30));

    final sparse = ActivityEntry.fromJson({'event': 'profile_updated'});
    expect(sparse.group, 'account');
    expect(sparse.channel, 'web');
    expect(sparse.description, isNull);
    expect(sparse.ipAddress, isNull);
  });
}

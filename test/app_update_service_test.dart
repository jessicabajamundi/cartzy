import 'package:flutter_test/flutter_test.dart';
import 'package:cartzy/services/app_update_service.dart';

void main() {
  group('AppUpdateService Version Parsing & Comparison', () {
    test('correctly parses semantic versions with tags and build numbers', () {
      expect(AppUpdateService.parseVersionParts('1.2.18'), [1, 2, 18, 0]);
      expect(AppUpdateService.parseVersionParts('v1.2.18'), [1, 2, 18, 0]);
      expect(AppUpdateService.parseVersionParts('V1.2.18'), [1, 2, 18, 0]);
      expect(AppUpdateService.parseVersionParts('1.2.18+29'), [1, 2, 18, 29]);
      expect(AppUpdateService.parseVersionParts('v1.2.18+29'), [1, 2, 18, 29]);
      expect(AppUpdateService.parseVersionParts('1.2.18-patch1'), [1, 2, 18, 0]);
      expect(AppUpdateService.parseVersionParts(''), [0, 0, 0, 0]);
    });

    test('does NOT trigger update when current app is on the latest version', () {
      // Current installed is 1.2.18 (or 1.2.18+29) and remote is 1.2.18
      final pRemote = AppUpdateService.parseVersionParts('v1.2.18');
      final pCurrentClean = AppUpdateService.parseVersionParts('1.2.18');
      final pCurrentWithBuild = AppUpdateService.parseVersionParts('1.2.18+29');

      bool isNewer(List<int> r, List<int> c) {
        for (var i = 0; i < 3; i++) {
          if (r[i] > c[i]) return true;
          if (r[i] < c[i]) return false;
        }
        if (r[3] > 0 && c[3] > 0) return r[3] > c[3];
        return false;
      }

      // Latest GitHub release is 1.2.18
      expect(isNewer(pRemote, pCurrentClean), isFalse);
      expect(isNewer(pRemote, pCurrentWithBuild), isFalse);

      // If current is ahead of remote
      final pCurrentAhead = AppUpdateService.parseVersionParts('1.2.19');
      expect(isNewer(pRemote, pCurrentAhead), isFalse);

      // When remote is genuinely newer (e.g., 1.2.19 available)
      final pRemoteNewer = AppUpdateService.parseVersionParts('v1.2.19');
      expect(isNewer(pRemoteNewer, pCurrentClean), isTrue);
      expect(isNewer(pRemoteNewer, pCurrentWithBuild), isTrue);

      // When remote has higher build number
      final pRemoteHigherBuild = AppUpdateService.parseVersionParts('v1.2.18+35');
      expect(isNewer(pRemoteHigherBuild, pCurrentWithBuild), isTrue);
    });
  });
}


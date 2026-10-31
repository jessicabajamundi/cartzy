import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:cartzy/widgets/patch_notes_dialog.dart';

class AppUpdateService {
  /// The current app release version.
  static const String currentAppVersion = '1.1.3';

  /// GitHub repository release endpoint for Cartzy.
  static const String latestReleaseApi =
      'https://api.github.com/repos/jessicabajamundi/cartzy/releases/latest';

  static const String defaultReleasePage =
      'https://github.com/jessicabajamundi/cartzy/releases/tag/v1.1.3';

  static const String _keyLastSeenPatch = 'cartzy_last_seen_patch_version';

  /// Runs once on startup or when navigating to the main screen.
  /// Checks for both remote newer updates and local patch notes.
  static Future<void> checkOnAppStart(BuildContext context) async {
    // 1. First, check if there's a newer version published on GitHub
    final remoteUpdateFound = await _checkRemoteUpdate(context);
    if (remoteUpdateFound) return;

    // 2. If no newer version needs to be downloaded, check if the user has
    // already seen the patch notes for the current installed version.
    if (!context.mounted) return;
    await checkLocalPatchNotes(context);
  }

  /// Checks if the current version's patch notes were already seen by the user.
  /// If not, pops up the Patch Notes dialog and records it.
  static Future<bool> checkLocalPatchNotes(BuildContext context,
      {bool forceShow = false}) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final lastSeen = prefs.getString(_keyLastSeenPatch);

      if (forceShow || lastSeen != currentAppVersion) {
        if (!context.mounted) return false;

        await PatchNotesDialog.showPatchNotes(
          context,
          version: currentAppVersion,
          onDismiss: () async {
            final p = await SharedPreferences.getInstance();
            await p.setString(_keyLastSeenPatch, currentAppVersion);
          },
        );

        await prefs.setString(_keyLastSeenPatch, currentAppVersion);
        return true;
      }
    } catch (_) {
      // Do not block app startup if SharedPreferences fails
    }
    return false;
  }

  /// Checks GitHub for an update newer than [currentAppVersion].
  static Future<bool> _checkRemoteUpdate(BuildContext context) async {
    try {
      final res = await http.get(
        Uri.parse(latestReleaseApi),
        headers: {
          'Accept': 'application/vnd.github+json',
          'User-Agent': 'CartzyMobileUpdateChecker',
        },
      ).timeout(const Duration(seconds: 4));

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data is Map<String, dynamic>) {
          final tagName = (data['tag_name'] ?? '').toString().replaceAll('v', '').trim();
          final htmlUrl = (data['html_url'] ?? defaultReleasePage).toString();

          // Find direct APK asset if available
          String downloadUrl = htmlUrl;
          final assets = data['assets'];
          if (assets is List) {
            for (final a in assets) {
              final name = (a['name'] ?? '').toString();
              if (name.endsWith('.apk')) {
                downloadUrl = (a['browser_download_url'] ?? htmlUrl).toString();
                break;
              }
            }
          }

          if (_isVersionNewer(tagName, currentAppVersion)) {
            if (!context.mounted) return false;

            List<String>? remoteHighlights;
            final rawBody = data['body']?.toString();
            if (rawBody != null && rawBody.trim().isNotEmpty) {
              final lines = rawBody
                  .split('\n')
                  .map((l) => l.trim())
                  .where((l) => l.startsWith('- ') || l.startsWith('* ') || l.startsWith('• '))
                  .map((l) => l.replaceFirst(RegExp(r'^[-*•]\s*(\*\*)?'), '').replaceFirst(RegExp(r'\*\*'), '').trim())
                  .where((l) => l.isNotEmpty)
                  .take(6)
                  .toList();
              if (lines.isNotEmpty) {
                remoteHighlights = lines;
              }
            }

            await PatchNotesDialog.showUpdateAvailable(
              context,
              newVersion: tagName,
              currentVersion: currentAppVersion,
              highlights: remoteHighlights,
              downloadUrl: downloadUrl,
            );
            return true;
          }
        }
      }
    } catch (_) {
      // Fail silently if offline
    }
    return false;
  }

  /// Compares semantic versions (e.g., "1.1.3" > "1.1.2").
  static bool _isVersionNewer(String remote, String current) {
    if (remote.isEmpty) return false;
    try {
      final rParts = remote.split('.').map((e) => int.tryParse(e) ?? 0).toList();
      final cParts = current.split('.').map((e) => int.tryParse(e) ?? 0).toList();

      for (var i = 0; i < 3; i++) {
        final r = i < rParts.length ? rParts[i] : 0;
        final c = i < cParts.length ? cParts[i] : 0;
        if (r > c) return true;
        if (r < c) return false;
      }
    } catch (_) {}
    return false;
  }
}


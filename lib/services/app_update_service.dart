import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:package_info_plus/package_info_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:cartzy/widgets/patch_notes_dialog.dart';

class AppUpdateService {
  /// The current app release version (fallback if PackageInfo is unavailable).
  static String currentAppVersion = '1.2.25';
  static bool _initialized = false;

  /// GitHub repository release endpoint for Cartzy.
  static const String latestReleaseApi =
      'https://api.github.com/repos/jessicabajamundi/cartzy/releases/latest';

  static const String defaultReleasePage =
      'https://github.com/jessicabajamundi/cartzy/releases/tag/v1.2.25';

  static const String _keyLastSeenPatch = 'cartzy_last_seen_patch_version';

  /// Initializes the app version from platform package metadata at runtime.
  static Future<void> init() async {
    if (_initialized) return;
    try {
      final info = await PackageInfo.fromPlatform();
      final v = info.version.trim();
      if (v.isNotEmpty) {
        currentAppVersion = v;
      }
    } catch (_) {
      // Keep static fallback version if platform info fails
    }
    _initialized = true;
  }

  /// Runs once on startup or when navigating to the main screen.
  /// Checks for both remote newer updates and local patch notes.
  static Future<void> checkOnAppStart(BuildContext context) async {
    await init();

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
      await init();
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
      await init();

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
          final rawTagName = (data['tag_name'] ?? '').toString().trim();
          final cleanTag = rawTagName.replaceFirst(RegExp(r'^[vV]'), '').trim();
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

          if (_isVersionNewer(cleanTag, currentAppVersion)) {
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
              newVersion: cleanTag,
              currentVersion: currentAppVersion,
              highlights: remoteHighlights,
              downloadUrl: downloadUrl,
            );
            return true;
          }
        }
      }
    } catch (_) {
      // Fail silently if offline or request fails
    }
    return false;
  }

  /// Parses a version string into [major, minor, patch, build].
  /// Handles formats like "1.2.18", "v1.2.18", "1.2.18+29", "1.2.18-patch1".
  static List<int> parseVersionParts(String version) {
    if (version.isEmpty) return [0, 0, 0, 0];
    try {
      var clean = version.trim().replaceFirst(RegExp(r'^[vV]'), '');

      var build = 0;
      if (clean.contains('+')) {
        final plusParts = clean.split('+');
        clean = plusParts[0];
        if (plusParts.length > 1) {
          final digits = RegExp(r'\d+').stringMatch(plusParts[1]);
          build = digits != null ? (int.tryParse(digits) ?? 0) : 0;
        }
      }

      if (clean.contains('-')) {
        clean = clean.split('-')[0];
      }

      final segments = clean.split('.');
      final major = segments.isNotEmpty
          ? (int.tryParse(RegExp(r'\d+').stringMatch(segments[0]) ?? '') ?? 0)
          : 0;
      final minor = segments.length > 1
          ? (int.tryParse(RegExp(r'\d+').stringMatch(segments[1]) ?? '') ?? 0)
          : 0;
      final patch = segments.length > 2
          ? (int.tryParse(RegExp(r'\d+').stringMatch(segments[2]) ?? '') ?? 0)
          : 0;

      return [major, minor, patch, build];
    } catch (_) {
      return [0, 0, 0, 0];
    }
  }

  /// Compares versions and returns true ONLY if [remote] is strictly newer than [current].
  /// If [current] >= [remote], returns false.
  static bool _isVersionNewer(String remote, String current) {
    if (remote.trim().isEmpty) return false;
    try {
      final r = parseVersionParts(remote);
      final c = parseVersionParts(current);

      // Compare major, minor, patch
      for (var i = 0; i < 3; i++) {
        if (r[i] > c[i]) return true;
        if (r[i] < c[i]) return false;
      }

      // If major, minor, and patch are all identical, check build number
      // only if both remote and current have a build number > 0.
      if (r[3] > 0 && c[3] > 0) {
        return r[3] > c[3];
      }
    } catch (_) {}
    return false;
  }
}


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Riders work in the Cartzy Android app, not on the website. This page tells
 * them so and hands them the newest APK from the GitHub releases.
 */
class RiderAppController extends Controller
{
    public const REPO = 'jessicabajamundi/cartzy';

    public const RELEASES_PAGE = 'https://github.com/'.self::REPO.'/releases/latest';

    /**
     * The newest release's version and APK link, checked at most every 10 minutes.
     *
     * @return array{version: ?string, apk_url: ?string, apk_name: ?string}
     */
    public static function latestRelease(): array
    {
        return Cache::remember('rider-app:latest-release', now()->addMinutes(10), function () {
            try {
                $release = Http::timeout(5)
                    ->withHeaders(['Accept' => 'application/vnd.github+json', 'User-Agent' => 'cartzy-rider-app-page'])
                    ->get('https://api.github.com/repos/'.self::REPO.'/releases/latest')
                    ->throw()->json();
            } catch (\Throwable $e) {
                report($e);

                return ['version' => null, 'apk_url' => null, 'apk_name' => null];
            }

            $apk = collect($release['assets'] ?? [])->first(fn ($asset) => str_ends_with(strtolower($asset['name'] ?? ''), '.apk'));

            return [
                'version' => isset($release['tag_name']) ? ltrim((string) $release['tag_name'], 'vV') : null,
                'apk_url' => $apk['browser_download_url'] ?? null,
                'apk_name' => $apk['name'] ?? null,
            ];
        });
    }

    public function show(Request $request)
    {
        return view('rider-app', [
            'release' => self::latestRelease(),
            'rider' => $request->user()?->role === \App\Models\User::ROLE_COURIER ? $request->user() : null,
        ]);
    }

    /** Always the newest APK, even though each release names its file after the version. */
    public function download()
    {
        return redirect()->away(self::latestRelease()['apk_url'] ?? self::RELEASES_PAGE);
    }
}

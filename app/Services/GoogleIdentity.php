<?php

namespace App\Services;

use Firebase\JWT\{JWT, JWK};
use Illuminate\Support\Facades\{Cache, Http};
use Illuminate\Validation\ValidationException;

class GoogleIdentity
{
    public function verify(string $idToken): array
    {
        try {
            $parts = explode('.', $idToken);
            if (count($parts) !== 3) throw new \RuntimeException('Invalid Google token.');
            $header = (array) JWT::jsonDecode(JWT::urlsafeB64Decode($parts[0]));
            if (($header['alg'] ?? '') !== 'RS256' || empty($header['kid'])) throw new \RuntimeException('Invalid Google token.');
            $fetchKeys = function () {
                return Http::timeout(10)->get('https://www.googleapis.com/oauth2/v3/certs')->throw()->json();
            };
            $keys = Cache::remember('google-identity-jwks', 3600, $fetchKeys);
            if (!collect($keys['keys'] ?? [])->contains('kid', $header['kid'])) {
                // Google may rotate its signing keys before the cache expires.
                Cache::forget('google-identity-jwks');
                $keys = Cache::remember('google-identity-jwks', 3600, $fetchKeys);
            }
            $claims = (array) JWT::decode($idToken, JWK::parseKeySet($keys, 'RS256'));
            $audiences = array_filter([config('services.google.client_id'), config('services.google.mobile_client_id')]);
            if (!in_array($claims['iss'] ?? '', ['https://accounts.google.com', 'accounts.google.com'], true)
                || !in_array($claims['aud'] ?? '', $audiences, true)
                || empty($claims['exp']) || $claims['exp'] <= time()
                || !filter_var($claims['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)
                || empty($claims['sub']) || !filter_var($claims['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Invalid Google identity.');
            }
            return $claims;
        } catch (\Throwable $error) {
            throw ValidationException::withMessages(['id_token' => 'Google identity could not be verified. Please select your Google account again.']);
        }
    }
}

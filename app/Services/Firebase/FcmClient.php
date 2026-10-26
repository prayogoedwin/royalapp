<?php

namespace App\Services\Firebase;

use App\Models\UserDevice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FcmClient
{
    public function isEnabled(): bool
    {
        if (! config('firebase.enabled')) {
            return false;
        }

        return $this->credentials() !== null;
    }

    /**
     * @param  list<string>  $tokens
     * @param  array<string, string>  $data
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_values(array_unique(array_filter($tokens)));

        if ($tokens === [] || ! $this->isEnabled()) {
            return;
        }

        foreach ($tokens as $token) {
            $this->send($token, $title, $body, $data);
        }
    }

    /**
     * @param  array<string, string>  $data
     */
    public function send(string $token, string $title, string $body, array $data = []): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $accessToken = $this->accessToken();
        $projectId = (string) config('firebase.project_id');

        if ($accessToken === null || $projectId === '') {
            return false;
        }

        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(15)
                ->post('https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send', [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => $this->stringifyData($data),
                        'android' => [
                            'priority' => 'high',
                        ],
                    ],
                ]);
        } catch (Throwable $e) {
            report($e);

            return false;
        }

        if ($response->successful()) {
            return true;
        }

        $error = $response->json('error.status') ?? $response->json('error.message');

        if (in_array($error, ['NOT_FOUND', 'UNREGISTERED', 'INVALID_ARGUMENT'], true)
            || str_contains(strtolower((string) $response->body()), 'unregistered')) {
            UserDevice::query()->where('fcm_token', $token)->delete();
        } else {
            Log::warning('FCM send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return false;
    }

    /**
     * @return array{client_email: string, private_key: string}|null
     */
    private function credentials(): ?array
    {
        $path = (string) config('firebase.credentials');

        if ($path !== '' && ! str_starts_with($path, '/')) {
            $path = base_path($path);
        }

        if ($path === '' || ! is_file($path)) {
            return null;
        }

        $json = json_decode((string) file_get_contents($path), true);

        if (! is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            return null;
        }

        return [
            'client_email' => (string) $json['client_email'],
            'private_key' => (string) $json['private_key'],
        ];
    }

    private function accessToken(): ?string
    {
        $cached = Cache::get('firebase.access_token');
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $credentials = $this->credentials();
        if ($credentials === null) {
            return null;
        }

        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));

        $unsigned = $header.'.'.$payload;
        $signature = '';
        if (! openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            Log::warning('FCM JWT sign failed');

            return null;
        }

        $jwt = $unsigned.'.'.$this->base64UrlEncode($signature);

        try {
            $response = Http::asForm()
                ->timeout(15)
                ->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]);
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        $token = $response->json('access_token');
        $expiresIn = (int) ($response->json('expires_in') ?? 0);

        if (! is_string($token) || $token === '') {
            Log::warning('FCM access token failed', ['body' => $response->body()]);

            return null;
        }

        Cache::put('firebase.access_token', $token, max(60, $expiresIn - 60));

        return $token;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function stringifyData(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $out[(string) $key] = is_scalar($value) || $value === null
                ? (string) $value
                : json_encode($value, JSON_THROW_ON_ERROR);
        }

        return $out;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

<?php

namespace Modules\GoogleIntegration\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class GoogleServiceAccountClient
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const SCOPES = 'https://www.googleapis.com/auth/spreadsheets https://www.googleapis.com/auth/calendar';

    private ?string $accessToken = null;

    private int $tokenExpiresAt = 0;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $credentialOverride = null;


    public static function isConfigured(): bool
    {
        return static::credentials() !== null;
    }


    /**
     * @return array<string, mixed>|null
     */
    public static function credentials(): ?array
    {
        return static::credentialsFromJson((string) setting('google_service_account_json', ''));
    }


    /**
     * @return array<string, mixed>|null
     */
    public static function credentialsFromJson(string $json): ?array
    {
        $json = trim($json);

        if ($json === '') {
            return null;
        }

        $data = json_decode($json, true);

        if (! is_array($data) || empty($data['client_email']) || empty($data['private_key'])) {
            return null;
        }

        return $data;
    }


    /**
     * @param array<string, mixed> $credentials
     */
    public function usingCredentials(array $credentials): self
    {
        $clone = clone $this;
        $clone->credentialOverride = $credentials;
        $clone->accessToken = null;
        $clone->tokenExpiresAt = 0;

        return $clone;
    }


    public function accessToken(): string
    {
        if ($this->accessToken && time() < ($this->tokenExpiresAt - 60)) {
            return $this->accessToken;
        }

        $credentials = $this->credentialOverride ?? static::credentials();

        if ($credentials === null) {
            throw new Exception('Google service account credentials are not configured.');
        }

        $jwt = $this->createJwt($credentials);

        $response = Http::asForm()
            ->timeout(30)
            ->post(self::TOKEN_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

        if ($response->failed()) {
            throw new Exception(
                'Google OAuth token error: ' . ($response->json('error_description') ?? $response->body())
            );
        }

        $this->accessToken = (string) $response->json('access_token');
        $this->tokenExpiresAt = time() + (int) ($response->json('expires_in') ?? 3600);

        return $this->accessToken;
    }


    public function http()
    {
        return Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(30);
    }


    /**
     * @param array<string, mixed> $credentials
     */
    private function createJwt(array $credentials): string
    {
        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => self::SCOPES,
            'aud' => self::TOKEN_URL,
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $signingInput = $header . '.' . $payload;
        $privateKey = openssl_pkey_get_private($credentials['private_key']);

        if ($privateKey === false) {
            throw new Exception('Invalid Google service account private key.');
        }

        $signature = '';

        if (! openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new Exception('Failed to sign Google JWT.');
        }

        return $signingInput . '.' . $this->base64UrlEncode($signature);
    }


    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

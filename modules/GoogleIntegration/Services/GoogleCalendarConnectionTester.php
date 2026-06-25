<?php

namespace Modules\GoogleIntegration\Services;

use Exception;
use Modules\GoogleIntegration\Support\GoogleCalendarUrl;

class GoogleCalendarConnectionTester
{
    public function __construct(
        private readonly GoogleServiceAccountClient $client,
    ) {
    }


    /**
     * @param array{
     *   google_service_account_json?: string|null,
     *   google_calendar_id?: string|null
     * } $input
     *
     * @return array{
     *   ok: bool,
     *   message: string,
     *   client_email?: string,
     *   calendar_summary?: string,
     *   calendar_timezone?: string
     * }
     */
    public function test(array $input = []): array
    {
        $json = trim((string) ($input['google_service_account_json'] ?? ''));

        if ($json === '') {
            $json = trim((string) setting('google_service_account_json', ''));
        }

        if ($json === '') {
            return [
                'ok' => false,
                'message' => trans('setting::messages.google_calendar_test_missing_json'),
            ];
        }

        $credentials = GoogleServiceAccountClient::credentialsFromJson($json);

        if ($credentials === null) {
            return [
                'ok' => false,
                'message' => trans('setting::messages.google_calendar_test_invalid_json'),
            ];
        }

        $authenticatedClient = $this->client->usingCredentials($credentials);

        try {
            $authenticatedClient->accessToken();
        } catch (Exception $exception) {
            return [
                'ok' => false,
                'message' => trans('setting::messages.google_calendar_test_auth_failed', [
                    'error' => $exception->getMessage(),
                ]),
            ];
        }

        $calendarId = trim((string) ($input['google_calendar_id'] ?? ''));

        if ($calendarId === '') {
            $calendarId = trim((string) setting('google_calendar_id', ''));
        }

        if ($calendarId === '') {
            return [
                'ok' => true,
                'message' => trans('setting::messages.google_calendar_test_auth_ok'),
                'client_email' => $credentials['client_email'],
            ];
        }

        try {
            $encodedCalendarId = rawurlencode($calendarId);
            $http = $authenticatedClient->http();

            $response = $http->get(
                "https://www.googleapis.com/calendar/v3/calendars/{$encodedCalendarId}",
                ['fields' => 'id,summary,timeZone'],
            );

            if ($response->failed()) {
                throw new Exception($response->json('error.message') ?? $response->body());
            }

            $accessRole = $this->resolveAccessRole($http, $calendarId);

            if (! in_array($accessRole, ['writer', 'owner'], true)) {
                return [
                    'ok' => false,
                    'message' => trans('setting::messages.google_calendar_test_reader_only', [
                        'role' => $accessRole !== '' ? $accessRole : 'reader',
                        'email' => $credentials['client_email'],
                    ]),
                    'client_email' => $credentials['client_email'],
                    'calendar_summary' => (string) ($response->json('summary') ?? ''),
                ];
            }
        } catch (Exception $exception) {
            return [
                'ok' => false,
                'message' => trans('setting::messages.google_calendar_test_calendar_failed', [
                    'error' => $exception->getMessage(),
                ]),
                'client_email' => $credentials['client_email'],
            ];
        }

        return [
            'ok' => true,
            'message' => trans('setting::messages.google_calendar_test_success', [
                'calendar' => (string) ($response->json('summary') ?? $calendarId),
            ]),
            'client_email' => $credentials['client_email'],
            'calendar_summary' => (string) ($response->json('summary') ?? ''),
            'calendar_timezone' => (string) ($response->json('timeZone') ?? ''),
            'calendar_url' => GoogleCalendarUrl::browserUrl($calendarId),
        ];
    }


    /**
     * accessRole is on calendarList entries, not the calendars resource.
     */
    private function resolveAccessRole($http, string $calendarId): string
    {
        $encodedCalendarId = rawurlencode($calendarId);

        $listResponse = $http->get(
            "https://www.googleapis.com/calendar/v3/users/me/calendarList/{$encodedCalendarId}",
            ['fields' => 'accessRole'],
        );

        if ($listResponse->successful()) {
            return (string) ($listResponse->json('accessRole') ?? '');
        }

        $aclResponse = $http->get(
            "https://www.googleapis.com/calendar/v3/calendars/{$encodedCalendarId}/acl",
            ['fields' => 'items(role,scope)'],
        );

        if ($aclResponse->failed()) {
            return '';
        }

        $bestRole = '';

        foreach ($aclResponse->json('items', []) as $rule) {
            $role = (string) ($rule['role'] ?? '');

            if (! in_array($role, ['owner', 'writer', 'reader', 'freeBusyReader'], true)) {
                continue;
            }

            if ($bestRole === '' || $this->roleRank($role) > $this->roleRank($bestRole)) {
                $bestRole = $role;
            }
        }

        return $bestRole;
    }


    private function roleRank(string $role): int
    {
        return match ($role) {
            'owner' => 4,
            'writer' => 3,
            'reader' => 2,
            'freeBusyReader' => 1,
            default => 0,
        };
    }
}

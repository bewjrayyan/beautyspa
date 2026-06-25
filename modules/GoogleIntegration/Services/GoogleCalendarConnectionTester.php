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

            $response = $authenticatedClient->http()->get(
                "https://www.googleapis.com/calendar/v3/calendars/{$encodedCalendarId}",
                ['fields' => 'id,summary,timeZone,accessRole'],
            );

            if ($response->failed()) {
                throw new Exception($response->json('error.message') ?? $response->body());
            }

            $accessRole = (string) ($response->json('accessRole') ?? '');

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
}

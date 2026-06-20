<?php

namespace Modules\User\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Setting\Support\SettingValues;
use Modules\Core\Support\WritableStorageBootstrap;
use Modules\User\Entities\OneSenderMessageLog;
use Modules\User\Entities\OneSenderOutboundMessage;
use Modules\User\Support\PhoneNumber;
use Modules\User\Support\WhatsAppFormatting;

class OneSenderWhatsAppService
{
    /** WhatsApp Cloud API document caption limit. */
    private const DOCUMENT_CAPTION_MAX_LENGTH = 1024;


    public static function isEnabled(): bool
    {
        return SettingValues::isTruthy('onesender_enabled');
    }


    public static function isConfigured(): bool
    {
        return static::isEnabled()
            && filled(SettingValues::get('onesender_api_url'))
            && filled(SettingValues::get('onesender_api_key'));
    }


    public static function allowsRealOutbound(): bool
    {
        if (! WritableStorageBootstrap::isLocalEnvironment()) {
            return true;
        }

        return filter_var(
            config('setting.whatsapp_notifications.onesender_allow_in_local', false),
            FILTER_VALIDATE_BOOLEAN
        );
    }


    /**
     * @param  array{source?: string, dedupe_key?: string, immediate?: bool}  $context
     *
     * @return bool True when the message was accepted by OneSender; false when skipped (still logged).
     *
     * @throws Exception
     */
    public function sendNotification(string $phone, string $message, array $context = []): bool
    {
        return $this->dispatchText($phone, $message, $context);
    }


    /**
     * @param  array{source?: string, dedupe_key?: string}  $context
     *
     * @throws Exception
     */
    /**
     * @return bool True when sent; false when skipped (still logged).
     *
     * @throws Exception
     */
    public function sendImage(string $phone, string $imageUrl, ?string $caption = null, array $context = []): bool
    {
        $to = PhoneNumber::normalize($phone);
        $imageUrl = trim($imageUrl);

        if ($to === '' || $imageUrl === '') {
            throw new Exception(trans('user::messages.whatsapp_otp.send_failed'));
        }

        $image = ['link' => $imageUrl];

        if ($caption !== null && trim($caption) !== '') {
            $image['caption'] = $this->fitDocumentCaption(trim($caption));
        }

        $payload = [
            [
                'type' => 'image',
                'to' => $to,
                'recipient_type' => 'individual',
                'image' => $image,
            ],
        ];

        $fingerprint = 'image:' . $imageUrl . '|' . ($image['caption'] ?? '');

        return $this->deliverPayload(
            $to,
            'individual',
            'image',
            $fingerprint,
            $payload,
            $context
        );
    }


    /**
     * @return bool True when sent; false when skipped (still logged).
     *
     * @throws Exception
     */
    public function sendDocument(
        string $phone,
        string $documentUrl,
        string $filename,
        ?string $caption = null,
        array $context = [],
    ): bool {
        $to = PhoneNumber::normalize($phone);
        $documentUrl = trim($documentUrl);
        $filename = trim($filename);

        if ($to === '') {
            throw new Exception(trans('user::messages.whatsapp_otp.invalid_phone'));
        }

        if ($documentUrl === '' || $filename === '') {
            throw new Exception(trans('user::messages.whatsapp_otp.send_failed'));
        }

        $document = [
            'link' => $documentUrl,
            'filename' => $filename,
        ];

        if ($caption !== null && trim($caption) !== '') {
            $document['caption'] = WhatsAppFormatting::boldOtpCodesInMessage(
                $this->fitDocumentCaption(trim($caption))
            );
        }

        $payload = [
            [
                'type' => 'document',
                'to' => $to,
                'recipient_type' => 'individual',
                'document' => $document,
            ],
        ];

        $fingerprint = 'document:' . $documentUrl . '|' . $filename . '|' . ($document['caption'] ?? '');

        return $this->deliverPayload(
            $to,
            'individual',
            'document',
            $fingerprint,
            $payload,
            $context
        );
    }


    /**
     * @param  array{source?: string, dedupe_key?: string}  $context
     *
     * @throws Exception
     */
    public function sendOtp(string $phone, string $message, array $context = []): void
    {
        if (! setting('whatsapp_otp_login_enabled')) {
            throw new Exception(trans('user::messages.whatsapp_otp.service_disabled'));
        }

        $context['source'] ??= 'auth.otp';

        if ($this->dispatchText($phone, $message, $context)) {
            return;
        }

        if (! static::isEnabled()) {
            throw new Exception(trans('user::messages.whatsapp_otp.missing_credentials'));
        }

        if (! static::isConfigured()) {
            throw new Exception(trans('user::messages.whatsapp_otp.missing_credentials'));
        }

        if (app(OneSenderMessageLogger::class)->isSendingPaused()) {
            throw new Exception(trans('user::messages.whatsapp_otp.service_disabled'));
        }

        throw new Exception(trans('user::messages.whatsapp_otp.send_failed'));
    }


    /**
     * @param  array{source?: string, dedupe_key?: string}  $context
     */
    public function notifyAdmins(string $message, array $context = []): void
    {
        $context['source'] ??= 'admin.notify';

        foreach ($this->adminPhones() as $phone) {
            try {
                $this->dispatchText($phone, $message, $context);
            } catch (Exception $exception) {
                Log::error('OneSender admin notification failed', [
                    'phone' => $phone,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }


    /**
     * @param  array{source?: string, dedupe_key?: string}  $context
     *
     * @throws Exception
     */
    /**
     * @return bool True when sent; false when skipped (still logged).
     *
     * @throws Exception
     */
    public function sendToGroup(string $groupId, string $message, array $context = []): bool
    {
        $groupId = trim($groupId);

        if ($groupId === '' || ! str_contains($groupId, '@g.us')) {
            throw new Exception(trans('order::messages.invalid_whatsapp_group_id'));
        }

        $message = WhatsAppFormatting::boldOtpCodesInMessage(trim($message));

        if ($message === '') {
            throw new Exception(trans('user::messages.whatsapp_otp.send_failed'));
        }

        $payload = [
            [
                'type' => 'text',
                'to' => $groupId,
                'recipient_type' => 'group',
                'text' => [
                    'body' => $message,
                ],
            ],
        ];

        return $this->deliverPayload(
            $groupId,
            'group',
            'text',
            $message,
            $payload,
            $context
        );
    }


    /**
     * @param  array{source?: string, dedupe_key?: string}  $context
     *
     * @throws Exception
     */
    private function dispatchText(string $phone, string $message, array $context = []): bool
    {
        $to = PhoneNumber::normalize($phone);
        $message = WhatsAppFormatting::boldOtpCodesInMessage(trim($message));

        if ($to === '') {
            throw new Exception(trans('user::messages.whatsapp_otp.invalid_phone'));
        }

        if ($message === '') {
            throw new Exception(trans('user::messages.whatsapp_otp.send_failed'));
        }

        $payload = [
            [
                'type' => 'text',
                'to' => $to,
                'recipient_type' => 'individual',
                'text' => [
                    'body' => $message,
                ],
            ],
        ];

        return $this->deliverPayload($to, 'individual', 'text', $message, $payload, $context);
    }


    /**
     * @param  array{source?: string, dedupe_key?: string}  $context
     *
     * @return bool True when sent; false when skipped (still logged).
     *
     * @throws Exception
     */
    private function deliverPayload(
        string $recipient,
        string $recipientType,
        string $messageType,
        string $fingerprint,
        array $payload,
        array $context,
    ): bool {
        $logger = app(OneSenderMessageLogger::class);
        $context = $this->resolveContext($context);

        if (! static::allowsRealOutbound()) {
            $logger->recordSkipped(
                $recipient,
                $recipientType,
                $messageType,
                $fingerprint,
                OneSenderMessageLog::STATUS_SKIPPED_DISABLED,
                array_merge($context, ['skip_reason' => 'local_dev_blocked'])
            );

            return false;
        }

        if (! static::isConfigured()) {
            $logger->recordSkipped(
                $recipient,
                $recipientType,
                $messageType,
                $fingerprint,
                OneSenderMessageLog::STATUS_SKIPPED_DISABLED,
                $context
            );

            return false;
        }

        if ($logger->isDuplicate($recipient, $fingerprint, $context)) {
            $logger->recordSkipped(
                $recipient,
                $recipientType,
                $messageType,
                $fingerprint,
                OneSenderMessageLog::STATUS_SKIPPED_DUPLICATE,
                $context
            );

            return false;
        }

        $queue = app(OneSenderOutboundQueueService::class);

        if ($queue->isEnabled() && empty($context['immediate'])) {
            if ($queue->isDuplicateInQueue($recipient, $fingerprint, $context)) {
                $logger->recordSkipped(
                    $recipient,
                    $recipientType,
                    $messageType,
                    $fingerprint,
                    OneSenderMessageLog::STATUS_SKIPPED_DUPLICATE,
                    $context
                );

                return false;
            }

            $queue->enqueue($recipient, $recipientType, $messageType, $fingerprint, $payload, $context);

            return true;
        }

        if ($logger->isSendingPaused()) {
            $logger->recordSkipped(
                $recipient,
                $recipientType,
                $messageType,
                $fingerprint,
                OneSenderMessageLog::STATUS_SKIPPED_PAUSED,
                $context
            );

            return false;
        }

        return $this->deliverPayloadNow($recipient, $recipientType, $messageType, $fingerprint, $payload, $context);
    }


    public function deliverQueuedPayload(OneSenderOutboundMessage $queued): void
    {
        $queued->refresh();

        if ($queued->status !== OneSenderOutboundMessage::STATUS_PROCESSING) {
            return;
        }

        $queueService = app(OneSenderOutboundQueueService::class);
        $logger = app(OneSenderMessageLogger::class);
        $context = [
            'source' => $queued->source,
            'dedupe_key' => $queued->dedupe_key,
        ];

        $fingerprint = (string) $queued->message_preview;
        $payload = $queued->payload;

        if (! is_array($payload) || $payload === []) {
            $queueService->markFailed($queued, 'Invalid queued payload.');

            return;
        }

        if (! static::isConfigured()) {
            $logger->recordSkipped(
                $queued->recipient,
                $queued->recipient_type,
                $queued->message_type,
                $fingerprint,
                OneSenderMessageLog::STATUS_SKIPPED_DISABLED,
                $context
            );
            $queueService->markFailed($queued, 'OneSender API is disabled or not configured.');

            return;
        }

        if ($logger->isSendingPaused()) {
            $logger->recordSkipped(
                $queued->recipient,
                $queued->recipient_type,
                $queued->message_type,
                $fingerprint,
                OneSenderMessageLog::STATUS_SKIPPED_PAUSED,
                $context
            );
            $queueService->markFailed($queued, 'Outbound WhatsApp is paused.');

            return;
        }

        if ($logger->isDuplicate($queued->recipient, $fingerprint, $context)) {
            $logger->recordSkipped(
                $queued->recipient,
                $queued->recipient_type,
                $queued->message_type,
                $fingerprint,
                OneSenderMessageLog::STATUS_SKIPPED_DUPLICATE,
                $context
            );
            $queueService->markFailed($queued, 'Duplicate message.');

            return;
        }

        try {
            $this->deliverPayloadNow(
                $queued->recipient,
                $queued->recipient_type,
                $queued->message_type,
                $fingerprint,
                $payload,
                $context
            );
            $queueService->markSent($queued);
        } catch (Exception $exception) {
            $queueService->markFailed($queued, $exception->getMessage());
        }
    }


    /**
     * @param  array{source?: string, dedupe_key?: string}  $context
     *
     * @throws Exception
     */
    private function deliverPayloadNow(
        string $recipient,
        string $recipientType,
        string $messageType,
        string $fingerprint,
        array $payload,
        array $context,
    ): bool {
        $logger = app(OneSenderMessageLogger::class);

        $response = $this->postPayload($payload);

        try {
            $this->assertSuccessfulResponse($response, $recipient);
            $logger->recordSent($recipient, $recipientType, $messageType, $fingerprint, $response, $context);

            return true;
        } catch (Exception $exception) {
            $logger->recordFailed(
                $recipient,
                $recipientType,
                $messageType,
                $fingerprint,
                $response,
                $exception->getMessage(),
                $context
            );

            throw $exception;
        }
    }


    /**
     * @param  array{source?: string, dedupe_key?: string}  $context
     *
     * @return array{source?: string, dedupe_key?: string}
     */
    private function resolveContext(array $context): array
    {
        if (! isset($context['source'])) {
            $context['source'] = $this->guessSourceFromBacktrace();
        }

        return $context;
    }


    private function guessSourceFromBacktrace(): ?string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12);

        foreach ($trace as $frame) {
            $class = $frame['class'] ?? null;

            if (! is_string($class) || ! str_contains($class, '\\')) {
                continue;
            }

            if (str_starts_with($class, 'Modules\\User\\Services\\OneSender')) {
                continue;
            }

            if (str_starts_with($class, 'Illuminate\\')) {
                continue;
            }

            return class_basename($class);
        }

        return null;
    }


    private function fitDocumentCaption(string $caption): string
    {
        if (mb_strlen($caption) <= self::DOCUMENT_CAPTION_MAX_LENGTH) {
            return $caption;
        }

        return mb_substr($caption, 0, self::DOCUMENT_CAPTION_MAX_LENGTH - 1).'…';
    }


    private function postPayload(array $payload): Response
    {
        return Http::withToken((string) SettingValues::get('onesender_api_key'))
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post((string) SettingValues::get('onesender_api_url'), $payload);
    }


    /**
     * @throws Exception
     */
    private function assertSuccessfulResponse(Response $response, string $recipient): void
    {
        if ($response->failed()) {
            Log::error('OneSender HTTP error', [
                'recipient' => $recipient,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new Exception($this->extractApiError($response));
        }

        $body = $response->json();

        if (! is_array($body)) {
            return;
        }

        if (array_key_exists('success', $body) && $body['success'] === false) {
            $this->logAndThrowApiError($recipient, $body, $response);
        }

        $code = $body['code'] ?? null;

        if (is_numeric($code) && (int) $code >= 400) {
            $this->logAndThrowApiError($recipient, $body, $response);
        }

        if (! empty($body['errors']) && empty($body['messages'])) {
            $this->logAndThrowApiError($recipient, $body, $response);
        }
    }


    /**
     * @param  array<string, mixed>  $body
     *
     * @throws Exception
     */
    private function logAndThrowApiError(string $recipient, array $body, Response $response): void
    {
        Log::error('OneSender API rejected message', [
            'recipient' => $recipient,
            'status' => $response->status(),
            'body' => $body,
        ]);

        throw new Exception($this->formatApiError($body, $response));
    }


    private function extractApiError(Response $response): string
    {
        $body = $response->json();

        if (is_array($body)) {
            return $this->formatApiError($body, $response);
        }

        $raw = trim($response->body());

        return $raw !== '' ? $raw : trans('user::messages.whatsapp_otp.send_failed');
    }


    /**
     * @param  array<string, mixed>  $body
     */
    private function formatApiError(array $body, Response $response): string
    {
        $message = $body['message'] ?? null;

        if (is_string($message) && trim($message) !== '') {
            return trim($message);
        }

        $errors = $body['errors'] ?? null;

        if (is_string($errors) && trim($errors) !== '') {
            return trim($errors);
        }

        if (is_array($errors)) {
            $parts = [];

            foreach ($errors as $value) {
                if (is_string($value) && trim($value) !== '') {
                    $parts[] = trim($value);
                }
            }

            if ($parts !== []) {
                return implode(' ', $parts);
            }
        }

        $raw = trim($response->body());

        return $raw !== '' ? $raw : trans('user::messages.whatsapp_otp.send_failed');
    }


    /**
     * @return array<int, string>
     */
    private function adminPhones(): array
    {
        $raw = (string) SettingValues::get('onesender_admin_phones', '');

        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (string $phone) => PhoneNumber::normalize($phone),
            preg_split('/[\s,;]+/', $raw) ?: []
        )));
    }
}

<?php

namespace Modules\SpecialGift\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\SpecialGift\Entities\GiftVoucherSubmission;
use Modules\SpecialGift\Http\Requests\SendGiftRequest;
use Modules\SpecialGift\Services\GiftVoucherSenderService;
use Modules\SpecialGift\Services\SpecialGiftConfig;

class SendGiftController
{
    public function __construct(
        private SpecialGiftConfig $config,
        private GiftVoucherSenderService $sender,
    ) {}


    public function create(): View
    {
        if (! $this->config->enabled()) {
            abort(404);
        }

        return view('specialgift::public.send-gift', [
            'giftConfig' => $this->config,
            'pageDesign' => $this->config->pageDesign(),
            'voucherPreviewUrl' => $this->config->resolveVoucherBackgroundUrl(),
        ]);
    }


    public function store(SendGiftRequest $request): JsonResponse|RedirectResponse
    {
        $submission = $this->sender->send($request->validated());

        if ($request->expectsJson()) {
            if ($submission->delivery_status === GiftVoucherSubmission::STATUS_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => trans('specialgift::messages.sent_success'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $submission->error_message ?: trans('specialgift::messages.send_failed'),
            ], 422);
        }

        if ($submission->delivery_status === GiftVoucherSubmission::STATUS_SENT) {
            return redirect()
                ->route('specialgift.send.create')
                ->with('success', trans('specialgift::messages.sent_success'));
        }

        return redirect()
            ->route('specialgift.send.create')
            ->withInput()
            ->with('error', $submission->error_message ?: trans('specialgift::messages.send_failed'));
    }
}

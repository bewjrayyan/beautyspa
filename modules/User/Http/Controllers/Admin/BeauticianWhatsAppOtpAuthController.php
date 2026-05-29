<?php

namespace Modules\User\Http\Controllers\Admin;

use FleetCart\Http\IntendedUrl;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\Beautician\Entities\Beautician;
use Modules\User\Http\Requests\Admin\BeauticianSendWhatsAppOtpRequest;
use Modules\User\Http\Requests\Admin\BeauticianVerifyWhatsAppOtpRequest;
use Modules\User\Services\WhatsAppOtpService;
use Modules\User\Support\PhoneNumber;

class BeauticianWhatsAppOtpAuthController extends Controller
{
    public function __construct(
        private readonly WhatsAppOtpService $otpService,
    ) {
        $this->middleware('guest');
    }


    public function sendOtp(BeauticianSendWhatsAppOtpRequest $request): JsonResponse
    {
        try {
            $this->resolveBeauticianUser($request->phone);

            $this->otpService->send($request->phone, 'admin');

            return response()->json([
                'message' => trans('user::messages.whatsapp_otp.sent'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }


    public function verifyOtp(BeauticianVerifyWhatsAppOtpRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $normalizedPhone = $this->otpService->verify($request->phone, $request->otp);
            $user = $this->resolveBeauticianUser($normalizedPhone);

            $user->login();

            $redirect = IntendedUrl::resolveAfterAdminLogin(session()->pull('url.intended'), $user);

            if ($request->expectsJson()) {
                return response()->json(['redirect' => $redirect]);
            }

            return redirect()->to($redirect);
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withError($e->getMessage());
        }
    }


    /**
     * @throws Exception
     */
    private function resolveBeauticianUser(string $phone)
    {
        $normalized = PhoneNumber::normalize($phone);

        if ($normalized === '') {
            throw new Exception(trans('user::messages.whatsapp_otp.invalid_phone'));
        }

        $beautician = Beautician::query()
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->whereIn('phone', PhoneNumber::variants($normalized))
            ->with('user')
            ->first();

        if (! $beautician?->user) {
            throw new Exception(trans('treatmentreservation::admin.portal.otp_no_account'));
        }

        return $beautician->user;
    }
}

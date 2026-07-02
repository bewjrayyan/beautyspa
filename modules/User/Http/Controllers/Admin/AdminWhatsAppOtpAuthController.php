<?php

namespace Modules\User\Http\Controllers\Admin;

use AestheticCart\Http\IntendedUrl;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\User\Http\Requests\Admin\AdminSendWhatsAppOtpRequest;
use Modules\User\Http\Requests\Admin\AdminVerifyWhatsAppOtpRequest;
use Modules\User\Services\AdminWhatsAppOtpUserResolver;
use Modules\User\Services\WhatsAppOtpService;

class AdminWhatsAppOtpAuthController extends Controller
{
    public function __construct(
        private readonly WhatsAppOtpService $otpService,
        private readonly AdminWhatsAppOtpUserResolver $userResolver,
    ) {
        $this->middleware('guest');
    }


    public function sendOtp(AdminSendWhatsAppOtpRequest $request): JsonResponse
    {
        try {
            $this->userResolver->resolve($request->phone);

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


    public function verifyOtp(AdminVerifyWhatsAppOtpRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $normalizedPhone = $this->otpService->verify($request->phone, $request->otp);
            $user = $this->userResolver->resolve($normalizedPhone);

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
}

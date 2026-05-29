<?php

namespace Modules\User\Http\Controllers;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Modules\User\Contracts\Authentication;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Modules\User\Events\CustomerRegistered;
use Modules\User\Http\Requests\SendWhatsAppOtpRequest;
use Modules\User\Http\Requests\VerifyWhatsAppOtpRequest;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Services\WhatsAppOtpService;
use Modules\User\Support\PhoneNumber;

class WhatsAppOtpAuthController extends Controller
{
    public function __construct(
        private readonly Authentication $auth,
        private readonly WhatsAppOtpService $otpService,
        private readonly OneSenderWhatsAppService $oneSender,
    ) {
        $this->middleware('guest');
    }


    public function sendOtp(SendWhatsAppOtpRequest $request): JsonResponse
    {
        try {
            $this->otpService->send($request->phone, 'login');

            return response()->json([
                'message' => trans('user::messages.whatsapp_otp.sent'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }


    public function verifyOtp(VerifyWhatsAppOtpRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $normalizedPhone = $this->otpService->verify($request->phone, $request->otp);
            $user = $this->findOrCreateUser($normalizedPhone);

            $user->login();

            if ($request->expectsJson()) {
                return response()->json([
                    'redirect' => route('account.dashboard.index'),
                ]);
            }

            return redirect()->intended(route('account.dashboard.index'));
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withError($e->getMessage());
        }
    }


    private function findOrCreateUser(string $normalizedPhone): User
    {
        $user = User::findByPhone($normalizedPhone);

        if ($user) {
            return $user;
        }

        $user = $this->auth->registerAndActivate([
            'first_name' => 'Customer',
            'last_name' => substr($normalizedPhone, -4),
            'email' => 'wa' . $normalizedPhone . '@whatsapp.local',
            'phone' => $normalizedPhone,
            'password' => Str::random(32),
        ]);

        $this->assignCustomerRole($user);

        event(new CustomerRegistered($user));

        $this->oneSender->notifyAdmins(
            trans('user::messages.whatsapp_otp.admin_new_registration', [
                'phone' => $normalizedPhone,
                'store' => setting('store_name'),
            ])
        );

        return $user;
    }


    private function assignCustomerRole(User $user): void
    {
        $role = Role::findOrNew(setting('customer_role'));

        if ($role->exists) {
            $this->auth->assignRole($user, $role);
        }
    }
}

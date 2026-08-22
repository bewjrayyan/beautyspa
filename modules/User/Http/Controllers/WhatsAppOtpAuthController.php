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
use Modules\User\Http\Requests\SendWhatsAppOtpRequest;
use Modules\User\Http\Requests\VerifyWhatsAppOtpRequest;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Services\WhatsAppOtpService;
use Modules\User\Support\DeferCustomerRegistered;
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
            $redirect = $this->safeIntendedUrl($request);

            if ($request->expectsJson()) {
                return response()->json([
                    'redirect' => $redirect,
                ]);
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
     * Only allow same-host http(s) intended URLs (open-redirect guard).
     */
    private function safeIntendedUrl(\Illuminate\Http\Request $request): string
    {
        $fallback = route('account.dashboard.index');
        $intended = $request->session()->pull('url.intended', $fallback);

        if (! is_string($intended) || $intended === '') {
            return $fallback;
        }

        if (str_starts_with($intended, '/')) {
            return url($intended);
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $intendedParts = parse_url($intended);

        if (! is_array($intendedParts)) {
            return $fallback;
        }

        $scheme = strtolower((string) ($intendedParts['scheme'] ?? ''));
        $host = $intendedParts['host'] ?? null;

        if (! in_array($scheme, ['http', 'https'], true)) {
            return $fallback;
        }

        if (! is_string($host) || ! is_string($appHost) || strcasecmp($host, $appHost) !== 0) {
            return $fallback;
        }

        return $intended;
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

        DeferCustomerRegistered::dispatch($user);

        $normalizedPhoneForAdmin = $normalizedPhone;
        $storeName = setting('store_name');

        dispatch(function () use ($normalizedPhoneForAdmin, $storeName): void {
            app(OneSenderWhatsAppService::class)->notifyAdmins(
                trans('user::messages.whatsapp_otp.admin_new_registration', [
                    'phone' => $normalizedPhoneForAdmin,
                    'store' => $storeName,
                ])
            );
        })->afterResponse();

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

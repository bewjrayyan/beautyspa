<?php

namespace Modules\User\Http\Controllers\Admin;

use AestheticCart\Http\IntendedUrl;
use Illuminate\Http\Response;
use Modules\User\Entities\User;
use Modules\User\Http\Controllers\BaseAuthController;
use Modules\User\Http\Requests\LoginRequest;

class AuthController extends BaseAuthController
{
    /**
     * Show login form.
     *
     * @return Response
     */
    public function getLogin()
    {
        $this->sanitizeIntendedUrl();

        return view('user::admin.auth.login', [
            'loginMode' => (bool) setting('whatsapp_otp_login_enabled') && request('login') === 'whatsapp'
                ? 'whatsapp'
                : 'email',
            'whatsappOtpEnabled' => (bool) setting('whatsapp_otp_login_enabled'),
        ]);
    }


    public function postLogin(LoginRequest $request)
    {
        $this->sanitizeIntendedUrl();

        return parent::postLogin($request);
    }


    protected function redirectAfterLogin()
    {
        return redirect()->to(
            IntendedUrl::resolveAfterAdminLogin(session()->pull('url.intended'), auth()->user())
        );
    }


    private function sanitizeIntendedUrl(): void
    {
        $intended = session()->get('url.intended');

        if (! is_string($intended) || $intended === '') {
            return;
        }

        if (! IntendedUrl::isAdmin($intended)) {
            session()->forget('url.intended');

            return;
        }

        $path = parse_url($intended, PHP_URL_PATH) ?? '/';

        if (IntendedUrl::isMalformedAdminPath($path)) {
            session()->forget('url.intended');
        }
    }


    /**
     * Show reset password form.
     *
     * @return Response
     */
    public function getReset()
    {
        return view('user::admin.auth.reset.begin');
    }


    /**
     * Where to redirect users after login.
     *
     * @return string
     */
    protected function redirectTo()
    {
        $user = auth()->user();

        if ($user && $user->isBeauticianOnly()) {
            return route('admin.treatment_reservations.portal');
        }

        return route('admin.dashboard.index');
    }


    /**
     * The login URL.
     *
     * @return string
     */
    protected function loginUrl()
    {
        return route('admin.login');
    }


    /**
     * Reset complete form route.
     *
     * @param User $user
     * @param string $code
     *
     * @return string
     */
    protected function resetCompleteRoute($user, $code)
    {
        return route('admin.reset.complete', [$user->email, $code]);
    }


    /**
     * Password reset complete view.
     *
     * @return string
     */
    protected function resetCompleteView()
    {
        return view('user::admin.auth.reset.complete');
    }
}

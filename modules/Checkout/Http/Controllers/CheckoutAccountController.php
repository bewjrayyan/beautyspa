<?php

namespace Modules\Checkout\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Modules\User\Contracts\Authentication;
use Modules\User\Entities\User;
use Modules\User\Http\Requests\LoginRequest;

class CheckoutAccountController extends Controller
{
    public function __construct(protected Authentication $auth)
    {
        $this->middleware('guest');
    }

    /**
     * Check whether the given email is already registered.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        return response()->json([
            'exists' => User::registered($request->email),
        ]);
    }

    /**
     * Log in a returning customer during checkout (JSON).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $loggedIn = $this->auth->login([
                'email' => $request->email,
                'password' => $request->password,
            ], (bool) $request->boolean('remember_me'));

            if (! $loggedIn) {
                return response()->json([
                    'message' => trans('user::messages.users.invalid_credentials'),
                ], 422);
            }

            return response()->json([
                'message' => trans('storefront::checkout.logged_in_successfully'),
                'redirect' => storefront_route('checkout.create'),
            ]);
        } catch (NotActivatedException $e) {
            return response()->json([
                'message' => trans('user::messages.users.account_not_activated'),
            ], 422);
        } catch (ThrottlingException $e) {
            return response()->json([
                'message' => trans('user::messages.users.account_is_blocked', [
                    'delay' => $e->getDelay(),
                ]),
            ], 429);
        }
    }
}

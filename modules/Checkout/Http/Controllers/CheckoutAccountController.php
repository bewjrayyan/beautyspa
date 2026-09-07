<?php

namespace Modules\Checkout\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Modules\User\Contracts\Authentication;
use Modules\User\Entities\User;
use Modules\User\Http\Requests\LoginRequest;
use Throwable;

class CheckoutAccountController extends Controller
{
    public function __construct(protected Authentication $auth)
    {
        $this->middleware('guest')->only('login');
    }

    /**
     * Check whether the given email is already registered.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email', 'max:255'],
            ]);

            $email = strtolower(trim((string) $validated['email']));

            return response()->json([
                'exists' => User::query()->where('email', $email)->exists(),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            // Degrade gracefully so checkout is never blocked by Redis/cache blips.
            return response()->json([
                'exists' => false,
                'degraded' => true,
            ]);
        }
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

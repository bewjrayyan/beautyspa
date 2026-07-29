<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Services\ConsultationCustomerAccess;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class ConsultationAccessController extends Controller
{
    public function __construct(private readonly ConsultationCustomerAccess $customerAccess)
    {
    }

    public function index(Request $request, string $token)
    {
        $submission = $this->findSubmissionByToken($token);

        if ($request->user()) {
            $this->customerAccess->claim($submission, $request->user());

            return redirect()->route(
                $submission->isCompleted()
                    ? 'account.consultations.show_submission'
                    : 'account.consultations.form',
                $submission
            );
        }

        return response()
            ->view('storefront::public.consultations.access', compact('submission'))
            ->withHeaders([
                'Cache-Control' => 'private, no-store, max-age=0',
                'Pragma' => 'no-cache',
                'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            ]);
    }

    public function lookup(Request $request, string $token): RedirectResponse
    {
        $submission = $this->findSubmissionByToken($token);
        $data = $request->validate(['identifier' => ['required', 'string', 'max:190']]);
        $identifier = trim($data['identifier']);
        $isEmail = str_contains($identifier, '@');

        if (
            ($isEmail && ! filter_var($identifier, FILTER_VALIDATE_EMAIL))
            || (! $isEmail && ! preg_match('/^\d{9,15}$/', PhoneNumber::normalize($identifier)))
        ) {
            return back()->withInput()->withErrors([
                'identifier' => trans('account::consultation.access.invalid_identifier'),
            ]);
        }

        if (! $this->customerAccess->identifierMatches($submission, $identifier, $isEmail)) {
            return back()->withInput()->withErrors([
                'identifier' => trans('account::consultation.access.not_match'),
            ]);
        }

        $user = $isEmail
            ? User::query()->where('email', mb_strtolower($identifier))->first()
            : User::findByPhone($identifier);

        $destination = route('consultations.access', ['token' => $token]);
        $request->session()->put('url.intended', $destination);

        if (! $user) {
            $parameters = $isEmail
                ? ['email' => $identifier, 'consultation_token' => $token]
                : ['phone' => $identifier, 'consultation_token' => $token];

            return redirect()->route('register', $parameters)
                ->with('error', trans('account::consultation.access.account_missing'));
        }

        return redirect()->route('login', array_filter([
            'email' => $isEmail ? $identifier : null,
            'login' => ! $isEmail && setting('whatsapp_otp_login_enabled') ? 'whatsapp' : null,
            'consultation_token' => $token,
        ]))->withSuccess(trans('account::consultation.access.account_found'));
    }

    private function findSubmissionByToken(string $token): ConsultationSubmission
    {
        $submission = ConsultationSubmission::query()
            ->select([
                'id',
                'user_id',
                'public_token_expires_at',
                'form_title',
                'customer_name',
                'customer_email',
                'customer_phone',
                'submitted_at',
                'revoked_at',
            ])
            ->where('public_token_hash', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->where('public_token_expires_at', '>', now())
            ->firstOrFail();

        return $submission;
    }
}

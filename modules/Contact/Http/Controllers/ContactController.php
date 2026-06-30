<?php

namespace Modules\Contact\Http\Controllers;

use Illuminate\Mail\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Modules\Contact\Http\Requests\ContactRequest;
use Modules\SpaBranch\Entities\SpaBranch;

class ContactController
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $spaBranches = $this->activeSpaBranches();

        return view('storefront::public.contact.create', [
            'spaBranches' => $spaBranches,
            'mapAddress' => $this->resolveMapAddress($spaBranches),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(ContactRequest $request)
    {
        Mail::raw($request->message, function (Message $message) use ($request) {
            $message->subject($request->subject)
                ->replyTo($request->email)
                ->to(setting('store_email'));
        });

        return back()->with('success', trans('contact::messages.your_message_has_been_sent'));
    }

    private function activeSpaBranches(): Collection
    {
        if (! app('modules')->isEnabled('SpaBranch')) {
            return collect();
        }

        return SpaBranch::activeForContact();
    }

    private function resolveMapAddress(Collection $spaBranches): ?string
    {
        if ($spaBranches->isNotEmpty()) {
            $address = trim((string) $spaBranches->first()->address);

            if ($address !== '') {
                return $address;
            }
        }

        $fallback = setting('storefront_address');

        return filled($fallback) ? (string) $fallback : null;
    }
}

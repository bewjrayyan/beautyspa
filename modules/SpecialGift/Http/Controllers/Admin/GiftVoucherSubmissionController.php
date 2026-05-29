<?php

namespace Modules\SpecialGift\Http\Controllers\Admin;

use Modules\SpecialGift\Entities\GiftVoucherSubmission;

class GiftVoucherSubmissionController
{
    public function index()
    {
        $submissions = GiftVoucherSubmission::query()
            ->latest()
            ->paginate(20);

        return view('specialgift::admin.submissions.index', compact('submissions'));
    }
}

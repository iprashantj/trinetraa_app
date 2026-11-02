<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrameBill;
use App\Support\Access;
use App\Support\Reminders;
use Illuminate\Http\Request;

/** Ask a customer for a Google review after a sale. */
class ReviewRequestController extends Controller
{
    public function store(Request $request, $id)
    {
        $bill = FrameBill::find($id);
        if (! $bill) {
            return $this->notFound();
        }
        if (! Access::canUseBranch(Access::admin($request), (int) $bill->branch_id)) {
            return $this->message('This bill belongs to another branch.', 403);
        }
        $r = Reminders::requestReview($bill);
        if (($r['error'] ?? null) === 'no_url') {
            return $this->message('Set the Google review link first: add GOOGLE_REVIEW_URL on the server, or the review link on the branch.', 422);
        }

        return $this->data($r);
    }
}

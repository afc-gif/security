<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\JobItemAttempt;
use App\Models\JobRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $availableJobs = JobRequestItem::query()
            ->available()
            ->with(['jobRequest.client', 'serviceCategory'])
            ->whereNotExists(function ($query) use ($userId) {
                $query->selectRaw('1')
                    ->from('job_item_attempts')
                    ->whereColumn('job_item_attempts.job_request_item_id', 'job_request_items.id')
                    ->where('job_item_attempts.user_id', $userId)
                    ->where('job_item_attempts.status', JobItemAttempt::STATUS_REJECTED);
            })
            ->latest('id')
            ->paginate(10, ['*'], 'available_page');

        $myJobs = JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory'])
            ->where('claimed_by', $userId)
            ->whereIn('status', [JobRequestItem::STATUS_CLAIMED, JobRequestItem::STATUS_SUBMITTED])
            ->latest('claimed_at')
            ->latest('id')
            ->paginate(10, ['*'], 'my_page');

        return view('field.jobs.index', compact('availableJobs', 'myJobs'));
    }

    public function claim(Request $request, JobRequestItem $jobItem)
    {
        DB::transaction(function () use ($jobItem, $request) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->whereIn('status', [JobRequestItem::STATUS_OPEN, JobRequestItem::STATUS_REOPENED])
                ->lockForUpdate()
                ->first();

            if (!$lockedItem || $lockedItem->claimed_by !== null) {
                abort(409, 'This job has already been claimed or is no longer available.');
            }

            $lockedItem->update([
                'claimed_by' => $request->user()->id,
                'claimed_at' => now(),
                'status' => JobRequestItem::STATUS_CLAIMED,
            ]);
        });

        return redirect()
            ->route('field.jobs.index')
            ->with('success', 'Job claimed successfully.');
    }
}

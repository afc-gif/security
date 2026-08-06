<?php

namespace App\Http\Controllers\FieldCoordinator;

use App\Http\Controllers\Controller;
use App\Models\JobRequestItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JobAssignmentController extends Controller
{
    public function index()
    {
        $pendingJobs = JobRequestItem::query()
            ->with(['jobRequest.client', 'serviceCategory', 'creator'])
            ->where('status', JobRequestItem::STATUS_PENDING_ASSIGNMENT)
            ->latest('id')
            ->paginate(15);

        $fieldStaff = User::query()
            ->where('status', 'approved')
            ->whereIn('role', ['field_staff', 'field_coordinator'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('coordinator.jobs.index', compact('pendingJobs', 'fieldStaff'));
    }

    public function assign(Request $request, JobRequestItem $jobItem)
    {
        $validated = $request->validate([
            'assigned_to' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('status', 'approved')
                    ->whereIn('role', ['field_staff', 'field_coordinator'])),
            ],
        ]);

        DB::transaction(function () use ($jobItem, $validated) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->where('status', JobRequestItem::STATUS_PENDING_ASSIGNMENT)
                ->whereNull('claimed_by')
                ->lockForUpdate()
                ->first();

            if (!$lockedItem) {
                abort(409, 'This job is no longer pending assignment.');
            }

            $lockedItem->update([
                'claimed_by' => $validated['assigned_to'],
                'claimed_at' => now(),
                'status' => JobRequestItem::STATUS_CLAIMED,
            ]);
        });

        return redirect()
            ->route('coordinator.jobs.index')
            ->with('success', 'Job assigned successfully.');
    }

    public function claim(JobRequestItem $jobItem)
    {
        DB::transaction(function () use ($jobItem) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->where('status', JobRequestItem::STATUS_PENDING_ASSIGNMENT)
                ->whereNull('claimed_by')
                ->lockForUpdate()
                ->first();

            if (!$lockedItem) {
                abort(409, 'This job is no longer pending assignment.');
            }

            $lockedItem->update([
                'claimed_by' => auth()->id(),
                'claimed_at' => now(),
                'status' => JobRequestItem::STATUS_CLAIMED,
            ]);
        });

        return redirect()
            ->route('coordinator.jobs.index')
            ->with('success', 'Job assigned to you.');
    }

    public function release(JobRequestItem $jobItem)
    {
        DB::transaction(function () use ($jobItem) {
            $lockedItem = JobRequestItem::query()
                ->where('id', $jobItem->id)
                ->where('status', JobRequestItem::STATUS_PENDING_ASSIGNMENT)
                ->whereNull('claimed_by')
                ->lockForUpdate()
                ->first();

            if (!$lockedItem) {
                abort(409, 'This job is no longer pending assignment.');
            }

            $lockedItem->update([
                'status' => JobRequestItem::STATUS_OPEN,
            ]);
        });

        return redirect()
            ->route('coordinator.jobs.index')
            ->with('success', 'Job released for field staff claim.');
    }
}

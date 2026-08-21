<?php

namespace App\Console\Commands;

use App\Models\JobRequestItem;
use App\Notifications\GenericWebPush;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendJobDeadlineNotifications extends Command
{
    protected $signature = 'notify:job-deadlines';
    protected $description = 'Send push notifications for jobs due in less than 24 hours';

    public function handle()
    {
        $approachingJobs = JobRequestItem::query()
            ->with('claimer')
            ->whereNotNull('claimed_by')
            ->whereIn('status', [JobRequestItem::STATUS_CLAIMED, JobRequestItem::STATUS_RETURNED])
            ->whereNotNull('due_date')
            ->where('due_date', '>', now())
            ->where('due_date', '<=', now()->addHours(24))
            ->get();

        $this->info("Found {$approachingJobs->count()} jobs approaching deadline.");

        foreach ($approachingJobs as $job) {
            if ($job->claimer) {
                try {
                    $hoursLeft = round(now()->diffInMinutes($job->due_date) / 60, 1);
                    $job->claimer->notify(new GenericWebPush(
                        'Job Deadline Approaching',
                        "Your assigned job '{$job->jobRequest?->title}' is due in {$hoursLeft} hours.",
                        route('field.jobs.show', $job)
                    ));
                    $this->info("Notified {$job->claimer->name} for job ID {$job->id}");
                } catch (\Exception $e) {
                    Log::error("Failed sending deadline push to user {$job->claimer->id}: " . $e->getMessage());
                }
            }
        }
    }
}

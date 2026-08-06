<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\JobRequestItem;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TransportFareNotificationService
{
    public function notifyAssignedJob(JobRequestItem $jobItem, User $assignedStaff): string
    {
        $jobItem->loadMissing(['jobRequest.client', 'serviceCategory']);

        $client = $jobItem->jobRequest?->client;
        $jobName = $jobItem->jobRequest?->title ?? $jobItem->title ?? 'Assigned job';
        $clientName = $client?->client_name ?? 'Client unavailable';
        $location = $this->locationFor($jobItem);

        $title = 'Transport fare required';
        $message = implode("\n", [
            'Transport fare is urgently required for assigned field staff to move.',
            '',
            'Job: ' . $jobName,
            'Service: ' . ($jobItem->serviceCategory?->name ?? $jobItem->title ?? 'Service category unavailable'),
            'Assigned staff: ' . $assignedStaff->name,
            'Client: ' . $clientName,
            'Location: ' . $location,
        ]);

        $data = [
            'job_request_item_id' => $jobItem->id,
            'job_request_id' => $jobItem->job_request_id,
            'job_name' => $jobName,
            'assigned_staff_id' => $assignedStaff->id,
            'assigned_staff_name' => $assignedStaff->name,
            'client_name' => $clientName,
            'location' => $location,
        ];

        User::query()
            ->where('role', 'admin')
            ->where('status', 'approved')
            ->get(['id'])
            ->each(fn (User $admin) => AdminNotification::create([
                'user_id' => $admin->id,
                'type' => 'transport_fare_required',
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]));

        Log::info('Transport fare admin notification recorded.', $data);

        return $this->whatsappUrl($message);
    }

    private function locationFor(JobRequestItem $jobItem): string
    {
        $client = $jobItem->jobRequest?->client;

        return collect([
            $client?->address,
            $client?->city_state,
        ])
            ->filter(fn ($value) => trim((string) $value) !== '')
            ->implode(', ') ?: 'Location not specified';
    }

    private function whatsappUrl(string $message): string
    {
        $number = preg_replace('/\D+/', '', (string) config('services.whatsapp.admin_number', '2349160450776'));

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
    }
}

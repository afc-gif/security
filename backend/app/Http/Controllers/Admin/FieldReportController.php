<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\ProjectUpdate;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FieldReportController extends Controller
{
    private const INSPECTION_REVIEW_STATUSES = ['pending_review', 'approved', 'rejected'];
    private const PROJECT_UPDATE_REVIEW_STATUSES = ['pending_review', 'reviewed', 'needs_correction'];
    private const TYPES = ['all', 'inspections', 'project_updates', 'tasks'];
    private const REVIEW_STATUSES = ['all', 'pending_review', 'approved', 'rejected', 'reviewed', 'needs_correction'];
    private const QUICK_RANGES = ['today', 'this_week', 'this_month'];

    public function index(Request $request)
    {
        try {
            $filters = $this->filters($request);
            $fieldStaff = User::where('role', 'field_staff')
                ->orderBy('name')
                ->get(['id', 'name']);

            $pendingInspectionReviewsQuery = $this->tableExists('inspections')
                ? $this->inspectionQuery($filters, 'pending_review')
                : null;
            $recentInspectionSubmissionsQuery = $this->tableExists('inspections')
                ? $this->inspectionQuery($filters)
                : null;
            $recentProjectUpdatesQuery = $this->tableExists('project_updates')
                ? $this->projectUpdateQuery($filters)
                : null;
            $projectUpdatesNeedingCorrectionQuery = $this->tableExists('project_updates')
                ? $this->projectUpdateQuery($filters, 'needs_correction')
                : null;
            $recentlyCompletedTasksQuery = $this->tableExists('tasks')
                ? $this->completedTaskQuery($filters)
                : null;

            $pendingInspectionReviewsCount = $this->queryCount($pendingInspectionReviewsQuery);
            $recentProjectUpdatesCount = $this->queryCount($recentProjectUpdatesQuery);
            $projectUpdatesNeedingCorrectionCount = $this->queryCount($projectUpdatesNeedingCorrectionQuery);
            $completedTasksCount = $this->queryCount($recentlyCompletedTasksQuery);

            $pendingInspectionReviews = $pendingInspectionReviewsQuery
                ? $pendingInspectionReviewsQuery->latest('submitted_at')->limit(10)->get()
                : collect();

            $recentInspectionSubmissions = $recentInspectionSubmissionsQuery
                ? $recentInspectionSubmissionsQuery->latest('submitted_at')->limit(10)->get()
                : collect();

            $recentProjectUpdates = $recentProjectUpdatesQuery
                ? $recentProjectUpdatesQuery->latest('created_at')->limit(10)->get()
                : collect();

            $projectUpdatesNeedingCorrection = $projectUpdatesNeedingCorrectionQuery
                ? $projectUpdatesNeedingCorrectionQuery
                    ->when($this->columnExists('project_updates', 'reviewed_at'), fn (Builder $q) => $q->latest('reviewed_at'))
                    ->latest('created_at')
                    ->limit(10)
                    ->get()
                : collect();

            $recentlyCompletedTasks = $recentlyCompletedTasksQuery
                ? $recentlyCompletedTasksQuery->latest('completed_at')->limit(10)->get()
                : collect();

            $activityFeed = $this->buildActivityFeed(
                $recentInspectionSubmissions,
                $recentProjectUpdates,
                $recentlyCompletedTasks
            );

            $activeFilters = $this->activeFilterLabels($filters, $fieldStaff);

            return view('admin.field-reports.index', compact(
                'filters',
                'fieldStaff',
                'activeFilters',
                'pendingInspectionReviewsCount',
                'pendingInspectionReviews',
                'recentInspectionSubmissions',
                'recentProjectUpdatesCount',
                'recentProjectUpdates',
                'projectUpdatesNeedingCorrectionCount',
                'projectUpdatesNeedingCorrection',
                'completedTasksCount',
                'recentlyCompletedTasks',
                'activityFeed'
            ));
        } catch (\Exception $e) {
            Log::error('Field reports page error: ' . $e->getMessage());
            return view('admin.field-reports.index', [
                'filters' => [],
                'fieldStaff' => collect(),
                'activeFilters' => [],
                'pendingInspectionReviewsCount' => 0,
                'pendingInspectionReviews' => collect(),
                'recentInspectionSubmissions' => collect(),
                'recentProjectUpdatesCount' => 0,
                'recentProjectUpdates' => collect(),
                'projectUpdatesNeedingCorrectionCount' => 0,
                'projectUpdatesNeedingCorrection' => collect(),
                'completedTasksCount' => 0,
                'recentlyCompletedTasks' => collect(),
                'activityFeed' => collect(),
                'databaseError' => true,
            ]);
        }
    }

    private function queryCount(?Builder $query): int
    {
        try {
            return $query ? (clone $query)->count() : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            return $this->tableExists($table) && Schema::hasColumn($table, $column);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function filters(Request $request): array
    {
        $quickRange = in_array($request->input('quick_range'), self::QUICK_RANGES, true)
            ? $request->input('quick_range')
            : null;

        [$startAt, $endAt] = $this->dateRange(
            $quickRange,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return [
            'start_date' => $startAt?->toDateString(),
            'end_date' => $endAt?->toDateString(),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'quick_range' => $quickRange,
            'field_staff_id' => $request->filled('field_staff_id') ? (int) $request->input('field_staff_id') : null,
            'type' => in_array($request->input('type'), self::TYPES, true) ? $request->input('type') : 'all',
            'review_status' => in_array($request->input('review_status'), self::REVIEW_STATUSES, true) ? $request->input('review_status') : 'all',
            'search' => trim((string) $request->input('search', '')),
        ];
    }

    private function dateRange(?string $quickRange, ?string $startDate, ?string $endDate): array
    {
        if ($quickRange === 'today') {
            return [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()];
        }

        if ($quickRange === 'this_week') {
            return [Carbon::now()->startOfWeek()->startOfDay(), Carbon::now()->endOfWeek()->endOfDay()];
        }

        if ($quickRange === 'this_month') {
            return [Carbon::now()->startOfMonth()->startOfDay(), Carbon::now()->endOfMonth()->endOfDay()];
        }

        $startAt = $this->parseDate($startDate, false);
        $endAt = $this->parseDate($endDate, true);

        if ($startAt && $endAt && $endAt->lt($startAt)) {
            [$startAt, $endAt] = [$endAt->copy()->startOfDay(), $startAt->copy()->endOfDay()];
        }

        return [$startAt, $endAt];
    }

    private function parseDate(?string $date, bool $endOfDay): ?Carbon
    {
        if (!$date) {
            return null;
        }

        try {
            $parsed = Carbon::parse($date);

            return $endOfDay ? $parsed->endOfDay() : $parsed->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function inspectionQuery(array $filters, ?string $forcedReviewStatus = null): Builder
    {
        $query = Inspection::with(['client', 'assignedUser'])
            ->whereNotNull('submitted_at');

        if (!$this->typeAllows($filters, 'inspections')) {
            return $query->whereRaw('1 = 0');
        }

        $this->applyDateRange($query, 'submitted_at', $filters);

        $query
            ->when($filters['field_staff_id'], fn (Builder $q, int $staffId) => $q->where('assigned_to', $staffId))
            ->when($filters['search'] !== '', function (Builder $q) use ($filters) {
                $search = '%' . $filters['search'] . '%';

                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('title', 'like', $search)
                        ->orWhere('inspection_code', 'like', $search);
                });
            });

        return $this->applyReviewStatus($query, $filters, self::INSPECTION_REVIEW_STATUSES, $forcedReviewStatus);
    }

    private function projectUpdateQuery(array $filters, ?string $forcedReviewStatus = null): Builder
    {
        $query = ProjectUpdate::with(['project', 'user']);

        if (!$this->typeAllows($filters, 'project_updates')) {
            return $query->whereRaw('1 = 0');
        }

        $this->applyDateRange($query, 'created_at', $filters);

        $query
            ->when($filters['field_staff_id'], fn (Builder $q, int $staffId) => $q->where('user_id', $staffId))
            ->when($filters['search'] !== '', function (Builder $q) use ($filters) {
                $search = '%' . $filters['search'] . '%';

                $q->whereHas('project', function (Builder $projectQuery) use ($search) {
                    $projectQuery->where('title', 'like', $search)
                        ->orWhere('project_code', 'like', $search);
                });
            });

        return $this->applyReviewStatus($query, $filters, self::PROJECT_UPDATE_REVIEW_STATUSES, $forcedReviewStatus);
    }

    private function completedTaskQuery(array $filters): Builder
    {
        $query = Task::with(['assignable', 'assignee'])
            ->where('status', 'completed');

        if (!$this->typeAllows($filters, 'tasks') || $filters['review_status'] !== 'all') {
            return $query->whereRaw('1 = 0');
        }

        $this->applyEitherDateRange($query, ['completed_at', 'updated_at'], $filters);

        return $query
            ->when($filters['field_staff_id'], fn (Builder $q, int $staffId) => $q->where('assigned_to', $staffId))
            ->when($filters['search'] !== '', function (Builder $q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%');
            });
    }

    private function applyReviewStatus(Builder $query, array $filters, array $allowedStatuses, ?string $forcedReviewStatus = null): Builder
    {
        $selectedStatus = $filters['review_status'];

        if ($forcedReviewStatus && $selectedStatus !== 'all' && $selectedStatus !== $forcedReviewStatus) {
            return $query->whereRaw('1 = 0');
        }

        if ($forcedReviewStatus) {
            return $query->where('review_status', $forcedReviewStatus);
        }

        if ($selectedStatus !== 'all' && !in_array($selectedStatus, $allowedStatuses, true)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->when($selectedStatus !== 'all', fn (Builder $q) => $q->where('review_status', $selectedStatus));
    }

    private function applyDateRange(Builder $query, string $column, array $filters): void
    {
        $query
            ->when($filters['start_at'], fn (Builder $q, Carbon $startAt) => $q->where($column, '>=', $startAt))
            ->when($filters['end_at'], fn (Builder $q, Carbon $endAt) => $q->where($column, '<=', $endAt));
    }

    private function applyEitherDateRange(Builder $query, array $columns, array $filters): void
    {
        if (!$filters['start_at'] && !$filters['end_at']) {
            return;
        }

        $query->where(function (Builder $dateQuery) use ($columns, $filters) {
            foreach ($columns as $column) {
                $method = $column === $columns[0] ? 'where' : 'orWhere';

                $dateQuery->{$method}(function (Builder $columnQuery) use ($column, $filters) {
                    $this->applyDateRange($columnQuery, $column, $filters);
                });
            }
        });
    }

    private function typeAllows(array $filters, string $type): bool
    {
        return $filters['type'] === 'all' || $filters['type'] === $type;
    }

    private function activeFilterLabels(array $filters, Collection $fieldStaff): array
    {
        $labels = [];

        if ($filters['quick_range']) {
            $labels[] = [
                'label' => 'Date',
                'value' => match ($filters['quick_range']) {
                    'today' => 'Today',
                    'this_week' => 'This Week',
                    'this_month' => 'This Month',
                },
            ];
        } elseif ($filters['start_date'] || $filters['end_date']) {
            $labels[] = [
                'label' => 'Date',
                'value' => trim(($filters['start_date'] ?: 'Any time') . ' to ' . ($filters['end_date'] ?: 'Now')),
            ];
        }

        if ($filters['field_staff_id']) {
            $labels[] = [
                'label' => 'Staff',
                'value' => $fieldStaff->firstWhere('id', $filters['field_staff_id'])?->name ?? 'Selected staff',
            ];
        }

        if ($filters['type'] !== 'all') {
            $labels[] = [
                'label' => 'Type',
                'value' => match ($filters['type']) {
                    'inspections' => 'Inspections',
                    'project_updates' => 'Project Updates',
                    'tasks' => 'Tasks',
                },
            ];
        }

        if ($filters['review_status'] !== 'all') {
            $labels[] = [
                'label' => 'Review',
                'value' => str_replace('_', ' ', ucwords($filters['review_status'], '_')),
            ];
        }

        if ($filters['search'] !== '') {
            $labels[] = [
                'label' => 'Search',
                'value' => $filters['search'],
            ];
        }

        return $labels;
    }

    private function buildActivityFeed(Collection $inspections, Collection $updates, Collection $tasks): Collection
    {
        return collect()
            ->merge($inspections->map(fn (Inspection $inspection) => [
                'type' => 'Inspection Submission',
                'title' => trim($inspection->inspection_code . ' — ' . $inspection->title, ' —') ?: 'Inspection record unavailable',
                'user' => $inspection->assignedUser?->name ?? '—',
                'timestamp' => $inspection->submitted_at,
                'link' => route('admin.inspections.show', $inspection),
            ]))
            ->merge($updates->map(fn (ProjectUpdate $update) => [
                'type' => 'Project Update',
                'title' => ($update->project?->project_code ?? 'Project unavailable') . ' — ' . ($update->summary ?: 'Project update'),
                'user' => $update->user?->name ?? '—',
                'timestamp' => $update->created_at,
                'link' => $update->project ? route('admin.projects.show', $update->project) : null,
            ]))
            ->merge($tasks->map(fn (Task $task) => [
                'type' => 'Task Completed',
                'title' => $task->title ?: 'Task record unavailable',
                'user' => $task->assignee?->name ?? '—',
                'timestamp' => $task->completed_at,
                'link' => route('admin.tasks.show', $task),
            ]))
            ->filter(fn (array $item) => $item['timestamp'] !== null)
            ->sortByDesc('timestamp')
            ->take(15)
            ->values();
    }
}

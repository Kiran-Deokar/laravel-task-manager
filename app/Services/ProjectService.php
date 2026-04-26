<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ProjectService
{
  public function getAll()
{
    $today = now()->toDateString();

    $taskStats = DB::table('tasks')
        ->select(
            'project_id',
            DB::raw('COUNT(*) as tasks_count'),
            DB::raw("SUM(CASE WHEN status = 'DONE' THEN 1 ELSE 0 END) as completed_tasks_count"),
            DB::raw("SUM(CASE WHEN status != 'DONE' AND due_date < '{$today}' THEN 1 ELSE 0 END) as overdue_tasks_count")
        )
        ->groupBy('project_id');

    $projects = DB::table('projects as p')
        ->select(
            'p.project_id',
            'p.name',
            'p.description',
            'p.created_at',

            'u.name as creator_name',
            'u.role as creator_role',

            DB::raw('COALESCE(ts.tasks_count, 0) as tasks_count'),
            DB::raw('COALESCE(ts.completed_tasks_count, 0) as completed_tasks_count'),
            DB::raw('COALESCE(ts.overdue_tasks_count, 0) as overdue_tasks_count')
        )
        ->leftJoin('users as u', 'u.user_id', '=', 'p.created_by')

        // 🔥 Join aggregated result instead of raw tasks
        ->leftJoinSub($taskStats, 'ts', function ($join) {
            $join->on('ts.project_id', '=', 'p.project_id');
        })

        ->orderByDesc('p.created_at')
        ->get();

    return $projects->map(function ($project) {
        $project->progress_percentage = $project->tasks_count > 0
            ? round(($project->completed_tasks_count / $project->tasks_count) * 100, 2)
            : 0;

        return $project;
    });
}

    public function create(array $data, int $userId): Project
    {
        $data['created_by'] = $userId;

        return Project::create($data);
    }

    public function getById(Project $project): Project
{
    return $project->load([
        'tasks.assignee:user_id,name,role'
    ]);
}

public function getDashboardData(int $userId, bool $isAdmin)
{
    // -------------------------
    // 1. LOAD PROJECTS (EAGER LOADING)
    // -------------------------
    $projects = Project::with([
        'tasks.assignee:user_id,name',
        'creator:user_id,name'
    ])
    ->orderByDesc('created_at')
    ->get();

    // -------------------------
    // 2. FLATTEN TASKS
    // -------------------------
    $allTasks = $projects->flatMap(function ($project) {
        return $project->tasks->map(function ($task) use ($project) {
            return [
                'id' => $task->task_id,
                'title' => $task->title,
                'status' => $task->status,
                'due_date' => $task->due_date,
                'assigned_to' => $task->assigned_to_user_id,
                'assignee_name' => $task->assignee?->name,
                'project_id' => $project->project_id,
                'projectName' => $project->name,
            ];
        });
    });

    // -------------------------
    // 3. FILTER NON-ADMIN TASKS
    // -------------------------
    if (!$isAdmin) {
        $allTasks = $allTasks->where('assigned_to', $userId);
    }

    // -------------------------
    // 4. OVERDUE CHECK (SINGLE SOURCE OF TRUTH)
    // -------------------------
    $checked = collect($this->checkOverdue($allTasks->values()->all()));

    // -------------------------
    // 5. STATS (FIXED)
    // -------------------------
    $stats = [
        'total' => $checked->count(),
        'active' => $checked->where('status', 'in_progress')->count(),
        'done' => $checked->where('status', 'done')->count(),
        'overdue' => $checked->where('status', 'OVERDUE')->count(),
    ];

    // -------------------------
    // 6. RECENT TASKS
    // -------------------------
    $recentTasks = $checked
        ->sortByDesc('id')
        ->take(8)
        ->values();

    // -------------------------
    // 7. PROJECT PROGRESS (FIXED)
    // -------------------------
    $projects = $projects->map(function ($project) use ($checked) {

        $projectTasks = $checked->where('project_id', $project->project_id);

        $total = $projectTasks->count();
        $done = $projectTasks->where('status', 'done')->count();
        $overdue = $projectTasks->where('status', 'OVERDUE')->count();

        $project->tasks_count = $total;
        $project->completed_tasks_count = $done;
        $project->overdue_tasks_count = $overdue;

        $project->progress_percentage = $total > 0
            ? round(($done / $total) * 100, 2)
            : 0;

        return $project;
    });

    // -------------------------
    // FINAL RESPONSE
    // -------------------------
    return [
        'stats' => $stats,
        'projects' => $projects,
        'recent_tasks' => $recentTasks,
    ];
}

private function checkOverdue(array $tasks)
    {
        $today = now()->toDateString();

        return array_map(function ($task) use ($today) {
            if (
                $task['status'] !== 'done' &&
                isset($task['due_date']) &&
                $task['due_date'] < $today
            ) {
                $task['status'] = 'OVERDUE';
            }
            return $task;
        }, $tasks);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);
        return $project;
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
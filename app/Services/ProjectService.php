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
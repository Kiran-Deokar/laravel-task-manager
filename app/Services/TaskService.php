<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function getAll($user): Collection
    {
        $query = Task::with([
            'project:project_id,name',
            'assignee:user_id,name'
        ]);

        if (!$user->isAdmin()) {
            $query->where('assigned_to_user_id', $user->user_id);
        }

        $tasks = $query->latest()->get();

        foreach ($tasks as $task) {
            $this->syncOverdueStatus($task);
        }

        return $tasks;
    }

    public function create(array $data, int $userId): Task
    {
        $data['created_by'] = $userId;

        return Task::create($data);
    }

    public function update(Task $task, array $data, $user): Task
    {
        if (!$user->isAdmin() && $task->assigned_to_user_id != $user->user_id) {
            throw new \Exception('This task is not assigned to you.', 403);
        }

        if (isset($data['status'])) {
            $isValid = $this->validateStatusChange($task, $data['status'], $user->isAdmin());

            if (!$isValid) {
                throw new \Exception('Invalid status transition. Check overdue rules.', 422);
            }
        }

        $task->update($data);

        return $task;
    }

    public function changeStatus(Task $task, string $newStatus, $user): Task
{
    if (!$user->isAdmin() && $task->assigned_to_user_id != $user->user_id) {
        throw new \Exception('This task is not assigned to you.', 403);
    }

    $isValid = $this->validateStatusChange($task, $newStatus, $user->isAdmin());

    if (!$isValid) {
        throw new \Exception('Invalid status transition. Check overdue rules.', 422);
    }

    $task->status = $newStatus;
    $task->save();

    return $task;
}

    public function delete(Task $task): void
    {
        $task->delete();
    }

    /**
     * Sync overdue status
     */
    public function syncOverdueStatus(Task $task): void
    {
        if ($task->status !== 'DONE' && $task->due_date->isPast()) {
            $task->status = 'OVERDUE';
            $task->saveQuietly();
        }
    }

    /**
     * Validate status transitions
     */
    public function validateStatusChange(Task $task, string $newStatus, bool $isAdmin): bool
    {
        $this->syncOverdueStatus($task);

        if ($task->status === 'OVERDUE' && $newStatus === 'IN_PROGRESS') {
            return false;
        }

        if ($task->status === 'OVERDUE' && $newStatus === 'DONE' && !$isAdmin) {
            return false;
        }

        return true;
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class TaskController extends Controller implements HasMiddleware
{
    protected TaskService $taskService;

    public function __construct(TaskService $service)
    {
        $this->taskService = $service;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('role:admin', only: ['store', 'destroy']),
        ];
    }

    public function index()
    {
        $tasks = $this->taskService->getAll(auth()->user());

        return ApiResponse::success($tasks, 'Tasks fetched successfully');
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->create(
            $request->validated(),
            $request->user()->user_id
        );

        return ApiResponse::success($task, 'Task created successfully', 201);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        try {
            $task = $this->taskService->update(
                $task,
                $request->validated(),
                $request->user()
            );

            return ApiResponse::success($task, 'Task updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), null, $e->getCode() ?: 400);
        }
    }

    public function changeStatus(Request $request, Task $task)
{
    $request->validate([
        'status' => 'required|string|in:TODO,IN_PROGRESS,DONE,OVERDUE',
    ]);

    try {
        $task = $this->taskService->changeStatus(
            $task,
            $request->status,
            $request->user()
        );

        return ApiResponse::success($task, 'Status updated successfully');
    } catch (\Exception $e) {
        return ApiResponse::error($e->getMessage(), null, $e->getCode() ?: 400);
    }
}

    public function destroy(Task $task)
    {
        $this->taskService->delete($task);

        return ApiResponse::success(null, 'Task deleted successfully');
    }
}
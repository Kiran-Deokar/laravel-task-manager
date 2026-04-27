<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Helpers\ApiResponse;
use App\Services\ProjectService;

class ProjectController extends Controller implements HasMiddleware
{
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('role:admin', only: ['store', 'update', 'destroy']),
        ];
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        $data = $this->projectService->getDashboardData(
            $user->user_id,
            $user->role === 'admin'
        );

        return ApiResponse::success($data, 'Dashboard data fetched successfully');
    }

    public function index()
    {
        $projects = $this->projectService->getAll();

        return ApiResponse::success($projects, 'Projects fetched successfully');
    }

       public function store(StoreProjectRequest $request)
{
    $project = $this->projectService->create(
        $request->validated(),
        $request->user()->user_id
    );

    return ApiResponse::success($project, 'Project created successfully', 201);
}

    public function show(Project $project)
    {
        $project = $this->projectService->getById($project);

        return ApiResponse::success($project, 'Project fetched successfully');
    }

 

public function update(UpdateProjectRequest $request, Project $project)
{
    $project = $this->projectService->update(
        $project,
        $request->validated()
    );

    return ApiResponse::success($project, 'Project updated successfully');
}

    public function destroy(Project $project)
    {
        $this->projectService->delete($project);

        return ApiResponse::success(null, 'Project deleted successfully');
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    /**
     * Display all accessible projects.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'start_date',
            'due_date',
            'with_tasks',
            'per_page'
        ]);
    
        $projects = $this->projectService->getAll($request->user(), $filters);
    
        return $this->ok(
            ProjectResource::collection($projects),
            'Projects fetched successfully.'
        );
    }

    /**
     * Display a specific project.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $project = $this->projectService->getById(
            $request->user(),
            $id
        );

        if (!$project) {
            return $this->fail(
                'Project not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->ok(
            new ProjectResource($project),
            'Project fetched successfully.'
        );
    }

    /**
     * Create a new project.
     */
    public function store(ProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->create(
            $request->validated()
        );

        return $this->ok(
            new ProjectResource($project),
            'Project created successfully.'
        );
    }

    /**
     * Update a project.
     */
    public function update(ProjectRequest $request, string $id): JsonResponse
    {
        $project = $this->projectService->getById(
            $request->user(),
            $id
        );

        if (!$project) {
            return $this->fail(
                'Project not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $project = $this->projectService->update(
            $project,
            $request->validated()
        );

        return $this->ok(
            new ProjectResource($project),
            'Project updated successfully.'
        );
    }

    /**
     * Delete a project.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $project = $this->projectService->getById(
            $request->user(),
            $id
        );
    
        if (!$project) {
            return $this->fail(
                'Project not found.',
                Response::HTTP_NOT_FOUND
            );
        }
    
        $this->projectService->delete($project);
    
        return $this->ok(
            null,
            'Project deleted successfully.'
        );
    }
}
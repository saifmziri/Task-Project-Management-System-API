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
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }




    
    /**
     * عرض جميع المشاريع
     */
    public function index(Request $request): JsonResponse
    {
        $withTasks = $request->boolean('with_tasks', false);

        $projects = $this->projectService->getAll($withTasks);

        return $this->ok(ProjectResource::collection($projects), 'Projects fetched successfully.');
    }

    /**
     * إنشاء مشروع جديد
     */
    public function store(ProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->create(
            $request->validated(),
            $request->user()?->id
        );

        return $this->ok(new ProjectResource($project), 'Project created successfully.');
    }

    /**
     * عرض مشروع محدد
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $withTasks = $request->boolean('with_tasks', false);

        $project = $this->projectService->getById($id, $withTasks);

        if (!$project) {
            return $this->fail('Project not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->ok(new ProjectResource($project), 'Project details fetched successfully.');
    }

    /**
     * تحديث مشروع
     */
    public function update(ProjectRequest $request, string $id): JsonResponse
    {
        $project = $this->projectService->getById($id);

        if (!$project) {
            return $this->fail('Project not found.', Response::HTTP_NOT_FOUND);
        }

        $updatedProject = $this->projectService->update($project, $request->validated());

        return $this->ok(new ProjectResource($updatedProject), 'Project updated successfully.');
    }

    /**
     * حذف مشروع
     */
    public function destroy(string $id): JsonResponse
    {
        $project = $this->projectService->getById($id);

        if (!$project) {
            return $this->fail('Project not found.', Response::HTTP_NOT_FOUND);
        }

        $this->projectService->delete($project);

        return $this->ok(null, 'Project deleted successfully.');
    }
}
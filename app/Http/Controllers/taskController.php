<?php

namespace App\Http\Controllers;

use App\Http\Requests\task\ChangeTaskStatusRequest;
use App\Http\Requests\task\TaskRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    /**
     * Display all accessible tasks.
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getAll($request->user());

        return $this->ok(
            $tasks,
            'Tasks fetched successfully.'
        );
    }

    /**
     * Store a newly created task.
     */
    public function store(TaskRequest $request): JsonResponse
    {
        $task = $this->taskService->create(
            $request->validated(),
        );

        return $this->ok(
            $task,
            'Task created successfully.'
        );
    }

    /**
     * Display the specified task.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $task = $this->taskService->getById(
            $request->user(),
            $id
        );

        if (!$task) {
            return $this->fail(
                'Task not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->ok(
            $task,
            'Task fetched successfully.'
        );
    }

    /**
     * Update the specified task.
     */
    public function update(TaskRequest $request, string $id): JsonResponse
    {
        $task = $this->taskService->getById(
            $request->user(),
            $id
        );

        if (!$task) {
            return $this->fail(
                'Task not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $task = $this->taskService->update(
            $task,
            $request->validated()
        );

        return $this->ok(
            $task,
            'Task updated successfully.'
        );
    }

    /**
     * Change task status.
     */
    public function changeStatus(ChangeTaskStatusRequest $request, string $id): JsonResponse
    {

        $task = $this->taskService->getById(
            $request->user(),
            $id
        );

        if (!$task) {
            return $this->fail(
                'Task not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $task = $this->taskService->changeStatus(
            $task,
            $request->validated()['status']
        );

        if(!$task) {
            return $this->fail(
                "Task is already {$request->validated()['status']}.",
                null,
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->ok(
            $task,
            'Task status updated successfully.'
        );
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $task = $this->taskService->getById(
            $request->user(),
            $id
        );

        if (!$task) {
            return $this->fail(
                'Task not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $this->taskService->delete($task);

        return $this->ok(
            null,
            'Task deleted successfully.'
        );
    }
}
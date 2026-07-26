<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    /**
     * Get all accessible tasks.
     */
    public function getAll(User $user, array $filters = []): LengthAwarePaginator
    {
        return Task::query()
            ->with(['project', 'user']) 
            ->filter($user, $filters)
            ->paginate($filters['per_page'] ?? 15);
    }
    /**
     * Get one accessible task.
     */
    public function getById(User $user, int|string $id): ?Task
    {
        $query = Task::query();

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query->whereKey($id)->first();
    }

    /**
     * Create task.
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Update task.
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }

    /**
     * Delete task.
     */
    public function delete(Task $task): bool
    {
        return (bool) $task->delete();
    }

    /**
     * Change task status.
     */
    public function changeStatus(Task $task, string $status): ?Task
    {
        if($task->status === $status) {
            return null;
        }

        $task->update([
            'status' => $status,
        ]);

        return $task->fresh();
    }
}
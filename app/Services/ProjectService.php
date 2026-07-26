<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\ProjectHasTasksException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    /**
     * Get all accessible projects.
     */
    public function getAll(User $user, array $filters = []): LengthAwarePaginator
    {
        return Project::query()
            ->filter($filters, $user)
            ->when($filters['with_tasks'] ?? false, function ($query) use ($user) {
                $query->with(['tasks' => function ($taskQuery) use ($user) {
                    
                    if (!$user->isAdmin()) {
                        $taskQuery->where('user_id', $user->id);
                    }
                }]);
            })
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get one accessible project.
     */
    public function getById(User $user, int|string $id): ?Project
    {
        $query = Project::query();

        if (!$user->isAdmin()) {
            $query->whereHas('tasks', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }

        return $query
            ->whereKey($id)
            ->first();
    }

    /**
     * Create project.
     */
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    /**
     * Update project.
     */
    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh();
    }

    /**
     * Delete project.
     */
    public function delete(Project $project): bool
    {
        if ($project->tasks()->exists()) {
            throw new ProjectHasTasksException(
                'Cannot delete project because it contains tasks.'
            );
        }
    
        return (bool) $project->delete();
    }
}
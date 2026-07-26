<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\ProjectHasTasksException;

class ProjectService
{
    /**
     * Get all accessible projects.
     */
    public function getAll(User $user): Collection
    {
        $query = Project::query();

        if (!$user->isAdmin()) {
            $query->whereHas('tasks', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }

        return $query->get();
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
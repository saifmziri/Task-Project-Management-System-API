<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get Dashboard Statistics based on User Role.
     */
    public function getDashboardData(User $user): array
    {
        // 1. التحقق من الصلاحية
        $isAdmin = $user->isAdmin();

        // 2. استعلام المهام المفلتر
        $tasksQuery = Task::query();
        if (!$isAdmin) {
            $tasksQuery->where('user_id', $user->id);
        }

        // 3. الإحصائيات العامة (Counts)
        $totalProjects = $isAdmin 
            ? Project::count() 
            : Project::whereHas('tasks', fn($q) => $q->where('user_id', $user->id))->count();

        $totalTasks = (clone $tasksQuery)->count();
        $totalUsers = $isAdmin ? User::count() : 0;

        // 4. إحصائيات المهام حسب الحالة
        $taskStatusCounts = (clone $tasksQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $completedTasks  = $taskStatusCounts['completed'] ?? 0;
        $inProgressTasks = $taskStatusCounts['in_progress'] ?? 0;
        $canceledTasks   = $taskStatusCounts['canceled'] ?? 0;

        // 5. المهام حسب الأولوية
        $tasksByPriority = (clone $tasksQuery)
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // 6. أحدث 5 مهام مع إضافة due_date وتبسيط أسماء المالك والمشروع 🎯
        $recentTasks = (clone $tasksQuery)
            ->with(['project:id,name', 'user:id,full_name'])
            ->latest()
            ->take(5)
            ->get(['id', 'task_name', 'status', 'priority', 'due_date', 'project_id', 'user_id', 'created_at'])
            ->map(function ($task) {
                return [
                    'id'           => $task->id,
                    'task_name'    => $task->task_name,
                    'status'       => $task->status,
                    'priority'     => $task->priority,
                    'due_date'     => $task->due_date,
                    'project_id'   => $task->project_id,
                    'project_name' => $task->project?->name ?? 'N/A',
                    'user_id'      => $task->user_id,
                    'user_name'    => $task->user?->full_name ?? 'N/A',
                    'created_at'   => $task->created_at,
                ];
            });

        // 7. نسبة إنجاز أحدث 5 مشاريع فقط (Take 5) 🎯
        $projectProgressQuery = Project::query();
        if (!$isAdmin) {
            $projectProgressQuery->whereHas('tasks', fn($q) => $q->where('user_id', $user->id));
        }

        $projectProgress = $projectProgressQuery->withCount([
            'tasks as total_tasks' => function ($q) use ($isAdmin, $user) {
                if (!$isAdmin) {
                    $q->where('user_id', $user->id);
                }
            },
            'tasks as completed_tasks' => function ($q) use ($isAdmin, $user) {
                $q->where('status', 'completed');
                if (!$isAdmin) {
                    $q->where('user_id', $user->id);
                }
            }
        ])
        ->latest()
        ->take(5) // 🎯 تحديد أحدث 5 مشاريع فقط لضمان سرعة وحجم استجابة مثالي
        ->get()
        ->map(function ($project) {
            $total = $project->total_tasks;
            $completed = $project->completed_tasks;
            $percentage = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

            return [
                'id'              => $project->id,
                'name'            => $project->name,
                'total_tasks'     => $total,
                'completed_tasks' => $completed,
                'progress'        => $percentage,
            ];
        });

        // 8. الاستجابة النهائية
        return [
            'total_projects'    => $totalProjects,
            'total_tasks'       => $totalTasks,
            'completed_tasks'   => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'canceled_tasks'    => $canceledTasks,
            'total_users'       => $totalUsers,

            'recent_tasks'      => $recentTasks,
            'project_progress'  => $projectProgress,
            'tasks_by_priority' => $tasksByPriority,
            'tasks_by_status'   => $taskStatusCounts,
        ];
    }
}
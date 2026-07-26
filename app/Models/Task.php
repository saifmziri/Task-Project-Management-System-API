<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_name', 
        'project_id', 
        'user_id', 
        'status', 
        'priority', 
        'due_date'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope لتطبيق الفلترة والصلاحيات
     */
    public function scopeFilter(Builder $query, User $user, array $filters = []): Builder
    {
        return $query
            // 🛡️ التقييد حسب دور المستخدم (الأدمن يرى الكل، الموظف يرى مهامه فقط)
            ->when(!$user->isAdmin(), function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            // 🔍 البحث باسم المهمة
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('task_name', 'LIKE', "%{$search}%");
            })
            // 📌 الفلترة بحالة المهمة (Pending, In Progress, Completed, ...)
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            // ⚡ الفلترة بالأولوية (Low, Medium, High)
            ->when($filters['priority'] ?? null, function ($q, $priority) {
                $q->where('priority', $priority);
            })
            // 📁 الفلترة حسب مشروع معين
            ->when($filters['project_id'] ?? null, function ($q, $projectId) {
                $q->where('project_id', $projectId);
            })
            // 👤 الفلترة حسب مستخدم معين (خاصة بالأدمن لو أراد رؤية مهام شخص محدد)
            ->when(($filters['user_id'] ?? null) && $user->isAdmin(), function ($q, $userId) {
                $q->where('user_id', $userId);
            })
            ->latest();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'start_date', 'due_date'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Scope لتطبيق الفلاتر والبحث
     */
    public function scopeFilter(Builder $query, array $filters, User $user): Builder
    {
        return $query
            // 🛡️ التقييد حسب دور المستخدم (إذا مو أدمن تجيب بس مشاريع مهامه)
            ->when(!$user->isAdmin(), function ($q) use ($user) {
                $q->whereHas('tasks', function ($taskQuery) use ($user) {
                    $taskQuery->where('user_id', $user->id);
                });
            })
            // 🔍 البحث بالاسم أو الوصف
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            })
            // 📅 التصفية حسب التاريخ
            ->when($filters['start_date'] ?? null, function ($q, $date) {
                $q->whereDate('start_date', '>=', $date);
            })
            ->when($filters['due_date'] ?? null, function ($q, $date) {
                $q->whereDate('due_date', '<=', $date);
            })
            // 🔃 الترتيب (Default: أحدث مشروع)
            ->latest();
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_name'); // اسم المهمة الفرعية (مثل الواجهة الخلفية)
            
            // العلاقات والربط الاحترافي
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade'); // مربوط بالمشروع
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');       // مربوط بالموظف المسؤول
            
            $table->string('status', 30)->default('pending');   
            $table->string('priority', 20)->default('medium');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
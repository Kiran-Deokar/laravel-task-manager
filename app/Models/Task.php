<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $primaryKey = 'task_id';
    
    protected $fillable = [
        'project_id', 'assigned_to_user_id', 'title', 'description', 
        'status', 'priority', 'due_date', 'created_by'
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project() {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function assignee() {
        return $this->belongsTo(User::class, 'assigned_to_user_id', 'user_id');
    }
}
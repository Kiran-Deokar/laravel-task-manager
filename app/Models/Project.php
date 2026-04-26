<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $primaryKey = 'project_id';
    
    protected $fillable = ['name', 'description', 'created_by'];

    public function tasks() {
        return $this->hasMany(Task::class, 'project_id', 'project_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
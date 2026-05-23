<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'assigned_to',
        'created_by',
        'status'
    ];

    protected $casts = [
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship with assigned user
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Relationship with creator
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
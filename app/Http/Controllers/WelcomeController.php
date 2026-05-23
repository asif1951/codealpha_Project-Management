<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;

class WelcomeController extends Controller
{
    public function index()
    {
        $userTasks = [];
        
        if (Auth::check()) {
            // Get tasks where user is assigned OR user created the task
            $userTasks = Task::where('assigned_to', Auth::id())
                            ->orWhere('created_by', Auth::id())
                            ->orderBy('due_date', 'asc')
                            ->get();
        }
        
        return inertia('Welcome', [
            'canLogin' => true,
            'canRegister' => true,
            'auth' => [
                'user' => Auth::user(),
            ],
            'userTasks' => $userTasks,
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'total_tasks' => Task::count(),
            'total_notifications' => Notification::count(),
            'pending_tasks' => Task::where('status', 'pending')->count(),
            'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
            'recent_tasks' => Task::orderBy('created_at', 'desc')->limit(5)->get(),
        ];

        return inertia('Dashboard', [
            'stats' => $stats
        ]);
    }
}
<?php
// app/Http/Controllers/TaskController.php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get all tasks with relationships
        $tasks = Task::with(['assignedUser', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all users (both admin and user)
        $users = User::all();
        
        // Return to CreateTask page with data
        return inertia('CreateTask', [
            'tasks' => $tasks,
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'assigned_to' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'assigned_to' => $request->assigned_to,
            'created_by' => Auth::id(),
            'status' => 'pending'
        ]);

        // Create notification for assigned user
        Notification::create([
            'user_id' => $request->assigned_to,
            'title' => 'নতুন টাস্ক এসেছে!',
            'message' => 'আপনাকে একটি নতুন টাস্ক দেওয়া হয়েছে: ' . $task->title,
            'type' => 'task',
            'task_id' => $task->id,
            'is_read' => false
        ]);

        return response()->json([
            'message' => 'Task created successfully!',
            'task' => $task->load(['assignedUser', 'createdBy'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'assigned_to' => 'required|exists:users,id',
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'assigned_to' => $request->assigned_to,
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Task updated successfully!',
            'task' => $task->load(['assignedUser', 'createdBy'])
        ]);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully!'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        // Create notification when task status changes (optional)
        if ($oldStatus != $request->status) {
            Notification::create([
                'user_id' => $task->assigned_to,
                'title' => 'টাস্কের স্ট্যাটাস পরিবর্তন হয়েছে',
                'message' => 'আপনার টাস্ক "' . $task->title . '" স্ট্যাটাস পরিবর্তন করে ' . $request->status . ' করা হয়েছে',
                'type' => 'task',
                'task_id' => $task->id,
                'is_read' => false
            ]);
        }

        return response()->json([
            'message' => 'Task status updated successfully!',
            'task' => $task
        ]);
    }

    public function getUserTasks()
    {
        $userId = Auth::id();
        
        $tasks = Task::where('assigned_to', $userId)
                     ->orWhere('created_by', $userId)
                     ->orderBy('due_date', 'asc')
                     ->get();
        
        return response()->json($tasks);
    }

    // Get notifications for current user
    public function getNotifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
            
        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();
            
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    // Mark single notification as read
    public function markNotificationAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
            
        if ($notification) {
            $notification->update(['is_read' => true]);
        }
        
        return response()->json(['success' => true]);
    }

    // Mark all notifications as read
    public function markAllNotificationsAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        return response()->json(['success' => true]);
    }
}
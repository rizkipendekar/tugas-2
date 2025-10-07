<?php

namespace App\Http\Controllers;

use App\Models\CalendarTask;
use App\Models\Goal;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Display the calendar view
     */
    public function index(Request $request)
    {
        $currentDate = $request->input('date', now()->format('Y-m-d'));
        $viewMode = $request->input('view', 'month'); // month, week, day
        
        // Get tasks for the current view period
        $startDate = Carbon::parse($currentDate)->startOfMonth();
        $endDate = Carbon::parse($currentDate)->endOfMonth();
        
        if ($viewMode === 'week') {
            $startDate = Carbon::parse($currentDate)->startOfWeek();
            $endDate = Carbon::parse($currentDate)->endOfWeek();
        } elseif ($viewMode === 'day') {
            $startDate = Carbon::parse($currentDate);
            $endDate = Carbon::parse($currentDate);
        }
        
        $tasks = $request->user()->calendarTasks()
            ->whereBetween('task_date', [$startDate, $endDate])
            ->orderBy('task_date')
            ->orderBy('task_time')
            ->get()
            ->groupBy(function ($task) {
                return $task->task_date->format('Y-m-d');
            });

        // Get user progress and stats
        $userProgress = $request->user()->progress ?: $request->user()->progress()->create(['user_id' => $request->user()->id]);
        
        // Get active goals
        $activeGoals = $request->user()->goals()->active()->get()->map(function ($goal) {
            return [
                'id' => $goal->id,
                'title' => $goal->title,
                'description' => $goal->description,
                'target_points' => $goal->target_points,
                'current_progress' => $goal->getCurrentProgress(),
                'progress_percentage' => $goal->getProgressPercentage(),
                'days_remaining' => $goal->days_remaining,
                'reward_name' => $goal->reward_name,
                'reward_icon' => $goal->reward_icon,
                'reward_color' => $goal->reward_color,
            ];
        });

        // Get recent achievements
        $recentAchievements = $request->user()->achievements()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Calendar/Index', [
            'tasks' => $tasks,
            'currentDate' => $currentDate,
            'viewMode' => $viewMode,
            'userProgress' => [
                'total_points' => $userProgress->total_points,
                'daily_points' => $userProgress->daily_points,
                'level' => $userProgress->level,
                'experience' => $userProgress->experience,
                'xp_to_next_level' => $userProgress->getXPToNextLevel(),
                'xp_progress' => $userProgress->getXPProgress(),
                'streak_days' => $userProgress->streak_days,
            ],
            'activeGoals' => $activeGoals,
            'recentAchievements' => $recentAchievements,
            'categories' => ['work', 'personal', 'health', 'learning', 'social', 'other']
        ]);
    }

    /**
     * Store a new calendar task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_date' => 'required|date',
            'task_time' => 'nullable|date_format:H:i',
            'points' => 'required|integer|min:1|max:100',
            'priority' => 'required|in:low,medium,high',
            'category' => 'nullable|string|max:50'
        ]);

        $task = $request->user()->calendarTasks()->create($validated);

        return redirect()->back()->with('success', 'Task created successfully!');
    }

    /**
     * Update a calendar task
     */
    public function update(Request $request, CalendarTask $task)
    {
        if ($task->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'task_date' => 'sometimes|required|date',
            'task_time' => 'nullable|date_format:H:i',
            'points' => 'sometimes|required|integer|min:1|max:100',
            'priority' => 'sometimes|required|in:low,medium,high',
            'category' => 'nullable|string|max:50'
        ]);

        $task->update($validated);

        return redirect()->back()->with('success', 'Task updated successfully!');
    }

    /**
     * Delete a calendar task
     */
    public function destroy(Request $request, CalendarTask $task)
    {
        if ($task->user_id !== $request->user()->id) {
            abort(403);
        }

        // Remove points if task was completed
        if ($task->completed) {
            $request->user()->removePoints($task->points);
        }

        $task->delete();

        return redirect()->back()->with('success', 'Task deleted successfully!');
    }

    /**
     * Toggle task completion
     */
    public function toggleComplete(Request $request, CalendarTask $task)
    {
        if ($task->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($task->completed) {
            $task->markAsIncomplete();
            $message = 'Task marked as incomplete!';
        } else {
            $task->markAsCompleted();
            $message = 'Task completed! +' . $task->points . ' points earned!';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Create a new goal
     */
    public function storeGoal(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_points' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'reward_name' => 'nullable|string|max:255',
            'reward_icon' => 'nullable|string|max:50',
            'reward_color' => 'nullable|string|max:7'
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['reward_color'] = $validated['reward_color'] ?? '#FFD700';
        $validated['reward_icon'] = $validated['reward_icon'] ?? '🏆';

        Goal::create($validated);

        return redirect()->back()->with('success', 'Goal created successfully!');
    }

    /**
     * Get tasks for a specific date
     */
    public function getTasksForDate(Request $request, $date)
    {
        $tasks = $request->user()->calendarTasks()
            ->forDate($date)
            ->orderBy('task_time')
            ->get();

        return response()->json($tasks);
    }

    /**
     * Get user statistics
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        $progress = $user->progress ?: $user->progress()->create(['user_id' => $user->id]);

        $stats = [
            'total_tasks' => $user->calendarTasks()->count(),
            'completed_tasks' => $user->calendarTasks()->completed()->count(),
            'pending_tasks' => $user->calendarTasks()->pending()->count(),
            'total_points' => $progress->total_points,
            'daily_points' => $progress->daily_points,
            'weekly_points' => $progress->weekly_points,
            'monthly_points' => $progress->monthly_points,
            'level' => $progress->level,
            'experience' => $progress->experience,
            'streak_days' => $progress->streak_days,
            'active_goals' => $user->goals()->active()->count(),
            'achieved_goals' => $user->goals()->achieved()->count(),
            'total_achievements' => $user->achievements()->count(),
        ];

        return response()->json($stats);
    }
}
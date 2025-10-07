<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class HabitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $habits = $request->user()->habits()
                ->with(['entries' => function ($query) {
                    $query->where('completed_at', '>=', Carbon::now()->subDays(30));
                }])
                ->when($request->filter === 'active', function ($query) {
                    return $query->active();
                })
                ->when($request->filter === 'inactive', function ($query) {
                    return $query->where('is_active', false);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($habit) {
                    // Get today's entry
                    $todayEntry = $habit->entries()
                        ->where('completed_at', Carbon::today()->format('Y-m-d'))
                        ->first();
                    
                    $completedToday = $todayEntry && $todayEntry->count >= $habit->target_count;
                    
                    return [
                        'id' => $habit->id,
                        'name' => $habit->name,
                        'description' => $habit->description,
                        'frequency' => $habit->frequency,
                        'target_count' => $habit->target_count,
                        'color' => $habit->color,
                        'is_active' => $habit->is_active,
                        'created_at' => $habit->created_at,
                        'current_streak' => $this->calculateStreak($habit),
                        'weekly_completion' => $this->calculateWeeklyCompletion($habit),
                        'monthly_completion' => $this->calculateMonthlyCompletion($habit),
                        'completed_today' => $completedToday,
                        'today_entry' => $todayEntry,
                    ];
                });

            return Inertia::render('Habits/Index', [
                'habits' => $habits,
                'filters' => $request->only(['filter'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading habits: ' . $e->getMessage());
            
            // Return empty habits if there's a database error
            return Inertia::render('Habits/Index', [
                'habits' => [],
                'filters' => $request->only(['filter']),
                'error' => 'Database tables not found. Please run migrations first.'
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'required|in:daily,weekly,monthly',
            'target_count' => 'required|integer|min:1',
            'color' => 'required|string|max:7'
        ]);

        $request->user()->habits()->create($validated);

        return redirect()->back()->with('success', 'Habit created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Habit $habit)
    {
        // Ensure the habit belongs to the authenticated user
        if ($habit->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'frequency' => 'sometimes|required|in:daily,weekly,monthly',
            'target_count' => 'sometimes|required|integer|min:1',
            'color' => 'sometimes|required|string|max:7',
            'is_active' => 'sometimes|boolean'
        ]);

        $habit->update($validated);

        return redirect()->back()->with('success', 'Habit updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Habit $habit)
    {
        // Ensure the habit belongs to the authenticated user
        if ($habit->user_id !== $request->user()->id) {
            abort(403);
        }

        $habit->delete();

        return redirect()->back()->with('success', 'Habit deleted successfully!');
    }

    /**
     * Mark habit as completed for today
     */
    public function complete(Request $request, Habit $habit)
    {
        try {
            // Ensure the habit belongs to the authenticated user
            if ($habit->user_id !== $request->user()->id) {
                abort(403);
            }

            $validated = $request->validate([
                'count' => 'sometimes|integer|min:1',
                'notes' => 'nullable|string',
                'date' => 'nullable|date'
            ]);

            $date = $validated['date'] ?? Carbon::today()->format('Y-m-d');
            $count = $validated['count'] ?? 1;

            // Check if entry already exists for this date
            $entry = HabitEntry::where('habit_id', $habit->id)
                ->where('user_id', $request->user()->id)
                ->where('completed_at', $date)
                ->first();

            if ($entry) {
                // Update existing entry
                $entry->update([
                    'count' => $entry->count + $count,
                    'notes' => $validated['notes'] ?? $entry->notes
                ]);
            } else {
                // Create new entry
                HabitEntry::create([
                    'habit_id' => $habit->id,
                    'user_id' => $request->user()->id,
                    'completed_at' => $date,
                    'count' => $count,
                    'notes' => $validated['notes'] ?? null
                ]);
            }

            return redirect()->back()->with('success', 'Habit marked as completed!');
        } catch (\Exception $e) {
            \Log::error('Error completing habit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to mark habit as completed. Please try again.');
        }
    }

    /**
     * Undo habit completion for today
     */
    public function uncomplete(Request $request, Habit $habit)
    {
        // Ensure the habit belongs to the authenticated user
        if ($habit->user_id !== $request->user()->id) {
            abort(403);
        }

        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $entry = HabitEntry::where('habit_id', $habit->id)
            ->where('user_id', $request->user()->id)
            ->where('completed_at', $date)
            ->first();

        if ($entry) {
            if ($entry->count > 1) {
                $entry->decrement('count');
            } else {
                $entry->delete();
            }
        }

        return redirect()->back()->with('success', 'Habit completion undone!');
    }

    /**
     * Get habit statistics
     */
    public function statistics(Request $request, Habit $habit)
    {
        // Ensure the habit belongs to the authenticated user
        if ($habit->user_id !== $request->user()->id) {
            abort(403);
        }

        $period = $request->input('period', 'month'); // week, month, year
        
        $startDate = match($period) {
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth()
        };

        $entries = $habit->entries()
            ->where('completed_at', '>=', $startDate)
            ->orderBy('completed_at')
            ->get();

        $statistics = [
            'total_completions' => $entries->sum('count'),
            'completion_days' => $entries->count(),
            'current_streak' => $habit->getCurrentStreak(),
            'entries' => $entries->map(function ($entry) {
                return [
                    'date' => $entry->completed_at->format('Y-m-d'),
                    'count' => $entry->count,
                    'notes' => $entry->notes
                ];
            })
        ];

        return response()->json($statistics);
    }

    /**
     * Calculate streak for a habit
     */
    private function calculateStreak($habit)
    {
        try {
            $streak = 0;
            $currentDate = Carbon::today();
            
            while (true) {
                $entry = $habit->entries()
                    ->where('completed_at', $currentDate->format('Y-m-d'))
                    ->first();
                    
                if ($entry && $entry->count >= $habit->target_count) {
                    $streak++;
                    $currentDate->subDay();
                } else {
                    break;
                }
            }
            
            return $streak;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calculate weekly completion percentage
     */
    private function calculateWeeklyCompletion($habit)
    {
        try {
            $startOfWeek = Carbon::now()->startOfWeek();
            $completedDays = 0;
            $totalDays = 0;
            
            for ($i = 0; $i < 7; $i++) {
                $date = $startOfWeek->copy()->addDays($i);
                if ($date->lte(Carbon::today())) {
                    $totalDays++;
                    $entry = $habit->entries()
                        ->where('completed_at', $date->format('Y-m-d'))
                        ->first();
                    if ($entry && $entry->count >= $habit->target_count) {
                        $completedDays++;
                    }
                }
            }
            
            return $totalDays > 0 ? round(($completedDays / $totalDays) * 100) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calculate monthly completion percentage
     */
    private function calculateMonthlyCompletion($habit)
    {
        try {
            $startOfMonth = Carbon::now()->startOfMonth();
            $completedDays = 0;
            $totalDays = 0;
            
            $currentDate = $startOfMonth->copy();
            while ($currentDate->lte(Carbon::today()) && $currentDate->month === Carbon::now()->month) {
                $totalDays++;
                $entry = $habit->entries()
                    ->where('completed_at', $currentDate->format('Y-m-d'))
                    ->first();
                if ($entry && $entry->count >= $habit->target_count) {
                    $completedDays++;
                }
                $currentDate->addDay();
            }
            
            return $totalDays > 0 ? round(($completedDays / $totalDays) * 100) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
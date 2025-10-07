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
                return [
                    'id' => $habit->id,
                    'name' => $habit->name,
                    'description' => $habit->description,
                    'frequency' => $habit->frequency,
                    'target_count' => $habit->target_count,
                    'color' => $habit->color,
                    'is_active' => $habit->is_active,
                    'created_at' => $habit->created_at,
                    'current_streak' => $habit->getCurrentStreak(),
                    'weekly_completion' => $habit->getWeeklyCompletionPercentage(),
                    'monthly_completion' => $habit->getMonthlyCompletionPercentage(),
                    'completed_today' => $habit->isCompletedForDate(Carbon::today()),
                    'today_entry' => $habit->getEntryForDate(Carbon::today()),
                ];
            });

        return Inertia::render('Habits/Index', [
            'habits' => $habits,
            'filters' => $request->only(['filter'])
        ]);
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
    public function destroy(Habit $habit, Request $request)
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
        // Ensure the habit belongs to the authenticated user
        if ($habit->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'count' => 'sometimes|integer|min:1',
            'notes' => 'nullable|string',
            'date' => 'nullable|date'
        ]);

        $date = isset($validated['date']) ? $validated['date'] : Carbon::today()->format('Y-m-d');
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
}
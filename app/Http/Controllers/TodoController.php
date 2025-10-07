<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $todos = $request->user()->todos()
            ->when($request->filter, function ($query, $filter) {
                if ($filter === 'completed') {
                    return $query->completed();
                } elseif ($filter === 'pending') {
                    return $query->pending();
                }
            })
            ->when($request->priority, function ($query, $priority) {
                return $query->byPriority($priority);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Todos/Index', [
            'todos' => $todos,
            'filters' => $request->only(['filter', 'priority'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high'
        ]);

        $request->user()->todos()->create($validated);

        return redirect()->back()->with('success', 'Todo created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Todo $todo)
    {
        // Ensure the todo belongs to the authenticated user
        if ($todo->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'sometimes|boolean',
            'due_date' => 'nullable|date',
            'priority' => 'sometimes|required|in:low,medium,high'
        ]);

        $todo->update($validated);

        return redirect()->back()->with('success', 'Todo updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Todo $todo, Request $request)
    {
        // Ensure the todo belongs to the authenticated user
        if ($todo->user_id !== $request->user()->id) {
            abort(403);
        }

        $todo->delete();

        return redirect()->back()->with('success', 'Todo deleted successfully!');
    }

    /**
     * Toggle the completion status of a todo
     */
    public function toggle(Todo $todo, Request $request)
    {
        // Ensure the todo belongs to the authenticated user
        if ($todo->user_id !== $request->user()->id) {
            abort(403);
        }

        $todo->update([
            'completed' => !$todo->completed
        ]);

        return redirect()->back();
    }
}

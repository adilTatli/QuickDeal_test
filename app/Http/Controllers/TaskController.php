<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $tasks = Task::query()
                ->when($request->has('status'), function ($query) use ($request) {
                    $query->where('status', $request->status);
                })
                ->when($request->has('due_date'), function ($query) use ($request) {
                    $query->whereDate('due_date', $request->due_date);
                })
                ->when($request->has('created_at'), function ($query) use ($request) {
                    $query->whereDate('created_at', $request->created_at);
                })
                ->get();

            return TaskResource::collection($tasks);
        } catch (Exception $e) {
            Log::error('Error fetching tasks. Details: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Не удалось получить задания'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        try {
            $validated = $request->validated();

            $task = Task::create($validated);

            return new TaskResource($task);
        } catch (Exception $e) {
            Log::error('Error creating task. Details: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Не удалось создать задачу'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        try {
            return new TaskResource($task);
        } catch (ModelNotFoundException $e) {
            Log::error('Task not found: ' . $e->getMessage(), [
                'task_id' => $task->id,
            ]);

            return response()->json(['error' => 'Задача не найдена'], 404);
        } catch (Exception $e) {
            Log::error('Error fetching task. Details: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Не удалось получить задание'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        try {
            $validated = $request->validated();

            $task->update($validated);

            return new TaskResource($task);
        } catch (ModelNotFoundException $e) {
            Log::error('Task not found: ' . $e->getMessage(), [
                'task_id' => $task->id,
            ]);

            return response()->json(['error' => 'Задача не найдена'], 404);
        } catch (Exception $e) {
            Log::error('Error updating task. Details: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Не удалось обновить задачу'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        try {
            $task->delete();

            return response()->json(['message' => 'Задача успешно удалена'], 204);
        } catch (ModelNotFoundException $e) {
            Log::error('Task not found: ' . $e->getMessage(), [
                'task_id' => $task->id,
            ]);

            return response()->json(['error' => 'Задача не найдена'], 404);
        } catch (Exception $e) {
            Log::error('Error deleting task. Details: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Не удалось удалить задачу'], 500);
        }
    }
}

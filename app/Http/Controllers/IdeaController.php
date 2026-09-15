<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskReaction;
use App\Services\ImageService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IdeaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tenant = $user->currentTenant();

        if (!$tenant) {
            return redirect()->route('tenants.create');
        }

        // Calculate upcoming focus days (Friday, Saturday, Sunday)
        $now = Carbon::now();
        $friday = $now->copy()->next(Carbon::FRIDAY)->toDateString();
        if ($now->isFriday()) {
            $friday = $now->toDateString();
        } elseif ($now->isSaturday()) {
            $friday = $now->copy()->subDay()->toDateString();
        } elseif ($now->isSunday()) {
            $friday = $now->copy()->subDays(2)->toDateString();
        }

        $saturday = Carbon::parse($friday)->addDay()->toDateString();
        $sunday = Carbon::parse($friday)->addDays(2)->toDateString();

        // Query backlog tasks (scheduled_date is NULL)
        $categoryFilter = $request->query('category');
        $backlogQuery = $tenant->tasks()
            ->with(['creator', 'reactions'])
            ->whereNull('scheduled_date')
            ->orderBy('created_at', 'desc');

        if ($categoryFilter && $categoryFilter !== 'all') {
            $backlogQuery->where('category', $categoryFilter);
        }

        $backlogIdeas = $backlogQuery->get();

        // Query scheduled tasks
        $fridayTasks = $tenant->tasks()->with(['creator', 'reactions'])->whereDate('scheduled_date', $friday)->orderBy('sort_order')->get();
        $saturdayTasks = $tenant->tasks()->with(['creator', 'reactions'])->whereDate('scheduled_date', $saturday)->orderBy('sort_order')->get();
        $sundayTasks = $tenant->tasks()->with(['creator', 'reactions'])->whereDate('scheduled_date', $sunday)->orderBy('sort_order')->get();

        $weekendTasksCount = $fridayTasks->count() + $saturdayTasks->count() + $sundayTasks->count();
        $weekendCompletedCount = $fridayTasks->where('is_completed', true)->count() 
            + $saturdayTasks->where('is_completed', true)->count() 
            + $sundayTasks->where('is_completed', true)->count();

        if ($request->header('HX-Request') && $request->header('HX-Target') === 'backlog-container') {
            return view('ideas.partials.backlog_list', compact('backlogIdeas', 'tenant', 'user', 'friday', 'saturday', 'sunday'));
        }

        if ($request->header('HX-Request') && $request->header('HX-Target') === 'weekend-container') {
            return view('ideas.partials.weekend_plan', compact('fridayTasks', 'saturdayTasks', 'sundayTasks', 'friday', 'saturday', 'sunday', 'tenant', 'user'));
        }

        return view('ideas.index', compact(
            'tenant',
            'user',
            'backlogIdeas',
            'fridayTasks',
            'saturdayTasks',
            'sundayTasks',
            'friday',
            'saturday',
            'sunday',
            'weekendTasksCount',
            'weekendCompletedCount',
            'categoryFilter'
        ));
    }

    public function store(Request $request, ImageService $imageService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|in:projekti,steidzami,attistiba,sanaksmes,ikdienas,citi',
            'image_url' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|max:12288', // up to 12MB
            'scheduled_date' => 'nullable|date',
            'scheduled_time_slot' => 'nullable|string|max:50',
        ]);

        $user = auth()->user();
        $tenant = $user->currentTenant();

        if (!$tenant) {
            return response('Darbavieta nav atrasta', 400);
        }

        $imageUrl = null;

        // Process uploaded image file -> Convert to WebP & upload to Hetzner S3
        if ($request->hasFile('image_file')) {
            $imageUrl = $imageService->processAndUpload($request->file('image_file'), 'tasks', 1200, 82);
        } elseif (!empty($validated['image_url'])) {
            $imageUrl = $validated['image_url'];
        } else {
            $category = $validated['category'] ?? 'citi';
            $imageUrl = match ($category) {
                'projekti' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80',
                'steidzami' => 'https://images.unsplash.com/photo-1507925921958-8a62f3d1a50d?auto=format&fit=crop&w=600&q=80',
                'attistiba' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=600&q=80',
                'sanaksmes' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=600&q=80',
                'ikdienas' => 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?auto=format&fit=crop&w=600&q=80',
                default => null,
            };
        }

        $tenant->tasks()->create([
            'created_by_id' => $user->id,
            'title' => $validated['title'],
            'category' => $validated['category'] ?? 'citi',
            'image_url' => $imageUrl,
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'scheduled_time_slot' => $validated['scheduled_time_slot'] ?? null,
        ]);

        if ($request->header('HX-Request')) {
            return $this->index($request);
        }

        return redirect()->route('ideas.index');
    }

    public function schedule(Request $request, Task $task)
    {
        $validated = $request->validate([
            'scheduled_date' => 'nullable|string',
            'scheduled_time_slot' => 'nullable|string|max:50',
        ]);

        $scheduledDate = $validated['scheduled_date'] ?? null;
        if ($scheduledDate === 'null' || empty($scheduledDate)) {
            $task->scheduled_date = null;
        } else {
            $task->scheduled_date = Carbon::parse($scheduledDate)->toDateString();
        }

        if (isset($validated['scheduled_time_slot'])) {
            $task->scheduled_time_slot = $validated['scheduled_time_slot'];
        }

        $task->save();

        if ($request->header('HX-Request')) {
            return $this->index($request);
        }

        return redirect()->route('ideas.index');
    }

    public function toggle(Request $request, Task $task)
    {
        $task->is_completed = !$task->is_completed;
        $task->save();

        if ($request->header('HX-Request')) {
            return $this->index($request);
        }

        return redirect()->route('ideas.index');
    }

    public function react(Request $request, Task $task)
    {
        $userId = auth()->id();
        $reactionType = $request->input('reaction', 'thumbs_up');

        $existing = TaskReaction::where('task_id', $task->id)
            ->where('user_id', $userId)
            ->where('reaction', $reactionType)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            TaskReaction::create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'reaction' => $reactionType,
            ]);
        }

        if ($request->header('HX-Request')) {
            return $this->index($request);
        }

        return redirect()->route('ideas.index');
    }

    public function destroy(Request $request, Task $task)
    {
        $task->delete();

        if ($request->header('HX-Request')) {
            return $this->index($request);
        }

        return redirect()->route('ideas.index');
    }
}

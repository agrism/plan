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

        $tenant->ensureDefaultCategories();
        $categories = $tenant->categories()->get();

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
            ->with(['creator', 'reactions', 'categoryRelation', 'comments.user'])
            ->whereNull('scheduled_date')
            ->orderBy('created_at', 'desc');

        if ($categoryFilter && $categoryFilter !== 'all') {
            $backlogQuery->where(function ($q) use ($categoryFilter) {
                $q->where('category', $categoryFilter)
                  ->orWhere('category_id', $categoryFilter);
            });
        }

        $backlogIdeas = $backlogQuery->get();

        // Query all scheduled tasks (where scheduled_date is NOT NULL)
        $allScheduledTasks = $tenant->tasks()
            ->with(['creator', 'reactions', 'categoryRelation', 'comments.user'])
            ->whereNotNull('scheduled_date')
            ->orderBy('scheduled_date')
            ->orderBy('sort_order')
            ->get();

        $scheduledTasksByDate = $allScheduledTasks->groupBy(function ($task) {
            return $task->scheduled_date->toDateString();
        });

        // Ensure key focus dates are included and sorted
        $scheduledDates = collect([$friday, $saturday, $sunday])
            ->merge($scheduledTasksByDate->keys())
            ->unique()
            ->sort()
            ->values();

        $fridayTasks = $scheduledTasksByDate->get($friday, collect());
        $saturdayTasks = $scheduledTasksByDate->get($saturday, collect());
        $sundayTasks = $scheduledTasksByDate->get($sunday, collect());

        $weekendTasksCount = $allScheduledTasks->count();
        $weekendCompletedCount = $allScheduledTasks->where('is_completed', true)->count();

        $today = Carbon::now()->toDateString();
        $tomorrow = Carbon::now()->addDay()->toDateString();

        if ($request->header('HX-Request') && $request->header('HX-Target') === 'backlog-container') {
            return view('ideas.partials.backlog_list', compact('backlogIdeas', 'tenant', 'user', 'friday', 'saturday', 'sunday', 'today', 'tomorrow', 'categories'));
        }

        if ($request->header('HX-Request') && $request->header('HX-Target') === 'weekend-container') {
            return view('ideas.partials.weekend_plan', compact('scheduledDates', 'scheduledTasksByDate', 'fridayTasks', 'saturdayTasks', 'sundayTasks', 'friday', 'saturday', 'sunday', 'today', 'tomorrow', 'tenant', 'user', 'categories'));
        }

        return view('ideas.index', compact(
            'tenant',
            'user',
            'categories',
            'backlogIdeas',
            'scheduledDates',
            'scheduledTasksByDate',
            'fridayTasks',
            'saturdayTasks',
            'sundayTasks',
            'friday',
            'saturday',
            'sunday',
            'today',
            'tomorrow',
            'weekendTasksCount',
            'weekendCompletedCount',
            'categoryFilter'
        ));
    }

    public function store(Request $request, ImageService $imageService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category' => 'nullable|string|max:50',
            'category_id' => 'nullable|integer',
            'image_url' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|max:12288',
            'links' => 'nullable|array',
            'links.*' => 'nullable|string|max:500',
            'scheduled_date' => 'nullable|date',
            'scheduled_time_slot' => 'nullable|string|max:50',
        ]);

        $user = auth()->user();
        $tenant = $user->currentTenant();

        if (!$tenant) {
            return response('Darbavieta nav atrasta', 400);
        }

        // Image handling: strictly only if uploaded or provided, NO automatic fallback
        $imageUrl = null;
        if ($request->hasFile('image_file')) {
            $imageUrl = $imageService->processAndUpload($request->file('image_file'), 'tasks', 1200, 82);
        } elseif (!empty($validated['image_url'])) {
            $imageUrl = $validated['image_url'];
        }

        // Category resolution
        $categoryId = null;
        $categorySlug = 'citi';

        if (!empty($validated['category_id'])) {
            $cat = $tenant->categories()->find($validated['category_id']);
            if ($cat) {
                $categoryId = $cat->id;
                $categorySlug = $cat->slug;
            }
        } elseif (!empty($validated['category'])) {
            $cat = $tenant->categories()->where('slug', $validated['category'])->orWhere('id', $validated['category'])->first();
            if ($cat) {
                $categoryId = $cat->id;
                $categorySlug = $cat->slug;
            } else {
                $categorySlug = $validated['category'];
            }
        }

        // Clean links
        $cleanLinks = array_values(array_filter($request->input('links', []), function ($link) {
            return !empty(trim((string)$link));
        }));

        $tenant->tasks()->create([
            'created_by_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $categorySlug,
            'category_id' => $categoryId,
            'image_url' => $imageUrl,
            'links' => $cleanLinks,
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'scheduled_time_slot' => $validated['scheduled_time_slot'] ?? null,
        ]);

        if ($request->header('HX-Request')) {
            return $this->index($request);
        }

        return redirect()->route('ideas.index');
    }

    public function edit(Task $task)
    {
        $tenant = auth()->user()->currentTenant();
        $tenant->ensureDefaultCategories();
        $categories = $tenant->categories()->get();

        $task->load(['comments.user', 'creator', 'categoryRelation']);

        return view('ideas.partials.edit_modal', compact('task', 'categories'));
    }

    public function update(Request $request, Task $task, ImageService $imageService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category' => 'nullable|string|max:50',
            'category_id' => 'nullable|integer',
            'image_url' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|max:12288',
            'links' => 'nullable|array',
            'links.*' => 'nullable|string|max:500',
            'scheduled_date' => 'nullable|string',
            'scheduled_time_slot' => 'nullable|string|max:50',
            'remove_image' => 'nullable|boolean',
        ]);

        $task->title = $validated['title'];
        $task->description = $validated['description'] ?? null;

        $tenant = auth()->user()->currentTenant();
        if (!empty($validated['category_id'])) {
            $cat = $tenant->categories()->find($validated['category_id']);
            if ($cat) {
                $task->category_id = $cat->id;
                $task->category = $cat->slug;
            }
        } elseif (!empty($validated['category'])) {
            $cat = $tenant->categories()->where('slug', $validated['category'])->orWhere('id', $validated['category'])->first();
            if ($cat) {
                $task->category_id = $cat->id;
                $task->category = $cat->slug;
            } else {
                $task->category = $validated['category'];
            }
        }

        if ($request->boolean('remove_image')) {
            $task->image_url = null;
        } elseif ($request->hasFile('image_file')) {
            $task->image_url = $imageService->processAndUpload($request->file('image_file'), 'tasks', 1200, 82);
        } elseif (!empty($validated['image_url'])) {
            $task->image_url = $validated['image_url'];
        }

        // Clean links
        $cleanLinks = array_values(array_filter($request->input('links', []), function ($link) {
            return !empty(trim((string)$link));
        }));
        $task->links = $cleanLinks;

        if (array_key_exists('scheduled_date', $validated)) {
            $schedDate = $validated['scheduled_date'];
            $task->scheduled_date = ($schedDate === 'null' || empty($schedDate)) ? null : Carbon::parse($schedDate)->toDateString();
        }

        if (array_key_exists('scheduled_time_slot', $validated)) {
            $task->scheduled_time_slot = $validated['scheduled_time_slot'];
        }

        $task->save();

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

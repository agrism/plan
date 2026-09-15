<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $user = auth()->user();
        $tenant = $user->currentTenant();

        if (!$tenant || $task->tenant_id !== $tenant->id) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $task->comments()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'content' => trim($validated['content']),
        ]);

        $task->load(['comments.user', 'creator']);

        if ($request->header('HX-Request')) {
            return view('ideas.partials.comments_section', compact('task'));
        }

        return back();
    }

    public function destroy(Request $request, Task $task, TaskComment $comment)
    {
        $user = auth()->user();
        $tenant = $user->currentTenant();

        if (!$tenant || $task->tenant_id !== $tenant->id || $comment->task_id !== $task->id) {
            abort(403);
        }

        $isCommentOwner = $comment->user_id === $user->id;
        $isAdmin = $tenant->owner_id === $user->id || $tenant->users()->where('users.id', $user->id)->wherePivot('role', 'admin')->exists();

        if (!$isCommentOwner && !$isAdmin) {
            abort(403, 'Unauthorized');
        }

        $comment->delete();

        $task->load(['comments.user', 'creator']);

        if ($request->header('HX-Request')) {
            return view('ideas.partials.comments_section', compact('task'));
        }

        return back();
    }
}

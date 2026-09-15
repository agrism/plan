<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|in:blue,rose,purple,amber,emerald,indigo,cyan,slate,orange,pink',
        ]);

        $user = auth()->user();
        $tenant = $user->currentTenant();

        if (!$tenant) {
            return response('Darbavieta nav atrasta', 400);
        }

        $name = trim($validated['name']);
        $slug = Str::slug($name);
        if (empty($slug)) {
            $slug = 'cat-' . Str::random(6);
        }

        $category = $tenant->categories()->create([
            'name' => $name,
            'slug' => $slug,
            'emoji' => $validated['emoji'] ?? '📁',
            'color' => $validated['color'] ?? 'blue',
        ]);

        if ($request->header('HX-Request')) {
            return redirect()->route('ideas.index');
        }

        return redirect()->route('ideas.index');
    }

    public function destroy(Request $request, Category $category)
    {
        $user = auth()->user();
        $tenant = $user->currentTenant();

        if ($category->tenant_id !== $tenant->id) {
            abort(403);
        }

        $category->delete();

        if ($request->header('HX-Request')) {
            return redirect()->route('ideas.index');
        }

        return redirect()->route('ideas.index');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('user_id', auth()->id())->get();

        return response()->json($categories, 200);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Category::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Category créée avec succès',
            'category' => $category,
        ], 201);
    }

    public function show(string $id)
    {
        $category = Category::findOrFail($id);

        Gate::authorize('view', $category);

        return response()->json($category, 200);
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        Gate::authorize('update', $category);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Category modifiée avec succès',
            'category' => $category,
        ], 200);
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        Gate::authorize('delete', $category);

        $category->delete();

        return response()->json([
            'message' => 'Category supprimée avec succès',
        ], 200);
    }



    public function assignPlats(Request $request, string $id)
{
    $category = Category::findOrFail($id);

    Gate::authorize('update', $category);

    $validated = $request->validate([
        'plat_ids' => 'required|array|min:1',
        'plat_ids.*' => 'exists:plats,id',
    ]);

    $plats = \App\Models\Plat::whereIn('id', $validated['plat_ids'])
        ->where('user_id', auth()->id())
        ->get();

    if ($plats->count() !== count($validated['plat_ids'])) {
        return response()->json([
            'message' => 'Un ou plusieurs plats sont introuvables ou ne vous appartiennent pas'
        ], 403);
    }

    foreach ($plats as $plat) {
        $plat->update([
            'category_id' => $category->id,
        ]);
    }

    return response()->json([
        'message' => 'Plats associés à la catégorie avec succès',
        'category' => $category,
        'plats' => $plats,
    ], 200);
}
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Plat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlatController extends Controller
{
    public function index()
    {
        $plats = Plat::where('user_id', auth()->id())->get();

        return response()->json($plats, 200);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Plat::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $category = Category::findOrFail($validated['category_id']);

        Gate::authorize('view', $category);

        $plat = Plat::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'category_id' => $validated['category_id'],
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Plat créé avec succès',
            'plat' => $plat,
        ], 201);
    }

    public function show(string $id)
    {
        $plat = Plat::findOrFail($id);

        Gate::authorize('view', $plat);

        return response()->json($plat, 200);
    }

    public function update(Request $request, string $id)
    {
        $plat = Plat::findOrFail($id);

        Gate::authorize('update', $plat);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $category = Category::findOrFail($validated['category_id']);

        Gate::authorize('view', $category);

        $plat->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'category_id' => $validated['category_id'],
        ]);

        return response()->json([
            'message' => 'Plat modifié avec succès',
            'plat' => $plat,
        ], 200);
    }

    public function destroy(string $id)
    {
        $plat = Plat::findOrFail($id);

        Gate::authorize('delete', $plat);

        $plat->delete();

        return response()->json([
            'message' => 'Plat supprimé avec succès',
        ], 200);
    }
}
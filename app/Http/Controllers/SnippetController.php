<?php

namespace App\Http\Controllers;

use App\Models\Snippet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SnippetController extends Controller
{
    /**
     * Display a listing of the snippets.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $department = $request->query('department');
        $tag = $request->query('tag');

        $query = Snippet::query();

        if (!empty($search)) {
            $query->search($search);
        } else {
            $query->latest();
        }

        if (!empty($department)) {
            $query->byDepartment($department);
        }

        if (!empty($tag)) {
            $query->byTag($tag);
        }

        $snippets = $query->get();

        return response()->json($snippets);
    }

    /**
     * Store a newly created snippet in storage.
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'manager') {
            return response()->json(['message' => 'Unauthorized. Only Team Managers can create templates.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'department' => 'required|string|in:Keuangan,SDM,Umum',
            'tags' => 'required|array',
        ]);

        // Workaround for Laravel validator limitation where array validation cannot easily enforce that all elements are non-empty strings without writing a custom Rule object
        $tags = array_filter($validated['tags'], function ($tag) {
            return is_string($tag) && trim($tag) !== '';
        });

        $snippet = Snippet::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'department' => $validated['department'],
            'tags' => array_values($tags),
            'created_by' => $request->user()->name,
        ]);

        return response()->json($snippet, 201);
    }

    /**
     * Update the specified snippet in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if ($request->user()?->role !== 'manager') {
            return response()->json(['message' => 'Unauthorized. Only Team Managers can modify templates.'], 403);
        }

        $snippet = Snippet::find($id);

        if (!$snippet) {
            return response()->json(['message' => 'Snippet not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'department' => 'required|string|in:Keuangan,SDM,Umum',
            'tags' => 'required|array',
        ]);

        // Workaround for Laravel validator limitation where array validation cannot easily enforce that all elements are non-empty strings without writing a custom Rule object
        $tags = array_filter($validated['tags'], function ($tag) {
            return is_string($tag) && trim($tag) !== '';
        });

        $snippet->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'department' => $validated['department'],
            'tags' => array_values($tags),
        ]);

        return response()->json($snippet);
    }

    /**
     * Remove the specified snippet from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($request->user()?->role !== 'manager') {
            return response()->json(['message' => 'Unauthorized. Only Team Managers can delete templates.'], 403);
        }

        $snippet = Snippet::find($id);

        if (!$snippet) {
            return response()->json(['message' => 'Snippet not found.'], 404);
        }

        $snippet->delete();

        return response()->json(['message' => 'Snippet deleted successfully.']);
    }
}

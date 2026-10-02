<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetPack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Asset::with(['category', 'assetPack']);

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('theme') && $request->theme !== 'all') {
            $query->where('theme', $request->theme);
        }

        if ($request->filled('pack') && $request->pack !== 'all') {
            $packSlug = $request->pack;
            $query->whereHas('assetPack', function ($q) use ($packSlug) {
                $q->where('slug', $packSlug);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = min(120, max(12, (int) $request->input('per_page', 48)));
        $assets = $query->orderBy('id', 'desc')->paginate($perPage);
        return response()->json($assets);
    }

    public function packs(): JsonResponse
    {
        $packs = AssetPack::withCount('assets')->get();
        return response()->json($packs);
    }

    public function categories(): JsonResponse
    {
        $categories = AssetCategory::withCount('assets')->get();
        return response()->json($categories);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:character,background,object,item,ui,icon,audio',
            'file' => 'required|file|mimes:png,jpg,jpeg,svg,webp,mp3,wav|max:10240', // Max 10MB
            'theme' => 'nullable|string',
            'category_id' => 'nullable|exists:asset_categories,id',
        ]);

        $path = $request->file('file')->store('assets', 'public');

        $asset = Asset::create([
            'name' => $request->name,
            'type' => $request->type,
            'category_id' => $request->category_id,
            'file_path' => '/storage/' . $path,
            'preview_url' => '/storage/' . $path,
            'theme' => $request->theme ?? 'custom',
            'uploaded_by' => $request->user()->id,
            'is_public' => false,
        ]);

        return response()->json([
            'message' => 'อัปโหลด Asset สำเร็จ!',
            'asset' => $asset,
        ], 201);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAboutUsRequest;
use App\Http\Requests\UpdateAboutUsRequest;
use App\Http\Resources\AboutUsResource;
use App\Services\AboutUsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AboutUsController extends Controller
{
    protected AboutUsService $aboutUsService;

    public function __construct(AboutUsService $aboutUsService)
    {
        $this->aboutUsService = $aboutUsService;
    }

    public function index(): JsonResponse
    {
        $aboutUs = $this->aboutUsService->getSettings();

        return response()->json([
            'success' => true,
            'data' => $aboutUs ? new AboutUsResource($aboutUs) : null,
        ]);
    }

    public function store(StoreAboutUsRequest $request): JsonResponse
    {
       // dd($request->all());
        $data = $request->safe()->except(['logo']);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('about-us', 'public');
        }

        $aboutUs = $this->aboutUsService->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Company information created successfully.',
            'data' => new AboutUsResource($aboutUs),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $aboutUs = $this->aboutUsService->getById($id);

        if (!$aboutUs) {
            return response()->json(['success' => false, 'message' => 'Company information not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => new AboutUsResource($aboutUs)]);
    }

    public function update(UpdateAboutUsRequest $request, int $id): JsonResponse
    {
        $aboutUs = $this->aboutUsService->getById($id);

        if (!$aboutUs) {
            return response()->json(['success' => false, 'message' => 'Company information not found.'], 404);
        }

        $data = $request->safe()->except(['logo']);

        if ($request->hasFile('logo')) {
            if ($aboutUs->logo_path) {
                Storage::disk('public')->delete($aboutUs->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('about-us', 'public');
        }

        $updated = $this->aboutUsService->update($id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Company information updated successfully.',
            'data' => new AboutUsResource($updated),
        ]);
    }
}

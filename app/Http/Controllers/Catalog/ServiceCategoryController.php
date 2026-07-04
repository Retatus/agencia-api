<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ServiceCategory\StoreServiceCategoryRequest;
use App\Http\Requests\Catalog\ServiceCategory\UpdateServiceCategoryRequest;
use App\Http\Resources\Catalog\ServiceCategoryResource;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search'   => 'nullable|string|max:100',
            'active'   => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        
        $query = ServiceCategory::query();
    
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }
    
        return ServiceCategoryResource::collection(
            $query->orderBy('code')
                ->paginate($request->integer('per_page', 20))
        );
    }

    public function store(StoreServiceCategoryRequest $request): ServiceCategoryResource
    {
        $serviceCategory = ServiceCategory::create($request->validated());

        return new ServiceCategoryResource($serviceCategory);
    }

    public function show(ServiceCategory $serviceCategory): ServiceCategoryResource
    {
        return new ServiceCategoryResource($serviceCategory);
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory): ServiceCategoryResource
    {
        $serviceCategory->update($request->validated());

        return new ServiceCategoryResource($serviceCategory);
    }
    
    public function destroy(ServiceCategory $serviceCategory)
    {
        $serviceCategory->delete();

        return response()->json([
            'message' => 'Service category deleted successfully.'
        ]);
    }
}

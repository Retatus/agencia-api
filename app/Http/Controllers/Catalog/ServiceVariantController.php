<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ServiceVariant\StoreServiceVariantRequest;
use App\Http\Requests\Catalog\ServiceVariant\UpdateServiceVariantRequest;
use App\Http\Resources\Catalog\ServiceVariantResource;
use App\Models\ServiceVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceVariantController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search'   => 'nullable|string|max:100',
            'active'   => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        
        $query = ServiceVariant::query();
    
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }
    
        return ServiceVariantResource::collection(
            $query->orderBy('code')
                ->paginate($request->integer('per_page', 20))
        );
    }

    public function store(StoreServiceVariantRequest $request): ServiceVariantResource
    {
        $serviceVariant = ServiceVariant::create($request->validated());

        return new ServiceVariantResource($serviceVariant);
    }

    public function show(ServiceVariant $serviceVariant): ServiceVariantResource
    {
        return new ServiceVariantResource($serviceVariant);
    }

    public function update(UpdateServiceVariantRequest $request, ServiceVariant $serviceVariant): ServiceVariantResource
    {
        $serviceVariant->update($request->validated());

        return new ServiceVariantResource($serviceVariant);
    }
    
    public function destroy(ServiceVariant $serviceVariant)
    {
        $serviceVariant->delete();

        return response()->json([
            'message' => 'Service variant deleted successfully.'
        ]);
    }
}

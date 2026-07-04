<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\Service\StoreServiceRequest;
use App\Http\Requests\Catalog\Service\UpdateServiceRequest;
use App\Http\Resources\Catalog\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search'   => 'nullable|string|max:100',
            'active'   => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        
        $query = Service::query();
        
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return ServiceResource::collection(
            $query->orderBy('code')
                ->paginate($request->integer('per_page', 20))
        );
    }

    public function store(StoreServiceRequest $request): ServiceResource
    {
        $service = Service::create($request->validated());

        return new ServiceResource($service);
    }

    public function show(Service $service): ServiceResource
    {
        return new ServiceResource($service);
    }

    public function update(UpdateServiceRequest $request, Service $service): ServiceResource 
    {
        $service->update($request->validated());

        return new ServiceResource($service);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully.'
        ]);
    }
}
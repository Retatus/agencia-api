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
        
        $query = Service::query()->with([
            'variants',
            'provider' => function ($q) {
                $q->select('id', 'uuid', 'code', 'business_name');
            },
            'serviceCategory',
        ]);
        
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
        $service->load([
            'provider' => function ($q) {
                $q->select('id', 'uuid', 'code', 'business_name');
            },
            'serviceCategory',
        ]);
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

    public function search(Request $request)
    {
        $search = trim($request->input('search'));

        $services = Service::query()
            ->with([
                'provider:id,business_name',
                'serviceCategory:id,name',
                //'currency:id,code,symbol',
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->where('active', true)
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $services,
        ]);
    }

    public function variants(Service $service)
    {
        $variants = $service->variants()
            ->where('active', true)
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,

            'data' => $variants,
        ]);
    }

    public function prices( Request $request, Service $service, int $variant_id) 
    {
        $request->validate([
            'passenger_type_id' => 'nullable|integer',
        ]);

        $variant = $service->variants()
            ->where('id', $variant_id)
            ->where('active', true)
            ->firstOrFail();

        $prices = $variant->prices()
            ->where('active', true)
            ->when(
                $request->filled('passenger_type_id'),
                fn ($query) =>
                    $query->where(
                        'passenger_type_id',
                        $request->integer('passenger_type_id')
                    )
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $prices,
        ]);
    }
}

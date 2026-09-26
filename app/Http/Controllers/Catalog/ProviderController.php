<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\Provider\StoreProviderRequest;
use App\Http\Requests\Catalog\Provider\UpdateProviderRequest;
use App\Http\Resources\Catalog\ProviderResource;
use App\Http\Resources\Catalog\ProviderSelectResource;
use App\Filters\Catalog\ProviderFilter;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProviderController extends Controller
{
    public function index(Request $request, ProviderFilter $filter)
    {
        $providers = $filter->apply(
                Provider::query()->with(['documentType', 'country'])
            )
            ->paginate(
                $request->integer('per_page', 20)
            );

        return ProviderResource::collection($providers);
    }
    
    public function store(StoreProviderRequest $request): ProviderResource 
    {
        $provider = Provider::create(
            $request->validated()
        );

        $provider->load('documentType');

        return new ProviderResource($provider);
    }
    
    public function show(Provider $provider): ProviderResource 
    {
        $provider->load('documentType');

        return new ProviderResource($provider);
    }

    public function update(UpdateProviderRequest $request, Provider $provider): ProviderResource 
    {
        $provider->update(
            $request->validated()
        );

        $provider->load('documentType');

        return new ProviderResource($provider);
    }
    
    public function destroy(Provider $provider): JsonResponse 
    {
        if ($provider->services()->exists()) {

            return response()->json([
                'success' => false,
                'message' => 'No es posible eliminar el proveedor porque tiene servicios asociados.'
            ], Response::HTTP_CONFLICT);

        }

        $provider->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proveedor eliminado correctamente.'
        ]);
    }
    
    public function select()
    {
        $providers = Provider::select('id', 'business_name')
            ->where('active', true)
            ->orderBy('business_name')
            ->get();
            
        return ProviderSelectResource::collection($providers);
    }
}
<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\PassengerType\StorePassengerTypeRequest;
use App\Http\Requests\Shared\PassengerType\UpdatePassengerTypeRequest;
use App\Http\Resources\Shared\PassengerTypeResource;
use App\Models\PassengerType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PassengerTypeController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search'   => 'nullable|string|max:100',
            'active'   => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        
        $query = PassengerType::query();
    
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }
    
        return PassengerTypeResource::collection(
            $query->orderBy('code')
                ->paginate($request->integer('per_page', 20))
        );
    }

    public function store(StorePassengerTypeRequest $request): PassengerTypeResource
    {
        $passengerType = PassengerType::create($request->validated());

        return new PassengerTypeResource($passengerType);
    }

    public function show(PassengerType $passengerType): PassengerTypeResource
    {
        return new PassengerTypeResource($passengerType);
    }

    public function update(UpdatePassengerTypeRequest $request, PassengerType $passengerType): PassengerTypeResource
    {
        $passengerType->update($request->validated());

        return new PassengerTypeResource($passengerType);
    }
    
    public function destroy(PassengerType $passengerType)
    {
        $passengerType->delete();

        return response()->json([
            'message' => 'Passenger type deleted successfully.'
        ]);
    }
}

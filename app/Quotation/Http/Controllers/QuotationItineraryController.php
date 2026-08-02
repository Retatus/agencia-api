<?php

namespace App\Quotation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Quotation\Http\Requests\QuotationItinerary\StoreQuotationItineraryRequest;
use App\Quotation\Http\Requests\QuotationItinerary\UpdateQuotationItineraryRequest;
use App\Quotation\Http\Resources\QuotationItineraryResource;
use App\Quotation\Models\QuotationItinerary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QuotationItineraryController extends Controller
{
    public function index(Request $request)
    {            
        return QuotationItinerary::orderBy('day_number')->get();
    }
    
    public function store(StoreQuotationItineraryRequest $request): QuotationItineraryResource 
    {
        $quotitationItinerary = QuotationItinerary::create(
            $request->validated()
        );

        $quotitationItinerary->load('items');

        return new QuotationItineraryResource($quotitationItinerary);
    }
    
    public function show(QuotationItinerary $quotitationItinerary): QuotationItineraryResource 
    {
        $quotitationItinerary->load('items');

        return new QuotationItineraryResource($quotitationItinerary);
    }

    public function update(UpdateQuotationItineraryRequest $request, QuotationItinerary $quotitationItinerary): QuotationItineraryResource 
    {
        $quotitationItinerary->update(
            $request->validated()
        );

        $quotitationItinerary->load('items');

        return new QuotationItineraryResource($quotitationItinerary);
    }
    
    public function destroy(QuotationItinerary $quotitationItinerary): JsonResponse 
    {
        if ($quotitationItinerary->items()->exists()) {

            return response()->json([
                'success' => false,
                'message' => 'No es posible eliminar el itinerario porque tiene servicios asociados.'
            ], Response::HTTP_CONFLICT);

        }

        $quotitationItinerary->delete();

        return response()->json([
            'success' => true,
            'message' => 'itinerario eliminado correctamente.'
        ]);
    }
}
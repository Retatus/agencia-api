<?php

namespace App\Quotation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Quotation\Models\QuotationItem;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QuotationItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuotationItem $quotitationItem): JsonResponse 
    { //dd($quotitationItem->toArray());
        $quotitationItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service eliminado correctamente.',
            'object' => $quotitationItem->toArray()
        ]);
    }
}

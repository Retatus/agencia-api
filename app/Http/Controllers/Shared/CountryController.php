<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Listar países
     */
    public function index(): JsonResponse
    {
        $countries = Country::orderBy('name')->get();

        return response()->json($countries);
    }

    /**
     * Crear país
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'iso' => ['required', 'string', 'size:2', 'unique:countries,iso'],
            'phone_code' => ['required', 'string', 'max:10'],
            'flag' => ['nullable', 'string', 'max:10'],
        ]);

        $country = Country::create($validated);

        return response()->json([
            'message' => 'País creado correctamente',
            'data' => $country,
        ], 201);
    }

    /**
     * Mostrar un país
     */
    public function show(Country $country): JsonResponse
    {
        return response()->json($country);
    }

    /**
     * Actualizar país
     */
    public function update(Request $request, Country $country): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'iso' => [
                'required',
                'string',
                'size:2',
                'unique:countries,iso,' . $country->id,
            ],
            'phone_code' => ['required', 'string', 'max:10'],
            'flag' => ['nullable', 'string', 'max:10'],
        ]);

        $country->update($validated);

        return response()->json([
            'message' => 'País actualizado correctamente',
            'data' => $country,
        ]);
    }

    /**
     * Eliminar país
     */
    public function destroy(Country $country): JsonResponse
    {
        $country->delete();

        return response()->json([
            'message' => 'País eliminado correctamente',
        ]);
    }
}
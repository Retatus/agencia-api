<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pais;
use App\Http\Requests\Pais\StorePaisRequest;
use App\Http\Requests\Pais\UpdatePaisRequest;

class PaisController extends Controller
{
    public function index()
    {
        return Pais::orderBy('nombre')->get();
    }

    public function store(StorePaisRequest $request)
    {
        return Pais::create($request->validated());
    }

    public function show($id)
    {
        return Pais::findOrFail($id);
    }

    public function update(UpdatePaisRequest $request, $id)
    {
        $pais = Pais::findOrFail($id);
        $pais->update($request->validated());

        return $pais;
    }

    public function destroy($id)
    {
        Pais::findOrFail($id)->delete();

        return response()->json(['message' => 'Eliminado']);
    }
}

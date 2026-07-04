<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentType\StoreDocumentTypeRequest;
use App\Http\Requests\DocumentType\UpdateDocumentTypeRequest;
use App\Http\Resources\Shared\DocumentTypeResource;
use App\Models\DocumentType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search'   => 'nullable|string|max:100',
            'active'   => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        
        $query = DocumentType::query();
        
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return DocumentTypeResource::collection(
            $query->orderBy('code')
                ->paginate($request->integer('per_page', 20))
        );
    }

    public function store(StoreDocumentTypeRequest $request): DocumentTypeResource
    {
        $documentType = DocumentType::create($request->validated());

        return new DocumentTypeResource($documentType);
    }

    public function show(DocumentType $documentType): DocumentTypeResource
    {
        return new DocumentTypeResource($documentType);
    }

    public function update(UpdateDocumentTypeRequest $request, DocumentType $documentType): DocumentTypeResource 
    {
        $documentType->update($request->validated());

        return new DocumentTypeResource($documentType);
    }

    public function destroy(DocumentType $documentType): JsonResponse
    {
        $documentType->delete();

        return response()->json([
            'message' => 'DocumentType deleted successfully.'
        ]);
    }
}
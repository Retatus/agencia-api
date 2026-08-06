<?php

namespace App\Audit\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

use App\Audit\Actions\GetHistoryAction;
use App\Audit\Http\Resources\HistoryResource;


class HistoryController extends Controller
{
    public function __construct(
        protected GetHistoryAction $getHistoryAction
    ) {
    }

    /**
     * Obtener el historial de una entidad.
     *
     * Ejemplo:
     * GET /api/v1/audit/history?entity_type=Quotation&entity_uuid=xxxx
     */
    public function index(
        Request $request
    ): AnonymousResourceCollection {
        $request->validate([
            'entity_type' => ['required', 'string'],
            'entity_uuid' => ['required', 'uuid'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);
        $history = $this->getHistoryAction->execute(
            $request->string('entity_type')->toString(),
            $request->string('entity_uuid')->toString(),
            $request->integer('per_page', 50)
        );
        return HistoryResource::collection($history);
    }
}
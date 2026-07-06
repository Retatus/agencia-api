<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

abstract class BaseCrudController extends Controller
{
    /**
     * Modelo Eloquent.
     */
    protected string $model;

    /**
     * API Resource.
     */
    protected string $resource;

    /**
     * Filter.
     */
    protected ?string $filter = null;

    /**
     * Relaciones por defecto.
     */
    protected array $with = [];

    /**
     * Orden por defecto.
     */
    protected string $defaultSort = 'id';

    /**
     * Dirección del orden.
     */
    protected string $defaultDirection = 'desc';

    /**
     * Cantidad por defecto.
     */
    protected int $perPage = 20;

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = ($this->model)::query();

        if (!empty($this->with)) {
            $query->with($this->with);
        }

        if ($this->filter) {
            $filter = new $this->filter($request);

            $query = $filter->apply($query);
        }

        $sort = $request->get('sort', $this->defaultSort);

        $direction = $request->get(
            'direction',
            $this->defaultDirection
        );

        $query->orderBy($sort, $direction);

        $perPage = $request->integer(
            'per_page',
            $this->perPage
        );

        return $this->resource::collection(
            $query->paginate($perPage)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    // public function show(Model $model)
    // {
    //     $model->load($this->with);

    //     return new $this->resource($model);
    // }

    public function show($id)
    {
        return new $this->resource(
            $this->findModel($id)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $model = ($this->model)::create(
            $request->validated()
        );

        $model->load($this->with);

        return new $this->resource($model);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Model $model)
    {
        $model->update(
            $request->validated()
        );

        $model->load($this->with);

        return new $this->resource($model);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(Model $model): JsonResponse
    {
        $model->delete();

        return response()->json([
            'message' => 'Deleted successfully.'
        ]);
    }

    protected function findModel($id)
    {
        return ($this->model)::query()
            ->with($this->with)
            ->findOrFail($id);
    }
}
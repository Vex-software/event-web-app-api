<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\EventCategoryResource;
use App\Http\Resources\EventResource;
use App\Models\EventCategory;
use Illuminate\Http\JsonResponse;

class EventCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $query = new EventCategory();
        $per_page = $request->input('paginate', $this->getPerPage());
        if ($request->has('q')) {
            $query = $query->where('name', 'like', '%'.$request->input('q').'%');
        }

        $order = $request->input('order', 'asc');
        $validOrderValues = ['asc', 'desc'];
        if (! in_array($order, $validOrderValues)) {
            $order = 'asc';
        }

        if ($request->has('orderBy')) {
            $orderBy = $request->input('orderBy');
            $validColumns = ['name', 'created_at', 'updated_at'];

            if (in_array($orderBy, $validColumns)) {
                $categories = $query->orderBy($orderBy, $order)->paginate($per_page);
            } else {
                $categories = $query->paginate($per_page);
            }
        } else {
            $categories = $query->paginate($per_page);
        }

        $categories->getCollection()->transform(function ($category) {
            return (new EventCategoryResource($category))->toArray(request());
        });

        return response()->json($categories, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $category = EventCategory::find($id);
        if (! $category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json($category, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get spesific category events
     */
    public function eventCategoryEvents(int $id): JsonResponse
    {
        $category = EventCategory::find($id);
        if (! $category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        $events = $category->events()->paginate($this->getPerPage());

        $events->getCollection()->transform(function ($event) {
            return (new EventResource($event))->toArray(request());
        });

        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Search Categories
     */
    public function searchEventCategory(string $q): JsonResponse
    {
        $categories = EventCategory::where('name', 'like', '%'.$q.'%')->get();

        return response()->json($categories, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\EventCategory;

class EventCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index()
    {
        $categories = EventCategory::all();
        return response()->json($categories, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $category = EventCategory::find($id);
        if (!$category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($category, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get spesific category events
     * @param ind $id
     * @return JsonResponse
     */
    public function eventCategoryEvents(int $id): JsonResponse
    {
        $events = EventCategory::find($id)->events()->paginate($this->getPerPage());
        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Search Categories
     * @param string $q
     * @return JsonResponse
     */
    public function searchEventCategory(string $q): JsonResponse
    {
        $categories = EventCategory::where('name', 'like', '%' . $q . '%')->get();
        return response()->json($categories, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }
}

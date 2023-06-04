<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminAddEventCategoryRequest;
use App\Http\Requests\Admin\AdminUpdateEventCategoryRequest;
use App\Models\EventCategory;
use Illuminate\Http\JsonResponse;

class EventCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = EventCategory::all();

        return response()->json($categories, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function store(AdminAddEventCategoryRequest $request)
    {
        $category = new EventCategory();
        $category->name = $request->name;
        $category->save();

        return response()->json($category, JsonResponse::HTTP_CREATED, [], JSON_UNESCAPED_UNICODE);
    }

    public function show(int $id)
    {
        $category = EventCategory::find($id);
        if (! $category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json($category, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function update(AdminUpdateEventCategoryRequest $request, int $id)
    {
        $category = EventCategory::find($id);
        if (! $category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        $category->name = $request->name;
        $category->save();

        return response()->json($category, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroy(int $id)
    {
        $category = EventCategory::find($id);
        if (! $category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        $category->delete();

        return response()->json(['message' => 'Kategori silindi!'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function restore(int $id)
    {
        $category = EventCategory::onlyTrashed()->find($id);
        if (! $category) {
            return response()->json(['message' => 'Kategori bulunamadı!'], JsonResponse::HTTP_NOT_FOUND, [], JSON_UNESCAPED_UNICODE);
        }
        $category->restore();

        return response()->json(['message' => 'Kategori geri yüklendi!'], JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public function deletedEventCategories()
    {
        $categories = EventCategory::onlyTrashed()->paginate($this->getPerPage());

        return $categories;
    }

    public function search(string $search)
    {
        $events = EventCategory::where('name', 'LIKE', '%'.$search.'%')->get();

        return response()->json($events, JsonResponse::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }
}

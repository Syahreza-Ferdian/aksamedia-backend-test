<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DivisiModel;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;


class DivisiController extends Controller
{
    use ApiResponse;

    public function getDivisions(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation Error', Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $name = htmlspecialchars($request->query('name', ''), ENT_QUOTES, 'UTF-8');
        $per_page_content = 2;

        $cacheKey = 'division_search_' . hash('sha256', $name . $per_page_content . $request->query('page', 1));

        if (Cache::has($cacheKey)) {
            $divisions = Cache::get($cacheKey);
            if (empty($divisions['data'])) {
                return $this->errorResponse('Data divisi tidak ditemukan', Response::HTTP_NOT_FOUND);
            }
            return $this->successResponse('Berhasil mengambil data divisi', $divisions['data'], $divisions['pagination']);
        }

        $query = DivisiModel::when($name, function($query) use ($name) {
            return $query->where('name', 'like', '%' . $name . '%');
        });

        $divisions = $query->paginate($per_page_content);

        if ($divisions->isEmpty()) {
            Cache::put($cacheKey, ['data' => [], 'pagination' => []], now()->addMinutes(15));
            return $this->errorResponse('Data divisi tidak ditemukan', Response::HTTP_NOT_FOUND);
        }

        $response = [
            'data' => [
                'divisions' => $divisions->items()
            ],
            'pagination' => [
                'current_page' => $divisions->currentPage(),
                'total_items' => $divisions->total(),
                'total_pages' => $divisions->lastPage(),
                'per_page' => $divisions->perPage(),
                'next_page_url' => $divisions->nextPageUrl(),
                'prev_page_url' => $divisions->previousPageUrl()
            ]
        ];

        Cache::put($cacheKey, $response, now()->addMinutes(15));

        return $this->successResponse('Berhasil mengambil data divisi', $response['data'], $response['pagination']);
    }
}

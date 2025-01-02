<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeModel;
use Illuminate\Http\Request;
use App\Services\SupabaseUploader;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Response;

class EmployeeController extends Controller
{
    use ApiResponse;

    private $storageService;

    public function __construct(SupabaseUploader $storageService) {
        $this->storageService = $storageService;
    }

    public function addNewEmployee(Request $request) {
        $validatorMessage = [
            'required' => 'Kolom :attribute harus diisi',
            'string' => 'Kolom :attribute harus berupa string',
            'max' => 'Kolom :attribute tidak boleh lebih dari :max karakter',
            'exists' => 'Data :attribute tidak ditemukan',
            'file' => 'Kolom :attribute harus berupa file',
            'mimes' => 'Kolom :attribute harus berupa file gambar dengan format jpg, jpeg, atau png',
            'max' => 'Kolom :attribute tidak boleh lebih dari :max KB',
        ];

        $validator = Validator::make($request->all(), [
            'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'division' => 'required|string|exists:divisi,id',
            'position' => 'required|string|max:255'
        ], $validatorMessage);

        if ($validator->fails()) {
            return $this->errorResponse('Validation Error', Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $file = $request->file('image');
        $cleanedEmployeeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $request->name);
        $filePath = uniqid() . '-' . $cleanedEmployeeName;

        try {
            $this->storageService->uploadFile($file, $filePath);
            $publicUrl = $this->storageService->getPublicUrl($filePath);
        } catch (\Exception $e) {
            return $this->errorResponse('Upload failed', Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        $employee = EmployeeModel::create([
            'name' => $request->name,
            'image' => $publicUrl,
            'phone' => $request->phone,
            'position' => $request->position,
            'divisi_id' => $request->division
        ]);

        if (!$employee) {
            return $this->errorResponse('Failed to add new employee', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse('Employee added successfully', null, null, Response::HTTP_CREATED);
    }

    public function getAllEmployeeData(Request $request) {
        $validatorMessage = [
            'string' => 'Kolom :attribute harus berupa string',
            'max' => 'Kolom :attribute tidak boleh lebih dari :max karakter',
            'exists' => 'Data :attribute tidak ditemukan'
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'divisi_id' => 'nullable|exists:divisi,id',
            'per_page' => 'nullable|integer|min:1'
        ], $validatorMessage);

        if ($validator->fails()) {
            return $this->errorResponse('Validation Error', Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $name = htmlspecialchars($request->query('name', ''), ENT_QUOTES, 'UTF-8');

        $divisi_id = htmlspecialchars($request->query('divisi_id', ''), ENT_QUOTES, 'UTF-8');

        $num_content_per_page = $request->query('per_page', 2);

        $query = EmployeeModel::when($name, function($query) use ($name) {
            return $query->where('name', 'like', '%' . $name . '%');
        })->when($divisi_id, function($query) use ($divisi_id) {
            return $query->where('divisi_id', $divisi_id);
        });

        $employees = $query->with('divisi')->paginate($num_content_per_page);

        $employeeDataResponse = $employees->map(function($employee) {
            return [
                'id' => $employee->id,
                'image' => $employee->image,
                'name' => $employee->name,
                'phone' => $employee->phone,
                'division' => [
                    'id' => $employee->divisi->id,
                    'name' => $employee->divisi->name
                ],
                'position' => $employee->position,
            ];
        });

        $response = [
            'data' => [
                'employees' => $employeeDataResponse
            ],
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'total_items' => $employees->total(),
                'total_pages' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'next_page_url' => $employees->nextPageUrl(),
                'prev_page_url' => $employees->previousPageUrl()
            ]
        ];

        return $this->successResponse('Successfully get employee data', $response['data'], $response['pagination']);
    }

    public function updateEmployee(Request $request, $id) {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation Error', Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        if (!$request->hasAny(['name', 'phone', 'division', 'position']) && !$request->hasFile('image')) {
            return $this->errorResponse('Please fill at least one field to update', Response::HTTP_BAD_REQUEST);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'sometimes|required|file|mimes:jpg,jpeg,png|max:2048',
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:15',
            'division' => 'sometimes|required|string|exists:divisi,id',
            'position' => 'sometimes|required|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation Error', Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $currentEmployeeData = EmployeeModel::find($id);

        $updatedEmployeePhoto = $request->file('image');
        if ($updatedEmployeePhoto) {
            try {
                $filePath = str_replace($this->storageService->getBasePublicUrl(), '', $currentEmployeeData->image);

                $this->storageService->updateFile($updatedEmployeePhoto, $filePath);
            } catch (Exception $e) {
                return $this->errorResponse('Employee Photo Update Failed', Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
            }
        }

        $updatedEmployeeData = $request->only(['name', 'phone', 'position']);
        if ($request->has('division')) {
            $updatedEmployeeData['divisi_id'] = $request->division;
        }
        $currentEmployeeData->update($updatedEmployeeData);

        return $this->successResponse('Employee data updated successfully');
    }

    public function deleteEmployee($id) {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation Error', Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $employee = EmployeeModel::find($id);

        if (!$employee) {
            return $this->errorResponse('Employee not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $filePath = str_replace($this->storageService->getBasePublicUrl(), '', $employee->image);

            $this->storageService->deleteFile($filePath);
        } catch (Exception $e) {
            return $this->errorResponse('Employee Photo Delete Failed', Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        $employee->delete();

        return $this->successResponse('Employee data deleted successfully');
    }
}

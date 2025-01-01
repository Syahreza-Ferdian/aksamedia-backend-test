<?php

namespace App\Traits;

trait ApiResponse {

    protected function successResponse($successMessage, $data = [], $pagination = null, $code = 200) {
        $response = [
            'status' => 'success',
            'message' => $successMessage,
            'data' => $data
        ];

        if ($pagination) {
            $response['pagination'] = $pagination;
        }

        return response()->json($response, $code);
    }

    protected function errorResponse($errMessage, $code, $data = null) {
        $response = [
            'status' => 'error',
            'message' => $errMessage
        ];

        if ($data) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }
}

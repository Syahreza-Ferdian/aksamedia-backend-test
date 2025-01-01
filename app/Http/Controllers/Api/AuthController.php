<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    //
    use ApiResponse;

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Please fill all required fields', Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $credentials = $request->only('username', 'password');

        if (!$token = auth()->guard('api')->attempt($credentials)) {
            return $this->errorResponse('Email or password is incorrect', Response::HTTP_UNAUTHORIZED);
        }

        $responseData = [
            'token' => $token,
            'admin' => auth()->guard('api')->user()
        ];

        return $this->successResponse('Login success', $responseData);
    }
}

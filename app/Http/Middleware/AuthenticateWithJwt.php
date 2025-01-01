<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthenticateWithJwt
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = auth()->guard('api')->user();

            if (!$user) {
                return $this->errorResponse('Unauthorized: User not found', Response::HTTP_UNAUTHORIZED);
            }

            $request->merge(['auth_user' => $user]);

        } catch (TokenExpiredException $e) {
            return $this->errorResponse('Unauthorized: Token has expired', Response::HTTP_UNAUTHORIZED);

        } catch (TokenInvalidException $e) {
            return $this->errorResponse('Unauthorized: Token is invalid', Response::HTTP_UNAUTHORIZED);

        } catch (JWTException $e) {
            return $this->errorResponse('An error occurred while decoding token', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $next($request);
    }
}

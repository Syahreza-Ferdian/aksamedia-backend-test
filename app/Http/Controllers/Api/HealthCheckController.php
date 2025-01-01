<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function check() {
        $health = [
            'status' => 'OK',
            'timestamp' => now()->format('d-m-Y H:i:s'),
            'checks' => []
        ];

        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = 'OK';
        } catch (Exception $e) {
            $health['checks']['database'] = 'ERROR ' . $e->getMessage();
            $health['status'] = 'ERROR';
        }

        $health['checks']['app'] = 'Running';

        return response()->json($health, $health['status'] == 'OK' ? 200 : 500);
    }
}

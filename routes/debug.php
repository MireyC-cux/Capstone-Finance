<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/debug/db', function () {
    try {
        $pdo = DB::connection()->getPdo();
        return [
            'status' => 'Connected',
            'database' => DB::connection()->getDatabaseName(),
            'tables' => DB::select('SHOW TABLES'),
        ];
    } catch (\Exception $e) {
        return [
            'status' => 'Error',
            'message' => $e->getMessage(),
            'connection' => config('database.default'),
            'env' => [
                'DB_CONNECTION' => env('DB_CONNECTION'),
                'DB_DATABASE' => env('DB_DATABASE'),
                'DB_USERNAME' => env('DB_USERNAME'),
                'DB_HOST' => env('DB_HOST'),
            ]
        ];
    }
});

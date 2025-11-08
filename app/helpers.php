<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

if (!function_exists('canView')) {
    function canView(string $key): bool
    {
        // If permissions are missing, try restoring from central_sessions
        if (!Session::has('permissions')) {
            $userEmail = Session::get('user_email');

            if ($userEmail) {
                $centralSession = DB::connection('capstone_central')
                    ->table('central_sessions')
                    ->latest('last_activity')
                    ->get();

                foreach ($centralSession as $cs) {
                    $payload = json_decode($cs->payload, true);
                    if (!empty($payload['user_email']) && $payload['user_email'] === $userEmail) {
                        Session::put('permissions', $payload['permissions'] ?? []);
                        Session::put('user_position', $payload['user_position'] ?? null);
                        break;
                    }
                }
            }
        }

        $permissions = Session::get('permissions', []);
        return in_array($key, $permissions);
    }
}

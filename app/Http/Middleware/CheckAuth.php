<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->query('token')) {
            try{
                $payload = \App\Helpers\JWTHelper::decodeJWT($request->query('token'));
            $request->session()->put([
                'user_email' => $payload["user_email"],
                'user_position' => $payload["user_position"],
                'permissions' => $payload["permissions"] ?? [],
            ]);
            }catch(\Exception $e){
                return redirect()->away('http://3RS-ERP.test/login');

            }
        }

        $userEmail = $request->session()->get('user_email');
        $userPosition = $request->session()->get('user_position');

        if (!$userEmail || !$userPosition) {
            $centralSessions = DB::connection('capstone_central')
                ->table('central_sessions')
                ->latest('last_activity')
                ->get();

            foreach ($centralSessions as $cs) {
                $payload = json_decode($cs->payload, true);
                if (!empty($payload['user_email'])) {
                    $userEmail = $payload['user_email'];
                    $userPosition = $payload['user_position'] ?? null;
                    $permissions = $payload['permissions'] ?? [];

                    $request->session()->put([
                        'user_email' => $userEmail,
                        'user_position' => $userPosition,
                        'permissions' => $permissions,
                    ]);

                    break;
                }
            }
        }

        if (!$userEmail || !$userPosition) {
            return redirect()->away('http://3RS-ERP.test/login');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Exception;

class AuthTransferController extends Controller
{
    public function verify(Request $request)
    {
        try {
            $token = urldecode($request->query('token'));

            // Decrypt token
            $decoded = json_decode(Crypt::decryptString($token), true);

            // Store data in session
            session([
                'user_email'    => $decoded['email'],
                'user_position' => $decoded['position'],
                'logged_in_at'  => now(),
            ]);

            // Redirect to your dashboard or homepage
            return redirect()->away('http://Finance.test');
        } catch (Exception $e) {
            return redirect()->away('http://login.test')->withErrors([
                'token' => 'Invalid or expired token.',
            ]);
        }
    }
}

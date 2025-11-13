<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use App\Models\PositionPermission;
use Illuminate\Contracts\Encryption\DecryptException;
use Exception;
use Illuminate\Support\Facades\DB;

class AuthTransferController extends Controller
{
    public function verify(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response('Missing authentication token.', 400);
        }

        try {
            // 🔐 Decrypt and decode
            $decoded = json_decode(Crypt::decryptString(urldecode($token)), true);

            $email = trim($decoded['email'] ?? '');
            $position = trim(strtolower($decoded['position'] ?? ''));

            if (!$email || !$position) {
                throw new Exception('Invalid token payload.');
            }

            // 🧾 Store basic session data
            session([
                'user_email' => $email,
                'user_position' => ucfirst($position),
                'authenticated_at' => now(),
            ]);

            // ✅ Load permissions from the shared DB
            $allowedPermissions = PositionPermission::query()
                ->whereRaw('LOWER(position) = ?', [$position])
                ->where('is_allowed', true)
                ->with('permission')
                ->get()
                ->pluck('permission.permission_key')
                ->filter()
                ->values()
                ->toArray();

            Session::put('permissions', $allowedPermissions);
            Session::save();

            // ✅ Redirect to Finance dashboard
            return redirect()->away('http://Capstone-Finance.test/finance');

        } catch (DecryptException | Exception $e) {
            return redirect()->away('http://3RS-ERP.test/login')->withErrors([
                'token' => 'Invalid or expired token.',
            ]);
        }
    }

 public function visitModule(Request $request)
{
    $module = $request->query('module');

    // 🔹 Get main user info
    $userEmail = session('user_email');
    $userPosition = session('user_position');

    // 🔹 Acting as logic
    $actingAs = session('acting_as'); // optional, can be set by Admin

    if (!$userEmail) {
        // Log unauthorized access attempt
        DB::table('cross_project_activity_logs')->insert([
            'email' => 'Unknown',
            'position' => 'Unknown',
            'activity' => "Unauthorized attempt to access {$module} module",
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->away('http://3RS-ERP.test/login');
    }

    // 🔹 Determine redirect target
    switch ($module) {
        case 'hr-dashboard':
            $target = 'http://Humanresource.test/HR';
            break;
        case 'employeeprofiles':
            $target = 'http://Humanresource.test/Employeeprofiles';
            break;
        case 'evaluateservices':
            $target = 'http://Humanresource.test/evaluateservices';
            break;
        case 'booking':
            $target = 'http://Humanresource.test/Booking';
            break;
        case 'finance-dashboard':
            $target = 'http://Capstone-Finance.test/finance';
            break;
        default:
            $target = 'http://Capstone-Admin.test/AdminDashboard';
    }

    // 🔹 Log the visit with acting_as info
    DB::table('cross_project_activity_logs')->insert([
        'email' => $userEmail,
        'position' => $userPosition ?? 'Unknown',
        'activity' => $actingAs
            ? "Visited {$module} module acting as {$actingAs}"
            : "Visited {$module} module",
        'ip_address' => $request->ip(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 🔹 Optional: pass acting_as as query param if needed
    if ($actingAs) {
        $target .= '?acting_as=' . urlencode($actingAs);
    }

    return redirect()->away($target);
}


}

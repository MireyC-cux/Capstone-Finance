<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AdminActivityLogger
{
    /**
     * Log admin actions to centralized database
     *
     * @param string|null $targetEmail
     * @param string $module
     * @param string $action
     * @param array|null $changes
     */
    public static function log(?string $targetEmail, string $module, string $action, ?array $changes = null)
    {
        $actorEmail = Session::get('user_email', 'Unknown');
        $actorPosition = Session::get('user_position', 'Unknown');

        DB::connection('capstone_central')->table('admin_activity_logs')->insert([
            'actor_email'  => $actorEmail,
            'target_email' => $targetEmail,
            'module'       => $module,
            'action'       => $action,
            'changes'      => $changes ? json_encode($changes) : null,
            'ip_address'   => request()->ip(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Also record in cross_project_activity_logs for cross-project visibility
        DB::table('cross_project_activity_logs')->insert([
            'email'       => $actorEmail,
            'position'    => $actorPosition,
            'activity'    => $action . " in " . $module,
            'ip_address'  => request()->ip(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}

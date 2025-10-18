<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\AccountsPayable;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            AccountsPayable::whereIn('status', ['Unpaid', 'Partially Paid'])
                ->whereDate('due_date', '<', Carbon::today()->toDateString())
                ->update(['status' => 'Overdue']);
        })->dailyAt('01:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}

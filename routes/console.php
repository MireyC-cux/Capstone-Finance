<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ar:mark-overdue', function () {
    $affected = DB::table('accounts_receivable')
        ->whereDate('due_date', '<', now()->toDateString())
        ->whereIn('status', ['Unpaid', 'Partially Paid'])
        ->update(['status' => 'Overdue']);
    $this->info("{$affected} AR rows marked as Overdue.");
})->purpose('Mark overdue Accounts Receivable based on due_date');

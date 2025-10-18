<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\BusinessFinancial;
use App\Models\CashFlow;
use App\Models\ActivityLog;

class CapitalController extends Controller
{
    public function setCapital(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $bf = BusinessFinancial::lockForUpdate()->first();
            if (!$bf) {
                $bf = new BusinessFinancial();
                $bf->as_of_date = now()->toDateString();
            }
            $bf->capital = number_format((float)$data['amount'], 2, '.', '');
            if (!empty($data['remarks'])) {
                $bf->remarks = $data['remarks'];
            }
            $bf->save();
        });

        return back()->with('success', 'Capital has been set.');
    }

    public function inject(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'account_id' => 'nullable|integer|exists:cash_accounts,account_id',
            'remarks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $bf = BusinessFinancial::lockForUpdate()->first();
            if (!$bf) {
                $bf = new BusinessFinancial();
                $bf->as_of_date = now()->toDateString();
                $bf->capital = 0;
                $bf->total_inflows = 0;
                $bf->total_outflows = 0;
                $bf->save();
            }

            $bf->capital = number_format(((float)$bf->capital + (float)$data['amount']), 2, '.', '');
            $bf->as_of_date = now()->toDateString();
            $bf->save();

            CashFlow::create([
                'transaction_type' => 'Inflow',
                'source_type' => 'Other',
                'source_id' => null,
                'account_id' => $data['account_id'] ?? null,
                'amount' => $data['amount'],
                'transaction_date' => Carbon::parse($data['date'])->toDateString(),
                'description' => 'Owner Capital Injection' . (!empty($data['remarks']) ? (': '.$data['remarks']) : ''),
            ]);

            ActivityLog::create([
                'event_type' => 'capital_injected',
                'title' => 'Capital injected: ₱'.number_format((float)$data['amount'], 2),
                'context_type' => 'CashFlow',
                'context_id' => null,
                'amount' => $data['amount'],
                'meta' => [
                    'account_id' => $data['account_id'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'date' => Carbon::parse($data['date'])->toDateString(),
                ],
            ]);
        });

        return back()->with('success', 'Capital injection recorded.');
    }

    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'account_id' => 'nullable|integer|exists:cash_accounts,account_id',
            'remarks' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $bf = BusinessFinancial::lockForUpdate()->firstOrFail();
            $newCapital = (float)$bf->capital - (float)$data['amount'];
            if ($newCapital < -0.0001) {
                abort(422, 'Withdrawal exceeds current capital.');
            }

            $bf->capital = number_format($newCapital, 2, '.', '');
            $bf->as_of_date = now()->toDateString();
            $bf->save();

            CashFlow::create([
                'transaction_type' => 'Outflow',
                'source_type' => 'Other',
                'source_id' => null,
                'account_id' => $data['account_id'] ?? null,
                'amount' => $data['amount'],
                'transaction_date' => Carbon::parse($data['date'])->toDateString(),
                'description' => 'Owner Capital Withdrawal' . (!empty($data['remarks']) ? (': '.$data['remarks']) : ''),
            ]);

            ActivityLog::create([
                'event_type' => 'capital_withdrawn',
                'title' => 'Capital withdrawn: ₱'.number_format((float)$data['amount'], 2),
                'context_type' => 'CashFlow',
                'context_id' => null,
                'amount' => $data['amount'],
                'meta' => [
                    'account_id' => $data['account_id'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'date' => Carbon::parse($data['date'])->toDateString(),
                ],
            ]);
        });

        return back()->with('success', 'Capital withdrawal recorded.');
    }
}

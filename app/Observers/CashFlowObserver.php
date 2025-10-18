<?php

namespace App\Observers;

use App\Models\CashFlow;
use App\Models\BusinessFinancial;
use App\Models\CashAccount;
use Illuminate\Support\Facades\DB;

class CashFlowObserver
{
    public function created(CashFlow $cashFlow): void
    {
        $this->applyAccountDelta($cashFlow, null, null);
        BusinessFinancial::recalcTotals();
    }

    public function updated(CashFlow $cashFlow): void
    {
        $origAmount = (float)$cashFlow->getOriginal('amount');
        $origType = $cashFlow->getOriginal('transaction_type');
        $origAccount = $cashFlow->getOriginal('account_id');
        $this->applyAccountDelta($cashFlow, $origAccount, $origType, $origAmount);
        BusinessFinancial::recalcTotals();
    }

    public function deleted(CashFlow $cashFlow): void
    {
        $this->revertAccountDelta($cashFlow);
        BusinessFinancial::recalcTotals();
    }

    protected function applyAccountDelta(CashFlow $new, $origAccountId = null, $origType = null, $origAmount = null): void
    {
        DB::transaction(function () use ($new, $origAccountId, $origType, $origAmount) {
            if ($origAccountId && $origAccountId != $new->account_id) {
                $this->adjustAccount((int)$origAccountId, $origType ?? $new->transaction_type, $origAmount ?? (float)$new->amount, true);
            } elseif ($origAccountId && $origAccountId == $new->account_id && $origAmount !== null) {
                $delta = (float)$new->amount - (float)$origAmount;
                if (abs($delta) > 0.0001) {
                    $this->adjustAccount((int)$new->account_id, $new->transaction_type, $delta, false, true);
                }
            }

            if ($new->account_id) {
                $this->adjustAccount((int)$new->account_id, $new->transaction_type, (float)$new->amount, false);
            }
        });
    }

    protected function revertAccountDelta(CashFlow $old): void
    {
        if ($old->account_id) {
            $type = $old->transaction_type === 'Inflow' ? 'Outflow' : 'Inflow';
            $this->adjustAccount((int)$old->account_id, $type, (float)$old->amount, false);
        }
    }

    protected function adjustAccount(int $accountId, string $type, float $amount, bool $revertOriginal = false, bool $deltaOnly = false): void
    {
        $account = CashAccount::lockForUpdate()->find($accountId);
        if (!$account) return;

        $delta = $amount;
        if ($deltaOnly) {
            // amount is delta already
        }
        if ($type === 'Inflow') {
            $account->balance = number_format((float)$account->balance + $delta, 2, '.', '');
        } else {
            $account->balance = number_format((float)$account->balance - $delta, 2, '.', '');
        }
        $account->save();
    }
}

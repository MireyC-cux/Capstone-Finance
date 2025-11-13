<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForecastController extends Controller
{
    public function index()
    {
        $script = base_path('analytics/forecasting.py');
        $cmd = 'python ' . escapeshellarg($script) . ' 3';
        $output = shell_exec($cmd);
        $predictions = json_decode($output ?? '', true);

        return response()->json($predictions ?? ['error' => 'Unable to generate forecast']);
    }

    public function json(Request $request)
    {
        $months = (int)($request->query('months', 3));
        $script = base_path('analytics/forecasting.py');

        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', '');
        $dbName = env('DB_DATABASE', 'sacstms');

        $python = 'python';
        $cmd = $python . ' ' . escapeshellarg($script) . ' ' . escapeshellarg((string)$months) . ' ' .
            escapeshellarg($dbHost) . ' ' . escapeshellarg($dbUser) . ' ' . escapeshellarg($dbPass) . ' ' . escapeshellarg($dbName);

        $output = shell_exec($cmd);

        if (!$output) {
            $python = 'python3';
            $cmd = $python . ' ' . escapeshellarg($script) . ' ' . escapeshellarg((string)$months) . ' ' .
                escapeshellarg($dbHost) . ' ' . escapeshellarg($dbUser) . ' ' . escapeshellarg($dbPass) . ' ' . escapeshellarg($dbName);
            $output = shell_exec($cmd);
        }

        $data = json_decode($output ?? '', true);
        if ($data === null || (is_array($data) && array_key_exists('error', $data))) {
            // Fallback: compute forecast in PHP if Python is unavailable
            try {
                $rows = DB::table('cash_flow')
                    ->selectRaw("DATE_FORMAT(transaction_date,'%Y-%m') as ym,
                                 SUM(CASE WHEN transaction_type='Inflow' THEN amount ELSE 0 END) as inflow,
                                 SUM(CASE WHEN transaction_type='Outflow' THEN amount ELSE 0 END) as outflow")
                    ->groupBy('ym')
                    ->orderBy('ym')
                    ->get();

                $labels = [];
                $inVals = [];
                $outVals = [];
                foreach ($rows as $r) {
                    $labels[] = $r->ym;
                    $inVals[] = (float)($r->inflow ?? 0);
                    $outVals[] = (float)($r->outflow ?? 0);
                }
                $profitVals = [];
                foreach ($inVals as $i => $v) {
                    $profitVals[] = $inVals[$i] - ($outVals[$i] ?? 0);
                }

                $predict = function(array $y, int $months) {
                    $n = count($y);
                    if ($n === 0) return array_fill(0, $months, 0.0);
                    $sumX = $n * ($n + 1) / 2.0;
                    $sumXX = $n * ($n + 1) * (2 * $n + 1) / 6.0;
                    $sumY = array_sum($y);
                    $sumXY = 0.0;
                    for ($i = 1; $i <= $n; $i++) { $sumXY += $i * $y[$i - 1]; }
                    $den = ($n * $sumXX - $sumX * $sumX);
                    $m = $den != 0 ? ($n * $sumXY - $sumX * $sumY) / $den : 0.0;
                    $b = ($sumY - $m * $sumX) / $n;
                    $preds = [];
                    for ($k = 1; $k <= $months; $k++) { $preds[] = max(0.0, round($m * ($n + $k) + $b, 2)); }
                    return $preds;
                };

                $inPred = $predict($inVals, $months);
                $outPred = $predict($outVals, $months);
                $profitPred = [];
                for ($i = 0; $i < $months; $i++) { $profitPred[] = max(0.0, round(($inPred[$i] ?? 0) - ($outPred[$i] ?? 0), 2)); }

                $lastYm = end($labels) ?: date('Y-m');
                $lastMonth = Carbon::createFromFormat('Y-m', $lastYm)->startOfMonth();
                $monthsForecast = [];
                for ($i = 1; $i <= $months; $i++) { $monthsForecast[] = $lastMonth->copy()->addMonths($i)->format('M Y'); }
                $monthsHistory = array_map(function($ym){ return Carbon::createFromFormat('Y-m', $ym)->format('M Y'); }, $labels);

                $avg = function(array $arr){ return count($arr) ? array_sum($arr) / count($arr) : 0.0; };
                $histProfitTail = array_slice($profitVals, -3);
                $growth = ($avg($histProfitTail) == 0) ? 0.0 : ($avg($profitPred) - $avg($histProfitTail)) / max(1e-9, $avg($histProfitTail));
                $trend = $growth > 0.05 ? 'rising' : ($growth < -0.05 ? 'falling' : 'stable');

                $insight = 'Sales performance outlook: Profit is expected to ' . ($trend == 'rising' ? 'improve' : ($trend == 'falling' ? 'decline' : 'remain steady')) .
                    ' over the next ' . $months . ' months (avg change: ' . number_format($growth*100, 1) . '%).';

                $fallback = [
                    'months_history' => $monthsHistory,
                    'months_forecast' => $monthsForecast,
                    'inflow' => [ 'history' => $inVals, 'forecast' => $inPred, 'trend' => $trend, 'growth_rate' => round($growth, 3), 'confidence' => 0.0 ],
                    'outflow' => [ 'history' => $outVals, 'forecast' => $outPred, 'trend' => $trend, 'growth_rate' => round($growth, 3), 'confidence' => 0.0 ],
                    'profit' => [ 'history' => $profitVals, 'forecast' => $profitPred, 'trend' => $trend, 'growth_rate' => round($growth, 3), 'confidence' => 0.0 ],
                    'insight' => $insight,
                ];

                return response()->json($fallback);
            } catch (\Throwable $e) {
                Log::warning('Forecast fallback failed', ['err' => $e->getMessage()]);
                return response()->json(['error' => 'Forecast service unavailable'], 500);
            }
        }

        return response()->json($data);
    }
}

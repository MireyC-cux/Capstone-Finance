<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Payroll;
use App\Models\PayrollDisbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Storage;

class DisbursementController extends Controller
{
   public function index(Request $request)
{
    $start = $request->filled('start') 
        ? Carbon::parse($request->start) 
        : Carbon::today()->startOfMonth();

    $end = $request->filled('end') 
        ? Carbon::parse($request->end) 
        : Carbon::today()->endOfMonth();

    $rows = PayrollDisbursement::with(['employeeProfile', 'payroll'])
        ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()])
        ->orderBy('payment_date', 'desc')
        ->paginate(10);

    return view('finance.disbursements.index', [
        'rows' => $rows,
        'start' => $start->toDateString(),
        'end' => $end->toDateString(),
    ]);
}

public function exportTable(Request $request)
{
    $start = $request->filled('start') 
        ? Carbon::parse($request->start) 
        : Carbon::today()->startOfMonth();

    $end = $request->filled('end') 
        ? Carbon::parse($request->end) 
        : Carbon::today()->endOfMonth();

    // Load disbursements with payroll and employeeProfile
    $rows = PayrollDisbursement::with(['employeeProfile', 'payroll'])
        ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()])
        ->orderBy('payment_date', 'desc')
        ->get();

    $pdf = Pdf::setOptions([
        'defaultFont' => 'DejaVu Sans',
        'isRemoteEnabled' => true,
    ])->loadView('finance.disbursements.table_pdf', [
        'rows' => $rows,
        'period' => $start->format('Y-m-d').' to '.$end->format('Y-m-d'),
    ]);

    return $pdf->download('disbursements_'.$start->format('Ymd').'-'.$end->format('Ymd').'.pdf');
}


public function disburseSalary(Request $request)
{
    // ✅ Validate request data
    $data = $request->validate([
        'payroll_id' => 'required|integer|exists:payrolls,payroll_id',
        'payment_date' => 'required|date',
        'payment_method' => 'required|in:Cash,Bank Transfer,GCash,Check,Other',
        'reference_number' => 'nullable|string|max:255',
        'account_id' => 'nullable|integer|exists:cash_accounts,account_id',
        'proof_of_payment' => 'required|image|max:2048', // required image
    ]);

    $payroll = Payroll::with('employeeprofiles')->findOrFail($data['payroll_id']);

    DB::transaction(function () use ($payroll, $data, $request) {

        // ✅ Handle proof_of_payment upload (store in storage/app/public/proofs)
        $proofFileName = null;
        if ($request->hasFile('proof_of_payment')) {
            $file = $request->file('proof_of_payment');

            // Sanitize filename and add timestamp
            $proofFileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                . '.' . $file->getClientOriginalExtension();

            // ✅ Store directly to the "public" disk under "proofs/"
            // This saves to: storage/app/public/proofs
            $file->storeAs('proofs', $proofFileName, 'public');
        }

        // ✅ Create disbursement record
        PayrollDisbursement::create([
            'payroll_id' => $payroll->payroll_id,
            'employeeprofiles_id' => $payroll->employeeprofiles_id,
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'reference_number' => $data['reference_number'] ?? null,
            'proof_of_payment' => $proofFileName,
            'status' => 'Released',
        ]);

        // ✅ Update payroll status
        $payroll->update(['status' => 'Released']);

        // ✅ Record transaction in CashFlow
        CashFlow::create([
            'transaction_type' => 'Outflow',
            'source_type' => 'Expense',
            'source_id' => $payroll->payroll_id,
            'account_id' => $data['account_id'] ?? null,
            'amount' => $payroll->net_pay,
            'transaction_date' => $data['payment_date'],
            'description' => 'Salary disbursement for payroll #' . $payroll->payroll_id,
        ]);

        // ✅ Log activity
        ActivityLog::create([
            'event_type' => 'payroll_released',
            'title' => 'Payroll #' . $payroll->payroll_id . ' released (₱' . number_format((float)$payroll->net_pay, 2) . ')',
            'context_type' => 'Payroll',
            'context_id' => $payroll->payroll_id,
            'amount' => $payroll->net_pay,
            'meta' => [
                'employeeprofiles_id' => $payroll->employeeprofiles_id,
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'proof_of_payment' => $proofFileName,
            ],
        ]);
    });

    return back()->with('success', 'Payroll successfully released and status updated.');
}

    public function showProof(string $filename)
    {
        // Preferred: public disk (storage/app/public/proofs)
        $path = 'proofs/' . $filename;
        if (Storage::disk('public')->exists($path)) {
            // Resolve the absolute path on the public disk and return a file response
            $fullPath = Storage::disk('public')->path($path);
            return response()->file($fullPath);
        }

        // Fallback: legacy path if files were stored under storage/public/proofs
        $legacy = storage_path('public/proofs/' . $filename);
        if (is_file($legacy)) {
            return response()->file($legacy);
        }

        abort(404);
    }

}

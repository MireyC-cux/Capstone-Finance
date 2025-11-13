@extends('layouts.finance_app')

@section('title', 'Disbursed Payroll')

@section('content')
<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Disbursed Payroll</h1>
            <div class="text-muted small">All payroll disbursements within the selected period.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <form method="GET" action="{{ route('finance.disbursement.export') }}" class="d-inline">
                <input type="hidden" name="start" value="{{ $start }}">
                <input type="hidden" name="end" value="{{ $end }}">
                <button class="btn btn-dark btn-sm">Export PDF</button>
            </form>
            <a href="{{ route('finance.payroll') }}" class="btn btn-success btn-sm">Payroll</a>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Start</label>
                    <input type="date" name="start" value="{{ $start }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">End</label>
                    <input type="date" name="end" value="{{ $end }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-primary btn-sm w-100">Apply Filters</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-nowrap">
                        <th>Payment Date</th>
                        <th>Employee</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Proof of Payment</th> <!-- NEW COLUMN -->
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $d)
                    <tr>
                        <td>{{ $d->payment_date->format('Y-m-d') }}</td>
                        <td>
                            {{ optional($d->employeeProfile)->last_name ?? 'N/A' }},
                            {{ optional($d->employeeProfile)->first_name ?? '' }}
                        </td>
                        <td>{{ $d->payment_method ?? '-' }}</td>
                        <td>{{ $d->reference_number ?? '-' }}</td>
                        <td>
                            @if($d->proof_of_payment)
                                <a href="{{ route('finance.disbursement.proof', $d->proof_of_payment) }}" target="_blank">
                                    <img src="{{ route('finance.disbursement.proof', $d->proof_of_payment) }}" 
                                         alt="Proof of Payment" 
                                         class="img-thumbnail" 
                                         style="width: 60px; height: auto;">
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end fw-bold text-primary">
                            ₱ {{ number_format($d->payroll->net_pay ?? 0, 2) }}
                        </td>
                        <td>
                            <span class="badge 
                                {{ $d->status === 'Paid' ? 'bg-success' : 
                                   ($d->status === 'Pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                {{ $d->status ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-4 text-center text-muted">
                            No disbursements found for this period.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $rows->appends(['start' => $start, 'end' => $end])->links('pagination::bootstrap-5') }}
    </div>

</div>
@endsection

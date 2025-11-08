@extends('layouts.finance_app')

@section('title', 'Payroll Management')

@section('content')

<div class="container-fluid py-4">


<!-- Page Header -->
<div>
    <h1 class="h3 fw-bold mb-1">Payroll Management</h1>
    <div class="text-muted small">Managing payroll via semi-monthly.</div>
</div>

<!-- Action Bar -->
<div class="d-flex justify-content-end mb-3">
    <div class="d-flex flex-wrap gap-2">
        <form id="exportPayrollForm" method="GET" action="{{ route('finance.payroll.export') }}" class="d-inline">
            <input type="hidden" name="employee" value="{{ $filters['employee'] ?? '' }}">
            <input type="hidden" name="position" value="{{ $filters['position'] ?? '' }}">
            <input type="hidden" name="status" value="Approved">
            <button type="submit" class="btn btn-dark btn-sm">Export PDF</button>
        </form>
        <a href="{{ route('finance.disbursement.index') }}" class="btn btn-success btn-sm">Disbursed Payroll</a>
        <button id="openGenerateModal" type="button" class="btn btn-warning btn-sm text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#generateModal">Generate Payroll</button>
    </div>
</div>

<!-- Filter Section -->
<form method="GET" class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold mb-1">Employee</label>
                <input type="text" name="employee" value="{{ $filters['employee'] ?? '' }}" class="form-control form-control-sm" placeholder="Name">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold mb-1">Position</label>
                <input type="text" name="position" value="{{ $filters['position'] ?? '' }}" class="form-control form-control-sm" placeholder="e.g. Technician">
            </div>
            <div class="col-12 col-md-2 text-end">
                <button class="btn btn-primary btn-sm w-100">Apply Filters</button>
            </div>
        </div>
    </div>
</form>

<!-- Payroll Table -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-nowrap">
                    <th>Select</th>
                    <th>Employee</th>
                    <th>Position</th>
                    <th>Pay Period</th>
                    <th>Days Worked</th>
                    <th>OT (hrs)</th>
                    <th>OT Pay</th>
                    <th>Salary Rate</th>
                    <th>Basic Salary</th>
                    <th>Gross Pay</th>
                    <th>Tax</th>
                    <th>SSS</th>
                    <th>PhilHealth</th>
                    <th>Pag-IBIG</th>
                    <th>Deductions</th>
                    <th>Bonuses</th>
                    <th>Bonus Amount</th>
                    <th>Cash Advance</th>
                    <th>Net Pay</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                      @php
                        $p = $r['payroll'];
                        $canDisburse = $p && $p->admin_approval === 'Approved Release' && $p->status !== 'Released';
                        $canViewPayslip = $p && $p->status === 'Released';
                        $isPending = $p && $p->status === 'Pending';
                    @endphp
                    <tr>
                        <td><input type="checkbox" class="form-check-input emp-checkbox" value="{{ $r['employee']->employeeprofiles_id }}"></td>
                        <td class="fw-semibold">{{ $r['employee']->last_name }}, {{ $r['employee']->first_name }}</td>
                        <td>{{ $r['position'] }}</td>
                        <td class="text-muted">{{ $r['period'] }}</td>
                        <td>{{ $r['days_worked'] }}</td>
                        <td>{{ $r['ot_hours'] ?? 0 }}</td>
                        <td>₱ {{ number_format($r['ot_pay'],2) }}</td>
                        <td>₱ {{ number_format($p->salary_rate ?? 0,2) }}</td>
                        <td>₱ {{ number_format($p->basic_salary ?? 0,2) }}</td>
                        <td>₱ {{ number_format($p->gross_pay ?? 0,2) }}</td>
                        <td class="text-danger">₱ {{ number_format($p->tax_deduction ?? 0,2) }}</td>
                        <td class="text-danger">₱ {{ number_format($p->sss_contribution ?? 0,2) }}</td>
                        <td class="text-danger">₱ {{ number_format($p->philhealth_contribution ?? 0,2) }}</td>
                        <td class="text-danger">₱ {{ number_format($p->pagibig_contribution ?? 0,2) }}</td>
                        <td class="text-danger">₱ {{ number_format($p->deductions ?? 0,2) }}</td>
                        <td>{{ $p->bonuses ?? '—' }}</td>
                        <td>₱ {{ number_format($p->bonus_amount ?? 0,2) }}</td>
                        <td class="text-warning">₱ {{ number_format($r['cash_advance'] ?? 0,2) }}</td>
                        <td class="fw-bold text-primary">₱ {{ number_format($p->net_pay ?? $r['net'],2) }}</td>
                        <td>
                            <span class="badge {{ $r['status']==='Approved' ? 'bg-success' : ($r['status']==='Paid' ? 'bg-primary' : ($r['status']==='Pending' ? 'bg-warning text-dark' : 'bg-secondary')) }}">{{ $r['status'] }}</span>
                        </td>
                       
                        <td>
                            @if($r['payroll'])
                                @php 
    $isPending = ($r['status'] === 'Pending');
    $canDisburse = ($approvalStatus === 'Approved Release'); // <— change here
@endphp

                                <div class="d-flex gap-2">
                                    <a href="{{ route('finance.payroll.payslip', $p->payroll_id) }}" class="btn btn-outline-warning btn-sm {{ $isPending ? 'disabled' : '' }}" @if($isPending) aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.65;" @endif>Payslip</a>
                                    <button type="button" class="btn btn-outline-primary btn-sm disburse-btn" data-id="{{ $p->payroll_id }}" @if(!$canDisburse) disabled @endif>Disburse</button>
                                </div>
                            @else
                                <span class="text-muted">No payroll</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="23" class="py-4 text-center text-muted">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
      @php
          $totalGross = 0;
          $totalNet = 0;
          $sumBasic = 0;
          $sumBonus = 0;
          $sumOt = 0;
          $sumTax = 0;
          $sumSss = 0;
          $sumPhil = 0;
          $sumPagibig = 0;
          $sumCash = 0;
          $sumDeductions = 0;
          foreach ($rows as $row) {
              $gross = data_get($row, 'payroll.gross_pay', 0) ?: 0;
              $net = data_get($row, 'payroll.net_pay');
              if ($net === null) {
                  $net = data_get($row, 'net', 0);
              }
              $totalGross += $gross;
              $totalNet += ($net ?: 0);

              $sumBasic += data_get($row, 'payroll.basic_salary', 0) ?: 0;
              $sumBonus += data_get($row, 'payroll.bonus_amount', 0) ?: 0;
              $sumOt += data_get($row, 'payroll.overtime_pay', 0) ?: 0;
              $sumTax += data_get($row, 'payroll.tax_deduction', 0) ?: 0;
              $sumSss += data_get($row, 'payroll.sss_contribution', 0) ?: 0;
              $sumPhil += data_get($row, 'payroll.philhealth_contribution', 0) ?: 0;
              $sumPagibig += data_get($row, 'payroll.pagibig_contribution', 0) ?: 0;
              $sumCash += data_get($row, 'payroll.cash_advance', data_get($row, 'cash_advance', 0)) ?: 0;
              $sumDeductions += data_get($row, 'payroll.deductions', 0) ?: 0;
          }
          $grandTotal = $totalNet;
      @endphp
      <div class="row g-2">
  <div class="col-12 col-md-4">
    <div class="small text-muted">
      Total Gross Pay: 
      <span class="fw-bold text-dark">₱ {{ number_format($totalGross,2) }}</span>
     
    </div>
  </div>

  <div class="col-12 col-md-4">
    <div class="small text-muted">
      Total Net Pay: 
      <span class="fw-bold text-dark">₱ {{ number_format($totalNet,2) }}</span>
     
    </div>
  </div>

 <div class="col-12 col-md-4">
  <div class="small text-muted">
    Grand Total: 
    <span class="fw-bold text-primary">₱ {{ number_format($grandTotal,2) }}</span>
    <button type="button" 
            class="btn btn-outline-primary btn-sm ms-2 px-2 py-1 rounded-pill shadow-sm"
            data-bs-toggle="modal" 
            data-bs-target="#totalsBreakdownModal">
      View
    </button>
  </div>
</div>

</div>

<div class="d-flex justify-content-end mt-4">
    <form action="{{ route('finance.payroll.sendApproval') }}" method="POST" class="d-inline">
        @csrf

        <input type="hidden" name="total_gross" value="{{ $totalGross }}">
        <input type="hidden" name="total_net" value="{{ $totalNet }}">
        <input type="hidden" name="grand_total" value="{{ $grandTotal }}">

        @if($approvalStatus === 'Approved Release')
            <span class="badge bg-success px-4 py-2 rounded-pill shadow-sm">
                {{ $approvalStatus }}
            </span>
        @else
            <button type="submit"
                    class="btn btn-dark btn-sm px-4 py-2 rounded-pill shadow-sm d-flex align-items-center gap-1">
                <i class="bi bi-send-fill me-1"></i> Send for Approval
            </button>
        @endif
    </form>
</div>


</div>





<div class="modal fade" id="totalsBreakdownModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Totals Breakdown</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between"><div class="small text-muted">Basic Salary</div><div class="fw-semibold">₱ {{ number_format($sumBasic,2) }}</div></div>
        <div class="d-flex justify-content-between"><div class="small text-muted">Bonus Amount</div><div class="fw-semibold">₱ {{ number_format($sumBonus,2) }}</div></div>
        <div class="d-flex justify-content-between"><div class="small text-muted">Overtime Pay</div><div class="fw-semibold">₱ {{ number_format($sumOt,2) }}</div></div>
        <hr class="my-2">
        <div class="d-flex justify-content-between"><div class="small text-muted">Income Tax</div><div class="fw-semibold">₱ {{ number_format($sumTax,2) }}</div></div>
        <div class="d-flex justify-content-between"><div class="small text-muted">SSS</div><div class="fw-semibold">₱ {{ number_format($sumSss,2) }}</div></div>
        <div class="d-flex justify-content-between"><div class="small text-muted">PhilHealth</div><div class="fw-semibold">₱ {{ number_format($sumPhil,2) }}</div></div>
        <div class="d-flex justify-content-between"><div class="small text-muted">Pag-IBIG</div><div class="fw-semibold">₱ {{ number_format($sumPagibig,2) }}</div></div>
        <div class="d-flex justify-content-between"><div class="small text-muted">Cash Advance</div><div class="fw-semibold">₱ {{ number_format($sumCash,2) }}</div></div>
        <div class="d-flex justify-content-between"><div class="small text-muted">Total of Deductions</div><div class="fw-semibold">₱ {{ number_format($sumDeductions,2) }}</div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Generate Payroll Modal (Bootstrap) -->
<div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Generate Payroll</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('finance.payroll.generate') }}">
        @csrf
        <div class="modal-body">
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label small mb-1">Period Start</label>
              <input type="date" name="period_start" value="{{ $period_start }}" class="form-control form-control-sm" required>
            </div>
            <div class="col-6">
              <label class="form-label small mb-1">Period End</label>
              <input type="date" name="period_end" value="{{ $period_end }}" class="form-control form-control-sm" required>
            </div>
          </div>
          <div class="mt-2">
            <label class="form-label small mb-1">Selected Employees</label>
            <div id="selectedEmployees" class="small text-muted">None</div>
          </div>
          <div id="employeeIdsContainer"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Save as Pending</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Disbursement Modal (Bootstrap) -->
<div class="modal fade" id="disbursementModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Record Salary Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="{{ route('finance.disbursement.record') }}">
        @csrf
        <input type="hidden" name="payroll_id" id="disbursePayrollId">
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label small mb-1">Payment Date</label>
            <input type="date" name="payment_date" class="form-control form-control-sm" required>
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1">Method</label>
            <select name="payment_method" class="form-select form-select-sm" required>
              <option>Cash</option>
              <option>Bank Transfer</option>
              <option>GCash</option>
              <option>Check</option>
              <option>Other</option>
            </select>
          </div>
          <div>
            <label class="form-label small mb-1">Reference No.</label>
            <input type="text" name="reference_number" class="form-control form-control-sm" placeholder="Optional">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Record Payment</button>
        </div>
      </form>
    </div>
  </div>
  </div>
@endsection

@push('scripts')
<script>
    // Sweet alerts for flash messages
    @if (session('success'))
    Swal.fire({ icon: 'success', title: 'Success', text: @json(session('success')), confirmButtonColor: '#06b6d4' });
    @endif
    @if (session('error'))
    Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')), confirmButtonColor: '#ef4444' });
    @endif

    // Bootstrap modals, append to body to avoid inline display
    const genModalEl = document.getElementById('generateModal');
    const apprModalEl = document.getElementById('approvalModal');
    const disbModalEl = document.getElementById('disbursementModal');
    const totalsModalEl = document.getElementById('totalsBreakdownModal');
    const mainContent = document.querySelector('.main-content');
    [genModalEl, apprModalEl, disbModalEl, totalsModalEl].forEach(el => { if (el && el.parentElement !== document.body) document.body.appendChild(el); });

    const genModal = genModalEl ? new bootstrap.Modal(genModalEl) : null;
    const apprModal = apprModalEl ? new bootstrap.Modal(apprModalEl) : null;
    const disbModal = disbModalEl ? new bootstrap.Modal(disbModalEl) : null;
    const totalsModal = totalsModalEl ? new bootstrap.Modal(totalsModalEl) : null;

    const openGenBtn = document.getElementById('openGenerateModal');
    openGenBtn?.addEventListener('click', () => {
        const ids = Array.from(document.querySelectorAll('.emp-checkbox:checked')).map(cb => cb.value);
        const container = document.getElementById('employeeIdsContainer');
        container.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'employee_ids[]';
            input.value = id;
            container.appendChild(input);
        });
        document.getElementById('selectedEmployees').textContent = ids.length ? ids.join(', ') : 'None';
        genModal?.show();
    });

    const approvalForm = document.getElementById('approvalForm');
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const action = btn.dataset.action || 'approve';
            approvalForm.action = `{{ url('finance/payroll') }}/${id}/approve`;
            approvalForm.querySelector('select[name="action"]').value = action;
            apprModal?.show();
        });
    });

    const disbursePayrollId = document.getElementById('disbursePayrollId');
    document.querySelectorAll('.disburse-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            disbursePayrollId.value = btn.dataset.id;
            disbModal?.show();
        });
    });
</script>

@endpush

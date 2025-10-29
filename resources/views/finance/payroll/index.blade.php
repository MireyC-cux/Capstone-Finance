@extends('layouts.finance_app')

@section('title', 'Payroll Management')

@section('content')
<div class="container-fluid py-4">

  <div>
      <h1 class="h3 fw-bold mb-1">Payroll Management</h1>
      <div class="text-muted small">Managing payroll via semi-monthly.</div>
    </div>
    <!-- Action Bar -->
    <div class="d-flex justify-content-end mb-3">
        <div class="d-flex flex-wrap gap-2">
            <form id="exportPayrollForm" method="GET" action="{{ route('finance.payroll.export') }}" class="d-inline">
                <input type="hidden" name="employee" value="{{ request('employee') }}">
                <input type="hidden" name="position" value="{{ request('position') }}">
                <input type="hidden" name="status" value="Approved">
                <input type="hidden" name="period_start" value="{{ $period_start }}">
                <input type="hidden" name="period_end" value="{{ $period_end }}">
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
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Period Start</label>
                    <input type="date" name="period_start" value="{{ $period_start }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Period End</label>
                    <input type="date" name="period_end" value="{{ $period_end }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-2 text-end">
                    <button class="btn btn-primary btn-sm w-100">Apply Filters</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Data Table -->
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
                        <th>Deductions</th>
                        <th>Cash Advance</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @php $filterStatus = 'Approved'; @endphp
                @forelse ($rows as $r)
                    @if($r['status'] === 'Approved')
                    <tr>
                        <td><input type="checkbox" class="form-check-input emp-checkbox" value="{{ $r['employee']->employeeprofiles_id }}"></td>
                        <td class="fw-semibold">{{ $r['employee']->last_name }}, {{ $r['employee']->first_name }}</td>
                        <td>{{ $r['position'] }}</td>
                        <td class="text-muted">{{ $r['period'] }}</td>
                        <td>{{ $r['days_worked'] }}</td>
                        <td>{{ $r['ot_hours'] }}</td>
                        <td>₱ {{ number_format($r['ot_pay'],2) }}</td>
                        <td class="text-danger">₱ {{ number_format($r['deductions'],2) }}</td>
                        <td class="text-warning">₱ {{ number_format($r['cash_advance'],2) }}</td>
                        <td class="fw-bold text-primary">₱ {{ number_format($r['net'],2) }}</td>
                        <td>
                            <span class="badge {{ $r['status']==='Approved' ? 'bg-success' : ($r['status']==='Paid' ? 'bg-primary' : ($r['status']==='Pending' ? 'bg-warning text-dark' : 'bg-secondary')) }}">{{ $r['status'] }}</span>
                        </td>
                        <td>
                            @if($r['payroll'])
                                <div class="d-flex gap-2">
                                    <a href="{{ route('finance.payroll.payslip', $r['payroll']->payroll_id) }}" class="btn btn-outline-warning btn-sm">Payslip</a>
                                    <button type="button" class="btn btn-outline-primary btn-sm disburse-btn" data-id="{{ $r['payroll']->payroll_id }}">Disburse</button>
                                </div>
                            @else
                                <span class="text-muted">No payroll</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="12" class="py-4 text-center text-muted">No employees found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
<!-- End container -->

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

<!-- Approval Modal (Bootstrap) -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Payroll Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="approvalForm" method="POST" action="#">
        @csrf
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label small mb-1">Action</label>
            <select name="action" class="form-select form-select-sm">
              <option value="approve">Approve</option>
              <option value="reject">Reject</option>
            </select>
          </div>
          <div>
            <label class="form-label small mb-1">Remarks (optional)</label>
            <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Reason">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success btn-sm">Confirm</button>
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
    const mainContent = document.querySelector('.main-content');
    [genModalEl, apprModalEl, disbModalEl].forEach(el => { if (el && el.parentElement !== document.body) document.body.appendChild(el); });

    const genModal = genModalEl ? new bootstrap.Modal(genModalEl) : null;
    const apprModal = apprModalEl ? new bootstrap.Modal(apprModalEl) : null;
    const disbModal = disbModalEl ? new bootstrap.Modal(disbModalEl) : null;

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

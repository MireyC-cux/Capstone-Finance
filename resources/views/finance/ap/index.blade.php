@extends('layouts.finance_app')

@section('content')
<div style="padding: 20px;">
  <!-- Page Header -->
  <div style="margin-bottom: 2rem;">
    <div class="d-flex justify-content-between align-items-center">
      <a href="{{ route('purchase-orders.index') }}" class="btn btn-primary" style="gap: 0.5rem;">
        <i class="fa fa-list"></i>
        <span>View POs</span>
      </a>
    </div>
  </div>

  @if(session('success'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Success',text:'{{ session('success') }}',confirmButtonColor:'#06b6d4'}));</script>
  @endif
  @if(session('error'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'error',title:'Error',text:'{{ session('error') }}'}));</script>
  @endif

  <!-- Filter Section -->
  <form method="GET" style="margin-bottom: 2rem;">
    <div class="card" style="padding: 1.25rem;">
      <div class="row g-3">
        <div class="col-12 col-md-6 col-xl">
          <label class="form-label">Supplier</label>
          <select name="supplier_id" class="form-select">
            <option value="">All</option>
            @foreach($suppliers as $s)
              <option value="{{ $s->supplier_id }}" @selected(request('supplier_id')==$s->supplier_id)>{{ $s->supplier_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="">All</option>
            @foreach(['Unpaid','Partially Paid','Paid','Overdue','Cancelled'] as $st)
              <option value="{{ $st }}" @selected(request('status')==$st)>{{ $st }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl">
          <label class="form-label">PO#</label>
          <input type="text" name="po_number" value="{{ request('po_number') }}" class="form-control" placeholder="PO Number" />
        </div>
        <div class="col-12 col-md-6 col-xl">
          <label class="form-label">From</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-control" />
        </div>
        <div class="col-12 col-md-6 col-xl">
          <label class="form-label">To</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-control" />
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2" style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary" style="gap: 0.5rem;"><i class="fa fa-filter"></i><span>Apply Filters</span></button>
        <a href="{{ route('accounts-payable.index') }}" class="btn" style="border: 1px solid var(--border-card); background: white; gap: 0.5rem;"><i class="fa fa-rotate"></i><span>Reset</span></a>
      </div>
    </div>
  </form>

  <!-- Metrics Cards -->
  <div class="row g-4" style="margin-bottom: 2rem;">
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
        <div class="d-flex justify-content-between align-items-center">
          <div style="flex: 1;">
            <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Total Payables</div>
            <div style="font-size: 24px; font-weight: 700; color: #2563EB; margin-top: 0.25rem;">₱{{ number_format($stats['total'] ?? 0,2) }}</div>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 8px; background: #2563EB; color: white; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-file-invoice" style="font-size: 20px;"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
        <div class="d-flex justify-content-between align-items-center">
          <div style="flex: 1;">
            <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Paid</div>
            <div style="font-size: 24px; font-weight: 700; color: #10B981; margin-top: 0.25rem;">₱{{ number_format($stats['paid'] ?? 0,2) }}</div>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 8px; background: #10B981; color: white; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-check-circle" style="font-size: 20px;"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
        <div class="d-flex justify-content-between align-items-center">
          <div style="flex: 1;">
            <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Overdue</div>
            <div style="font-size: 24px; font-weight: 700; color: #EF4444; margin-top: 0.25rem;">{{ $stats['overdue'] ?? 0 }}</div>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 8px; background: #EF4444; color: white; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-exclamation-triangle" style="font-size: 20px;"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
        <div class="d-flex justify-content-between align-items-center">
          <div style="flex: 1;">
            <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Partially Paid</div>
            <div style="font-size: 24px; font-weight: 700; color: #F59E0B; margin-top: 0.25rem;">{{ $stats['partial'] ?? 0 }}</div>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 8px; background: #F59E0B; color: white; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-clock" style="font-size: 20px;"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  

  <div style="margin-top: 1rem;">{{ $payables->links() }}</div>

  <!-- Unpaid Expenses -->
  <div style="margin-top: 2rem;">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">Unpaid Expenses</h5>
    </div>
    <div class="card" style="padding: 0; overflow: hidden;">
      <div class="table-responsive">
        <table class="table table-hover align-middle" style="margin-bottom: 0;">
          <thead class="table-white">
            <tr>
              <th style="padding:8px;">Expense</th>
              <th style="padding:8px;">Category</th>
              <th style="padding:8px;">Supplier</th>
              <th style="padding:8px;">Date</th>
              <th style="padding:8px;" class="text-end">Total</th>
              <th style="padding:8px;">Status</th>
              <th style="padding:8px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($unpaidExpenses as $ex)
            <tr>
              <td style="padding:8px; font-weight:600;">{{ $ex->expense_name }}</td>
              <td style="padding:8px;">{{ $ex->category }}</td>
              <td style="padding:8px;">{{ $ex->supplier->supplier_name ?? '—' }}</td>
              <td style="padding:8px;">{{ \Illuminate\Support\Carbon::parse($ex->expense_date)->format('Y-m-d') }}</td>
              <td style="padding:8px; font-weight:600;" class="text-end">₱{{ number_format($ex->amount,2) }}</td>
              <td style="padding:8px;"><span class="badge badge-default">{{ $ex->status ?? 'Unpaid' }}</span></td>
              <td style="padding:8px;">
                <button class="btn btn-sm btn-info exp-view" data-exp-id="{{ $ex->expense_id }}" style="gap:.5rem;"><i class="fa fa-eye"></i> View</button>
              </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted" style="padding:12px;">No unpaid expenses.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div style="margin-top: 1rem;">{{ $unpaidExpenses->links() }}</div>
  </div>

  <!-- Expense Details Modal -->
  <div id="expenseDetailsModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Expense Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><div class="text-muted small">Expense</div><div id="exDetName" class="fw-semibold">—</div></div>
            <div class="col-md-6"><div class="text-muted small">Category</div><div id="exDetCat" class="fw-semibold">—</div></div>
            <div class="col-md-6"><div class="text-muted small">Supplier</div><div id="exDetSupp" class="fw-semibold">—</div></div>
            <div class="col-md-6"><div class="text-muted small">Date</div><div id="exDetDate" class="fw-semibold">—</div></div>
            <div class="col-md-6"><div class="text-muted small">Status</div><div id="exDetStatus" class="fw-semibold">—</div></div>
            <div class="col-md-6"><div class="text-muted small">Total</div><div id="exDetTotal" class="fw-semibold">—</div></div>
            <div class="col-12"><div class="text-muted small">Outstanding</div><div id="exDetOut" class="text-primary fw-bold fs-5">—</div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="exDetRecordBtn">Record Payment</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Expense Record Payment Modal -->
  <div id="expensePaymentModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Record Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <form id="expensePaymentForm" method="POST" action="#" enctype="multipart/form-data">
          <div class="modal-body">
            @csrf
            <input type="hidden" name="expense_id" id="exPmId" />
            <div class="mb-3">
              <label class="form-label">Payment Date</label>
              <input type="date" class="form-control" name="payment_date" id="exPmDate" value="{{ now()->toDateString() }}" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Payment Method</label>
              <select class="form-select" name="payment_method" id="exPmMethod" required>
                <option>Cash</option>
                <option>GCash</option>
                <option>Bank Transfer</option>
                <option>Check</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Payment Type</label>
              <select class="form-select" name="payment_type" id="exPmType" required>
                <option value="Full">Full</option>
                <option value="Partial">Partial</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Reference #</label>
              <input type="text" class="form-control" name="reference_number" id="exPmRef" placeholder="Required if not cash" />
            </div>
            <div class="mb-3">
              <label class="form-label">Official Receipt (Image/PDF)</label>
              <input type="file" class="form-control" name="or_file" id="exPmOr" accept="image/*,application/pdf" />
              <div class="form-text">Required if not cash.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Amount</label>
              <input type="number" class="form-control" min="0.01" step="0.01" name="amount" id="exPmAmount" placeholder="0.00" required />
              <small>Outstanding: <span id="exPmMax" data-value="0">—</span></small>
            </div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Payment</button></div>
        </form>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const exDetailsModal = new bootstrap.Modal(document.getElementById('expenseDetailsModal'));
    const exPayModal = new bootstrap.Modal(document.getElementById('expensePaymentModal'));
    let currentExp = null;

    document.querySelectorAll('.exp-view').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.getAttribute('data-exp-id');
        try {
          const res = await fetch(`/finance/expenses/${id}/summary`, { headers: { 'Accept': 'application/json' } });
          if (!res.ok) throw new Error('Failed to load expense');
          const ex = await res.json();
          currentExp = ex;
          document.getElementById('exDetName').textContent = ex.name;
          document.getElementById('exDetCat').textContent = ex.category || '—';
          document.getElementById('exDetSupp').textContent = ex.supplier || '—';
          document.getElementById('exDetDate').textContent = ex.date;
          document.getElementById('exDetStatus').textContent = ex.status;
          document.getElementById('exDetTotal').textContent = `₱${Number(ex.total).toLocaleString(undefined,{minimumFractionDigits:2})}`;
          document.getElementById('exDetOut').textContent = `₱${Number(ex.outstanding).toLocaleString(undefined,{minimumFractionDigits:2})}`;
          exDetailsModal.show();
        } catch (e) {
          window.Swal && Swal.fire('Error','Unable to load expense details','error');
        }
      });
    });

    document.getElementById('exDetRecordBtn').addEventListener('click', () => {
      if (!currentExp) return;
      const form = document.getElementById('expensePaymentForm');
      form.action = `/finance/expenses/${currentExp.id}/payment`;
      document.getElementById('exPmId').value = currentExp.id;
      const max = document.getElementById('exPmMax');
      max.textContent = `₱${Number(currentExp.outstanding).toLocaleString(undefined,{minimumFractionDigits:2})}`;
      max.dataset.value = String(Number(currentExp.outstanding).toFixed(2));
      const amt = document.getElementById('exPmAmount');
      const typ = document.getElementById('exPmType');
      if ((typ.value||'Full') === 'Full') { amt.value = Number(currentExp.outstanding).toFixed(2); amt.readOnly = true; } else { amt.readOnly = false; amt.value = ''; }
      exDetailsModal.hide();
      exPayModal.show();
    });

    const pmMethod = document.getElementById('exPmMethod');
    const pmRef = document.getElementById('exPmRef');
    const pmOr = document.getElementById('exPmOr');
    pmMethod.addEventListener('change', () => {
      const isCash = (pmMethod.value||'').toLowerCase() === 'cash';
      pmRef.required = !isCash; pmOr.required = !isCash;
    });

    const pmType = document.getElementById('exPmType');
    pmType.addEventListener('change', () => {
      const amt = document.getElementById('exPmAmount');
      const out = parseFloat(document.getElementById('exPmMax').dataset.value || '0');
      if ((pmType.value||'Full') === 'Full') { amt.value = out.toFixed(2); amt.readOnly = true; }
      else { amt.readOnly = false; if (!amt.value || parseFloat(amt.value) >= out) amt.value = ''; }
    });

    document.getElementById('expensePaymentForm').addEventListener('submit', (e) => {
      const out = parseFloat(document.getElementById('exPmMax').dataset.value || '0');
      const amt = parseFloat(document.getElementById('exPmAmount').value || '0');
      const isCash = (pmMethod.value||'').toLowerCase() === 'cash';
      if (!isCash && !pmRef.value.trim()) { e.preventDefault(); return Swal && Swal.fire('Missing Reference','Reference number is required for non-cash payments.','warning'); }
      if (!isCash && !pmOr.files.length) { e.preventDefault(); return Swal && Swal.fire('Missing OR','Please upload the Official Receipt (image/PDF).','warning'); }
      if ((pmType.value||'Full') === 'Full') {
        if (Math.abs(amt - out) > 0.009) { e.preventDefault(); return Swal && Swal.fire('Invalid Amount','Full payment must match the outstanding balance.','error'); }
      } else {
        if (!(amt > 0 && amt < out)) { e.preventDefault(); return Swal && Swal.fire('Invalid Amount','Partial payment must be > 0 and < outstanding.','error'); }
      }
    });
  });
  </script>
</div>
@endsection

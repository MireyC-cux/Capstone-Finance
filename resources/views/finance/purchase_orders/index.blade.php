@extends('layouts.finance_app')

@section('content')
<div style="padding: 20px;">

  <div style="margin-bottom: 2rem;">
    <div class="d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Purchase Orders</h3>
      <a href="{{ route('accounts-payable.index') }}" class="btn btn-primary" style="gap: 0.5rem;"><i class="fa fa-credit-card"></i><span>Accounts Payable</span></a>
    </div>
  </div>

  @if(session('success'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Success',text:'{{ session('success') }}',confirmButtonColor:'#06b6d4'}));</script>
  @endif
  @if(session('error'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'error',title:'Error',text:'{{ session('error') }}'}));</script>
  @endif

  <form method="GET" style="margin-bottom: 2rem;">
    <div class="card" style="padding: 1.25rem;">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-md">
          <label class="form-label">Supplier</label>
          <input type="text" name="supplier" value="{{ request('supplier') }}" class="form-control" placeholder="Search supplier" />
        </div>
        <div class="col-12 col-md">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="">All</option>
            @foreach(['Pending','Approved','Rejected','Completed'] as $st)
              <option value="{{ $st }}" @selected(request('status')==$st)>{{ $st }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md">
          <label class="form-label">PO#</label>
          <input type="text" name="po_number" value="{{ request('po_number') }}" class="form-control" />
        </div>
        <div class="col-12 col-md">
          <label class="form-label">From</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-control" />
        </div>
        <div class="col-12 col-md">
          <label class="form-label">To</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-control" />
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2" style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary" style="gap: 0.5rem;"><i class="fa fa-filter"></i><span>Apply Filters</span></button>
        <a href="{{ route('purchase-orders.index') }}" class="btn" style="border: 1px solid var(--border-card); background: white; gap: 0.5rem;"><i class="fa fa-rotate"></i><span>Reset</span></a>
      </div>
    </div>
  </form>

  <div class="row g-4" style="margin-bottom: 2rem;">
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
        <div class="d-flex justify-content-between align-items-center">
          <div style="flex: 1;">
            <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Total POs</div>
            <div style="font-size: 24px; font-weight: 700; color: #2563EB; margin-top: 0.25rem;">{{ $pos->total() }}</div>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 8px; background: #2563EB; color: white; display: flex; align-items: center; justify-content: center;"><i class="fa fa-file-signature" style="font-size: 20px;"></i></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
        <div class="d-flex justify-content-between align-items-center">
          <div style="flex: 1;">
            <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Pending</div>
            <div style="font-size: 24px; font-weight: 700; color: #F59E0B; margin-top: 0.25rem;">{{ $pos->filter(fn($p)=>$p->status==='Pending')->count() }}</div>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 8px; background: #F59E0B; color: white; display: flex; align-items: center; justify-content: center;"><i class="fa fa-clock" style="font-size: 20px;"></i></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
      <table class="table table-hover align-middle" style="margin-bottom: 0;">
        <thead class="table-dark">
          <tr>
            <th style="padding: 8px;">PO#</th>
            <th style="padding: 8px;">Supplier</th>
            <th style="padding: 8px;">Date</th>
            <th style="padding: 8px;">Payment Status</th>
            <th style="padding: 8px;" class="text-end">Total</th>
            <th style="padding: 8px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($pos as $po)
          <tr>
            <td style="padding: 8px; font-weight: 600; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">{{ $po->po_number }}</td>
            <td style="padding: 8px;">{{ $po->supplier->supplier_name ?? '—' }}</td>
            <td style="padding: 8px;">{{ \Illuminate\Support\Carbon::parse($po->po_date)->format('Y-m-d') }}</td>
            <td style="padding: 8px;">
              @php($payStatus = $po->accountsPayable->status ?? 'Unpaid')
              @php($badgeMap = [
                'Unpaid' => 'badge badge-default',
                'Partially Paid' => 'badge badge-partially-paid',
                'Paid' => 'badge badge-paid',
                'Overdue' => 'badge badge-unpaid',
              ])
              <span class="{{ $badgeMap[$payStatus] ?? 'badge badge-default' }}">{{ $payStatus }}</span>
            </td>
            <td style="padding: 8px; font-weight: 600;" class="text-end">₱{{ number_format($po->total_amount,2) }}</td>
            <td style="padding: 8px;">
              <div class="d-flex flex-wrap gap-2">
                <a href="#" class="btn btn-sm btn-info po-view" data-po-id="{{ $po->purchase_order_id }}" style="gap: 0.5rem;"><i class="fa fa-eye"></i> View</a>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-6">{{ $pos->links() }}</div>
</div>

<!-- PO Details Modal -->
<div id="poDetailsModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Purchase Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">PO#</div>
            <div class="fw-semibold" id="poDetNumber">—</div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Supplier</div>
            <div class="fw-semibold" id="poDetSupplier">—</div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Date</div>
            <div class="fw-semibold" id="poDetDate">—</div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Payment Status</div>
            <div class="fw-semibold" id="poDetStatus">—</div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Total</div>
            <div class="fw-semibold" id="poDetTotal">—</div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Paid</div>
            <div class="fw-semibold" id="poDetPaid">—</div>
          </div>
          <div class="col-12">
            <div class="text-muted small">Outstanding</div>
            <div class="text-primary fw-bold fs-5" id="poDetOut">—</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="poDetRecordBtn">Record Payment</button>
      </div>
    </div>
  </div>
 </div>

<!-- Record Payment Modal -->
<div id="poPaymentModal" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Record Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <form id="poPaymentForm" method="POST" action="#" enctype="multipart/form-data">
      <div class="modal-body">
        @csrf
        <input type="hidden" name="po_id" id="poPmId" />
        <div class="mb-3">
          <label class="form-label">Payment Date</label>
          <input type="date" class="form-control" name="payment_date" id="poPmDate" value="{{ now()->toDateString() }}" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Payment Method</label>
          <select class="form-select" name="payment_method" id="poPmMethod" required>
            <option>Cash</option>
            <option>GCash</option>
            <option>Bank Transfer</option>
            <option>Check</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Payment Type</label>
          <select class="form-select" name="payment_type" id="poPmType" required>
            <option value="Full">Full</option>
            <option value="Partial">Partial</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Reference #</label>
          <input type="text" class="form-control" name="reference_number" id="poPmRef" placeholder="Required if not cash" />
        </div>
        <div class="mb-3">
          <label class="form-label">Official Receipt (Image/PDF)</label>
          <input type="file" class="form-control" name="or_file" id="poPmOr" accept="image/*,application/pdf" />
          <div class="form-text">Required if not cash.</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Amount</label>
          <input type="number" class="form-control" min="0.01" step="0.01" name="amount" id="poPmAmount" placeholder="0.00" required />
          <small>Outstanding: <span id="poPmMax" data-value="0">—</span></small>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Payment</button></div>
      </form>
    </div>
  </div>
 </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const detailsModal = new bootstrap.Modal(document.getElementById('poDetailsModal'));
  const payModal = new bootstrap.Modal(document.getElementById('poPaymentModal'));
  let currentPO = null;

  // Open details modal
  document.querySelectorAll('.po-view').forEach(a => {
    a.addEventListener('click', async (e) => {
      e.preventDefault();
      const id = a.getAttribute('data-po-id');
      try {
        const res = await fetch(`/purchase-orders/${id}/summary`, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('Failed to load PO');
        const po = await res.json();
        currentPO = po;
        document.getElementById('poDetNumber').textContent = po.po_number;
        document.getElementById('poDetSupplier').textContent = po.supplier || '—';
        document.getElementById('poDetDate').textContent = po.po_date;
        document.getElementById('poDetStatus').textContent = po.payment_status;
        document.getElementById('poDetTotal').textContent = `₱${Number(po.total).toLocaleString(undefined,{minimumFractionDigits:2})}`;
        document.getElementById('poDetPaid').textContent = `₱${Number(po.paid).toLocaleString(undefined,{minimumFractionDigits:2})}`;
        document.getElementById('poDetOut').textContent = `₱${Number(po.outstanding).toLocaleString(undefined,{minimumFractionDigits:2})}`;
        detailsModal.show();
      } catch (err) {
        window.Swal && Swal.fire('Error','Unable to load PO details','error');
      }
    });
  });

  // From details -> open payment modal
  document.getElementById('poDetRecordBtn').addEventListener('click', () => {
    if (!currentPO) return;
    document.getElementById('poPmId').value = currentPO.id;
    const form = document.getElementById('poPaymentForm');
    form.action = `/purchase-orders/${currentPO.id}/payment`;
    const pmMax = document.getElementById('poPmMax');
    pmMax.textContent = `₱${Number(currentPO.outstanding).toLocaleString(undefined,{minimumFractionDigits:2})}`;
    pmMax.dataset.value = String(Number(currentPO.outstanding).toFixed(2));
    const pmAmount = document.getElementById('poPmAmount');
    const pmType = document.getElementById('poPmType');
    if ((pmType.value||'Full') === 'Full') { pmAmount.value = Number(currentPO.outstanding).toFixed(2); pmAmount.readOnly = true; } else { pmAmount.readOnly = false; pmAmount.value = ''; }
    detailsModal.hide();
    payModal.show();
  });

  // Payment method requirements
  const pmMethod = document.getElementById('poPmMethod');
  const pmRef = document.getElementById('poPmRef');
  const pmOr = document.getElementById('poPmOr');
  pmMethod.addEventListener('change', () => {
    const isCash = (pmMethod.value||'').toLowerCase() === 'cash';
    pmRef.required = !isCash; pmOr.required = !isCash;
  });

  // Payment type behavior
  const pmType = document.getElementById('poPmType');
  pmType.addEventListener('change', () => {
    const pmAmount = document.getElementById('poPmAmount');
    const out = parseFloat(document.getElementById('poPmMax').dataset.value || '0');
    if ((pmType.value||'Full') === 'Full') { pmAmount.value = out.toFixed(2); pmAmount.readOnly = true; }
    else { pmAmount.readOnly = false; if (!pmAmount.value || parseFloat(pmAmount.value) >= out) pmAmount.value = ''; }
  });

  // Validate on submit
  document.getElementById('poPaymentForm').addEventListener('submit', (e) => {
    const out = parseFloat(document.getElementById('poPmMax').dataset.value || '0');
    const amt = parseFloat(document.getElementById('poPmAmount').value || '0');
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
@endsection

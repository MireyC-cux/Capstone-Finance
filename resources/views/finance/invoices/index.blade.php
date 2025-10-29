@extends('layouts.finance_app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Invoice History</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .btn-rounded { border-radius: 1rem; }
    .shadow-soft { box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
    .table-hover tbody tr:hover { background: #f8fafc; }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Invoice History</h3>
    <div class="ms-auto">
      <a class="btn btn-outline-secondary btn-rounded" href="{{ route('finance.billing.index') }}">Back to Billing</a>
    </div>
  </div>

  <div class="card shadow-soft">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead style="background: #ffffff;">
            <tr>
              <th class="py-2">#</th>
              <th class="py-2">Service Request #</th>
              <th class="py-2">Customer</th>
              <th class="py-2">Payment Date</th>
              <th class="py-2">Due Date</th>
              <th class="py-2">Type</th>
              <th class="py-2 text-end">Amount</th>
              <th class="py-2">Status</th>
              <th class="py-2 text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($histories as $h)
              <tr>
                <td>{{ $h->payment_id }}</td>
                <td>{{ $h->serviceRequest->service_request_number ?? $h->service_request_id }}</td>
                <td>{{ $h->serviceRequest->customer->full_name ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($h->payment_date)->format('Y-m-d') }}</td>
                <td>{{ $h->due_date ? \Carbon\Carbon::parse($h->due_date)->format('Y-m-d') : '—' }}</td>
                <td>{{ $h->type_of_payment ?? '—' }}</td>
                <td class="text-end">₱ {{ number_format($h->amount, 2) }}</td>
                <td><span class="badge text-bg-secondary">{{ $h->status }}</span></td>
                <td class="text-end">
                  @php $arId = $arMap[$h->service_request_id] ?? null; @endphp
                  @if($arId)
                  <button type="button" class="btn btn-sm btn-success me-1 record-payment" data-ar-id="{{ $arId }}" title="Record Payment">
                    <i class="fa-solid fa-money-bill"></i>
                  </button>
                  @endif
                  @if(!empty($h->or_file_path))
                  <button type="button" class="btn btn-sm btn-secondary view-or" data-or="{{ asset('storage/'.$h->or_file_path) }}" title="View OR">
                    <i class="fa-regular fa-eye"></i>
                  </button>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{ $histories->links() }}
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const pmModalEl = document.getElementById('paymentModal');
  const pmModal = pmModalEl ? new bootstrap.Modal(pmModalEl) : null;
  document.querySelectorAll('.record-payment').forEach(btn => {
    btn.addEventListener('click', async () => {
      const arId = btn.getAttribute('data-ar-id');
      if (!pmModal) return;
      try {
        const res = await fetch(`/accounts-receivable/${arId}`);
        if (!res.ok) throw new Error('Failed to load AR');
        const ar = await res.json();
        const balance = Math.max(0, Number(ar.total_amount) - Number(ar.amount_paid));
        document.getElementById('pmArId').value = arId;
        const pmAmount = document.getElementById('pmAmount');
        const pmMax = document.getElementById('pmMax');
        pmMax.textContent = `₱${balance.toLocaleString(undefined,{minimumFractionDigits:2})}`;
        pmMax.dataset.value = String(balance.toFixed(2));
        const pmType = document.getElementById('pmType');
        if ((pmType.value||'Full') === 'Full') { pmAmount.value = balance.toFixed(2); pmAmount.readOnly = true; } else { pmAmount.readOnly = false; pmAmount.value = ''; }
        const form = document.getElementById('paymentForm');
        form.action = `/accounts-receivable/${arId}/payment`;
        pmModal.show();
      } catch (e) {
        Swal && Swal.fire('Error', 'Unable to load AR details', 'error');
      }
    });
  });

  // OR viewer
  const orModalEl = document.getElementById('orModal');
  const orModal = orModalEl ? new bootstrap.Modal(orModalEl) : null;
  document.querySelectorAll('.view-or').forEach(btn => {
    btn.addEventListener('click', () => {
      const url = btn.getAttribute('data-or');
      const body = document.getElementById('orBody');
      if (!body) return;
      body.innerHTML = '';
      if (url.match(/\.(png|jpe?g|gif|webp|bmp|svg)$/i)) {
        body.innerHTML = `<img src="${url}" alt="OR" style="max-width:100%;height:auto;">`;
      } else if (url.match(/\.pdf$/i)) {
        body.innerHTML = `<a href="${url}" class="btn btn-primary" target="_blank">Download OR (PDF)</a>`;
      } else {
        body.textContent = 'No preview available.';
      }
      orModal && orModal.show();
    });
  });

  // Payment form validations: reference + OR required when not cash; amount constraints for type
  const pmForm = document.getElementById('paymentForm');
  const pmMethod = document.getElementById('pmMethod');
  const pmRef = document.getElementById('pmRef');
  const pmType = document.getElementById('pmType');
  const pmAmount = document.getElementById('pmAmount');
  const pmFile = document.getElementById('pmOr');

  function isCash(){ return (pmMethod.value||'').toLowerCase() === 'cash'; }
  function outstanding(){ const max = parseFloat(document.getElementById('pmMax')?.dataset?.value || '0'); return isNaN(max)?0:max; }

  pmMethod?.addEventListener('change', () => {
    pmRef.required = !isCash();
    pmFile.required = !isCash();
  });

  pmType?.addEventListener('change', () => {
    const out = outstanding();
    if ((pmType.value||'').toLowerCase() === 'full') {
      pmAmount.value = out.toFixed(2);
      pmAmount.readOnly = true;
    } else {
      pmAmount.readOnly = false;
    }
  });

  pmForm?.addEventListener('submit', (e) => {
    if (!isCash() && !pmRef.value.trim()) {
      e.preventDefault();
      return Swal.fire('Missing Reference','Reference number is required for non-cash payments.','warning');
    }
    if (!isCash() && !pmFile.files.length) {
      e.preventDefault();
      return Swal.fire('Missing OR','Please upload the Official Receipt image or PDF for non-cash payments.','warning');
    }
    const out = outstanding();
    const amt = parseFloat(pmAmount.value||'0');
    if ((pmType.value||'').toLowerCase() === 'full' && Math.abs(amt - out) > 0.009) {
      e.preventDefault();
      return Swal.fire('Invalid Amount','Full payment must match the outstanding balance.','error');
    }
    if ((pmType.value||'').toLowerCase() === 'partial' && (amt <= 0 || amt >= out)) {
      e.preventDefault();
      return Swal.fire('Invalid Amount','Partial payment must be greater than 0 and less than the outstanding balance.','error');
    }
  });
});
</script>
@if(session('success'))
<script>Swal.fire({ icon:'success', title:'Success', text:'{{ session('success') }}', timer:1600, showConfirmButton:false });</script>
@endif
@if(session('error'))
<script>Swal.fire({ icon:'error', title:'Error', text:'{{ session('error') }}' });</script>
@endif

<!-- Payment Modal (copied and adapted from AR page) -->
<div class="modal" id="paymentModal" tabindex="-1" style="display:none;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Record Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <form id="paymentForm" method="POST" action="#" enctype="multipart/form-data">
      <div class="modal-body">
        @csrf
        <input type="hidden" name="ar_id" id="pmArId" />
        <div class="mb-3">
          <label class="form-label">Payment Date</label>
          <input type="date" name="payment_date" id="pmDate" class="form-control" value="{{ now()->toDateString() }}" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Payment Method</label>
          <select name="payment_method" id="pmMethod" class="form-select" required>
            <option>Cash</option>
            <option>GCash</option>
            <option>Bank Transfer</option>
            <option>Check</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Payment Type</label>
          <select name="payment_type" id="pmType" class="form-select" required>
            <option value="Full">Full</option>
            <option value="Partial">Partial</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Reference #</label>
          <input type="text" name="reference_number" id="pmRef" class="form-control" placeholder="Required if not cash" />
        </div>
        <div class="mb-3">
          <label class="form-label">Official Receipt (Image/PDF)</label>
          <input type="file" name="or_file" id="pmOr" class="form-control" accept="image/*,application/pdf" />
          <small class="text-muted">Required if not cash.</small>
        </div>
        <div class="mb-3">
          <label class="form-label">Amount</label>
          <input type="number" min="0.01" step="0.01" name="amount" id="pmAmount" class="form-control" placeholder="0.00" required />
          <small>Outstanding: <span id="pmMax" data-value="0">—</span></small>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
      </form>
    </div>
  </div>
</div>

<!-- OR Viewer Modal -->
<div class="modal" id="orModal" tabindex="-1" style="display:none;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Official Receipt</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
      <div class="modal-body" id="orBody">—</div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" data-bs-dismiss="modal">Okay</button></div>
    </div>
  </div>
</div>
@if(session('success'))
<script>Swal.fire({ icon:'success', title:'Success', text:'{{ session('success') }}', timer:1400, showConfirmButton:false })</script>
@endif
</body>
</html>
@endsection

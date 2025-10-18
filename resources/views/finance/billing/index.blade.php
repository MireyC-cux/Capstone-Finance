@extends('layouts.finance_app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Billing Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .btn-rounded { border-radius: 1rem; }
        .shadow-soft { box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .table-hover tbody tr:hover { background-color: #f8fafc; }
        .modal-content { border-radius: 1rem; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex align-items-center mb-3">
      <h3 class="mb-0">Billing Dashboard</h3>
      <div class="ms-auto d-flex gap-2">
        <a class="btn btn-outline-secondary btn-rounded" href="{{ route('invoices.index') }}">Invoices</a>
        <a class="btn btn-outline-primary btn-rounded" href="{{ route('finance.billing.approvals.index') }}">Billing Approvals</a>
      </div>
    </div>

    <form method="get" class="card card-body shadow-soft mb-4">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" class="form-control" name="customer" placeholder="Customer" value="{{ $filters['customer'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="sr_number" placeholder="Service Request #" value="{{ $filters['sr_number'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary btn-rounded">Search</button>
            </div>
        </div>
    </form>

    <div class="card shadow-soft">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <div class="fw-semibold">Completed, Unbilled Service Requests</div>
                <div class="ms-auto d-flex gap-2">
                    <button id="btnBulkBill" class="btn btn-success btn-rounded">Generate Bills (Selected)</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="chkAll"></th>
                        <th>Service Request #</th>
                        <th>Customer</th>
                        <th>Service Date</th>
                        <th>Total (Est.)</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($groups as $service_request_id => $items)
                        @php
                            $sr = $items->first()->serviceRequest;
                            $subtotal = 0; $discount = 0; $tax = 0;
                            foreach ($items as $it) {
                                $line = ($it->quantity ?? 1) * (float)$it->unit_price;
                                $lineDiscount = (float)($it->discount ?? 0);
                                $lineTax = (float)($it->tax ?? 0);
                                $extras = \App\Models\ServiceRequestItemExtra::where('item_id', $it->item_id)->get();
                                $extraSum = $extras->sum(fn($e)=> (float)$e->qty * (float)$e->price);
                                $subtotal += $line + $extraSum; $discount += $lineDiscount; $tax += $lineTax;
                            }
                            $total = round($subtotal - $discount + $tax, 2);
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="chkRow" value="{{ $service_request_id }}"></td>
                            <td>{{ $sr->service_request_number ?? $service_request_id }}</td>
                            <td>{{ $sr->customer->full_name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($sr->end_date ?? $sr->service_date)->format('Y-m-d') }}</td>
                            <td>₱ {{ number_format($total, 2) }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-rounded" data-view-items data-srid="{{ $service_request_id }}">View Details</button>
                                <button class="btn btn-sm btn-primary btn-rounded" data-generate-bill data-srid="{{ $service_request_id }}">Generate Bill</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No completed unbilled items found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Generate Bill Modal -->
<div class="modal fade" id="modalGenerateBill" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-soft">
      <div class="modal-header">
        <h5 class="modal-title">Generate Bill</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="billItems"></div>
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="form-label">Billing Date</label>
                <input type="date" class="form-control" id="billing_date" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" value="{{ now()->addDays(7)->format('Y-m-d') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="generate_invoice" checked>
                    <label class="form-check-label" for="generate_invoice">Generate Invoice</label>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-rounded" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-rounded" id="btnConfirmGenerate">Submit for Approval</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const modal = new bootstrap.Modal(document.getElementById('modalGenerateBill'));
  let currentSrId = null;

  function toastSuccess(msg){ Swal.fire({ icon:'success', title:'Success', text: msg, timer: 1400, showConfirmButton:false}); }
  function toastError(msg){ Swal.fire({ icon:'error', title:'Error', text: msg }); }

  // Select all checkbox
  const chkAll = document.getElementById('chkAll');
  if (chkAll) chkAll.addEventListener('change', e => {
      document.querySelectorAll('.chkRow').forEach(c => c.checked = e.target.checked);
  });

  // View details
  addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-view-items]');
    if (!btn) return;
    const id = btn.getAttribute('data-srid');
    const res = await fetch(`{{ route('finance.billing.view-items', 0) }}`.replace('/0', `/${id}`));
    if (!res.ok) return toastError('Failed to load items');
    const sr = await res.json();
    const rows = [];
    (sr.items || []).forEach(it => {
      const extras = (it.extras || []).map(x => `${x.name} (x${x.qty}) - ₱${Number(x.price).toFixed(2)}`).join('<br>');
      rows.push(`<tr>
          <td>${it.service?.service_name ?? it.service_type ?? ''}</td>
          <td class="text-end">${Number(it.quantity || 1)}</td>
          <td class="text-end">₱ ${Number(it.unit_price || 0).toFixed(2)}</td>
          <td class="text-end">₱ ${Number(it.discount || 0).toFixed(2)}</td>
          <td class="text-end">₱ ${Number(it.tax || 0).toFixed(2)}</td>
          <td>${extras}</td>
        </tr>`);
    });
    document.getElementById('billItems').innerHTML = `<div class="table-responsive"><table class="table table-sm">
      <thead><tr><th>Service</th><th class=\"text-end\">Qty</th><th class=\"text-end\">Unit</th><th class=\"text-end\">Discount</th><th class=\"text-end\">Tax</th><th>Extras</th></tr></thead>
      <tbody>${rows.join('')}</tbody></table></div>`;
    currentSrId = id;
    modal.show();
  });

  // Generate bill single
  addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-generate-bill]');
    if (!btn) return;
    const id = btn.getAttribute('data-srid');
    currentSrId = id;
    // pre-load items
    document.querySelector('[data-view-items][data-srid="'+id+'"]')?.click();
  });

  // Confirm generate single
  const btnConfirm = document.getElementById('btnConfirmGenerate');
  btnConfirm?.addEventListener('click', async () => {
    if (!currentSrId) return;
    const payload = {
      service_request_id: Number(currentSrId),
      billing_date: document.getElementById('billing_date').value,
      due_date: document.getElementById('due_date').value,
      generate_invoice: document.getElementById('generate_invoice').checked
    };
    const res = await fetch(`{{ route('finance.billing.store') }}`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify(payload)
    });
    let data = null, text = '';
    try { data = await res.clone().json(); } catch(e) { try { text = await res.text(); } catch(_) {} }
    if (res.ok) {
      toastSuccess((data && data.message) || 'Submitted for approval');
      setTimeout(()=> location.reload(), 900);
    }
    else {
      const err = (data && (data.message || (data.errors && Object.values(data.errors).flat().join('\n')))) || text || `HTTP ${res.status}`;
      toastError(err);
    }
  });

  // Bulk generate
  const btnBulk = document.getElementById('btnBulkBill');
  btnBulk?.addEventListener('click', async () => {
    const ids = Array.from(document.querySelectorAll('.chkRow:checked')).map(x => Number(x.value));
    if (!ids.length) return toastError('Select at least one service request');
    const billing_date = new Date().toISOString().slice(0,10);
    const due_date = new Date(Date.now()+7*86400000).toISOString().slice(0,10);
    const res = await fetch(`{{ route('finance.billing.bulk-store') }}`, {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify({ service_request_ids: ids, billing_date, due_date, generate_invoice: true })
    });
    if (res.ok) { toastSuccess('Submitted for approval'); setTimeout(()=> location.reload(), 900); }
    else { toastError('Bulk billing failed'); }
  });
});
</script>
</body>
</html>
@endsection

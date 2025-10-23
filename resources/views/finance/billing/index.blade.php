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
            <div class="col-md-2 d-grid ms-auto">
                <button class="btn btn-primary btn-rounded">Search</button>
            </div>
        </div>
    </form>

    <div class="card shadow-soft">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <div class="fw-semibold">Completed, Unbilled Service Requests (Quotation: Approved)</div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                    <tr>
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
                            <td>{{ $sr->service_request_number ?? $service_request_id }}</td>
                            <td>{{ $sr->customer->full_name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($sr->end_date ?? $sr->service_date)->format('Y-m-d') }}</td>
                            <td>₱ {{ number_format($total, 2) }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-rounded" data-view-items data-srid="{{ $service_request_id }}">View Details</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No completed unbilled items found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="modalGenerateBill" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-soft">
      <div class="modal-header">
        <h5 class="modal-title">Service Request Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="billItems"></div>
        <div class="mt-3">
          <div class="d-flex justify-content-end">
            <div class="text-end">
              <div><span class="text-muted">Subtotal:</span> <strong id="total_subtotal">₱ 0.00</strong></div>
              <div><span class="text-muted">Discount:</span> <strong id="total_discount">₱ 0.00</strong></div>
              <div><span class="text-muted">Tax:</span> <strong id="total_tax">₱ 0.00</strong></div>
              <div class="fs-5"><span class="text-muted">Total:</span> <strong id="total_grand">₱ 0.00</strong></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-rounded" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-rounded" id="btnGenerateFromDetails">Generate Bill</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const modalEl = document.getElementById('modalGenerateBill');
  if (modalEl && modalEl.parentElement !== document.body) {
    document.body.appendChild(modalEl);
  }
  const modal = new bootstrap.Modal(modalEl);
  let currentSrId = null;
  const mainContent = document.querySelector('.main-content');
  if (modalEl && mainContent) {
    modalEl.addEventListener('show.bs.modal', () => {
      mainContent.setAttribute('inert', '');
    });
    modalEl.addEventListener('hidden.bs.modal', () => {
      mainContent.removeAttribute('inert');
    });
  }

  function toastSuccess(msg){ Swal.fire({ icon:'success', title:'Success', text: msg, timer: 1400, showConfirmButton:false}); }
  function toastError(msg){ Swal.fire({ icon:'error', title:'Error', text: msg }); }

  function getMetaCsrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }
  function getCookie(name){
    const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : '';
  }

  // View details
  addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-view-items]');
    if (!btn) return;
    const id = btn.getAttribute('data-srid');
    const res = await fetch(`{{ route('finance.billing.view-items', 0) }}`.replace('/0', `/${id}`));
    if (!res.ok) return toastError('Failed to load items');
    const sr = await res.json();
    const rows = [];
    let subtotal = 0, discount = 0, tax = 0;
    (sr.items || []).forEach(it => {
      const extrasArr = (it.extras || []);
      const extras = extrasArr.map(x => `${x.name} (x${x.qty}) - ₱${Number(x.price).toFixed(2)}`).join('<br>');
      const qty = Number(it.quantity || 1);
      const unit = Number(it.unit_price || 0);
      const line = qty * unit;
      const extraSum = extrasArr.reduce((s, x) => s + Number(x.qty || 0) * Number(x.price || 0), 0);
      subtotal += line + extraSum;
      discount += Number(it.discount || 0);
      tax += Number(it.tax || 0);
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
    const grand = Math.round((subtotal - discount + tax) * 100) / 100;
    const fmt = (n) => `₱ ${n.toFixed(2)}`;
    document.getElementById('total_subtotal').textContent = fmt(subtotal);
    document.getElementById('total_discount').textContent = fmt(discount);
    document.getElementById('total_tax').textContent = fmt(tax);
    document.getElementById('total_grand').textContent = fmt(grand);
    currentSrId = id;
    modal.show();
  });

  // Generate bill from details
  document.getElementById('btnGenerateFromDetails')?.addEventListener('click', async () => {
    if (!currentSrId) return;
    const today = new Date();
    const billing_date = today.toISOString().slice(0,10);
    const due = new Date(today.getTime() + 7*86400000);
    const due_date = due.toISOString().slice(0,10);
    const form = new FormData();
    form.append('service_request_id', String(Number(currentSrId)));
    form.append('billing_date', billing_date);
    form.append('due_date', due_date);
    form.append('generate_invoice', '1');
    form.append('_token', getMetaCsrf() || getCookie('XSRF-TOKEN'));
    let res = await fetch(`{{ route('finance.billing.store') }}`, {
      method: 'POST',
      headers: {
        'Accept':'application/json',
        'X-Requested-With':'XMLHttpRequest',
        'X-CSRF-TOKEN': getMetaCsrf() || getCookie('XSRF-TOKEN'),
        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')
      },
      credentials: 'same-origin',
      body: form
    });
    // One-time retry on 419 in case of token rotation
    if (res.status === 419) {
      form.set('_token', getMetaCsrf() || getCookie('XSRF-TOKEN'));
      res = await fetch(`{{ route('finance.billing.store') }}`, {
        method: 'POST',
        headers: {
          'Accept':'application/json',
          'X-Requested-With':'XMLHttpRequest',
          'X-CSRF-TOKEN': getMetaCsrf() || getCookie('XSRF-TOKEN'),
          'X-XSRF-TOKEN': getCookie('XSRF-TOKEN')
        },
        credentials: 'same-origin',
        body: form
      });
    }
    let data = null, text = '';
    try { data = await res.clone().json(); } catch(e) { try { text = await res.text(); } catch(_) {} }
    if (res.ok) {
      const billingId = data && data.billing_id;
      toastSuccess((data && data.message) || 'Billing created successfully.');
      if (billingId) {
        const url = `{{ route('finance.billing.slip.pdf', 0) }}`.replace('/0', `/${billingId}`);
        window.open(url, '_blank');
      }
      setTimeout(()=> {
        try { if (document.activeElement) document.activeElement.blur(); } catch(_) {}
        modal.hide();
      }, 600);
    }
    else {
      const err = (data && (data.message || (data.errors && Object.values(data.errors).flat().join('\n')))) || text || `HTTP ${res.status}`;
      toastError(err);
    }
  });
});
</script>
</body>
</html>
@endsection

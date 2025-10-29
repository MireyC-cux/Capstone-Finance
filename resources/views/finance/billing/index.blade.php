@extends('layouts.finance_app')

@section('content')
<div style="padding: 20px; font-family: Inter, Poppins, 'Segoe UI', system-ui, -apple-system, Arial, sans-serif;">
    <div class="d-flex justify-content-end align-items-center mb-3">
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2">
            <i class="fas fa-file-invoice"></i>
            <span>Invoice History</span>
        </a>
    </div>

    <form method="get" class="mb-4">
        <div class="card border-0 shadow-sm" style="padding: 1rem;">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold mb-1">Customer</label>
                    <input type="text" class="form-control form-control-sm" name="customer" placeholder="Search by customer name" value="{{ $filters['customer'] ?? '' }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold mb-1">Service Request #</label>
                    <input type="text" class="form-control form-control-sm" name="sr_number" placeholder="Search by SR number" value="{{ $filters['sr_number'] ?? '' }}">
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-search"></i>
                        <span>Search</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm" style="padding: 0; overflow: hidden;">
        <div class="px-3 py-3 border-bottom">
            <h5 class="mb-0 fw-semibold">Completed, Unbilled Service Requests</h5>
            <div class="text-muted small mt-1">Quotation: Approved</div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-nowrap">
                        <th class="py-2">Service Request #</th>
                        <th class="py-2">Customer</th>
                        <th class="py-2">Service Date</th>
                        <th class="py-2 text-end">Subtotal</th>
                        <th class="py-2 text-end">Line Discount</th>
                        <th class="py-2 text-end">Grand Discount</th>
                        <th class="py-2 text-end">Tax</th>
                        <th class="py-2 text-end">Total</th>
                        <th class="py-2">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($groups as $service_request_id => $items)
                        @php
                            $sr = $items->first()->serviceRequest;
                            $subtotal = 0; $lineDiscount = 0; $lineTax = 0;
                            foreach ($items as $it) {
                                $line = ($it->quantity ?? 1) * (float)$it->unit_price;
                                $lineDiscount += (float)($it->discount ?? 0);
                                $lineTax += (float)($it->tax ?? 0);
                                $extras = \App\Models\ServiceRequestItemExtra::where('item_id', $it->item_id)->get();
                                $extraSum = $extras->sum(fn($e)=> (float)$e->qty * (float)$e->price);
                                $subtotal += $line + $extraSum;
                            }
                            $grandDiscount = (float)($sr->overall_discount ?? 0);
                            $grandTax = (float)($sr->overall_tax_amount ?? 0);
                            $taxTotal = $lineTax + $grandTax;
                            $total = round($subtotal - ($lineDiscount + $grandDiscount) + $taxTotal, 2);
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $sr->service_request_number ?? $service_request_id }}</td>
                            <td>{{ $sr->customer->full_name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($sr->end_date ?? $sr->service_date)->format('Y-m-d') }}</td>
                            <td class="text-end">₱ {{ number_format($subtotal, 2) }}</td>
                            <td class="text-end">₱ {{ number_format($lineDiscount, 2) }}</td>
                            <td class="text-end">₱ {{ number_format($grandDiscount, 2) }}</td>
                            <td class="text-end">₱ {{ number_format($taxTotal, 2) }}</td>
                            <td class="fw-semibold text-end">₱ {{ number_format($total, 2) }}</td>
                            <td>
                                <button class="btn btn-info btn-sm d-inline-flex align-items-center gap-2" data-view-items data-srid="{{ $service_request_id }}">
                                    <i class="fas fa-eye"></i>
                                    <span>View</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-muted">No completed unbilled items found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="modalGenerateBill" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 10px;">
      <div class="modal-header py-3 px-4">
        <h5 class="modal-title fw-semibold">Service Request Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div id="billItems"></div>
        <div class="mt-3">
          <div class="d-flex justify-content-end">
            <div class="text-end">
              <div class="small"><span class="text-muted">Subtotal:</span> <strong id="total_subtotal">₱ 0.00</strong></div>
              <div class="small"><span class="text-muted">Discount:</span> <strong id="total_discount">₱ 0.00</strong></div>
              <div class="small"><span class="text-muted">Tax:</span> <strong id="total_tax">₱ 0.00</strong></div>
              <div class="fs-6 fw-semibold mt-1"><span class="text-muted">Total:</span> <strong id="total_grand">₱ 0.00</strong></div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer py-3 px-4">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2" id="btnGenerateFromDetails">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Generate Bill</span>
        </button>
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
        setTimeout(()=> { window.location.reload(); }, 500);
      }, 600);
    }
    else {
      const err = (data && (data.message || (data.errors && Object.values(data.errors).flat().join('\n')))) || text || `HTTP ${res.status}`;
      toastError(err);
    }
  });
});
</script>
@endsection

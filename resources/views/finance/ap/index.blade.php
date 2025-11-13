@extends('layouts.finance_app')

@section('content')
<div style="padding: 20px;">

  <!-- Page Header -->
  <div style="margin-bottom: 2rem;">
    <div class="d-flex justify-content-between align-items-center">
      <h2 class="fw-bold text-warning m-0">
        <i class="fa fa-clock me-2"></i> Partially Paid Accounts Payable
      </h2>
      <a href="{{ route('purchase-orders.index') }}" class="btn btn-primary d-flex align-items-center" style="gap: 0.5rem;">
        <i class="fa fa-list"></i> View POs
      </a>
    </div>
    <p class="text-muted mt-1 mb-0">Showing only records where payment is partially settled.</p>
  </div>

  <!-- Flash Alerts -->
  @if(session('success'))
    <script>
      window.addEventListener('DOMContentLoaded', () => Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '{{ session('success') }}',
        confirmButtonColor: '#06b6d4'
      }));
    </script>
  @endif
  @if(session('error'))
    <script>
      window.addEventListener('DOMContentLoaded', () => Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '{{ session('error') }}'
      }));
    </script>
  @endif

  <!-- Filters -->
  <form method="GET" style="margin-bottom: 2rem;">
    <div class="card p-4">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Supplier</label>
          <select name="supplier_id" class="form-select">
            <option value="">All</option>
            @foreach($suppliers as $s)
              <option value="{{ $s->supplier_id }}" @selected(request('supplier_id') == $s->supplier_id)>
                {{ $s->supplier_name }}
              </option>
            @endforeach
          </select>
        </div>
        {{-- <div class="col-md-3">
          <label class="form-label">PO#</label>
          <input type="text" name="po_number" value="{{ request('po_number') }}" class="form-control" placeholder="PO Number">
        </div> --}}
        <div class="col-md-3">
          <label class="form-label">From</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">To</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-control">
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2 mt-3">
        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Apply Filters</button>
        <a href="{{ route('accounts-payable.index') }}" class="btn btn-outline-secondary">
          <i class="fa fa-rotate"></i> Reset
        </a>
      </div>
    </div>
  </form>

  <!-- Summary Cards -->
  <div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small fw-semibold text-uppercase">Total Payables</div>
            <div class="fw-bold fs-4 text-primary">₱{{ number_format($stats['total'] ?? 0,2) }}</div>
          </div>
          <div class="rounded bg-primary text-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            <i class="fa fa-file-invoice"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small fw-semibold text-uppercase">Paid</div>
            <div class="fw-bold fs-4 text-success">₱{{ number_format($stats['paid'] ?? 0,2) }}</div>
          </div>
          <div class="rounded bg-success text-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            <i class="fa fa-check-circle"></i>
          </div>
        </div>
      </div>
    </div>
    {{-- <div class="col-12 col-md-6 col-lg-3">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small fw-semibold text-uppercase">Overdue</div>
            <div class="fw-bold fs-4 text-danger">{{ $stats['overdue'] ?? 0 }}</div>
          </div>
          <div class="rounded bg-danger text-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            <i class="fa fa-exclamation-triangle"></i>
          </div>
        </div>
      </div>
    </div> --}}
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small fw-semibold text-uppercase">Partially Paid</div>
            <div class="fw-bold fs-4 text-warning">{{ $stats['partial'] ?? 0 }}</div>
          </div>
          <div class="rounded bg-warning text-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
            <i class="fa fa-clock"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Invoice #</th>
            <th>Supplier</th>
            <th>Invoice Date</th>
            <th>Due Date</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Balance</th>
            <th>Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($payables as $ap)
            <tr>
              <td>{{ $ap->invoice_number }}</td>
              <td>{{ $ap->supplier->supplier_name ?? '—' }}</td>
              <td>{{ $ap->invoice_date }}</td>
              <td>{{ $ap->due_date }}</td>
              <td>₱{{ number_format($ap->total_amount,2) }}</td>
              <td>₱{{ number_format($ap->amount_paid,2) }}</td>
              <td><strong>₱{{ number_format($ap->balance,2) }}</strong></td>
              <td><span class="badge bg-warning">{{ ucfirst($ap->status) }}</span></td>
              <td class="text-center">
                <button class="btn btn-sm btn-success ap-pay d-flex align-items-center" data-ap='@json($ap)' style="gap:.5rem;">
                  <i class="fa fa-credit-card"></i> Record Full Payment
                </button>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="text-center text-muted py-3">No partially paid records found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $payables->links() }}</div>

  <!-- Record Payment Modal -->
  <div id="recordPaymentModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="recordPaymentForm" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title">Record Full Payment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="payment_type" value="Full">
            <div class="mb-3">
              <label class="form-label">Payment Date</label>
              <input type="date" class="form-control" name="payment_date" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Payment Method</label>
              <select class="form-select" name="payment_method" id="rpMethod" required>
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Reference #</label>
              <input type="text" class="form-control" name="reference_number" id="rpRef" placeholder="Required if not cash">
            </div>
            <div class="mb-3">
              <label class="form-label">Official Receipt (Image/PDF)</label>
              <input type="file" class="form-control" name="or_file" id="rpOr" accept="image/*,application/pdf">
            </div>
            <div class="mb-3">
              <label class="form-label">Amount (Must Match Balance)</label>
              <input type="number" class="form-control" step="0.01" name="amount" id="rpAmount" readonly required>
              <small>Outstanding Balance: <span id="rpBalance" class="fw-bold text-primary">₱0.00</span></small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Confirm Full Payment</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const modal = new bootstrap.Modal(document.getElementById('recordPaymentModal'));
  const form = document.getElementById('recordPaymentForm');

  document.querySelectorAll('.ap-pay').forEach(btn => {
    btn.addEventListener('click', function() {
      const ap = JSON.parse(this.dataset.ap);
      const actionUrl = "{{ route('accounts-payable.record-payment', ':id') }}".replace(':id', ap.ap_id);

      // Fill modal fields
      form.action = actionUrl;
      const balance = (ap.total_amount - ap.amount_paid).toFixed(2);
      form.querySelector('[name=amount]').value = balance;
      document.getElementById('rpBalance').innerText = '₱' + parseFloat(balance).toLocaleString(undefined, { minimumFractionDigits: 2 });

      modal.show();
    });
  });

  // Toggle ref/receipt based on method
  document.getElementById('rpMethod').addEventListener('change', function() {
    const refInput = document.getElementById('rpRef');
    const orInput = document.getElementById('rpOr');
    if (this.value.toLowerCase() === 'cash') {
      refInput.required = false;
      orInput.required = false;
    } else {
      refInput.required = true;
      orInput.required = true;
    }
  });
});
</script>
@endpush

@endsection

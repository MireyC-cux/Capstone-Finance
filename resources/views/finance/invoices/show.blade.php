@extends('layouts.finance_app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Invoice Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>.btn-rounded{border-radius:1rem}.shadow-soft{box-shadow:0 10px 25px rgba(0,0,0,.08)}</style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Invoice #{{ $invoice->invoice_number }}</h3>
    <div class="ms-auto">
      <a class="btn btn-outline-secondary btn-rounded" href="{{ route('invoices.index') }}">Back</a>
      @if($invoice->billing)
      <a class="btn btn-outline-primary btn-rounded" target="_blank" href="{{ route('finance.billing.slip', $invoice->billing->billing_id) }}">Print Billing Slip</a>
      <a class="btn btn-primary btn-rounded" target="_blank" href="{{ route('finance.billing.slip.pdf', $invoice->billing->billing_id) }}">Download PDF</a>
      @endif
    </div>
  </div>

  <div class="card shadow-soft mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <div class="text-muted">Customer</div>
          <div class="fw-semibold">{{ $invoice->billing->serviceRequest->customer->full_name ?? 'N/A' }}</div>
        </div>
        <div class="col-md-4">
          <div class="text-muted">Amount</div>
          <div class="fw-semibold">₱ {{ number_format($invoice->amount, 2) }}</div>
        </div>
        <div class="col-md-4">
          <div class="text-muted">Status</div>
          <span class="badge text-bg-secondary">{{ $invoice->status }}</span>
        </div>
        <div class="col-md-4">
          <div class="text-muted">Invoice Date</div>
          <div>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}</div>
        </div>
        <div class="col-md-4">
          <div class="text-muted">Due Date</div>
          <div>{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') }}</div>
        </div>
      </div>
      <div class="mt-3">
        <button class="btn btn-primary btn-rounded" data-bs-toggle="modal" data-bs-target="#modalRecordPayment">Record Payment</button>
      </div>
    </div>
  </div>

  <div class="card shadow-soft">
    <div class="card-header bg-white">Accounts Receivable</div>
    <div class="card-body">
      @php($ar = $invoice->accountsReceivable)
      @if($ar)
        <div class="row g-3">
          <div class="col-md-3"><div class="text-muted">Total</div><div class="fw-semibold">₱ {{ number_format($ar->total_amount, 2) }}</div></div>
          <div class="col-md-3"><div class="text-muted">Paid</div><div class="fw-semibold">₱ {{ number_format($ar->amount_paid, 2) }}</div></div>
          <div class="col-md-3"><div class="text-muted">Balance</div><div class="fw-semibold">₱ {{ number_format($ar->balance, 2) }}</div></div>
          <div class="col-md-3"><div class="text-muted">Status</div><span class="badge text-bg-secondary">{{ $ar->status }}</span></div>
        </div>
      @else
        <div class="text-muted">No AR linked.</div>
      @endif
    </div>
  </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="modalRecordPayment" tabindex="-1">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content shadow-soft">
      <form action="{{ route('payments.store') }}" method="post">
        @csrf
        <input type="hidden" name="ar_id" value="{{ $invoice->accountsReceivable->ar_id ?? '' }}">
        <div class="modal-header">
          <h5 class="modal-title">Record Payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Payment Date</label>
              <input type="date" name="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-12">
              <label class="form-label">Method</label>
              <select name="payment_method" class="form-select" required>
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Cheque">Cheque</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Amount</label>
              <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Reference # (optional)</label>
              <input type="text" name="reference_number" class="form-control" placeholder="Receipt / Txn Ref">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-rounded" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-rounded">Save Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@if(session('success'))
<script>Swal.fire({ icon:'success', title:'Success', text:'{{ session('success') }}', timer:1400, showConfirmButton:false })</script>
@endif
@if($errors->any())
<script>Swal.fire({ icon:'error', title:'Validation Error', html:`{!! implode('<br>', $errors->all()) !!}` })</script>
@endif
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@endsection

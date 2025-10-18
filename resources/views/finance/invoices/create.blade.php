@extends('layouts.finance_app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Create Invoice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>.btn-rounded{border-radius:1rem}.shadow-soft{box-shadow:0 10px 25px rgba(0,0,0,.08)}</style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Generate Invoice</h3>
    <div class="ms-auto">
      <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-rounded">Back</a>
    </div>
  </div>

  <div class="card shadow-soft">
    <div class="card-body">
      <div class="mb-3">
        <div class="fw-semibold">Billing Summary</div>
        <div>Customer: <strong>{{ $billing->serviceRequest->customer->name ?? 'N/A' }}</strong></div>
        <div>SR #: <strong>{{ $billing->serviceRequest->service_request_number ?? $billing->service_request_id }}</strong></div>
        <div>Amount: <strong>₱ {{ number_format($billing->total_amount, 2) }}</strong></div>
      </div>

      <form method="post" action="{{ route('invoices.store') }}" class="row g-3">
        @csrf
        <input type="hidden" name="billing_id" value="{{ $billing->billing_id }}">
        <div class="col-md-4">
          <label class="form-label">Invoice Date</label>
          <input type="date" name="invoice_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Due Date</label>
          <input type="date" name="due_date" class="form-control" value="{{ now()->addDays(7)->format('Y-m-d') }}">
        </div>
        <div class="col-12">
          <button class="btn btn-primary btn-rounded">Generate Invoice & Send to AR</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@endsection

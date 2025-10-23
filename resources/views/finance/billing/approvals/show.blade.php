@extends('layouts.finance_app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Review Billing #{{ $billing->billing_id }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .btn-rounded{border-radius:1rem}
    .shadow-soft{box-shadow:0 10px 25px rgba(0,0,0,.08)}
    .table-slim td,.table-slim th{padding:.45rem .6rem}
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Review Billing #{{ $billing->billing_id }}</h3>
    <div class="ms-auto">
      <a class="btn btn-outline-secondary btn-rounded" href="{{ route('finance.billing.approvals.index') }}">Back to Approvals</a>
      <a class="btn btn-outline-primary btn-rounded" target="_blank" href="{{ route('finance.billing.slip', $billing->billing_id) }}">View Slip</a>
    </div>
  </div>

  <div class="card shadow-soft mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <div class="text-muted">Customer</div>
          <div class="fw-semibold">{{ $billing->serviceRequest->customer->full_name ?? 'N/A' }}</div>
          <div class="small text-muted">{{ $billing->serviceRequest->customer->business_name }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted">Billing Date</div>
          <div>{{ \Carbon\Carbon::parse($billing->billing_date)->format('Y-m-d') }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted">Due Date</div>
          <div>{{ \Carbon\Carbon::parse($billing->due_date)->format('Y-m-d') }}</div>
        </div>
        <div class="col-md-2 text-end">
          <div class="text-muted">Total</div>
          <div class="fw-bold">₱ {{ number_format($billing->total_amount,2) }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-soft mb-3">
    <div class="card-header bg-white">Items</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-slim align-middle">
          <thead>
            <tr>
              <th>Service</th>
              <th class="text-end">Qty</th>
              <th class="text-end">Unit Price</th>
              <th class="text-end">Discount</th>
              <th class="text-end">Tax</th>
            </tr>
          </thead>
          <tbody>
          @foreach($items as $it)
            <tr>
              <td>{{ $it->service->service_name ?? $it->service_type }}</td>
              <td class="text-end">{{ (int)($it->quantity ?? 1) }}</td>
              <td class="text-end">{{ number_format((float)$it->unit_price,2) }}</td>
              <td class="text-end">{{ number_format((float)($it->discount ?? 0),2) }}</td>
              <td class="text-end">{{ number_format((float)($it->tax ?? 0),2) }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card shadow-soft">
    <div class="card-body">
      <form class="row g-3" method="GET" action="{{ route('finance.billing.approve', $billing->billing_id) }}">
        <div class="col-12">
          <label class="form-label">Approval Note (optional)</label>
          <textarea name="note" class="form-control" rows="2" placeholder="Add a note for this approval..."></textarea>
        </div>
        <div class="col-12 d-flex gap-2">
          <button class="btn btn-primary btn-rounded">Approve</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
@if(session('success'))
<script>
  Swal.fire({ icon:'success', title:'Success', text: @json(session('success')), timer: 1400, showConfirmButton:false })
    .then(()=>{ window.location.href = @json(route('finance.billing.approvals.index')); });
</script>
@endif
@if($errors->any())
<script>
  Swal.fire({ icon:'error', title:'Error', html: @json(implode('<br>', $errors->all())) });
</script>
@endif
</html>
@endsection

@extends('layouts.finance_app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Invoices</title>
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
    <h3 class="mb-0">Invoices</h3>
    <div class="ms-auto">
      <a class="btn btn-outline-secondary btn-rounded" href="{{ route('finance.billing.index') }}">Back to Billing</a>
    </div>
  </div>

  <div class="card shadow-soft">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Invoice No.</th>
              <th>Customer</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($invoices as $inv)
              <tr>
                <td>{{ $inv->invoice_id }}</td>
                <td>{{ $inv->invoice_number }}</td>
                <td>{{ $inv->billing->serviceRequest->customer->full_name ?? 'N/A' }}</td>
                <td>₱ {{ number_format($inv->amount, 2) }}</td>
                <td><span class="badge text-bg-secondary">{{ $inv->status }}</span></td>
                <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('Y-m-d') }}</td>
                <td>
                  <a href="{{ route('invoices.show', $inv->invoice_id) }}" class="btn btn-sm btn-primary btn-rounded">View</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{ $invoices->links() }}
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@if(session('success'))
<script>Swal.fire({ icon:'success', title:'Success', text:'{{ session('success') }}', timer:1400, showConfirmButton:false })</script>
@endif
</body>
</html>
@endsection

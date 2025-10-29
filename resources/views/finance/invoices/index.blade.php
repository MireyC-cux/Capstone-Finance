@extends('layouts.finance_app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Billing History</title>
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
    <h3 class="mb-0">Billing History</h3>
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
              <th class="py-2">Business Name</th>
              <th class="py-2">Billing Date</th>
              <th class="py-2">Status</th>
              <th class="py-2 text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($billings as $b)
              <tr>
                <td>{{ $b->billing_id }}</td>
                <td>{{ $b->serviceRequest->service_request_number ?? $b->service_request_id }}</td>
                <td>{{ $b->serviceRequest->customer->full_name ?? 'N/A' }}</td>
                <td>{{ $b->serviceRequest->customer->business_name ?? '—' }}</td>
                <td>{{ \Carbon\Carbon::parse($b->billing_date)->format('Y-m-d') }}</td>
                <td><span class="badge text-bg-secondary">{{ $b->status }}</span></td>
                <td class="text-end">
                  <a href="{{ route('finance.billing.slip.pdf', $b->billing_id) }}" target="_blank" class="btn btn-sm btn-primary">PDF</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{ $billings->links() }}
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
 
@if(session('success'))
<script>Swal.fire({ icon:'success', title:'Success', text:'{{ session('success') }}', timer:1600, showConfirmButton:false });</script>
@endif
@if(session('error'))
<script>Swal.fire({ icon:'error', title:'Error', text:'{{ session('error') }}' });</script>
@endif
</body>
</html>
@endsection

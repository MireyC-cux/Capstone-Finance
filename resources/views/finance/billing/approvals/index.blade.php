@extends('layouts.finance_app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Billing Approvals</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .btn-rounded{border-radius:1rem}
    .shadow-soft{box-shadow:0 10px 25px rgba(0,0,0,.08)}
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Billing Approvals</h3>
    <div class="ms-auto">
      <a class="btn btn-outline-secondary btn-rounded" href="{{ route('finance.billing.index') }}">Back to Billing</a>
    </div>
  </div>

  <div class="card shadow-soft">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Billing #</th>
              <th>Customer</th>
              <th>SR #</th>
              <th>Billing Date</th>
              <th>Due Date</th>
              <th class="text-end">Total</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          @forelse($billings as $b)
            <tr>
              <td>{{ $b->billing_id }}</td>
              <td>{{ $b->serviceRequest->customer->full_name ?? 'N/A' }}</td>
              <td>{{ $b->serviceRequest->service_request_number ?? $b->service_request_id }}</td>
              <td>{{ \Carbon\Carbon::parse($b->billing_date)->format('Y-m-d') }}</td>
              <td>{{ \Carbon\Carbon::parse($b->due_date)->format('Y-m-d') }}</td>
              <td class="text-end">₱ {{ number_format($b->total_amount, 2) }}</td>
              <td><span class="badge bg-warning text-dark">{{ $b->approval_status }}</span></td>
              <td>
                <a class="btn btn-sm btn-outline-secondary btn-rounded" href="{{ route('finance.billing.approvals.show', $b->billing_id) }}">Review</a>
                <form class="d-inline" method="POST" action="{{ route('finance.billing.approve', $b->billing_id) }}">
                  @csrf
                  <input type="hidden" name="note" value="" />
                  <button class="btn btn-sm btn-primary btn-rounded">Approve</button>
                </form>
                <form class="d-inline" method="POST" action="{{ route('finance.billing.reject', $b->billing_id) }}">
                  @csrf
                  <input type="hidden" name="note" value="" />
                  <button class="btn btn-sm btn-outline-danger btn-rounded">Reject</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted">No pending billings.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $billings->links() }}
    </div>
  </div>
</div>
</body>
@if(session('success'))
<script>
  Swal.fire({ icon:'success', title:'Success', text: @json(session('success')), timer: 1400, showConfirmButton:false })
  .then(()=>{ try{ window.location.reload(); }catch(_){} });
  </script>
@endif
@if($errors->any())
<script>
  Swal.fire({ icon:'error', title:'Error', html: @json(implode('<br>', $errors->all())) });
</script>
@endif
</html>
@endsection

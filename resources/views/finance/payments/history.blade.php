<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Payments History</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>.btn-rounded{border-radius:1rem}.shadow-soft{box-shadow:0 10px 25px rgba(0,0,0,.08)}</style>
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Payments History</h3>
    <div class="ms-auto">
      <a class="btn btn-outline-secondary btn-rounded" href="{{ route('finance.accounts-receivable') }}">Back to Accounts Receivable</a>
    </div>
  </div>

  <div class="card shadow-soft">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>AR #</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Amount</th>
              <th>Method</th>
              <th>Reference</th>
            </tr>
          </thead>
          <tbody>
            @foreach($payments as $p)
              <tr>
                <td>{{ $p->payment_id }}</td>
                <td>{{ $p->ar_id }}</td>
                <td>{{ $p->accountsReceivable->customer->full_name ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($p->payment_date)->format('Y-m-d') }}</td>
                <td>₱ {{ number_format($p->amount, 2) }}</td>
                <td>{{ $p->payment_method }}</td>
                <td>{{ $p->reference_number }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      {{ $payments->links() }}
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

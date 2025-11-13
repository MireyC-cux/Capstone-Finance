@extends('layouts.finance_app')

@section('content')
<div class="container-xxl py-3">
  <div class="inv-hero p-4 p-md-5 mb-4 position-relative overflow-hidden">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h1 class="mb-1">Stock-In</h1>
        <p class="text-muted mb-0">Auto-recorded from delivered purchase orders.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('finance.inventory.dashboard') }}" class="btn btn-light border btn-ghost">
          <i class="fa fa-arrow-left me-2"></i><span>Back to Dashboard</span>
        </a>
      </div>
    </div>
  </div>

  @if(session('success'))
    <script>
      window.addEventListener('DOMContentLoaded', () => 
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: '{{ session('success') }}',
          confirmButtonColor: '#06b6d4'
        })
      );
    </script>
  @endif

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
          <tr class="text-nowrap">
            <th>Date Delivered</th>
            <th>PO Number</th>
            <th>Supplier</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Items</th>
            <th>Remarks</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $r)
          <tr>
            <td>{{ $r->delivered_date ? $r->delivered_date->format('Y-m-d') : '—' }}</td>
            <td>{{ $r->po_number ?? '—' }}</td>
            <td>{{ $r->supplier->supplier_name ?? '—' }}</td>
            <td>
              <span class="badge bg-{{ $r->status === 'delivered' ? 'success' : ($r->status === 'pending' ? 'warning' : 'danger') }}">
                {{ ucfirst($r->status) }}
              </span>
            </td>
            <td>
              <span class="badge bg-{{ $r->payment_status === 'paid' ? 'success' : ($r->payment_status === 'partial' ? 'info' : 'secondary') }}">
                {{ ucfirst($r->payment_status) }}
              </span>
            </td>
            <td>
              @php $items = json_decode($r->items, true); @endphp
              @if($items && is_array($items))
                <ul class="mb-0 small">
                  @foreach($items as $item)
                    <li>
                      {{ $item['name'] ?? 'Unnamed Item' }} 
                      (x{{ $item['qty'] ?? $item['quantity'] ?? '?' }})
                      @if(isset($item['unit_price'])) 
                        - ₱{{ number_format($item['unit_price'], 2) }}
                      @endif
                    </li>
                  @endforeach
                </ul>
              @else
                —
              @endif
            </td>
            <td>{{ $r->remarks ?? '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center text-muted">No stock-in records yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection

@push('styles')
<style>
.btn-ghost {
  border-radius: 12px;
  background: #fff;
  color: #0d6efd;
  border-color: #cfe2ff;
}
.btn-ghost:hover {
  background: #f1f6ff;
  color: #0a58ca;
  box-shadow: 0 6px 18px rgba(13,110,253,.15);
}
.inv-hero {
  border-radius: 24px;
  background: radial-gradient(700px 200px at 100% 0,rgba(59,130,246,.08),transparent),
              linear-gradient(180deg,#ffffff,#f8fafc);
  border: 1px solid #e8ecf1;
  box-shadow: 0 10px 26px rgba(2,6,23,.06);
}
.inv-hero h1 {
  font-weight: 700;
}
</style>
@endpush

@extends('layouts.finance_app')

@section('title', 'Inventory Dashboard')

@section('content')
<div class="container-xxl py-3">

  <div class="inv-hero p-4 p-md-5 mb-4 position-relative overflow-hidden">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
      <div>
        <h1 class="mb-1">Inventory Management</h1>
        <p class="text-muted mb-0">Manage stock via purchase orders and service requests.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-light border btn-ghost" href="{{ route('finance.inventory.stock-in.index') }}"><i class="fa fa-arrow-down-wide-short me-2"></i>Stock-In</a>
        <a class="btn btn-light border btn-ghost" href="{{ route('finance.inventory.stock-out.index') }}"><i class="fa fa-arrow-up-wide-short me-2"></i>Stock-Out</a>
        
      </div>
    </div>
  </div>

  <!-- Metrics Cards -->
  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">Active Stocks</div>
              <div class="fs-3 fw-bolder text-primary mt-1">
                {{ number_format($activeStockCount) }}
              </div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 10px; background:#DBEAFE; display:flex; align-items:center; justify-content:center;">
              <i class="fa fa-boxes" style="color:#2563EB;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">Low-Stock Items</div>
              <div class="fs-3 fw-bolder text-warning mt-1">{{ number_format($totalStockOutQty) }}</div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 10px; background:#FEF3C7; display:flex; align-items:center; justify-content:center;">
              <i class="fa fa-triangle-exclamation" style="color:#D97706;"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Low Stock Alerts Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
      <h5 class="mb-0 fw-semibold">Low Stock Alerts</h5>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
          <tr class="text-nowrap">
            <th>Date</th>
            <th>Item Name</th>
            <th>Service Type</th>
            <th>Aircon Type</th>
            <th class="text-end">Quantity</th>
            <th>Status</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($stockOutRows as $r)
            <tr>
              <td>{{ $r->issued_date ? \Carbon\Carbon::parse($r->issued_date)->format('Y-m-d') : '—' }}</td>
              <td class="fw-semibold">{{ $r->item_name ?? '—' }}</td>
              <td>{{ $r->service_type ?? '—' }}</td>
              <td>{{ $r->aircon_type ?? '—' }}</td>
              <td class="text-end">{{ $r->quantity }}</td>
              <td>
                @php 
                  $status = $r->status; 
                  $badge = $status === 'Approve' ? 'success' : ($status === 'Requested' ? 'warning text-dark' : 'secondary'); 
                @endphp
                <span class="badge bg-{{ $badge }}">{{ $status }}</span>
              </td>
              <td class="text-center">
                <form method="POST" action="{{ route('finance.inventory.stock-out.request-by-item') }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="item_name" value="{{ $r->item_name }}">
                  <input type="hidden" name="status" value="Requested">
                  <button type="submit" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-2">
                    <i class="fa fa-paper-plane items-center"></i><span>Send Request</span>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td class="py-4 text-center text-muted" colspan="7">No stock-out records.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
.btn-ghost{border-radius:12px;background:#fff;color:#0d6efd;border-color:#cfe2ff}
.btn-ghost:hover{background:#f1f6ff;color:#0a58ca;box-shadow:0 6px 18px rgba(13,110,253,.15)}
.btn-gradient{border-radius:12px;background:linear-gradient(90deg,#e65c33,#f57c42);color:#fff;border:0;padding:.5rem 1rem;box-shadow:0 4px 14px rgba(230,92,51,.3)}
.btn-gradient:hover{filter:brightness(.95);color:#fff}
.inv-hero{border-radius:24px;background:radial-gradient(700px 200px at 100% 0,rgba(59,130,246,.08),transparent),linear-gradient(180deg,#ffffff,#f8fafc);border:1px solid #e8ecf1;box-shadow:0 10px 26px rgba(2,6,23,.06)}
.inv-hero h1{font-weight:700}
</style>
@endpush

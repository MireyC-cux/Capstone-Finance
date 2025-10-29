@extends('layouts.finance_app')

@section('title', 'Inventory Dashboard')

@section('content')
<div class="container-fluid py-4">

   <div>
      <h1 class="h3 fw-bold mb-1">Inventory Management</h1>
      <div class="text-muted small">Managing inventory via purchase order.</div>
    </div>
  <!-- Top Actions -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-outline-secondary btn-sm" href="{{ route('finance.inventory.items.index') }}">Items</a>
      <a class="btn btn-outline-secondary btn-sm" href="{{ route('finance.inventory.stock-in.index') }}">Stock-In</a>
      <a class="btn btn-outline-secondary btn-sm" href="{{ route('finance.inventory.stock-out.index') }}">Stock-Out</a>
      <a class="btn btn-outline-secondary btn-sm" href="{{ route('finance.inventory.adjustments.index') }}">Adjustments</a>
      <a class="btn btn-primary btn-sm" href="{{ route('finance.inventory.reports.index') }}">Reports</a>
    </div>
  </div>

  <!-- Metrics Cards -->
  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold text-uppercase">Active Items</div>
              <div class="fs-3 fw-bolder text-primary mt-1">{{ $items->total() }}</div>
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
              <div class="fs-3 fw-bolder text-warning mt-1">{{ $lowStock->count() }}</div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 10px; background:#FEF3C7; display:flex; align-items:center; justify-content:center;">
              <i class="fa fa-triangle-exclamation" style="color:#D97706;"></i>
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
              <div class="text-muted small fw-semibold text-uppercase">Quick Action</div>
              <div class="mt-2">
                <a href="{{ route('finance.inventory.items.index') }}" class="btn btn-success btn-sm d-inline-flex align-items-center gap-2"><i class="fa fa-plus"></i><span>New Item</span></a>
              </div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 10px; background:#D1FAE5; display:flex; align-items:center; justify-content:center;">
              <i class="fa fa-plus" style="color:#10B981;"></i>
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
            <th>Item</th>
            <th>Category</th>
            <th>Brand/Model</th>
            <th class="text-end">Stock</th>
            <th class="text-end">Reorder Lvl</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($lowStock as $r)
            <tr>
              <td class="fw-semibold">{{ $r->item_name }}</td>
              <td class="text-muted">{{ $r->category }}</td>
              <td class="text-muted">{{ $r->brand }} {{ $r->model }}</td>
              <td class="text-end fw-bold text-warning">{{ $r->stock }}</td>
              <td class="text-end text-muted">{{ $r->reorder_level }}</td>
              <td>
                <a class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2"
                   href="{{ route('purchase-orders.create', ['add_item_name' => $r->item_name]) }}">
                   <i class="fa fa-plus"></i><span>Create PO</span>
                </a>
              </td>
            </tr>
          @empty
            <tr><td class="py-4 text-center text-muted" colspan="6">No low-stock items.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

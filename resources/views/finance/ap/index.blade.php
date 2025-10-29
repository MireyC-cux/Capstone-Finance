@extends('layouts.finance_app')

@section('content')
<div style="padding: 20px;">
  <!-- Page Header -->
  <div style="margin-bottom: 2rem;">
    <div class="d-flex justify-content-between align-items-center">
      <a href="{{ route('purchase-orders.index') }}" class="btn btn-primary" style="gap: 0.5rem;">
        <i class="fa fa-list"></i>
        <span>View POs</span>
      </a>
    </div>
  </div>

  @if(session('success'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Success',text:'{{ session('success') }}',confirmButtonColor:'#06b6d4'}));</script>
  @endif
  @if(session('error'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'error',title:'Error',text:'{{ session('error') }}'}));</script>
  @endif

  <!-- Filter Section -->
  <form method="GET" style="margin-bottom: 2rem;">
    <div class="card" style="padding: 1.25rem;">
      <div class="row g-3">
        <div class="col-12 col-md-6 col-xl">
          <label class="form-label">Supplier</label>
          <select name="supplier_id" class="form-select">
            <option value="">All</option>
            @foreach($suppliers as $s)
              <option value="{{ $s->supplier_id }}" @selected(request('supplier_id')==$s->supplier_id)>{{ $s->supplier_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="">All</option>
            @foreach(['Unpaid','Partially Paid','Paid','Overdue','Cancelled'] as $st)
              <option value="{{ $st }}" @selected(request('status')==$st)>{{ $st }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-6 col-xl">
          <label class="form-label">PO#</label>
          <input type="text" name="po_number" value="{{ request('po_number') }}" class="form-control" placeholder="PO Number" />
        </div>
        <div class="col-12 col-md-6 col-xl">
          <label class="form-label">From</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-control" />
        </div>
        <div class="col-12 col-md-6 col-xl">
          <label class="form-label">To</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-control" />
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2" style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary" style="gap: 0.5rem;"><i class="fa fa-filter"></i><span>Apply Filters</span></button>
        <a href="{{ route('accounts-payable.index') }}" class="btn" style="border: 1px solid var(--border-card); background: white; gap: 0.5rem;"><i class="fa fa-rotate"></i><span>Reset</span></a>
      </div>
    </div>
  </form>

  <!-- Metrics Cards -->
  <div class="row g-4" style="margin-bottom: 2rem;">
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
        <div class="d-flex justify-content-between align-items-center">
          <div style="flex: 1;">
            <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Total Payables</div>
            <div style="font-size: 24px; font-weight: 700; color: #2563EB; margin-top: 0.25rem;">₱{{ number_format($stats['total'] ?? 0,2) }}</div>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 8px; background: #2563EB; color: white; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-file-invoice" style="font-size: 20px;"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
        <div class="d-flex justify-content-between align-items-center">
          <div style="flex: 1;">
            <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Paid</div>
            <div style="font-size: 24px; font-weight: 700; color: #10B981; margin-top: 0.25rem;">₱{{ number_format($stats['paid'] ?? 0,2) }}</div>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 8px; background: #10B981; color: white; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-check-circle" style="font-size: 20px;"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
        <div class="d-flex justify-content-between align-items-center">
          <div style="flex: 1;">
            <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Overdue</div>
            <div style="font-size: 24px; font-weight: 700; color: #EF4444; margin-top: 0.25rem;">{{ $stats['overdue'] ?? 0 }}</div>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 8px; background: #EF4444; color: white; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-exclamation-triangle" style="font-size: 20px;"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
        <div class="d-flex justify-content-between align-items-center">
          <div style="flex: 1;">
            <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Partially Paid</div>
            <div style="font-size: 24px; font-weight: 700; color: #F59E0B; margin-top: 0.25rem;">{{ $stats['partial'] ?? 0 }}</div>
          </div>
          <div style="width: 48px; height: 48px; border-radius: 8px; background: #F59E0B; color: white; display: flex; align-items: center; justify-content: center;">
            <i class="fa fa-clock" style="font-size: 20px;"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Table -->
  <div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
      <table class="table table-hover align-middle" style="margin-bottom: 0;">
        <thead class="table-dark">
          <tr>
            <th style="padding: 8px;">PO#</th>
            <th style="padding: 8px;">Supplier</th>
            <th style="padding: 8px;">Invoice#</th>
            <th style="padding: 8px;">Due Date</th>
            <th style="padding: 8px;">Total</th>
            <th style="padding: 8px;">Paid</th>
            <th style="padding: 8px;">Balance</th>
            <th style="padding: 8px;">Status</th>
            <th style="padding: 8px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($payables as $ap)
          <tr style="@if($ap->is_overdue && $ap->status!=='Paid') background: #FEF2F2; @endif">
            <td style="padding: 8px; font-weight: 600;">{{ $ap->purchaseOrder->po_number ?? '—' }}</td>
            <td style="padding: 8px;">{{ $ap->supplier->supplier_name ?? '—' }}</td>
            <td style="padding: 8px; font-weight: 600;">{{ $ap->invoice_number }}</td>
            <td style="padding: 8px;">{{ \Illuminate\Support\Carbon::parse($ap->due_date)->format('Y-m-d') }}</td>
            <td style="padding: 8px; font-weight: 600;">₱{{ number_format($ap->total_amount,2) }}</td>
            <td style="padding: 8px; color: var(--success);">₱{{ number_format($ap->amount_paid,2) }}</td>
            <td style="padding: 8px; font-weight: 700; color: #2563EB;">₱{{ number_format(max(0, $ap->total_amount - $ap->amount_paid),2) }}</td>
            <td style="padding: 8px;">
              @if($ap->status === 'Paid')
                <span class="badge badge-paid">{{ $ap->status }}</span>
              @elseif($ap->status === 'Partially Paid')
                <span class="badge badge-partially-paid">{{ $ap->status }}</span>
              @elseif($ap->status === 'Overdue')
                <span class="badge badge-unpaid">{{ $ap->status }}</span>
              @elseif($ap->status === 'Cancelled')
                <span class="badge badge-default">{{ $ap->status }}</span>
              @else
                <span class="badge badge-default">{{ $ap->status }}</span>
              @endif
            </td>
            <td style="padding: 8px;">
              <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-sm btn-info" href="{{ route('accounts-payable.show',$ap->ap_id) }}" style="gap: 0.5rem;"><i class="fa fa-eye"></i> View</a>
                <a class="btn btn-sm btn-primary" href="{{ route('accounts-payable.show',$ap->ap_id) }}#record-payment" style="gap: 0.5rem;"><i class="fa fa-coins"></i> Pay</a>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div style="margin-top: 1rem;">{{ $payables->links() }}</div>
</div>
@endsection

@extends('layouts.finance_app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-6">

  <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
    <div>
      <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-brand-600 to-cyan-500 bg-clip-text text-transparent">Purchase Orders</h1>
      <p class="text-slate-600 mt-1">Create, review, and approve supplier POs.</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('finance.accounts-payable') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa fa-file-invoice"></i><span>Accounts Payable</span>
      </a>
      <a href="{{ route('purchase-orders.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-white shadow hover:bg-brand-700 transition">
        <i class="fa fa-plus"></i><span>Create PO</span>
      </a>
    </div>
  </div>

  @if(session('success'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Success',text:'{{ session('success') }}',confirmButtonColor:'#06b6d4'}));</script>
  @endif
  @if(session('error'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'error',title:'Error',text:'{{ session('error') }}'}));</script>
  @endif

  <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6 p-4 bg-white/70 backdrop-blur rounded-2xl shadow-sm border border-slate-200">
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Supplier</label>
      <input type="text" name="supplier" value="{{ request('supplier') }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" placeholder="Search supplier" />
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
      <select name="status" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600">
        <option value="">All</option>
        @foreach(['Pending','Approved','Rejected','Completed'] as $st)
          <option value="{{ $st }}" @selected(request('status')==$st)>{{ $st }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">PO#</label>
      <input type="text" name="po_number" value="{{ request('po_number') }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">From</label>
      <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-600 mb-1">To</label>
      <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
    </div>
    <div class="md:col-span-5 flex gap-2">
      <button class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-white shadow hover:bg-brand-700 transition"><i class="fa fa-filter"></i>Filter</button>
      <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200 transition"><i class="fa fa-rotate"></i>Reset</a>
    </div>
  </form>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl bg-gradient-to-br from-white to-sky-50 border border-sky-100 p-4 shadow-sm">
      <div class="text-xs uppercase text-sky-600">Total POs</div>
      <div class="text-2xl font-extrabold mt-1">{{ $pos->total() }}</div>
    </div>
    <div class="rounded-2xl bg-gradient-to-br from-white to-emerald-50 border border-emerald-100 p-4 shadow-sm">
      <div class="text-xs uppercase text-emerald-600">Approved</div>
      <div class="text-2xl font-extrabold mt-1">{{ $pos->filter(fn($p)=>$p->status==='Approved')->count() }}</div>
    </div>
    <div class="rounded-2xl bg-gradient-to-br from-white to-amber-50 border border-amber-100 p-4 shadow-sm">
      <div class="text-xs uppercase text-amber-600">Pending</div>
      <div class="text-2xl font-extrabold mt-1">{{ $pos->filter(fn($p)=>$p->status==='Pending')->count() }}</div>
    </div>
    <div class="rounded-2xl bg-gradient-to-br from-white to-rose-50 border border-rose-100 p-4 shadow-sm">
      <div class="text-xs uppercase text-rose-600">Rejected</div>
      <div class="text-2xl font-extrabold mt-1">{{ $pos->filter(fn($p)=>$p->status==='Rejected')->count() }}</div>
    </div>
  </div>

  <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50/80 backdrop-blur sticky top-0">
          <tr class="text-left text-slate-600">
            <th class="px-4 py-3 font-semibold">PO#</th>
            <th class="px-4 py-3 font-semibold">Supplier</th>
            <th class="px-4 py-3 font-semibold">Date</th>
            <th class="px-4 py-3 font-semibold">Status</th>
            <th class="px-4 py-3 font-semibold text-right">Total</th>
            <th class="px-4 py-3 font-semibold">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($pos as $po)
          <tr class="hover:bg-slate-50 transition">
            <td class="px-4 py-3 font-mono text-slate-700">{{ $po->po_number }}</td>
            <td class="px-4 py-3">{{ $po->supplier->supplier_name ?? '—' }}</td>
            <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($po->po_date)->format('Y-m-d') }}</td>
            <td class="px-4 py-3">
              @php($badgeMap = [
                'Pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                'Approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'Rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                'Completed' => 'bg-sky-100 text-sky-800 border-sky-200',
              ])
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeMap[$po->status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">{{ $po->status }}</span>
            </td>
            <td class="px-4 py-3 text-right">₱{{ number_format($po->total_amount,2) }}</td>
            <td class="px-4 py-3">
              <div class="flex flex-wrap items-center gap-2">
                <a class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition" href="{{ route('purchase-orders.show',$po->po_id) }}"><i class="fa fa-eye"></i><span>View</span></a>
                @if($po->status==='Pending')
                <form method="POST" class="inline" action="{{ route('purchase-orders.approve',$po->po_id) }}">@csrf
                  <button type="button" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition js-po-confirm" data-confirm-title="Approve this PO?" data-confirm-text="This will approve the PO and create Accounts Payable." data-confirm-icon="question"><i class="fa fa-check"></i><span>Approve</span></button>
                </form>
                <form method="POST" class="inline" action="{{ route('purchase-orders.reject',$po->po_id) }}">@csrf
                  <button type="button" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 bg-rose-600 text-white hover:bg-rose-700 shadow-sm transition js-po-confirm" data-confirm-title="Reject this PO?" data-confirm-text="This will mark the PO as Rejected." data-confirm-icon="warning"><i class="fa fa-xmark"></i><span>Reject</span></button>
                </form>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-6">{{ $pos->links() }}</div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.js-po-confirm');
    buttons.forEach(btn => {
      btn.addEventListener('click', function (e) {
        const form = e.currentTarget.closest('form');
        if (!form) return;
        const title = e.currentTarget.getAttribute('data-confirm-title') || 'Are you sure?';
        const text = e.currentTarget.getAttribute('data-confirm-text') || '';
        const icon = e.currentTarget.getAttribute('data-confirm-icon') || 'question';
        if (typeof Swal === 'undefined') { form.submit(); return; }
        Swal.fire({
          title: title,
          text: text,
          icon: icon,
          showCancelButton: true,
          confirmButtonText: 'Yes, proceed',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#059669',
          cancelButtonColor: '#6b7280'
        }).then(result => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  });
</script>
@endsection

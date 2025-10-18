@extends('layouts.finance_app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-6" x-data="stockOutPage()">
  <div class="flex items-end justify-between mb-6">
    <div>
      <h1 class="text-3xl font-extrabold bg-gradient-to-r from-brand-600 to-cyan-500 bg-clip-text text-transparent">Stock-Out</h1>
      <p class="text-slate-600 mt-1">Issue items to service requests or technicians.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('finance.inventory.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2 text-slate-700 hover:bg-slate-50 transition"><i class="fa fa-arrow-left"></i><span>Back to Dashboard</span></a>
      <button @click="openCreate()" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-white shadow hover:bg-brand-700 transition"><i class="fa fa-minus"></i><span>New Stock-Out</span></button>
    </div>
  </div>

  @if(session('success'))
    <script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Success',text:'{{ session('success') }}',confirmButtonColor:'#06b6d4'}));</script>
  @endif

  <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50/80 backdrop-blur sticky top-0">
          <tr class="text-left text-slate-600">
            <th class="px-4 py-3 font-semibold">Date</th>
            <th class="px-4 py-3 font-semibold">Item</th>
            <th class="px-4 py-3 font-semibold text-right">Qty</th>
            <th class="px-4 py-3 font-semibold">Service Request</th>
            <th class="px-4 py-3 font-semibold">Issued To</th>
            <th class="px-4 py-3 font-semibold">Purpose</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($rows as $r)
          <tr class="hover:bg-slate-50 transition">
            <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($r->issued_date)->format('Y-m-d') }}</td>
            <td class="px-4 py-3">{{ $r->item->item_name ?? '—' }}</td>
            <td class="px-4 py-3 text-right">{{ $r->quantity }}</td>
            <td class="px-4 py-3">{{ $r->service_request_id ? ('SR#'.$r->service_request_id) : '—' }}</td>
            <td class="px-4 py-3">{{ $r->issued_to ? ('EMP#'.$r->issued_to) : '—' }}</td>
            <td class="px-4 py-3">{{ $r->purpose ?? '—' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-6">{{ $rows->links() }}</div>

  <!-- Create Modal -->
  <div x-show="show" x-cloak class="fixed inset-0 z-[1001] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="close()"></div>
    <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
      <div class="flex items-center justify-between px-5 py-3 bg-gradient-to-r from-brand-600 to-cyan-500 text-white">
        <h3 class="font-semibold">New Stock-Out</h3>
        <button class="opacity-90 hover:opacity-100" @click="close()"><i class="fa fa-xmark"></i></button>
      </div>
      <form method="POST" action="{{ route('finance.inventory.stock-out.store') }}" class="p-5 grid grid-cols-1 gap-4">
        @csrf
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Item</label>
          <select name="item_id" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required>
            <option value="">Select item</option>
            @foreach($items as $it)
              <option value="{{ $it->item_id }}">{{ $it->item_name }}</option>
            @endforeach
          </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Quantity</label>
            <input type="number" min="1" name="quantity" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Issued Date</label>
            <input type="date" name="issued_date" value="{{ now()->toDateString() }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Service Request (optional)</label>
            <input type="number" min="1" name="service_request_id" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" placeholder="SR ID" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Issued To (Employee)</label>
            <input type="number" min="1" name="issued_to" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" placeholder="employeeprofiles_id" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Purpose</label>
          <input name="purpose" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" placeholder="Maintenance, Installation, etc." />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Remarks</label>
          <textarea name="remarks" rows="2" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" placeholder="Optional remarks..."></textarea>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
          <button type="button" @click="close()" class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">Cancel</button>
          <button class="rounded-xl bg-brand-600 px-4 py-2 text-white hover:bg-brand-700">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function stockOutPage(){
  return { show:false, openCreate(){ this.show=true }, close(){ this.show=false } }
}
</script>
@endpush
@endsection

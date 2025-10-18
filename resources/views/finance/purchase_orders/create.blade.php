@extends('layouts.finance_app')

@section('content')
<div class="max-w-5xl mx-auto px-4 md:px-6 lg:px-8 py-6">
  <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-2 text-brand-600 hover:text-brand-700 mb-4">
    <i class="fa fa-arrow-left"></i><span>Back to POs</span>
  </a>

  <div class="flex items-end justify-between mb-6">
    <div>
      <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-brand-600 to-cyan-500 bg-clip-text text-transparent">Create Purchase Order</h1>
      <p class="text-slate-600 mt-1">Auto-generates PO number on save.</p>
    </div>
  </div>

  @if($errors->any())
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-rose-700">
      <ul class="list-disc ml-5">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('purchase-orders.store') }}" x-data="poForm()" class="space-y-6">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Supplier</label>
        <select name="supplier_id" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required>
          <option value="">Select supplier</option>
          @foreach($suppliers as $s)
            <option value="{{ $s->supplier_id }}">{{ $s->supplier_name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">PO Date</label>
        <input type="date" name="po_date" value="{{ now()->toDateString() }}" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required />
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Service Request (optional)</label>
        <input type="number" min="1" name="service_request_id" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" placeholder="ID" />
      </div>
    </div>

    <div>
      <label class="block text-xs font-medium text-slate-600 mb-2">Remarks</label>
      <textarea name="remarks" rows="3" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" placeholder="Optional remarks..."></textarea>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-slate-800">Installation Service Requests</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50">
            <tr class="text-left text-slate-600">
              <th class="px-3 py-2 font-semibold">SR#</th>
              <th class="px-3 py-2 font-semibold">Service</th>
              <th class="px-3 py-2 font-semibold">Aircon Type</th>
              <th class="px-3 py-2 font-semibold">Qty</th>
              <th class="px-3 py-2 font-semibold">Unit Price</th>
              <th class="px-3 py-2 font-semibold">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($installationItems as $it)
            <tr>
              <td class="px-3 py-2">{{ $it->service_request_id }}</td>
              <td class="px-3 py-2">{{ $it->service->service_type ?? 'Installation' }}</td>
              <td class="px-3 py-2">
                @php($t = $it->airconType)
                {{ $t->name ?? '—' }}
                @if($t && $t->brand) • {{ $t->brand }} @endif
                @if($t && $t->capacity) • {{ $t->capacity }} @endif
                @if($t && $t->model) • {{ $t->model }} @endif
              </td>
              <td class="px-3 py-2">{{ $it->quantity }}</td>
              <td class="px-3 py-2">₱{{ number_format($it->unit_price ?? 0,2) }}</td>
              <td class="px-3 py-2">
                <button type="button"
                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 bg-brand-600 text-white hover:bg-brand-700 shadow-sm transition"
                        @click="addFromSR({
                          item_id: {{ $it->item_id }},
                          service_request_id: {{ $it->service_request_id }},
                          description: 'Installation - {{ $t->name ? e($t->name) : 'Aircon' }}',
                          quantity: {{ (int)($it->quantity ?? 1) }},
                          unit_price: {{ (float)($it->unit_price ?? 0) }}
                        })">
                  <i class="fa fa-plus"></i><span>Select</span>
                </button>
              </td>
            </tr>
            @empty
            <tr><td class="px-3 py-4 text-slate-500" colspan="6">No installation service requests found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $installationItems->links() }}</div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold text-slate-800">Items</h2>
        <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-3 py-1.5 text-white shadow hover:bg-brand-700" @click="addItem()"><i class="fa fa-plus"></i>Add Item</button>
      </div>
      <template x-for="(it, idx) in items" :key="idx">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-3">
          <div class="md:col-span-3">
            <input :name="`items[${idx}][description]`" x-model="it.description" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" placeholder="Description" />
          </div>
          <div>
            <input type="number" min="1" :name="`items[${idx}][quantity]`" x-model.number="it.quantity" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" placeholder="Qty" required />
          </div>
          <div>
            <input type="number" step="0.01" min="0" :name="`items[${idx}][unit_price]`" x-model.number="it.unit_price" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" placeholder="Unit Price" required />
          </div>
          <div class="flex items-center justify-between">
            <div class="text-slate-600">₱<span x-text="(it.quantity*it.unit_price).toFixed(2)"></span></div>
            <button type="button" class="text-rose-600 hover:text-rose-700" @click="removeItem(idx)"><i class="fa fa-trash"></i></button>
          </div>
        </div>
      </template>
      <input type="hidden" name="service_request_id" :value="linkedServiceRequestId ?? ''">
      <div class="text-right font-semibold">Total: ₱<span x-text="total().toFixed(2)"></span></div>
    </div>

    <div class="flex gap-2">
      <button class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-white shadow hover:bg-brand-700 transition"><i class="fa fa-paper-plane"></i><span>Submit for Approval</span></button>
      <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200 transition"><i class="fa fa-xmark"></i><span>Cancel</span></a>
    </div>
  </form>
</div>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function poForm(){
  return {
    items: [{ description: '', quantity: 1, unit_price: 0 }],
    linkedServiceRequestId: null,
    addItem(){ this.items.push({ description: '', quantity: 1, unit_price: 0 }); },
    removeItem(i){ this.items.splice(i,1); },
    addFromSR(payload){
      if(!this.linkedServiceRequestId){ this.linkedServiceRequestId = payload.service_request_id }
      // Push mapped item preserving link to SR item
      this.items.push({ description: payload.description, quantity: payload.quantity, unit_price: payload.unit_price });
      // Also include hidden item_id field for linkage
      const idx = this.items.length - 1;
      // Create a hidden input on next tick to include item_id
      this.$nextTick(()=>{
        const form = this.$el;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `items[${idx}][item_id]`;
        input.value = payload.item_id;
        form.appendChild(input);
      });
    },
    total(){ return this.items.reduce((s,i)=>s + (Number(i.quantity)||0)*(Number(i.unit_price)||0), 0); }
  }
}
</script>
@endpush
@endsection

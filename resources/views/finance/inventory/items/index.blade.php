@extends('layouts.finance_app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-6" x-data="itemPage()">
  <div class="flex items-end justify-between mb-6">
    <div>
      <h1 class="text-3xl font-extrabold bg-gradient-to-r from-brand-600 to-cyan-500 bg-clip-text text-transparent">Item Masterlist</h1>
      <p class="text-slate-600 mt-1">Manage inventory items (units, parts, materials, consumables).</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('finance.inventory.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2 text-slate-700 hover:bg-slate-50 transition"><i class="fa fa-arrow-left"></i><span>Back to Dashboard</span></a>
      <button @click="openCreate()" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-white shadow hover:bg-brand-700 transition"><i class="fa fa-plus"></i><span>New Item</span></button>
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
            <th class="px-4 py-3 font-semibold">Item</th>
            <th class="px-4 py-3 font-semibold">Category</th>
            <th class="px-4 py-3 font-semibold">Brand/Model</th>
            <th class="px-4 py-3 font-semibold text-right">Reorder</th>
            <th class="px-4 py-3 font-semibold text-right">Stock</th>
            <th class="px-4 py-3 font-semibold text-right">Cost</th>
            <th class="px-4 py-3 font-semibold text-right">Price</th>
            <th class="px-4 py-3 font-semibold">Status</th>
            <th class="px-4 py-3 font-semibold">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($items as $it)
          <tr class="hover:bg-slate-50 transition">
            <td class="px-4 py-3 font-medium">{{ $it->item_name }}</td>
            <td class="px-4 py-3">{{ $it->category }}</td>
            <td class="px-4 py-3">{{ $it->brand }} {{ $it->model }}</td>
            <td class="px-4 py-3 text-right">{{ $it->reorder_level }}</td>
            <td class="px-4 py-3 text-right">{{ optional($it->balance)->current_stock ?? 0 }}</td>
            <td class="px-4 py-3 text-right">{{ $it->unit_cost !== null ? '₱'.number_format($it->unit_cost,2) : '—' }}</td>
            <td class="px-4 py-3 text-right">{{ $it->selling_price !== null ? '₱'.number_format($it->selling_price,2) : '—' }}</td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $it->status==='active' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200' }}">{{ ucfirst($it->status) }}</span>
            </td>
            <td class="px-4 py-3">
              <div class="flex flex-wrap items-center gap-2">
                <button
                  @click="openEditFrom($event.currentTarget)"
                  data-id="{{ $it->item_id }}"
                  data-item_name="{{ $it->item_name }}"
                  data-category="{{ $it->category }}"
                  data-brand="{{ $it->brand }}"
                  data-model="{{ $it->model }}"
                  data-unit="{{ $it->unit }}"
                  data-reorder_level="{{ $it->reorder_level }}"
                  data-unit_cost="{{ $it->unit_cost }}"
                  data-selling_price="{{ $it->selling_price }}"
                  data-status="{{ $it->status }}"
                  class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition"
                ><i class="fa fa-pen"></i><span>Edit</span></button>
                <form method="POST" action="{{ route('finance.inventory.items.destroy', $it->item_id) }}" class="js-delete-item">
                  @csrf @method('DELETE')
                  <button class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 bg-rose-600 text-white hover:bg-rose-700 transition"><i class="fa fa-trash"></i><span>Delete</span></button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-6">{{ $items->links() }}</div>

  <!-- Create / Edit Modal -->
  <div x-show="show" x-cloak class="fixed inset-0 z-[1001] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="close()"></div>
    <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
      <div class="flex items-center justify-between px-5 py-3 bg-gradient-to-r from-brand-600 to-cyan-500 text-white">
        <h3 class="font-semibold" x-text="mode==='create' ? 'New Item' : 'Edit Item'"></h3>
        <button class="opacity-90 hover:opacity-100" @click="close()"><i class="fa fa-xmark"></i></button>
      </div>
      <form method="POST" :action="formAction()" @submit.prevent="beforeSubmit($event)" class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        <input type="hidden" :name="mode==='edit' ? '_method' : null" value="PUT" />
        @if ($errors->any())
          <div class="md:col-span-2">
            <div class="rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-2 text-sm">
              {{ $errors->first() }}
            </div>
          </div>
        @endif
        <div class="md:col-span-2">
          <label class="block text-xs font-medium text-slate-600 mb-1">Item Name</label>
          <input name="item_name" x-model="form.item_name" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Category</label>
          <select name="category" x-model="form.category" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required>
            <option>Aircon Unit</option>
            <option>Spare Part</option>
            <option>Material</option>
            <option>Consumable</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Unit</label>
          <input name="unit" x-model="form.unit" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Brand</label>
          <input name="brand" x-model="form.brand" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Model</label>
          <input name="model" x-model="form.model" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Reorder Level</label>
          <input type="number" min="0" name="reorder_level" x-model.number="form.reorder_level" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Standard Cost</label>
          <input type="number" step="0.01" min="0" name="unit_cost" x-model.number="form.unit_cost" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Selling Price</label>
          <input type="number" step="0.01" min="0" name="selling_price" x-model.number="form.selling_price" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
          <select name="status" x-model="form.status" class="w-full rounded-xl border-slate-300 focus:border-brand-600 focus:ring-brand-600" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="md:col-span-2 flex items-center justify-end gap-2 pt-2">
          <button type="button" @click="close()" class="rounded-xl bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200">Cancel</button>
          <button class="rounded-xl bg-brand-600 px-4 py-2 text-white hover:bg-brand-700">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function itemPage(){
  return {
    show: false,
    mode: 'create',
    form: { item_name:'', category:'Aircon Unit', brand:'', model:'', unit:'pcs', reorder_level:5, unit_cost:null, selling_price:null, status:'active' },
    editId: null,
    openCreate(){ this.mode='create'; this.editId=null; this.form={ item_name:'', category:'Aircon Unit', brand:'', model:'', unit:'pcs', reorder_level:5, unit_cost:null, selling_price:null, status:'active' }; this.show=true; },
    openEdit(it){ this.mode='edit'; this.editId=it.item_id; this.form={ item_name:it.item_name, category:it.category, brand:it.brand, model:it.model, unit:it.unit, reorder_level:it.reorder_level, unit_cost:it.unit_cost, selling_price:it.selling_price, status:it.status }; this.show=true; },
    openEditFrom(el){
      const d = el.dataset;
      this.mode = 'edit';
      this.editId = Number(d.id);
      this.form = {
        item_name: d.item_name || '',
        category: d.category || 'Aircon Unit',
        brand: d.brand || '',
        model: d.model || '',
        unit: d.unit || 'pcs',
        reorder_level: Number(d.reorder_level || 0),
        unit_cost: d.unit_cost === '' || d.unit_cost === 'null' ? null : Number(d.unit_cost),
        selling_price: d.selling_price === '' || d.selling_price === 'null' ? null : Number(d.selling_price),
        status: d.status || 'active'
      };
      this.show = true;
    },
    close(){ this.show=false; },
    formAction(){
      if (this.mode==='create') return '{{ route('finance.inventory.items.store') }}';
      const tpl = '{{ route('finance.inventory.items.update', ['item' => '__ID__']) }}';
      return tpl.replace('__ID__', this.editId);
    },
    beforeSubmit(e){
      // Ensure correct action and method override are set right before submit
      const form = e.target;
      form.action = this.formAction();
      // Handle method spoofing
      let m = form.querySelector('input[data-method]');
      if (!m) {
        m = document.createElement('input');
        m.type = 'hidden';
        m.setAttribute('data-method','1');
        form.appendChild(m);
      }
      if (this.mode === 'edit') {
        m.name = '_method';
        m.value = 'PUT';
      } else {
        m.removeAttribute('name');
        m.value = '';
      }
      form.submit();
    }
  }
}

// SweetAlert2 delete confirmation
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form.js-delete-item').forEach(form => {
    form.addEventListener('submit', function(e){
      e.preventDefault();
      Swal.fire({
        title: 'Delete item?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it',
      }).then((result)=>{ if(result.isConfirmed){ form.submit(); } });
    });
  });
});
</script>
@endpush
@endsection

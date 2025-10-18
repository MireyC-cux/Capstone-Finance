@extends('layouts.finance_app')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-6">
  <div class="flex items-end justify-between mb-6">
    <div>
      <h1 class="text-3xl font-extrabold bg-gradient-to-r from-brand-600 to-cyan-500 bg-clip-text text-transparent">Inventory Dashboard</h1>
      <p class="text-slate-600 mt-1">Balances, low-stock alerts, and quick actions.</p>
    </div>
    <div class="flex gap-2">
      <a class="rounded-xl bg-white border border-slate-200 px-4 py-2 hover:bg-slate-50" href="{{ route('finance.inventory.items.index') }}">Items</a>
      <a class="rounded-xl bg-white border border-slate-200 px-4 py-2 hover:bg-slate-50" href="{{ route('finance.inventory.stock-in.index') }}">Stock-In</a>
      <a class="rounded-xl bg-white border border-slate-200 px-4 py-2 hover:bg-slate-50" href="{{ route('finance.inventory.stock-out.index') }}">Stock-Out</a>
      <a class="rounded-xl bg-white border border-slate-200 px-4 py-2 hover:bg-slate-50" href="{{ route('finance.inventory.adjustments.index') }}">Adjustments</a>
      <a class="rounded-xl bg-brand-600 text-white px-4 py-2 hover:bg-brand-700" href="{{ route('finance.inventory.reports.index') }}">Reports</a>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl bg-gradient-to-br from-white to-sky-50 border border-sky-100 p-4 shadow-sm">
      <div class="text-xs uppercase text-sky-600">Active Items</div>
      <div class="text-2xl font-extrabold mt-1">{{ $items->total() }}</div>
    </div>
    <div class="rounded-2xl bg-gradient-to-br from-white to-amber-50 border border-amber-100 p-4 shadow-sm">
      <div class="text-xs uppercase text-amber-600">Low-Stock Items</div>
      <div class="text-2xl font-extrabold mt-1">{{ $lowStock->count() }}</div>
    </div>
    <div class="rounded-2xl bg-gradient-to-br from-white to-emerald-50 border border-emerald-100 p-4 shadow-sm">
      <div class="text-xs uppercase text-emerald-600">Quick Action</div>
      <div class="mt-1 flex gap-2">
        <a href="{{ route('finance.inventory.items.index') }}" class="rounded-lg bg-emerald-600 text-white px-3 py-1.5 hover:bg-emerald-700">New Item</a>
      </div>
    </div>
  </div>

  <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm mb-6">
    <h2 class="font-semibold text-slate-800 mb-3">Low Stock Alerts</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
          <tr class="text-left text-slate-600">
            <th class="px-3 py-2">Item</th>
            <th class="px-3 py-2">Category</th>
            <th class="px-3 py-2">Brand/Model</th>
            <th class="px-3 py-2 text-right">Stock</th>
            <th class="px-3 py-2 text-right">Reorder Lvl</th>
            <th class="px-3 py-2">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($lowStock as $r)
            <tr>
              <td class="px-3 py-2">{{ $r->item_name }}</td>
              <td class="px-3 py-2">{{ $r->category }}</td>
              <td class="px-3 py-2">{{ $r->brand }} {{ $r->model }}</td>
              <td class="px-3 py-2 text-right">{{ $r->stock }}</td>
              <td class="px-3 py-2 text-right">{{ $r->reorder_level }}</td>
              <td class="px-3 py-2">
                <a class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 bg-brand-600 text-white hover:bg-brand-700"
                   href="{{ route('purchase-orders.create', ['add_item_name' => $r->item_name]) }}">
                   <i class="fa fa-plus"></i><span>Create PO</span>
                </a>
              </td>
            </tr>
          @empty
            <tr><td class="px-3 py-4 text-slate-500" colspan="6">No low-stock items.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

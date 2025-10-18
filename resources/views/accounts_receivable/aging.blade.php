@extends('layouts.finance_app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent">A/R Aging Report</h1>
            <p class="text-sm text-gray-500 mt-1">Outstanding receivables by aging bucket.</p>
        </div>
        <div class="flex gap-2">
            <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-cyan-200 bg-white px-4 py-2 text-cyan-700 hover:bg-cyan-50 transition">
                <i class="fa-regular fa-file-pdf"></i>
                <span>Export PDF</span>
            </a>
            <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 hover:bg-gray-50 transition">
                <i class="fa-regular fa-file-excel"></i>
                <span>Export Excel</span>
            </a>
        </div>
    </div>

    <div class="bg-white/90 backdrop-blur rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-gray-50/70 text-left">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Customer</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Current</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">1–30</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">31–60</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">61–90</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">91+</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($grouped as $customer => $row)
                <tr class="hover:bg-gray-50/60">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $customer }}</td>
                    <td class="px-4 py-3">₱{{ number_format($row['Current'], 2) }}</td>
                    <td class="px-4 py-3">₱{{ number_format($row['1-30'], 2) }}</td>
                    <td class="px-4 py-3">₱{{ number_format($row['31-60'], 2) }}</td>
                    <td class="px-4 py-3">₱{{ number_format($row['61-90'], 2) }}</td>
                    <td class="px-4 py-3">₱{{ number_format($row['91+'], 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-900">₱{{ number_format($row['Total'], 2) }}</td>
                </tr>
                @empty
                <tr><td class="px-4 py-6 text-center text-gray-500" colspan="7">No data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('layouts.finance_app')

@section('title', 'Payroll Management')

@section('content')
<div class="relative">
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-rose-50 via-cyan-50 to-emerald-50"></div>
    <div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <div class="rounded-3xl p-6 bg-white/70 backdrop-blur shadow-sm border border-white/50">
            <h1 class="text-3xl font-extrabold tracking-tight">Payroll Management</h1>
            <p class="text-slate-600 mt-1">Semi-monthly payroll dashboard and actions</p>
        </div>
    </div>
    <div class="flex items-center justify-between mb-4 sticky top-3 z-10">
        <div></div>
        <div class="flex gap-2 bg-white/80 backdrop-blur px-3 py-2 rounded-2xl shadow border border-white/50">
            <form id="exportPayrollForm" method="GET" action="{{ route('finance.payroll.export') }}">
                <input type="hidden" name="employee" value="{{ request('employee') }}">
                <input type="hidden" name="position" value="{{ request('position') }}">
                <input type="hidden" name="status" value="Approved">
                <input type="hidden" name="period_start" value="{{ $period_start }}">
                <input type="hidden" name="period_end" value="{{ $period_end }}">
                <button type="submit" class="px-4 py-2 bg-slate-900/90 hover:bg-slate-900 text-white rounded-2xl shadow">Export PDF</button>
            </form>
            <a href="{{ route('finance.disbursement.index') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl shadow">Disbursed Payroll</a>
            <a href="{{ route('finance.payroll.approvals') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl shadow">Approval Form</a>
            <button id="openGenerateModal" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-2xl shadow">Generate Payroll</button>
        </div>
    </div>

    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 bg-white/90 backdrop-blur p-5 rounded-2xl shadow border border-white/60 mb-6">
        <div>
            <label class="block text-sm text-slate-600">Employee</label>
            <input type="text" name="employee" value="{{ $filters['employee'] ?? '' }}" class="w-full mt-1 border rounded-xl px-3 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="Name">
        </div>
        <div>
            <label class="block text-sm text-slate-600">Position</label>
            <input type="text" name="position" value="{{ $filters['position'] ?? '' }}" class="w-full mt-1 border rounded-xl px-3 py-2 focus:ring-2 focus:ring-cyan-500" placeholder="e.g. Technician">
        </div>
        <div>
            <label class="block text-sm text-slate-600">Status</label>
            <select name="status" class="w-full mt-1 border rounded-xl px-3 py-2 bg-slate-50" disabled>
                <option selected>Approved</option>
            </select>
        </div>
        <div>
            <label class="block text-sm text-slate-600">Period Start</label>
            <input type="date" name="period_start" value="{{ $period_start }}" class="w-full mt-1 border rounded-xl px-3 py-2 focus:ring-2 focus:ring-cyan-500">
        </div>
        <div>
            <label class="block text-sm text-slate-600">Period End</label>
            <input type="date" name="period_end" value="{{ $period_end }}" class="w-full mt-1 border rounded-xl px-3 py-2 focus:ring-2 focus:ring-cyan-500">
        </div>
        <div class="md:col-span-5 flex justify-end mt-2">
            <button class="px-4 py-2 bg-slate-900/90 text-white rounded-2xl hover:bg-slate-900">Apply Filters</button>
        </div>
    </form>

    <div class="bg-white/95 backdrop-blur rounded-2xl shadow-lg overflow-hidden border border-white/60">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gradient-to-r from-slate-50 to-slate-100 text-left text-slate-700">
                    <th class="px-4 py-3">Select</th>
                    <th class="px-4 py-3">Employee</th>
                    <th class="px-4 py-3">Position</th>
                    <th class="px-4 py-3">Pay Period</th>
                    <th class="px-4 py-3">Days Worked</th>
                    <th class="px-4 py-3">OT (hrs)</th>
                    <th class="px-4 py-3">OT Pay</th>
                    <th class="px-4 py-3">Deductions</th>
                    <th class="px-4 py-3">Cash Advance</th>
                    <th class="px-4 py-3">Net Pay</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @php $filterStatus = 'Approved'; @endphp
                @forelse ($rows as $r)
                    @if($r['status'] === 'Approved')
                    <tr class="odd:bg-white even:bg-slate-50/50 hover:bg-slate-50">
                        <td class="px-4 py-3 align-top">
                            <input type="checkbox" class="emp-checkbox" value="{{ $r['employee']->employeeprofiles_id }}">
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="font-semibold">{{ $r['employee']->last_name }}, {{ $r['employee']->first_name }}</div>
                        </td>
                        <td class="px-4 py-3 align-top">{{ $r['position'] }}</td>
                        <td class="px-4 py-3 align-top">{{ $r['period'] }}</td>
                        <td class="px-4 py-3 align-top">{{ $r['days_worked'] }}</td>
                        <td class="px-4 py-3 align-top">{{ $r['ot_hours'] }}</td>
                        <td class="px-4 py-3 align-top">₱ {{ number_format($r['ot_pay'],2) }}</td>
                        <td class="px-4 py-3 align-top">₱ {{ number_format($r['deductions'],2) }}</td>
                        <td class="px-4 py-3 align-top">₱ {{ number_format($r['cash_advance'],2) }}</td>
                        <td class="px-4 py-3 align-top font-semibold">₱ {{ number_format($r['net'],2) }}</td>
                        <td class="px-4 py-3 align-top">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{
                                $r['status']==='Approved' ? 'bg-green-100 text-green-700' : ($r['status']==='Paid' ? 'bg-blue-100 text-blue-700' : ($r['status']==='Pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700'))
                            }}">{{ $r['status'] }}</span>
                        </td>
                        <td class="px-4 py-3 align-top space-x-2">
                            @if($r['payroll'])
                                <a href="{{ route('finance.payroll.payslip', $r['payroll']->payroll_id) }}" class="text-brand-700 hover:underline">Payslip</a>
                                <button class="text-blue-700 hover:underline disburse-btn" data-id="{{ $r['payroll']->payroll_id }}">Disburse</button>
                            @else
                                <span class="text-slate-400">No payroll</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="12" class="px-4 py-6 text-center text-slate-500">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>

<!-- Generate Payroll Modal -->
<div id="generateModal" class="fixed inset-0 bg-black/30 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Generate Payroll</h3>
            <button class="text-slate-500 hover:text-slate-800" id="closeGenerateModal">✕</button>
        </div>
        <form method="POST" action="{{ route('finance.payroll.generate') }}" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-600">Period Start</label>
                    <input type="date" name="period_start" value="{{ $period_start }}" class="w-full mt-1 border rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm text-slate-600">Period End</label>
                    <input type="date" name="period_end" value="{{ $period_end }}" class="w-full mt-1 border rounded-lg px-3 py-2" required>
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-600 mb-1">Selected Employees</label>
                <div id="selectedEmployees" class="text-sm text-slate-700">None</div>
            </div>
            <div id="employeeIdsContainer"></div>
            <div class="flex justify-end gap-3">
                <button type="button" id="cancelGenerate" class="px-4 py-2 rounded-2xl bg-slate-200 hover:bg-slate-300">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white">Save as Pending</button>
            </div>
        </form>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-black/30 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Update Payroll Status</h3>
            <button class="text-slate-500 hover:text-slate-800" id="closeApprovalModal">✕</button>
        </div>
        <form id="approvalForm" method="POST" action="#" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-slate-600">Action</label>
                <select name="action" class="w-full mt-1 border rounded-lg px-3 py-2">
                    <option value="approve">Approve</option>
                    <option value="reject">Reject</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-slate-600">Remarks (optional)</label>
                <input type="text" name="remarks" class="w-full mt-1 border rounded-lg px-3 py-2" placeholder="Reason">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" id="cancelApproval" class="px-4 py-2 rounded-2xl bg-slate-200 hover:bg-slate-300">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-2xl bg-green-600 hover:bg-green-700 text-white">Confirm</button>
            </div>
        </form>
    </div>
</div>

<!-- Disbursement Modal -->
<div id="disbursementModal" class="fixed inset-0 bg-black/30 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold">Record Salary Payment</h3>
            <button class="text-slate-500 hover:text-slate-800" id="closeDisbursementModal">✕</button>
        </div>
        <form method="POST" action="{{ route('finance.disbursement.record') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="payroll_id" id="disbursePayrollId">
            <div>
                <label class="block text-sm text-slate-600">Payment Date</label>
                <input type="date" name="payment_date" class="w-full mt-1 border rounded-lg px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm text-slate-600">Method</label>
                <select name="payment_method" class="w-full mt-1 border rounded-lg px-3 py-2" required>
                    <option>Cash</option>
                    <option>Bank Transfer</option>
                    <option>GCash</option>
                    <option>Check</option>
                    <option>Other</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-slate-600">Reference No.</label>
                <input type="text" name="reference_number" class="w-full mt-1 border rounded-lg px-3 py-2" placeholder="Optional">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" id="cancelDisbursement" class="px-4 py-2 rounded-2xl bg-slate-200 hover:bg-slate-300">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white">Record Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Sweet alerts for flash messages
    @if (session('success'))
    Swal.fire({ icon: 'success', title: 'Success', text: @json(session('success')), confirmButtonColor: '#06b6d4' });
    @endif
    @if (session('error'))
    Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')), confirmButtonColor: '#ef4444' });
    @endif

    const genModal = document.getElementById('generateModal');
    const openGenBtn = document.getElementById('openGenerateModal');
    const closeGenBtn = document.getElementById('closeGenerateModal');
    const cancelGenBtn = document.getElementById('cancelGenerate');

    const apprModal = document.getElementById('approvalModal');
    const closeApprBtn = document.getElementById('closeApprovalModal');
    const cancelApprBtn = document.getElementById('cancelApproval');
    const approvalForm = document.getElementById('approvalForm');

    const disbModal = document.getElementById('disbursementModal');
    const closeDisbBtn = document.getElementById('closeDisbursementModal');
    const cancelDisbBtn = document.getElementById('cancelDisbursement');
    const disbursePayrollId = document.getElementById('disbursePayrollId');

    function openModal(el){ el.classList.remove('hidden'); el.classList.add('flex'); }
    function closeModal(el){ el.classList.add('hidden'); el.classList.remove('flex'); }

    openGenBtn?.addEventListener('click', () => {
        const ids = Array.from(document.querySelectorAll('.emp-checkbox:checked')).map(cb => cb.value);
        const container = document.getElementById('employeeIdsContainer');
        container.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'employee_ids[]';
            input.value = id;
            container.appendChild(input);
        });
        document.getElementById('selectedEmployees').textContent = ids.length ? ids.join(', ') : 'None';
        openModal(genModal);
    });
    closeGenBtn?.addEventListener('click', () => closeModal(genModal));
    cancelGenBtn?.addEventListener('click', () => closeModal(genModal));

    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = btn.dataset.id;
            const action = btn.dataset.action; // approve|reject (prefill select)
            approvalForm.action = `{{ url('finance/payroll') }}/${id}/approve`;
            approvalForm.querySelector('select[name="action"]').value = action;
            openModal(apprModal);
        });
    });
    closeApprBtn?.addEventListener('click', () => closeModal(apprModal));
    cancelApprBtn?.addEventListener('click', () => closeModal(apprModal));

    document.querySelectorAll('.disburse-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            disbursePayrollId.value = btn.dataset.id;
            openModal(disbModal);
        });
    });
    closeDisbBtn?.addEventListener('click', () => closeModal(disbModal));
    cancelDisbBtn?.addEventListener('click', () => closeModal(disbModal));
</script>
@endpush

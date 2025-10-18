@extends('layouts.finance_app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-cyan-600 to-blue-600 bg-clip-text text-transparent">Accounts Receivable</h1>
            <p class="text-sm text-gray-500 mt-1">Track invoices, balances, and incoming payments in real time.</p>
        </div>
        <a href="{{ route('finance.ar.aging') }}" class="inline-flex items-center gap-2 rounded-lg border border-cyan-200 bg-white px-4 py-2 text-cyan-700 hover:bg-cyan-50 transition">
            <i class="fa-solid fa-table-cells-large"></i>
            <span>Aging Report</span>
        </a>
    </div>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.Swal) {
                    Swal.fire({
                        toast: true, position: 'top-end', timer: 2500, showConfirmButton: false,
                        icon: 'success', title: '✅ {{ session('success') }}'
                    });
                }
            });
        </script>
    @endif
    @if(session('info'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.Swal) {
                    Swal.fire({ toast: true, position: 'top-end', timer: 2500, showConfirmButton: false, icon: 'info', title: '{{ session('info') }}' });
                }
            });
        </script>
    @endif

    <form method="GET" class="mb-6">
        <div class="bg-white/80 backdrop-blur rounded-2xl border border-gray-100 shadow-sm p-4 md:p-5">
            <div class="grid md:grid-cols-5 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500">Search</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Customer or Invoice #" class="w-full border border-gray-200 rounded-lg pl-9 pr-3 py-2 focus:ring-2 focus:ring-cyan-500 focus:outline-none" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                        <option value="">All</option>
                        @foreach(['Unpaid','Partially Paid','Paid','Overdue'] as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-cyan-500 focus:outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-cyan-500 focus:outline-none" />
                </div>
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
                <button class="inline-flex items-center gap-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white px-4 py-2 rounded-lg shadow-sm">
                    <i class="fa-solid fa-sliders"></i><span>Filter</span>
                </button>
                <a href="{{ route('accounts-receivable.index') }}" class="inline-flex items-center gap-2 border border-gray-200 hover:border-gray-300 px-4 py-2 rounded-lg">
                    <i class="fa-solid fa-rotate-left"></i><span>Reset</span>
                </a>
            </div>
        </div>
    </form>

    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-2xl border border-cyan-100 bg-gradient-to-br from-cyan-50 to-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center"><i class="fa-solid fa-sack-dollar"></i></div>
                <div>
                    <div class="text-xs text-gray-500">Total Outstanding</div>
                    <div class="text-2xl font-bold text-cyan-700">₱{{ number_format($totals['total_outstanding'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="text-xs text-gray-500">Total Paid</div>
                    <div class="text-2xl font-bold text-emerald-700">₱{{ number_format($totals['total_paid'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <div class="text-xs text-gray-500">Total Overdue</div>
                    <div class="text-2xl font-bold text-rose-700">₱{{ number_format($totals['total_overdue'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white/90 backdrop-blur rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-gray-50/70 text-left">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500"><input type="checkbox" class="rounded" /></th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Invoice #</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Customer</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Invoice Date</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Due Date</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Total</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Paid</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Balance</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($ars as $ar)
                <tr class="hover:bg-gray-50/60">
                    <td class="px-4 py-3"><input type="checkbox" class="rounded" /></td>
                    <td class="px-4 py-3">{{ $ar->invoice_number ?? $ar->invoice->invoice_number ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $ar->customer->business_name ?? $ar->customer->full_name }}</td>
                    <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($ar->invoice_date)->format('Y-m-d') }}</td>
                    <td class="px-4 py-3">{{ \Illuminate\Support\Carbon::parse($ar->due_date)->format('Y-m-d') }}</td>
                    <td class="px-4 py-3">₱{{ number_format($ar->total_amount, 2) }}</td>
                    <td class="px-4 py-3">₱{{ number_format($ar->amount_paid, 2) }}</td>
                    <td class="px-4 py-3">₱{{ number_format($ar->balance, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border
                            @class([
                                'bg-gray-50 text-gray-700 border-gray-200' => $ar->status==='Unpaid',
                                'bg-amber-50 text-amber-700 border-amber-200' => $ar->status==='Partially Paid',
                                'bg-emerald-50 text-emerald-700 border-emerald-200' => $ar->status==='Paid',
                                'bg-rose-50 text-rose-700 border-rose-200' => $ar->status==='Overdue',
                            ])">
                            {{ $ar->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 flex flex-wrap gap-2">
                        <button type="button" data-id="{{ $ar->ar_id }}" class="view-details inline-flex items-center gap-2 border border-gray-200 hover:border-gray-300 px-3 py-1.5 rounded-lg text-sm">
                            <i class="fa-regular fa-eye"></i> View Details
                        </button>
                        <a href="{{ route('payments.history') }}" class="inline-flex items-center gap-2 border border-gray-200 hover:border-gray-300 px-3 py-1.5 rounded-lg text-sm">
                            <i class="fa-regular fa-clock"></i> View History
                        </a>
                        <form class="inline record-payment-form" method="POST" action="#" data-ar-id="{{ $ar->ar_id }}">
                            @csrf
                            <input type="hidden" name="ar_id" value="{{ $ar->ar_id }}" />
                            <button type="button" class="open-payment-modal inline-flex items-center gap-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white px-3 py-1.5 rounded-lg text-sm shadow-sm" data-ar-id="{{ $ar->ar_id }}">
                                <i class="fa-solid fa-plus"></i> Record Payment
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $ars->links() }}</div>

    <!-- View Details Modal -->
    <div id="arModal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/30 p-4">
        <div class="w-full max-w-3xl rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">AR Details</h3>
                <button class="close-ar-modal text-gray-500 hover:text-gray-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-6 grid md:grid-cols-2 gap-4 text-sm" id="arModalBody">
                <div>
                    <div class="text-gray-500">Customer</div>
                    <div class="font-medium" id="mdCustomer">—</div>
                </div>
                <div>
                    <div class="text-gray-500">Invoice #</div>
                    <div class="font-medium" id="mdInvoice">—</div>
                </div>
                <div>
                    <div class="text-gray-500">Invoice Date</div>
                    <div class="font-medium" id="mdInvDate">—</div>
                </div>
                <div>
                    <div class="text-gray-500">Due Date</div>
                    <div class="font-medium" id="mdDue">—</div>
                </div>
                <div>
                    <div class="text-gray-500">Total</div>
                    <div class="font-semibold" id="mdTotal">—</div>
                </div>
                <div>
                    <div class="text-gray-500">Paid</div>
                    <div class="font-semibold" id="mdPaid">—</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-gray-500">Balance</div>
                    <div class="text-xl font-bold text-cyan-700" id="mdBalance">—</div>
                </div>
            </div>
            <div class="px-6 py-4 border-t flex justify-end gap-2">
                <button class="close-ar-modal border border-gray-200 px-4 py-2 rounded-lg">Close</button>
                <button class="open-payment-from-details bg-gradient-to-r from-cyan-600 to-blue-600 text-white px-4 py-2 rounded-lg">Record Payment</button>
            </div>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/30 p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">Record Payment</h3>
                <button class="close-payment-modal text-gray-500 hover:text-gray-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="paymentForm" method="POST" action="#" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="ar_id" id="pmArId" />
                <div>
                    <label class="block text-xs text-gray-500">Payment Date</label>
                    <input type="date" name="payment_date" id="pmDate" class="w-full border border-gray-200 rounded-lg px-3 py-2" value="{{ now()->toDateString() }}" required>
                </div>
                <div>
                    <label class="block text-xs text-gray-500">Payment Method</label>
                    <select name="payment_method" id="pmMethod" class="w-full border border-gray-200 rounded-lg px-3 py-2" required>
                        <option>Cash</option>
                        <option>GCash</option>
                        <option>Bank Transfer</option>
                        <option>Check</option>
                    </select>
                </div>
                <div id="gcashQrContainer" class="hidden mt-3 text-center border border-gray-200 rounded-lg p-3">
                  <p class="text-sm text-gray-600 mb-2">Scan this GCash QR to pay:</p>
                  <div id="gcashQrCode"></div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500">Reference #</label>
                    <input type="text" name="reference_number" id="pmRef" class="w-full border border-gray-200 rounded-lg px-3 py-2" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-xs text-gray-500">Amount</label>
                    <input type="number" min="0.01" step="0.01" name="amount" id="pmAmount" class="w-full border border-gray-200 rounded-lg px-3 py-2" placeholder="0.00" required>
                    <small class="text-xs text-gray-500">Max: <span id="pmMax">—</span></small>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="close-payment-modal border border-gray-200 px-4 py-2 rounded-lg">Cancel</button>
                    <button type="submit" class="bg-gradient-to-r from-cyan-600 to-blue-600 text-white px-4 py-2 rounded-lg">Save Payment</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const arModal = document.getElementById('arModal');
        const paymentModal = document.getElementById('paymentModal');
        let currentAR = null;

        function open(el){ el.classList.remove('hidden'); el.classList.add('flex'); }
        function close(el){ el.classList.add('hidden'); el.classList.remove('flex'); }

        document.querySelectorAll('.view-details').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                const res = await fetch(`/accounts-receivable/${id}`);
                if(!res.ok){ return Swal && Swal.fire('Error', 'Unable to load AR details', 'error'); }
                const ar = await res.json();
                currentAR = ar;
                document.getElementById('mdCustomer').textContent = ar.customer?.business_name ?? ar.customer?.full_name ?? '—';
                document.getElementById('mdInvoice').textContent = ar.invoice_number ?? ar.invoice?.invoice_number ?? '—';
                document.getElementById('mdInvDate').textContent = ar.invoice_date;
                document.getElementById('mdDue').textContent = ar.due_date;
                document.getElementById('mdTotal').textContent = `₱${Number(ar.total_amount).toLocaleString(undefined,{minimumFractionDigits:2})}`;
                document.getElementById('mdPaid').textContent = `₱${Number(ar.amount_paid).toLocaleString(undefined,{minimumFractionDigits:2})}`;
                const balance = (Number(ar.total_amount) - Number(ar.amount_paid)).toFixed(2);
                document.getElementById('mdBalance').textContent = `₱${Number(balance).toLocaleString(undefined,{minimumFractionDigits:2})}`;
                open(arModal);
            });
        });

        document.querySelectorAll('.close-ar-modal').forEach(x => x.addEventListener('click', () => close(arModal)));
        document.querySelectorAll('.close-payment-modal').forEach(x => x.addEventListener('click', () => close(paymentModal)));

        // Open payment modal from list button
        document.querySelectorAll('.open-payment-modal').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-ar-id');
                const res = await fetch(`/accounts-receivable/${id}`);
                if(!res.ok){ return Swal && Swal.fire('Error', 'Unable to load AR', 'error'); }
                const ar = await res.json();
                currentAR = ar;
                const balance = Math.max(0, Number(ar.total_amount) - Number(ar.amount_paid));
                document.getElementById('pmArId').value = ar.ar_id;
                document.getElementById('pmAmount').value = balance.toFixed(2);
                document.getElementById('pmMax').textContent = `₱${balance.toLocaleString(undefined,{minimumFractionDigits:2})}`;
                const form = document.getElementById('paymentForm');
                form.action = `/accounts-receivable/${ar.ar_id}/payment`;
                open(paymentModal);
            });
        });

        // Open payment modal from details footer
        document.querySelector('.open-payment-from-details')?.addEventListener('click', () => {
            if(!currentAR) return;
            const balance = Math.max(0, Number(currentAR.total_amount) - Number(currentAR.amount_paid));
            document.getElementById('pmArId').value = currentAR.ar_id;
            document.getElementById('pmAmount').value = balance.toFixed(2);
            document.getElementById('pmMax').textContent = `₱${balance.toLocaleString(undefined,{minimumFractionDigits:2})}`;
            const form = document.getElementById('paymentForm');
            form.action = `/accounts-receivable/${currentAR.ar_id}/payment`;
            close(arModal); open(paymentModal);
        });

        // Validate amount > 0
        document.getElementById('paymentForm').addEventListener('submit', (e) => {
            const amount = parseFloat(document.getElementById('pmAmount').value || '0');
            if(amount <= 0){ e.preventDefault(); Swal && Swal.fire('Invalid Amount','Please enter an amount greater than 0.00','warning'); }
        });

        // Dynamic GCash QR loading
        const pmMethod = document.getElementById('pmMethod');
        const gcashQrContainer = document.getElementById('gcashQrContainer');
        const gcashQrCode = document.getElementById('gcashQrCode');
        pmMethod.addEventListener('change', async () => {
            if (pmMethod.value.toLowerCase() === 'gcash') {
                const arId = document.getElementById('pmArId').value;
                const amount = document.getElementById('pmAmount').value;
                try {
                    const res = await fetch(`/payments/gcash/source`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                        },
                        body: JSON.stringify({ ar_id: arId, amount: amount })
                    });
                    const data = await res.json();
                    if (data.checkout_url) {
                        gcashQrContainer.classList.remove('hidden');
                        gcashQrCode.innerHTML = "";
                        new QRCode(gcashQrCode, { text: data.checkout_url, width: 180, height: 180 });
                    } else {
                        gcashQrContainer.classList.add('hidden');
                        gcashQrCode.innerHTML = "";
                        window.Swal && Swal.fire('Error', 'Unable to generate GCash QR', 'error');
                    }
                } catch (e) {
                    gcashQrContainer.classList.add('hidden');
                    gcashQrCode.innerHTML = "";
                    window.Swal && Swal.fire('Error', 'Failed to connect to PayMongo', 'error');
                }
            } else {
                gcashQrContainer.classList.add('hidden');
                gcashQrCode.innerHTML = "";
            }
        });
    });
    </script>
</div>
@endsection

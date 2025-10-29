@extends('layouts.finance_app')

@section('content')
<div style="padding: 20px;">
    <!-- Page Header -->
    <div style="margin-bottom: 2rem;">
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('finance.ar.aging') }}" class="btn btn-primary" style="min-height: 38px; gap: 0.5rem;">
                <i class="fa-solid fa-table-cells-large"></i>
                <span>Aging Report</span>
            </a>
        </div>
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
    @if(session('receipt_url'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment saved',
                        text: 'Receipt is ready.',
                        showCancelButton: true,
                        confirmButtonText: 'View Receipt',
                        cancelButtonText: 'Close'
                    }).then(res => {
                        if (res.isConfirmed) {
                            window.open(@json(session('receipt_url')), '_blank');
                        }
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

    <!-- Filter Section -->
    <form method="GET" style="margin-bottom: 2rem;">
        <div class="card" style="padding: 1.25rem;">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-5">
                    <label class="form-label" style="font-size: 14px; font-weight: 500; margin-bottom: 0.5rem;">Search</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Customer or Invoice #" class="form-control" style="padding-left: 2.5rem;" />
                    </div>
                </div>
                <div class="col-12 col-md">
                    <label class="form-label" style="font-size: 14px; font-weight: 500; margin-bottom: 0.5rem;">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['Unpaid','Partially Paid','Paid','Overdue'] as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md">
                    <label class="form-label" style="font-size: 14px; font-weight: 500; margin-bottom: 0.5rem;">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control" />
                </div>
                <div class="col-12 col-md">
                    <label class="form-label" style="font-size: 14px; font-weight: 500; margin-bottom: 0.5rem;">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control" />
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2" style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="gap: 0.5rem;">
                    <i class="fa-solid fa-sliders"></i><span>Apply Filters</span>
                </button>
                <a href="{{ route('accounts-receivable.index') }}" class="btn" style="border: 1px solid var(--border-card); background: white; gap: 0.5rem;">
                    <i class="fa-solid fa-rotate-left"></i><span>Reset</span>
                </a>
            </div>
        </div>
    </form>

    <!-- Metrics Cards -->
    <div class="row g-4" style="margin-bottom: 2rem;">
        <div class="col-12 col-lg-4">
            <div class="card metric-card" style="min-height: 96px; padding: 1rem 1.25rem; cursor: pointer; transition: all 0.2s ease-in-out;" data-metric="outstanding" title="View outstanding breakdown">
                <div class="d-flex justify-content-between align-items-center">
                    <div style="flex: 1;">
                        <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Total Outstanding</div>
                        <div style="font-size: 24px; font-weight: 700; color: #2563EB; margin-top: 0.25rem;">₱{{ number_format($totals['total_outstanding'] ?? 0, 2) }}</div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: #2563EB; color: white; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-sack-dollar" style="font-size: 20px;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card metric-card" style="min-height: 96px; padding: 1rem 1.25rem; cursor: pointer; transition: all 0.2s ease-in-out;" data-metric="paid" title="View payments breakdown">
                <div class="d-flex justify-content-between align-items-center">
                    <div style="flex: 1;">
                        <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Total Paid</div>
                        <div style="font-size: 24px; font-weight: 700; color: var(--success); margin-top: 0.25rem;">₱{{ number_format($totals['total_paid'] ?? 0, 2) }}</div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: var(--success); color: white; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-circle-check" style="font-size: 20px;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card metric-card" style="min-height: 96px; padding: 1rem 1.25rem; cursor: pointer; transition: all 0.2s ease-in-out;" data-metric="overdue" title="View overdue breakdown">
                <div class="d-flex justify-content-between align-items-center">
                    <div style="flex: 1;">
                        <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Total Overdue</div>
                        <div style="font-size: 24px; font-weight: 700; color: var(--danger); margin-top: 0.25rem;">₱{{ number_format($totals['total_overdue'] ?? 0, 2) }}</div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: var(--danger); color: white; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-triangle-exclamation" style="font-size: 20px;"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="margin-bottom: 0;">
                <thead class="table-white">
                    <tr>
                        <th style="width: 40px; padding: 8px;"><input type="checkbox" /></th>
                        <th style="padding: 8px; font-weight: bold;">Invoice #</th>
                        <th style="padding: 8px; font-weight: bold;">Customer</th>
                        <th style="padding: 8px; font-weight: bold;">Invoice Date</th>
                        <th style="padding: 8px; font-weight: bold;">Due Date</th>
                        <th style="padding: 8px; font-weight: bold;">Total</th>
                        <th style="padding: 8px; font-weight: bold;">Paid</th>
                        <th style="padding: 8px; font-weight: bold;">Balance</th>
                        <th style="padding: 8px; font-weight: bold;">Status</th>
                        <th style="padding: 8px; font-weight: bold;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($ars as $ar)
                <tr>
                    <td style="padding: 8px;"><input type="checkbox" /></td>
                    <td style="padding: 8px; font-weight: 600;">{{ $ar->invoice_number ?? $ar->invoice->invoice_number ?? '—' }}</td>
                    <td style="padding: 8px;">{{ $ar->customer->business_name ?? $ar->customer->full_name }}</td>
                    <td style="padding: 8px;">{{ \Illuminate\Support\Carbon::parse($ar->invoice_date)->format('Y-m-d') }}</td>
                    <td style="padding: 8px;">{{ \Illuminate\Support\Carbon::parse($ar->due_date)->format('Y-m-d') }}</td>
                    <td style="padding: 8px; font-weight: 600;">₱{{ number_format($ar->total_amount, 2) }}</td>
                    <td style="padding: 8px; color: var(--success);">₱{{ number_format($ar->amount_paid, 2) }}</td>
                    <td style="padding: 8px; font-weight: 700; color: #2563EB;">₱{{ number_format($ar->balance, 2) }}</td>
                    <td style="padding: 8px;">
                        @if($ar->status === 'Paid')
                            <span class="badge badge-paid">{{ $ar->status }}</span>
                        @elseif($ar->status === 'Partially Paid')
                            <span class="badge badge-partially-paid">{{ $ar->status }}</span>
                        @elseif($ar->status === 'Overdue')
                            <span class="badge badge-unpaid">{{ $ar->status }}</span>
                        @else
                            <span class="badge badge-default">{{ $ar->status }}</span>
                        @endif
                    </td>
                    <td style="padding: 8px;">
                        <button type="button" data-id="{{ $ar->ar_id }}" class="btn btn-sm btn-info view-details" style="gap: 0.5rem;">
                            <i class="fa-regular fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 1rem;">{{ $ars->links() }}</div>

    <!-- View Details Modal -->
    <div class="modal" id="arModal" tabindex="-1" style="display: none;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: 8px;">
                <div class="modal-header" style="padding: 1rem 1.5rem;">
                    <h5 class="modal-title" style="font-size: 20px; font-weight: 600;">AR Details</h5>
                    <button type="button" class="btn-close close-ar-modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="row g-3" id="arModalBody">
                        <div class="col-md-6">
                            <div style="font-size: 14px; color: var(--text-muted);">Customer</div>
                            <div style="font-weight: 500;" id="mdCustomer">—</div>
                        </div>
                        <div class="col-md-6">
                            <div style="font-size: 14px; color: var(--text-muted);">Invoice #</div>
                            <div style="font-weight: 500;" id="mdInvoice">—</div>
                        </div>
                        <div class="col-md-6">
                            <div style="font-size: 14px; color: var(--text-muted);">Invoice Date</div>
                            <div style="font-weight: 500;" id="mdInvDate">—</div>
                        </div>
                        <div class="col-md-6">
                            <div style="font-size: 14px; color: var(--text-muted);">Due Date</div>
                            <div style="font-weight: 500;" id="mdDue">—</div>
                        </div>
                        <div class="col-md-6">
                            <div style="font-size: 14px; color: var(--text-muted);">Total</div>
                            <div style="font-weight: 600;" id="mdTotal">—</div>
                        </div>
                        <div class="col-md-6">
                            <div style="font-size: 14px; color: var(--text-muted);">Paid</div>
                            <div style="font-weight: 600;" id="mdPaid">—</div>
                        </div>
                        <div class="col-12">
                            <div style="font-size: 14px; color: var(--text-muted);">Balance</div>
                            <div style="font-size: 20px; font-weight: 700; color: #2563EB;" id="mdBalance">—</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem 1.5rem;">
                    <button type="button" class="btn close-ar-modal" style="border: 1px solid var(--border-card); background: white;">Close</button>
                    <button type="button" class="btn btn-primary open-payment-from-details">Record Payment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div class="modal" id="paymentModal" tabindex="-1" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 8px;">
                <div class="modal-header" style="padding: 1rem 1.5rem;">
                    <h5 class="modal-title" style="font-size: 20px; font-weight: 600;">Record Payment</h5>
                    <button type="button" class="btn-close close-payment-modal" aria-label="Close"></button>
                </div>
                <form id="paymentForm" method="POST" action="#" enctype="multipart/form-data">
                <div class="modal-body" style="padding: 1.5rem;">
                    @csrf
                    <input type="hidden" name="ar_id" id="pmArId" />
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" id="pmDate" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" id="pmMethod" class="form-select" required>
                            <option>Cash</option>
                            <option>GCash</option>
                            <option>Bank Transfer</option>
                            <option>Check</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Type</label>
                        <select name="payment_type" id="pmType" class="form-select" required>
                            <option value="Full">Full</option>
                            <option value="Partial">Partial</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference #</label>
                        <input type="text" name="reference_number" id="pmRef" class="form-control" placeholder="Required if not cash">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Official Receipt (Image/PDF)</label>
                        <input type="file" name="or_file" id="pmOr" class="form-control" accept="image/*,application/pdf">
                        <small style="font-size: 14px; color: var(--text-muted);">Required if not cash.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" min="0.01" step="0.01" name="amount" id="pmAmount" class="form-control" placeholder="0.00" required>
                        <small style="font-size: 14px; color: var(--text-muted);">Outstanding: <span id="pmMax" data-value="0">—</span></small>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem 1.5rem;">
                    <button type="button" class="btn close-payment-modal" style="border: 1px solid var(--border-card); background: white;">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Payment</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Totals Breakdown Modal -->
    <div class="modal" id="totalsModal" tabindex="-1" style="display: none;">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 8px;">
                <div class="modal-header" style="padding: 1rem 1.5rem;">
                    <div>
                        <h5 class="modal-title" style="font-size: 20px; font-weight: 600;" id="totalsTitle">Totals Breakdown</h5>
                        <div style="font-size: 14px; color: var(--text-muted);">Sum: <span id="totalsSum">—</span></div>
                    </div>
                    <button type="button" class="btn-close close-totals-modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark" id="totalsHead"></thead>
                            <tbody id="totalsBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 1rem 1.5rem;">
                    <button type="button" class="btn close-totals-modal" style="border: 1px solid var(--border-card); background: white;">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const arModal = new bootstrap.Modal(document.getElementById('arModal'));
        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        const totalsModal = new bootstrap.Modal(document.getElementById('totalsModal'));
        let currentAR = null;

        function open(modal){ modal.show(); }
        function close(modal){ modal.hide(); }

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
        document.querySelectorAll('.close-totals-modal').forEach(x => x.addEventListener('click', () => close(totalsModal)));

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
                const pmAmount = document.getElementById('pmAmount');
                const pmMax = document.getElementById('pmMax');
                pmMax.textContent = `₱${balance.toLocaleString(undefined,{minimumFractionDigits:2})}`;
                pmMax.dataset.value = String(balance.toFixed(2));
                const pmType = document.getElementById('pmType');
                if ((pmType.value||'Full') === 'Full') { pmAmount.value = balance.toFixed(2); pmAmount.readOnly = true; } else { pmAmount.readOnly = false; pmAmount.value = ''; }
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
            const pmAmount = document.getElementById('pmAmount');
            const pmMax = document.getElementById('pmMax');
            pmMax.textContent = `₱${balance.toLocaleString(undefined,{minimumFractionDigits:2})}`;
            pmMax.dataset.value = String(balance.toFixed(2));
            const pmType = document.getElementById('pmType');
            if ((pmType.value||'Full') === 'Full') { pmAmount.value = balance.toFixed(2); pmAmount.readOnly = true; } else { pmAmount.readOnly = false; pmAmount.value=''; }
            const form = document.getElementById('paymentForm');
            form.action = `/accounts-receivable/${currentAR.ar_id}/payment`;
            close(arModal); open(paymentModal);
        });

        // Validate amount > 0
        document.getElementById('paymentForm').addEventListener('submit', (e) => {
            const pmMethod = document.getElementById('pmMethod');
            const pmRef = document.getElementById('pmRef');
            const pmFile = document.getElementById('pmOr');
            const pmType = document.getElementById('pmType');
            const pmAmount = document.getElementById('pmAmount');
            const pmMax = document.getElementById('pmMax');
            const out = parseFloat(pmMax.dataset.value || '0');
            const amt = parseFloat(pmAmount.value || '0');
            const isCash = (pmMethod.value||'').toLowerCase() === 'cash';
            if (!isCash && !pmRef.value.trim()) { e.preventDefault(); return Swal && Swal.fire('Missing Reference','Reference number is required for non-cash payments.','warning'); }
            if (!isCash && !pmFile.files.length) { e.preventDefault(); return Swal && Swal.fire('Missing OR','Please upload the Official Receipt (image/PDF) for non-cash payments.','warning'); }
            if ((pmType.value||'Full') === 'Full') {
                if (Math.abs(amt - out) > 0.009) { e.preventDefault(); return Swal && Swal.fire('Invalid Amount','Full payment must match the outstanding balance.','error'); }
            } else {
                if (!(amt > 0 && amt < out)) { e.preventDefault(); return Swal && Swal.fire('Invalid Amount','Partial payment must be > 0 and < outstanding.','error'); }
            }
        });

        // Dynamic GCash QR loading
        const pmMethod = document.getElementById('pmMethod');
        const gcashQrContainer = document.getElementById('gcashQrContainer');
        const gcashQrCode = document.getElementById('gcashQrCode');
        const pmType = document.getElementById('pmType');
        const pmRef = document.getElementById('pmRef');
        const pmOr = document.getElementById('pmOr');
        pmMethod.addEventListener('change', async () => {
            const isCash = (pmMethod.value||'').toLowerCase() === 'cash';
            pmRef.required = !isCash; pmOr.required = !isCash;
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

        // Payment type changes amount behavior
        pmType.addEventListener('change', () => {
            const pmAmount = document.getElementById('pmAmount');
            const out = parseFloat(document.getElementById('pmMax').dataset.value || '0');
            if ((pmType.value||'Full') === 'Full') { pmAmount.value = out.toFixed(2); pmAmount.readOnly = true; }
            else { pmAmount.readOnly = false; if (!pmAmount.value || parseFloat(pmAmount.value) >= out) pmAmount.value = ''; }
        });

        // Metric cards -> totals breakdown modal
        function formatMoney(v){
            const n = Number(v||0);
            return '₱'+n.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
        }
        function renderTable(headCols, rows){
            const thead = document.getElementById('totalsHead');
            const tbody = document.getElementById('totalsBody');
            thead.innerHTML = '<tr>' + headCols.map(c=>`<th class=\"px-3 py-2 text-xs font-semibold text-gray-600\">${c.replace('_',' ').toUpperCase()}</th>`).join('') + '</tr>';
            const moneyCols = new Set(['amount','total','paid','balance']);
            tbody.innerHTML = rows.map(r => {
                return '<tr>' + headCols.map(c => {
                    let val = r[c] ?? '';
                    if (moneyCols.has(c)) val = formatMoney(val);
                    return `<td class=\"px-3 py-2\">${String(val)}</td>`;
                }).join('') + '</tr>';
            }).join('');
        }
        document.querySelectorAll('.metric-card').forEach(card => {
            card.addEventListener('click', async () => {
                const metric = card.getAttribute('data-metric');
                try {
                    const res = await fetch(`/accounts-receivable/totals?metric=${encodeURIComponent(metric)}`);
                    if (!res.ok) { throw new Error('failed'); }
                    const data = await res.json();
                    document.getElementById('totalsTitle').textContent = data.title || 'Totals Breakdown';
                    document.getElementById('totalsSum').textContent = formatMoney(data.total || 0);
                    renderTable(data.columns || [], data.rows || []);
                    open(totalsModal);
                } catch (e) {
                    window.Swal && Swal.fire('Error','Unable to load totals breakdown','error');
                }
            });
        });
    });
    </script>
</div>
@endsection

@extends('layouts.finance_app')

@section('content')
<div class="container-xxl py-3">
    <!-- Header -->
    <div class="inv-hero p-4 p-md-5 mb-4 position-relative overflow-hidden">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="mb-1">Stock-Out</h1>
                <p class="text-muted mb-0">Record and manage stock-out items (no deletion allowed).</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('finance.inventory.dashboard') }}" class="btn btn-light border btn-ghost">
                    <i class="fa fa-arrow-left me-2"></i><span>Back to Dashboard</span>
                </a>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fa fa-plus me-2"></i> Add Stock-Out
                </button>
            </div>
        </div>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#06b6d4'
                });
            });
        </script>
    @endif

    <!-- Search -->
    <div class="mb-3">
        <div class="row g-2">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                    <input type="text" id="stockOutSearch" class="form-control" placeholder="Search item name or service type...">
                </div>
            </div>
        </div>
    </div>

    <!-- Stock-Out Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table id="stockOutTable" class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-nowrap">
                        <th>Date</th>
                        <th>Item Name</th>
                        <th>Service Type</th>
                        <th>Aircon Type</th>
                        <th class="text-end">Quantity</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($r->issued_date)->format('Y-m-d') }}</td>
                        <td class="item-name">{{ $r->item_name ?? '—' }}</td>
                        <td>{{ $r->service_type ?? '—' }}</td>
                        <td>{{ $r->airconType->name ?? '—' }}</td>
                        <td class="text-end">{{ $r->quantity }}</td>
                        <td>
                            @php 
                                $status = $r->status; 
                                $badge = $status === 'Approve' ? 'success' : ($status === 'Requested' ? 'warning text-dark' : 'secondary'); 
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $status }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewModal-{{ $r->stock_out_id }}">
                                    <i class="fa fa-eye me-1"></i> View
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal-{{ $r->stock_out_id }}">
                                    <i class="fa fa-edit me-1"></i> Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No stock-out records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    @foreach($rows as $r)
    <!-- View Modal -->
    <div class="modal fade" id="viewModal-{{ $r->stock_out_id }}" tabindex="-1" aria-hidden="true" x-cloak>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Stock-Out Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="small">
                        <div class="mb-2"><span class="fw-semibold">Item Name:</span> {{ $r->item_name ?? '—' }}</div>
                        <div class="mb-2"><span class="fw-semibold">Service Type:</span> {{ $r->service_type ?? '—' }}</div>
                        <div class="mb-2"><span class="fw-semibold">Aircon Type:</span> {{ $r->airconType->name ?? '—' }}</div>
                        <div class="mb-2"><span class="fw-semibold">Quantity:</span> {{ $r->quantity }}</div>
                        <div class="mb-2"><span class="fw-semibold">Issued Date:</span> {{ $r->issued_date }}</div>
                        <div class="mb-0"><span class="fw-semibold">Status:</span> {{ $r->status }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal-{{ $r->stock_out_id }}" tabindex="-1" aria-hidden="true" x-cloak>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('finance.inventory.stock-out.update', $r->stock_out_id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Stock-Out</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Item Name (optional)</label>
                            <input type="text" name="item_name" value="{{ $r->item_name }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Service Type</label>
                            <select name="item_id" class="form-select item-select" data-target="#airconSelectEdit{{ $r->stock_out_id }}">
                                <option value="">-- Select Item --</option>
                                @foreach($items as $i)
                                    <option value="{{ $i->item_id }}" {{ $r->item_id == $i->item_id ? 'selected' : '' }}>
                                        {{ $i->service_type ?? '—' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Aircon Type (optional)</label>
                            <select name="aircon_type_id" id="airconSelectEdit{{ $r->stock_out_id }}" class="form-select">
                                @if($r->aircon_type_id)
                                    <option value="{{ $r->aircon_type_id }}" selected>{{ $r->airconType->name }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" value="{{ $r->quantity }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Issued Date</label>
                            <input type="date" name="issued_date" value="{{ $r->issued_date }}" class="form-control" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['Needed Request','Requested','Approve','Shipped'] as $status)
                                    <option value="{{ $status }}" {{ $r->status==$status?'selected':'' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <div class="mt-4">{{ $rows->links() }}</div>
</div>

<!-- Add Stock-Out Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('finance.inventory.stock-out.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Stock-Out Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item Name (optional)</label>
                        <input type="text" name="item_name" class="form-control" placeholder="Enter item name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Service Type</label>
                        <select name="item_id" class="form-select item-select" data-target="#airconSelectAdd">
                            <option value="">-- Select Item --</option>
                            @foreach($items as $i)
                                <option value="{{ $i->item_id }}">{{ $i->service_type ?? '—' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Aircon Type (optional)</label>
                        <select name="aircon_type_id" id="airconSelectAdd" class="form-select"></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Issued Date</label>
                        <input type="date" name="issued_date" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Needed Request">Needed Request</option>
                            <option value="Requested">Requested</option>
                            <option value="Approve">Approve</option>
                            <option value="Shipped">Shipped</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
[x-cloak] { display: none !important; }
.btn-ghost { border-radius: 12px; background: #fff; color: #0d6efd; border-color: #cfe2ff; }
.btn-ghost:hover { background: #f1f6ff; color: #0a58ca; box-shadow: 0 6px 18px rgba(13,110,253,.15); }
.inv-hero { border-radius: 24px; background: radial-gradient(700px 200px at 100% 0,rgba(59,130,246,.08),transparent),linear-gradient(180deg,#ffffff,#f8fafc); border: 1px solid #e8ecf1; box-shadow: 0 10px 26px rgba(2,6,23,.06); }
.inv-hero h1 { font-weight: 700; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.modal[x-cloak]').forEach(el => el.removeAttribute('x-cloak'));

    const input = document.getElementById('stockOutSearch');
    const table = document.getElementById('stockOutTable');

    if (input && table) {
        input.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            table.querySelectorAll('tbody tr').forEach(function(tr) {
                const itemName = tr.querySelector('.item-name')?.textContent.trim().toLowerCase() || '';
                const serviceType = tr.children[2]?.textContent.trim().toLowerCase() || '';
                tr.style.display = (itemName.includes(q) || serviceType.includes(q)) ? '' : 'none';
            });
        });
    }

    // Dynamic Aircon Type dropdown
    const AIRCON_TYPES_URL = "{{ route('finance.inventory.stock-out.aircon-types') }}";

    document.querySelectorAll('.item-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const targetId = this.dataset.target;
            const airSelect = document.querySelector(targetId);
            if (!airSelect) return;

            airSelect.innerHTML = '<option value="">-- Select Aircon Type --</option>';
            const itemId = this.value;
            if (!itemId) return;

            fetch(`${AIRCON_TYPES_URL}?item_id=${itemId}`)
                .then(res => res.json())
                .then(data => {
                    airSelect.innerHTML = '<option value="">-- Select Aircon Type --</option>';
                    data.forEach(ac => {
                        const opt = document.createElement('option');
                        opt.value = ac.aircon_type_id;
                        opt.text = ac.aircon_type;
                        airSelect.appendChild(opt);
                    });
                })
                .catch(err => console.error('Error fetching aircon types:', err));
        });
    });
});
</script>
@endpush

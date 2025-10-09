@extends('layouts.finance_app')

@section('content')
<div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-money-check-alt"></i> Billing & Invoicing</h1>
                <p>Manage and process employee payroll</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
        
    <div class="container mt-4">
    <!-- Filter -->
    <div class="mb-3">
        <button class="btn btn-dark" id="filterCompleted">Show Completed Requests</button>
    </div>

    <!-- Service Request Table -->
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th>Request ID</th>
                <th>Customer Name</th>
                <th>Service Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="serviceRequestTable">
            @if($requests->count() > 0)
                @foreach($requests as $request)
                    <tr class="text-center">
                        <td>{{ $request->service_request_id }}</td>
                        <td data-customer-id="{{ $request->customer_id }}">{{ $request->customer->full_name ?? 'N/A' }}</td>
                        <td>{{ $request->service_date->format('M d, Y') }}</td>
                        <td>
                            @php
                                $statusClass = [
                                    'Pending' => 'bg-warning',
                                    'Ongoing' => 'bg-info',
                                    'Completed' => 'bg-success',
                                    'Cancelled' => 'bg-danger'
                                ][$request->order_status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $request->order_status }}</span>
                        </td>
                        <td>
                            <button class="btn btn-info btn-sm view-items" data-id="{{ $request->service_request_id }}">
                                <i class="bi bi-eye"></i> View Items
                            </button>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center">No completed service requests found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
<!-- Items Modal -->
<div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Service Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="itemsContent">
                <p class="text-muted text-center">Select a service request to view items.</p>
            </div>
            <div class="modal-footer">
                <form id="billingForm" method="POST" action="{{ route('finance.billing.store') }}">
                    @csrf
                    <input type="hidden" name="service_request_id" id="billingServiceRequestId">
                    <input type="hidden" name="customer_id" id="billingCustomerId">
                    <input type="hidden" name="total_amount" id="billingTotalAmount">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="generateBillingBtn" class="btn btn-success">
                        <i class="fas fa-file-invoice me-1"></i> Generate Billing
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include Bootstrap & jQuery (if not already in layout) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Optional Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- AJAX Script -->
<script>
$(document).ready(function() {

    // View Service Items
    $(document).on('click', '.view-items', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var id = $button.data('id');
        var $row = $button.closest('tr');
        var customerId = $row.find('td:eq(1)').data('customer-id');
        
        console.log('Viewing items for request ID:', id, 'Customer ID:', customerId);
        
        if (!id) {
            console.error('No service request ID found');
            return;
        }
        
        // Set the service request ID and customer ID in the form
        $('#billingServiceRequestId').val(id);
        $('#billingCustomerId').val(customerId);

        // Show loading state
        $('#itemsContent').html(`
            <div class="text-center my-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading items...</p>
            </div>
        `);
        
        // Show the modal
        var modal = new bootstrap.Modal(document.getElementById('itemsModal'));
        modal.show();
        
        // Make AJAX call to get items
        var baseUrl = '{{ url("/") }}';
        var url = baseUrl + '/finance/billing/view-items/' + id;
        console.log('AJAX URL:', url);
        
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#itemsContent').html(response.html);
                    
                    // Calculate and set the total amount
                    var total = 0;
                    $('.item-total').each(function() {
                        total += parseFloat($(this).data('total')) || 0;
                    });
                    $('#billingTotalAmount').val(total.toFixed(2));
                } else {
                    $('#itemsContent').html(`
                        <div class="alert alert-warning">
                            ${response.message || 'No items found for this request.'}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                var errorMsg = 'An error occurred while loading the items.';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) errorMsg = response.message;
                    if (response.errors) {
                        errorMsg = Object.values(response.errors).join('\n');
                    }
                } catch (e) {
                    console.error('Error parsing error response:', e);
                }
                
                $('#itemsContent').html(`
                    <div class="alert alert-danger">
                        ${errorMsg}
                    </div>
                `);
            }
        });
    });

    // Generate Billing
    $(document).on('click', '#generateBillingBtn', function(e) {
        e.preventDefault();
        
        var $form = $('#billingForm');
        var $btn = $(this);
        var $originalText = $btn.html();
        
        // Get the service request ID and customer ID from the hidden fields
        var serviceRequestId = $('#billingServiceRequestId').val();
        var customerId = $('#billingCustomerId').val();
        var totalAmount = parseFloat($('#billingTotalAmount').val()) || 0;
        
        if (!serviceRequestId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No service request selected.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Show loading state
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        
        // Prepare the data to send
        var formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            service_request_id: serviceRequestId,
            customer_id: customerId,
            total_amount: totalAmount,
            discount: 0,
            tax: 0
        };
        
        console.log('Submitting form data:', formData);
        
        // Submit the form via AJAX
        $.ajax({
            url: '{{ route("finance.billing.store") }}',
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Server response:', response);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Billing generated successfully!',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        // Close the modal and reload the page
                        var modal = bootstrap.Modal.getInstance(document.getElementById('itemsModal'));
                        if (modal) {
                            modal.hide();
                        }
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to generate billing',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                
                var errorMessage = 'An error occurred while generating the billing.';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                    if (response.errors) {
                        errorMessage = Object.values(response.errors).join('\n');
                    }
                } catch (e) {
                    console.error('Error parsing error response:', e);
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                // Re-enable the button
                $btn.prop('disabled', false).html($originalText);
            }
        });
    });
});
</script>
@endsection
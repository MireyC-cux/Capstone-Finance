@extends('layouts.finance_app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Billing Slip</h4>
                    <div>
                        <a href="{{ route('finance.billing.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Billing
                        </a>
                        <button onclick="window.print()" class="btn btn-primary ms-2">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Company Header -->
                    <div class="text-center mb-4">
                        <h2 class="mb-0">3R's Airconditioning Solution</h2>
                        <p class="mb-0">Phone: {{ $billing->customer->contact_number ?? 'N/A' }}</p>
                        <p class="mb-0">Date: {{ \Carbon\Carbon::parse($billing->billing_date)->format('F d, Y') }}</p>
                        <p class="mb-0">Email: {{ $billing->customer->email ?? 'N/A' }}</p>
                        <h4 class="mt-3">BILLING SLIP</h4>
                    </div>

                    <!-- Billing Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Bill To:</h5>
                            <p class="mb-1"><strong>Business Name:</strong> {{ $billing->customer->business_name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Customer Name:</strong> {{ $billing->customer->first_name }} {{ $billing->customer->last_name }}</p>
                            <p class="mb-1"><strong>Address:</strong> {{ $billing->customer->address ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>Contact #:</strong> {{ $billing->customer->contact_number ?? 'N/A' }}</p>
                            <p class="mt-2"><strong>Invoice #:</strong> {{ $billing->invoice->invoice_number ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>Subject:</strong> {{ $billing->serviceRequest->subject ?? 'Service Request' }}</p>
                        </div>
                    </div>

                    <!-- Service Details -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>DATE</th>
                                    <th>SERVICES</th>
                                    <th>AC TYPE</th>
                                    <th>DETAILS</th>
                                    <th>QTY</th>
                                    <th>UNIT PRICE</th>
                                    <th>TAX</th>
                                    <th>TOTAL AMOUNT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $serviceDate = $billing->serviceRequest->created_at ?? now();
                                    $serviceType = $billing->serviceRequest->service_type ?? 'Aircon Service';
                                    $acType = $billing->serviceRequest->ac_type ?? 'N/A';
                                    $details = $billing->serviceRequest->details ?? 'Service Request';
                                    $quantity = $billing->serviceRequest->quantity ?? 1;
                                    $unitPrice = $billing->serviceRequest->unit_price ?? $billing->total_amount;
                                    $tax = $billing->tax;
                                    $total = $billing->total_amount + $tax;
                                @endphp
                                <tr>
                                    <td>{{ $serviceDate->format('m/d/Y') }}</td>
                                    <td>{{ $serviceType }}</td>
                                    <td>{{ $acType }}</td>
                                    <td>{{ $details }}</td>
                                    <td class="text-center">{{ $quantity }}</td>
                                    <td class="text-end">₱{{ number_format($unitPrice, 2) }}</td>
                                    <td class="text-end">₱{{ number_format($tax, 2) }}</td>
                                    <td class="text-end">₱{{ number_format($total, 2) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td colspan="6" class="text-end">Subtotal</td>
                                    <td colspan="2" class="text-end">₱{{ number_format($billing->total_amount, 2) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td colspan="6" class="text-end">Total Amount Due</td>
                                    <td colspan="2" class="text-end">₱{{ number_format($total, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Payment Information -->
                    <div class="mt-5">
                        <div class="row mt-4">
                            <div class="col-12">
                                <p class="mb-2">Please make all checks payable to:</p>
                                <p class="mb-2"><strong>3R's Airconditioning Solution</strong></p>
                                <p class="mb-4">We appreciate your business. Should you have any questions concerning this invoice, please contact us.</p>
                                
                                <div class="mt-5">
                                    <p class="mb-1">Thank you for your business!</p>
                                    <p class="mb-1">Sincerely,</p>
                                    <div class="mt-4" style="margin: 10px 0;">
                                        <img src="{{ asset('storage/esignature.png') }}" alt="Authorized Signature" style="max-width: 200px; height: auto;">
                                    </div>
                                    <p class="mb-0">Adrian N. Genobia</p>
                                    <p class="mb-0">Proprietor</p>
                                    <p class="mb-0">3R's Airconditioning Solution</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            box-shadow: none;
        }
        .no-print, .card-header {
            display: none !important;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
    }
</style>
@endsection

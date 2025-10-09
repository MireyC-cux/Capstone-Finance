<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestItem;
use App\Models\AccountsReceivable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    /**
     * Display the billing slip.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showSlip($id)
    {
        $billing = Billing::with(['customer', 'serviceRequest', 'invoice.accountsReceivable'])
            ->findOrFail($id);
            
        return view('billing_slip', compact('billing'));
    }

    public function index()
    {
        // Fetch all service requests with completed status
        $requests = ServiceRequest::where('order_status', 'Completed')
            ->with(['customer', 'items'])
            ->get()
            ->map(function($request) {
                // Ensure service_date is a Carbon instance
                if (is_string($request->service_date)) {
                    $request->service_date = \Carbon\Carbon::parse($request->service_date);
                }
                return $request;
            });
            
        // Debug output - you can remove this after confirming it works
        \Log::info('Fetched service requests:', $requests->toArray());
        
        return view('billing', compact('requests'));
    }
    
    public function viewItems($id = null)
    {
        try {
            if (!$id) {
                throw new \Exception('No service request ID provided');
            }

            \Log::info('Fetching items for service request ID: ' . $id);
            
            // Use raw SQL query to avoid relationship issues
            $items = \DB::table('service_request_items')
                ->select(
                    'service_request_items.*',
                    'services.service_type',
                    'aircon_types.name as aircon_type_name'
                )
                ->leftJoin('services', 'service_request_items.services_id', '=', 'services.services_id')
                ->leftJoin('aircon_types', 'service_request_items.aircon_type_id', '=', 'aircon_types.aircon_type_id')
                ->where('service_request_items.service_request_id', $id)
                ->get()
                ->map(function ($item) {
                    // Convert to array for easier manipulation
                    $itemArray = (array) $item;
                    
                    // Set default values
                    $itemArray['unit_type'] = $item->aircon_type_name ?? $item->unit_type ?? 'N/A';
                    $itemArray['service_type'] = $item->service_type ?? 'N/A';
                    
                    // Format dates
                    if (!empty($item->start_date)) {
                        $itemArray['start_date'] = \Carbon\Carbon::parse($item->start_date)->toDateString();
                    }
                    if (!empty($item->end_date)) {
                        $itemArray['end_date'] = \Carbon\Carbon::parse($item->end_date)->toDateString();
                    }
                    
                    // Ensure numeric fields have values
                    $itemArray['quantity'] = $item->quantity ?? 0;
                    $itemArray['unit_price'] = $item->unit_price ?? 0;
                    $itemArray['discount'] = $item->discount ?? 0;
                    $itemArray['tax'] = $item->tax ?? 0;
                    $itemArray['line_total'] = $item->line_total ?? 0;
                    
                    return (object) $itemArray;
                });
                
            \Log::info('Items found: ' . $items->count());
            
            if ($items->isEmpty()) {
                return response()->json([
                    'html' => '<div class="alert alert-warning">No items found for this request.</div>',
                    'success' => true
                ]);
            }
            
            $html = view('partials.items_table', ['items' => $items])->render();
            
            return response()->json([
                'html' => $html,
                'success' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in viewItems: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'html' => '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store a newly created billing in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'service_request_id' => 'required|exists:service_requests,service_request_id',
            'customer_id' => 'required|exists:customers,customer_id',
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        try {
            // Start database transaction
            \DB::beginTransaction();

            // Create the billing record
            $billing = Billing::create([
                'service_request_id' => $validated['service_request_id'],
                'customer_id' => $validated['customer_id'],
                'billing_date' => now()->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'total_amount' => $validated['total_amount'],
                'discount' => $validated['discount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'status' => 'Billed' // Matches ENUM in database
            ]);

            // Create an accounts receivable entry
            $ar = AccountsReceivable::create([
                'customer_id' => $validated['customer_id'],
                'service_request_id' => $validated['service_request_id'],
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'total_amount' => $validated['total_amount'],
                'amount_paid' => 0,
                'status' => 'Unpaid',
                'payment_terms' => 'Net 15',
                'balance' => $validated['total_amount']
            ]);

            // Create an invoice for the billing
            $invoice = new Invoice([
                'billing_id' => $billing->billing_id,
                'ar_id' => $ar->ar_id,
                'invoice_number' => $ar->invoice_number, // Use the same invoice number as AR
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'amount' => $validated['total_amount'],
                'status' => 'Unpaid' // Matches ENUM in database
            ]);
            
            // Save the invoice
            $billing->invoice()->save($invoice);

            // Mark service request items as billed
            ServiceRequestItem::where('service_request_id', $validated['service_request_id'])
                ->update(['billed' => true]);

            // Delete the service request and its items after successful billing
            $serviceRequest = ServiceRequest::find($validated['service_request_id']);
            if ($serviceRequest) {
                // First delete the items to maintain referential integrity
                $serviceRequest->items()->delete();
                // Then delete the service request
                $serviceRequest->delete();
            }

            // Commit the transaction
            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Billing generated successfully!',
                'redirect' => route('finance.billing.slip', $billing->billing_id),
                'print_url' => route('finance.billing.slip', ['id' => $billing->billing_id, 'print' => '1'])
            ]);

        } catch (\Exception $e) {
            // Rollback the transaction on error
            \DB::rollBack();
            \Log::error('Error generating billing: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Log the input data for debugging
            \Log::error('Input data: ', $request->all());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate billing: ' . $e->getMessage(),
                'error_details' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
}

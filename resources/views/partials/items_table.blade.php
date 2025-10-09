<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Date of Service</th>
                <th>Unit Type</th>
                <th>Details</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Tax</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>
                        @php
                            $dateDisplay = 'N/A';
                            try {
                                if (!empty($item->start_date)) {
                                    $startDate = \Carbon\Carbon::parse($item->start_date);
                                    $dateDisplay = $startDate->format('M d, Y');
                                    
                                    if (!empty($item->end_date) && $item->end_date !== $item->start_date) {
                                        $endDate = \Carbon\Carbon::parse($item->end_date);
                                        $dateDisplay .= ' - ' . $endDate->format('M d, Y');
                                    }
                                } elseif (!empty($item->service_date)) {
                                    $serviceDate = \Carbon\Carbon::parse($item->service_date);
                                    $dateDisplay = $serviceDate->format('M d, Y');
                                }
                                
                                echo e($dateDisplay);
                                
                                // Display time if available
                                if (!empty($item->start_time) && !empty($item->end_time)) {
                                    try {
                                        $startTime = \Carbon\Carbon::parse($item->start_time);
                                        $endTime = \Carbon\Carbon::parse($item->end_time);
                                        echo '<div class="text-muted small">' . 
                                             $startTime->format('h:i A') . ' - ' . 
                                             $endTime->format('h:i A') . 
                                             '</div>';
                                    } catch (\Exception $e) {
                                        // Silently fail for time parsing
                                    }
                                }
                            } catch (\Exception $e) {
                                echo 'N/A';
                            }
                        @endphp
                    </td>
                    <td>
                        {{ $item->unit_type ?? 'N/A' }}
                    </td>
                    <td>
                        <div class="fw-bold">
                            {{ $item->service_type ?? 'N/A' }}
                        </div>
                        @if(!empty($item->service_notes))
                            <div class="text-muted small">{{ $item->service_notes }}</div>
                        @endif
                    </td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end">₱{{ number_format($item->discount, 2) }}</td>
                    <td class="text-end">₱{{ number_format($item->tax, 2) }}</td>
                    <td class="text-end fw-bold">₱{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No items found for this request.</td>
                </tr>
            @endforelse
        </tbody>
        @if($items->isNotEmpty())
            <tfoot>
                <tr class="table-active">
                    <td colspan="5"></td>
                    <td class="text-end fw-bold">Subtotal:</td>
                    <td class="text-end fw-bold">₱{{ number_format($items->sum('line_total'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

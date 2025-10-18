
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>3R's AirConditioningSolution</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#fff; }
    .doc { max-width: 900px; margin: 24px auto; color:#111; }
    .brand { font-family: Georgia, 'Times New Roman', Times, serif; font-style: italic; color:#e76f51; font-size: 28px; }
    .muted { color:#4b5563; }
    .table-slim td, .table-slim th { padding:.5rem .6rem; }
    .table-bordered, .table-bordered th, .table-bordered td { border:1px solid #333 !important; }
    .underline { text-decoration: underline; }
    .note { color:#dc2626; font-weight:600; }
    @media print {
      .no-print { display:none !important; }
      body { background:#fff; }
    }
  </style>
  <script>
    function doPrint(){ window.print(); }
  </script>
  </head>
  <body>
  <div class="doc">
    @if(empty($forPdf))
    <div class="d-flex align-items-center justify-content-between no-print mb-3">
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="{{ url()->previous() }}">Back</a>
        <a class="btn btn-outline-primary btn-sm" target="_blank" href="{{ route('finance.billing.slip.pdf', $billing->billing_id) }}">Download PDF</a>
        <button onclick="doPrint()" class="btn btn-primary btn-sm">Print</button>
      </div>
    </div>
    @endif

    <div class="mb-2">
      <div class="brand">3R's AirConditioningSolution</div>
    </div>

    <div class="d-flex justify-content-between mb-2">
      <div class="muted">Contact number: {{ $billing->serviceRequest->customer->contact_info ?? '—' }}</div>
      <div>{{ \Carbon\Carbon::parse($billing->billing_date)->format('m/d/Y') }}</div>
    </div>

    <div class="mb-2">
      <div>genobiaadrian@gmail.com</div>
      <div>
        <span class="underline">{{ $billing->serviceRequest->customer->business_name ?? strtoupper($billing->serviceRequest->customer->full_name ?? '—') }}</span>
        <span class="muted">(Customer’s business name)</span>
      </div>
      <div>
        ATTENTION: <span class="underline">{{ strtoupper($billing->serviceRequest->customer->full_name ?? '—') }}</span><span class="muted"> (customer name)</span>
      </div>
      <div>Subject: Aircon Requirements</div>
      <div>Dear Sir / Madam: <span class="note">please see attached billing statement</span></div>
    </div>

    @php
      $rows = [];
      $grand = 0;
    @endphp
    <div class="table-responsive mb-2">
      <table class="table table-bordered table-slim align-middle">
        <thead class="text-center">
          <tr>
            <th style="width:18%">DATE SERVICES</th>
            <th style="width:16%">AC TYPE</th>
            <th>DETAILS</th>
            <th style="width:8%">QTY</th>
            <th style="width:14%">UNIT PRICE</th>
            <th style="width:16%">TOTAL AMOUNT</th>
          </tr>
        </thead>
        <tbody>
        @foreach(($items ?? collect()) as $it)
          @php
            $dateRange = ($it->start_date ? \Carbon\Carbon::parse($it->start_date)->format('m/d/y') : '') .
                        ($it->end_date ? ' - '.\Carbon\Carbon::parse($it->end_date)->format('m/d/y') : '');
            $qty = (int)($it->quantity ?? 1);
            $unit = (float)($it->unit_price ?? 0);
            $extrasTotal = ($it->extras ?? collect())->sum(fn($e)=> (int)$e->qty * (float)$e->price);
            $line = ($qty * $unit) + $extrasTotal;
            $grand += $line;
          @endphp
          <tr>
            <td>{{ $dateRange }}</td>
            <td class="text-uppercase">{{ $it->airconType->name ?? $it->service_type ?? '—' }}</td>
            <td class="text-uppercase">{{ $it->service->service_type ?? 'GENERAL CLEANING' }}</td>
            <td class="text-end">{{ number_format($qty) }}</td>
            <td class="text-end">{{ number_format($unit, 2) }}</td>
            <td class="text-end">{{ number_format($line, 2) }}</td>
          </tr>
        @endforeach
        <tr>
          <td colspan="4"></td>
          <td class="text-end fw-semibold">TOTAL AMOUNT</td>
          <td class="text-end fw-semibold">{{ number_format($grand, 2) }}</td>
        </tr>
        </tbody>
      </table>
    </div>

    <div class="mb-4">
      Payable to: <span class="text-danger fw-semibold">3RS Airconditioning Solution</span>
    </div>

    <p class="mb-5">We hope that you find our offer in order and we look forward to be of service to you.</p>

    <div class="row mt-5">
      <div class="col-6">
        <div class="mb-2" style="height:60px">
          <img src="{{ $signatureDataUrl ?? asset('storage/esignature.png') }}" alt="E-signature" style="height:60px; object-fit:contain;" />
        </div>
        <div class="fw-semibold">Adriah N. Genobia</div>
        <div class="muted">Proprietor</div>
        <div class="muted">0927-137-4570</div>
      </div>
      <div class="col-6">
        <div class="mb-4" style="height:60px"></div>
        <div class="muted">Conformed by: __________________________</div>
        <div class="muted">Name & Date</div>
      </div>
    </div>
  </div>
  </body>
  </html>

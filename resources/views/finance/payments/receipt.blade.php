<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Official Receipt</title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #0f172a; }
    .wrap { width: 100%; }
    .muted { color: #6b7280; font-size: 11px; }
    .box { border: 1px solid #e5e7eb; padding: 10px; border-radius: 4px; }
    .right { text-align: right; }
    .letterhead { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 2px solid #111827; padding-bottom: 8px; }
    .brand { display: flex; align-items: center; gap: 10px; }
    .brand img { height: 42px; width: auto; }
    .brand-name { font-size: 18px; font-weight: 800; letter-spacing: 0.5px; }
    .brand-meta { font-size: 12px; color: #374151; }
    .doc-meta { text-align: right; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    thead th { background: #f3f4f6; color: #111827; font-weight: 700; border: 1px solid #e5e7eb; padding: 8px; }
    tbody td { border: 1px solid #e5e7eb; padding: 8px; }
    .totals td { font-weight: 700; }
    .title { font-size: 14px; font-weight: 700; margin: 6px 0 2px; text-transform: uppercase; letter-spacing: .5px; }
  </style>
  </head>
<body>
  <div class="wrap">
    <div class="letterhead">
      <div class="brand">
        <img src="{{ asset('images/3Rs_logo.png') }}" alt="3Rs Logo">
        <div>
          <div class="brand-meta">Official Receipt</div>
        </div>
      </div>
      <div class="doc-meta">
        <div><strong>Receipt #:</strong> {{ $payment->payment_id }}</div>
        <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</div>
        <div><strong>AR #:</strong> {{ $ar->ar_id }}</div>
        @if($invoice)
          <div><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
        @endif
      </div>
    </div>

    <div class="box" style="margin-bottom:10px;">
      <div class="title">Customer</div>
      <div>{{ ($customer->business_name ?? '') ?: ($customer->full_name ?? 'Unknown Customer') }}</div>
      @if(!empty($customer?->address))
        <div class="muted">{{ $customer->address }}</div>
      @endif
    </div>

    <div class="title">Payment Details</div>
    <table>
      <thead>
        <tr>
          <th>Description</th>
          <th class="right">Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Payment ({{ $payment->payment_method }})@if(!empty($payment->reference_number)) — Ref # {{ $payment->reference_number }}@endif</td>
          <td class="right">₱ {{ number_format((float)$payment->amount, 2) }}</td>
        </tr>
      </tbody>
    </table>

    <div class="title">Summary</div>
    <table class="totals">
      <tbody>
        <tr>
          <td>Balance Before</td>
          <td class="right">₱ {{ number_format((float)$balanceBefore, 2) }}</td>
        </tr>
        <tr>
          <td>Payment</td>
          <td class="right">₱ {{ number_format((float)$payment->amount, 2) }}</td>
        </tr>
        <tr>
          <td>Balance After</td>
          <td class="right">₱ {{ number_format((float)$balanceAfter, 2) }}</td>
        </tr>
      </tbody>
    </table>

    <div class="muted" style="margin-top:8px;">Thank you for your payment.</div>
  </div>
</body>
</html>

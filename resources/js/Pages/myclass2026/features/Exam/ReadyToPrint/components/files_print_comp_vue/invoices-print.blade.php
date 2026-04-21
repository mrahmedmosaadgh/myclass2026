{{-- resources/views/invoices/print.blade.php --}}
{{-- Returned by Laravel when PrintButton uses the `url` prop --}}

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Invoice #{{ $invoice->number }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', sans-serif; font-size: 13px; color: #111; }
    .invoice { max-width: 720px; margin: 0 auto; padding: 40px; }
    .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
    .logo { font-size: 22px; font-weight: 700; }
    .badge { background: #f0fdf4; color: #16a34a; padding: 4px 10px; border-radius: 4px;
             font-size: 11px; font-weight: 600; border: 1px solid #bbf7d0; }
    .meta { display: flex; gap: 40px; margin-bottom: 32px; }
    .meta div { display: flex; flex-direction: column; gap: 4px; }
    .meta label { font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.05em; }
    .meta span  { font-weight: 600; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    thead th { background: #f9fafb; padding: 10px 12px; text-align: left;
               font-size: 11px; text-transform: uppercase; color: #666; border-bottom: 1px solid #e5e7eb; }
    tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; }
    .totals { margin-left: auto; width: 260px; }
    .totals tr td:first-child { color: #666; }
    .totals tr td:last-child  { text-align: right; font-weight: 500; }
    .totals tr.grand td { font-size: 15px; font-weight: 700; border-top: 2px solid #111; padding-top: 10px; }
    .footer { margin-top: 48px; font-size: 11px; color: #999; text-align: center; }
    @media print {
      .invoice { padding: 0; }
      body { color: #000; }
    }
  </style>
</head>
<body>
  <div class="invoice">
    <div class="header">
      <div>
        <div class="logo">{{ config('app.name') }}</div>
        <p style="color:#666;margin-top:4px;">{{ config('app.url') }}</p>
      </div>
      <div style="text-align:right">
        <div style="font-size:20px;font-weight:700">Invoice #{{ $invoice->number }}</div>
        <div style="margin-top:6px"><span class="badge">{{ ucfirst($invoice->status) }}</span></div>
        <div style="margin-top:8px;color:#666">{{ $invoice->date->format('d M Y') }}</div>
      </div>
    </div>

    <div class="meta">
      <div>
        <label>From</label>
        <span>{{ config('app.name') }}</span>
        <span style="color:#666;font-weight:400">billing@yourapp.com</span>
      </div>
      <div>
        <label>Bill to</label>
        <span>{{ $invoice->client->name }}</span>
        <span style="color:#666;font-weight:400">{{ $invoice->client->email }}</span>
      </div>
      <div>
        <label>Due date</label>
        <span>{{ $invoice->due_date->format('d M Y') }}</span>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Description</th>
          <th style="width:80px;text-align:center">Qty</th>
          <th style="width:110px;text-align:right">Unit price</th>
          <th style="width:110px;text-align:right">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($invoice->items as $item)
        <tr>
          <td>{{ $item->description }}</td>
          <td style="text-align:center">{{ $item->quantity }}</td>
          <td style="text-align:right">{{ number_format($item->unit_price, 2) }}</td>
          <td style="text-align:right">{{ number_format($item->total, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <table class="totals">
      <tr><td>Subtotal</td><td>{{ number_format($invoice->subtotal, 2) }}</td></tr>
      <tr><td>Tax ({{ $invoice->tax_rate }}%)</td><td>{{ number_format($invoice->tax, 2) }}</td></tr>
      <tr class="grand"><td>Total</td><td>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td></tr>
    </table>

    @if ($invoice->notes)
    <div style="margin-top:24px;padding:16px;background:#f9fafb;border-radius:6px;color:#555">
      <strong style="display:block;margin-bottom:4px">Notes</strong>
      {{ $invoice->notes }}
    </div>
    @endif

    <div class="footer">Thank you for your business.</div>
  </div>
</body>
</html>

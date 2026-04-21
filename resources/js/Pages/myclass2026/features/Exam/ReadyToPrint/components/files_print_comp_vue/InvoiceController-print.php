<?php

// ─── Laravel Controller ───────────────────────────────────────────────────────
// app/Http/Controllers/InvoiceController.php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    // Normal show page
    public function show(Invoice $invoice)
    {
        return view('invoices.show', compact('invoice'));
    }

    // Returns HTML for the Vue PrintButton `url` prop
    public function print(Invoice $invoice)
    {
        $invoice->load('client', 'items');

        return view('invoices.print', compact('invoice'));
    }
}


// ─── Routes (web.php) ─────────────────────────────────────────────────────────
// Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])
//     ->name('invoices.print')
//     ->middleware('auth');


// ─── Vue usage examples ───────────────────────────────────────────────────────
/*

// 1. Print from a Laravel URL (fetches the Blade view, prints it)
<PrintButton
  :url="`/invoices/${invoice.id}/print`"
  title="Invoice #INV-001"
  orientation="portrait"
/>

// 2. Print a specific element in the current page by ref
<div ref="reportRef">...</div>
<PrintButton :target="reportRef" label="Print Report" />

// 3. Print the whole current page
<PrintButton :url="null" :target="null" label="Print Page" />

// 4. Landscape, ghost style, with events
<PrintButton
  :url="`/reports/${id}/print`"
  title="Monthly Report"
  orientation="landscape"
  page-size="A3"
  variant="ghost"
  size="sm"
  @before-print="trackAnalytics"
  @after-print="showToast('Sent to printer!')"
  @error="handlePrintError"
/>

*/

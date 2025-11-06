<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndentPrintController;
use App\Http\Controllers\PackingListPrintController;
use App\Http\Controllers\InvoicePrintController;
use App\Http\Controllers\CertificatePrintController;

Route::get('/', function () {
    return view('welcome');
});

// Indent printable view/PDF
Route::get('/indents/{indent}/print', [IndentPrintController::class, 'show'])
    ->name('indents.print');

// Packing List printable view
Route::get('/packing-lists/{packingList}/print', [PackingListPrintController::class, 'show'])
    ->name('packing-lists.print');

// Packing List PDF generation (stores to public disk and optionally downloads)
Route::get('/packing-lists/{packingList}/generate-pdf', [PackingListPrintController::class, 'generate'])
    ->name('packing-lists.generate');

// Invoice printable view/PDF
Route::get('/invoices/{invoice}/print', [InvoicePrintController::class, 'show'])->name('invoices.print');
Route::get('/invoices/{invoice}/pdf', [InvoicePrintController::class, 'pdf'])->name('invoices.pdf');

// Generate or update invoice from a packing list, then redirect to invoice PDF
Route::post('/packing-lists/{packingList}/generate-invoice', [InvoicePrintController::class, 'generateForPackingList'])
    ->name('invoices.generate-from-packing-list');

// Certificate of Origin printable view/PDF
Route::get('/certificates/{certificate}/print', [CertificatePrintController::class, 'show'])->name('certificates.print');
Route::get('/certificates/{certificate}/pdf', [CertificatePrintController::class, 'pdf'])->name('certificates.pdf');


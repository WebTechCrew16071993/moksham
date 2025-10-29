<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndentPrintController;

Route::get('/', function () {
    return view('welcome');
});

// Indent printable view/PDF
Route::get('/indents/{indent}/print', [IndentPrintController::class, 'show'])
    ->name('indents.print');

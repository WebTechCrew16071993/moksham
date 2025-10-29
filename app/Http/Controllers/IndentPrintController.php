<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Indent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class IndentPrintController extends Controller
{
    public function show(Request $request, Indent $indent)
    {
        $setting = CompanySetting::query()->first();

        $view = view('indents.print', [
            'indent' => $indent,
            'setting' => $setting,
        ])->render();

        // If DOMPDF is available, return a PDF; otherwise, return HTML
        try {
            $pdf = Pdf::loadHTML($view)->setPaper('a4');
            if ($request->boolean('download')) {
                return $pdf->download('indent-'.$indent->indent_no.'.pdf');
            }
            return $pdf->stream('indent-'.$indent->indent_no.'.pdf');
        } catch (\Throwable $e) {
            // Fallback to raw HTML
            return response($view);
        }
    }
}

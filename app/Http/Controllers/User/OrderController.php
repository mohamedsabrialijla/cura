<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->locales = Language::all();
        view()->share([
            'locales' => $this->locales,
        ]);
    }

    public function show($id)
    {
        $invoice = Payment::with('user', 'ad')->findOrFail($id);

        return view('pdf.invoice', compact('invoice'));
    }

    public function invoices(Request $request)
    {
        $user = auth('sanctum')->user();
        if (! $user) {
            return mainResponse(false, __('common.error'), null, 401, 'items', '');
        }

        $query = Payment::with('ad', 'user')->where('user_id', $user->id);

        if ($request->filled('track_id')) {
            $item = (clone $query)->where('track_id', $request->track_id)->first();
            if (! $item) {
                $item = Payment::with('ad', 'user')
                    ->where('track_id', $request->track_id)
                    ->first();
            }

            return mainResponse(true, __('common.return'), $item, 200, 'items', '');
        }

        $items = $query->orderByDesc('id')->get();

        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }

    public function downloadPdf($id)
    {
        $invoice = Payment::with('user', 'ad')->findOrFail($id);
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))->setPaper('a4');

        return $pdf->download('keepersport-invoice-'.$invoice->track_id.'.pdf');
    }
}

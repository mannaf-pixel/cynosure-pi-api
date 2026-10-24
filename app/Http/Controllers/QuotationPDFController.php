<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuotationPDFController extends Controller
{
    private function getBrandData(string $brand): array
    {
        $brands = [
            'assre_plasto' => [
                'name'        => 'ASSRE PLASTO WORLD INTERNATIONAL PVT. LTD.',
                'brand_label' => 'Assre Plasto — Quality Comfort Luxury',
                'address'     => 'Baliandaga More, NH-12, Kaliachak, Malda, 732201',
                'gstin'       => '19AASCA2816R1ZU',
                'phone'       => '7479005212',
                'email'       => 'mannaf@cynosurewindow.com',
                'account_name'=> 'Assre Plasto World International Pvt Ltd',
                'bank'        => 'Canara Bank',
                'account'     => '125004130546',
                'ifsc'        => 'CNRB0019550',
                'branch'      => 'Malda',
                'logo'        => 'assre_plasto.png',
                'color'       => '#1E6FD9',
            ],
            'cynosure' => [
                'name'        => 'ASSRE PLASTO WORLD INTERNATIONAL PVT. LTD.',
                'brand_label' => 'Cynosure Profile — German Standard',
                'address'     => 'Baliandaga More, NH-12, Kaliachak, Malda, 732201',
                'gstin'       => '19AASCA2816R1ZU',
                'phone'       => '7479005212',
                'email'       => 'mannaf@cynosurewindow.com',
                'account_name'=> 'Assre Plasto World International Pvt Ltd',
                'bank'        => 'Canara Bank',
                'account'     => '125004130546',
                'ifsc'        => 'CNRB0019550',
                'branch'      => 'Malda',
                'logo'        => 'cynosure.png',
                'color'       => '#1E6FD9',
            ],
            'sinewy' => [
                'name'        => 'ASSRE PLASTO WORLD INTERNATIONAL PVT. LTD.',
                'brand_label' => 'Sinewy uPVC Profiles — German Standard',
                'address'     => 'Baliandaga More, NH-12, Kaliachak, Malda, 732201',
                'gstin'       => '19AASCA2816R1ZU',
                'phone'       => '7479005212',
                'email'       => 'mannaf@cynosurewindow.com',
                'account_name'=> 'Assre Plasto World International Pvt Ltd',
                'bank'        => 'Canara Bank',
                'account'     => '125004130546',
                'ifsc'        => 'CNRB0019550',
                'branch'      => 'Malda',
                'logo'        => 'sinewy.png',
                'color'       => '#1E6FD9',
            ],
        ];
        return $brands[$brand] ?? $brands['cynosure'];
    }

    public function generate($id, Request $request)
    {
        $quotation  = Quotation::with('customer', 'items', 'creator')->findOrFail($id);
        $brand      = $quotation->brand ?? 'cynosure';
        $brandData  = $this->getBrandData($brand);
        $logoPath   = public_path('logos/' . $brandData['logo']);
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $isInternal = (bool) $request->query('internal', false);
        $filename   = $isInternal
            ? 'QT-' . $quotation->quotation_number . '-internal.pdf'
            : 'QT-' . $quotation->quotation_number . '.pdf';

        $data = [
            'quotation'   => $quotation,
            'is_internal' => $isInternal,
            'company'     => $brandData,
            'logo_base64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.quotation', $data)->setPaper('a4', 'portrait');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Headers', '*')
            ->header('Access-Control-Expose-Headers', 'Content-Disposition');
    }
}

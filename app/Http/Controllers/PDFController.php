<?php

namespace App\Http\Controllers;

use App\Models\PiMaster;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PDFController extends Controller
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
                'pricing_mode'=> 'per_meter',
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
                'pricing_mode'=> 'per_meter',
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
                'pricing_mode'=> 'per_meter',
            ],
            'plastrong' => [
                'name'        => 'DIAMOND WINDOOR INDUSTRIES',
                'brand_label' => 'Plastrong uPVC Profile',
                'address'     => 'MG Road, Aurangabad, Bihar',
                'gstin'       => '10AYLPH6964M2Z0',
                'phone'       => '7301230001 / 9973859767',
                'email'       => 'diamondupvcindustries022@gmail.com',
                'account_name'=> 'Diamond Windoor Industries',
                'bank'        => 'Central Bank of India',
                'account'     => '5293201615',
                'ifsc'        => 'CBIN0283162',
                'branch'      => 'Aurangabad, Bihar',
                'logo'        => 'plastrong.png',
                'color'       => '#8B0000',
                'pricing_mode'=> 'per_kg',
            ],
        ];

        return $brands[$brand] ?? $brands['cynosure'];
    }

    public function generatePI($id, Request $request)
    {
        $pi = PiMaster::with('customer', 'items', 'creator')->findOrFail($id);

        $brand       = $pi->brand ?? 'cynosure';
        $brandData   = $this->getBrandData($brand);
        $logoPath    = public_path('logos/' . $brandData['logo']);
        $logoBase64  = '';

        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $isPlastrong = $brandData['pricing_mode'] === 'per_kg';

        $data = [
            'pi'          => $pi,
            'show_weight' => (bool) $request->query('show_weight', false),
            'is_plastrong'=> $isPlastrong,
            'company'     => $brandData,
            'logo_base64' => $logoBase64,
        ];

        $pdf = Pdf::loadView('pdf.pi', $data)->setPaper('a4', 'portrait');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="PI-'.$pi->pi_number.'.pdf"')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Headers', '*')
            ->header('Access-Control-Expose-Headers', 'Content-Disposition');
    }
}

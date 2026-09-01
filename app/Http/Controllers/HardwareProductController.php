<?php

namespace App\Http\Controllers;

use App\Models\HardwareProduct;
use Illuminate\Http\Request;

class HardwareProductController extends Controller
{
    private function getCompanyId()
    {
        $user = auth('api')->user();
        if ($user->is_super_admin) {
            return request()->header('X-Company-Id') ?? request()->query('company_id');
        }
        return $user->company_id;
    }

    public function index()
    {
        $companyId = $this->getCompanyId();
        $hardware = HardwareProduct::where('company_id', $companyId)
            ->orderBy('code')
            ->get();
        return response()->json(['success' => true, 'data' => $hardware]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'unit' => 'required|in:piece,meter,set,kg',
            'rate' => 'required|numeric|min:0',
        ]);

        $companyId = $this->getCompanyId();
        $lastCode = HardwareProduct::where('company_id', $companyId)
            ->orderBy('id', 'desc')->first();
        $nextNum = $lastCode ? (intval(substr($lastCode->code, 3)) + 1) : 1;
        $code = 'HW-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $hardware = HardwareProduct::create([
            'company_id' => $companyId,
            'code'       => $request->code ?? $code,
            'name'       => $request->name,
            'unit'       => $request->unit,
            'rate'       => $request->rate,
            'is_active'  => true,
        ]);

        return response()->json(['success' => true, 'data' => $hardware], 201);
    }

    public function update(Request $request, $id)
    {
        $hardware = HardwareProduct::findOrFail($id);
        $request->validate([
            'name' => 'required|string',
            'unit' => 'required|in:piece,meter,set,kg',
            'rate' => 'required|numeric|min:0',
        ]);

        $hardware->update([
            'name'      => $request->name,
            'unit'      => $request->unit,
            'rate'      => $request->rate,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json(['success' => true, 'data' => $hardware]);
    }

    public function destroy($id)
    {
        $hardware = HardwareProduct::findOrFail($id);
        $hardware->delete();
        return response()->json(['success' => true, 'message' => 'Deleted']);
    }
}

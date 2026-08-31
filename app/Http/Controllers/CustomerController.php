<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    private function getCompanyId()
    {
        // 1. Header se lo pehle
        $header = request()->header('X-Company-Id');
        if ($header) {
            return (int) $header;
        }

        // 2. Auth user se lo
        $user = auth('api')->user();
        if ($user && $user->company_id) {
            return (int) $user->company_id;
        }

        return null;
    }

    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $query = Customer::query();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', '%'.$search.'%')
                  ->orWhere('company_name', 'like', '%'.$search.'%')
                  ->orWhere('mobile', 'like', '%'.$search.'%');
            });
        }
        return response()->json(['success' => true, 'data' => $query->orderBy('company_name')->get()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'company_name'  => 'required|string',
            'mobile'        => 'nullable|digits:10',
            'email'         => 'nullable|email',
        ]);

        $companyId = $this->getCompanyId();
        Log::info('Customer store - company_id: ' . $companyId . ' | header: ' . request()->header('X-Company-Id') . ' | user_id: ' . auth('api')->id());

        $customer = Customer::create([
            'company_id'       => $companyId,
            'customer_name'    => $request->customer_name,
            'company_name'     => $request->company_name,
            'gstin'            => $request->gstin ?: null,
            'contact_person'   => $request->contact_person ?: null,
            'mobile'           => $request->mobile ?: null,
            'email'            => $request->email ?: null,
            'billing_address'  => $request->billing_address ?: null,
            'shipping_address' => $request->shipping_address ?: null,
            'state'            => $request->state ?: null,
            'city'             => $request->city ?: null,
            'created_by'       => auth('api')->id(),
        ]);

        return response()->json(['success' => true, 'data' => $customer], 201);
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json(['success' => true, 'data' => $customer]);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $request->validate([
            'customer_name' => 'required|string',
            'company_name'  => 'required|string',
            'mobile'        => 'nullable|digits:10',
            'email'         => 'nullable|email',
        ]);
        $customer->update([
            'customer_name'    => $request->customer_name,
            'company_name'     => $request->company_name,
            'gstin'            => $request->gstin ?: null,
            'contact_person'   => $request->contact_person ?: null,
            'mobile'           => $request->mobile ?: null,
            'email'            => $request->email ?: null,
            'billing_address'  => $request->billing_address ?: null,
            'shipping_address' => $request->shipping_address ?: null,
            'state'            => $request->state ?: null,
            'city'             => $request->city ?: null,
        ]);
        return response()->json(['success' => true, 'data' => $customer]);
    }
}
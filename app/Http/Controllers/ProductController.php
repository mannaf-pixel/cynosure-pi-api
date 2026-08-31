<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private function getCompanyId()
    {
        $user = auth('api')->user();
        if ($user->is_super_admin) {
            return request()->header('X-Company-Id') ?? request()->query('company_id');
        }
        return $user->company_id;
    }

    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $query = Product::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('product_code', 'like', '%'.$request->search.'%')
                  ->orWhere('product_name', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->active);
        }

        $products = $query->orderBy('product_code')->get();
        return response()->json(['success' => true, 'data' => $products]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_code'   => 'required|max:20',
            'product_name'   => 'required|string',
            'white_rate'     => 'required|numeric|min:0',
            'color_rate'     => 'required|numeric|min:0',
            'profile_length' => 'required|numeric|min:0',
            'bundle_qty'     => 'required|integer|min:1',
        ]);

        $companyId = $this->getCompanyId();

        $product = Product::create([
            'company_id'       => $companyId,
            'product_code'     => strtoupper($request->product_code),
            'product_name'     => $request->product_name,
            'category'         => $request->category,
            'white_rate'       => $request->white_rate,
            'color_rate'       => $request->color_rate,
            'unit'             => $request->unit ?? 'meter',
            'profile_length'   => $request->profile_length,
            'bundle_qty'       => $request->bundle_qty,
            'weight_per_meter'  => $request->weight_per_meter ?? 0,
            'sinewy_white_rate' => $request->sinewy_white_rate ?? 0,
            'sinewy_color_rate' => $request->sinewy_color_rate ?? 0,
            'assre_white_rate'  => $request->assre_white_rate ?? 0,
            'assre_color_rate'  => $request->assre_color_rate ?? 0,
            'mrp'              => $request->mrp ?? 0,
            'discount_pct'     => $request->discount_pct ?? 0,
            'is_active'        => true,
        ]);

        return response()->json(['success' => true, 'data' => $product], 201);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json(['success' => true, 'data' => $product]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'product_code'   => 'required|max:20',
            'product_name'   => 'required|string',
            'white_rate'     => 'required|numeric|min:0',
            'color_rate'     => 'required|numeric|min:0',
            'profile_length' => 'required|numeric|min:0',
            'bundle_qty'     => 'required|integer|min:1',
        ]);

        if ($product->white_rate != $request->white_rate) {
            \App\Models\PricingHistory::create([
                'product_id'   => $product->id,
                'profile_type' => 'white',
                'old_rate'     => $product->white_rate,
                'new_rate'     => $request->white_rate,
                'changed_by'   => auth('api')->id(),
            ]);
        }

        if ($product->color_rate != $request->color_rate) {
            \App\Models\PricingHistory::create([
                'product_id'   => $product->id,
                'profile_type' => 'color',
                'old_rate'     => $product->color_rate,
                'new_rate'     => $request->color_rate,
                'changed_by'   => auth('api')->id(),
            ]);
        }

        $product->update([
            'product_code'     => strtoupper($request->product_code),
            'product_name'     => $request->product_name,
            'category'         => $request->category,
            'white_rate'       => $request->white_rate,
            'color_rate'       => $request->color_rate,
            'profile_length'   => $request->profile_length,
            'bundle_qty'       => $request->bundle_qty,
            'weight_per_meter'  => $request->weight_per_meter ?? 0,
            'sinewy_white_rate' => $request->sinewy_white_rate ?? 0,
            'sinewy_color_rate' => $request->sinewy_color_rate ?? 0,
            'assre_white_rate'  => $request->assre_white_rate ?? 0,
            'assre_color_rate'  => $request->assre_color_rate ?? 0,
            'mrp'              => $request->mrp ?? 0,
            'discount_pct'     => $request->discount_pct ?? 0,
            'is_active'        => $request->is_active ?? true,
        ]);

        return response()->json(['success' => true, 'data' => $product]);
    }

    public function toggle($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
        return response()->json(['success' => true, 'data' => $product]);
    }
}
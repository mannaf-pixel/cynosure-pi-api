<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\Product;
use App\Models\HardwareProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
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
        $query = Stock::with('product', 'hardwareProduct')
            ->where('company_id', $companyId)
            ->orderBy('item_type')
            ->orderBy('item_name');

        if ($request->item_type) $query->where('item_type', $request->item_type);
        if ($request->search)    $query->where(function($q) use ($request) {
            $q->where('item_name', 'like', "%{$request->search}%")
              ->orWhere('item_code', 'like', "%{$request->search}%");
        });
        if ($request->low_stock) $query->whereRaw('quantity <= low_stock_alert AND low_stock_alert > 0');

        $stocks = $query->get()->map(function($s) {
            $s->is_low_stock = $s->low_stock_alert > 0 && $s->quantity <= $s->low_stock_alert;
            return $s;
        });

        $summary = [
            'total_profile_items'  => $stocks->where('item_type', 'profile')->count(),
            'total_hardware_items' => $stocks->where('item_type', 'hardware')->count(),
            'low_stock_count'      => $stocks->where('is_low_stock', true)->count(),
        ];

        return response()->json(['success' => true, 'data' => $stocks, 'summary' => $summary]);
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyId();
        $request->validate([
            'item_type' => 'required|in:profile,hardware',
            'item_code' => 'required|string',
            'item_name' => 'required|string',
            'quantity'  => 'required|numeric|min:0',
            'unit'      => 'required|string',
        ]);

        // Check duplicate
        $existing = Stock::where('company_id', $companyId)
            ->where('item_code', $request->item_code)
            ->where('item_type', $request->item_type)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Yeh item already stock mein hai.'], 422);
        }

        DB::beginTransaction();
        try {
            $stock = Stock::create([
                'company_id'          => $companyId,
                'item_type'           => $request->item_type,
                'product_id'          => $request->product_id,
                'hardware_product_id' => $request->hardware_product_id,
                'item_code'           => $request->item_code,
                'item_name'           => $request->item_name,
                'quantity'            => $request->quantity,
                'unit'                => $request->unit,
                'low_stock_alert'     => $request->low_stock_alert ?? 0,
            ]);

            if ($request->quantity > 0) {
                StockTransaction::create([
                    'stock_id'        => $stock->id,
                    'company_id'      => $companyId,
                    'type'            => 'in',
                    'quantity'        => $request->quantity,
                    'quantity_before' => 0,
                    'quantity_after'  => $request->quantity,
                    'note'            => 'Opening stock',
                    'created_by'      => auth('api')->id(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $stock]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);
        $stock->update([
            'item_name'       => $request->item_name ?? $stock->item_name,
            'unit'            => $request->unit ?? $stock->unit,
            'low_stock_alert' => $request->low_stock_alert ?? $stock->low_stock_alert,
        ]);
        return response()->json(['success' => true, 'data' => $stock]);
    }

    public function addStock(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|numeric|min:0.001', 'note' => 'nullable|string']);
        $companyId = $this->getCompanyId();

        DB::beginTransaction();
        try {
            $stock = Stock::findOrFail($id);
            $before = $stock->quantity;
            $after  = $before + $request->quantity;

            $stock->update(['quantity' => $after]);

            StockTransaction::create([
                'stock_id'        => $stock->id,
                'company_id'      => $companyId,
                'type'            => 'in',
                'quantity'        => $request->quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'note'            => $request->note ?? 'Stock in',
                'created_by'      => auth('api')->id(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $stock]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function removeStock(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|numeric|min:0.001']);
        $companyId = $this->getCompanyId();

        DB::beginTransaction();
        try {
            $stock = Stock::findOrFail($id);
            if ($stock->quantity < $request->quantity) {
                return response()->json(['success' => false, 'message' => 'Insufficient stock.'], 422);
            }
            $before = $stock->quantity;
            $after  = $before - $request->quantity;

            $stock->update(['quantity' => $after]);

            StockTransaction::create([
                'stock_id'        => $stock->id,
                'company_id'      => $companyId,
                'type'            => 'out',
                'quantity'        => $request->quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'note'            => $request->note ?? 'Stock out',
                'created_by'      => auth('api')->id(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $stock]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function transactions($id)
    {
        $stock = Stock::findOrFail($id);
        $transactions = StockTransaction::where('stock_id', $id)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success' => true, 'data' => $transactions, 'stock' => $stock]);
    }

    public function initFromProducts(Request $request)
    {
        $companyId = $this->getCompanyId();
        DB::beginTransaction();
        try {
            $products = Product::where('company_id', $companyId)->get();
            $created = 0;
            foreach ($products as $p) {
                $exists = Stock::where('company_id', $companyId)
                    ->where('item_type', 'profile')
                    ->where('product_id', $p->id)->exists();
                if (!$exists) {
                    Stock::create([
                        'company_id'  => $companyId,
                        'item_type'   => 'profile',
                        'product_id'  => $p->id,
                        'item_code'   => $p->product_code,
                        'item_name'   => $p->product_name,
                        'quantity'    => 0,
                        'unit'        => 'bundle',
                        'low_stock_alert' => 0,
                    ]);
                    $created++;
                }
            }

            $hardware = HardwareProduct::where('company_id', $companyId)->get();
            foreach ($hardware as $h) {
                $exists = Stock::where('company_id', $companyId)
                    ->where('item_type', 'hardware')
                    ->where('hardware_product_id', $h->id)->exists();
                if (!$exists) {
                    Stock::create([
                        'company_id'          => $companyId,
                        'item_type'           => 'hardware',
                        'hardware_product_id' => $h->id,
                        'item_code'           => $h->code,
                        'item_name'           => $h->name,
                        'quantity'            => 0,
                        'unit'                => $h->unit ?? 'piece',
                        'low_stock_alert'     => 0,
                    ]);
                    $created++;
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => "{$created} items initialized."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

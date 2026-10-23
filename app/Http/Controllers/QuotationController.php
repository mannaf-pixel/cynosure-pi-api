<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Product;
use App\Models\HardwareProduct;
use App\Models\PiMaster;
use App\Models\PiItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuotationController extends Controller
{
    private function getCompanyId()
    {
        $user = auth('api')->user();
        if ($user->is_super_admin) {
            return request()->header('X-Company-Id') ?? request()->query('company_id');
        }
        return $user->company_id;
    }

    private function generateNumber($companyId)
    {
        $prefix = $companyId == 2 ? 'QT-PL-' : 'QT-CY-';
        $year   = date('Y');
        $last   = Quotation::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')->first();
        $seq = $last ? (intval(substr($last->quotation_number, -4)) + 1) : 1;
        return $prefix . $year . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function calcItems(array $requestItems, $company)
    {
        $subtotal  = 0;
        $itemsData = [];

        foreach ($requestItems as $index => $item) {
            $itemType = $item['item_type'] ?? 'profile';

            if ($itemType === 'hardware') {
                $hardware  = HardwareProduct::findOrFail($item['hardware_product_id']);
                $quantity  = $item['quantity'] ?? 0;
                $discPct   = $item['discount_pct'] ?? 0;
                $rate      = $hardware->rate;
                $netRate   = round($rate * (1 - $discPct / 100));
                $lineTotal = round($quantity * $netRate, 2);
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'item_type'           => 'hardware',
                    'product_id'          => $hardware->id,
                    'hardware_product_id' => $hardware->id,
                    'hardware_name_snap'  => $hardware->name,
                    'hardware_unit_snap'  => $hardware->unit,
                    'product_code_snap'   => $hardware->code,
                    'product_name_snap'   => $hardware->name,
                    'unit_rate_snap'      => $netRate,
                    'mrp_rate_snap'       => $rate,
                    'item_discount_pct'   => $discPct,
                    'quantity'            => $quantity,
                    'bundle_qty_ordered'  => 0,
                    'total_length'        => 0,
                    'total_pieces'        => 0,
                    'total_weight'        => 0,
                    'line_total'          => $lineTotal,
                    'sort_order'          => $index,
                ];
                continue;
            }

            $product      = Product::findOrFail($item['product_id']);
            $itemProfType = $item['profile_type'] ?? 'white';
            $brand        = request()->input('brand', 'cynosure');

            if ($company->pricing_mode === 'per_meter') {
                if ($brand === 'sinewy') {
                    $rate = $itemProfType === 'white' ? $product->sinewy_white_rate : $product->sinewy_color_rate;
                } elseif ($brand === 'assre_plasto') {
                    $rate = $itemProfType === 'white' ? $product->assre_white_rate : $product->assre_color_rate;
                } else {
                    $rate = $itemProfType === 'white' ? $product->white_rate : $product->color_rate;
                }

                $discPct = $item['discount_pct'] ?? 0;
                $netRate = round($rate * (1 - $discPct / 100));
                $exactNetRate = $rate * (1 - $discPct / 100);

                if ($itemProfType === 'white') {
                    $bundleQty   = $item['bundle_qty_ordered'] ?? 1;
                    $totalPieces = $bundleQty * $product->bundle_qty;
                    $totalLength = $bundleQty * $product->bundle_qty * $product->profile_length;
                } else {
                    $totalPieces = $item['total_pieces'] ?? 0;
                    $bundleQty   = ceil($totalPieces / $product->bundle_qty);
                    $totalLength = $totalPieces * $product->profile_length;
                }

                $totalWeight = $totalLength * ($product->weight_per_meter ?? 0);
                $lineTotal   = round($totalLength * $exactNetRate, 2);
            } else {
                $rate        = $item['unit_rate'] ?? 0;
                $netRate     = $rate;
                $discPct     = 0;
                $totalPieces = $item['total_pieces'] ?? 0;
                $totalWeight = $item['total_weight'] ?? 0;
                $totalLength = 0;
                $lineTotal   = $totalWeight * $rate;
                $bundleQty   = 1;
                $itemProfType = 'white';
            }

            $subtotal += $lineTotal;

            $itemsData[] = [
                'item_type'             => 'profile',
                'product_id'            => $product->id,
                'hardware_product_id'   => null,
                'product_code_snap'     => $product->product_code,
                'product_name_snap'     => $product->product_name,
                'profile_type_snap'     => $itemProfType,
                'color_name'            => $itemProfType === 'color' ? ($item['color_name'] ?? null) : null,
                'unit_rate_snap'        => $netRate,
                'mrp_rate_snap'         => $rate,
                'item_discount_pct'     => $discPct,
                'profile_length_snap'   => $product->profile_length,
                'weight_per_meter_snap' => $product->weight_per_meter ?? 0,
                'bundle_qty_ordered'    => $bundleQty,
                'total_length'          => round($totalLength, 2),
                'total_pieces'          => $totalPieces,
                'total_weight'          => round((float)$totalWeight, 3),
                'line_total'            => round($lineTotal, 2),
                'sort_order'            => $index,
            ];
        }

        return ['subtotal' => $subtotal, 'items' => $itemsData];
    }

    private function calcTotals(float $subtotal, Request $request)
    {
        $transport        = $request->transport_charge ?? 0;
        $insurancePct     = $request->insurance_pct ?? 0;
        $cdDiscountPct    = $request->cd_discount_pct ?? 0;
        $cdDiscountAmount = round($subtotal * $cdDiscountPct / 100, 2);
        $afterCd          = $subtotal - $cdDiscountAmount;
        $insurance        = round($afterCd * $insurancePct / 100, 2);
        $taxableAmount    = $afterCd + $transport + $insurance;
        $gstAmount        = round($taxableAmount * 0.18, 2);
        $grandTotal       = round($taxableAmount + $gstAmount, 2);

        return compact('transport', 'insurance', 'insurancePct', 'cdDiscountPct', 'cdDiscountAmount', 'gstAmount', 'grandTotal');
    }

    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $query = Quotation::with('customer')
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc');

        if ($request->status)      $query->where('status', $request->status);
        if ($request->customer_id) $query->where('customer_id', $request->customer_id);
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('quotation_number', 'like', "%{$request->search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('company_name', 'like', "%{$request->search}%"));
            });
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyId();
        $company   = \App\Models\Company::find($companyId);
        if (!$company) return response()->json(['success' => false, 'message' => 'Company not found'], 422);

        $request->validate(['customer_id' => 'required|exists:customers,id', 'items' => 'required|array|min:1']);

        DB::beginTransaction();
        try {
            $calc   = $this->calcItems($request->items, $company);
            $totals = $this->calcTotals($calc['subtotal'], $request);

            $quotation = Quotation::create([
                'quotation_number'     => $this->generateNumber($companyId),
                'company_id'           => $companyId,
                'customer_id'          => $request->customer_id,
                'created_by'           => auth('api')->id(),
                'brand'                => $request->brand ?? $company->slug,
                'salesperson_name'     => $request->salesperson_name,
                'color_name'           => $request->color_name,
                'white_discount_pct'   => $request->white_discount_pct ?? 0,
                'color_discount_pct'   => $request->color_discount_pct ?? 0,
                'hardware_discount_pct'=> $request->hardware_discount_pct ?? 0,
                'cd_discount_pct'      => $totals['cdDiscountPct'],
                'cd_discount_amount'   => $totals['cdDiscountAmount'],
                'transport_charge'     => $totals['transport'],
                'insurance_pct'        => $totals['insurancePct'],
                'insurance_charge'     => $totals['insurance'],
                'subtotal'             => $calc['subtotal'],
                'gst_amount'           => $totals['gstAmount'],
                'grand_total'          => $totals['grandTotal'],
                'status'               => 'draft',
                'valid_until'          => now()->addDays(30),
                'remarks'              => $request->remarks,
            ]);

            foreach ($calc['items'] as $item) {
                $item['quotation_id'] = $quotation->id;
                QuotationItem::create($item);
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $quotation->load('items', 'customer')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $quotation = Quotation::with('customer', 'items', 'creator')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $quotation]);
    }

    public function update(Request $request, $id)
    {
        $quotation = Quotation::findOrFail($id);
        if (!in_array($quotation->status, ['draft'])) {
            return response()->json(['success' => false, 'message' => 'Sirf Draft quotation edit ho sakti hai.'], 422);
        }

        $company = \App\Models\Company::find($quotation->company_id);
        DB::beginTransaction();
        try {
            $calc   = $this->calcItems($request->items, $company);
            $totals = $this->calcTotals($calc['subtotal'], $request);

            $quotation->update([
                'customer_id'          => $request->customer_id ?? $quotation->customer_id,
                'brand'                => $request->brand ?? $quotation->brand,
                'salesperson_name'     => $request->salesperson_name,
                'color_name'           => $request->color_name,
                'white_discount_pct'   => $request->white_discount_pct ?? 0,
                'color_discount_pct'   => $request->color_discount_pct ?? 0,
                'hardware_discount_pct'=> $request->hardware_discount_pct ?? 0,
                'cd_discount_pct'      => $totals['cdDiscountPct'],
                'cd_discount_amount'   => $totals['cdDiscountAmount'],
                'transport_charge'     => $totals['transport'],
                'insurance_pct'        => $totals['insurancePct'],
                'insurance_charge'     => $totals['insurance'],
                'subtotal'             => $calc['subtotal'],
                'gst_amount'           => $totals['gstAmount'],
                'grand_total'          => $totals['grandTotal'],
                'remarks'              => $request->remarks,
            ]);

            QuotationItem::where('quotation_id', $quotation->id)->delete();
            foreach ($calc['items'] as $item) {
                $item['quotation_id'] = $quotation->id;
                QuotationItem::create($item);
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $quotation->load('items', 'customer')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $quotation = Quotation::findOrFail($id);
        $request->validate(['status' => 'required|in:draft,sent,confirmed,expired,cancelled']);
        $quotation->update(['status' => $request->status]);
        return response()->json(['success' => true, 'data' => $quotation]);
    }

    public function convertToPI($id)
    {
        $quotation = Quotation::with('items')->findOrFail($id);
        if ($quotation->status !== 'confirmed') {
            return response()->json(['success' => false, 'message' => 'Sirf Confirmed quotation PI mein convert ho sakti hai.'], 422);
        }
        if ($quotation->pi_id) {
            return response()->json(['success' => false, 'message' => 'Yeh quotation already PI mein convert ho chuki hai.'], 422);
        }

        DB::beginTransaction();
        try {
            // Generate PI number
            $companyId = $quotation->company_id;
            $prefix = $companyId == 2 ? 'PI-PL-' : 'PI-CY-';
            $year   = date('Y');
            $last   = PiMaster::where('company_id', $companyId)->whereYear('created_at', $year)->orderBy('id', 'desc')->first();
            $seq    = $last ? (intval(substr($last->pi_number, -4)) + 1) : 1;
            $piNumber = $prefix . $year . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $pi = PiMaster::create([
                'pi_number'            => $piNumber,
                'company_id'           => $quotation->company_id,
                'customer_id'          => $quotation->customer_id,
                'created_by'           => auth('api')->id(),
                'brand'                => $quotation->brand,
                'salesperson_name'     => $quotation->salesperson_name,
                'color_name'           => $quotation->color_name,
                'white_discount_pct'   => $quotation->white_discount_pct,
                'color_discount_pct'   => $quotation->color_discount_pct,
                'hardware_discount_pct'=> $quotation->hardware_discount_pct,
                'cd_discount_pct'      => $quotation->cd_discount_pct,
                'cd_discount_amount'   => $quotation->cd_discount_amount,
                'transport_charge'     => $quotation->transport_charge,
                'insurance_pct'        => $quotation->insurance_pct,
                'insurance_charge'     => $quotation->insurance_charge,
                'subtotal'             => $quotation->subtotal,
                'discount_pct'         => 0,
                'discount_amount'      => 0,
                'gst_amount'           => $quotation->gst_amount,
                'grand_total'          => $quotation->grand_total,
                'status'               => 'draft',
                'profile_type'         => 'white',
            ]);

            foreach ($quotation->items as $item) {
                PiItem::create([
                    'pi_id'                 => $pi->id,
                    'item_type'             => $item->item_type,
                    'product_id'            => $item->product_id,
                    'hardware_product_id'   => $item->hardware_product_id,
                    'product_code_snap'     => $item->product_code_snap,
                    'product_name_snap'     => $item->product_name_snap,
                    'hardware_name_snap'    => $item->hardware_name_snap,
                    'hardware_unit_snap'    => $item->hardware_unit_snap,
                    'profile_type_snap'     => $item->profile_type_snap,
                    'color_name'            => $item->color_name,
                    'unit_rate_snap'        => $item->unit_rate_snap,
                    'mrp_rate_snap'         => $item->mrp_rate_snap,
                    'item_discount_pct'     => $item->item_discount_pct,
                    'profile_length_snap'   => $item->profile_length_snap,
                    'weight_per_meter_snap' => $item->weight_per_meter_snap,
                    'bundle_qty_ordered'    => $item->bundle_qty_ordered,
                    'total_length'          => $item->total_length,
                    'total_pieces'          => $item->total_pieces,
                    'total_weight'          => $item->total_weight,
                    'quantity'              => $item->quantity,
                    'line_total'            => $item->line_total,
                    'sort_order'            => $item->sort_order,
                ]);
            }

            $quotation->update(['status' => 'converted', 'pi_id' => $pi->id]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $pi->load('items', 'customer'), 'message' => 'Quotation PI mein convert ho gayi.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $quotation = Quotation::findOrFail($id);
        if (!in_array($quotation->status, ['draft', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Sirf Draft ya Cancelled quotation delete ho sakti hai.'], 422);
        }
        QuotationItem::where('quotation_id', $quotation->id)->delete();
        $quotation->delete();
        return response()->json(['success' => true, 'message' => 'Quotation delete ho gayi.']);
    }
}

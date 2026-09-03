<?php

namespace App\Http\Controllers;

use App\Models\PiMaster;
use App\Models\PiItem;
use App\Models\Product;
use App\Models\Approval;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PIController extends Controller
{
    private function getCompanyId()
    {
        $user = auth('api')->user();
        if ($user->is_super_admin) {
            return request()->header('X-Company-Id') ?? request()->query('company_id');
        }
        return $user->company_id;
    }

    private function getCompany()
    {
        $companyId = $this->getCompanyId();
        return $companyId ? Company::find($companyId) : null;
    }

    private function generatePINumber(int $companyId): string
    {
        $company = Company::find($companyId);
        $prefix  = strtoupper(substr($company->slug ?? 'cs', 0, 2));
        $year    = date('Y');
        $pattern = "PI-{$prefix}-{$year}%";

        $last = PiMaster::where('company_id', $companyId)
                        ->where('pi_number', 'like', $pattern)
                        ->orderBy('pi_number', 'desc')
                        ->first();

        if ($last) {
            $lastNum = (int) substr($last->pi_number, -4);
            $next    = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }

        return "PI-{$prefix}-{$year}{$next}";
    }

    private function calcItems(array $requestItems, string $profileType, Company $company): array
    {
        $subtotal  = 0;
        $itemsData = [];

        foreach ($requestItems as $index => $item) {
            $itemType = $item['item_type'] ?? 'profile';

            // Hardware item
            if ($itemType === 'hardware') {
                $hardware  = \App\Models\HardwareProduct::findOrFail($item['hardware_product_id']);
                $quantity  = $item['quantity'] ?? 0;
                $rate      = $hardware->rate;
                $lineTotal = $quantity * $rate;
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'item_type'           => 'hardware',
                    'product_id'          => null,
                    'hardware_product_id' => $hardware->id,
                    'hardware_name_snap'  => $hardware->name,
                    'hardware_unit_snap'  => $hardware->unit,
                    'product_code_snap'   => $hardware->code,
                    'product_name_snap'   => $hardware->name,
                    'unit_rate_snap'      => $netRate,
                'mrp_rate_snap'       => $rate,
                    'quantity'            => $quantity,
                    'bundle_qty_ordered'  => 0,
                    'total_length'        => 0,
                    'total_pieces'        => 0,
                    'total_weight'        => 0,
                    'line_total'          => round($lineTotal, 2),
                    'sort_order'          => $index,
                    'profile_length_snap'   => 0,
                    'weight_per_meter_snap' => 0,
                ];
                continue;
            }

            $product      = Product::findOrFail($item['product_id']);
            $itemProfType = $item['profile_type'] ?? $profileType;

            // Per Meter pricing (Cynosure)
            if ($company->pricing_mode === 'per_meter') {
                $brand = request()->input('brand', 'cynosure');
                if ($brand === 'sinewy') {
                    $rate = $itemProfType === 'white' ? $product->sinewy_white_rate : $product->sinewy_color_rate;
                } elseif ($brand === 'assre_plasto') {
                    $rate = $itemProfType === 'white' ? $product->assre_white_rate : $product->assre_color_rate;
                } else {
                    $rate = $itemProfType === 'white' ? $product->white_rate : $product->color_rate;
                }
                if ($itemProfType === 'white') {
                    $bundleQty   = $item['bundle_qty_ordered'] ?? 1;
                    $totalPieces = $bundleQty * $product->bundle_qty;
                    $totalLength = $bundleQty * $product->bundle_qty * $product->profile_length;
                } else {
                    $totalPieces = $item['total_pieces'] ?? 0;
                    $bundleQty   = ceil($totalPieces / $product->bundle_qty);
                    $totalLength = $totalPieces * $product->profile_length;
                }
                $totalWeight      = $totalLength * ($product->weight_per_meter ?? 0);
                $discountPctItem  = request()->input('discount_pct', 0);
                $netRate          = round($rate * (1 - $discountPctItem / 100));
                $lineTotal        = round($totalLength * $netRate, 2);
            }
            // Per Kg pricing (Plastrong)
            else {
                $rate        = $item['unit_rate'] ?? 0;
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

    private function calcTotals(float $subtotal, Request $request): array
    {
        $transport      = $request->transport_charge ?? 0;
        $insurancePct   = $request->insurance_pct ?? 0;
        $discountPct    = $request->discount_pct ?? 0;
        $discountAmount = 0; // Already applied per item
        $afterDiscount  = $subtotal; // Already discounted
        $insurance      = round($afterDiscount * $insurancePct / 100, 2);
        $taxableAmount  = $afterDiscount + $transport + $insurance;
        $gstAmount      = round($taxableAmount * 0.18, 2);
        $grandTotal     = round($taxableAmount + $gstAmount, 2);

        return compact('transport', 'insurance', 'insurancePct', 'discountPct', 'discountAmount', 'gstAmount', 'grandTotal');
    }

    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $query     = PiMaster::with('customer', 'salesperson')->orderBy('created_at', 'desc');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pi_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q2) use ($search) {
                      $q2->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $pis = $query->get();

        // Add payment summary to each PI
        $pis->each(function($pi) {
            $received = \App\Models\Payment::where('pi_id', $pi->id)->sum('amount');
            $pi->total_received = $received;
            $pi->balance = $pi->grand_total - $received;
        });

        return response()->json(['success' => true, 'data' => $pis]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'  => 'required|exists:customers,id',
            'items'        => 'required|array|min:1',
        ]);

        $companyId = $this->getCompanyId();
        $company   = $this->getCompany();

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found.'], 422);
        }

        DB::beginTransaction();
        try {
            $calc   = $this->calcItems($request->items, $request->profile_type ?? 'white', $company);
            $totals = $this->calcTotals($calc['subtotal'], $request);

            $pi = PiMaster::create([
                'company_id'       => $companyId,
                'pi_number'        => $this->generatePINumber($companyId),
                'customer_id'      => $request->customer_id,
                'profile_type'     => $request->profile_type,
                'brand'            => $request->brand ?? $company->slug,
                'color_name'       => $request->color_name ?? null,
                'status'           => 'draft',
                'transport_charge' => $totals['transport'],
                'insurance_charge' => $totals['insurance'],
                'insurance_pct'    => $totals['insurancePct'],
                'discount_pct'     => $totals['discountPct'],
                'discount_amount'  => $totals['discountAmount'],
                'subtotal'         => round($calc['subtotal'], 2),
                'gst_amount'       => $totals['gstAmount'],
                'grand_total'      => $totals['grandTotal'],
                'remarks'          => $request->remarks,
                'salesperson_id'   => $request->salesperson_id ?: null,
                'salesperson_name' => $request->salesperson_name ?? null,
                'actual_amount'    => $request->actual_amount ?: 0,
                'payment_mode'     => $request->payment_mode ?? null,
                'received_in'      => $request->received_in ?? null,
                'payment_note'     => $request->payment_note ?? null,
                'created_by'       => auth('api')->id(),
                'salesperson_id'   => $request->salesperson_id ?: null,
                'salesperson_name' => $request->salesperson_name ?? null,
            ]);

            foreach ($calc['items'] as $itemData) {
                $itemData['pi_id'] = $pi->id;
                PiItem::create($itemData);
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $pi->load('customer', 'items')], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'PI creation failed: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $pi = PiMaster::findOrFail($id);

        if (!in_array($pi->status, ['draft', 'rejected'])) {
            return response()->json(['success' => false, 'message' => 'Sirf Draft ya Rejected PI edit ki ja sakti hai.'], 422);
        }

        $request->validate([
            'customer_id'  => 'required|exists:customers,id',
            'items'        => 'required|array|min:1',
        ]);

        $company = Company::find($pi->company_id);

        DB::beginTransaction();
        try {
            PiItem::where('pi_id', $pi->id)->delete();

            $calc   = $this->calcItems($request->items, $request->profile_type, $company);
            $totals = $this->calcTotals($calc['subtotal'], $request);

            $pi->update([
                'customer_id'      => $request->customer_id,
                'profile_type'     => $request->profile_type,
                'brand'            => $request->brand ?? $pi->brand,
                'color_name'       => $request->color_name ?? $pi->color_name,
                'status'           => 'draft',
                'transport_charge' => $totals['transport'],
                'insurance_charge' => $totals['insurance'],
                'insurance_pct'    => $totals['insurancePct'],
                'discount_pct'     => $totals['discountPct'],
                'discount_amount'  => $totals['discountAmount'],
                'subtotal'         => round($calc['subtotal'], 2),
                'gst_amount'       => $totals['gstAmount'],
                'grand_total'      => $totals['grandTotal'],
                'remarks'          => $request->remarks,
                'salesperson_id'   => $request->salesperson_id ?: null,
                'salesperson_name' => $request->salesperson_name ?? null,
                'actual_amount'    => $request->actual_amount ?: 0,
                'payment_mode'     => $request->payment_mode ?? null,
                'received_in'      => $request->received_in ?? null,
                'payment_note'     => $request->payment_note ?? null,
            ]);

            foreach ($calc['items'] as $itemData) {
                $itemData['pi_id'] = $pi->id;
                PiItem::create($itemData);
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $pi->load('customer', 'items')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'PI update failed: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $pi = PiMaster::with('customer', 'items', 'approvals.approver', 'salesperson')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $pi]);
    }

    public function submit($id)
    {
        $pi = PiMaster::findOrFail($id);

        if ($pi->status !== 'draft' && $pi->status !== 'rejected') {
            return response()->json(['success' => false, 'message' => 'Sirf Draft ya Rejected PI submit ki ja sakti hai.'], 422);
        }

        $pi->update(['status' => 'md_pending', 'submitted_at' => now()]);

        // Notify MD
        $mdUsers = \App\Models\User::where('company_id', $pi->company_id)
            ->where('role', 'md')->get();
        foreach ($mdUsers as $md) {
            \App\Models\Notification::create([
                'company_id' => $pi->company_id,
                'user_id'    => $md->id,
                'pi_id'      => $pi->id,
                'type'       => 'pi_submitted',
                'title'      => 'New PI for Approval',
                'message'    => 'PI ' . $pi->pi_number . ' submitted for your approval.',
                'is_read'    => false,
            ]);
        }

        return response()->json(['success' => true, 'data' => $pi]);
    }

    public function approve(Request $request, $id)
    {
        $pi   = PiMaster::findOrFail($id);
        $user = auth('api')->user();

        if ($user->role === 'md') {
            if ($pi->status !== 'md_pending') {
                return response()->json(['success' => false, 'message' => 'PI MD approval ke liye ready nahi hai.'], 422);
            }
            $pi->update(['status' => 'ceo_pending']);
            $approverRole = 'md';
        } elseif ($user->role === 'ceo' || $user->role === 'admin') {
            if ($pi->status !== 'ceo_pending') {
                return response()->json(['success' => false, 'message' => 'PI CEO approval ke liye ready nahi hai.'], 422);
            }
            $pi->update(['status' => 'ceo_approved']);
            $approverRole = 'ceo';
        } else {
            return response()->json(['success' => false, 'message' => 'Permission nahi hai.'], 403);
        }

        Approval::create([
            'pi_id'         => $pi->id,
            'approver_id'   => $user->id,
            'action'        => 'approved',
            'approver_role' => $approverRole,
            'comments'      => $request->comments,
        ]);

        // Notify PI creator
        $creator = \App\Models\User::find($pi->created_by);
        if ($creator) {
            \App\Models\Notification::create([
                'company_id' => $pi->company_id,
                'user_id'    => $creator->id,
                'pi_id'      => $pi->id,
                'type'       => 'pi_approved',
                'title'      => 'PI Approved',
                'message'    => 'PI ' . $pi->pi_number . ' has been approved by ' . strtoupper($approverRole) . '.',
                'is_read'    => false,
            ]);
        }

        // If MD approved, notify CEO
        if ($approverRole === 'md') {
            $ceoUsers = \App\Models\User::where('company_id', $pi->company_id)
                ->where('role', 'ceo')->get();
            foreach ($ceoUsers as $ceo) {
                \App\Models\Notification::create([
                    'company_id' => $pi->company_id,
                    'user_id'    => $ceo->id,
                    'pi_id'      => $pi->id,
                    'type'       => 'pi_submitted',
                    'title'      => 'PI Pending CEO Approval',
                    'message'    => 'PI ' . $pi->pi_number . ' approved by MD, waiting for your approval.',
                    'is_read'    => false,
                ]);
            }
        }

        return response()->json(['success' => true, 'data' => $pi]);
    }

    // Dispatch Manager — Delivery Schedule karo
    public function scheduleDelivery(Request $request, $id)
    {
        $pi   = PiMaster::findOrFail($id);
        $user = auth('api')->user();

        if (!in_array($user->role, ['admin', 'dispatch_manager'])) {
            return response()->json(['success' => false, 'message' => 'Permission nahi hai.'], 403);
        }

        if ($pi->status !== 'payment_confirmed') {
            return response()->json(['success' => false, 'message' => 'Sirf Payment Confirmed PI schedule ki ja sakti hai.'], 422);
        }

        $request->validate(['expected_dispatch_date' => 'required|date']);

        $pi->update([
            'status'                 => 'delivery_scheduled',
            'expected_dispatch_date' => $request->expected_dispatch_date,
            'delivery_scheduled_at'  => now(),
        ]);

        $creator = \App\Models\User::find($pi->created_by);
        if ($creator) {
            \App\Models\Notification::create([
                'company_id' => $pi->company_id,
                'user_id'    => $creator->id,
                'pi_id'      => $pi->id,
                'type'       => 'pi_approved',
                'title'      => 'Delivery Scheduled',
                'message'    => 'PI ' . $pi->pi_number . ' delivery scheduled for ' . $request->expected_dispatch_date . '.',
                'is_read'    => false,
            ]);
        }

        return response()->json(['success' => true, 'data' => $pi->fresh()]);
    }

    // Admin — Payment Confirm karo
    public function confirmPayment(Request $request, $id)
    {
        $pi   = PiMaster::findOrFail($id);
        $user = auth('api')->user();

        if (!in_array($user->role, ['admin', 'ceo'])) {
            return response()->json(['success' => false, 'message' => 'Permission nahi hai.'], 403);
        }

        if ($pi->status !== 'ceo_approved') {
            return response()->json(['success' => false, 'message' => 'Sirf CEO Approved PI ka payment confirm kar sakte hain.'], 422);
        }

        $pi->update(['status' => 'payment_confirmed']);

        // Notify dispatch managers
        $dispatchers = \App\Models\User::where('company_id', $pi->company_id)
            ->where('role', 'dispatch_manager')->get();
        foreach ($dispatchers as $d) {
            \App\Models\Notification::create([
                'company_id' => $pi->company_id,
                'user_id'    => $d->id,
                'pi_id'      => $pi->id,
                'type'       => 'pi_approved',
                'title'      => 'New Dispatch Order',
                'message'    => 'PI ' . $pi->pi_number . ' payment confirmed. Ready for dispatch.',
                'is_read'    => false,
            ]);
        }

        return response()->json(['success' => true, 'data' => $pi]);
    }

    // PI Delete — sirf Draft
    public function destroy($id)
    {
        $pi   = PiMaster::findOrFail($id);
        $user = auth('api')->user();

        if (!in_array($pi->status, ['draft', 'rejected'])) {
            return response()->json(['success' => false, 'message' => 'Sirf Draft ya Rejected PI delete ki ja sakti hai.'], 422);
        }

        PiItem::where('pi_id', $pi->id)->delete();
        $pi->delete();

        return response()->json(['success' => true, 'message' => 'PI delete ho gayi.']);
    }

    // Dispatch Manager — Dispatch karo
    public function dispatch(Request $request, $id)
    {
        $pi   = PiMaster::findOrFail($id);
        $user = auth('api')->user();

        if (!in_array($user->role, ['admin', 'dispatch_manager'])) {
            return response()->json(['success' => false, 'message' => 'Permission nahi hai.'], 403);
        }

        if ($pi->status !== 'payment_confirmed') {
            return response()->json(['success' => false, 'message' => 'Sirf Payment Confirmed PI dispatch kar sakte hain.'], 422);
        }

        // Handle file uploads
        $dispatchPhoto  = null;
        $transportCopy  = null;

        if ($request->hasFile('dispatch_photo')) {
            $dispatchPhoto = $request->file('dispatch_photo')->store('dispatch/photos', 'public');
        }
        if ($request->hasFile('transport_copy')) {
            $transportCopy = $request->file('transport_copy')->store('dispatch/transport', 'public');
        }

        $pi->update([
            'status'                  => 'dispatched',
            'transport_company'       => $request->transport_company,
            'vehicle_number'          => $request->vehicle_number,
            'driver_name'             => $request->driver_name,
            'driver_phone'            => $request->driver_phone,
            'lr_number'               => $request->lr_number,
            'freight_amount'          => $request->freight_amount ?? 0,
            'dispatch_note'           => $request->dispatch_note,
            'dispatch_photo'          => $dispatchPhoto,
            'transport_copy'          => $transportCopy,
            'dispatched_at'           => now(),
            'expected_dispatch_date'  => $request->expected_dispatch_date ?? null,
            'remarks'                 => $pi->remarks . ' | Dispatched by: ' . $user->name . ' on ' . now()->format('d-M-Y H:i'),
        ]);

        // Notify PI creator
        $creator = \App\Models\User::find($pi->created_by);
        if ($creator) {
            \App\Models\Notification::create([
                'company_id' => $pi->company_id,
                'user_id'    => $creator->id,
                'pi_id'      => $pi->id,
                'type'       => 'pi_approved',
                'title'      => 'PI Dispatched',
                'message'    => 'PI ' . $pi->pi_number . ' has been dispatched by ' . $user->name . '.',
                'is_read'    => false,
            ]);
        }

        return response()->json(['success' => true, 'data' => $pi->fresh()]);
    }

    public function reject(Request $request, $id)
    {
        $pi   = PiMaster::findOrFail($id);
        $user = auth('api')->user();

        $request->validate(['comments' => 'required|string']);

        $approverRole = $user->role === 'md' ? 'md' : 'ceo';
        $pi->update(['status' => 'rejected']);

        Approval::create([
            'pi_id'         => $pi->id,
            'approver_id'   => $user->id,
            'action'        => 'rejected',
            'approver_role' => $approverRole,
            'comments'      => $request->comments,
        ]);

        // Notify PI creator
        $creator = \App\Models\User::find($pi->created_by);
        if ($creator) {
            \App\Models\Notification::create([
                'company_id' => $pi->company_id,
                'user_id'    => $creator->id,
                'pi_id'      => $pi->id,
                'type'       => 'pi_rejected',
                'title'      => 'PI Rejected',
                'message'    => 'PI ' . $pi->pi_number . ' rejected by ' . strtoupper($approverRole) . '. Reason: ' . $request->comments,
                'is_read'    => false,
            ]);
        }

        return response()->json(['success' => true, 'data' => $pi]);
    }
}
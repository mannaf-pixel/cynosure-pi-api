<?php

namespace App\Http\Controllers;

use App\Models\PiMaster;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function getCompanyId()
    {
        $user = auth('api')->user();
        if ($user->is_super_admin) {
            return request()->header('X-Company-Id') ?? request()->query('company_id');
        }
        return $user->company_id;
    }

    private function applyFilters($query, Request $request, $table = 'pi_master')
    {
        if ($request->brand)       $query->where("{$table}.brand", $request->brand);
        if ($request->status)      $query->where("{$table}.status", $request->status);
        if ($request->customer_id) $query->where("{$table}.customer_id", $request->customer_id);
        if ($request->salesperson) $query->where("{$table}.salesperson_name", 'like', "%{$request->salesperson}%");
        if ($request->amount_min)  $query->where("{$table}.grand_total", '>=', $request->amount_min);
        if ($request->amount_max)  $query->where("{$table}.grand_total", '<=', $request->amount_max);
        return $query;
    }

    public function datewise(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = PiMaster::with('customer')
            ->whereBetween(DB::raw('DATE(pi_master.created_at)'), [$from, $to]);

        if ($companyId) $query->where('pi_master.company_id', $companyId);
        $this->applyFilters($query, $request);

        $pis = $query->orderBy('pi_master.created_at', 'desc')->get();

        $data = $pis->map(fn($pi) => [
            'pi_number'        => $pi->pi_number,
            'customer_name'    => $pi->customer->company_name ?? '-',
            'brand'            => $pi->brand,
            'salesperson_name' => $pi->salesperson_name,
            'date'             => $pi->created_at->format('Y-m-d'),
            'status'           => $pi->status,
            'amount'           => $pi->grand_total,
        ]);

        $summary = [
            'total_pi'    => $pis->count(),
            'total_value' => $pis->sum('grand_total'),
            'approved'    => $pis->whereIn('status', ['ceo_approved','payment_confirmed','delivery_scheduled','dispatched'])->count(),
            'pending'     => $pis->whereIn('status', ['md_pending','ceo_pending'])->count(),
            'draft'       => $pis->where('status', 'draft')->count(),
            'rejected'    => $pis->where('status', 'rejected')->count(),
        ];

        return response()->json(['success' => true, 'data' => $data, 'summary' => $summary]);
    }

    public function customerwise(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = PiMaster::select(
                'customer_id',
                DB::raw('COUNT(*) as total_pi'),
                DB::raw('SUM(grand_total) as total_value')
            )
            ->with('customer')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        if ($companyId) $query->where('company_id', $companyId);
        $this->applyFilters($query, $request);

        $rows = $query->groupBy('customer_id')->orderBy('total_value', 'desc')->get();

        $data = $rows->map(fn($r) => [
            'customer_name' => $r->customer->company_name ?? '-',
            'total_pi'      => $r->total_pi,
            'total_value'   => $r->total_value,
        ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function productwise(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = DB::table('pi_items')
            ->join('pi_master', 'pi_items.pi_id', '=', 'pi_master.id')
            ->select(
                'pi_items.product_code_snap',
                'pi_items.product_name_snap',
                DB::raw('SUM(pi_items.total_length) as total_qty'),
                DB::raw('SUM(pi_items.total_weight) as total_weight'),
                DB::raw('SUM(pi_items.total_pieces) as total_pieces'),
                DB::raw('SUM(pi_items.line_total) as total_value'),
                DB::raw('COUNT(DISTINCT pi_master.id) as pi_count')
            )
            ->whereBetween(DB::raw('DATE(pi_master.created_at)'), [$from, $to])
            ->where('pi_items.item_type', '!=', 'hardware');

        if ($companyId) $query->where('pi_master.company_id', $companyId);
        if ($request->brand)  $query->where('pi_master.brand', $request->brand);
        if ($request->status) $query->where('pi_master.status', $request->status);

        $rows = $query->groupBy('pi_items.product_code_snap', 'pi_items.product_name_snap')
            ->orderBy('total_value', 'desc')
            ->get();

        $data = $rows->map(fn($r) => [
            'product_name' => $r->product_code_snap . ' - ' . $r->product_name_snap,
            'total_qty'    => round($r->total_qty, 2) . ' m',
            'total_value'  => $r->total_value,
        ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function monthly(Request $request)
    {
        $companyId = $this->getCompanyId();
        $year = $request->year ?? date('Y');

        $query = PiMaster::select(
                DB::raw('MONTH(created_at) as month_num'),
                DB::raw('MONTHNAME(created_at) as month'),
                DB::raw('COUNT(*) as total_pi'),
                DB::raw('SUM(grand_total) as total_value')
            )
            ->whereYear('created_at', $year);

        if ($companyId) $query->where('company_id', $companyId);
        if ($request->brand)  $query->where('brand', $request->brand);
        if ($request->status) $query->where('status', $request->status);

        $data = $query->groupBy(DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
            ->orderBy('month_num')
            ->get();

        return response()->json(['success' => true, 'data' => $data, 'year' => $year]);
    }

    public function statuswise(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = PiMaster::select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(grand_total) as total_value')
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        if ($companyId) $query->where('company_id', $companyId);
        if ($request->brand)       $query->where('brand', $request->brand);
        if ($request->customer_id) $query->where('customer_id', $request->customer_id);

        $data = $query->groupBy('status')->orderBy('total_value', 'desc')->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function gst(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = PiMaster::with('customer')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        if ($companyId) $query->where('company_id', $companyId);
        $this->applyFilters($query, $request);

        $pis = $query->orderBy('created_at')->get();

        $data = $pis->map(fn($pi) => [
            'pi_number'      => $pi->pi_number,
            'customer_name'  => $pi->customer->company_name ?? '-',
            'date'           => $pi->created_at->format('Y-m-d'),
            'taxable_amount' => $pi->subtotal,
            'gst_amount'     => $pi->gst_amount,
            'grand_total'    => $pi->grand_total,
        ]);

        $summary = [
            'total_taxable' => $pis->sum('subtotal'),
            'total_gst'     => $pis->sum('gst_amount'),
            'total_value'   => $pis->sum('grand_total'),
        ];

        return response()->json(['success' => true, 'data' => $data, 'summary' => $summary]);
    }

    public function dispatch(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = PiMaster::with('customer')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        if ($companyId) $query->where('company_id', $companyId);
        if ($request->brand)       $query->where('brand', $request->brand);
        if ($request->customer_id) $query->where('customer_id', $request->customer_id);

        $pis = $query->orderBy('created_at', 'desc')->get();

        $data = $pis->map(fn($pi) => [
            'pi_number'         => $pi->pi_number,
            'customer_name'     => $pi->customer->company_name ?? '-',
            'status'            => $pi->status,
            'dispatched_at'     => $pi->dispatched_at,
            'transport_company' => $pi->transport_company,
            'vehicle_number'    => $pi->vehicle_number,
            'grand_total'       => $pi->grand_total,
        ]);

        $summary = [
            'total_dispatched'  => $pis->where('status', 'dispatched')->count(),
            'dispatched_value'  => $pis->where('status', 'dispatched')->sum('grand_total'),
            'payment_confirmed' => $pis->where('status', 'payment_confirmed')->count(),
            'payment_pending'   => $pis->where('status', 'ceo_approved')->count(),
        ];

        return response()->json(['success' => true, 'data' => $data, 'summary' => $summary]);
    }

    public function salespersonwise(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = PiMaster::select(
                DB::raw('COALESCE(NULLIF(salesperson_name,""), "Unassigned") as salesperson_name'),
                DB::raw('COUNT(*) as total_pi'),
                DB::raw('SUM(grand_total) as total_value')
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        if ($companyId) $query->where('company_id', $companyId);
        if ($request->brand)  $query->where('brand', $request->brand);
        if ($request->status) $query->where('status', $request->status);

        $data = $query->groupBy(DB::raw('COALESCE(NULLIF(salesperson_name,""), "Unassigned")'))
            ->orderBy('total_value', 'desc')
            ->get();

        $summary = [
            'total_pi'    => $data->sum('total_pi'),
            'total_value' => $data->sum('total_value'),
        ];

        return response()->json(['success' => true, 'data' => $data, 'summary' => $summary]);
    }
}

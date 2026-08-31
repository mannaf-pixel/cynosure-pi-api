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

    // 1. Date-wise PI Report
    public function datewise(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = PiMaster::with('customer')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        if ($companyId) $query->where('company_id', $companyId);

        $pis = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_pi'    => $pis->count(),
            'total_value' => $pis->sum('grand_total'),
            'approved'    => $pis->where('status', 'ceo_approved')->count(),
            'pending'     => $pis->whereIn('status', ['md_pending','ceo_pending'])->count(),
            'draft'       => $pis->where('status', 'draft')->count(),
            'rejected'    => $pis->where('status', 'rejected')->count(),
        ];

        return response()->json(['success' => true, 'data' => $pis, 'summary' => $summary]);
    }

    // 2. Customer-wise Report
    public function customerwise(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = PiMaster::select('customer_id',
                DB::raw('COUNT(*) as total_pi'),
                DB::raw('SUM(grand_total) as total_value'),
                DB::raw('SUM(CASE WHEN status="ceo_approved" THEN grand_total ELSE 0 END) as approved_value')
            )
            ->with('customer')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        if ($companyId) $query->where('company_id', $companyId);

        $data = $query->groupBy('customer_id')
            ->orderBy('total_value', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // 3. Product-wise Report
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
                DB::raw('SUM(pi_items.total_length) as total_length'),
                DB::raw('SUM(pi_items.total_weight) as total_weight'),
                DB::raw('SUM(pi_items.total_pieces) as total_pieces'),
                DB::raw('SUM(pi_items.line_total) as total_value'),
                DB::raw('COUNT(DISTINCT pi_master.id) as pi_count')
            )
            ->whereBetween(DB::raw('DATE(pi_master.created_at)'), [$from, $to]);

        if ($companyId) $query->where('pi_master.company_id', $companyId);

        $data = $query->groupBy('pi_items.product_code_snap', 'pi_items.product_name_snap')
            ->orderBy('total_value', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // 4. Monthly Sales Summary
    public function monthly(Request $request)
    {
        $companyId = $this->getCompanyId();
        $year = $request->year ?? date('Y');

        $query = PiMaster::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('MONTHNAME(created_at) as month_name'),
                DB::raw('COUNT(*) as total_pi'),
                DB::raw('SUM(grand_total) as total_value'),
                DB::raw('SUM(CASE WHEN status="ceo_approved" THEN grand_total ELSE 0 END) as approved_value')
            )
            ->whereYear('created_at', $year);

        if ($companyId) $query->where('company_id', $companyId);

        $data = $query->groupBy(DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
            ->orderBy('month')
            ->get();

        return response()->json(['success' => true, 'data' => $data, 'year' => $year]);
    }

    // 5. Status-wise Report
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

        $data = $query->groupBy('status')->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // 8. Salesperson-wise Report
    public function salespersonwise(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = PiMaster::select(
                DB::raw('COALESCE(NULLIF(salesperson_name,""), "Unassigned") as salesperson_label'),
                DB::raw('COUNT(*) as total_pi'),
                DB::raw('SUM(grand_total) as total_value'),
                DB::raw('SUM(CASE WHEN status IN ("ceo_approved","payment_confirmed","dispatched") THEN grand_total ELSE 0 END) as approved_value'),
                DB::raw('SUM(CASE WHEN status="dispatched" THEN grand_total ELSE 0 END) as dispatched_value'),
                DB::raw('SUM(CASE WHEN status="payment_confirmed" THEN grand_total ELSE 0 END) as payment_confirmed_value'),
                DB::raw('SUM(CASE WHEN status IN ("md_pending","ceo_pending") THEN grand_total ELSE 0 END) as pending_value'),
                DB::raw('SUM(CASE WHEN status="rejected" THEN grand_total ELSE 0 END) as rejected_value'),
                DB::raw('SUM(CASE WHEN status="draft" THEN grand_total ELSE 0 END) as draft_value'),
                DB::raw('COUNT(CASE WHEN status IN ("ceo_approved","payment_confirmed","dispatched") THEN 1 END) as approved_count'),
                DB::raw('COUNT(CASE WHEN status="dispatched" THEN 1 END) as dispatched_count'),
                DB::raw('COUNT(CASE WHEN status IN ("md_pending","ceo_pending") THEN 1 END) as pending_count'),
                DB::raw('COUNT(CASE WHEN status="rejected" THEN 1 END) as rejected_count')
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        if ($companyId) $query->where('company_id', $companyId);

        $data = $query->groupBy(DB::raw('COALESCE(NULLIF(salesperson_name,""), "Unassigned")'))
            ->orderBy('total_value', 'desc')
            ->get();

        // PI list per salesperson
        $piQuery = PiMaster::with('customer')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);
        if ($companyId) $piQuery->where('company_id', $companyId);
        $allPis = $piQuery->orderBy('created_at','desc')->get();

        $pisBySalesperson = $allPis->groupBy(function($pi) {
            return $pi->salesperson_name ?: 'Unassigned';
        });

        $data = $data->map(function($row) use ($pisBySalesperson) {
            $row->pis = $pisBySalesperson->get($row->salesperson_label, collect());
            return $row;
        });

        $summary = [
            'total_salespersons' => $data->count(),
            'total_pi'           => $data->sum('total_pi'),
            'total_value'        => $data->sum('total_value'),
            'approved_value'     => $data->sum('approved_value'),
            'dispatched_value'   => $data->sum('dispatched_value'),
        ];

        return response()->json(['success' => true, 'data' => $data, 'summary' => $summary]);
    }

    // 7. Dispatch Report
    public function dispatch(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = PiMaster::with('customer')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereIn('status', ['payment_confirmed', 'dispatched', 'ceo_approved']);

        if ($companyId) $query->where('company_id', $companyId);

        $pis = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_dispatched'         => $pis->where('status', 'dispatched')->count(),
            'dispatched_value'         => $pis->where('status', 'dispatched')->sum('grand_total'),
            'payment_confirmed'        => $pis->where('status', 'payment_confirmed')->count(),
            'payment_pending'          => $pis->where('status', 'ceo_approved')->count(),
        ];

        return response()->json(['success' => true, 'data' => $pis, 'summary' => $summary]);
    }

    // 6. GST Report
    public function gst(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to ?? now()->toDateString();

        $query = PiMaster::with('customer')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereIn('status', ['ceo_approved']);

        if ($companyId) $query->where('company_id', $companyId);

        $pis = $query->orderBy('created_at')->get();

        $summary = [
            'total_taxable' => $pis->sum('subtotal'),
            'total_gst'     => $pis->sum('gst_amount'),
            'total_value'   => $pis->sum('grand_total'),
        ];

        return response()->json(['success' => true, 'data' => $pis, 'summary' => $summary]);
    }
}

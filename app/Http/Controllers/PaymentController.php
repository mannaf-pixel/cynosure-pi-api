<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PiMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private function getCompanyId()
    {
        $user = auth('api')->user();
        if ($user->is_super_admin) {
            return request()->header('X-Company-Id') ?? request()->query('company_id');
        }
        return $user->company_id;
    }

    // List all payments
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $query = Payment::with('pi', 'customer', 'creator')
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($companyId) $query->where('company_id', $companyId);

        if ($request->pi_id) $query->where('pi_id', $request->pi_id);
        if ($request->customer_id) $query->where('customer_id', $request->customer_id);
        if ($request->from) $query->where('payment_date', '>=', $request->from);
        if ($request->to) $query->where('payment_date', '<=', $request->to);

        $payments = $query->get();

        $summary = [
            'total_received' => $payments->sum('amount'),
            'total_count'    => $payments->count(),
        ];

        return response()->json(['success' => true, 'data' => $payments, 'summary' => $summary]);
    }

    // Add payment against PI
    public function store(Request $request)
    {
        $request->validate([
            'pi_id'        => 'required|exists:pi_master,id',
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,phonepay,upi,bank_transfer,other',
        ]);

        $companyId = $this->getCompanyId();
        $pi = PiMaster::findOrFail($request->pi_id);

        $payment = Payment::create([
            'company_id'     => $companyId,
            'pi_id'          => $request->pi_id,
            'customer_id'    => $pi->customer_id,
            'payment_date'   => $request->payment_date,
            'amount'         => $request->amount,
            'payment_mode'   => $request->payment_mode,
            'received_in'    => $request->received_in,
            'transaction_id' => $request->transaction_id,
            'note'           => $request->note,
            'created_by'     => auth('api')->id(),
        ]);

        return response()->json(['success' => true, 'data' => $payment->load('pi', 'customer')], 201);
    }

    // PI ke against payments + balance
    public function piPayments($piId)
    {
        $pi = PiMaster::with('customer')->findOrFail($piId);
        $payments = Payment::with('creator')
            ->where('pi_id', $piId)
            ->orderBy('payment_date')
            ->get();

        $totalReceived = $payments->sum('amount');
        $balance       = $pi->grand_total - $totalReceived;

        return response()->json([
            'success'        => true,
            'pi'             => $pi,
            'payments'       => $payments,
            'total_received' => $totalReceived,
            'balance'        => $balance,
            'grand_total'    => $pi->grand_total,
        ]);
    }

    // Customer ledger
    public function customerLedger($customerId)
    {
        $companyId = $this->getCompanyId();

        $pis = PiMaster::where('customer_id', $customerId)
            ->where('company_id', $companyId)
            ->with('items')
            ->orderBy('created_at')
            ->get();

        $ledger = $pis->map(function($pi) {
            $received = Payment::where('pi_id', $pi->id)->sum('amount');
            return [
                'pi_number'      => $pi->pi_number,
                'pi_id'          => $pi->id,
                'date'           => $pi->created_at->format('d-M-Y'),
                'grand_total'    => $pi->grand_total,
                'total_received' => $received,
                'balance'        => $pi->grand_total - $received,
                'status'         => $pi->status,
            ];
        });

        $totals = [
            'total_pi_value' => $ledger->sum('grand_total'),
            'total_received' => $ledger->sum('total_received'),
            'total_balance'  => $ledger->sum('balance'),
        ];

        return response()->json([
            'success' => true,
            'data'    => $ledger,
            'totals'  => $totals,
        ]);
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return response()->json(['success' => true, 'message' => 'Payment deleted.']);
    }
}

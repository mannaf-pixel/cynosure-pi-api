<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PIController;
use App\Http\Controllers\PDFController;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('/v1/ping', function () {
    return response()->json([
        'message' => 'Cynosure PI API is running',
        'version' => '1.0',
        'time'    => now()->toDateTimeString(),
    ]);
});

// Auth Routes
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Routes
Route::prefix('v1')->middleware('auth:api')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    Route::get('/dashboard/summary', function () {
        $user      = auth('api')->user();
        $companyId = null;

        if ($user->is_super_admin) {
            $companyId = request()->header('X-Company-Id') ?? request()->query('company_id');
        } else {
            $companyId = $user->company_id;
        }

        $query = \App\Models\PiMaster::query();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $total    = (clone $query)->count();
        $pending  = (clone $query)->whereIn('status', ['md_pending','ceo_pending'])->count();
        $approved = (clone $query)->where('status', 'ceo_approved')->count();
        $rejected = (clone $query)->where('status', 'rejected')->count();
        $monthly  = (clone $query)->where('status', 'ceo_approved')
                        ->whereMonth('created_at', date('m'))
                        ->sum('grand_total');

        return response()->json([
            'success' => true,
            'data'    => [
                'total_pi'      => $total,
                'pending'       => $pending,
                'approved'      => $approved,
                'rejected'      => $rejected,
                'monthly_value' => $monthly,
            ],
        ]);
    });

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', function () {
            $companyId = request()->header('X-Company-Id');
            $users = \App\Models\User::where('company_id', $companyId)
                ->whereIn('role', ['pi_creator', 'admin'])
                ->where('is_active', true)
                ->get(['id', 'name', 'email', 'role']);
            return response()->json(['success' => true, 'data' => $users]);
        });
    });

    // Products
    Route::get('/products',               [ProductController::class, 'index']);
    Route::post('/products',              [ProductController::class, 'store'])->middleware('role:admin');
    Route::get('/products/{id}',          [ProductController::class, 'show']);
    Route::put('/products/{id}',          [ProductController::class, 'update'])->middleware('role:admin');
    Route::patch('/products/{id}/toggle', [ProductController::class, 'toggle'])->middleware('role:admin');

    // Customers
    Route::get('/customers',      [CustomerController::class, 'index']);
    Route::post('/customers',     [CustomerController::class, 'store'])->middleware('role:admin,pi_creator');
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->middleware('role:admin,pi_creator');

    // Proforma Invoices
    Route::get('/pi',                [PIController::class, 'index']);
    Route::post('/pi',               [PIController::class, 'store'])->middleware('role:admin,pi_creator');
    Route::get('/pi/{id}',           [PIController::class, 'show']);
    Route::put('/pi/{id}',           [PIController::class, 'update'])->middleware('role:admin,pi_creator');
    Route::post('/pi/{id}/submit',   [PIController::class, 'submit'])->middleware('role:admin,pi_creator');
    Route::delete('/pi/{id}',        [PIController::class, 'destroy'])->middleware('role:admin,pi_creator');
    Route::post('/pi/{id}/approve',         [PIController::class, 'approve'])->middleware('role:md,ceo,admin');
    Route::post('/pi/{id}/confirm-payment', [PIController::class, 'confirmPayment'])->middleware('role:admin,ceo');
    Route::post('/pi/{id}/dispatch',        [PIController::class, 'dispatch'])->middleware('role:admin,dispatch_manager');
    Route::post('/pi/{id}/reject',   [PIController::class, 'reject'])->middleware('role:md,ceo,admin');

    // PDF
    Route::get('/pi/{id}/pdf', [PDFController::class, 'generatePI']);

    // Notifications
    Route::get('/notifications',           [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read',[\App\Http\Controllers\NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead']);
    Route::get('/pending-pis',             [\App\Http\Controllers\NotificationController::class, 'pendingPIs']);

    // Payments
    Route::get('/payments',                    [\App\Http\Controllers\PaymentController::class, 'index']);
    Route::post('/payments',                   [\App\Http\Controllers\PaymentController::class, 'store']);
    Route::get('/payments/pi/{piId}',          [\App\Http\Controllers\PaymentController::class, 'piPayments']);
    Route::get('/payments/customer/{customerId}', [\App\Http\Controllers\PaymentController::class, 'customerLedger']);
    Route::delete('/payments/{id}',            [\App\Http\Controllers\PaymentController::class, 'destroy']);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/datewise',    [\App\Http\Controllers\ReportController::class, 'datewise']);
        Route::get('/customerwise',[\App\Http\Controllers\ReportController::class, 'customerwise']);
        Route::get('/productwise', [\App\Http\Controllers\ReportController::class, 'productwise']);
        Route::get('/monthly',     [\App\Http\Controllers\ReportController::class, 'monthly']);
        Route::get('/statuswise',  [\App\Http\Controllers\ReportController::class, 'statuswise']);
        Route::get('/gst',         [\App\Http\Controllers\ReportController::class, 'gst']);
        Route::get('/dispatch',       [\App\Http\Controllers\ReportController::class, 'dispatch']);
        Route::get('/salespersonwise', [\App\Http\Controllers\ReportController::class, 'salespersonwise']);
    });

    // Hardware Products
    Route::get('/hardware',              [\App\Http\Controllers\HardwareProductController::class, 'index']);
    Route::post('/hardware',             [\App\Http\Controllers\HardwareProductController::class, 'store'])->middleware('role:admin');
    Route::put('/hardware/{id}',         [\App\Http\Controllers\HardwareProductController::class, 'update'])->middleware('role:admin');
    Route::delete('/hardware/{id}',      [\App\Http\Controllers\HardwareProductController::class, 'destroy'])->middleware('role:admin');
});

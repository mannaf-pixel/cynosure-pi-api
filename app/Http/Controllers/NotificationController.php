<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
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
        $user = auth('api')->user();
        $notifications = Notification::where('user_id', $user->id)
            ->with('pi')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success'      => true,
            'data'         => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        $user = auth('api')->user();
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    // Pending PIs for MD/CEO
    public function pendingPIs()
    {
        $user      = auth('api')->user();
        $companyId = $this->getCompanyId();

        if ($user->role === 'md') {
            $status = 'md_pending';
        } elseif ($user->role === 'ceo' || $user->role === 'admin') {
            $status = 'ceo_pending';
        } else {
            return response()->json(['success' => true, 'data' => []]);
        }

        $pis = \App\Models\PiMaster::with('customer', 'creator')
            ->where('company_id', $companyId)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $pis]);
    }
}

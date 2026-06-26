<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\EMediaLog;
use Illuminate\Http\Request;

class MediaLogsController extends Controller
{
    /**
     * Get activity logs with filters
     */
    public function index(Request $request)
    {
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $actionType = $request->query('action_type');
        $userId = $request->query('user_id');
        $perPage = (int) $request->query('per_page', 50);

        $query = EMediaLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        if ($actionType) {
            $query->where('action_type', $actionType);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $logs = $query->paginate($perPage);

        // Transform for display
        $logs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'action_type' => $log->action_type,
                'action_label' => $log->action_label,
                'target_type' => $log->target_type,
                'target_path' => $log->target_path,
                'target_name' => $log->target_name,
                'description' => $log->description,
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                ] : null,
                'created_at' => $log->created_at->format('H:i - d/m/Y'),
            ];
        });

        return response()->json($logs);
    }
}

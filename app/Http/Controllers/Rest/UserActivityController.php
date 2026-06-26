<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use App\Support\ApiResponse;

class UserActivityController extends Controller
{
    /**
     * Get user activity statistics.
     */
    public function stats()
    {
        return ApiResponse::success(UserActivity::getStats());
    }
}

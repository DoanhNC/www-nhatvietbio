<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EMediaLog extends Model
{
    protected $table = 'e_media_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action_type',
        'target_type',
        'target_path',
        'target_name',
        'old_path',
        'details',
        'created_at',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Log an action
     */
    public static function logAction(
        string $actionType,
        string $targetType,
        string $targetPath,
        ?string $targetName = null,
        ?string $oldPath = null,
        ?array $details = null
    ): self {
        return self::create([
            'user_id' => Auth::id(),
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_path' => $targetPath,
            'target_name' => $targetName,
            'old_path' => $oldPath,
            'details' => $details,
            'created_at' => now(),
        ]);
    }

    /**
     * Get action type label in Vietnamese
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action_type) {
            'upload' => 'Tải lên',
            'create_folder' => 'Tạo thư mục',
            'rename' => 'Đổi tên',
            'move' => 'Di chuyển',
            'delete' => 'Xóa',
            'restore' => 'Khôi phục',
            'force_delete' => 'Xóa vĩnh viễn',
            'empty_trash' => 'Dọn dẹp thùng rác',
            default => $this->action_type,
        };
    }

    /**
     * Get formatted action description
     */
    public function getDescriptionAttribute(): string
    {
        $userName = $this->user?->name ?? 'Unknown';

        return match ($this->action_type) {
            'upload' => "{$userName} đã đăng tải tệp {$this->target_path}",
            'create_folder' => "{$userName} đã tạo thư mục {$this->target_path}",
            'rename' => "{$userName} đã đổi tên {$this->old_path} thành {$this->target_path}",
            'move' => "{$userName} đã di chuyển {$this->old_path} đến {$this->target_path}",
            'delete' => "{$userName} đã xóa {$this->target_path}",
            'restore' => "{$userName} đã khôi phục {$this->target_path}",
            'force_delete' => "{$userName} đã xóa vĩnh viễn {$this->target_path}",
            'empty_trash' => "{$userName} đã dọn dẹp thùng rác",
            default => "{$userName} đã thực hiện {$this->action_type} trên {$this->target_path}",
        };
    }
}

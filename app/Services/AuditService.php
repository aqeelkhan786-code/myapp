<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit event
     *
     * @param string $action The action being logged (e.g., 'conflict_override', 'ical_sync')
     * @param array $changes Additional data about the change
     * @param int|null $userId User ID (defaults to authenticated user)
     * @param Model|null $auditable The model being audited (Booking, Room, etc.)
     * @param string|null $description Human-readable description
     * @return AuditLog
     */
    public static function log(
        string $action,
        array $changes = [],
        ?int $userId = null,
        ?Model $auditable = null,
        ?string $description = null
    ): AuditLog {
        $userId = $userId ?? Auth::id();
        
        $auditLog = AuditLog::create([
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable ? $auditable->id : null,
            'user_id' => $userId,
            'changes' => $changes,
            'description' => $description ?? self::generateDescription($action, $changes, $auditable),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
        
        return $auditLog;
    }

    /**
     * Generate a human-readable description from action and changes
     */
    protected static function generateDescription(string $action, array $changes, ?Model $auditable): string
    {
        $descriptions = [
            'conflict_override' => 'Booking conflict was overridden',
            'ical_sync' => sprintf(
                'iCal sync for %s: %d imported, %d updated, %d cancelled',
                $changes['room_name'] ?? 'room',
                $changes['imported'] ?? 0,
                $changes['updated'] ?? 0,
                $changes['cancelled'] ?? 0
            ),
            'booking_created' => 'Booking was created',
            'booking_updated' => 'Booking was updated',
            'booking_deleted' => 'Booking was deleted',
            'booking_status_changed' => sprintf(
                'Booking status changed to %s',
                $changes['new_status'] ?? 'unknown'
            ),
        ];

        return $descriptions[$action] ?? ucfirst(str_replace('_', ' ', $action));
    }
}













<?php

namespace Modules\Setting\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperationsAuditLogger
{
    /** @param array<string, mixed> $metadata */
    public function record(
        Request $request,
        string $action,
        string $targetType,
        string|int|null $targetId = null,
        array $metadata = []
    ): void {
        if (! Schema::hasTable('admin_operation_audits')) {
            return;
        }

        DB::table('admin_operation_audits')->insert([
            'user_id' => $request->user()?->id,
            'action' => mb_substr($action, 0, 80),
            'target_type' => mb_substr($targetType, 0, 80),
            'target_id' => $targetId === null ? null : mb_substr((string) $targetId, 0, 191),
            'metadata' => $metadata === [] ? null : json_encode($metadata),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }
}

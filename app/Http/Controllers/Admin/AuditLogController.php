<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SharesAdminNavigation;
use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    use SharesAdminNavigation;

    public function index(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        $entries = AdminAuditLog::query()
            ->with('admin:id,name,email')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('admin/AuditLog', [
            ...$this->adminPanelProps(),
            'entries' => collect($entries->items())->map(fn (AdminAuditLog $entry): array => [
                'id' => $entry->id,
                'action' => $entry->action,
                'adminName' => $entry->admin?->name ?? 'System',
                'adminEmail' => $entry->admin?->email,
                'targetLabel' => $entry->target_label,
                'meta' => $entry->meta,
                'ipAddress' => $entry->ip_address,
                'createdAt' => $entry->created_at?->toIso8601String(),
            ])->values()->all(),
            'pagination' => [
                'currentPage' => $entries->currentPage(),
                'lastPage' => $entries->lastPage(),
                'total' => $entries->total(),
                'prevPageUrl' => $entries->previousPageUrl(),
                'nextPageUrl' => $entries->nextPageUrl(),
            ],
        ]);
    }
}

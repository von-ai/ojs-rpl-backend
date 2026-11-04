<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ProductionEscalation;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class EditorialMediationService
{
    public function resolve(ProductionEscalation $escalation, User $actor, string $role, string $resolution): ProductionEscalation
    {
        return DB::transaction(function () use ($escalation, $actor, $role, $resolution) {
            abort_unless(in_array($role, ['editor_in_chief', 'section_editor'], true), 403);
            PortalAccess::lockSubmission($escalation->submission, $actor, $role);
            $escalation = ProductionEscalation::query()->lockForUpdate()->findOrFail($escalation->id);
            if (! in_array($escalation->status, ['open', 'investigating'], true) || trim($resolution) === '') {
                throw new DomainException('Tiket sudah diselesaikan atau catatan penyelesaian belum diisi.');
            }
            $escalation->update(['status' => 'resolved', 'resolution' => trim($resolution), 'resume_stage' => $escalation->copyedit_id ? 'in_progress' : 'layout', 'version' => $escalation->version + 1]);
            AuditLog::record(['actor_id' => $actor->id, 'actor_type' => 'user', 'effective_role' => $role, 'action' => 'production.escalation.resolved', 'resource_type' => 'production_escalation', 'resource_id' => $escalation->id, 'reason' => trim($resolution)]);

            return $escalation;
        });
    }
}

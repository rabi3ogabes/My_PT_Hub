<?php

namespace App\Domain\Tenancy\Services;

use App\Domain\Billing\Services\PlanGate;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Invitation;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Services\ProgramAssigner;
use App\Notifications\InvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

final class InvitationService
{
    public function __construct(
        private readonly MembershipService $memberships,
        private readonly PlanGate $gate,
        private readonly TenantContext $context,
    ) {}

    /**
     * @param  array{email?: ?string, phone?: ?string, name?: ?string, role?: string, payload?: array<string, mixed>, send?: bool}  $data
     */
    public function create(Tenant $tenant, User $inviter, array $data): Invitation
    {
        $role = $data['role'] ?? 'client';
        $role === 'client' ? $this->gate->assertCanAddClient($tenant) : $this->gate->assertCanAddStaff($tenant);

        $invitation = $this->context->run($tenant, fn () => Invitation::create([
            'invited_by' => $inviter->id,
            'code' => strtoupper(Str::random(10)),
            'email' => isset($data['email']) ? strtolower(trim($data['email'])) : null,
            'phone' => $data['phone'] ?? null,
            'name' => $data['name'] ?? null,
            'role' => $role,
            'payload' => $data['payload'] ?? null,
            'expires_at' => now()->addDays(14),
        ]));

        if (($data['send'] ?? true) && $invitation->email) {
            Notification::route('mail', $invitation->email)->notify(new InvitationNotification($invitation, $tenant, $inviter));
        }

        return $invitation;
    }

    public function findUsable(string $code): Invitation
    {
        $invitation = Invitation::withoutGlobalScopes()->where('code', strtoupper($code))->first();
        if (! $invitation || ! $invitation->isUsable()) {
            throw new ApiException('invitation_invalid', __('api.invitation_invalid'), 410);
        }

        return $invitation;
    }

    public function accept(string $code, User $user): Invitation
    {
        $invitation = $this->findUsable($code);
        if ($invitation->email && strtolower($user->email) !== $invitation->email) {
            throw new ApiException('invitation_email_mismatch', __('api.invitation_email_mismatch'), 403);
        }

        $tenant = Tenant::findOrFail($invitation->tenant_id);

        return DB::transaction(function () use ($invitation, $user, $tenant) {
            $this->memberships->add($tenant, $user, $invitation->role, coachId: $invitation->invited_by);
            $invitation->forceFill(['accepted_at' => now(), 'accepted_by' => $user->id])->save();
            $user->forceFill(['current_tenant_id' => $tenant->id])->save();

            $programId = $invitation->payload['program_id'] ?? null;
            if ($programId && $invitation->role === 'client') {
                $this->context->run($tenant, fn () => app(ProgramAssigner::class)->assignById($programId, $user, now()->toDateString(), $invitation->invited_by));
            }

            event(new \App\Domain\Tenancy\Events\ClientJoined($tenant, $user));

            return $invitation;
        });
    }

    /**
     * Bulk CSV import: columns name,email,phone. Returns [created, skipped, errors].
     *
     * @return array{created: int, skipped: int, errors: list<string>}
     */
    public function importCsv(Tenant $tenant, User $inviter, string $csv): array
    {
        $rows = array_map('str_getcsv', preg_split('/\r\n|\n|\r/', trim($csv)) ?: []);
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), array_shift($rows) ?? []);
        $created = 0;
        $skipped = 0;
        $errors = [];
        foreach ($rows as $i => $row) {
            if (count(array_filter($row)) === 0) {
                continue;
            }
            $data = array_combine(array_slice($header, 0, count($row)), array_slice($row, 0, count($header))) ?: [];
            $email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: null;
            if (! $email && empty($data['phone'])) {
                $errors[] = __('clients.import_row_invalid', ['row' => $i + 2]);

                continue;
            }
            if ($email && Invitation::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('email', strtolower($email))->whereNull('accepted_at')->exists()) {
                $skipped++;

                continue;
            }
            try {
                $this->create($tenant, $inviter, ['email' => $email, 'phone' => $data['phone'] ?? null, 'name' => $data['name'] ?? null]);
                $created++;
            } catch (ApiException $e) {
                $errors[] = $e->getMessage();
                break;
            }
        }

        return ['created' => $created, 'skipped' => $skipped, 'errors' => $errors];
    }
}

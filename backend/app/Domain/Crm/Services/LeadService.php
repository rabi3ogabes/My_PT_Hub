<?php

namespace App\Domain\Crm\Services;

use App\Domain\Crm\Models\Lead;
use App\Domain\Crm\Models\LeadActivity;
use App\Domain\Crm\Models\LeadStage;
use App\Domain\Identity\Models\User;
use App\Domain\Payments\Models\Product;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\InvitationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** CRM pipeline: customizable stages, activities, and one-click lead → client conversion. */
final class LeadService
{
    public function __construct(private readonly InvitationService $invitations) {}

    /** @return Collection<int, LeadStage> */
    public function stages(): Collection
    {
        return LeadStage::query()->orderBy('position')->get();
    }

    /**
     * Replaces the pipeline. Stages omitted from the payload are removed; their leads move to the first stage.
     *
     * @param  list<array{id?: ?string, name: string, kind?: ?string}>  $stages
     * @return Collection<int, LeadStage>
     */
    public function saveStages(array $stages): Collection
    {
        return DB::transaction(function () use ($stages) {
            $keep = [];
            foreach (array_values($stages) as $position => $data) {
                $stage = ! empty($data['id']) ? LeadStage::query()->find($data['id']) : null;
                if (! empty($data['id']) && ! $stage) {
                    throw ApiException::invalid('validation_failed', __('crm_module.errors.unknown_stage'), ["stages.$position.id" => [__('crm_module.errors.unknown_stage')]]);
                }
                $stage ??= new LeadStage;
                $stage->fill(['name' => $data['name'], 'position' => $position, 'kind' => $data['kind'] ?? 'open'])->save();
                $keep[] = $stage->id;
            }
            $removed = LeadStage::query()->whereNotIn('id', $keep)->pluck('id');
            if ($removed->isNotEmpty()) {
                Lead::withTrashed()->whereIn('lead_stage_id', $removed)->update(['lead_stage_id' => $keep[0] ?? null]);
                LeadStage::query()->whereIn('id', $removed)->delete();
            }

            return $this->stages();
        });
    }

    public function defaultStage(): ?LeadStage
    {
        return LeadStage::query()->where('kind', 'open')->orderBy('position')->first() ?? LeadStage::query()->orderBy('position')->first();
    }

    /** Moves the lead and records a stage activity (null when it already was in that stage). */
    public function moveToStage(Lead $lead, LeadStage $stage, ?User $by): ?LeadActivity
    {
        if ($lead->lead_stage_id === $stage->id) {
            return null;
        }
        $from = $lead->stage?->name;
        $lead->update(['lead_stage_id' => $stage->id]);
        $lead->setRelation('stage', $stage);

        return LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $by?->id,
            'type' => 'stage',
            'body' => __('crm_module.activity.stage_changed', ['from' => $from ?? '—', 'to' => $stage->name]),
        ]);
    }

    /**
     * @param  array{type: string, body?: ?string, due_at?: ?string, completed?: ?bool, lead_stage_id?: ?string}  $data
     */
    public function addActivity(Lead $lead, array $data, User $by): LeadActivity
    {
        if ($data['type'] === 'stage') {
            $stage = LeadStage::query()->findOrFail($data['lead_stage_id'] ?? null);

            return $this->moveToStage($lead, $stage, $by) ?? LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $by->id,
                'type' => 'stage',
                'body' => __('crm_module.activity.stage_changed', ['from' => $stage->name, 'to' => $stage->name]),
            ]);
        }

        return LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $by->id,
            'type' => $data['type'],
            'body' => $data['body'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'completed_at' => ! empty($data['completed']) ? now() : null,
        ]);
    }

    /**
     * Converts a lead: sends a client invitation and optionally a checkout link for a product.
     *
     * @return array{lead: Lead, invitation_code: string, checkout_url: ?string}
     */
    public function convert(Tenant $tenant, Lead $lead, User $by, ?Product $product = null, ?string $programId = null): array
    {
        if (! $lead->email) {
            throw ApiException::invalid('validation_failed', __('crm_module.errors.email_required'), ['email' => [__('crm_module.errors.email_required')]]);
        }

        return DB::transaction(function () use ($tenant, $lead, $by, $product, $programId) {
            $invitation = $this->invitations->create($tenant, $by, [
                'email' => $lead->email,
                'phone' => $lead->phone,
                'name' => $lead->name,
                'role' => 'client',
                'payload' => array_filter(['lead_id' => $lead->id, 'program_id' => $programId]),
            ]);

            $checkoutUrl = $product
                ? url('checkout/'.$tenant->slug.'/'.$product->slug).'?'.http_build_query(['email' => $lead->email, 'name' => $lead->name])
                : null;

            $won = LeadStage::query()->where('kind', 'won')->orderBy('position')->first();
            if ($won) {
                $this->moveToStage($lead, $won, $by);
            }
            $existing = User::query()->where('email', strtolower($lead->email))->first();
            $lead->update(['converted_at' => now(), 'converted_user_id' => $existing?->id]);
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $by->id,
                'type' => 'email',
                'body' => __('crm_module.activity.converted', ['email' => $lead->email]),
            ]);

            return ['lead' => $lead->refresh(), 'invitation_code' => $invitation->code, 'checkout_url' => $checkoutUrl];
        });
    }
}

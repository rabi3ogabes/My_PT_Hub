<?php

namespace App\Http\Api\V1\Payments;

use App\Domain\Identity\Models\User;
use App\Domain\Payments\Models\CommissionRule;
use App\Domain\Payments\Services\AnalyticsService;
use App\Domain\Payments\Support\Permission;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends ApiController
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    /** @return array{0: Carbon, 1: Carbon} */
    private function range(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $tz = $this->tenant()->timezone ?: 'UTC';
        $from = isset($data['from']) ? Carbon::parse($data['from'], $tz)->startOfDay() : now($tz)->startOfMonth();
        $to = isset($data['to']) ? Carbon::parse($data['to'], $tz)->endOfDay() : now($tz)->endOfDay();

        return [$from->utc(), $to->utc()];
    }

    public function overview(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'analytics.view');
        [$from, $to] = $this->range($request);

        return $this->ok($this->analytics->overview($this->tenant(), $from, $to));
    }

    public function retention(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'analytics.view');
        $months = (int) ($request->validate(['months' => ['nullable', 'integer', 'min:1', 'max:24']])['months'] ?? 12);

        return $this->ok($this->analytics->retentionCohorts($this->tenant(), $months));
    }

    public function coaches(Request $request): JsonResponse
    {
        Permission::assert($this->user(), 'analytics.view');
        [$from, $to] = $this->range($request);

        return $this->ok($this->analytics->coaches($this->tenant(), $from, $to), ['from' => $from->toIso8601String(), 'to' => $to->toIso8601String()]);
    }

    /** CSV streamed for accounting (payments) or payouts (coaches). */
    public function export(Request $request): StreamedResponse
    {
        Permission::assert($this->user(), 'analytics.view');
        $type = $request->validate(['type' => ['nullable', Rule::in(['payments', 'coaches'])]])['type'] ?? 'payments';
        [$from, $to] = $this->range($request);
        $tenant = $this->tenant();
        $rows = $this->analytics->exportRows($tenant, $type, $from, $to);
        $filename = sprintf('%s-%s-%s_%s.csv', $tenant->slug, $type, $from->toDateString(), $to->toDateString());

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so spreadsheets render Arabic correctly
            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'".$v : $v, $row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Owner sets a coach's commission: percent of package revenue (basis points) or a fixed amount per session. */
    public function saveCommission(Request $request): JsonResponse
    {
        $data = $request->validate([
            'coach_id' => ['required', 'string', 'size:26'],
            'type' => ['required', Rule::in(['percent_package', 'per_session'])],
            'value' => ['required', 'integer', 'min:0', 'max:100000000'],
        ]);
        if ($data['type'] === 'percent_package' && $data['value'] > 10000) {
            throw ApiException::invalid('validation_failed', __('payments.errors.commission_percent'), ['value' => [__('payments.errors.commission_percent')]]);
        }
        $coach = User::find($data['coach_id']);
        if (! $coach || ! in_array($coach->roleIn($this->tenant()->id), ['owner', 'coach'], true)) {
            throw ApiException::invalid('validation_failed', __('payments.errors.not_a_coach'), ['coach_id' => [__('payments.errors.not_a_coach')]]);
        }
        $rule = CommissionRule::query()->updateOrCreate(['coach_id' => $coach->id, 'type' => $data['type']], ['value' => $data['value']]);

        return $this->ok(['id' => $rule->id, 'coach_id' => $rule->coach_id, 'type' => $rule->type, 'value' => (int) $rule->value]);
    }
}

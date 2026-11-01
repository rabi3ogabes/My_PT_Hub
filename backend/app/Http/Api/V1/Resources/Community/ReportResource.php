<?php

namespace App\Http\Api\V1\Resources\Community;

use App\Domain\Community\Models\Comment;
use App\Domain\Community\Models\ContentReport;
use App\Domain\Community\Models\Post;
use App\Http\Api\V1\Resources\Messaging\UserSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContentReport */
class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this->reportable;

        return [
            'id' => $this->id,
            'type' => $this->reportable_type,
            'reportable_id' => $this->reportable_id,
            'reason' => $this->reason,
            'reason_label' => __('community_module.report_reasons.'.$this->reason),
            'status' => $this->status,
            'reporter' => UserSummary::make($this->reporter),
            'content' => $item instanceof Post || $item instanceof Comment ? [
                'body' => $item->body,
                'author' => UserSummary::make($item->author),
                'is_hidden' => (bool) $item->is_hidden,
                'deleted' => $item->trashed(),
                'post_id' => $item instanceof Comment ? $item->post_id : $item->id,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

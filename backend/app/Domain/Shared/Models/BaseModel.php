<?php

namespace App\Domain\Shared\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * Every model uses a ULID primary key which doubles as its public id.
 * Mass assignment is open because all writes go through validated
 * FormRequest/Action payloads, never raw request input.
 */
abstract class BaseModel extends Model
{
    use HasUlids;

    protected $guarded = [];

    /**
     * Store JSON with raw UTF-8 (not \uXXXX escapes) so translated content
     * (Arabic, accented French/Spanish) is searchable with LIKE on every driver.
     */
    protected function asJson($value, $flags = 0)
    {
        return parent::asJson($value, $flags | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

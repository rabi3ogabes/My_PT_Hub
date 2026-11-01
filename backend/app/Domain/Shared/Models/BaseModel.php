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
}

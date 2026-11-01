<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

/*
| Feature tests hit the HTTP layer with a fresh in-memory database.
| Unit tests are pure (no database) unless they opt in.
*/
pest()->extend(Tests\TestCase::class)->use(RefreshDatabase::class)->in('Feature');
pest()->extend(Tests\TestCase::class)->in('Unit');

/** Asserts a JSON API error envelope with the given code. */
function expectApiError(\Illuminate\Testing\TestResponse $response, int $status, string $code): void
{
    $response->assertStatus($status)->assertJsonPath('errors.0.code', $code);
}

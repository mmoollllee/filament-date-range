<?php

namespace CodeWithKyrian\FilamentDateRange\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * A record for the filter to find by its `starts_at`.
 */
class Appointment extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
    ];
}

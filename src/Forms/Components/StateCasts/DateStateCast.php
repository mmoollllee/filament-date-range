<?php

namespace CodeWithKyrian\FilamentDateRange\Forms\Components\StateCasts;

use Filament\Schemas\Components\StateCasts\Contracts\StateCast;

/**
 * State cast for the single date mode: one date string instead of a
 * start/end pair. A start/end pair (e.g. after switching the mode) keeps
 * its start date.
 */
class DateStateCast implements StateCast
{
    protected DateRangeStateCast $range;

    public function __construct(string $format, string $internalFormat, string $timezone)
    {
        $this->range = new DateRangeStateCast($format, $internalFormat, $timezone);
    }

    public function get(mixed $state): ?string
    {
        return $this->range->get(['start' => $this->extract($state), 'end' => null])['start'] ?? null;
    }

    public function set(mixed $state): ?string
    {
        return $this->range->set(['start' => $this->extract($state), 'end' => null])['start'] ?? null;
    }

    protected function extract(mixed $state): mixed
    {
        return is_array($state) ? ($state['start'] ?? null) : $state;
    }
}

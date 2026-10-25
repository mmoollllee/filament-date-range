<?php

namespace CodeWithKyrian\FilamentDateRange\Forms\Components\StateCasts;

use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;
use Illuminate\Support\Carbon;
use ValueError;

class DateRangeStateCast implements StateCast
{
    public function __construct(
        protected string $format,
        protected string $internalFormat,
        protected string $timezone,
    ) {
    }

    public function get(mixed $state): ?array
    {
        if (!is_array($state)) {
            return [
                'start' => null,
                'end' => null,
            ];
        }

        $start = $state['start'] ?? null;
        $end = $state['end'] ?? null;

        $start = $this->hydrate($start);
        $end = $this->hydrate($end);

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    public function set(mixed $state): ?array
    {
        if (!is_array($state)) {
            return null;
        }

        $start = $state['start'] ?? null;
        $end = $state['end'] ?? null;

        $start = $this->dehydrate($start);
        $end = $this->dehydrate($end);

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * The date one end of the raw state holds — as the browser writes it
     * (format) or as the server sets it (internal format) — in the app's
     * timezone. Null when it holds none, or anything that is no date.
     */
    public function toDate(mixed $date): ?CarbonInterface
    {
        $date = $this->parse($date, $this->format, $this->internalFormat);

        if ($date === null) {
            return null;
        }

        $date->shiftTimezone($this->timezone);

        return $date->setTimezone(config('app.timezone'));
    }

    protected function hydrate(mixed $date): ?string
    {
        return $this->toDate($date)?->format($this->format);
    }

    protected function dehydrate(mixed $date): ?string
    {
        $date = $this->parse($date, $this->format);

        if ($date === null) {
            return null;
        }

        $date = $date->setTimezone($this->timezone);

        return $date->format($this->internalFormat);
    }

    /**
     * Reads a date: an instance as a copy, a string in the first of the
     * formats that fits — "!" sets what a format leaves out to zero, so a day
     * is midnight, not the time of now — or else as Carbon parses it. A string
     * that names no date of the calendar ("2026-02-30", "2026-13-45") is no
     * date, never the one it would overflow into.
     */
    protected function parse(mixed $date, string ...$formats): ?Carbon
    {
        if (blank($date)) {
            return null;
        }

        if ($date instanceof DateTimeInterface) {
            return Carbon::instance($date);
        }

        if (!is_string($date)) {
            return null;
        }

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat('!' . $format, $date, config('app.timezone'));
            } catch (InvalidFormatException | ValueError) {
                continue;
            }

            if ($parsed !== null && !$this->hasParsingWarnings()) {
                return $parsed;
            }
        }

        try {
            $parsed = Carbon::parse($date, config('app.timezone'));
        } catch (InvalidFormatException) {
            return null;
        }

        return $this->hasParsingWarnings() ? null : $parsed;
    }

    /**
     * Whether the last parse overflowed: PHP reads "2026-02-30" as March 2nd
     * and only warns.
     */
    protected function hasParsingWarnings(): bool
    {
        $errors = Carbon::getLastErrors();

        return is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);
    }
}

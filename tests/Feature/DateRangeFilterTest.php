<?php

use CodeWithKyrian\FilamentDateRange\Tables\Filters\DateRangeFilter;
use CodeWithKyrian\FilamentDateRange\Tests\Fixtures\Appointment;
use CodeWithKyrian\FilamentDateRange\Tests\Fixtures\FilterTable;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::create('appointments', function (Blueprint $table): void {
        $table->id();
        $table->dateTime('starts_at');
    });

    Appointment::create(['starts_at' => '2026-11-02 10:00:00']);
    Appointment::create(['starts_at' => '2026-11-20 10:00:00']);
});

it('reads an end of the filter that is no date as unset', function (bool $hasTime, mixed $start): void {
    $table = FilterTable::test(fn (): DateRangeFilter => DateRangeFilter::make('starts_at')->withTime($hasTime))
        ->set('tableDeferredFilters.starts_at.starts_at', [
            'start' => $start,
            'end' => $hasTime ? '2026-11-10 12:00' : '2026-11-10',
        ])
        ->call('applyTableFilters')
        ->assertCountTableRecords(1);

    $indicators = array_map(
        fn (Indicator $indicator): string => (string) $indicator->getLabel(),
        array_values($table->instance()->getTable()->getFilter('starts_at')->getIndicators()),
    );

    expect($indicators)->toBe([$hasTime ? 'Starts at: Until Nov 10, 2026 12:00' : 'Starts at: Until Nov 10, 2026']);
})->with([
    'a day' => false,
    'a day and time' => true,
])->with([
    'a word' => 'morgen',
    'month 13' => '2026-13-45',
    'an array' => [['2026-11-02']],
]);

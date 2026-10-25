<?php

use CodeWithKyrian\FilamentDateRange\Forms\Components\DateRangePicker;
use CodeWithKyrian\FilamentDateRange\Forms\Components\StateCasts\DateRangeStateCast;
use CodeWithKyrian\FilamentDateRange\Tests\Fixtures\PickerForm;

beforeEach(function (): void {
    $this->travelTo('2026-10-24 10:30:00');
});

it('keeps a day the server sets as that day, without the time of now', function (): void {
    $form = PickerForm::test(fn (): array => [
        DateRangePicker::make('period')->default(['start' => '2026-11-02', 'end' => '2026-11-06']),
        DateRangePicker::make('day')->singleDate()->default('2026-11-02'),
    ]);

    expect($form->get('data'))->toBe([
        'period' => ['start' => '2026-11-02', 'end' => '2026-11-06'],
        'day' => '2026-11-02',
    ]);

    expect($form->call('save')->get('saved'))->toBe([
        'period' => ['start' => '2026-11-02', 'end' => '2026-11-06'],
        'day' => '2026-11-02',
    ]);
});

it('moves a day into no timezone', function (): void {
    $form = PickerForm::test(fn (): DateRangePicker => DateRangePicker::make('period')
        ->timezone('America/New_York')
        ->default(['start' => '2026-11-02', 'end' => '2026-11-06']));

    expect($form->get('data.period'))->toBe(['start' => '2026-11-02', 'end' => '2026-11-06']);

    expect($form->call('save')->get('saved.period'))->toBe(['start' => '2026-11-02', 'end' => '2026-11-06']);
});

it('moves a date with time into the timezone of the picker and back', function (): void {
    $form = PickerForm::test(fn (): DateRangePicker => DateRangePicker::make('period')
        ->withTime()
        ->timezone('Europe/Berlin')
        ->default(['start' => '2026-10-27 08:00', 'end' => '2026-10-27 17:30']));

    expect($form->get('data.period'))->toBe(['start' => '2026-10-27 09:00:00', 'end' => '2026-10-27 18:30:00']);

    expect($form->call('save')->get('saved.period'))->toBe(['start' => '2026-10-27 08:00', 'end' => '2026-10-27 17:30']);
});

it('reads a day in the format the browser writes it, and a day with a time of day as that day', function (): void {
    $cast = new DateRangeStateCast('d/m/Y', 'Y-m-d', 'UTC');

    expect($cast->get(['start' => '25/10/2026', 'end' => '2026-10-31 14:23:11']))
        ->toBe(['start' => '25/10/2026', 'end' => '31/10/2026']);
});

it('reads what is no date of the calendar as none, without throwing or overflowing into another date', function (string $state): void {
    $cast = new DateRangeStateCast('Y-m-d', 'Y-m-d', 'UTC');

    expect($cast->set(['start' => $state, 'end' => '2026-10-25']))->toBe(['start' => null, 'end' => '2026-10-25'])
        ->and($cast->get(['start' => $state, 'end' => '2026-10-25']))->toBe(['start' => null, 'end' => '2026-10-25']);
})->with([
    'February 30th' => '2026-02-30',
    'month 13' => '2026-13-45',
    'the zero date' => '0000-00-00',
    'no date at all' => 'soon',
]);

it('refuses a value that is no date, instead of saving none', function (bool $isSingleDate, mixed $state): void {
    PickerForm::test(fn (): DateRangePicker => DateRangePicker::make('date')->singleDate($isSingleDate))
        ->set('data.date', $state)
        ->call('save')
        ->assertHasFormErrors(['date' => 'The date field must be a valid date.'])
        ->assertSet('saved', null);
})->with([
    'an end on February 30th' => [false, ['start' => '2026-02-01', 'end' => '2026-02-30']],
    'a single date in month 13' => [true, '2026-13-45'],
]);

it('refuses a range that ends before it starts: by the day, with time by the minute', function (bool $hasTime, array $range, bool $isRefused): void {
    $form = PickerForm::test(fn (): DateRangePicker => DateRangePicker::make('period')->withTime($hasTime))
        ->set('data.period', $range)
        ->call('save');

    if ($isRefused) {
        $form->assertHasFormErrors(['period' => 'Period must not end before it starts.']);
    } else {
        $form->assertHasNoFormErrors();
    }
})->with([
    'a day earlier' => [false, ['start' => '2026-11-02', 'end' => '2026-11-01'], true],
    'on the same day' => [false, ['start' => '2026-11-02', 'end' => '2026-11-02'], false],
    'a minute earlier' => [true, ['start' => '2026-11-02 10:00', 'end' => '2026-11-02 09:59'], true],
    'seconds earlier in the same minute' => [true, ['start' => '2026-11-02 10:00:40', 'end' => '2026-11-02 10:00:10'], false],
]);

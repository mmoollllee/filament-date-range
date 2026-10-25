<?php

namespace CodeWithKyrian\FilamentDateRange\Tests\Fixtures;

use Closure;
use CodeWithKyrian\FilamentDateRange\Forms\Components\DateRangePicker;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * A form holding the pickers a test builds: filled on mount, its state
 * validated and dehydrated into `$saved` on save.
 */
class PickerForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /** @var Closure(): (DateRangePicker|array<DateRangePicker>) */
    public static Closure $pickers;

    /** @var array<string, mixed> */
    public array $data = [];

    /** @var array<string, mixed>|null */
    public ?array $saved = null;

    /**
     * @param  Closure(): (DateRangePicker|array<DateRangePicker>)  $pickers  called for each request, so each builds its own
     */
    public static function test(Closure $pickers): Testable
    {
        static::$pickers = $pickers;

        return Livewire::test(static::class);
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(Arr::wrap((static::$pickers)()))
            ->statePath('data');
    }

    public function save(): void
    {
        $this->saved = $this->form->getState();
    }

    public function render(): string
    {
        return '<div></div>';
    }
}

<?php

namespace CodeWithKyrian\FilamentDateRange\Tests\Fixtures;

use Closure;
use CodeWithKyrian\FilamentDateRange\Tables\Filters\DateRangeFilter;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * A table of appointments with the date range filter a test builds.
 */
class FilterTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    /** @var Closure(): DateRangeFilter */
    public static Closure $filter;

    /**
     * @param  Closure(): DateRangeFilter  $filter  called for each request, so each builds its own
     */
    public static function test(Closure $filter): Testable
    {
        static::$filter = $filter;

        return Livewire::test(static::class);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Appointment::query())
            ->columns([TextColumn::make('starts_at')])
            ->filters([(static::$filter)()]);
    }

    public function render(): string
    {
        return '<div></div>';
    }
}

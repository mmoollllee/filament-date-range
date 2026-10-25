<?php

namespace CodeWithKyrian\FilamentDateRange\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use CodeWithKyrian\FilamentDateRange\DateRangeServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Illuminate\Encryption\Encrypter;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FormsServiceProvider::class,
            LivewireServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            DateRangeServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Livewire signs the snapshots of the test components.
        $app['config']->set('app.key', 'base64:' . base64_encode(Encrypter::generateKey('AES-256-CBC')));
    }
}

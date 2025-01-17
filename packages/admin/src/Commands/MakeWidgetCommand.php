<?php

namespace Filament\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeWidgetCommand extends Command
{
    use Concerns\CanManipulateFiles;
    use Concerns\CanValidateInput;

    protected $description = 'Creates a Filament widget class.';

    protected $signature = 'make:filament-widget {name?}';

    public function handle(): int
    {
        $widget = (string) Str::of($this->argument('name') ?? $this->askRequired('Name (e.g. `BlogPostsChartWidget`)', 'name'))
            ->studly()
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');
        $widgetClass = (string) Str::of($widget)->afterLast('\\');
        $widgetNamespace = Str::of($widget)->contains('\\') ?
            (string) Str::of($widget)->beforeLast('\\') :
            '';

        $view = Str::of($widget)
            ->prepend('filament\\widgets\\')
            ->explode('\\')
            ->map(fn ($segment) => Str::kebab($segment))
            ->implode('.');

        $path = app_path(
            (string) Str::of($widget)
                ->prepend('Filament\\Widgets\\')
                ->replace('\\', '/')
                ->append('.php'),
        );
        $viewPath = resource_path(
            (string) Str::of($view)
                ->replace('.', '/')
                ->prepend('views/')
                ->append('.blade.php'),
        );

        if ($this->checkForCollision([
            $path,
            $viewPath,
        ])) {
            return static::INVALID;
        }

        $this->copyStubToApp('Widget', $path, [
            'class' => $widgetClass,
            'namespace' => 'App\\Filament\\Widgets' . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : ''),
            'view' => $view,
        ]);

        $this->copyStubToApp('WidgetView', $viewPath);

        $this->info("Successfully created {$widget}!");

        return static::SUCCESS;
    }
}

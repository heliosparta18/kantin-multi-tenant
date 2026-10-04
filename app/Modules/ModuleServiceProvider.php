<?php

namespace App\Modules;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use ReflectionClass;

/**
 * Basis provider modul (modular monolith, vertical slice).
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Alias modul untuk namespace view & Livewire, mis. 'admin'.
     */
    abstract protected function moduleAlias(): string;

    public function boot(): void
    {
        $path = $this->modulePath();
        $this->loadViewsFrom($path.'/resources/views', $this->moduleAlias());

        if (class_exists(Livewire::class)) {
            Livewire::addNamespace(
                namespace: $this->moduleAlias(),
                viewPath: $path.'/resources/views/livewire',
            );
        }

        foreach (glob($path.'/routes/*.php') ?: [] as $routeFile) {
            $this->loadRoutesFrom($routeFile);
        }
    }

    /**
     * Folder modul = folder tempat kelas provider turunan berada.
     */
    protected function modulePath(): string
    {
        return dirname((string) (new ReflectionClass($this))->getFileName());
    }
}

<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Heading;

class CalendarPage extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }

    public function getTitle(): string
    {
        return '📅 Calendario de Producción';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            Heading::make($this->getTitle()),
        ];
    }
    
    protected function viewData(): array
    {
        return [];
    }
    
    public function render(): string
    {
        return <<<HTML
        <style>
            .moonshine-page {
                padding: 0 !important;
                margin: 0 !important;
                height: 100vh !important;
            }
            .moonshine-page-wrapper {
                height: 100% !important;
            }
        </style>
        <div style="width: 100%; height: calc(100vh - 60px); margin: 0; padding: 0;">
            <iframe 
                src="/calendar" 
                style="width: 100%; height: 100%; border: none;"
                title="Calendario de Producción">
            </iframe>
        </div>
        HTML;
    }
}

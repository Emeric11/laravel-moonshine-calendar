<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Heading;

class Dashboard extends Page
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
        return $this->title ?: 'Panel de Control';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
	{
		return [
            Heading::make('Bienvenido al Sistema de Calendario'),
            \MoonShine\UI\Components\FlexibleRender::make('
                <div style="background: white; border-radius: 8px; padding: 30px; margin-top: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="color: #1f2937; margin-bottom: 15px; font-size: 1.25rem;">Gestión de Eventos de Producción</h3>
                    <p style="color: #6b7280; margin-bottom: 15px;">Utiliza el menú lateral para navegar:</p>
                    <ul style="margin-top: 10px; color: #374151; line-height: 1.8;">
                        <li><strong>CalendarEvents:</strong> Gestiona todos los eventos del calendario</li>
                        <li><strong>📅 Calendario de Producción:</strong> Vista visual del calendario</li>
                    </ul>
                </div>
            ')
        ];
	}
}
<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalendarEvent\Pages;

use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Select;
use App\MoonShine\Resources\CalendarEvent\CalendarEventResource;
use MoonShine\Support\ListOf;
use Throwable;


/**
 * @extends IndexPage<CalendarEventResource>
 */
class CalendarEventIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('OP', 'op_number')->sortable(),
            Text::make('Factura', 'factura')->sortable(),
           // Text::make('Título', 'title')->sortable(),
            Preview::make('📄 Factura', 'factura_pdf', function($item) {
                if ($item->factura_pdf) {
                    return '<span style="font-size: 20px; color: #10b981;" title="PDF Factura cargado">✓</span>';
                }
                return '<span style="font-size: 20px; color: #ef4444;" title="Sin PDF Factura">✗</span>';
            }),
            Preview::make('📋 Certificado', 'certif_pdf', function($item) {
                if ($item->certif_pdf) {
                    return '<span style="font-size: 20px; color: #10b981;" title="PDF Certificado cargado">✓</span>';
                }
                return '<span style="font-size: 20px; color: #ef4444;" title="Sin PDF Certificado">✗</span>';
            }),
            Preview::make('✓ OK', 'documentos_completos', function($item) {
                if ($item->factura_pdf && $item->certif_pdf) {
                    return '<span style="font-size: 24px; color: #10b981; font-weight: bold;" title="Documentos completos">✓✓</span>';
                }
                return '<span style="font-size: 20px; color: #9ca3af;" title="Documentos incompletos">—</span>';
            }),
            Preview::make('Estado', 'estado', function($item) {
                $colors = [
                    'pendiente' => '#f59e0b',
                    'en_progreso' => '#3b82f6',
                    'completado' => '#10b981',
                    'cancelado' => '#ef4444',
                ];
                $labels = [
                    'pendiente' => 'Pendiente',
                    'en_progreso' => 'En Progreso',
                    'completado' => 'Completado',
                    'cancelado' => 'Cancelado',
                ];
                $color = $colors[$item->estado] ?? '#6b7280';
                $label = $labels[$item->estado] ?? ucfirst($item->estado);
                return '<span style="display: inline-block; padding: 4px 12px; border-radius: 12px; background-color: ' . $color . '; color: white; font-size: 12px; font-weight: 600;">' . $label . '</span>';
            }),
            Text::make('Cliente', 'cliente')->sortable(),
        ];
    }

    /**
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [
            Text::make('Buscar OP', 'op_number'),
            Text::make('Buscar Cliente', 'cliente'),
            Text::make('Buscar Factura', 'factura'),
            Select::make('Estado', 'estado')
                ->options([
                    '' => 'Todos',
                    'pendiente' => 'Pendiente',
                    'en_progreso' => 'En Progreso',
                    'completado' => 'Completado',
                    'cancelado' => 'Cancelado',
                ]),
            Date::make('Fecha Desde', 'fecha_entrega_from'),
            Date::make('Fecha Hasta', 'fecha_entrega_to'),
        ];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        return [];
    }

    /**
     * @param  TableBuilder  $component
     *
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer()
        ];
    }
}

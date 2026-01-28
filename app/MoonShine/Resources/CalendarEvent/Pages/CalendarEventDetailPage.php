<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalendarEvent\Pages;

use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\Contracts\UI\FieldContract;
use App\MoonShine\Resources\CalendarEvent\CalendarEventResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Components\Layout\Box;
use Throwable;


/**
 * @extends DetailPage<CalendarEventResource>
 */
class CalendarEventDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make('Información General', [
                ID::make(),
                Text::make('Título', 'title'),
                Text::make('Número OP', 'op_number'),
                Text::make('Cliente', 'cliente'),
                Text::make('Estado', 'estado'),
            ]),
            
            Box::make('Fechas', [
                Date::make('Inicio', 'start')->format('d/m/Y H:i'),
                Date::make('Fin', 'end')->format('d/m/Y H:i'),
                Date::make('Fecha Entrega', 'fecha_entrega')->format('d/m/Y'),
                Date::make('Fecha Producción', 'fecha_produccion')->format('d/m/Y'),
            ]),
            
            Box::make('Cantidades', [
                Text::make('Cantidad Requerida', 'cantidad_req'),
                Text::make('Cantidad Facturada', 'cantidadFact'),
            ]),
            
            Box::make('Documentos', [
                Preview::make('PDF Factura', 'factura_pdf', function($item) {
                    if (!$item->factura_pdf || !$item->op_number) {
                        return '<span style="color: #6b7280;">Sin archivo</span>';
                    }
                    $url = "/storage/facturas_certf_pdf/{$item->op_number}/{$item->factura_pdf}";
                    return '<a href="' . $url . '" target="_blank" style="color: #3b82f6; text-decoration: underline;">📄 Ver PDF Factura</a>';
                }),
                Preview::make('PDF Certificado', 'certif_pdf', function($item) {
                    if (!$item->certif_pdf || !$item->op_number) {
                        return '<span style="color: #6b7280;">Sin archivo</span>';
                    }
                    $url = "/storage/facturas_certf_pdf/{$item->op_number}/{$item->certif_pdf}";
                    return '<a href="' . $url . '" target="_blank" style="color: #3b82f6; text-decoration: underline;">📄 Ver PDF Certificado</a>';
                }),
            ]),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    /**
     * @param  TableBuilder  $component
     *
     * @return TableBuilder
     */
    protected function modifyDetailComponent(ComponentContract $component): ComponentContract
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

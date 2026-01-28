<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalendarEvent\Pages;

use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use App\MoonShine\Resources\CalendarEvent\CalendarEventResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Color;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\File;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use Throwable;


/**
 * @extends FormPage<CalendarEventResource>
 */
class CalendarEventFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            // Fila: Fechas Embarque
            Box::make('Fechas Embarque', [
                Grid::make([
                    Column::make([
                        Date::make('Fecha Entrega', 'fecha_entrega'),
                    ])->columnSpan(3),
                    Column::make([
                        Date::make('Fecha Producción', 'fecha_produccion'),
                    ])->columnSpan(3),
                    Column::make([
                        Date::make('Inicio', 'start')->required()->withTime(),
                    ])->columnSpan(3),
                    Column::make([
                        Date::make('Fin', 'end')->withTime(),
                    ])->columnSpan(3),
                ]),
            ]),
            
            // Fila: Datos Embarque
            Box::make('Datos Embarque', [
                Grid::make([
                    Column::make([
                        Text::make('Cliente', 'cliente'),
                    ])->columnSpan(4),
                    Column::make([
                        Text::make('Factura', 'factura'),
                    ])->columnSpan(4),
                    Column::make([
                        Text::make('Orden de Compra', 'ordencompra'),
                    ])->columnSpan(4),
                ]),
                Grid::make([
                    Column::make([
                        Number::make('Cantidad Facturada', 'cantidadFact')->default(0),
                    ])->columnSpan(3),
                    Column::make([
                        Number::make('Cantidad Requerida', 'cantidad_req')->default(0),
                    ])->columnSpan(3),
                    Column::make([
                        Select::make('Estado', 'estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'en_progreso' => 'En Progreso',
                                'completado' => 'Completado',
                                'cancelado' => 'Cancelado',
                            ])
                            ->default('pendiente'),
                    ])->columnSpan(3),
                    Column::make([
                        Color::make('Color', 'color'),
                    ])->columnSpan(3),
                ]),
            ]),
            
            // Fila: Datos Producto
            Box::make('Datos Producto', [
                Grid::make([
                    Column::make([
                        Text::make('Número OP', 'op_number')->required(),
                    ])->columnSpan(3),
                    Column::make([
                        Text::make('Código', 'codigo'),
                    ])->columnSpan(3),
                    Column::make([
                        Text::make('Título', 'title')->required(),
                    ])->columnSpan(6),
                ]),
                Textarea::make('Descripción', 'descripcion'),
                Switcher::make('Todo el día', 'all_day'),
            ]),
            
            // Documentos PDF (El Observer se encarga de mover y renombrar automáticamente)
            Box::make('Documentos PDF', [
                Grid::make([
                    Column::make([
                        File::make('PDF Factura', 'factura_pdf')
                            ->disk('public')
                            ->dir('facturas_certf_pdf')
                            ->allowedExtensions(['pdf'])
                            ->removable()
                            ->hint('Solo archivos PDF, máx 10MB. Se organizará automáticamente al guardar.'),
                    ])->columnSpan(6),
                    Column::make([
                        File::make('PDF Certificado', 'certif_pdf')
                            ->disk('public')
                            ->dir('facturas_certf_pdf')
                            ->allowedExtensions(['pdf'])
                            ->removable()
                            ->hint('Solo archivos PDF, máx 10MB. Se organizará automáticamente al guardar.'),
                    ])->columnSpan(6),
                ]),
            ]),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function formButtons(): ListOf
    {
        return parent::formButtons();
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [];
    }

    /**
     * @param  FormBuilder  $component
     *
     * @return FormBuilder
     */
    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
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

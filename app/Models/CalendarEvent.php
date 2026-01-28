<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CalendarEvent extends Model
{
    use HasFactory;

    protected $table = 'calendar_events';

    protected $fillable = [
        'title',
        'start',
        'end',
        'all_day',
        'color',
        'codigo',
        'descripcion',
        'factura',
        'ordencompra',
        'cantidadFact',
        'op_number',
        'cliente',
        'cantidad_req',
        'fecha_entrega',
        'fecha_produccion',
        'estado',
        'factura_pdf',
        'certif_pdf',
       // 'codigos'
    ];

    protected $casts = [
        'all_day' => 'boolean',
        'start' => 'datetime',
        'end' => 'datetime',
        'fecha_entrega' => 'date',
        'fecha_produccion' => 'date',
        'cantidad_req' => 'integer',
        //'codigos' => 'array' // Cast automático para JSON
    ];

    /**
     * Convertir a formato para FullCalendar
     */
    public function toEventArray()
    {
        $start = $this->start;
        $end   = $this->end;

        // ✅ EVENTOS ALL DAY
        if ($this->all_day) {
            // FullCalendar requiere end exclusivo
            $end = $end
                ? $end
                : $start->copy()->addDay();

            return [
                'id'    => $this->id,
                'title' => $this->title,
                'start' => $start->format('Y-m-d'),
                'end'   => $end->format('Y-m-d'),
                'allDay' => true,
                'color' => $this->color,
                'extendedProps' => $this->extendedPropsArray()
            ];
        }

        // ✅ EVENTOS CON HORA
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'start' => $start,
            'end'   => $end ?? $start->copy()->addHour(),
            'allDay' => false,
            'color' => $this->color,
            'extendedProps' => $this->extendedPropsArray()
        ];
    }
    private function extendedPropsArray()
    {
        return [
            'op_number'       => $this->op_number,
            'cliente'         => $this->cliente,
            'codigo'         => $this->codigo,
            'descripcion'     => $this->descripcion,
            'ordencompra'     => $this->ordencompra,
            'factura'         => $this->factura,
            'cantidadFact'         => $this->cantidadFact,
            'cantidadReq'     => $this->cantidad_req,
            'fechaEntrega'    => optional($this->fecha_entrega)->format('Y-m-d'),
            'fechaProduccion' => optional($this->fecha_produccion)->format('Y-m-d'),
            'estado'          => $this->estado,
           // 'codigos'         => $this->codigos ?: []
              // ✅ NUEVO
            'factura_pdf'     => $this->factura_pdf,
            'certif_pdf'      => $this->certif_pdf,
        ];
    }



    /**
     * Buscar por número de OP
     */
    public static function findByOpNumber($opNumber)
    {
        return static::where('op_number', $opNumber)->first();
    }

    /**
     * Obtener eventos en un rango de fechas
     */
    public static function getEventsInRange($start, $end)
    {
        return static::whereBetween('start', [$start, $end])
            ->orWhereBetween('end', [$start, $end])
            ->orWhere(function ($query) use ($start, $end) {
                $query->where('start', '<=', $start)
                    ->where('end', '>=', $end);
            })
            ->get();
    }
}

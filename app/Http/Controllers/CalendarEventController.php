<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Notifications\CalendarEventNotification;
use MoonShine\Laravel\Models\MoonshineUser;
class CalendarEventController extends Controller
{


/**
     * Guardar PDF (factura o certificado)
     */
    public function guardarPdf(Request $request, CalendarEvent $event)
    {
        try {
            $request->validate([
                'pdf'  => 'required|file|mimes:pdf|max:10240',
                'tipo' => 'required|in:factura,certificado',
            ]);

            $op      = $event->op_number ?? 'SIN_OP';
            $factura = $event->factura ?? 'SIN_FACTURA';
            $codigo  = $event->codigo  ?? 'SIN_CODIGO';
            $tipo    = $request->tipo;

            // Nombre del archivo
            $nombreArchivo = "{$event->id}_{$tipo}_{$factura}_{$codigo}.pdf";

            // Ruta en storage/app/public
            $ruta = "facturas_certf_pdf/{$op}";

            // Eliminar archivo anterior si existe
            if ($tipo === 'factura' && $event->factura_pdf) {
                Storage::disk('public')->delete("{$ruta}/{$event->factura_pdf}");
            } elseif ($tipo === 'certificado' && $event->certif_pdf) {
                Storage::disk('public')->delete("{$ruta}/{$event->certif_pdf}");
            }

            // Guardar nuevo archivo
            Storage::disk('public')->putFileAs(
                $ruta,
                $request->file('pdf'),
                $nombreArchivo
            );

            // Actualizar registro en BD
            if ($tipo === 'factura') {
                $event->factura_pdf = $nombreArchivo;
            } else {
                $event->certif_pdf = $nombreArchivo;
            }

            $event->save();

            // Notificar a todos los usuarios
            $this->notifyPdfUpload($event);

            return response()->json([
                'success' => true,
                'archivo' => $nombreArchivo,
                'url' => "/storage/facturas_certf_pdf/{$op}/{$nombreArchivo}",
                'message' => 'PDF guardado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al guardar PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al guardar el PDF'
            ], 500);
        }
    }


    /**
     * Obtener eventos para el calendario
     */
    public function index(Request $request)
    {
        try {
            $start = $request->input('start');
            $end   = $request->input('end');

            // 🔒 ESTRUCTURA DE CONTROL
            if ($start && !$end) {
                // Si end es null, usar la fecha start
                $end = $start;
            }

            if ($start && $end) {
                $events = CalendarEvent::getEventsInRange($start, $end);
            } else {
                $events = CalendarEvent::all();
            }

            return response()->json(
                $events->map(function ($event) {
                    return $event->toEventArray();
                })
            );
        } catch (\Exception $e) {
            Log::error('Error al obtener eventos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar eventos'], 500);
        }
    }


    /**
     * Guardar nuevo evento
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'start' => 'required|date',
                'end' => 'nullable|date',
                'all_day' => 'boolean',
                'color' => 'nullable|string',
                'codigo' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string|max:255',
                'factura' => 'nullable|string|max:255',
                'ordencompra' => 'nullable|string|max:255',
                'cantidadFact' => 'nullable|integer|min:0',
                'op_number' => 'required|string|max:50',
                'cliente' => 'nullable|string|max:255',
                'cantidad_req' => 'nullable|integer|min:0',
                'fecha_entrega' => 'nullable|date',
                'fecha_produccion' => 'nullable|date',
                'estado' => 'nullable|string|in:pendiente,en_progreso,completado,cancelado',
                //'codigos' => 'nullable|array'
            ]);

            $event = CalendarEvent::create($validated);

            return response()->json([
                'success' => true,
                'event' => $event->toEventArray(),
                'message' => 'Evento guardado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al guardar evento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al guardar el evento'
            ], 500);
        }
    }

    /**
     * Actualizar evento existente
     */
    public function update(Request $request, $id)
    {
        try {
            $event = CalendarEvent::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'start' => 'sometimes|date',
                'end' => 'nullable|date',
                'all_day' => 'sometimes|boolean',
                'color' => 'nullable|string',
                'codigo' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string|max:255',
                'factura' => 'nullable|string|max:255',
                'ordencompra' => 'nullable|string|max:255',
                'cantidadFact' => 'nullable|integer|min:0',
                'op_number' => 'sometimes|string|max:50',
                'cliente' => 'nullable|string|max:255',
                'cantidad_req' => 'nullable|integer|min:0',
                'fecha_entrega' => 'nullable|date',
                'fecha_produccion' => 'nullable|date',
                'estado' => 'nullable|string|in:pendiente,en_progreso,completado,cancelado',
                //'codigos' => 'nullable|array'
            ]);

            $event->update($validated);

            return response()->json([
                'success' => true,
                'event' => $event->toEventArray(),
                'message' => 'Evento actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar evento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar el evento'
            ], 500);
        }
    }

    /**
     * Eliminar evento
     */
public function destroy($id)
{
    try {
        $event = CalendarEvent::findOrFail($id);

        $op = $event->op_number;
        $directorio = public_path("facturas_certf_pdf/{$op}");

        // 🔥 Eliminar factura asociada al evento
        if ($event->factura_pdf) {
            $rutaFactura = "{$directorio}/{$event->factura_pdf}";
            if (File::exists($rutaFactura)) {
                File::delete($rutaFactura);
            }
        }

        // 🔥 Eliminar certificado asociado al evento
        if ($event->certif_pdf) {
            $rutaCert = "{$directorio}/{$event->certif_pdf}";
            if (File::exists($rutaCert)) {
                File::delete($rutaCert);
            }
        }

        // ❌ NO borrar carpeta OP (puede tener más archivos)

        // ❌ Eliminar evento
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evento y archivos asociados eliminados'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error al eliminar evento'
        ], 500);
    }
}

    /**
     * Obtener un evento específico
     */
    public function show($id)
    {
        try {
            $event = CalendarEvent::findOrFail($id);
            return response()->json($event->toEventArray());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Evento no encontrado'], 404);
        }
    }
    public function updateDateTime(Request $request, $id)
    {
        try {
            $event = CalendarEvent::findOrFail($id);

            $validated = $request->validate([
                'start' => 'required|date',
                'end' => 'nullable|date',
                'all_day' => 'boolean',
                'estado' => 'nullable|string|in:pendiente,en_progreso,completado,cancelado'
            ]);

            $updateData = [
                'start' => $validated['start'],
                'end' => $validated['end'] ?? $event->end,
                'all_day' => $validated['all_day'] ?? $event->all_day
            ];

            // Si viene estado, actualizarlo también
            if (isset($validated['estado'])) {
                $updateData['estado'] = $validated['estado'];
            }

            $event->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Evento actualizado'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updateDateTime: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Notificar PDF subido
     */
    private function notifyPdfUpload(CalendarEvent $event): void
    {
        try {
            $currentUser = auth('moonshine')->user();
            $userName = $currentUser?->name ?? 'Sistema';

            $users = MoonshineUser::where('id', '!=', $currentUser?->id ?? 0)->get();

            foreach ($users as $user) {
                $user->notify(new CalendarEventNotification($event, 'pdf_uploaded', $userName));
            }
        } catch (\Exception $e) {
            Log::error("Error notificando PDF: " . $e->getMessage());
        }
    }
}

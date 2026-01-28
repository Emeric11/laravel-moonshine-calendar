<?php

namespace App\Observers;

use App\Models\CalendarEvent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CalendarEventObserver
{
    /**
     * Handle the CalendarEvent "saved" event.
     * Se ejecuta después de crear o actualizar
     */
    public function saved(CalendarEvent $event): void
    {
        if (!$event->op_number) {
            return;
        }

        $needsUpdate = false;
        $opDir = "facturas_certf_pdf/{$event->op_number}";

        // Crear directorio si no existe
        if (!Storage::disk('public')->exists($opDir)) {
            Storage::disk('public')->makeDirectory($opDir);
            Log::info("Observer: Directorio creado: {$opDir}");
        }

        // Procesar factura_pdf
        if ($event->factura_pdf) {
            $needsUpdate = $this->processPdf($event, 'factura_pdf', 'factura', $opDir) || $needsUpdate;
        }

        // Procesar certif_pdf
        if ($event->certif_pdf) {
            $needsUpdate = $this->processPdf($event, 'certif_pdf', 'certificado', $opDir) || $needsUpdate;
        }

        // Guardar cambios sin disparar el observer de nuevo
        if ($needsUpdate) {
            $event->saveQuietly();
            Log::info("Observer: Evento {$event->id} actualizado con nuevos nombres de PDF");
        }
    }

    /**
     * Procesar y mover PDF individual
     */
    private function processPdf(CalendarEvent $event, string $field, string $tipo, string $opDir): bool
    {
        $oldFileName = $event->$field;
        
        // Generar nombre correcto: {id}_{tipo}_{factura}_{codigo}.pdf
        $factura = $event->factura ?? 'SIN_FACTURA';
        $codigo = $event->codigo ?? 'SIN_CODIGO';
        $newFileName = "{$event->id}_{$tipo}_{$factura}_{$codigo}.pdf";

        Log::info("Observer processPdf: campo={$field}, oldFileName={$oldFileName}, newFileName={$newFileName}");

        // Si ya tiene el formato correcto, no hacer nada
        if ($oldFileName === $newFileName && Storage::disk('public')->exists("{$opDir}/{$newFileName}")) {
            Log::info("Observer: Archivo ya correcto: {$newFileName}");
            return false;
        }

        // Limpiar el nombre del archivo (remover prefijo de ruta si existe)
        $cleanFileName = str_replace('facturas_certf_pdf/', '', $oldFileName);
        
        // Buscar archivo en posibles ubicaciones
        $possiblePaths = [
            $oldFileName,                          // Ruta completa como viene de MoonShine
            "facturas_certf_pdf/{$cleanFileName}", // Con prefijo
            $cleanFileName,                        // Solo el nombre
            "{$opDir}/{$cleanFileName}",           // Ya en directorio OP con nombre hash
            "{$opDir}/{$oldFileName}",             // Ya en directorio OP con ruta completa
        ];

        $foundPath = null;
        foreach ($possiblePaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                $foundPath = $path;
                Log::info("Observer: Archivo encontrado en: {$path}");
                break;
            }
        }

        if (!$foundPath) {
            Log::warning("Observer: Archivo no encontrado. Intentado: " . implode(', ', $possiblePaths));
            // No hacer nada si el archivo ya tiene el nombre correcto (puede haber sido procesado por MoonShine)
            if ($oldFileName === $newFileName) {
                return false;
            }
            return false;
        }

        $newPath = "{$opDir}/{$newFileName}";

        // Mover y renombrar si es necesario
        if ($foundPath !== $newPath) {
            // Eliminar destino si existe
            if (Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->delete($newPath);
            }

            // Mover archivo
            Storage::disk('public')->move($foundPath, $newPath);
            Log::info("Observer: Archivo movido: {$foundPath} → {$newPath}");
        }

        // Actualizar campo con el nombre correcto (solo el nombre, sin ruta)
        $event->$field = $newFileName;
        return true;
    }

    /**
     * Handle the CalendarEvent "deleting" event.
     */
    public function deleting(CalendarEvent $event): void
    {
        if (!$event->op_number) {
            return;
        }

        $opDir = "facturas_certf_pdf/{$event->op_number}";

        // Eliminar factura_pdf
        if ($event->factura_pdf) {
            $path = "{$opDir}/{$event->factura_pdf}";
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info("Observer: PDF eliminado: {$path}");
            }
        }

        // Eliminar certif_pdf
        if ($event->certif_pdf) {
            $path = "{$opDir}/{$event->certif_pdf}";
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info("Observer: PDF eliminado: {$path}");
            }
        }
    }
}

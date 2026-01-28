<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\CalendarEvent;

class CalendarEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $event;
    public $action;
    public $userName;

    /**
     * Create a new notification instance.
     */
    public function __construct(CalendarEvent $event, string $action, string $userName = null)
    {
        $this->event = $event;
        $this->action = $action; // 'created', 'updated', 'deleted', 'pdf_uploaded'
        $this->userName = $userName ?? 'Usuario';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = [
            'event_id' => $this->event->id,
            'op_number' => $this->event->title,
            'action' => $this->action,
            'user_name' => $this->userName,
            'cliente' => $this->event->cliente,
            'estado' => $this->event->estado,
            'fecha_entrega' => $this->event->fecha_entrega,
        ];

        // Mensaje personalizado según acción
        switch ($this->action) {
            case 'created':
                $data['message'] = "📦 Nueva OP {$this->event->title} creada por {$this->userName}";
                $data['icon'] = '📦';
                break;
            case 'updated':
                $data['message'] = "✏️ OP {$this->event->title} actualizada por {$this->userName}";
                $data['icon'] = '✏️';
                break;
            case 'deleted':
                $data['message'] = "🗑️ OP {$this->event->title} eliminada por {$this->userName}";
                $data['icon'] = '🗑️';
                break;
            case 'pdf_uploaded':
                $data['message'] = "📄 PDF subido a OP {$this->event->title} por {$this->userName}";
                $data['icon'] = '📄';
                break;
            default:
                $data['message'] = "🔔 Cambio en OP {$this->event->title}";
                $data['icon'] = '🔔';
        }

        return $data;
    }
}

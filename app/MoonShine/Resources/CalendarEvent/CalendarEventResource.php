<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CalendarEvent;

use Illuminate\Database\Eloquent\Model;
use App\Models\CalendarEvent;
use App\MoonShine\Resources\CalendarEvent\Pages\CalendarEventIndexPage;
use App\MoonShine\Resources\CalendarEvent\Pages\CalendarEventFormPage;
use App\MoonShine\Resources\CalendarEvent\Pages\CalendarEventDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;
use Illuminate\Support\Facades\Storage;
use MoonShine\Support\Enums\Ability;
use App\Helpers\PermissionHelper;

/**
 * @extends ModelResource<CalendarEvent, CalendarEventIndexPage, CalendarEventFormPage, CalendarEventDetailPage>
 */
class CalendarEventResource extends ModelResource
{
    protected string $model = CalendarEvent::class;

    protected string $title = 'Eventos del Calendario';

    protected string $column = 'title';
    
    protected bool $createInModal = false;
    
    protected bool $editInModal = false;
    
    protected bool $columnSelection = true;
    
    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            CalendarEventIndexPage::class,
            CalendarEventFormPage::class,
            CalendarEventDetailPage::class,
        ];
    }
    
    /**
     * Controlar permisos del recurso
     */
    public function can(Ability|string $ability): bool
    {
        return match($ability) {
            Ability::VIEW => true, // Todos pueden ver
            Ability::CREATE => PermissionHelper::canCreateEvents(),
            Ability::UPDATE => PermissionHelper::canEditEvents(),
            Ability::DELETE => PermissionHelper::canDeleteEvents(),
            default => parent::can($ability),
        };
    }
}

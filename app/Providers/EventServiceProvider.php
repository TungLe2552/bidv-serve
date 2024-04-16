<?php

namespace App\Providers;

use App\Events\AddResourceForTranOrder;
use App\Events\CancelDieuPhoi;
use App\Events\CancelMultiTransportationOrderLocationDone;
use App\Events\CancelTransportationOrderLocationDone;
use App\Events\CancelTransportationRequestDone;
use App\Events\CreateTransportationOrderDone;
use App\Events\RemoveDepartmentForTranOrderDone;
use App\Events\RemoveResourceForTranOrder;
use App\Events\RemoveResourceForTranOrderDone;
use App\Events\SendNotificationForTransportationOrder;
use App\Events\UpdateTransportationRequestDone;
use App\Listeners\AddResourceForTranOrder_AddressReturnReference;
use App\Listeners\AddResourceForTranOrder_CreateExtra;
use App\Listeners\CancelDieuPhoi_UpdateWorkSchedule;
use App\Listeners\CancelMultiTransportationOrderLocationDone_CancelResource;
use App\Listeners\CancelMultiTransportationOrderLocationDone_SendNotifyForDepartment;
use App\Listeners\CancelMultiTransportationOrderLocationDone_SendNotifyForResource;
use App\Listeners\CancelTransportationOrderLocationDone_CancelResource;
use App\Listeners\CancelTransportationOrderLocationDone_SendNotifyForDepartment;
use App\Listeners\CancelTransportationOrderLocationDone_SendNotifyForResource;
use App\Listeners\CancelTransportationRequestDone_CancelTransportationOrder;
use App\Listeners\CancelTransportationRequestDone_SendForDieuPhoi;
use App\Listeners\ChangeResourceForTranOrder_UpdateWorkSchedule;
use App\Listeners\CreateTransportationOrderDone_SendForDieuPhoi;
use App\Listeners\RemoveDepartmentForTranOrderDone_SendForDepartment;
use App\Listeners\RemoveResourceForTranOrder_RemoveAddressReturnReference;
use App\Listeners\RemoveResourceForTranOrder_RemoveDoNotification;
use App\Listeners\RemoveResourceForTranOrder_SendNotifyForRemove;
use App\Listeners\SendNotificationForTransportationOrder_SendForDepartment;
use App\Listeners\SendNotificationForTransportationOrder_SendForResource;
use App\Listeners\SendNotificationForTransportationOrder_SendForSalesMan;
use App\Listeners\SendNotificationForTransportationOrder_SendForUserRelated;
use App\Listeners\UpdateTransportationRequestDone_SendForDieuPhoi;
use App\Listeners\UpdateTransportationRequestDone_SendForDriver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */

    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        AddResourceForTranOrder::class => [
            // AddResourceForTranOrder_CreateDoNotification::class,
            AddResourceForTranOrder_AddressReturnReference::class,
            ChangeResourceForTranOrder_UpdateWorkSchedule::class,
            AddResourceForTranOrder_CreateExtra::class,
        ],
        RemoveResourceForTranOrder::class => [
            RemoveResourceForTranOrder_RemoveDoNotification::class,
            RemoveResourceForTranOrder_RemoveAddressReturnReference::class,
        ],
        RemoveResourceForTranOrderDone::class => [
            ChangeResourceForTranOrder_UpdateWorkSchedule::class,
            RemoveResourceForTranOrder_SendNotifyForRemove::class,
        ],
        RemoveDepartmentForTranOrderDone::class => [
            RemoveDepartmentForTranOrderDone_SendForDepartment::class
        ],
        SendNotificationForTransportationOrder::class => [
            SendNotificationForTransportationOrder_SendForDepartment::class,
            SendNotificationForTransportationOrder_SendForSalesMan::class,
            SendNotificationForTransportationOrder_SendForResource::class,
            SendNotificationForTransportationOrder_SendForUserRelated::class,
        ],
        CancelTransportationRequestDone::class => [
            CancelTransportationRequestDone_CancelTransportationOrder::class,
            CancelTransportationRequestDone_SendForDieuPhoi::class,
        ],
        CancelTransportationOrderLocationDone::class => [
            CancelTransportationOrderLocationDone_CancelResource::class,
            CancelTransportationOrderLocationDone_SendNotifyForDepartment::class,
            CancelTransportationOrderLocationDone_SendNotifyForResource::class,
        ],
        CancelMultiTransportationOrderLocationDone::class => [
            CancelMultiTransportationOrderLocationDone_CancelResource::class,
            CancelMultiTransportationOrderLocationDone_SendNotifyForDepartment::class,
            CancelMultiTransportationOrderLocationDone_SendNotifyForResource::class,
        ],
        CancelDieuPhoi::class => [
            CancelDieuPhoi_UpdateWorkSchedule::class,
        ],
        CreateTransportationOrderDone::class => [
            CreateTransportationOrderDone_SendForDieuPhoi::class,
        ],
        UpdateTransportationRequestDone::class => [
            UpdateTransportationRequestDone_SendForDieuPhoi::class,
            UpdateTransportationRequestDone_SendForDriver::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

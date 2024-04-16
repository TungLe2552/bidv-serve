<?php

namespace App\Listeners;

use App\Events\AddResourceForTranOrder;
use App\Helpers\System\CacheHelper;
use App\Models\Logistic\FleetAddressReturnReference;
use App\Models\Res\PartnerCategory;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddResourceForTranOrder_AddressReturnReference
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AddResourceForTranOrder $event): void
    {
        $partner_category_ref = CacheHelper::getDataCache('tai-nguyen', 'dia-chi-lay-hang-mac-dinh', function () {
            return PartnerCategory::leftJoin('res_partner_category_references', 'res_partner_categories.id', '=', 'res_partner_category_references.partner_category_id')
                ->leftJoin('res_partners', 'res_partner_category_references.partner_id', '=', 'res_partners.id')
                ->where('res_partner_categories.short_name', 'return-location')
                ->where('res_partners.short_name', 'like', '%Gia Đông%')
                ->select('res_partner_category_references.id')->first();
        });
        $order_resource = $event->resource;

        FleetAddressReturnReference::create([
            'partner_category_ref_id' => $partner_category_ref->id,
            'fleet_tran_order_resource_id' => $order_resource->id,
        ]);
    }
}

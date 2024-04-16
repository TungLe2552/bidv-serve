<?php

namespace App\Helpers\Notification;

use App\Models\Logistic\FleetTransportationRequest;
use Carbon\Carbon;

class TransportationRequestNotificationHelper
{
    public static function getTitle(FleetTransportationRequest $tran_request)
    {
        $type_request = $tran_request['type'] ?? null;
        $shift = $tran_request['shift'] ?? null;
        $type_request_name = isset($type_request) ? $type_request['name'] : '';
        $date = (new Carbon($tran_request['start_date']))->format('d/m/Y');
        $shift_name = isset($shift) ? $shift['name'] : '';
        return "$type_request_name - $date - $shift_name";
    }
    public static function getContent(FleetTransportationRequest $tran_request)
    {
        $content = [];
        $tran_request->load('locations.address:id,name');
        $locations = $tran_request->locations;
        foreach ($locations as $location) {
            $address_name = $location['address']['name'];
            $notes2 = $location['notes2'];
            $notes3 = $location['notes3'];
            $content[] = "$address_name - HH: $notes2 - KL: $notes3";
        }

        return implode(PHP_EOL, $content);
    }
}

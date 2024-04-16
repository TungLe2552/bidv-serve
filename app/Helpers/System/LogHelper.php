<?php

namespace App\Helpers\System;

use App\Models\Auth\User;
use App\Models\Res\Device;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use Spatie\Activitylog\Contracts\Activity;
use Stevebauman\Location\Facades\Location;

class LogHelper
{
    public static function logLogin(User $user, $token_id, $description = "login", $subject_display = null)
    {
        $agent = new Agent();
        $ip = LogHelper::getIp();

        $position = Location::get($ip);
        $location = [];
        if ($position) {
            $location = $position->toArray();
        }
        Device::updateOrCreate([
            'ip' => $ip,
            'user_agent' => request()->header('User-Agent'),
            'device' => $agent->device(),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'user_id' => $user->getKey(),
        ], [
            'ip' => $ip,
            'token_id' => $token_id,
            'user_agent' => request()->header('User-Agent'),
            'device' => $agent->device(),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'user_id' => $user->getKey(),
            'country_name' => $location['countryName'] ?? '',
            'country_code' => $location['countryCode'] ?? '',
            'region_name' => $location['regionName'] ?? '',
            'region_code' => $location['regionCode'] ?? '',
            'latitude' => $location['latitude'] ?? '',
            'longitude' => $location['longitude'] ?? '',
            'logout' => false,
            'last_login' => Carbon::now(),
        ]);
    }
    public static function getIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        if (App::environment('local')) {
            return '42.113.143.223';
        }
        return request()->ip(); // it will return server ip when no client ip found
    }
}

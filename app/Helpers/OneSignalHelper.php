<?php

namespace App\Helpers;

use Ladumor\OneSignal\OneSignalClient;

define("NOTIFICATIONS", "notifications");
define("DEVICES", "players");
define("APPS", "apps");
define("SEGMENTS", "segments");
class OneSignalHelper extends OneSignalClient
{
    public function __construct()
    {
        $this->initConfig();
    }
    protected function initConfig()
    {
        $this->setUrl(config('one-signal.url'));
        $this->setMutableContent(config('one-signal.mutable_content'));
        $this->setAuthKey(config('one-signal.auth_key'));
    }

    public function sendNotification(string $type = 'driver', $fields, $message = '')
    {
        $this->setAppId(config('one-signal.app_id_' . $type));
        $this->setAuthorization(config('one-signal.authorize_' . $type));
        $content = array(
            "en" => $message,
        );
        $fields['app_id'] = $this->getAppId();
        $fields['mutable_content'] = $this->getMutableContent();
        if (!isset($fields['contents']) || empty($fields['contents'])) {
            $fields['contents'] = $content;
        }
        $url = $this->getUrl(NOTIFICATIONS);
        return $this->post($url, json_encode($fields));
    }
}

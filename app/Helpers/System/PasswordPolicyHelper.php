<?php

namespace App\Helpers\System;

use App\Enum\PasswordPolicyEnum;
use Illuminate\Support\Facades\Storage;


class PasswordPolicyHelper
{
    public static function getPasswordPolicy()
    {
        $policy = Storage::json('PasswordPolicy.json');
        if (!$policy) {
            return Self::createPasswordPolicy();
        }
        return $policy;
    }

    public static function createPasswordPolicy()
    {
        $content = [
            'passwordPolicy' => PasswordPolicyEnum::BASIC->value,
            'customSetting' => ['']
        ];
        Storage::put('PasswordPolicy.json', json_encode($content));
        $content = Storage::json('PasswordPolicy.json');
        return $content;
    }

    public static function CheckExist()
    {
        $policy = Storage::json('PasswordPolicy.json');
        if ($policy === null)
            return false;
        else
            return true;
    }
}

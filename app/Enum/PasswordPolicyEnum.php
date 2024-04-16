<?php

namespace App\Enum;

enum PasswordPolicyEnum: string
{
    case DISABLED = "DISABLED";
    case BASIC = "BASIC";
    case SECURE = "SECURE";
    case CUSTOM = "CUSTOM";
}

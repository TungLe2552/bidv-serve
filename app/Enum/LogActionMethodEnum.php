<?php

namespace App\Enum;


enum LogActionMethodEnum: string
{
    case DELETED = 'deleted';
    case CREATED = 'created';
    case UPDATED = 'updated';
    case READ = 'read';
    case EXPORT = 'export';
}

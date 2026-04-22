<?php

namespace Modules\Uploader\Enums;

enum DiskType: string
{
    case LOCAL = 'local';
    case PUBLIC = 'public';
    case ARVAN_PUBLIC = 's3-arvan_public';
    case ARVAN_PRIVATE = 's3-arvan_private';

}

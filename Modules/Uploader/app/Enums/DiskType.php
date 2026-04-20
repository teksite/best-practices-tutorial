<?php

namespace Modules\Uploader\Enums;

enum DiskType: string
{
    case LOCAL = 'local';
    case PUBLIC = 'public';
    case ARVANCLOUAD = 'arvancloud';

}

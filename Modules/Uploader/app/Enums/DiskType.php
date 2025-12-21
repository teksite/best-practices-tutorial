<?php

namespace Modules\Uploader\Enums;

enum DiskType: string
{
    case PUBLIC = 'public';
    case LOCAL = 'local';
    case S3 = 's3';

    case SFTP = 'sftp';

    case FTP = 'ftp';
}

<?php

namespace Modules\Uploader\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Uploader\Database\Factories\MediaModelFactory;

class MediaModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): MediaModelFactory
    // {
    //     // return MediaModelFactory::new();
    // }
}

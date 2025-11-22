<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPreferences extends Model
{

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id' ,'preferences'];

    protected function casts(): array
    {
        return [
            'preferences' => 'json',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}

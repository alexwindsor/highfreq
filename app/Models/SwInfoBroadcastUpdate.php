<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SwInfoBroadcastUpdate extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];




    public static function getLatestUpdate($field)
    {
        $result = SwInfoBroadcastUpdate::orderBy('id', 'DESC')->first();
        return $result->$field;
    }


    public function swInfoBroadcasts(): HasMany
    {
        return $this->hasMany(SwInfoBroadcast::class);
    }

}

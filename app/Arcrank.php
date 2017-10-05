<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Arcrank extends Model
{
    //
    protected $table = 'arcrank';

    public static function getShowAll()
    {
        return Arcrank::where('rank', '>=', 0)->get();
    }


}

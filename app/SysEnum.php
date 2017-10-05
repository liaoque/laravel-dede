<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SysEnum extends Model
{
    protected $table = 'sys_enum';

    //
    public static function getInfoTypeAll()
    {
        return self::where('egroup', 'like', 'infotype')
            ->orderBy('disorder', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();
    }


}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Addonimage extends Model
{
    //
    protected $table = 'addonimages';

    public static function createNew(Request $request)
    {
        $result = $request->post([
            'aid', 'typeid', 'redirecturl', 'templet', 'body'
        ]);
        $obj = new self();
        $obj->userip = $request->getClientIp();
        foreach ($result as $key => $value) {
            $obj->$key = $value;
        }
        return $obj->save() ? $obj : false;
    }
}

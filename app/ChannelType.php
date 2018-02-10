<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChannelType extends Model
{
    //
    protected $table = 'channeltype';


    public static function getShowAll()
    {
        return ChannelType::where('id', '<>', -1)->where('isshow', 1)->orderBy('id')->get();
    }

//    public function getTemplet(){
//        if($this->addtable){
//            $tableName = ucfirst($this->addtable);
//            $obj = new $tableName;
//        }
//
//    }

}

<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class Admin extends Authenticatable
{
    use Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uname', 'pwd', 'userid','loginip','logintime'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];


    public function getAuthPassword()
    {
        return $this->pwd;
    }


    public function rewriteAdminChannel($typeId = 0){
        $list=  [];
        if($typeId){
            $typeId = explode(',', $typeId);
            foreach($typeId as $value){
                $_list = Arctype::where('typeid', $value)->get();
                if(!empty($_list)){
                    foreach($_list as $v){
                        $list[] = $v['id'];
                        $result = self::rewriteAdminChannel($v['id']);
                        if(!empty($result)){
                            $list = array_merge($result, $list);
                            $list = array_unique($list);
                        }
                    }
                }
            }
        }
        return $list;
    }



}

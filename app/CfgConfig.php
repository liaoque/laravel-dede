<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CfgConfig extends Model
{



    //
    protected $table = 'cfg_config';

    public $cfg_df_ext = '.html';
    public $cfg_df_namerule = '{typedir}/{Y}/{M}{D}/{aid}';


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->cfg_df_namerule .= $this->cfg_df_ext;
    }


    public static function sysConfig()
    {
        $data = self::get()->toArray();
        $cfgConfig = new self;
        array_map(function ($v) use ($cfgConfig) {
            $cfgConfig[$v['config_name']] = $v['value'];
        }, $data);
        return $cfgConfig;
    }

    public static function getAllConfig()
    {
        $data = self::get()->toArray();

        $data = array_map(function ($v) {
            return [
                $v['config_name'] => $v['value']
            ];
        }, $data);
        $data = call_user_func_array('array_merge', $data);
        return $data;
    }

//    public function getCfgBasehostAttribute($value)
//    {
//        return ucfirst($value);
//    }

}

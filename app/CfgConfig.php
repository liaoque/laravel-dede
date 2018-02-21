<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CfgConfig extends Model
{



    //
    protected $table = 'cfg_config';

    public $cfg_df_ext = '.html';
    public $cfg_df_namerule = '{typedir}/{Y}/{M}{D}/{aid}';
    public $cfg_basedir = '';
    public $cfg_mainsite = '';


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->cfg_df_namerule .= $this->cfg_df_ext;
        $this->cfg_basedir = app()->basePath();
        $this->cfg_mainsite = '';
    }


    public static function sysConfig()
    {
        static $cfgConfig;
        if(empty($cfgConfig)){
            $data = self::get()->toArray();
            $cfgConfig = new self;
            array_map(function ($v) use ($cfgConfig) {
                $cfgConfig[$v['config_name']] = $v['value'];
            }, $data);
            if($cfgConfig->cfg_multi_site == 'Y'){
                $cfgConfig->cfg_mainsite = $cfgConfig->cfg_basehost;
            }

        }
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

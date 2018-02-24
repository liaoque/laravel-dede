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
        if (empty($cfgConfig)) {
            $data = self::get()->toArray();
            $cfgConfig = new self;
            array_map(function ($v) use ($cfgConfig) {
                $cfgConfig[$v['config_name']] = $v['value'];
            }, $data);
            if ($cfgConfig->cfg_multi_site == 'Y') {
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

    public static function getCfgCs()
    {
        $list = Arctype::all()->keyBy('id')->toArray();
        $list = array_map(function ($row) {
            return [
                $row->reid, $row->channeltype, $row->issend, $row->typename
            ];
        }, $list);

        return $list;
    }

    public function getCfgMainsiteAttribute()
    {
        if ($this->cfg_multi_site == 'Y') {
            $cfgMainsite = $this->cfg_basehost;
        } else {
            $cfgMainsite = '';
        }
        return $cfgMainsite;
    }

    public function getCfgMemberurlAttribute()
    {
        return $this->cfg_mainsite . $this->cfg_member_dir;
    }

    public function getCfgMemberDirlAttribute()
    {
        return $cfg_member_dir = $this->cfg_cmspath . '/member';
    }

    public function getCfgPhpurlAttribute()
    {
        return $cfg_phpurl = $this->cfg_mainsite . $this->cfg_plus_dir;
    }

    public function getCfgPlusDirlAttribute()
    {
        return $cfg_plus_dir = $this->cfg_cmspath . '/plus';
    }


//    public function getCfgBasehostAttribute($value)
//    {
//        return ucfirst($value);
//    }

}

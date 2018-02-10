<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/10
 * Time: 10:57
 */

class common
{
    public static function MfTemplet($tmpdir, $cfg_df_style = null)
    {
        if (!$cfg_df_style) {
            $cfg_df_style = CfgConfig::sysConfig()->cfg_df_style;
        }
        $tmpdir = str_replace("{style}", $cfg_df_style, $tmpdir);
        $tmpdir = preg_replace("/\/{1,}/", "/", $tmpdir);
        return $tmpdir;
    }
}
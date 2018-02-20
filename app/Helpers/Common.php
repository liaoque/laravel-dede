<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/10
 * Time: 10:57
 */
namespace App\Helpers;

class Common
{
    public static function mfTemplet($tmpdir, $cfg_df_style = null)
    {
        if (!$cfg_df_style) {
            $cfg_df_style = CfgConfig::sysConfig()->cfg_df_style;
        }
        $tmpdir = str_replace("{style}", $cfg_df_style, $tmpdir);
        $tmpdir = preg_replace("/\/{1,}/", "/", $tmpdir);
        return $tmpdir;
    }

    public static function  htmlReplace($str,$rptype=0)
    {
        $str = stripslashes($str);
        $str = preg_replace("/<[\/]{0,1}style([^>]*)>(.*)<\/style>/i", '', $str);//2011-06-30 禁止会员投稿添加css样式 (by:织梦的鱼)
        if($rptype==0)
        {
            $str = htmlspecialchars($str);
        }
        else if($rptype==1)
        {
            $str = htmlspecialchars($str);
            $str = str_replace("　", ' ', $str);
            $str = preg_replace("/[\r\n\t ]{1,}/", ' ', $str);
        }
        else if($rptype==2)
        {
            $str = htmlspecialchars($str);
            $str = str_replace("　", '', $str);
            $str = preg_replace("/[\r\n\t ]/", '', $str);
        }
        else
        {
            $str = preg_replace("/[\r\n\t ]{1,}/", ' ', $str);
            $str = preg_replace('/script/i', 'ｓｃｒｉｐｔ', $str);
            $str = preg_replace("/<[\/]{0,1}(link|meta|ifr|fra)[^>]*>/i", '', $str);
        }
        return addslashes($str);
    }

    public static function getSysTemplets($filename)
    {
        $sysConfig=CfgConfig::sysConfig();
        return getTemplets($sysConfig->cfg_basedir.$sysConfig->cfg_templets_dir.'/system/'.$filename);
    }

    public static function getTemplets($filename)
    {
        return file_exists($filename) ? file_get_contents($filename) : '';
    }


}
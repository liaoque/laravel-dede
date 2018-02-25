<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/25
 * Time: 14:23
 */

if (!function_exists('cn_substr')) {
    function cn_substr($str, $slen, $startdd = 0)
    {
        return mb_substr($str, $startdd, $startdd);
    }
}

if (!function_exists('makeOneTag')) {
    function makeOneTag($dtp, $refObj, $parfield = 'Y')
    {
        \App\Helpers\Common::makeOneTag($dtp, $refObj, $parfield);
    }
}

if (!function_exists('getFileNewName')) {
    function getFileNewName($aid, $typeid, $timetag, $title, $ismake = 0, $rank = 0, $namerule = '', $typedir = '', $money = 0, $filename = '')
    {
        return \App\Helpers\Common::getFileNewName($aid, $typeid, $timetag, $title, $ismake, $rank, $namerule, $typedir, $money,
            $filename);
    }
}


/**
 *  获得文件相对于主站点根目录的物理文件名(动态网址返回url)
 *
 * @param     int $aid 文档ID
 * @param     int $typeid 栏目ID
 * @param     int $timetag 时间戳
 * @param     string $title 标题
 * @param     int $ismake 是否生成
 * @param     int $rank 阅读权限
 * @param     string $namerule 名称规则
 * @param     string $typedir 栏目dir
 * @param     string $money 需要金币
 * @param     string $filename 文件名称
 * @return    string
 */
if (!function_exists('getFileName')) {
    function getFileName($aid, $typeid, $timetag, $title, $ismake = 0, $rank = 0, $namerule = '', $typedir = '',
                         $money = 0, $filename = '')
    {
        return \App\Helpers\Common::getFileName($aid, $typeid, $timetag, $title, $ismake, $rank, $namerule,
            $typedir , $money, $filename );
    }
}


/**
 *  创建目录
 *
 * @access    public
 * @param     string  $spath 目录名称
 * @return    string
 */
if (!function_exists('createDir')) {
    function createDir($spath)
    {
        return Storage::makeDirectory($spath);
    }
}


/**
 *  获得文章网址
 *  如果要获得文件的路径，直接用
 *  getFileUrl($aid,$typeid,$timetag,$title,$ismake,$rank,$namerule,$typedir,$money)
 *  即是不指定站点参数则返回相当对根目录的真实路径
 *
 * @param     int $aid 文档ID
 * @param     int $typeid 栏目ID
 * @param     int $timetag 时间戳
 * @param     string $title 标题
 * @param     int $ismake 是否生成
 * @param     int $rank 阅读权限
 * @param     string $namerule 名称规则
 * @param     string $typedir 栏目dir
 * @param     string $money 需要金币
 * @param     string $filename 文件名称
 * @param     string $moresite 多站点
 * @param     string $siteurl 站点地址
 * @param     string $sitepath 站点路径
 * @return    string
 */
function getFileUrl($aid, $typeid, $timetag, $title, $ismake = 0, $rank = 0, $namerule = '', $typedir = '',
                                  $money = 0, $filename = '', $moresite = 0, $siteurl = '', $sitepath = '')
{
    return \App\Helpers\Common::getFileUrl($aid, $typeid, $timetag, $title, $ismake, $rank, $namerule,
        $typedir , $money, $filename );
}
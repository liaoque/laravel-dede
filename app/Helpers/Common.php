<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/10
 * Time: 10:57
 */

namespace App\Helpers;

use App\CfgConfig;

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

    public static function htmlReplace($str, $rptype = 0)
    {
        $str = stripslashes($str);
        $str = preg_replace("/<[\/]{0,1}style([^>]*)>(.*)<\/style>/i", '', $str);//2011-06-30 禁止会员投稿添加css样式 (by:织梦的鱼)
        if ($rptype == 0) {
            $str = htmlspecialchars($str);
        } else if ($rptype == 1) {
            $str = htmlspecialchars($str);
            $str = str_replace("　", ' ', $str);
            $str = preg_replace("/[\r\n\t ]{1,}/", ' ', $str);
        } else if ($rptype == 2) {
            $str = htmlspecialchars($str);
            $str = str_replace("　", '', $str);
            $str = preg_replace("/[\r\n\t ]/", '', $str);
        } else {
            $str = preg_replace("/[\r\n\t ]{1,}/", ' ', $str);
            $str = preg_replace('/script/i', 'ｓｃｒｉｐｔ', $str);
            $str = preg_replace("/<[\/]{0,1}(link|meta|ifr|fra)[^>]*>/i", '', $str);
        }
        return addslashes($str);
    }

    public static function getSysTemplets($filename)
    {
        $sysConfig = CfgConfig::sysConfig();
        return getTemplets($sysConfig->cfg_basedir . $sysConfig->cfg_templets_dir . '/system/' . $filename);
    }

    public static function getTemplets($filename)
    {
        return file_exists($filename) ? file_get_contents($filename) : '';
    }

    public static function attDef($oldvar, $nv)
    {
        return empty($oldvar) ? $nv : $oldvar;
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
    public static function getFileUrl($aid, $typeid, $timetag, $title, $ismake = 0, $rank = 0, $namerule = '', $typedir = '',
                                      $money = 0, $filename = '', $moresite = 0, $siteurl = '', $sitepath = '')
    {
        $articleUrl = self::getFileName($aid, $typeid, $timetag, $title, $ismake, $rank, $namerule, $typedir, $money, $filename);
        $sitepath = self::mfTypedir($sitepath);

        //是否强制使用绝对网址
        if (CfgConfig::sysConfig()->cfg_multi_site == 'Y') {
            if ($siteurl == '') {
                $siteurl = CfgConfig::sysConfig()->cfg_basehost;
            }
            if ($moresite == 1) {
                $articleUrl = preg_replace("#^" . $sitepath . '#', '', $articleUrl);
            }
            if (!preg_match("/http:/", $articleUrl)) {
                $articleUrl = $siteurl . $articleUrl;
            }
        }

        return $articleUrl;
    }


    /**
     *  获得文件相对于主站点根目录的物理文件名(动态网址返回url)
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

    public static function getFileName($aid, $typeid, $timetag, $title, $ismake = 0, $rank = 0, $namerule = '', $typedir = '', $money = 0, $filename = '')
    {
        $sysConfig = CfgConfig::sysConfig();
        $cfg_rewrite = $sysConfig->cfg_rewrite;
        $cfg_cmspath = $sysConfig->cfg_cmspath;
        $cfg_arcdir = $sysConfig->cfg_arcdir;
        $cfg_special = $sysConfig->cfg_special;
        $cfg_arc_dirname = $sysConfig->cfg_arc_dirname;

        //没指定栏目时用固定规则（专题）
        if (empty($namerule)) {
            $namerule = $cfg_special . '/arc-{aid}.html';
            $typeid = -1;
        }
        if ($rank != 0 || $ismake == -1 || $typeid == 0 || $money > 0) {
            //动态文章
            if ($cfg_rewrite == 'Y') {
                return CfgConfig::sysConfig()->cfg_plus_dir . "/view-" . $aid . '-1.html';
            } else {
                return CfgConfig::sysConfig()->cfg_phpurl . "/view.php?aid=$aid";
            }
        } else {
            $articleDir = self::mfTypedir($typedir);
            $articleRule = strtolower($namerule);
            if ($articleRule == '') {
                $articleRule = strtolower(CfgConfig::sysConfig()->cfg_df_namerule);
            }
            if ($typedir == '') {
                $articleDir = $cfg_cmspath . $cfg_arcdir;
            }
            $dtime = self::getDateMk($timetag);
            list($y, $m, $d) = explode('-', $dtime);
            $arr_rpsource = array('{typedir}', '{y}', '{m}', '{d}', '{timestamp}', '{aid}', '{cc}');
            $arr_rpvalues = array($articleDir, $y, $m, $d, $timetag, $aid, dd2char($m . $d . $aid . $y));
            if ($filename != '') {
                $articleRule = dirname($articleRule) . '/' . $filename . $GLOBALS['cfg_df_ext'];
            }
            $articleRule = str_replace($arr_rpsource, $arr_rpvalues, $articleRule);
            if (preg_match("/\{p/", $articleRule)) {
                $articleRule = str_replace('{pinyin}', GetPinyin($title) . '_' . $aid, $articleRule);
                $articleRule = str_replace('{py}', GetPinyin($title, 1) . '_' . $aid, $articleRule);
            }
            $articleUrl = '/' . preg_replace("/^\//", '', $articleRule);
            if (preg_match("/index\.html/", $articleUrl) && $cfg_arc_dirname == 'Y') {
                $articleUrl = str_replace('index.html', '', $articleUrl);
            }
            return $articleUrl;
        }
    }

    /**
     *  返回格式化(Y-m-d)的日期
     *
     * @param     int $mktime 时间戳
     * @return    string
     */
    public static function getDateMk($mktime)
    {
        if ($mktime == "0") return "暂无";
        else return date("Y-m-d", $mktime);
    }

    /**
     *  栏目目录规则
     * @param     string $typedir 栏目目录
     * @return    string
     */
    public static function mfTypedir($typedir)
    {
        if (preg_match("/^http:|^ftp:/i", $typedir)) return $typedir;
        $typedir = str_replace("{cmspath}", CfgConfig::sysConfig()->cfg_cmspath, $typedir);
        $typedir = preg_replace("/\/{1,}/", "/", $typedir);
        return $typedir;
    }

    /**
     *  获得指定类目的URL链接
     *  对于使用封面文件和单独页面的情况，强制使用默认页名称
     *
     * @param     int $typeid 栏目ID
     * @param     string $typedir 栏目目录
     * @param     int $isdefault 是否默认
     * @param     string $defaultname 默认名称
     * @param     int $ispart 栏目属性
     * @param     string $namerule2 名称规则
     * @param     string $moresite 多站点
     * @param     string $siteurl 站点地址
     * @param     string $sitepath 站点目录
     * @return    string
     */
    public static function getTypeUrl($typeid, $typedir, $isdefault, $defaultname, $ispart, $namerule2, $moresite = 0, $siteurl = '', $sitepath = '')
    {
        global $cfg_typedir_df;
        $typedir = self::mfTypedir($typedir);
        $sitepath = self::mfTypedir($sitepath);
        if ($isdefault == -1) {
            //动态
            $reurl = CfgConfig::sysConfig()->cfg_phpurl . "/list.php?tid=" . $typeid;
        } else if ($ispart == 2) {
            //跳转网址
            $reurl = $typedir;
            return $reurl;
        } else {
            if ($isdefault == 0 && $ispart == 0) {
                $reurl = str_replace("{page}", "1", $namerule2);
                $reurl = str_replace("{tid}", $typeid, $reurl);
                $reurl = str_replace("{typedir}", $typedir, $reurl);
            } else {
                if ($cfg_typedir_df == 'N' || $isdefault == 0) $reurl = $typedir . '/' . $defaultname;
                else $reurl = $typedir . '/';
            }
        }

        if (!preg_match("/^http:\/\//", $reurl)) {
            $reurl = preg_replace("/\/{1,}/i", '/', $reurl);
        }

        if (CfgConfig::sysConfig()->cfg_multi_site == 'Y') {
            if ($siteurl == '') {
                $siteurl = CfgConfig::sysConfig()->cfg_basehost;
            }
            if ($moresite == 1) {
                $reurl = preg_replace("#^" . $sitepath . "#", '', $reurl);
            }
            if (!preg_match("/^http:\/\//", $reurl)) {
                $reurl = $siteurl . $reurl;
            }
        }
        return $reurl;
    }

    /**
     *  获得某id的所有下级id
     *
     * @param     string $id 栏目id
     * @param     string $channel 模型ID
     * @param     string $addthis 是否包含本身
     * @return    string
     */
    public static function getSonIds($id, $channel = 0, $addthis = true)
    {
        $cfg_Cs = CfgConfig::getCfgCs();
        $idArray = self::getSonIdsLogic($id, $cfg_Cs, $channel, $addthis);
        $rquery = join(',', $idArray);
//        $rquery = preg_replace("/,$/", '', $rquery);
        return $rquery;
    }

    //递归逻辑
    public static function getSonIdsLogic($id, $sArr, $channel = 0, $addthis = false)
    {
        $idArray = [];
        if ($id != 0 && $addthis) {
            $idArray[$id] = $id;
        }
        if (is_array($sArr)) {
            foreach ($sArr as $k => $v) {
                if ($v[0] == $id && ($channel == 0 || $v[1] == $channel)) {
                    $idArray = array_merge($idArray, self::getSonIdsLogic($k, $sArr, $channel, true));
                }
            }
        }
        return $idArray;

    }

    /**
     *  获取执行时间
     *  例如:$t1 = Common::execTime();
     *       在一段内容处理之后:
     *       $t2 = Common::execTime();
     *  我们可以将2个时间的差值输出:echo $t2-$t1;
     *
     * @return    int
     */
    public static function execTime()
    {
        $time = explode(" ", microtime());
        $usec = (double)$time[0];
        $sec = (double)$time[1];
        return $sec + $usec;
    }

    public static function makeOneTag($dtp, $refObj, $parfield = 'Y')
    {
        $cfg_disable_tags = CfgConfig::sysConfig()->cfg_disable_tags;
        $disable_tags = explode(',', $cfg_disable_tags);
        $alltags = array();
        $dtp->setRefObj($refObj);
        //读取自由调用tag列表
        $dh = dir(app_path() . '/Helpers/TagLib');
        while ($filename = $dh->read()) {
            if (preg_match("/\w+Lib\./", $filename)) {
                $alltags[] = str_replace('.php', '', $filename);
            }
        }
        $dh->close();

        //遍历tag元素
        if (!is_array($dtp->cTags)) {
            return '';
        }
        foreach ($dtp->cTags as $tagid => $ctag) {
            $tagname = $ctag->getName();
            if ($tagname == 'field' && $parfield == 'Y') {
                $vname = $ctag->getAtt('name');
                if ($vname == 'array' && isset($refObj->fields)) {
                    $dtp->assign($tagid, $refObj->fields);
                } else if (isset($refObj->fields[$vname])) {
                    $dtp->assign($tagid, $refObj->fields[$vname]);
                } else if ($ctag->getAtt('noteid') != '') {
                    if (isset($refObj->fields[$vname . '_' . $ctag->getAtt('noteid')])) {
                        $dtp->assign($tagid, $refObj->fields[$vname . '_' . $ctag->getAtt('noteid')]);
                    }
                }
                continue;
            }

            //由于考虑兼容性，原来文章调用使用的标记别名统一保留，这些标记实际调用的解析文件为inc_arclist.php
            if (preg_match("/^(artlist|likeart|hotart|imglist|imginfolist|coolart|specart|autolist)$/", $tagname)) {
                $tagname = 'arclist';
            }
            if ($tagname == 'friendlink') {
                $tagname = 'flink';
            }
            if (in_array($tagname, $alltags)) {
                if (in_array($tagname, $disable_tags)) {
                    continue;
                }
                $className = '\App\Helpers\TagLib\\' . ucfirst($tagname) . 'Lib';
                $funcname = 'lib_' . lcfirst($tagname);
                $dtp->assign($tagid, $className::$funcname($ctag, $refObj));
            }
        }
    }


    /**
     *  获得新文件名(本函数会自动创建目录)
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
    public static function getFileNewName($aid, $typeid, $timetag, $title, $ismake = 0, $rank = 0, $namerule = '', $typedir = '', $money = 0,
                                          $filename = '')
    {
        $cfg_arc_dirname = CfgConfig::sysConfig()->cfg_arc_dirname;
        $articlename = self::getFileName($aid, $typeid, $timetag, $title, $ismake, $rank, $namerule, $typedir, $money,
            $filename);

        if (preg_match("/\?/", $articlename)) {
            return $articlename;
        }

        if ($cfg_arc_dirname == 'Y' && preg_match("/\/$/", $articlename)) {
            $articlename = $articlename . "index.html";
        }

        $slen = strlen($articlename) - 1;
        for ($i = $slen; $i >= 0; $i--) {
            if ($articlename[$i] == '/') {
                $subpos = $i;
                break;
            }
        }
        $okdir = substr($articlename, 0, $subpos);
        createDir($okdir);
        return $articlename;
    }


    /**
     *  清理附件，如果关连的文档ID，先把上一批附件传给这个文档ID
     *
     * @access    public
     * @param     string  $aid  文档ID
     * @param     string  $title  文档标题
     * @return    empty
     */
    function ClearMyAddon($aid=0, $title='')
    {
        global $dsql;
        $cacheFile = DEDEDATA.'/cache/addon-'.session_id().'.inc';
        $_SESSION['bigfile_info'] = array();
        $_SESSION['file_info'] = array();
        if(!file_exists($cacheFile))
        {
            return ;
        }

        //把附件与文档关连
        if(!empty($aid))
        {
            include($cacheFile);
            foreach($myaddons as $addons)
            {
                if(!empty($title)) {
                    $dsql->ExecuteNoneQuery("Update `#@__uploads` set arcid='$aid',title='$title' where aid='{$addons[0]}'");
                }
                else {
                    $dsql->ExecuteNoneQuery("Update `#@__uploads` set arcid='$aid' where aid='{$addons[0]}' ");
                }
            }
        }
        @unlink($cacheFile);
    }




}
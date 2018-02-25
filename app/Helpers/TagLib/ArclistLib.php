<?php

namespace App\Helpers\TagLib;

/**
 * 文章列表调用标记
 *
 * 9:19 2010年7月13日:修正对isweight属性的支持
 *
 * @version        $Id: arclist.lib.php 3 9:19 2010年7月13日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
use App\ArcCache;
use App\Archives;
use App\Arcmulti;
use App\Arctype;
use App\CfgConfig;
use App\ChannelType;
use App\Helpers\Common;
use App\Helpers\DedeTagParse;
use Illuminate\Support\Collection;

/**
 *  arclist解析标签
 *
 * @access    public
 * @param     object $cTag 解析标签
 * @param     object $refObj 引用对象
 * @return    string  成功后返回解析后的标签内容
 */
/*>>dede>>
<name>文档列表</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>获取指定文档列表</description>
<demo>
{dede:arclist  flag='h' typeid='' row='' col='' titlelen='' infolen='' imgwidth='' imgheight='' listtype='' orderby='' keyword='' limit='0,1'}
<a href='[field:arcurl/]'>[field:title/]</a>
{/dede:arclist}
</demo>
<attributes>
    <iterm>col:分多少列显示（默认为单列），5.3版中本属性可以通过多种方式进行多行显示</iterm>
    <iterm>row:返回文档列表总数</iterm> 
    <iterm>typeid:栏目ID,在列表模板和档案模板中一般不需要指定，在首页模板中允许用","分开表示多个栏目</iterm>
    <iterm>getall:在没有指定这属性的情况下,在栏目页、文章页模板,不会获取以","分开的多个栏目的下级子类</iterm>
    <iterm>titlelen:标题长度 等同于titlelength</iterm>
    <iterm>infolen:表示内容简介长度 等同于infolength</iterm>
    <iterm>imgwidth:缩略图宽度</iterm>
    <iterm>imgheight:缩略图高度</iterm>
    <iterm>listtype: 栏目类型 image含有缩略图 commend推荐</iterm>
    <iterm>orderby:文档排序方式</iterm>
    <iterm>keyword:含有指定关键字的文档列表，多个关键字用","分</iterm>
    <iterm>innertext:单条记录样式</iterm>
    <iterm>aid:指定文档ID</iterm>
    <iterm>idlist:提取特定文档（文档ID</iterm>
    <iterm>channelid:频道ID</iterm>
    <iterm>limit:（起始ID从0开始）表示限定的记录范围（如：limit='1,2'  表示从ID为1的记录开始，取2条记录</iterm>
    <iterm>flag:自定义属性值：头条[h]推荐[c]图片[p]幻灯[f]滚动[s]跳转[j]图文[a]加粗[b]</iterm>
    <iterm>noflag:同flag，但这里是表示不包含这些属性</iterm>
    <iterm>orderway:值为 desc 或 asc ，指定排序方式是降序还是顺向排序，默认为降序</iterm>
    <iterm>subday:表示在多少天以内的文档</iterm>
</attributes> 
>>dede>>*/

class ArclistLib
{
    function lib_arclist($cTag, $refObj)
    {
        global $envs;

        $autopartid = 0;
        $tagid = '';
        $tagname = $cTag->getTagName();
        $channelid = $cTag->getAtt('channelid');

        //增加对分页内容的处理
        $pagesize = $cTag->getAtt('pagesize');
        if ($pagesize == '') {
            $multi = 0;
        } else {
            $tagid = $cTag->getAtt('tagid');
        }
        // arclist是否需要weight排序,默认为"N",如果需要排序则设置为"Y"
        $isweight = $cTag->getAtt('isweight');

        if ($tagname == 'imglist' || $tagname == 'imginfolist') {
            $listtype = 'image';
        } else if ($tagname == 'specart') {
            $channelid = -1;
            $listtype = '';
        } else if ($tagname == 'coolart') {
            $listtype = 'commend';
        } else if ($tagname == 'autolist') {
            $autopartid = $cTag->getAtt('partsort');
        } else {
            $listtype = $cTag->getAtt('type');
        }

        //排序
        if ($cTag->getAtt('sort') != '') $orderby = $cTag->getAtt('sort');
        else if ($tagname == 'hotart') $orderby = 'click';
        else $orderby = $cTag->getAtt('orderby');

        //对相应的标记使用不同的默认innertext
        if (trim($cTag->GetInnerText()) != '') $innertext = $cTag->GetInnerText();
        else if ($tagname == 'imglist') $innertext = Common::getSysTemplets('part_imglist.htm');
        else if ($tagname == 'imginfolist') $innertext = Common::getSysTemplets('part_imginfolist.htm');
        else $innertext = Common::getSysTemplets("part_arclist.htm");

        //兼容titlelength
        if ($cTag->getAtt('titlelength') != '') $titlelen = $cTag->getAtt('titlelength');
        else $titlelen = $cTag->getAtt('titlelen');

        //兼容infolength
        if ($cTag->getAtt('infolength') != '') $infolen = $cTag->getAtt('infolength');
        else $infolen = $cTag->getAtt('infolen');

        $typeid = trim($cTag->getAtt('typeid'));
        if (empty($typeid)) {
            $typeid = (isset($refObj->fields['typeid']) ? $refObj->fields['typeid'] : $envs['typeid']);
        }

        if ($listtype == 'autolist') {
            $typeid = self::lib_getAutoChannelID($cTag->getAtt('partsort'), $typeid);
        }

        if ($cTag->getAtt('att') == '') {
            $flag = $cTag->getAtt('flag');
        } else {
            $flag = $cTag->getAtt('att');
        }

        return self::lib_arclistDone
        (
            $refObj, $cTag, $typeid, $cTag->getAtt('row'), $cTag->getAtt('col'), $titlelen, $infolen,
            $cTag->getAtt('imgwidth'), $cTag->getAtt('imgheight'), $listtype, $orderby,
            $cTag->getAtt('keyword'), $innertext, $envs['aid'], $cTag->getAtt('idlist'), $channelid,
            $cTag->getAtt('limit'), $flag, $cTag->getAtt('orderway'), $cTag->getAtt('subday'), $cTag->getAtt('noflag'),
            $tagid, $pagesize, $isweight
        );
    }

    /**
     *  arclist解析函数
     *
     * @access    public
     * @param     object $refObj 引用对象
     * @param     object $cTag 标签
     * @param     int $typeid 栏目ID
     * @param     int $row 调用行数
     * @param     int $titlelen 字符串长度
     * @param     int $infolen 描述信息长度
     * @param     int $imgwidth 图片宽度
     * @param     int $imgheight 图片高度
     * @param     string $listtype 列表类型
     * @param     string $orderby 排列顺序
     * @param     string $keyword 关键词
     * @param     string $innertext 底层模板
     * @param     int $arcid 文档ID
     * @param     string $idlist ID列表
     * @param     int $channelid 频道ID
     * @param     string $limit 限制
     * @param     string $att 属性
     * @param     string $order 排序类型
     * @param     int $subday 天内
     * @param     string $noflag 属性标记
     * @param     string $tagid 标签id
     * @param     string $pagesize 显示条数
     * @param     string $isweight 是否需要对检索出来的内容按照weight排序
     * @return    string
     */
    public static function lib_arclistDone($refObj, $cTag, $typeid = 0, $row = 10, $col = 1, $titlelen = 30, $infolen = 160,
                                           $imgwidth = 120, $imgheight = 90, $listtype = 'all', $orderby = 'default', $keyword = '',
                                           $innertext = '', $arcid = 0, $idlist = '', $channelid = 0, $limit = '', $att = '', $order = 'desc', $subday = 0, $noflag = '', $tagid = '', $pagesize = 0, $isweight = 'N')
    {
        $cfg_keyword_like = CfgConfig::sysConfig()->cfg_keyword_like;
        $cfg_index_cache = CfgConfig::sysConfig()->cfg_index_cache;
        $cfg_cache_type = CfgConfig::sysConfig()->cfg_cache_type;
        $cfg_digg_update = CfgConfig::sysConfig()->cfg_digg_update;
        $row = Common::attDef($row, 10);
        $titlelen = Common::attDef($titlelen, 30);
        $infolen = Common::attDef($infolen, 160);
        $imgwidth = Common::attDef($imgwidth, 120);
        $imgheight = Common::attDef($imgheight, 120);
        $listtype = Common::attDef($listtype, 'all');
        $arcid = Common::attDef($arcid, 0);
        $channelid = Common::attDef($channelid, 0);
        $orderby = Common::attDef($orderby, 'default');
        $orderWay = Common::attDef($order, 'desc');
        $subday = Common::attDef($subday, 0);
        $pagesize = Common::attDef($pagesize, 0);
        $line = $row;
        $orderby = strtolower($orderby);
        $keyword = trim($keyword);
        $innertext = trim($innertext);

        $tablewidth = $cTag->getAtt('tablewidth');
        $writer = $cTag->getAtt('writer');
        if ($tablewidth == "") $tablewidth = 100;
        if (empty($col)) $col = 1;
        $colWidth = ceil(100 / $col);
        $tablewidth = $tablewidth . "%";
        $colWidth = $colWidth . "%";

        //记录属性,以便分页样式统一调用
        $attarray = compact("row", "titlelen", 'infolen', 'imgwidth', 'imgheight', 'listtype',
            'arcid', 'channelid', 'orderby', 'orderWay', 'subday', 'pagesize',
            'orderby', 'keyword', 'tablewidth', 'col', 'colWidth');

        if ($innertext == '') $innertext = Common::getSysTemplets('part_arclist.htm');
        if (@$cTag->getAtt('getall') == 1) $getall = 1;
        else $getall = 0;

        if ($att == '0') $att = '';
        if ($att == '3') $att = 'f';
        if ($att == '1') $att = 'h';

        $orwheres = array();
        //按不同情况设定SQL条件 排序方式
        $query = (new Archives)->newQuery();
        if ($idlist == '') {
            if ($orderby == 'near' && $cfg_keyword_like == 'N') {
                $keyword = '';
            }

            if ($writer == 'this') {
                $wmid = isset($refObj->fields['mid']) ? $refObj->fields['mid'] : 0;
                $query->where('mid', $wmid);
//                $orwheres[] = " arc.mid = '$wmid' ";
            }

            //时间限制(用于调用最近热门文章、热门评论之类)，这里的时间只能计算到天，否则缓存功能将无效
            if ($subday > 0) {
                $ntime = gmmktime(0, 0, 0, gmdate('m'), gmdate('d'), gmdate('Y'));
                $limitday = $ntime - ($subday * 24 * 3600);
                $query->where('senddate', '>', $limitday);
//                $orwheres[] = " arc.senddate > $limitday ";
            }
            //关键字条件
            if ($keyword != '') {
                $keyword = str_replace(',', '|', $keyword);
                $query->whereRaw(" CONCAT(arc.title,arc.keywords) REGEXP ? ", [$keyword]);
//                $orwheres[] = " CONCAT(arc.title,arc.keywords) REGEXP '$keyword' ";
            }
            //文档属性
            if (preg_match('/commend/i', $listtype)) {
                $query->whereRaw(" FIND_IN_SET('c', arc.flag)>0 ");
//                $orwheres[] = " FIND_IN_SET('c', arc.flag)>0  ";
            }

            if (preg_match('/image/i', $listtype)) {
                $query->whereRaw(" FIND_IN_SET('p', arc.flag)>0 ");
//                $orwheres[] = " FIND_IN_SET('p', arc.flag)>0  ";
            }
            if ($att != '') {
                $flags = explode(',', $att);
                for ($i = 0; isset($flags[$i]); $i++) {
//                    $orwheres[] = " FIND_IN_SET('{$flags[$i]}', arc.flag)>0 ";
                    $query->whereRaw(" FIND_IN_SET(?, arc.flag)>0 ", $flags[$i]);
                }

            }

            if (!empty($typeid) && $typeid != 'top') {
                //指定了多个栏目时，不再获取子类的id
                if (preg_match('#,#', $typeid)) {
                    //指定了getall属性或主页模板例外
                    if ($getall == 1 || empty($refObj->fields['typeid'])) {
                        $typeids = explode(',', $typeid);
                        foreach ($typeids as $ttid) {
                            $typeidss[] = Common::getSonIds($ttid);
                        }
                        $typeidStr = join(',', $typeidss);
                        $typeidss = explode(',', $typeidStr);
                        $typeidssok = array_unique($typeidss);
//                        $typeid = join(',', $typeidssok);
                        $typeid = $typeidssok;
                    }
//                    $orwheres[] = " arc.typeid IN ($typeid) ";
                    $query->whereIn("typeid", $typeid);
                } else {
                    //处理交叉栏目
                    $CrossID = [];
                    if ($cTag->getAtt('cross') == '1') {
//                        $arr = $dsql->GetOne("SELECT `id`,`topid`,`cross`,`crossid`,`ispart`,`typename` FROM `#@__arctype` WHERE id='$typeid' ");
                        $arr = Arctype::find($typeid)->toArray();
                        if ($arr['cross'] == 0 || ($arr['cross'] == 2 && trim($arr['crossid'] == ''))) {
//                            $orwheres[] = ' arc.typeid IN (' . Common::getSonIds($typeid) . ')';
                            $query->whereIn("typeid", Common::getSonIds($typeid));
                        } else {
                            $selqueryList = [];
                            if ($arr['cross'] == 1) {
                                $selqueryList = Arctype::where('typename', 'like', $arr['typename'])->where('id', '<>', $typeid)->where('topid', '<>', $typeid)->all()->toArray();
//                                $selquery = "SELECT id,topid FROM `#@__arctype` WHERE typename LIKE '{$arr['typename']}' AND id<>'{$typeid}' AND topid<>'{$typeid}'  ";
                            } else {
                                $arr['crossid'] = preg_replace('#[^0-9,]#', '', trim($arr['crossid']));
                                if ($arr['crossid'] != '') {
//                                    $selquery = "SELECT id,topid FROM `#@__arctype` WHERE id IN('{$arr['crossid']}') AND id<>'{$typeid}' AND topid<>'{$typeid}'  ";
                                    $selqueryList = Arctype::whereIn('id', $arr['crossid'])->where('id', '<>', $typeid)->where('topid', '<>', $typeid)->all()->toArray();
                                }
                            }

                            foreach ($selqueryList as $arr) {
                                $CrossID[] = $arr['id'];
//                                $CrossID .= ($CrossID == '' ? $arr['id'] : ',' . $arr['id']);
                            }
//                            if ($selqueryList != '') {
//                                $dsql->SetQuery($selquery);
//                                $dsql->Execute();
//                                while ($arr = $dsql->GetArray()) {
//                                    $CrossID .= ($CrossID == '' ? $arr['id'] : ',' . $arr['id']);
//                                }
//                            }
                        }
                    }
                    if ($CrossID) {
//                        $orwheres[] = ' arc.typeid IN (' . Common::getSonIds($typeid) . ')';
                        $query->whereIn("typeid", Common::getSonIds($typeid));
                    } else {
//                        $orwheres[] = ' arc.typeid IN (' . Common::getSonIds($typeid) . ',' . $CrossID . ')';
                        $query->whereIn("typeid", Common::getSonIds($typeid) . ',' . $CrossID);
                    }
                }
            }

            //频道ID
            if (preg_match('#spec#i', $listtype)) $channelid == -1;

            if (!empty($channelid)) {
                $query->where("channel", $channelid);
//                $orwheres[] = " And arc.channel = '$channelid' ";
            }

            if (!empty($noflag)) {
                if (!preg_match('#,#', $noflag)) {
//                    $orwheres[] = " FIND_IN_SET('$noflag', arc.flag)<1 ";
                    $query->whereRaw(" FIND_IN_SET(?, flag)<1 ", $noflag);
                } else {
                    $noflags = explode(',', $noflag);
                    foreach ($noflags as $noflag) {
                        if (trim($noflag) == '') continue;
                        $query->whereRaw(" FIND_IN_SET(?, flag)<1 ", $noflag);
//                        $orwheres[] = " FIND_IN_SET('$noflag', arc.flag)<1 ";
                    }
                }
            }

            $orwheres[] = ' arc.arcrank > -1 ';

            //由于这个条件会导致缓存功能失去意义，因此取消
            //if($arcid!=0) $orwheres[] = " arc.id<>'$arcid' ";
        }

        //文档排序的方式
        $ordersql = '';
        if ($orderby == 'hot' || $orderby == 'click') {
            $query->orderBy('click', $orderWay);
//            $ordersql = " ORDER BY arc.click $orderWay";
        } else if ($orderby == 'sortrank' || $orderby == 'pubdate') {
            $query->orderBy('sortrank', $orderWay);
//            $ordersql = " ORDER BY arc.sortrank $orderWay";
        } else if ($orderby == 'id') {
            $query->orderBy('id', $orderWay);
//            $ordersql = "  ORDER BY arc.id $orderWay";
        } else if ($orderby == 'near') {
            $query->orderByRaw(" ORDER BY ABS(arc.id - " . $arcid . ")");
//            $ordersql = " ORDER BY ABS(arc.id - " . $arcid . ")";
        } else if ($orderby == 'lastpost') {
            $query->orderBy('lastpost', $orderWay);
//            $ordersql = "  ORDER BY arc.lastpost $orderWay";
        } else if ($orderby == 'scores') {
            $query->orderBy('scores', $orderWay);
//            $ordersql = "  ORDER BY arc.scores $orderWay";
        } //功能：增加按好评数和差评数调用
        else if ($orderby == 'goodpost') {
            $query->orderBy('goodpost', $orderWay);
//            $ordersql = " order by arc.goodpost $orderWay";
        } else if ($orderby == 'badpost') {
            $query->orderBy('badpost', $orderWay);
//            $ordersql = " order by arc.badpost $orderWay";
        } else if ($orderby == 'rand') {
            $query->orderByRaw("  ORDER BY rand()");
//            $ordersql = "  ORDER BY rand()";
        } else {
            $query->orderBy('sortrank', $orderWay);
//            $ordersql = " ORDER BY arc.sortrank $orderWay";
        }

        //limit条件
        $limit = trim(preg_replace('#limit#is', '', $limit));
        if ($limit != '') {
//            $limitsql = " LIMIT $limit ";
            $limitarr = explode(',', $limit);
            $line = isset($limitarr[1]) ? $limitarr[1] : $line;
            $query->skip($limitarr[0])->take($line);


        } else {
            $query->skip(0)->take($line);
//            $limitsql = " LIMIT 0,$line ";
        }

//        $orwhere = '';
//        if (isset($orwheres[0])) {
//            $orwhere = join(' And ', $orwheres);
//            $orwhere = preg_replace("#^ And#is", '', $orwhere);
//            $orwhere = preg_replace("#And[ ]{1,}And#is", 'And ', $orwhere);
//        }
//        if ($orwhere != '') $orwhere = " WHERE $orwhere ";

        //获取附加表信息
        $addfield = trim($cTag->getAtt('addfields'));
        $addfieldsSql = '';
        $addfieldsSqlJoin = '';
        if ($addfield != '' && !empty($channelid)) {
//            $row = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
            $row = ChannelType::find($channelid)->toArray();
            if (isset($row['addtable']) && trim($row['addtable']) != '') {
                $addtable = trim($row['addtable']);
                $addfields = explode(',', $addfield);
                $row['addtable'] = trim($row['addtable']);
                $addfieldsSql = ",addf." . join(',addf.', $addfields);
                $addfieldsSqlJoin = " LEFT JOIN `$addtable` addf ON addf.aid = arc.id ";
            }
        }
//
//        $query = "SELECT arc.*,tp.typedir,tp.typename,tp.corank,tp.isdefault,tp.defaultname,tp.namerule,
//        tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath
//        $addfieldsSql
//        FROM `$maintable` arc LEFT JOIN `#@__arctype` tp on arc.typeid=tp.id
//        $addfieldsSqlJoin
//        $orwhere $ordersql $limitsql";

        //统一hash
        $taghash = md5(serialize($cTag) . $typeid);
        $needSaveCache = true;
        //进行tagid的默认处理
        if ($pagesize > 0) $tagid = Common::attDef($tagid, 'tag' . $taghash);

        if ($idlist != '' || $GLOBALS['_arclistEnv'] == 'index' || $cfg_index_cache == 0) {
            $needSaveCache = false;
        } else {
            $idlist = self::getArclistCache($taghash);
            if ($idlist != '') {
                $needSaveCache = false;
            }
            //如果使用的是内容缓存，直接返回结果
            if ($cfg_cache_type == 'content' && $idlist != '') {
                $idlist = ($idlist == 0 ? '' : $idlist);
                return $idlist;
            }
        }

        //指定了id或使用缓存中的id
        if ($idlist != '') {
            $query->whereIn('id', $idlist);
//            $query = "SELECT arc.*,tp.typedir,tp.typename,tp.corank,tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,tp.ispart,
//            tp.moresite,tp.siteurl,tp.sitepath
//            $addfieldsSql
//             FROM `$maintable` arc left join `#@__arctype` tp on arc.typeid=tp.id
//             $addfieldsSqlJoin
//          WHERE arc.id in($idlist) $ordersql ";
        }

        // 好评差评缓存更新
        if ($cfg_digg_update > 0) {
            if ($orderby == 'goodpost' || $orderby == 'badpost') {
//                $t1 = Common::execTime();
//                $postsql = "SELECT arc.id,arc.goodpost,arc.badpost,arc.scores
//				FROM `$maintable` arc
//				$orwhere $ordersql $limitsql";

//                if ($idlist != '') {
//                    $postsql = "SELECT arc.id,arc.goodpost,arc.badpost,arc.scores
//					 FROM `$maintable` arc
//				  WHERE arc.id in($idlist) $ordersql ";
//                }
//                $dsql->SetQuery($query);
//                $dsql->Execute('lit');
                $rowList = $query->all()->toArray();
                foreach ($rowList as $row) {
                    $prefix = 'diggCache';
                    $key = 'aid-' . $row['id'];
//                    $cacherow = GetCache($prefix, $key);
                    $setsql = array();
                    $archives = Archives::find($row['id']);
                    if (!empty($cacherow['scores']) && $cacherow['scores'] != $row['scores']) {
                        $archives->scores = $cacherow['scores'];
//                        $setsql[] = "scores = {$cacherow['scores']}";
                    }
                    if (!empty($cacherow['goodpost']) && $cacherow['goodpost'] != $row['goodpost']) {
                        $archives->goodpost = $cacherow['goodpost'];
//                        $setsql[] = "goodpost = {$cacherow['goodpost']}";
                    }
                    if (!empty($cacherow['badpost']) && $cacherow['badpost'] != $row['badpost']) {
                        $archives->badpost = $cacherow['badpost'];
//                        $setsql[] = "badpost = {$cacherow['badpost']}";
                    }
                    $archives->save();
//                    $setsql = implode(',', $setsql);
//                    $sql = "UPDATE `$maintable` SET {$setsql} WHERE id='{$row['id']}'";
//                    if (!empty($setsql)) {
//                        $dsql->ExecuteNoneQuery($sql);
//                    }
                }

//                while ($row = $dsql->GetArray('lit')) {
//                    $prefix = 'diggCache';
//                    $key = 'aid-' . $row['id'];
//                    $cacherow = GetCache($prefix, $key);
//                    $setsql = array();
//                    if (!empty($cacherow['scores']) && $cacherow['scores'] != $row['scores']) {
//                        $setsql[] = "scores = {$cacherow['scores']}";
//                    }
//                    if (!empty($cacherow['goodpost']) && $cacherow['goodpost'] != $row['goodpost']) {
//                        $setsql[] = "goodpost = {$cacherow['goodpost']}";
//                    }
//                    if (!empty($cacherow['badpost']) && $cacherow['badpost'] != $row['badpost']) {
//                        $setsql[] = "badpost = {$cacherow['badpost']}";
//                    }
//                    $setsql = implode(',', $setsql);
//                    $sql = "UPDATE `$maintable` SET {$setsql} WHERE id='{$row['id']}'";
//                    if (!empty($setsql)) {
//                        $dsql->ExecuteNoneQuery($sql);
//                    }
//                }
                //echo Common::execTime()-$t1;
            }
        }

//        $dsql->SetQuery($query);
//        $dsql->Execute('al');
        //$row = $dsql->GetArray("al");
        $artlist = '';
        if ($pagesize > 0) $artlist .= "    <div id='{$tagid}'>\r\n";
        if ($col > 1) $artlist = "<table width='$tablewidth' border='0' cellspacing='0' cellpadding='0'>\r\n";


        $dtp2 = new DedeTagParse();
        $dtp2->setNameSpace('field', '[', ']');
        $dtp2->loadString($innertext);
        CfgConfig::sysConfig()->autoindex = 0;
        $ids = array();
        $orderWeight = array();

        for ($i = 0; $i < $line; $i++) {
            if ($col > 1) $artlist .= "<tr>\r\n";
            for ($j = 0; $j < $col; $j++) {
                if ($col > 1) $artlist .= "    <td width='$colWidth'>\r\n";
//                if ($row = $dsql->GetArray("al")) {
                if ($row = $query->cursor()) {
                    $ids[] = $row['id'];
                    //处理一些特殊字段
                    $row['info'] = $row['infos'] = mb_substr($row['description'], 0, $infolen);
                    $row['id'] = $row['id'];

                    if ($row['corank'] > 0 && $row['arcrank'] == 0) {
                        $row['arcrank'] = $row['corank'];
                    }

                    $row['filename'] = $row['arcurl'] = Common::getFileUrl($row['id'], $row['typeid'], $row['senddate'], $row['title'], $row['ismake'],
                        $row['arcrank'], $row['namerule'], $row['typedir'], $row['money'], $row['filename'], $row['moresite'], $row['siteurl'], $row['sitepath']);

                    $row['typeurl'] = Common::getTypeUrl($row['typeid'], $row['typedir'], $row['isdefault'], $row['defaultname'], $row['ispart'],
                        $row['namerule2'], $row['moresite'], $row['siteurl'], $row['sitepath']);

                    if ($row['litpic'] == '-' || $row['litpic'] == '') {
                        $row['litpic'] = CfgConfig::sysConfig()->cfg_cmspath . '/images/defaultpic.gif';
                    }
                    if (!preg_match("#^http:\/\/#i", $row['litpic']) && CfgConfig::sysConfig()->cfg_multi_site == 'Y') {
                        $row['litpic'] = CfgConfig::sysConfig()->cfg_mainsite . $row['litpic'];
                    }
                    $row['picname'] = $row['litpic'];
                    $row['stime'] = Common::getDateMK($row['pubdate']);
                    $row['typelink'] = "<a href='" . $row['typeurl'] . "'>" . $row['typename'] . "</a>";
                    $row['image'] = "<img src='" . $row['picname'] . "' border='0' width='$imgwidth' height='$imgheight' alt='" . preg_replace("#['><]#", "", $row['title']) . "'>";
                    $row['imglink'] = "<a href='" . $row['filename'] . "'>" . $row['image'] . "</a>";
                    $row['fulltitle'] = $row['title'];
                    $row['title'] = mb_substr($row['title'], $titlelen);
                    if ($row['color'] != '') $row['title'] = "<font color='" . $row['color'] . "'>" . $row['title'] . "</font>";
                    if (preg_match('#b#', $row['flag'])) $row['title'] = "<strong>" . $row['title'] . "</strong>";
                    //$row['title'] = "<b>".$row['title']."</b>";
                    $row['textlink'] = "<a href='" . $row['filename'] . "'>" . $row['title'] . "</a>";

                    $row['plusurl'] = $row['phpurl'] = CfgConfig::sysConfig()->cfg_phpurl;
                    $row['memberurl'] = CfgConfig::sysConfig()->cfg_memberurl;
                    $row['templeturl'] = CfgConfig::sysConfig()->cfg_memberurl;

                    if (is_array($dtp2->cTags)) {
                        foreach ($dtp2->cTags as $k => $cTag) {
                            if ($cTag->getName() == 'array') {
                                //传递整个数组，在runphp模式中有特殊作用
                                $dtp2->assign($k, $row);
                            } else {
                                if (isset($row[$cTag->getName()])) $dtp2->assign($k, $row[$cTag->getName()]);
                                else $dtp2->assign($k, '');
                            }
                        }
                        CfgConfig::sysConfig()->autoindex++;
                    }
                    if ($pagesize > 0) {
                        if (CfgConfig::sysConfig()->autoindex <= $pagesize) {
                            $liststr = $dtp2->getResult();
                            $artlist .= $liststr . "\r\n";
                        } else {
                            $artlist .= "";
                            $orderWeight[] = array(
                                'weight' => $row['weight'],
                                'arclist' => ''
                            );
                        }
                    } else {
                        $liststr = $dtp2->getResult();
                        $artlist .= $liststr . "\r\n";
                    }
                    $orderWeight[] = array(
                        'weight' => $row['weight'],
                        'arclist' => $liststr
                    );
                } else {
                    $artlist .= '';
                }

                // 进行判断,如果启用排序则内容输出为重新排序后的内容
                // var_dump($isweight=='y' && count($orderWeight) == $line);
                $isweight = strtolower($isweight);
                if ($isweight == 'y') {
                    $artlist = '';
                    $orderWeight = self::listSortBy($orderWeight, 'weight', 'asc');

                    foreach ($orderWeight as $vv) {
                        $artlist .= $vv['arclist'];
                    }
                }
                if ($col > 1) $artlist .= "    </td>\r\n";
            }//Loop Col
            if ($col > 1) $i += $col - 1;
            if ($col > 1) $artlist .= "    </tr>\r\n";
        }//loop line
        if ($col > 1) $artlist .= "    </table>\r\n";
//        $dsql->FreeResult("al");
        $idsstr = join(',', $ids);

        //分页特殊处理
        if ($pagesize > 0) {
            $artlist .= "    </div>\r\n";
            $row = Arcmulti::where('tagid', $tagid)->first();

//            $row = $dsql->GetOne("SELECT tagid FROM #@__arcmulti WHERE tagid='$tagid'");
            $uptime = time();
            $attstr = addslashes(serialize($attarray));
            $innertext = addslashes($innertext);
            $row->tagid = $tagid;
            $row->uptime = $uptime;
            $row->innertext = $innertext;
            $row->pagesize = $pagesize;
            $row->arcids = $idsstr;
            $row->ordersql = $ordersql;
            $row->addfieldsSql = $addfieldsSql;
            $row->addfieldsSqlJoin = $addfieldsSqlJoin;
            $row->attstr = $attstr;
            $row->save();
        }

        //保存ID缓存
        if ($needSaveCache) {
            if ($idsstr == '') $idsstr = '0';
            if ($cfg_cache_type == 'content' && $idsstr != '0') {
                $idsstr = addslashes($artlist);
            }
            $arcCache = ArcCache::where('md5hash', $taghash)->first();
            $arcCache->cachedata = $idsstr;
            $arcCache->save();
        }
        return $artlist;
    }

    /**
     *  查询缓存
     *
     * @access    public
     * @param     string $md5hash 唯一识别hash
     * @return    string
     */
    public static function getArclistCache($md5hash)
    {
        $cfg_makesign_cache = CfgConfig::sysConfig()->cfg_makesign_cache;
        $cfg_index_cache = CfgConfig::sysConfig()->cfg_index_cache;
        if ($cfg_index_cache <= 0) return '';
        if ($cfg_makesign_cache == 'N') return '';
        $mintime = time() - $cfg_index_cache;
        $arr = ArcCache::where('md5hash', $md5hash)->first(['cachedata', 'uptime'])->toArray();
        if (!is_array($arr)) {
            return '';
        } else if ($arr['uptime'] < $mintime) {
            return '';
        } else {
            return $arr['cachedata'];
        }
    }

    /**
     *  获取自动频道ID
     *
     * @access    public
     * @param     string $sortid
     * @param     string $topid
     * @return    string
     */
    public static function lib_getAutoChannelID($sortid, $topid)
    {
        if (empty($sortid)) $sortid = 1;
        $getstart = $sortid - 1;
        $row = Arctype::where([
            ['reid', '=', $topid],
            ['ispart', '<', 2],
            ['ishidden', '<>', 1],
        ])->orderBy('sortrank')->skip($getstart)->first()->toArray();
        return empty($row['id']) ? 0 : $row['id'];
    }

    /**
     *  对查询结果集进行排序
     *
     * @access    public
     * @param     array $list 查询结果
     * @param     string $field 排序的字段名
     * @param     array $sortby 排序类型
     *            asc正向排序 desc逆向排序 nat自然排序
     * @return    array
     */
    public static function listSortBy($list, $field, $sortby = 'asc')
    {
        if (is_array($list)) {
            $resultSet = array();
//            foreach ($list as $i => $data)
//                $refer[$i] = &$data[$field];
            switch ($sortby) {
                case 'asc': // 正向排序
//                    asort($refer);
                    $resultSet = array_values(array_sort($list, function ($value) use ($field) {
                        return $value[$field];
                    }));
                    break;
                case 'desc':// 逆向排序
//                    arsort($refer);
                    $resultSet = array_values(Collection::make($list)->sortBy(function ($value) use ($field) {
                        return $value[$field];
                    }, SORT_REGULAR, SORT_DESC)->all());
                    break;
                case 'nat': // 自然排序
//                    natcasesort($refer);
                    $resultSet = array_values(Collection::make($list)->sortBy(function ($value) use ($field) {
                        return $value[$field];
                    }, SORT_NATURAL)->all());
                    break;
            }
//            foreach ($refer as $key => $val)
//                $resultSet[] = &$list[$key];
            return $resultSet;
        }
        return false;
    }
}
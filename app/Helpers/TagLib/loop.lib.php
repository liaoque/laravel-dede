<?php
if(!defined('DEDEINC'))
{
    exit("Request Error!");
}
/**
 * 调用任意表的数据标签
 *
 * @version        $Id: loop.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>万能循环</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>调用任意表的数据标签</description>
<demo>
{dede:loop table='dede_archives' sort='' row='4' if=''}
<a href='[field:arcurl/]'>[field:title/]</a>
{/dede:loop}
</demo>
<attributes>
    <iterm>table:查询表名</iterm> 
    <iterm>sort:用于排序的字段</iterm>
    <iterm>row:返回结果的条数</iterm>
    <iterm>if:查询的条件</iterm>
</attributes> 
>>dede>>*/
 
require_once(DEDEINC.'/dedevote.class.php');
function lib_loop(&$cTag,&$refObj)
{
    global $dsql;
    $attlist="table|,tablename|,row|8,sort|,if|,ifcase|,orderway|desc";//(2011.7.22 增加loop标签orderway属性 by:织梦的鱼)
    FillAttsDefault($cTag->CAttribute->Items,$attlist);
    extract($cTag->CAttribute->Items, EXTR_SKIP);

    $innertext = trim($cTag->GetInnertext());
    $revalue = '';
    if(!empty($table)) $tablename = $table;

    if($tablename==''||$innertext=='') return '';
    if($if!='') $ifcase = $if;

    if($sort!='') $sort = " ORDER BY $sort $orderway ";
    if($ifcase!='') $ifcase=" WHERE $ifcase ";
    $dsql->SetQuery("SELECT * FROM $tablename $ifcase $sort LIMIT 0,$row");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->setNameSpace("field","[","]");
    $ctp->LoadSource($innertext);
    CfgConfig::sysConfig()->autoindex = 0;
    while($row = $dsql->GetArray())
    {
        CfgConfig::sysConfig()->autoindex++;
        foreach($ctp->cTags as $tagid=>$cTag)
        {
                if($cTag->getName()=='array')
                {
                        $ctp->assign($tagid, $row);
                }
                else
                {
                    if( !empty($row[$cTag->getName()])) $ctp->assign($tagid,$row[$cTag->getName()]);
                }
        }
        $revalue .= $ctp->getResult();
    }
    return $revalue;
}
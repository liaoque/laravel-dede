<?php
/**
 * 子栏目调用标签
 *
 * @version        $Id: sonchannel.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>子栏目标签</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>子栏目调用标签</description>
<demo>
{dede:sonchannel}
<a href='[field:typeurl/]'>[field:typename/]</a>
{/dede:sonchannel}
</demo>
<attributes>
    <iterm>row:返回数目</iterm> 
    <iterm>col:默认单列显示</iterm>
    <iterm>nosonmsg:没有指定ID子栏目显示的信息内容</iterm>
</attributes> 
>>dede>>*/
 
function lib_sonchannel(&$cTag,&$refObj)
{
    global $_sys_globals,$dsql;

    $attlist = "row|100,nosonmsg|,col|1";
    FillAttsDefault($cTag->CAttribute->Items,$attlist);
    extract($cTag->CAttribute->Items, EXTR_SKIP);
    $innertext = $cTag->GetInnerText();

    $typeid = $_sys_globals['typeid'];
    if(empty($typeid))
    {
        return $cTag->getAtt('nosonmsg');
    }

    $sql = "SELECT id,typename,typedir,isdefault,ispart,defaultname,namerule2,moresite,siteurl,sitepath
        FROM `#@__arctype` WHERE reid='$typeid' AND ishidden<>1 ORDER BY sortrank ASC LIMIT 0,$row";

    //And id<>'$typeid'
    $dtp2 = new DedeTagParse();
    $dtp2->setNameSpace("field","[","]");
    $dtp2->LoadSource($innertext);
    $dsql->SetQuery($sql);
    $dsql->Execute();
    $line = $row;
    $GLOBALS['autoindex'] = 0;
    $likeType = '';
    for($i=0;$i < $line;$i++)
    {
        if($col>1) $likeType .= "<dl>\r\n";
        for($j=0;$j<$col;$j++)
        {
            if($col>1) $likeType .= "<dd>\r\n";
            if($row=$dsql->GetArray())
            {
                $row['typelink'] = $row['typeurl'] = GetOneTypeUrlA($row);
                if(is_array($dtp2->cTags))
                {
                    foreach($dtp2->cTags as $tagid=>$cTag){
                        if(isset($row[$cTag->getName()])) $dtp2->assign($tagid,$row[$cTag->getName()]);
                    }
                }
                $likeType .= $dtp2->getResult();
            }
            if($col>1) $likeType .= "</dd>\r\n";
            $GLOBALS['autoindex']++;
        }//Loop Col
        if($col>1)
        {
            $i += $col - 1;
            $likeType .= "    </dl>\r\n";
        }
    }//Loop for $i
    $dsql->FreeResult();
    return $likeType;
}
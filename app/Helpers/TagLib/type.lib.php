<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * 指定的单个栏目的链接标签
 *
 * @version        $Id: type.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>指定栏目</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>表示指定的单个栏目的链接</description>
<demo>
{dede:type}
<a href="[field:typelink /]">[field:typename /]</a>
{/dede:type}
</demo>
<attributes>
    <iterm>typeid:指定栏目ID</iterm> 
</attributes> 
>>dede>>*/
 
function lib_type(&$cTag,&$refObj)
{
    global $dsql,$envs;

    $attlist='typeid|0';
    FillAttsDefault($cTag->CAttribute->Items,$attlist);
    extract($cTag->CAttribute->Items, EXTR_SKIP);
    $innertext = trim($cTag->GetInnerText());

    if($typeid==0) {
        $typeid = ( isset($refObj->TypeLink->TypeInfos['id']) ? $refObj->TypeLink->TypeInfos['id'] : $envs['typeid'] );
    }

  if(empty($typeid)) return '';

    $row = $dsql->GetOne("SELECT id,typename,typedir,isdefault,ispart,defaultname,namerule2,moresite,siteurl,sitepath 
                          FROM `#@__arctype` WHERE id='$typeid' ");
    if(!is_array($row)) return '';
    if(trim($innertext)=='') $innertext =Common::getSysTemplets("part_type_list.htm");
    
    $dtp = new DedeTagParse();
    $dtp->setNameSpace('field','[',']');
    $dtp->LoadSource($innertext);
    if(!is_array($dtp->cTags))
    {
        unset($dtp);
        return '';
    }
    else
    {
        $row['typelink'] = $row['typeurl'] = GetOneTypeUrlA($row);
        foreach($dtp->cTags as $tagid=>$cTag)
        {
            if(isset($row[$cTag->getName()])) $dtp->assign($tagid,$row[$cTag->getName()]);
        }
        $revalue = $dtp->getResult();
        unset($dtp);
        return $revalue;
    }
}
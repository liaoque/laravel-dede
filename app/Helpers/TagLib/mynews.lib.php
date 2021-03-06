<?php
/**
 * 站内新闻调用标签
 *
 * @version        $Id:mynews.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>站内新闻</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>站内新闻调用标签</description>
<demo>
{dede:mynews row='' titlelen=''/}
</demo>
<attributes>
    <iterm>row:调用站内新闻数</iterm> 
    <iterm>titlelen:新闻标题长度</iterm>
</attributes> 
>>dede>>*/
 
function lib_mynews(&$cTag,&$refObj)
{
    global $dsql,$envs;
    //属性处理
    $attlist="row|1,titlelen|24";
    FillAttsDefault($cTag->CAttribute->Items,$attlist);
    extract($cTag->CAttribute->Items, EXTR_SKIP);

    $innertext = trim($cTag->GetInnerText());
    if(empty($row)) $row=1;
    if(empty($titlelen)) $titlelen=30;
    if(empty($innertext)) $innertext =Common::getSysTemplets('mynews.htm');

    $idsql = '';
    if($envs['typeid'] > 0) $idsql = " WHERE typeid='".Arctype::getTopIdWithCache($this->TypeID)."' ";
    $dsql->SetQuery("SELECT * FROM #@__mynews $idsql ORDER BY senddate DESC LIMIT 0,$row");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->setNameSpace('field','[',']');
    $ctp->LoadSource($innertext);
    $revalue = '';
    while($row = $dsql->GetArray())
    {
        foreach($ctp->cTags as $tagid=>$cTag){
            @$ctp->assign($tagid,$row[$cTag->getName()]);
        }
        $revalue .= $ctp->getResult();
    }
    return $revalue;
}
<?php
if(!defined('DEDEINC'))
{
    exit("Request Error!");
}
/**
 * 会员信息调用标签
 *
 * @version        $Id: memberlist.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>会员信息列表</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>会员信息调用标签</description>
<demo>
{dede:memberlist orderby='scores' row='20'}
<a href="../member/index.php?uid={dede:field.userid /}">{dede:field.userid /}</a>
<span>{dede:field.scores /}</span>
{/dede:memberlist}
</demo>
<attributes>
    <iterm>row:调用数目</iterm> 
    <iterm>iscommend:是否为推荐会员</iterm>
    <iterm>orderby:按登陆时间排序 money 按金钱排序 scores 按积分排序</iterm>
</attributes> 
>>dede>>*/
 
//orderby = logintime(login new) or mid(register new)
function lib_memberlist(&$cTag, &$refObj)
{
    global $dsql,$sqlCt;
    $attlist="row|6,iscommend|0,orderby|logintime,signlen|50";
    FillAttsDefault($cTag->CAttribute->Items,$attlist);
    extract($cTag->CAttribute->Items, EXTR_SKIP);

    $revalue = '';
    $innerText = trim($cTag->GetInnerText());
    if(empty($innerText)) $innerText =Common::getSysTemplets('memberlist.htm');

    $wheresql = ' WHERE mb.spacesta>-1 AND mb.matt<10 ';

    if($iscommend > 0) $wheresql .= " AND  mb.matt='$iscommend' ";

    $sql = "SELECT mb.*,ms.spacename,ms.sign FROM `#@__member` mb
        LEFT JOIN `#@__member_space` ms ON ms.mid = mb.mid
        $wheresql order by mb.{$orderby} DESC LIMIT 0,$row ";
    
    $ctp = new DedeTagParse();
    $ctp->setNameSpace('field','[',']');
    $ctp->LoadSource($innerText);

    $dsql->Execute('mb',$sql);
    while($row = $dsql->GetArray('mb'))
    {
        $row['spaceurl'] = CfgConfig::sysConfig()->cfg_basehost.'/member/index.php?uid='.$row['userid'];
        if(empty($row['face'])){
            $row['face']=($row['sex']=='女')? CfgConfig::sysConfig()->cfg_memberurl.'/templets/images/dfgirl.png' : CfgConfig::sysConfig()->cfg_memberurl.'/templets/images/dfboy.png';
        }
        foreach($ctp->cTags as $tagid=>$cTag){
            if(isset($row[$cTag->getName()])){ $ctp->assign($tagid,$row[$cTag->getName()]); }
        }
        $revalue .= $ctp->getResult();
    }
    
    return $revalue;
}
<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * 圈子调用标签
 *
 * @version        $Id: group.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>圈子标签</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>圈子调用标签</description>
<demo>
{dede:group row='6' orderby='threads' titlelen='30'}
 <li>
  <span><img style="visibility: inherit;" title="[field:groupname/]" src="[field:icon/]" /></span>
  <span><a href="[field:url/]" title="[field:groupname/]" target="_blank">[field:groupname/]</a></span>
 </li>
{/dede:group} 
</demo>
<attributes>
    <iterm>row:调用条数</iterm> 
    <iterm>orderby:排列顺序（默认是主题数）</iterm>
    <iterm>titlelen:圈子名称最大长度</iterm>
</attributes> 
>>dede>>*/
 
function lib_group(&$cTag,&$refObj)
{
    global $dsql, $envs, $cfg_dbprefix, $cfg_cmsurl;
    //属性处理
    $attlist="row|6,orderby|threads,titlelen|30";
    FillAttsDefault($cTag->CAttribute->Items,$attlist);
    extract($cTag->CAttribute->Items, EXTR_SKIP);

    if( !$dsql->IsTable("{$cfg_dbprefix}groups") ) return '没安装圈子模块';
    
    if(!preg("#\/$#", $cfg_cmsurl)) $cfg_group_url = $cfg_cmsurl.'/group';
    else $cfg_group_url = $cfg_cmsurl.'group';
    
    $innertext = $cTag->GetInnerText();
    if(trim($innertext)=='') $innertext =Common::getSysTemplets("groups.htm");
    
    $list = '';
    $dsql->SetQuery("SELECT groupimg,groupid,groupname FROM `#@__groups` WHERE ishidden=0 ORDER BY $orderby DESC LIMIT 0,{$row}");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->setNameSpace('field', '[', ']');
    while($rs = $dsql->GetArray())
    {
        $ctp->LoadSource($innertext);
        $rs['groupname'] = cn_substr($rs['groupname'], $titlelen);
        $rs['url'] = $cfg_group_url."/group.php?id={$rs['groupid']}";
        $rs['icon']  = $rs['groupimg'];
        foreach($ctp->cTags as $tagid=>$cTag)
        {
            if( !empty($rs[strtolower($cTag->getName())]) ) {
                $ctp->assign($tagid,$rs[$cTag->getName()]);
            }
          }
          $list .= $ctp->getResult();
    }
    return $list;
}
<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * 分类信息的地区与小分类搜索
 *
 * @version        $Id: infoguide.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
 /*>>dede>>
<name>分类信息搜索</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>分类信息的地区与小分类搜索</description>
<demo>
{dede:infoguide /}
</demo>
<attributes>
</attributes> 
>>dede>>*/
 
function lib_infoguide(&$cTag,&$refObj)
{
    global $dsql,$nativeplace,$infotype,$hasSetEnumJs,$cfg_cmspath,$cfg_mainsite;
    
    //属性处理
    //$attlist="row|12,titlelen|24";
    //FillAttsDefault($cTag->CAttribute->Items,$attlist);
    //extract($cTag->CAttribute->Items, EXTR_SKIP);
    
    $cmspath = ( (empty($cfg_cmspath) || preg_match('#[/$]#', $cfg_cmspath)) ? $cfg_cmspath.'/' : $cfg_cmspath );
    
    if(empty($refObj->fields['typeid']))
    {
        $row = $dsql->GetOne("SELECT id FROM `#@__arctype` WHERE channeltype='-8' And reid = '0' ");
        $typeid = (is_array($row) ? $row['id'] : 0);
        if(empty($typeid))
        {
            return '请指定一个栏目类型为“分类信息”，否则无法使用这个搜索表单！';
        }
    }
    else
    {
        $typeid = $refObj->fields['typeid'];
    }
    
    $innerText = trim($cTag->GetInnerText());
    if(empty($innerText)) $innerText =Common::getSysTemplets("info_guide.htm");
    $ctp = new DedeTagParse();
    $ctp->setNameSpace('field','[',']');
    $ctp->LoadSource($innerText);

    $revalue = $seli = '';
    
    $fields = array('nativeplace'=>'','infotype'=>'','typeid'=>$typeid);
    
    if($hasSetEnumJs !='has' )
    {
        $revalue .= '<script language="javascript" type="text/javascript" src="'.$cfg_mainsite.$cmspath.'images/enums.js"></script>'."\r\n";
        $GLOBALS['hasSetEnumJs'] = 'hasset';
    }
    
    $fields['nativeplace'] = $fields['infotype'] = '';
    
    if(empty($nativeplace)) $nativeplace = 0;
    if(empty($infotype)) $infotype = 0;
    
    $fields['nativeplace'] .= "<input type='hidden' id='hidden_nativeplace' name='nativeplace' value='{$nativeplace}' />\r\n";
    $fields['nativeplace'] .= "<span class='infosearchtxt'>地区：</span><span id='span_nativeplace'></span>\r\n";
    $fields['nativeplace'] .= "<span id='span_nativeplace_son'></span>\r\n<span id='span_nativeplace_sec'></span><br />\r\n";
    $fields['nativeplace'] .= "<script language='javascript' type='text/javascript' src='{$cfg_mainsite}{$cmspath}data/enums/nativeplace.js'></script>\r\n";
    $fields['nativeplace'] .= '<script language="javascript">MakeTopSelect("nativeplace", '.$nativeplace.');</script>'."\r\n";
    
    $fields['infotype'] .= "<input type='hidden' id='hidden_infotype' name='infotype' value='{$infotype}' />\r\n";
    $fields['infotype'] .= "<span class='infosearchtxt'>类型：</span><span id='span_infotype'></span>\r\n";
    $fields['infotype'] .= "<span id='span_infotype_son'></span><span id='span_infotype_sec'></span><br />\r\n";
    $fields['infotype'] .= "<script language='javascript' type='text/javascript' src='{$cfg_mainsite}{$cmspath}data/enums/infotype.js'></script>\r\n";
    $fields['infotype'] .= '<script language="javascript">MakeTopSelect("infotype", '.$infotype.');</script>'."\r\n";
    
    if(is_array($ctp->cTags))
    {
        foreach($ctp->cTags as $tagid=>$cTag)
        {
            if(isset($fields[$cTag->getName()])) {
                $ctp->assign($tagid,$fields[$cTag->getName()]);
            }
        }
        $revalue .= $ctp->getResult();
    }
    
    return $revalue;
}
<?php if (!defined('DEDEINC')) exit('Request Error!');
/**
 * 单页文档调用标签
 *
 * @version        $Id: likesgpage.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

/*>>dede>>
<name>单页文档调用标签</name>
<type>全局标记</type>
<for>V55,V56,V57</for>
<description>单页文档调用标签</description>
<demo>
{dede:likespage row=''/}
</demo>
<attributes>
    <iterm>row:调用条数</iterm> 
</attributes> 
>>dede>>*/

function lib_likesgpage(&$cTag, &$refObj)
{
    global $dsql;

    //把属性转为变量，如果不想进行此步骤，也可以直接从 $cTag->CAttribute->Items 获得，这样也可以支持中文名
    $attlist = "row|8";
    FillAttsDefault($cTag->CAttribute->Items, $attlist);
    extract($cTag->CAttribute->Items, EXTR_SKIP);
    $innertext = trim($cTag->GetInnerText());

    $aid = (isset($refObj->fields['aid']) ? $refObj->fields['aid'] : 0);

    $revalue = '';
    if ($innertext == '') $innertext = Common::getSysTemplets("part_likesgpage.htm");

    $likeid = (empty($refObj->fields['likeid']) ? 'all' : $refObj->fields['likeid']);

    $dsql->SetQuery("SELECT aid,title,filename FROM `#@__sgpage` WHERE likeid LIKE '$likeid' LIMIT 0,$row");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->setNameSpace('field', '[', ']');
    $ctp->LoadSource($innertext);
    while ($row = $dsql->GetArray()) {
        if ($aid != $row['aid']) {
            $row['url'] = $GLOBALS['cfg_cmsurl'] . '/' . $row['filename'];
            foreach ($ctp->cTags as $tagid => $cTag) {
                if (!empty($row[$cTag->getName()])) $ctp->assign($tagid, $row[$cTag->getName()]);
            }
            $revalue .= $ctp->getResult();
        } else {
            $revalue .= '<dd class="cur"><span>' . $row['title'] . '</span></dd>';
        }
    }
    return $revalue;
}
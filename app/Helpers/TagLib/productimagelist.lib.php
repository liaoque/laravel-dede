<?php
!defined('DEDEINC') && exit("403 Forbidden!");
/**
 * 
 *
 * @version        $Id: productimagelist.lib.php 1 9:29 2010年7月6日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
function lib_productimagelist(&$cTag, &$refObj)
{
    global $dsql,$sqlCt;
    $attlist="desclen|80";
    FillAttsDefault($cTag->CAttribute->Items,$attlist);
    extract($cTag->CAttribute->Items, EXTR_SKIP);

    if(!isset($refObj->addTableRow['imgurls'])) return ;
    
    $revalue = '';
    $innerText = trim($cTag->GetInnerText());
    if(empty($innerText)) $innerText =Common::getSysTemplets('productimagelist.htm');
    
    $dtp = new DedeTagParse();
    $dtp->LoadSource($refObj->addTableRow['imgurls']);
    
    $images = array();
    if(is_array($dtp->cTags))
    {
        foreach($dtp->cTags as $cTag)
        {
            if($cTag->getName()=="img")
            {
                $row = array();
                $row['imgsrc'] = trim($cTag->GetInnerText());
                $row['text'] = $cTag->getAtt('text');
                $images[] = $row;
            }
        }
    }
    $dtp->Clear();

    $revalue = '';
    $ctp = new DedeTagParse();
    $ctp->setNameSpace('field','[',']');
    $ctp->LoadSource($innerText);

    foreach($images as $row)
    {
        foreach($ctp->cTags as $tagid=>$cTag)
        {
            if(isset($row[$cTag->getName()])){ $ctp->assign($tagid,$row[$cTag->getName()]); }
        }
        $revalue .= $ctp->getResult();
    }
    return $revalue;
}
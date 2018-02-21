<?php
if(!defined('DEDEINC'))
{
    exit("Request Error!");
}
/**
 * 图像标签
 *
 * @version        $Id:img.lib.php 1 9:33 2010年7月8日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

function ch_img($fvalue,&$arcTag,&$refObj,$fname='')
{
    global $cfg_album_width,$cfg_album_row,$cfg_album_col,$cfg_album_pagesize,$cfg_album_style,$cfg_album_ddwidth,$cfg_basehost,$cfg_multi_site;
    $dtp = new DedeTagParse();
    $dtp->loadSource($fvalue);
    if(!is_array($dtp->cTags))
    {
        $dtp->clear();
        return "无图片信息！";
    }
    $pagestyle = $cfg_album_style;
    $maxwidth = $cfg_album_width;
    $ddmaxwidth = $cfg_album_ddwidth;
    $pagepicnum = $cfg_album_pagesize;
    $row = $cfg_album_row;
    $icol = $cfg_album_col;
    $ptag = $dtp->GetTag('pagestyle');
    if(is_object($ptag))
    {
        $pagestyle = $ptag->getAtt('value');
        $maxwidth = $ptag->getAtt('maxwidth');
        $ddmaxwidth = $ptag->getAtt('ddmaxwidth');
        $pagepicnum = $ptag->getAtt('pagepicnum');
        $irow = $ptag->getAtt('row');
        $icol = $ptag->getAtt('col');
        if(empty($maxwidth))
        {
            $maxwidth = $cfg_album_width;
        }
    }

    //遍历图片信息
    $mrow = 0;
    $mcol = 0;
    $images = array();
    $innerTmp = $arcTag->getInnerText();
    if(trim($innerTmp)=='')
    {
        $innerTmp = GetSysTemplets("channel_article_image.htm");
    }

    if($pagestyle==1)
    {
        $pagesize = $pagepicnum;
    }
    else if($pagestyle==2)
    {
        $pagesize = 1;
    }
    else
    {
        $pagesize = $irow * $icol;
    }

    if(is_object($arcTag) && $arcTag->getAtt('pagesize') > 0)
    {
        $pagesize = $arcTag->getAtt('pagesize');
    }
    if(empty($pagesize))
    {
        $pagesize = 12;
    }
    $aid = $refObj->ArcID;
    $row = $refObj->dsql->GetOne("SELECT title FROM `#@__archives` WHERE `id` = '$aid';");
    $title = $row['title'];
    $revalue = '';
    $GLOBAL['photoid'] = 0;
    foreach($dtp->cTags as $ctag)
    {
        if($ctag->getName()=="img")
        {
            $fields = $ctag->cAttribute->items;
            $fields['text'] = str_replace("'","",$ctag->getAtt('text'));
            $fields['title'] = $title;
            $fields['imgsrc'] = trim($ctag->getInnerText());
            $fields['imgsrctrue'] = $fields['imgsrc'];
            if(empty($fields['ddimg']))
            {
                $fields['ddimg'] = $fields['imgsrc'];
            }
            if($cfg_multi_site=='Y')
            {
                //$cfg_basehost)
                if( !preg_match('#^http:#i', $fields['imgsrc']) ) {
                    $fields['imgsrc'] = $cfg_basehost.$fields['imgsrc'];
                }
                if( !preg_match('#^http:#i', $fields['ddimg']) ) {
                    $fields['ddimg'] = $cfg_basehost.$fields['ddimg'];
                }
            }
            if(empty($fields['width']))
            {
                $fields['width'] = $maxwidth;
            }
            //if($fields['text']=='')
            //{
                //$fields['text'] = '图片'.($GLOBAL['photoid']+1);
            //}
            $fields['alttext'] = str_replace("'",'',$fields['text']);
            $fields['pagestyle'] = $pagestyle;
            $dtp2 = new DedeTagParse();
            $dtp2->setNameSpace("field","[","]");
            $dtp2->loadSource($innerTmp);
            if($GLOBAL['photoid']>0 && ($GLOBAL['photoid'] % $pagesize)==0)
            {
                $revalue .= "#p#分页标题#e#";
            }
            if($pagestyle==1)
            {
                $fields['imgwidth'] = '';
                $fields['linkurl'] = $fields['imgsrc'];
                $fields['textlink'] = "<br /><a href='{$fields['linkurl']}' target='_blank'>{$fields['text']}</a>";
            }
            else if($pagestyle==2)
            {
                if($fields['width'] > $maxwidth) {
                    $fields['imgwidth'] = " width='$maxwidth' ";
                }
                else {
                    $fields['imgwidth'] = " width='{$fields['width']}' ";
                }
                $fields['linkurl'] = $fields['imgsrc'];
                if($fields['text']!='') {
                    $fields['textlink'] = "<br /><a href='{$fields['linkurl']}' target='_blank'>{$fields['text']}</a>\r\n";
                }
                else {
                    $fields['textlink'] = '';
                }
            }
            else if($pagestyle==3)
            {
                $fields['text'] = $fields['textlink'] = '';
                $fields['imgsrc'] = $fields['ddimg'];
                $fields['imgwidth'] = " width='$ddmaxwidth' ";
                $fields['linkurl'] = "{$GLOBALS['cfg_phpurl']}/showphoto.php?aid={$refObj->ArcID}&src=".urlencode($fields['imgsrctrue'])."&npos={$GLOBAL['photoid']}";
            }
            if(is_array($dtp2->cTags))
            {
                foreach($dtp2->cTags as $tagid=>$ctag)
                {
                    if(isset($fields[$ctag->getName()]))
                    {
                        $dtp2->assign($tagid,$fields[$ctag->getName()]);
                    }
                }
                $revalue .= $dtp2->getResult();
            }
            $GLOBAL['photoid']++;
        }
    }
    return $revalue;
}
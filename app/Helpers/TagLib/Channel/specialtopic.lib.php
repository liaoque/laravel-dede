<?php  if(!defined('DEDEINC')) exit('Request Error!');
/**
 * 专题主题调用标签
 *
 * @version        $Id: arclist.lib.php 2 8:29 2010年7月8日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
function ch_specialtopic($noteinfo, $arcTag, $refObj, $fname='')
{
    require_once(DEDEINC.'/taglib/arclist.lib.php');
    if($noteinfo=='') return '';
    $noteid = $arcTag->getAtt('noteid');
    $rvalue = '';
    $tempStr = GetSysTemplets('channel_spec_note.htm');
    $dtp = new DedeTagParse();
    $dtp->loadSource($noteinfo);
    if(is_array($dtp->cTags))
    {
        foreach($dtp->cTags as $k=>$ctag)
        {
            $notename = $ctag->getAtt('name');
            //指定名称的专题节点
            if($noteid != '' && $ctag->getAtt('noteid') != $noteid)
            {
                continue;
            }
            $isauto = $ctag->getAtt('isauto');
            $idlist = trim($ctag->getAtt('idlist'));
            $rownum = trim($ctag->getAtt('rownum'));
            $keywords = '';
            $stypeid = 0;
            if(empty($rownum)) $rownum = 40;

            //通过关键字和栏目ID自动获取模式
            if($isauto==1)
            {
                $idlist = '';
                $keywords = trim($ctag->getAtt('keywords'));
                $stypeid = $ctag->getAtt('typeid');
            }

            $listTemplet = trim($ctag->getInnerText())!='' ? $ctag->getInnerText() : GetSysTemplets('spec_arclist.htm');
            
            $idvalue = lib_arclistDone
                      (
                        $refObj, $ctag, $stypeid, $rownum, $ctag->getAtt('col'), $ctag->getAtt('titlelen'),$ctag->getAtt('infolen'),
                        $ctag->getAtt('imgwidth'), $ctag->getAtt('imgheight'), 'all', 'default', $keywords, $listTemplet, 0, $idlist,
                        $ctag->getAtt('channel'), '', $ctag->getAtt('att')
                      );
            $notestr = str_replace('~notename~', $notename, $tempStr);
            $notestr = str_replace('~spec_arclist~', $idvalue, $notestr);
            $rvalue .= $notestr;
            if($noteid != '' && $ctag->getAtt('noteid')==$noteid) break;
        }
    }
    $dtp->clear();
    return $rvalue;
}
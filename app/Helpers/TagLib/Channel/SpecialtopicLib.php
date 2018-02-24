<?php
namespace App\Helpers\TagLib\Channel;

use App\Helpers\Common;
use App\Helpers\DedeTagParse;
use App\Helpers\TagLib\ArclistLib;

/**
 * 专题主题调用标签
 *
 * @version        $Id: arclist.lib.php 2 8:29 2010年7月8日Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
class SpecialtopicLib
{
    public function ch_specialtopic($noteinfo, $arcTag, $refObj, $fname = '')
    {

        if ($noteinfo == '') return '';
        $noteid = $arcTag->getAtt('noteid');
        $rvalue = '';
        $tempStr = Common::getSysTemplets('channel_spec_note.htm');
        $dtp = new DedeTagParse();
        $dtp->loadSource($noteinfo);
        if (is_array($dtp->cTags)) {
            foreach ($dtp->cTags as $k => $cTag) {
                $notename = $cTag->getAtt('name');
                //指定名称的专题节点
                if ($noteid != '' && $cTag->getAtt('noteid') != $noteid) {
                    continue;
                }
                $isauto = $cTag->getAtt('isauto');
                $idlist = trim($cTag->getAtt('idlist'));
                $rownum = trim($cTag->getAtt('rownum'));
                $keywords = '';
                $stypeid = 0;
                if (empty($rownum)) $rownum = 40;

                //通过关键字和栏目ID自动获取模式
                if ($isauto == 1) {
                    $idlist = '';
                    $keywords = trim($cTag->getAtt('keywords'));
                    $stypeid = $cTag->getAtt('typeid');
                }

                $listTemplet = trim($cTag->getInnerText()) != '' ? $cTag->getInnerText() :Common::getSysTemplets('spec_arclist.htm');

                $idvalue = ArclistLib::lib_arclistDone
                (
                    $refObj, $cTag, $stypeid, $rownum, $cTag->getAtt('col'), $cTag->getAtt('titlelen'), $cTag->getAtt('infolen'),
                    $cTag->getAtt('imgwidth'), $cTag->getAtt('imgheight'), 'all', 'default', $keywords, $listTemplet, 0, $idlist,
                    $cTag->getAtt('channel'), '', $cTag->getAtt('att')
                );
                $notestr = str_replace('~notename~', $notename, $tempStr);
                $notestr = str_replace('~spec_arclist~', $idvalue, $notestr);
                $rvalue .= $notestr;
                if ($noteid != '' && $cTag->getAtt('noteid') == $noteid) break;
            }
        }
        $dtp->clear();
        return $rvalue;
    }
}

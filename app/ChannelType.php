<?php

namespace App;

use App\Helpers\Common;
use App\Helpers\DedeTagParse;
use Illuminate\Database\Eloquent\Model;

class ChannelType extends Model
{
    //
    protected $table = 'channeltype';

    private $allFieldNames = '';
    private $channelFields = [];
    private $splitPageField = '';
    private $archives = null;

    public static function getShowAll()
    {
        return ChannelType::where('id', '<>', -1)->where('isshow', 1)->orderBy('id')->get();
    }

//    public function getTemplet(){
//        if($this->addtable){
//            $tableName = ucfirst($this->addtable);
//            $obj = new $tableName;
//        }
//
//    }

    public function getChannelFields()
    {
        if (empty($this->channelFields)) {
            $dtp = new DedeTagParse();
            $dtp->setNameSpace('field', '<', '>');
            $dtp->loadSource($this->fieldset);
            if (is_array($dtp->cTags)) {
                $tnames = Array();
                foreach ($dtp->cTags as $cTag) {
                    $tname = $cTag->getName();
                    if (isset($tnames[$tname])) {
                        break;
                    }
                    $tnames[$tname] = 1;
                    if ($this->allFieldNames != '') {
                        $this->allFieldNames .= ',' . $tname;
                    } else {
                        $this->allFieldNames .= $tname;
                    }
                    if (is_array($cTag->cAttribute->items)) {
                        $this->channelFields[$tname] = $cTag->cAttribute->items;
                    }
                    $this->channelFields[$tname]['value'] = '';
                    $this->channelFields[$tname]['innertext'] = $cTag->getInnerText();
                    if (empty($this->channelFields[$tname]['itemname'])) {
                        $this->channelFields[$tname]['itemname'] = $tname;
                    }
                    if ($cTag->getAtt('page') == 'split') {
                        $this->splitPageField = $tname;
                    }
                }
            }
            $dtp->clear();
        }
        return $this->channelFields;
    }


    /**
     * @return Archives
     */
    public function getArchives()
    {
        return $this->archives;
    }

    public function setArchives(Archives $arc)
    {
        $this->archives = $arc;
        return $this;
    }

    /**
     *  处理某个字段的值
     *
     * @access    public
     * @param     string $fname 字段名称
     * @param     string $fvalue 字段值
     * @param     string|DedeTag $addvalue 增加值
     * @return    string
     */
    function makeField($fname, $fvalue, $addvalue = '')
    {
        //处理各种数据类型
        $channelFields = $this->getChannelFields();
        $ftype = $channelFields[$fname]['type'];
        if ($fvalue == '') {
            if ($ftype != 'checkbox') $fvalue = $channelFields[$fname]['default'];
        }

        if ($ftype == 'text') {
            $fvalue = Common::HtmlReplace($fvalue);
        } else if ($ftype == 'textdata') {
            $cfgBasedir = CfgConfig::sysConfig()->cfg_basedir;
            if (!is_file($cfgBasedir . $fvalue)) {
                return '';
            }
            $fp = fopen($cfgBasedir . $fvalue, 'r');
            $fvalue = '';
            while (!feof($fp)) {
                $fvalue .= fgets($fp, 1024);
            }
            fclose($fp);
        } else if ($ftype == 'addon') {
            $foldvalue = $fvalue;
            $tmptext = Common::getSysTemplets("channel_addon.htm");
            $fvalue = str_replace('~link~', $foldvalue, $tmptext);
            $fvalue = str_replace('~phpurl~', CfgConfig::sysConfig()->cfg_phpurl, $fvalue);
        } else if (class_exists(ucfirst($ftype) . 'Lib')) {
            $className = ucfirst($ftype) . 'Lib';
            $fvalue = call_user_func_array([
                new $className,
                'ch_' . $ftype
            ], [
                $fvalue, $addvalue, $this, $fname
            ]);
//            include_once(DEDEINC . '/taglib/channel/' . $ftype . '.lib.php');
//            $func = 'ch_' . $ftype;
//            $fvalue = $func($fvalue, $addvalue, $this, $fname);
        }
        return $fvalue;
    }


}

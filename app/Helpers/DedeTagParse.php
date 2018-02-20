<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/13
 * Time: 18:02
 */

namespace App\Helpers;

/**
 * DedeTagParse Dede模板类
 * function c____DedeTagParse();
 *
 * @package          DedeTagParse
 * @subpackage       DedeCMS.Libraries
 * @link             http://www.dedecms.com
 */
class DedeTagParse
{
    public $nameSpace = 'dede';   //标记的名字空间
    public $tagStartWord = '{';   //标记起始
    public $tagEndWord = '}';     //标记结束
    public $tagMaxLen = 64;       //标记名称的最大值
    public $charToLow = TRUE;     // TRUE表示对属性和标记名称不区分大小写
    public $isCache = FALSE;      //是否使用缓冲
    public $tempMkTime = 0;
    public $cacheFile = '';
    public $sourceString = '';    //模板字符串
    public $cTags = '';           //标记集合
    public $count = -1;           //$Tags标记个数
    public $refObj = '';          //引用当前模板类的对象
    public $taghashfile = '';

    function __construct()
    {
        if (!isset($GLOBALS['cfg_tplcache'])) {
            $GLOBALS['cfg_tplcache'] = 'N';
        }
        if ($GLOBALS['cfg_tplcache'] == 'Y') {
            $this->IsCache = TRUE;
        } else {
            $this->IsCache = FALSE;
        }
        if (DEDE_ENVIRONMENT == 'development') {
            $this->IsCache = FALSE;
        }
        $this->nameSpace = 'dede';
        $this->tagStartWord = '{';
        $this->tagEndWord = '}';
        $this->tagMaxLen = 64;
        $this->charToLow = TRUE;
        $this->sourceString = '';
        $this->cTags = array();
        $this->count = -1;
        $this->tempMkTime = 0;
        $this->cacheFile = '';
    }

    function DedeTagParse()
    {
        $this->__construct();
    }

    /**
     *  设置标记的命名空间，默认为dede
     *
     * @access    public
     * @param     string $str 字符串
     * @param     string $s 开始标记
     * @param     string $e 结束标记
     * @return    void
     */
    function setNameSpace($str, $s = "{", $e = "}")
    {
        $this->nameSpace = strtolower($str);
        $this->tagStartWord = $s;
        $this->tagEndWord = $e;
    }

    /**
     *  重置成员变量或Clear
     *
     * @access    public
     * @return    void
     */
    function setDefault()
    {
        $this->sourceString = '';
        $this->cTags = array();
        $this->count = -1;
    }

    /**
     *  强制引用
     *
     * @access    public
     * @param     object $refObj 隶属对象
     * @return    void
     */
    function setRefObj(&$refObj)
    {
        $this->refObj = $refObj;
    }

    function getCount()
    {
        return $this->count + 1;
    }

    function clear()
    {
        $this->setDefault();
    }

    // ------------------------------------------------------------------------

    /**
     * CheckDisabledFunctions
     *
     * COMMENT : CheckDisabledFunctions : 检查是否存在禁止的函数
     *
     * @access    public
     * @param    string
     * @return    bool
     */
    function checkDisabledFunctions($str, &$errmsg = '')
    {
        global $cfg_disable_funs;
        $cfg_disable_funs = isset($cfg_disable_funs) ? $cfg_disable_funs : 'phpinfo,eval,exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source,file_put_contents,fsockopen,fopen,fwrite';
        // 模板引擎增加disable_functions
        if (defined('DEDEDISFUN')) {
            $tokens = token_get_all_nl('<?php' . $str . "\n\r?>");
            $disabled_functions = explode(',', $cfg_disable_funs);
            foreach ($tokens as $token) {
                if (is_array($token)) {
                    if ($token[0] = '306' && in_array($token[1], $disabled_functions)) {
                        $errmsg = 'Error:function disabled "' . $token[1] . '" ';
                        return FALSE;
                    }
                }
            }
        }
        return TRUE;
    }

    /**
     *  检测模板缓存
     *
     * @access    public
     * @param     string $filename 文件名称
     * @return    string
     */
    function loadCache($filename)
    {
        global $cfg_tplcache, $cfg_tplcache_dir;
        if (!$this->IsCache) {
            return FALSE;
        }
        $cdir = dirname($filename);
        $cachedir = DEDEROOT . $cfg_tplcache_dir;
        $ckfile = str_replace($cdir, '', $filename) . substr(md5($filename), 0, 16) . '.inc';
        $ckfullfile = $cachedir . '/' . $ckfile;
        $ckfullfile_t = $cachedir . '/' . $ckfile . '.txt';
        $this->cacheFile = $ckfullfile;
        $this->tempMkTime = filemtime($filename);
        if (!file_exists($ckfullfile) || !file_exists($ckfullfile_t)) {
            return FALSE;
        }

        //检测模板最后更新时间
        $fp = fopen($ckfullfile_t, 'r');
        $time_info = trim(fgets($fp, 64));
        fclose($fp);
        if ($time_info != $this->tempMkTime) {
            return FALSE;
        }

        //引入缓冲数组
        include($this->cacheFile);
        $errmsg = '';

        //把缓冲数组内容读入类
        if (isset($z) && is_array($z)) {
            foreach ($z as $k => $v) {
                $this->count++;
                $ctag = new DedeTag();
                $ctag->cAttribute = new DedeAttribute();
                $ctag->isReplace = FALSE;
                $ctag->tagName = $v[0];
                $ctag->InnerText = $v[1];
                $ctag->startPos = $v[2];
                $ctag->endPos = $v[3];
                $ctag->tagValue = '';
                $ctag->tagID = $k;
                if (isset($v[4]) && is_array($v[4])) {
                    $i = 0;
                    foreach ($v[4] as $k => $v) {
                        $ctag->cAttribute->count++;
                        $ctag->cAttribute->items[$k] = $v;
                    }
                }
                $this->cTags[$this->count] = $ctag;
            }
        } else {
            //模板没有缓冲数组
            $this->cTags = '';
            $this->count = -1;
        }
        return TRUE;
    }

    /**
     *  写入缓存
     *
     * @access    public
     * @param     string
     * @return    string
     */
    function saveCache()
    {
        $fp = fopen($this->cacheFile . '.txt', "w");
        fwrite($fp, $this->tempMkTime . "\n");
        fclose($fp);
        $fp = fopen($this->cacheFile, "w");
        flock($fp, 3);
        fwrite($fp, '<' . '?php' . "\r\n");
        $errmsg = '';
        if (is_array($this->cTags)) {
            foreach ($this->cTags as $tid => $ctag) {
                $arrayValue = 'Array("' . $ctag->tagName . '",';
                if (!$this->CheckDisabledFunctions($ctag->InnerText, $errmsg)) {
                    fclose($fp);
                    @unlink($this->taghashfile);
                    @unlink($this->cacheFile);
                    @unlink($this->cacheFile . '.txt');
                    die($errmsg);
                }
                $arrayValue .= '"' . str_replace('$', '\$', str_replace("\r", "\\r", str_replace("\n", "\\n", str_replace('"', '\"', str_replace("\\", "\\\\", $ctag->InnerText))))) . '"';
                $arrayValue .= ",{$ctag->startPos},{$ctag->endPos});";
                fwrite($fp, "\$z[$tid]={$arrayValue}\n");
                if (is_array($ctag->cAttribute->items)) {
                    foreach ($ctag->cAttribute->items as $k => $v) {
                        $v = str_replace("\\", "\\\\", $v);
                        $v = str_replace('"', "\\" . '"', $v);
                        $v = str_replace('$', '\$', $v);
                        $k = trim(str_replace("'", "", $k));
                        if ($k == "") {
                            continue;
                        }
                        if ($k != 'tagname') {
                            fwrite($fp, "\$z[$tid][4]['$k']=\"$v\";\n");
                        }
                    }
                }
            }
        }
        fwrite($fp, "\n" . '?' . '>');
        fclose($fp);
    }

    /**
     *  载入模板文件
     *
     * @access    public
     * @param     string $filename 文件名称
     * @return    string
     */
    function loadTemplate($filename)
    {
        $this->setDefault();
        if (!file_exists($filename)) {
            $this->sourceString = " $filename Not Found! ";
            $this->parseTemplet();
        } else {
            $fp = @fopen($filename, "r");
            while ($line = fgets($fp, 1024)) {
                $this->sourceString .= $line;
            }
            fclose($fp);
            if ($this->loadCache($filename)) {
                return '';
            } else {
                $this->parseTemplet();
            }
        }
    }

    // 仅用于兼容旧版本
    function loadTemplet($filename)
    {
        $this->loadTemplate($filename);
    }

    // 仅用于兼容旧版本
    function loadFile($filename)
    {
        $this->loadTemplate($filename);
    }

    /**
     *  载入模板字符串
     *
     * @access    public
     * @param     string $str 字符串
     * @return    void
     */
    function loadSource($str)
    {
        /*
        $this->setDefault();
        $this->sourceString = $str;
        $this->IsCache = FALSE;
        $this->parseTemplet();
        */
        //优化模板字符串存取读取方式
        $this->taghashfile = $filename = DEDEDATA . '/tplcache/' . md5($str) . '.inc';
        if (!is_file($filename)) {
            file_put_contents($filename, $str);
        }
        $this->loadTemplate($filename);
    }

    function loadString($str)
    {
        $this->loadSource($str);
    }

    /**
     *  获得指定名称的Tag的ID(如果有多个同名的Tag,则取没有被取代为内容的第一个Tag)
     *
     * @access    public
     * @param     string $str 字符串
     * @return    int
     */
    function getTagID($str)
    {
        if ($this->count == -1) {
            return -1;
        }
        if ($this->charToLow) {
            $str = strtolower($str);
        }
        foreach ($this->cTags as $id => $CTag) {
            if ($CTag->tagName == $str && !$CTag->isReplace) {
                return $id;
                break;
            }
        }
        return -1;
    }

    /**
     *  获得指定名称的CTag数据类(如果有多个同名的Tag,则取没有被分配内容的第一个Tag)
     *
     * @access    public
     * @param     string $str 字符串
     * @return    string|DedeTag
     */
    public function getTag($str)
    {
        if ($this->count == -1) {
            return '';
        }
        if ($this->charToLow) {
            $str = strtolower($str);
        }
        foreach ($this->cTags as $id => $cTag) {
            if ($cTag->tagName == $str && !$cTag->isReplace) {
                return $cTag;
                break;
            }
        }
        return '';
    }

    /**
     *  通过名称获取标记
     *
     * @access    public
     * @param     string $str 字符串
     * @return    string
     */
    function getTagByName($str)
    {
        return $this->getTag($str);
    }

    /**
     *  获得指定ID的CTag数据类
     *
     * @access    public
     * @param     string  标签id
     * @return    string
     */
    function getTagByID($id)
    {
        if (isset($this->cTags[$id])) {
            return $this->cTags[$id];
        } else {
            return '';
        }
    }

    /**
     *  给_publics数组传递一个元素
     *
     * @access    public
     * @param     string $vname 标签名
     * @param     string $vvalue 标签值
     * @return    string
     */
    function assignpublic($vname, $vvalue)
    {
        if (!isset($_sys_globals['define'])) {
            $_sys_globals['define'] = 'yes';
        }
        $_sys_globals[$vname] = $vvalue;
    }

    /**
     *  分配指定ID的标记的值
     *
     * @access    public
     * @param     string $i 标签id
     * @param     string $str 字符串
     * @param     string $runfunc 运行函数
     * @return    void
     */
    function assign($i, $str, $runfunc = TRUE)
    {
        if (isset($this->cTags[$i])) {
            $this->cTags[$i]->isReplace = TRUE;
            $this->cTags[$i]->tagValue = $str;

            if ($this->cTags[$i]->getAtt('function') != '' && $runfunc) {
                $this->cTags[$i]->tagValue = $this->EvalFunc($str, $this->cTags[$i]->getAtt('function'), $this->cTags[$i]);
            }
        }
    }

    /**
     *  分配指定名称的标记的值，如果标记包含属性，请不要用此函数
     *
     * @access    public
     * @param     string $tagname 标签名称
     * @param     string $str 字符串
     * @return    void
     */
    function assignName($tagname, $str)
    {
        foreach ($this->cTags as $id => $CTag) {
            if ($CTag->tagName == $tagname) {
                $this->assign($id, $str);
            }
        }
    }

    /**
     *  处理特殊标记
     *
     * @access    public
     * @return    void
     */
    function assignSysTag()
    {
        global $_sys_globals;
        for ($i = 0; $i <= $this->count; $i++) {
            $CTag = $this->cTags[$i];
            $str = '';

            //获取一个外部变量
            if ($CTag->tagName == 'global') {
                $str = $this->GetGlobals($CTag->getAtt('name'));
                if ($this->cTags[$i]->getAtt('function') != '') {
                    //$str = $this->EvalFunc( $this->cTags[$i]->tagValue, $this->cTags[$i]->getAtt('function'),$this->cTags[$i] );
                    $str = $this->EvalFunc($str, $this->cTags[$i]->getAtt('function'), $this->cTags[$i]);
                }
                $this->cTags[$i]->isReplace = TRUE;
                $this->cTags[$i]->tagValue = $str;
            } //引入静态文件
            else if ($CTag->tagName == 'include') {
                $filename = ($CTag->getAtt('file') == '' ? $CTag->getAtt('filename') : $CTag->getAtt('file'));
                $str = $this->IncludeFile($filename, $CTag->getAtt('ismake'));
                $this->cTags[$i]->isReplace = TRUE;
                $this->cTags[$i]->tagValue = $str;
            } //循环一个普通数组
            else if ($CTag->tagName == 'foreach') {
                $arr = $this->cTags[$i]->getAtt('array');
                if (isset($GLOBALS[$arr])) {
                    foreach ($GLOBALS[$arr] as $k => $v) {
                        $istr = '';
                        $istr .= preg_replace("/\[field:key([\r\n\t\f ]+)\/\]/is", $k, $this->cTags[$i]->InnerText);
                        $str .= preg_replace("/\[field:value([\r\n\t\f ]+)\/\]/is", $v, $istr);
                    }
                }
                $this->cTags[$i]->isReplace = TRUE;
                $this->cTags[$i]->tagValue = $str;
            } //设置/获取变量值
            else if ($CTag->tagName == 'public') {
                $vname = $this->cTags[$i]->getAtt('name');
                if ($vname == '') {
                    $str = '';
                } else if ($this->cTags[$i]->getAtt('value') != '') {
                    $_publics[$vname] = $this->cTags[$i]->getAtt('value');
                } else {
                    $str = (isset($_publics[$vname]) ? $_publics[$vname] : '');
                }
                $this->cTags[$i]->isReplace = TRUE;
                $this->cTags[$i]->tagValue = $str;
            }

            //运行PHP接口
            if ($CTag->getAtt('runphp') == 'yes') {
                $this->RunPHP($CTag, $i);
            }
            if (is_array($this->cTags[$i]->tagValue)) {
                $this->cTags[$i]->tagValue = 'array';
            }
        }
    }

    //运行PHP代码
    function runPHP(&$refObj, $i)
    {
        $DedeMeValue = $phpcode = '';
        if ($refObj->getAtt('source') == 'value') {
            $phpcode = $this->cTags[$i]->tagValue;
        } else {
            $DedeMeValue = $this->cTags[$i]->tagValue;
            $phpcode = $refObj->getInnerText();
        }
        $phpcode = preg_replace("/'@me'|\"@me\"|@me/i", '$DedeMeValue', $phpcode);
        @eval($phpcode); //or die("<xmp>$phpcode</xmp>");

        $this->cTags[$i]->tagValue = $DedeMeValue;
        $this->cTags[$i]->isReplace = TRUE;
    }

    /**
     *  把分析模板输出到一个字符串中
     *  不替换没被处理的值
     *
     * @access    public
     * @return    string
     */
    function getResultNP()
    {
        $ResultString = '';
        if ($this->count == -1) {
            return $this->sourceString;
        }
        $this->assignSysTag();
        $nextTagEnd = 0;
        $strok = "";
        for ($i = 0; $i <= $this->count; $i++) {
            if ($this->cTags[$i]->GetValue() != "") {
                if ($this->cTags[$i]->GetValue() == '#@Delete@#') {
                    $this->cTags[$i]->tagValue = "";
                }
                $ResultString .= substr($this->sourceString, $nextTagEnd, $this->cTags[$i]->startPos - $nextTagEnd);
                $ResultString .= $this->cTags[$i]->GetValue();
                $nextTagEnd = $this->cTags[$i]->endPos;
            }
        }
        $slen = strlen($this->sourceString);
        if ($slen > $nextTagEnd) {
            $ResultString .= substr($this->sourceString, $nextTagEnd, $slen - $nextTagEnd);
        }
        return $ResultString;
    }

    /**
     *  把分析模板输出到一个字符串中,并返回
     *
     * @access    public
     * @return    string
     */
    function getResult()
    {
        $ResultString = '';
        if ($this->count == -1) {
            return $this->sourceString;
        }
        $this->assignSysTag();
        $nextTagEnd = 0;
        $strok = "";
        for ($i = 0; $i <= $this->count; $i++) {
            $ResultString .= substr($this->sourceString, $nextTagEnd, $this->cTags[$i]->startPos - $nextTagEnd);
            $ResultString .= $this->cTags[$i]->GetValue();
            $nextTagEnd = $this->cTags[$i]->endPos;
        }
        $slen = strlen($this->sourceString);
        if ($slen > $nextTagEnd) {
            $ResultString .= substr($this->sourceString, $nextTagEnd, $slen - $nextTagEnd);
        }
        return $ResultString;
    }

    /**
     *  直接输出解析模板
     *
     * @access    public
     * @return    void
     */
    function display()
    {
        echo $this->getResult();
    }

    /**
     *  把解析模板输出为文件
     *
     * @access    public
     * @param     string $filename 要保存到的文件
     * @return    string
     */
    function saveTo($filename)
    {
        $fp = @fopen($filename, "w") or die("DedeTag Engine Create File False");
        fwrite($fp, $this->getResult());
        fclose($fp);
    }

    /**
     *  解析模板
     *
     * @access    public
     * @return    string
     */
    function parseTemplet()
    {
        $tagStartWord = $this->tagStartWord;
        $tagEndWord = $this->tagEndWord;
        $sPos = 0;
        $ePos = 0;
        $FullTagStartWord = $tagStartWord . $this->nameSpace . ":";
        $sTagEndWord = $tagStartWord . "/" . $this->nameSpace . ":";
        $eTagEndWord = "/" . $tagEndWord;
        $tsLen = strlen($FullTagStartWord);
        $sourceLen = strlen($this->sourceString);

        if ($sourceLen <= ($tsLen + 3)) {
            return;
        }
        $cAtt = new DedeAttributeParse();
        $cAtt->charToLow = $this->charToLow;

        //遍历模板字符串，请取标记及其属性信息
        for ($i = 0; $i < $sourceLen; $i++) {
            $tTagName = '';

            //如果不进行此判断，将无法识别相连的两个标记
            if ($i - 1 >= 0) {
                $ss = $i - 1;
            } else {
                $ss = 0;
            }
            $sPos = strpos($this->sourceString, $FullTagStartWord, $ss);
            $isTag = $sPos;
            if ($i == 0) {
                $headerTag = substr($this->sourceString, 0, strlen($FullTagStartWord));
                if ($headerTag == $FullTagStartWord) {
                    $isTag = TRUE;
                    $sPos = 0;
                }
            }
            if ($isTag === FALSE) {
                break;
            }
            //判断是否已经到倒数第三个字符(可能性几率极小，取消此逻辑)
            /*
            if($sPos > ($sourceLen-$tsLen-3) )
            {
                break;
            }
            */
            for ($j = ($sPos + $tsLen); $j < ($sPos + $tsLen + $this->tagMaxLen); $j++) {
                if ($j > ($sourceLen - 1)) {
                    break;
                } else if (preg_match("/[\/ \t\r\n]/", $this->sourceString[$j]) || $this->sourceString[$j] == $this->tagEndWord) {
                    break;
                } else {
                    $tTagName .= $this->sourceString[$j];
                }
            }
            if ($tTagName != '') {
                $i = $sPos + $tsLen;
                $endPos = -1;
                $fullTagEndWordThis = $sTagEndWord . $tTagName . $tagEndWord;

                $e1 = strpos($this->sourceString, $eTagEndWord, $i);
                $e2 = strpos($this->sourceString, $FullTagStartWord, $i);
                $e3 = strpos($this->sourceString, $fullTagEndWordThis, $i);

                //$eTagEndWord = /} $FullTagStartWord = {tag: $fullTagEndWordThis = {/tag:xxx]

                $e1 = trim($e1);
                $e2 = trim($e2);
                $e3 = trim($e3);
                $e1 = ($e1 == '' ? '-1' : $e1);
                $e2 = ($e2 == '' ? '-1' : $e2);
                $e3 = ($e3 == '' ? '-1' : $e3);
                //not found '{/tag:'
                if ($e3 == -1) {
                    $endPos = $e1;
                    $elen = $endPos + strlen($eTagEndWord);
                } //not found '/}'
                else if ($e1 == -1) {
                    $endPos = $e3;
                    $elen = $endPos + strlen($fullTagEndWordThis);
                } //found '/}' and found '{/dede:'
                else {
                    //if '/}' more near '{dede:'、'{/dede:' , end tag is '/}', else is '{/dede:'
                    if ($e1 < $e2 && $e1 < $e3) {
                        $endPos = $e1;
                        $elen = $endPos + strlen($eTagEndWord);
                    } else {
                        $endPos = $e3;
                        $elen = $endPos + strlen($fullTagEndWordThis);
                    }
                }

                //not found end tag , error
                if ($endPos == -1) {
                    echo "Tag Character postion $sPos, '$tTagName' Error！<br />\r\n";
                    break;
                }
                $i = $elen;
                $ePos = $endPos;

                //分析所找到的标记位置等信息
                $attStr = '';
                $innerText = '';
                $startInner = 0;
                for ($j = ($sPos + $tsLen); $j < $ePos; $j++) {
                    if ($startInner == 0 && ($this->sourceString[$j] == $tagEndWord && $this->sourceString[$j - 1] != "\\")) {
                        $startInner = 1;
                        continue;
                    }
                    if ($startInner == 0) {
                        $attStr .= $this->sourceString[$j];
                    } else {
                        $innerText .= $this->sourceString[$j];
                    }
                }
                //echo "<xmp>$attStr</xmp>\r\n";
                $cAtt->SetSource($attStr);
                if ($cAtt->cAttributes->getTagName() != '') {
                    $this->count++;
                    $CDTag = new DedeTag();
                    $CDTag->tagName = $cAtt->cAttributes->getTagName();
                    $CDTag->startPos = $sPos;
                    $CDTag->endPos = $i;
                    $CDTag->cAttribute = $cAtt->cAttributes;
                    $CDTag->isReplace = FALSE;
                    $CDTag->tagID = $this->count;
                    $CDTag->InnerText = $innerText;
                    $this->cTags[$this->count] = $CDTag;
                }
            } else {
                $i = $sPos + $tsLen;
                break;
            }
        }//结束遍历模板字符串

        if ($this->IsCache) {
            $this->SaveCache();
        }
    }

    /**
     *  处理某字段的函数
     *
     * @access    public
     * @param     string $fieldvalue 字段值
     * @param     string $functionname 函数名称
     * @param     object $refObj 隶属对象
     * @return    string
     */
    function evalFunc($fieldvalue, $functionname, &$refObj)
    {
        $DedeFieldValue = $fieldvalue;
        $functionname = str_replace("{\"", "[\"", $functionname);
        $functionname = str_replace("\"}", "\"]", $functionname);
        $functionname = preg_replace("/'@me'|\"@me\"|@me/i", '$DedeFieldValue', $functionname);
        $functionname = "\$DedeFieldValue = " . $functionname;
        @eval($functionname . ";"); //or die("<xmp>$functionname</xmp>");
        if (empty($DedeFieldValue)) {
            return '';
        } else {
            return $DedeFieldValue;
        }
    }

    /**
     *  获得一个外部变量
     *
     * @access    public
     * @param     string $publicname 变量名称
     * @return    string
     */
    function getGlobals($publicname)
    {
        $publicname = trim($publicname);

        //禁止在模板文件读取数据库密码
        if ($publicname == "dbuserpwd" || $publicname == "cfg_dbpwd") {
            return "";
        }

        //正常情况
        if (isset($GLOBALS[$publicname])) {
            return $GLOBALS[$publicname];
        } else {
            return "";
        }
    }

    /**
     *  引入文件
     *
     * @access    public
     * @param     string $filename 文件名
     * @param     string $ismake 是否需要编译
     * @return    string
     */
    function includeFile($filename, $ismake = 'no')
    {
        global $cfg_df_style;
        $restr = '';
        if ($filename == '') {
            return '';
        }
        if (file_exists(DEDEROOT . "/templets/" . $filename)) {
            $okfile = DEDEROOT . "/templets/" . $filename;
        } else if (file_exists(DEDEROOT . '/templets/' . $cfg_df_style . '/' . $filename)) {
            $okfile = DEDEROOT . '/templets/' . $cfg_df_style . '/' . $filename;
        } else {
            return "无法在这个位置找到： $filename";
        }

        //编译
        if ($ismake != "no") {
            require_once(DEDEINC . "/channelunit.func.php");
            $dtp = new DedeTagParse();
            $dtp->LoadTemplet($okfile);
            MakeOneTag($dtp, $this->refObj);
            $restr = $dtp->getResult();
        } else {
            $fp = @fopen($okfile, "r");
            while ($line = fgets($fp, 1024)) $restr .= $line;
            fclose($fp);
        }
        return $restr;
    }

    function getCurTag($fieldname)
    {
        if (!isset($this->cTags)) {
            return '';
        }
        foreach ($this->cTags as $ctag) {
            if ($ctag->getTagName() == 'field' && $ctag->getAtt('name') == $fieldname) {
                return $ctag;
            } else {
                continue;
            }
        }
        return '';
    }

}

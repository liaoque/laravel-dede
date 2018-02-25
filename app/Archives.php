<?php

namespace App;

use App\Helpers\Common;
use App\Helpers\DedeTagParse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Archives extends Model
{
    const UPDATED_AT = 'pubdate';

    const CREATED_AT = 'senddate';

    //
    protected $table = 'archives';

    protected $tempSource = '';
    public $fields = [];
    public $splitTitles = [];
    public $splitPageField = [];
    public $splitfields = [];
    public $shortName = '';
    public $nameFirst = '';

    public static $sortTypeList = [
        'id' => '排序',
        'pubdate' => '更新时间',
        'sortrank' => '置顶权值',
        'click' => '点击量',
        'scores' => '评论积分',
        'lastpost' => '最后评论'
    ];


    public static $ismakeTypeList = [
        '1' => '已生成',
        '-1' => '仅动态',
        '0' => '未生成',
    ];

    public static $isHtmlList = [
        '1' => '生成HTML',
        '0' => '仅动态浏览'
    ];

    public static $sortArticleList = [
        '0' => '默认排序',
        '7' => '置顶一周',
        '30' => '置顶一个月',
        '90' => '置顶三个月',
        '180' => '置顶半年',
        '360' => '置顶一年'
    ];

    public static function searchFilter(Request $request)
    {
        if (empty($request->all())) {
            return self::where('channel', '>', 0)
                ->where('arcrank', '>', -2)
                ->orderBy('id', 'DESC')->paginate(1);
        }
        $r = self::where('arcrank', '>', -2);
        if ($request->get('channel')) {
            $r->where('channel', $request->get('channel'));
        }
        if ($request->get('mid')) {
            $r->where('mid', $request->get('mid'));
        }
        if ($request->get('cid')) {
            $r->whereIn('typeid', array_keys(Arctype::getChildAllWithCache($request->get('cid'))));
        }
        if ($request->get('keyword')) {
            $r->whereRaw('CONCAT(title,writer) like "%' . $request->get('keyword') . '%"');
        }
        if ($request->get('flag')) {
            $r->whereRaw('FIND_IN_SET("' . $request->get('flag') . '", flag) ');
        }
        if ($request->get('orderby')) {
            $r->orderBy($request->get('orderby'), 'DESC');
        }
        return $r->paginate(1);
    }

    public function member()
    {
        return self::hasOne(Member::class, 'mid', 'mid');
    }

    public static function isMakeTypeText($ismake)
    {
        $text = self::$ismakeTypeList[0];
        if (!empty(self::$ismakeTypeList[$ismake])) {
            $text = self::$ismakeTypeList[$ismake];
        }
        return $text;
    }

    public function channelType()
    {
        return $this->hasOne(ChannelType::class, 'id', 'channel');
    }

    public function arctype()
    {
        return $this->hasOne(Arctype::class, 'id', 'typeid');
    }

    public static function defalutArchives()
    {
        $sysConfig = CfgConfig::sysConfig();
        $arctype = new self();
//        $arctype->id = 0;
        $arctype->typeid = 0;
        $arctype->typeid2 = 0;
        $arctype->sortrank = 50;
        $arctype->flag = '';
        $arctype->ismake = 0;
        $arctype->channel = 0;
        $arctype->arcrank = 0;
        $arctype->click = $sysConfig->getAttribute('cfg_arc_click') == -1 ? mt_rand(50, 200) : $sysConfig->getAttribute('cfg_arc_click');
        $arctype->money = 0;
        $arctype->title = '';
        $arctype->shorttitle = '';
        $arctype->color = '#000';
        $arctype->writer = '';
        $arctype->source = '';
        $arctype->litpic = '';
        $arctype->pubdate = time();
        $arctype->mid = '';
        $arctype->voteid = 0;
        $arctype->notpost = 1;
        $arctype->description = '';
        $arctype->keywords = '';
        $arctype->filename = '';
        $arctype->dutyadmin = '';
        $arctype->weight = '';
        return $arctype;
    }

    public static function createNewArctype(Request $request)
    {

//        $query = "INSERT INTO `#@__archives`(id,typeid,typeid2,sortrank,flag,ismake,channel,arcrank,click,money,title,shorttitle,
//    color,writer,source,litpic,pubdate,senddate,mid,voteid,notpost,description,keywords,filename,dutyadmin,weight)
//    VALUES ('$arcID','$typeid','$typeid2','$sortrank','$flag','$ismake','$channelid','$arcrank','$click','$money',
//    '$title','$shorttitle','$color','$writer','$source','$litpic','$pubdate','$senddate',
//    '$adminid','$voteid','$notpost','$description','$keywords','$filename','$adminid','$weight');";

        $parmas = $request->post([
            'id',
            'typeid',
            'typeid2',
            'sortrank',
            'flag',
            'ismake',
            'channel',
            'arcrank',
            'click',
            'money',
            'title',
            'shorttitle',
            'color',
            'writer',
            'source',
            'litpic',
            'litpic',
            'pubdate',
            'mid',
            'voteid',
            'notpost',
            'description',
            'keywords',
            'filename',
            'dutyadmin',
            'weight',
        ]);

        $parmas = array_filter($parmas);

        $archives = self::defalutArchives();
        foreach ($parmas as $key => $v) {
            $archives->$key = $v;
        }

        return $archives->save() ? $archives : false;

    }

    public function makeHtml()
    {
        $this->fields["displaytype"] = "st";
        $this->loadTemplet();
        $this->parAddTable();
        $this->parseTempletsFirst();

        $this->fields['senddate'] = empty($this->fields['senddate']) ? '' : $this->fields['senddate'];
        $this->fields['title'] = empty($this->fields['title']) ? '' : $this->fields['title'];
        $this->fields['arcrank'] = empty($this->fields['arcrank']) ? 0 : $this->fields['arcrank'];
        $this->fields['ismake'] = empty($this->fields['ismake']) ? 0 : $this->fields['ismake'];
        $this->fields['money'] = empty($this->fields['money']) ? 0 : $this->fields['money'];
        $this->fields['filename'] = empty($this->fields['filename']) ? '' : $this->fields['filename'];

        //分析要创建的文件名称
        $filename = getFileNewName(
            $this->id, $this->fields['typeid'], $this->fields['senddate'],
            $this->fields['title'], $this->fields['ismake'], $this->fields['arcrank'],
            $this->arctype->namerule, $this->arctype->typedir, $this->fields['money'], $this->fields['filename']
        );

        $filenames = explode(".", $filename);
        $this->shortName = $filenames[count($filenames) - 1];
        if ($this->shortName == '') $this->shortName = 'html';
        $fileFirst = preg_replace("/\." . $this->shortName . "$/i", "", $filename);
        $this->fields['namehand'] = basename($fileFirst);
        $filenames = explode("/", $filename);
        $this->nameFirst = preg_replace("/\." . $this->shortName . "$/i", "", $filenames[count($filenames) - 1]);
        if ($this->nameFirst == '') {
            $this->nameFirst = $this->id;
        }

        //获得当前文档的全名
        $filenameFull = getFileUrl(
            $this->id, $this->fields['typeid'], $this->fields["senddate"],
            $this->fields["title"], $this->fields["ismake"],
            $this->fields["arcrank"], $this->arctype->namerule, $this->arctype->typedir, $this->fields["money"], $this->fields['filename'],
            $this->arctype->moresite, $this->arctype->siteurl, $this->arctype->sitepath
        );
        $this->fields['arcurl'] = $this->fields['fullname'] = $filenameFull;

        //对于已设置不生成HTML的文章直接返回网址
        if ($this->fields['ismake'] == -1 || $this->fields['arcrank'] != 0 || $this->fields['money'] > 0
            || ($this->fields['typeid'] == 0 && $this->fields['channel'] != -1)) {
            return $this->getTrueUrl($filename);
        } //循环生成HTML文件
        else {
            for ($i = 1; $i <= $this->totalPage; $i++) {
                if ($this->totalPage > 1) {
                    $this->fields['tmptitle'] = (empty($this->fields['tmptitle']) ? $this->fields['title'] : $this->fields['tmptitle']);
                    if ($i > 1) $this->fields['title'] = $this->fields['tmptitle'] . "($i)";
                }
                if ($i > 1) {
                    $TRUEfilename = $this->getTruePath() . $fileFirst . "_" . $i . "." . $this->shortName;
                } else {
                    $TRUEfilename = $this->getTruePath() . $filename;
                }
                $this->parseDMfields($i, 1);
                $this->getDtp()->SaveTo($TRUEfilename);
                //如果启用远程发布则需要进行判断
                if (false) {
//
//                    //分析远程文件路径
//                    $remotefile = str_replace(DEDEROOT, '', $TRUEfilename);
//                    $localfile = '..'.$remotefile;
//                    //创建远程文件夹
//                    $remotedir = preg_replace("#[^\/]*\.html#", '', $remotefile);
//                    $this->ftp->rmkdir($remotedir);
//                    $this->ftp->upload($localfile, $remotefile, 'ascii');
                }
            }
        }
        $this->ismake = 1;
        $this->save();
        return $this->getTrueUrl($filename);
    }

    /**
     *  解析模板，对内容里的变动进行赋值
     *
     * @access    public
     * @param     string $pageNo 页码数
     * @param     string $ismake 是否生成
     * @return    string
     */
    function parseDMfields($pageNo, $ismake = 1)
    {
        $this->nowPage = $pageNo;
        $this->fields['nowpage'] = $this->nowPage;
        if ($this->splitPageField != '' && isset($this->fields[$this->splitPageField])) {
            $this->fields[$this->splitPageField] = $this->splitfields[$pageNo - 1];
            if ($pageNo > 1) $this->fields['description'] = trim(preg_replace("/[\r\n\t]/", ' ', cn_substr(html2text($this->fields[$this->splitPageField]), 200)));
        }

        //解析模板
        if (is_array($this->getDtp()->cTags)) {
            foreach ($this->getDtp()->cTags as $i => $ctag) {
                if ($ctag->getName() == 'field') {
                    $this->getDtp()->assign($i, $this->getField($ctag->getAtt('name'), $ctag));
                } else if ($ctag->getName() == 'pagebreak') {
                    if ($ismake == 0) {
                        $this->getDtp()->assign($i, $this->getPagebreakDM($this->totalPage, $this->nowPage, $this->id));
                    } else {
                        $this->getDtp()->assign($i, $this->GetPagebreak($this->totalPage, $this->nowPage, $this->id));
                    }
                } else if ($ctag->getName() == 'pagetitle') {
                    if ($ismake == 0) {
                        $this->getDtp()->assign($i, $this->getPagebreakDM($ctag->getAtt("style"), $pageNo));
                    } else {
                        $this->getDtp()->assign($i, $this->getPageTitlesST($ctag->getAtt("style"), $pageNo));
                    }
                } else if ($ctag->getName() == 'prenext') {
                    $this->getDtp()->assign($i, $this->getPreNext($ctag->getAtt('get')));
                } else if ($ctag->getName() == 'fieldlist') {
                    $innertext = trim($ctag->getInnerText());
                    if ($innertext == '') $innertext = CfgConfig::sysConfig()->getSysTemplets('tag_fieldlist.htm');
                    $dtp2 = new DedeTagParse();
                    $dtp2->setNameSpace('field', '[', ']');
                    $dtp2->loadSource($innertext);
                    $oldSource = $dtp2->sourceString;
                    $oldCtags = $dtp2->cTags;
                    $res = '';
                    if (is_array($dtp2->cTags)) {
                        foreach ($this->channelType->toArray() as $k => $v) {
                            if (isset($v['autofield']) && empty($v['autofield'])) {
                                continue;
                            }
                            $dtp2->sourceString = $oldSource;
                            $dtp2->cTags = $oldCtags;
                            $fname = $v['itemname'];
                            foreach ($dtp2->cTags as $tid => $ctag2) {
                                if ($ctag2->getName() == 'name') {
                                    $dtp2->assign($tid, $fname);
                                } else if ($ctag2->getName() == 'tagname') {
                                    $dtp2->assign($tid, $k);
                                } else if ($ctag2->getName() == 'value') {
                                    $this->fields[$k] = $this->channelType->makeField($k, $this->fields[$k], $ctag2);
                                    @$dtp2->assign($tid, $this->fields[$k]);
                                }
                            }
                            $res .= $dtp2->getResult();
                        }
                    }
                    $this->getDtp()->assign($i, $res);
                }//end case

            }//结束模板循环

        }
    }


    public function loadTemplet()
    {
        if ($this->getTempSource() == '') {
            $tempfile = $this->getTempletFile();
            if (!file_exists($tempfile) || !is_file($tempfile)) {
//            throw new \Error("文档ID：{$this->fields['id']} - {$this->TypeLink->TypeInfos['typename']} - {$this->fields['title']}<br />");
                return false;
            }
            $this->getDtp()->loadTemplate($tempfile);
            $this->setTempSource($this->getDtp()->SourceString);
        } else {
            $this->getDtp()->loadSource($this->getTempSource());
        }
    }

    public function getTempSource()
    {
        return $this->tempSource;
    }

    public function setTempSource($tempSource)
    {
        return $this->tempSource = $tempSource;
    }

    public function getDtp()
    {
        static $dtp = null;
        if (empty($dtp)) {
            $dtp = new DedeTagParse();
        }
        return $dtp;
    }

    public function getTempletFile()
    {
        $cid = $this->channelType->nid;
        $this->channelType->setArchives($this);
        $addtable = $this->channelType->addtable;
        $templet = $this->$addtable->templet;
        $sysConfig = CfgConfig::sysConfig();
        $cfgDfStyle = $sysConfig->cfg_df_style;
        $cfgBasedir = $sysConfig->cfg_basedir;
        $cfgTempletsDir = $sysConfig->cfg_templets_dir;
        if ($templet) {
            $filetag = Common::mfTemplet($templet);
            if (!preg_match("/\//", $filetag))
                $filetag = $cfgDfStyle . '/' . $filetag;
        } else {
            $filetag = Common::mfTemplet($this->arctype->temparticle);
        }
        $tid = $this->arctype->typeid;
        $filetag = str_replace('{cid}', $cid, $filetag);
        $filetag = str_replace('{tid}', $tid, $filetag);
        $tmpfile = $cfgBasedir . $cfgTempletsDir . '/' . $filetag;
        if ($cid == 'spec') {
            if (!empty($this->fields['templet'])) {
                $tmpfile = $cfgBasedir . $cfgTempletsDir . '/' . $filetag;
            } else {
                $tmpfile = $cfgBasedir . $cfgTempletsDir . "/{$cfgDfStyle}/article_spec.htm";
            }
        }
        if (defined('DEDEMOB')) {
            $tmpfile = str_replace('.htm', '_m.htm', $tmpfile);
        }
        if (!file_exists($tmpfile)) {
            $tmpfile = $cfgBasedir . $cfgTempletsDir . "/{$cfgDfStyle}/" . ($cid == 'spec' ? 'article_spec.htm' : 'article_default.htm');
            if (defined('DEDEMOB')) {
                $tmpfile = str_replace('.htm', '_m.htm', $tmpfile);
            }
        }
        if (!preg_match("/.htm$/", $tmpfile)) return FALSE;
        return $tmpfile;
    }


    public function addonarticle()
    {
        return $this->hasOne(Addonarticle::class, 'aid', 'id');
    }

    public function addonimage()
    {
        return $this->hasOne(Addonimage::class, 'aid', 'id');
    }

    public function addoninfo()
    {
        return $this->hasOne(Addoninfo::class, 'aid', 'id');
    }

    public function addonshop()
    {
        return $this->hasOne(Addonshop::class, 'aid', 'id');
    }

    public function addonsoft()
    {
        return $this->hasOne(Addonsoft::class, 'aid', 'id');
    }

    public function addonspec()
    {
        return $this->hasOne(Addonspec::class, 'aid', 'id');
    }

    /**
     *  解析附加表的内容
     * @access    public
     * @return    void
     */
    function parAddTable()
    {
        //读取附加表信息，并把附加表的资料经过编译处理后导入到$this->fields中，以方便在模板中用 {dede:field name='fieldname' /} 标记统一调用
        if ($this->channelType->addtable != '') {
            $addtable = $this->channelType->addtable;
            $row = $this->$addtable->toArray();
            if ($this->channelType->issystem == -1) {
                $this->fields['title'] = $row['title'];
                $this->fields['senddate'] = $this->fields['pubdate'] = $row['senddate'];
                $this->fields['mid'] = $this->fields['adminid'] = $row['mid'];
                $this->fields['ismake'] = 1;
                $this->fields['arcrank'] = 0;
                $this->fields['money'] = 0;
                $this->fields['filename'] = '';
            }

            if (is_array($row)) {
                foreach ($row as $k => $v) $row[strtolower($k)] = $v;
            }
            $channelfields = $this->channelType->getChannelfields();
            if (is_array($channelfields) && !empty($channelfields)) {
                foreach ($channelfields as $k => $arr) {
                    if (isset($row[$k])) {
                        if (!empty($arr['rename'])) {
                            $nk = $arr['rename'];
                        } else {
                            $nk = $k;
                        }
                        $cobj = $this->getDtp()->getCurTag($k);
                        if (is_object($cobj)) {
                            foreach ($this->getDtp()->cTags as $cTag) {
                                if ($cTag->getTagName() == 'field' && $cTag->getAtt('name') == $k) {
                                    //带标识的专题节点
                                    if ($cTag->getAtt('noteid') != '') {
                                        $this->fields[$k . '_' . $cTag->getAtt('noteid')] = $this->channelType->makeField($k, $row[$k], $cTag);
                                    } //带类型的字段节点
                                    else if ($cTag->getAtt('type') != '') {
                                        $this->fields[$k . '_' . $cTag->getAtt('type')] = $this->channelType->makeField($k, $row[$k], $cTag);
                                    } //其它字段
                                    else {
                                        $this->fields[$nk] = $this->channelType->makeField($k, $row[$k], $cTag);
                                    }
                                }
                            }
                        } else {
                            $this->fields[$nk] = $row[$k];
                        }
                        if ($arr['type'] == 'htmltext' && $GLOBALS['cfg_keyword_replace'] == 'Y' && !empty($this->fields['keywords'])) {
                            $this->fields[$nk] = $this->replaceKeyword($this->fields['keywords'], $this->fields[$nk]);
                        }
                    }
                }//End foreach
            }
            //设置全局环境变量
            $this->fields['typename'] = $this->channelType->typename;
//            @SetSysEnv($this->fields['typeid'], $this->fields['typename'], $this->fields['id'], $this->fields['title'], 'archives');
        }
        //完成附加表信息读取
        unset($row);

        //处理要分页显示的字段
        $this->splitTitles = Array();
        if ($this->splitPageField != '' && CfgConfig::sysConfig()->cfg_arcsptitle == 'Y'
            && isset($this->fields[$this->splitPageField])
        ) {
            $this->splitfields = explode("#p#", $this->fields[$this->splitPageField]);
            $i = 1;
            foreach ($this->splitfields as $k => $v) {
                $tmpv = cn_substr($v, 50);
                $pos = strpos($tmpv, '#e#');
                if ($pos > 0) {
                    $st = trim(cn_substr($tmpv, $pos));
                    if ($st == "" || $st == "副标题" || $st == "分页标题") {
                        $this->splitfields[$k] = preg_replace("/^(.*)#e#/is", "", $v);
                        continue;
                    } else {
                        $this->splitfields[$k] = preg_replace("/^(.*)#e#/is", "", $v);
                        $this->splitTitles[$k] = $st;
                    }
                } else {
                    continue;
                }
                $i++;
            }
            $this->totalPage = count($this->splitfields);
            $this->fields['totalPage'] = $this->totalPage;
        }

        //处理默认缩略图等
        if (isset($this->fields['litpic'])) {
            if ($this->fields['litpic'] == '-' || $this->fields['litpic'] == '') {
                $this->fields['litpic'] = CfgConfig::sysConfig()->cfg_cmspath . '/images/defaultpic.gif';
            }
            if (!preg_match("#^http:\/\/#i", $this->fields['litpic']) && CfgConfig::sysConfig()->cfg_multi_site == 'Y') {
                $this->fields['litpic'] = CfgConfig::sysConfig()->cfg_mainsite . $this->fields['litpic'];
            }
            $this->fields['picname'] = $this->fields['litpic'];

            //模板里直接使用{dede:field name='image'/}获取缩略图
            $this->fields['image'] = (!preg_match('/jpg|gif|png/i', $this->fields['picname']) ? '' : "<img src='{$this->fields['picname']}' />");
        }
        // 处理投票选项
        if (isset($this->fields['voteid']) && !empty($this->fields['voteid'])) {
            $this->fields['vote'] = '';
            $voteid = $this->fields['voteid'];
            $this->fields['vote'] = "<script language='javascript' src='{CfgConfig::sysConfig()->cfg_cmspath}/data/vote/vote_{$voteid}.js'></script>";
            if (CfgConfig::sysConfig()->cfg_multi_site == 'Y') {
                $this->fields['vote'] = "<script language='javascript' src='{CfgConfig::sysConfig()->cfg_mainsite}/data/vote/vote_{$voteid}.js'></script>";
            }
        }

        if (isset($this->fields['goodpost']) && isset($this->fields['badpost'])) {
            //digg
            if ($this->fields['goodpost'] + $this->fields['badpost'] == 0) {
                $this->fields['goodper'] = $this->fields['badper'] = 0;
            } else {
                $this->fields['goodper'] = number_format($this->fields['goodpost'] / ($this->fields['goodpost'] + $this->fields['badpost']), 3) * 100;
                $this->fields['badper'] = 100 - $this->fields['goodper'];
            }
        }
    }

    /**
     *  解析模板，对固定的标记进行初始给值
     * @access    public
     * @return    void
     */
    function parseTempletsFirst()
    {
        if (empty($this->fields['keywords'])) {
            $this->fields['keywords'] = '';
        }

        if (empty($this->fields['reid'])) {
            $this->fields['reid'] = 0;
        }

        $GLOBALS['envs']['tags'] = $this->fields['tags'];

        if (isset($this->arctype->reid)) {
            $GLOBALS['envs']['reid'] = $this->arctype->reid;
        }

        $GLOBALS['envs']['keyword'] = $this->fields['keywords'];

        $GLOBALS['envs']['typeid'] = $this->fields['typeid'];

        $GLOBALS['envs']['topid'] = Arctype::getTopIdWithCache($this->fields['typeid']);

        $GLOBALS['envs']['aid'] = $GLOBALS['envs']['id'] = $this->fields['id'];

        $GLOBALS['envs']['adminid'] = $GLOBALS['envs']['mid'] = isset($this->fields['mid']) ? $this->fields['mid'] : 1;

        $GLOBALS['envs']['channelid'] = $this->channel;

        if ($this->fields['reid'] > 0) {
            $GLOBALS['envs']['typeid'] = $this->fields['reid'];
        }

        MakeOneTag($this->dtp, $this, 'N');
    }

    function replaceKeyword($kw, $body)
    {
        $maxkey = 5;
        $kws = explode(",", trim($kw));    //以分好为间隔符
        $i = 0;
        $karr = $kaarr = array();

        //暂时屏蔽超链接
        $body = preg_replace("#(<a(.*))(>)(.*)(<)(\/a>)#isU", '\\1-]-\\4-[-\\6', $body);
        $rowList = KeyWord::where('rpurl', '<>', '')->orderBy('rank', 'desc')->all()->toArray();
        foreach ($rowList as $row) {
            $key = trim($row['keyword']);
            $key_url = trim($row['rpurl']);
            $karr[] = $key;
            $kaarr[] = "<a href='$key_url' target='_blank'><u>$key</u></a>";
        }

        // 这里可能会有错误
        $body = @preg_replace("#(^|>)([^<]+)(?=<|$)#sUe", "_highlight('\\2', \$karr, \$kaarr, '\\1')", $body);

        //恢复超链接
        $body = preg_replace("#(<a(.*))-\]-(.*)-\[-(\/a>)#isU", '\\1>\\3<\\4', $body);
        return $body;
    }


    /**
     *  获得真实连接路径
     *
     * @access    public
     * @param     string $nurl 连接
     * @return    string
     */
    public function getTrueUrl($nurl = '')
    {
        return getFileUrl
        (
            $this->fields['id'],
            $this->fields['typeid'],
            $this->fields['senddate'],
            $this->fields['title'],
            $this->fields['ismake'],
            $this->fields['arcrank'],
            $this->arctype->namerule,
            $this->arctype->typedir,
            $this->fields['money'],
            $this->fields['filename'],
            $this->arctype->moresite,
            $this->arctype->siteurl,
            $this->arctype->sitepath
        );
    }


    /**
     *  获得站点的真实根路径
     *
     * @access    public
     * @return    string
     */
    public function getTruePath()
    {
        return CfgConfig::sysConfig()->cfg_basedir;
    }

    /**
     *  获得动态页面分页列表
     *
     * @access    public
     * @param     int $totalPage 总页数
     * @param     int $nowPage 当前页数
     * @param     int $aid 文档id
     * @return    string
     */
    public function getPagebreakDM($totalPage, $nowPage, $aid)
    {
        $cfg_rewrite = CfgConfig::sysConfig()->cfg_rewrite;
        if ($totalPage == 1) {
            return "";
        }
        $PageList = "<li><a>共" . $totalPage . "页: </a></li>";
        $nPage = $nowPage - 1;
        $lPage = $nowPage + 1;
        if ($nowPage == 1) {
            $PageList .= "<li><a href='#'>上一页</a></li>";
        } else {
            if ($nPage == 1) {
                $PageList .= "<li><a href='view.php?aid=$aid'>上一页</a></li>";
                if ($cfg_rewrite == 'Y') {
                    $PageList = preg_replace("#.php\?aid=(\d+)#i", '-\\1-1.html', $PageList);
                }
            } else {
                $PageList .= "<li><a href='view.php?aid=$aid&pageno=$nPage'>上一页</a></li>";
                if ($cfg_rewrite == 'Y') {
                    $PageList = str_replace(".php?aid=", "-", $PageList);
                    $PageList = preg_replace("#&pageno=(\d+)#i", '-\\1.html', $PageList);
                }
            }
        }
        for ($i = 1; $i <= $totalPage; $i++) {
            if ($i == 1) {
                if ($nowPage != 1) {
                    $PageList .= "<li><a href='view.php?aid=$aid'>1</a></li>";
                    if ($cfg_rewrite == 'Y') {
                        $PageList = preg_replace("#.php\?aid=(\d+)#i", '-\\1-1.html', $PageList);
                    }
                } else {
                    $PageList .= "<li class=\"thisclass\"><a>1</a></li>";
                }
            } else {
                $n = $i;
                if ($nowPage != $i) {
                    $PageList .= "<li><a href='view.php?aid=$aid&pageno=$i'>" . $n . "</a></li>";
                    if ($cfg_rewrite == 'Y') {
                        $PageList = str_replace(".php?aid=", "-", $PageList);
                        $PageList = preg_replace("#&pageno=(\d+)#i", '-\\1.html', $PageList);
                    }
                } else {
                    $PageList .= "<li class=\"thisclass\"><a href='#'>{$n}</a></li>";
                }
            }
        }
        if ($lPage <= $totalPage) {
            $PageList .= "<li><a href='view.php?aid=$aid&pageno=$lPage'>下一页</a></li>";
            if ($cfg_rewrite == 'Y') {
                $PageList = str_replace(".php?aid=", "-", $PageList);
                $PageList = preg_replace("#&pageno=(\d+)#i", '-\\1.html', $PageList);
            }
        } else {
            $PageList .= "<li><a href='#'>下一页</a></li>";
        }
        return $PageList;
    }

    /**
     *  获得静态页面分页列表
     *
     * @access    public
     * @param     int $totalPage 总页数
     * @param     int $nowPage 当前页数
     * @param     int $aid 文档id
     * @return    string
     */
    public function getPagebreak($totalPage, $nowPage, $aid)
    {
        if ($totalPage == 1) {
            return "";
        }
        $PageList = "<li><a>共" . $totalPage . "页: </a></li>";
        $nPage = $nowPage - 1;
        $lPage = $nowPage + 1;
        if ($nowPage == 1) {
            $PageList .= "<li><a href='#'>上一页</a></li>";
        } else {
            if ($nPage == 1) {
                $PageList .= "<li><a href='" . $this->NameFirst . "." . $this->ShortName . "'>上一页</a></li>";
            } else {
                $PageList .= "<li><a href='" . $this->NameFirst . "_" . $nPage . "." . $this->ShortName . "'>上一页</a></li>";
            }
        }
        for ($i = 1; $i <= $totalPage; $i++) {
            if ($i == 1) {
                if ($nowPage != 1) {
                    $PageList .= "<li><a href='" . $this->NameFirst . "." . $this->ShortName . "'>1</a></li>";
                } else {
                    $PageList .= "<li class=\"thisclass\"><a href='#'>1</a></li>";
                }
            } else {
                $n = $i;
                if ($nowPage != $i) {
                    $PageList .= "<li><a href='" . $this->NameFirst . "_" . $i . "." . $this->ShortName . "'>" . $n . "</a></li>";
                } else {
                    $PageList .= "<li class=\"thisclass\"><a href='#'>{$n}</a></li>";
                }
            }
        }
        if ($lPage <= $totalPage) {
            $PageList .= "<li><a href='" . $this->NameFirst . "_" . $lPage . "." . $this->ShortName . "'>下一页</a></li>";
        } else {
            $PageList .= "<li><a href='#'>下一页</a></li>";
        }
        return $PageList;
    }


    /**
     *  获取上一篇，下一篇链接
     *
     * @access    public
     * @param     string $gtype 获取类型
     *                    pre:上一篇  preimg:上一篇图片  next:下一篇  nextimg:下一篇图片
     * @return    string
     */
    function getPreNext($gtype = '')
    {
        $rs = '';
        if (count($this->PreNext) < 2) {
            $aid = $this->ArcID;
            $preR = $this->dsql->GetOne("Select id From `#@__arctiny` where id<$aid And arcrank>-1 And typeid='{$this->Fields['typeid']}' order by id desc");
            $nextR = $this->dsql->GetOne("Select id From `#@__arctiny` where id>$aid And arcrank>-1 And typeid='{$this->Fields['typeid']}' order by id asc");
            $next = (is_array($nextR) ? " where arc.id={$nextR['id']} " : ' where 1>2 ');
            $pre = (is_array($preR) ? " where arc.id={$preR['id']} " : ' where 1>2 ');
            $query = "Select arc.id,arc.title,arc.shorttitle,arc.typeid,arc.ismake,arc.senddate,arc.arcrank,arc.money,arc.filename,arc.litpic,
                        t.typedir,t.typename,t.namerule,t.namerule2,t.ispart,t.moresite,t.siteurl,t.sitepath
                        from `#@__archives` arc left join #@__arctype t on arc.typeid=t.id  ";
            $nextRow = $this->dsql->GetOne($query . $next);
            $preRow = $this->dsql->GetOne($query . $pre);
            if (is_array($preRow)) {
                if (defined('DEDEMOB')) {
                    $mlink = 'view.php?aid=' . $preRow['id'];
                } else {
                    $mlink = GetFileUrl($preRow['id'], $preRow['typeid'], $preRow['senddate'], $preRow['title'], $preRow['ismake'], $preRow['arcrank'],
                        $preRow['namerule'], $preRow['typedir'], $preRow['money'], $preRow['filename'], $preRow['moresite'], $preRow['siteurl'], $preRow['sitepath']);
                }

                $this->PreNext['pre'] = "上一篇：<a href='$mlink'>{$preRow['title']}</a> ";
                $this->PreNext['preimg'] = "<a href='$mlink'><img src=\"{$preRow['litpic']}\" alt=\"{$preRow['title']}\"/></a> ";
            } else {
                $this->PreNext['pre'] = "上一篇：没有了 ";
                $this->PreNext['preimg'] = "<img src=\"/templets/default/images/nophoto.jpg\" alt=\"对不起，没有上一图集了！\"/>";
            }
            if (is_array($nextRow)) {
                if (defined('DEDEMOB')) {
                    $mlink = 'view.php?aid=' . $preRow['id'];
                } else {
                    $mlink = GetFileUrl($nextRow['id'], $nextRow['typeid'], $nextRow['senddate'], $nextRow['title'], $nextRow['ismake'], $nextRow['arcrank'],
                        $nextRow['namerule'], $nextRow['typedir'], $nextRow['money'], $nextRow['filename'], $nextRow['moresite'], $nextRow['siteurl'], $nextRow['sitepath']);
                }

                $this->PreNext['next'] = "下一篇：<a href='$mlink'>{$nextRow['title']}</a> ";
                $this->PreNext['nextimg'] = "<a href='$mlink'><img src=\"{$nextRow['litpic']}\" alt=\"{$nextRow['title']}\"/></a> ";
            } else {
                $this->PreNext['next'] = "下一篇：没有了 ";
                $this->PreNext['nextimg'] = "<a href='javascript:void(0)' alt=\"\"><img src=\"/templets/default/images/nophoto.jpg\" alt=\"对不起，没有下一图集了！\"/></a>";
            }
        }
        if ($gtype == 'pre') {
            $rs = $this->PreNext['pre'];
        } else if ($gtype == 'preimg') {

            $rs = $this->PreNext['preimg'];
        } else if ($gtype == 'next') {
            $rs = $this->PreNext['next'];
        } else if ($gtype == 'nextimg') {

            $rs = $this->PreNext['nextimg'];
        } else {
            $rs = $this->PreNext['pre'] . " &nbsp; " . $this->PreNext['next'];
        }
        return $rs;
    }


}

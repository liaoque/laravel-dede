<?php

namespace App;

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

        $this->loadTemplet();
        $this->parAddTable();
        $this->parseTempletsFirst();
    }

    public function loadTemplet()
    {
        if ($this->getTempSource() == '') {
            $tempfile = $this->getTempletFile();
            if (!file_exists($tempfile) || !is_file($tempfile)) {
//            throw new \Error("文档ID：{$this->Fields['id']} - {$this->TypeLink->TypeInfos['typename']} - {$this->Fields['title']}<br />");
                return false;
            }
            $this->getDtp()->LoadTemplate($tempfile);
            $this->setTempSource($this->getDtp()->SourceString);
        } else {
            $this->getDtp()->LoadSource($this->getTempSource());
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
        $addtable = $this->channelType->addtable;
        $templet = $this->$addtable->templet;
        $sysConfig = CfgConfig::sysConfig();
        $cfgDfStyle = $sysConfig->cfg_df_style;
        $cfgBasedir = $sysConfig->cfg_basedir;
        $cfgTempletsDir = $sysConfig->cfg_templets_dir;
        if ($templet) {
            $filetag = common::mfTemplet($templet);

            if (!preg_match("#\/#", $filetag))
                $filetag = $cfgDfStyle . '/' . $filetag;
        } else {
            $filetag = common::MfTemplet($this->arctype->temparticle);
        }
        $tid = $this->arctype->typeid;
        $filetag = str_replace('{cid}', $cid, $filetag);
        $filetag = str_replace('{tid}', $tid, $filetag);
        $tmpfile = $cfgBasedir . $cfgTempletsDir . '/' . $filetag;
        if ($cid == 'spec') {
            if (!empty($this->Fields['templet'])) {
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
        if (!preg_match("#.htm$#", $tmpfile)) return FALSE;
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
    function ParAddTable()
    {
        //读取附加表信息，并把附加表的资料经过编译处理后导入到$this->Fields中，以方便在模板中用 {dede:field name='fieldname' /} 标记统一调用
        if ($this->channelType->addtable != '') {
            $row = $this->addTableRow;
            if ($this->channelType->issystem == -1) {
//                $this->Fields['title'] = $row['title'];
//                $this->Fields['senddate'] = $this->Fields['pubdate'] = $row['senddate'];
//                $this->Fields['mid'] = $this->Fields['adminid'] = $row['mid'];
//                $this->Fields['ismake'] = 1;
//                $this->Fields['arcrank'] = 0;
//                $this->Fields['money'] = 0;
//                $this->Fields['filename'] = '';
            }

            if (is_array($row)) {
                foreach ($row as $k => $v) $row[strtolower($k)] = $v;
            }
            if (is_array($this->channelType) && !empty($this->channelType)) {
                foreach ($this->channelType as $k => $arr) {
                    if (isset($row[$k])) {
                        if (!empty($arr['rename'])) {
                            $nk = $arr['rename'];
                        } else {
                            $nk = $k;
                        }
                        $cobj = $this->getDtp()->GetCurTag($k);
                        if (is_object($cobj)) {
                            foreach ($this->getDtp()->CTags as $ctag) {
                                if ($ctag->getTagName() == 'field' && $ctag->getAtt('name') == $k) {
                                    //带标识的专题节点
                                    if ($ctag->getAtt('noteid') != '') {
                                        $this->Fields[$k . '_' . $ctag->getAtt('noteid')] = $this->channelType->makeField($k, $row[$k], $ctag);
                                    } //带类型的字段节点
                                    else if ($ctag->getAtt('type') != '') {
                                        $this->Fields[$k . '_' . $ctag->getAtt('type')] = $this->channelType->makeField($k, $row[$k], $ctag);
                                    } //其它字段
                                    else {
                                        $this->Fields[$nk] = $this->channelType->makeField($k, $row[$k], $ctag);
                                    }
                                }
                            }
                        } else {
                            $this->Fields[$nk] = $row[$k];
                        }
                        if ($arr['type'] == 'htmltext' && $GLOBALS['cfg_keyword_replace'] == 'Y' && !empty($this->Fields['keywords'])) {
                            $this->Fields[$nk] = $this->ReplaceKeyword($this->Fields['keywords'], $this->Fields[$nk]);
                        }
                    }
                }//End foreach
            }
            //设置全局环境变量
            $this->Fields['typename'] = $this->TypeLink->TypeInfos['typename'];
            @SetSysEnv($this->Fields['typeid'], $this->Fields['typename'], $this->Fields['id'], $this->Fields['title'], 'archives');
        }
        //完成附加表信息读取
        unset($row);

        //处理要分页显示的字段
        $this->SplitTitles = Array();
        if ($this->SplitPageField != '' && $GLOBALS['cfg_arcsptitle'] = 'Y'
                && isset($this->Fields[$this->SplitPageField])
        ) {
            $this->SplitFields = explode("#p#", $this->Fields[$this->SplitPageField]);
            $i = 1;
            foreach ($this->SplitFields as $k => $v) {
                $tmpv = cn_substr($v, 50);
                $pos = strpos($tmpv, '#e#');
                if ($pos > 0) {
                    $st = trim(cn_substr($tmpv, $pos));
                    if ($st == "" || $st == "副标题" || $st == "分页标题") {
                        $this->SplitFields[$k] = preg_replace("/^(.*)#e#/is", "", $v);
                        continue;
                    } else {
                        $this->SplitFields[$k] = preg_replace("/^(.*)#e#/is", "", $v);
                        $this->SplitTitles[$k] = $st;
                    }
                } else {
                    continue;
                }
                $i++;
            }
            $this->TotalPage = count($this->SplitFields);
            $this->Fields['totalpage'] = $this->TotalPage;
        }

        //处理默认缩略图等
        if (isset($this->Fields['litpic'])) {
            if ($this->Fields['litpic'] == '-' || $this->Fields['litpic'] == '') {
                $this->Fields['litpic'] = $GLOBALS['cfg_cmspath'] . '/images/defaultpic.gif';
            }
            if (!preg_match("#^http:\/\/#i", $this->Fields['litpic']) && $GLOBALS['cfg_multi_site'] == 'Y') {
                $this->Fields['litpic'] = $GLOBALS['cfg_mainsite'] . $this->Fields['litpic'];
            }
            $this->Fields['picname'] = $this->Fields['litpic'];

            //模板里直接使用{dede:field name='image'/}获取缩略图
            $this->Fields['image'] = (!preg_match('/jpg|gif|png/i', $this->Fields['picname']) ? '' : "<img src='{$this->Fields['picname']}' />");
        }
        // 处理投票选项
        if (isset($this->Fields['voteid']) && !empty($this->Fields['voteid'])) {
            $this->Fields['vote'] = '';
            $voteid = $this->Fields['voteid'];
            $this->Fields['vote'] = "<script language='javascript' src='{$GLOBALS['cfg_cmspath']}/data/vote/vote_{$voteid}.js'></script>";
            if ($GLOBALS['cfg_multi_site'] == 'Y') {
                $this->Fields['vote'] = "<script language='javascript' src='{$GLOBALS['cfg_mainsite']}/data/vote/vote_{$voteid}.js'></script>";
            }
        }

        if (isset($this->Fields['goodpost']) && isset($this->Fields['badpost'])) {
            //digg
            if ($this->Fields['goodpost'] + $this->Fields['badpost'] == 0) {
                $this->Fields['goodper'] = $this->Fields['badper'] = 0;
            } else {
                $this->Fields['goodper'] = number_format($this->Fields['goodpost'] / ($this->Fields['goodpost'] + $this->Fields['badpost']), 3) * 100;
                $this->Fields['badper'] = 100 - $this->Fields['goodper'];
            }
        }
    }

    /**
     *  解析模板，对固定的标记进行初始给值
     * @access    public
     * @return    void
     */
    function ParseTempletsFirst()
    {
        if (empty($this->Fields['keywords'])) {
            $this->Fields['keywords'] = '';
        }

        if (empty($this->Fields['reid'])) {
            $this->Fields['reid'] = 0;
        }

        $GLOBALS['envs']['tags'] = $this->Fields['tags'];

        if (isset($this->TypeLink->TypeInfos['reid'])) {
            $GLOBALS['envs']['reid'] = $this->TypeLink->TypeInfos['reid'];
        }

        $GLOBALS['envs']['keyword'] = $this->Fields['keywords'];

        $GLOBALS['envs']['typeid'] = $this->Fields['typeid'];

        $GLOBALS['envs']['topid'] = GetTopid($this->Fields['typeid']);

        $GLOBALS['envs']['aid'] = $GLOBALS['envs']['id'] = $this->Fields['id'];

        $GLOBALS['envs']['adminid'] = $GLOBALS['envs']['mid'] = isset($this->Fields['mid']) ? $this->Fields['mid'] : 1;

        $GLOBALS['envs']['channelid'] = $this->TypeLink->TypeInfos['channeltype'];

        if ($this->Fields['reid'] > 0) {
            $GLOBALS['envs']['typeid'] = $this->Fields['reid'];
        }

        MakeOneTag($this->dtp, $this, 'N');
    }

    function ReplaceKeyword($kw, $body)
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
}

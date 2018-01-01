<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Archives extends Model
{
    const UPDATED_AT = 'pubdate';

    const CREATED_AT = 'senddate';

    //
    protected $table = 'archives';

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
        $arctype->click = $sysConfig->getAttribute('cfg_arc_click') == -1  ? mt_rand(50, 200) : $sysConfig->getAttribute('cfg_arc_click');
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
        foreach ($parmas as $key => $v){
            $archives->$key = $v;
        }

       return $archives->save() ? $archives : false;

    }

}

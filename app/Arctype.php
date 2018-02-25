<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Arctype extends Model
{
    public $timestamps = false;

    protected $table = 'arctype';

    const PARENT = 'parent';
    const CMSPATH = 'cmspath';
    const BASEPATH = 'basepath';

    public static $referpathList = [
        self::PARENT => '上级目录',
        self::CMSPATH => 'CMS根目录',
        self::BASEPATH => '站点根目录'
    ];

    const LINK_DYNAMIC = '-1';
    const LINK_FIRST = '0';
    const LINK_DEFAULT = '1';

    public static $isdefaultList = [
        self::LINK_DYNAMIC => '使用动态页',
        self::LINK_FIRST => '链接到列表第一页',
        self::LINK_DEFAULT => '链接到默认页'
    ];

    const ISPART_LIST = '0';
    const ISPART_OUT = '1';
    const ISPART_OUTLINK = '2';

    public static $ispartList = [
        self::ISPART_LIST => '最终列表栏目（允许在本栏目发布文档，并生成文档列表）',
        self::ISPART_OUT => '频道封面（栏目本身不允许发布文档）',
        self::ISPART_OUTLINK => '外部连接（在"文件保存目录"处填写网址）'
    ];

    const CROSS_NO = '0';
    const CROSS_NAV = '1';
    const CROSS_NAVID = '2';

    public static $crossList = [
        self::CROSS_NO => '不交叉',
        self::CROSS_NAV => '自动获取同名栏目内容',
        self::CROSS_NAVID => '手工指定交叉栏目ID(用逗号分开)'
    ];

    const NID = 'article';

    //

    /**
     *  读出所有分类,在类目管理页(list_type)中使用
     */
    public static function ListAllType($reid = 0)
    {
        $list = [];
        if (Auth::user()->typeid) {
            $admin = new Admin();
            $list = $admin->rewriteAdminChannel(Auth::user()->typeid);
        }
        $result = self::where('reid', $reid)->orderBy('sortrank')->get();
        if (!empty($list)) {
            foreach ($result as $key => $v) {
                if (!in_array($v->id, $list)) {
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }

    /**
     * 递归构建一个多维数组菜单
     * @param int $reid
     * @return mixed
     */
    public static function listAllTypeArray($reid = 0)
    {
        $re = self::ListAllType($reid);
        foreach ($re as $key => $value) {
            $result = self::listAllTypeArray($value->id);
            if (!empty($result)) {
                $re[$key]->child = $result;
            }
        }
        return $re;
    }

    public static function listAllTypeArrayOne($list = [], $prefix = '')
    {
        $_list = [];
        foreach ($list as $value) {
            $child = $value->child;
            unset($value->child);
            $value->_typename = $prefix . $value->typename;
            $_list[] = $value;
            if ($child) {
                $_list = array_merge($_list, self::listAllTypeArrayOne($child, $prefix . '-'));
            }
        }
        return $_list;
    }


    public static function defalutArctype()
    {
        $arctype = new Arctype();

//        $arctype->id = 0;
        $arctype->issend = 1;
        $arctype->ishidden = 0;
        $arctype->sortrank = 50;
        $arctype->channeltype = 1;
        $arctype->corank = 0;
        $arctype->referpath = self::PARENT;
        $arctype->isdefault = self::LINK_DEFAULT;
        $arctype->cross = self::CROSS_NO;
        $arctype->defaultname = 'index.html';
        $arctype->ispart = 0;
        $arctype->siteurl = '';
//        $arctype->nid = self::NID;


        $arctype->id = 0;
        $arctype->topid = 0;

        $arctype->tempindex = "{style}/index_" . self::NID . ".htm";
        $arctype->templist = "{style}/list_" . self::NID . ".htm";
        $arctype->temparticle = "{style}/article_" . self::NID . ".htm";

        return $arctype;
    }


    public function channelType()
    {
        return $this->hasOne(ChannelType::class, 'id', 'channeltype');
    }


    public static function createNewArctype(Request $request)
    {
//          reid,topid,sortrank,typename,typedir,isdefault,defaultname,issend,channeltype,
//          tempindex,templist,temparticle,modname,namerule,namerule2,ispart,corank,description,
//          keywords,seotitle,moresite,siteurl,sitepath,ishidden,`cross`,`crossid`,`content`,`smalltypes`
        $topid = $request->post('topid') ? $request->post('topid') : 0;
        $reid = $request->post('reid') ? $request->post('reid') : 0;
        if ($topid == 0 && $reid > 0) {
            $topid = $reid;
        }

        $cross = $request->post('cross') ? $request->post('cross') : 0;
        $ispart = $request->post('ispart') ? $request->post('ispart') : 0;
        if (!$ispart) {
            $cross = 0;
        }

        $arctype = new Arctype();
        $arctype->reid = $reid;
        $arctype->topid = $topid;
        $arctype->sortrank = $request->post('sortrank') ? $request->post('sortrank') : 50;
        $arctype->typename = $request->post('typename');
        $arctype->typedir = $request->post('typedir') ? $request->post('typedir') : '';
        $arctype->isdefault = $request->post('isdefault', 0) ? $request->post('isdefault') : 0;
        $arctype->defaultname = $request->post('defaultname') ? $request->post('defaultname') : 'index.html';
        $arctype->issend = $request->post('issend', 1) ? $request->post('issend') : 1;
        $arctype->channeltype = $request->post('channeltype', 1) ? $request->post('channeltype') : 1;
        $arctype->tempindex = $request->post('tempindex') ? $request->post('tempindex') : "{style}/index_" . self::NID . ".htm";
        $arctype->templist = $request->post('templist') ? $request->post('templist') : "{style}/list_" . self::NID . ".htm";
        $arctype->temparticle = $request->post('temparticle') ? $request->post('temparticle') : "{style}/article_" . self::NID . ".htm";
        $arctype->modname = 'default';
        $sysConfig = CfgConfig::sysConfig();
        $arctype->namerule = $request->post('namerule') ? $request->post('namerule') : $sysConfig->cfg_df_namerule;
        $arctype->namerule2 = $request->post('namerule2') ? $request->post('namerule2') : '{typedir}/list_{tid}_{page}.html';
        $arctype->ispart = $ispart;
        $arctype->corank = $request->post('corank') ? $request->post('corank') : 0;
        $arctype->description = $request->post('description') ? $request->post('description') : '';
        $arctype->keywords = $request->post('keywords') ? $request->post('keywords') : '';
        $arctype->seotitle = $request->post('seotitle') ? $request->post('seotitle') : '';
        $arctype->moresite = $request->post('moresite') ? $request->post('moresite') : 0;
        $arctype->siteurl = $request->post('siteurl') ? $request->post('siteurl') : '';
        $arctype->sitepath = $request->post('sitepath') ? $request->post('sitepath') : '';
        $arctype->ishidden = $request->post('ishidden') ? $request->post('ishidden') : 0;
        $arctype->cross = $cross;
        $arctype->crossid = $request->post('crossid') ? $request->post('crossid') : '';
        $arctype->content = $request->post('content') ? $request->post('content') : '';
        $arctype->smalltypes = '';
        $smalltype = $request->post('smalltype') ? $request->post('smalltype') : '';
        if (is_array($smalltype)) {
            $arctype->smalltypes = join(',', $smalltype);
        }

        if ($arctype->ispart != 2) {
            //栏目的参照目录
            $nextdir = $request->post('nextdir');
            if (empty($nextdir)) {
                $sysConfig = CfgConfig::sysConfig();
                $arctype->typedir = '{cmspath}' . $sysConfig->cfg_arcdir;
            }
            $referpath = $request->post('referpath');
            if ($referpath == 'cmspath') $nextdir = '{cmspath}';
            if ($referpath == 'basepath') $nextdir = '';
            //用拼音命名
            if ($request->post('upinyin') == 1 || $arctype->typedir == '') {
                $arctype->typedir = app('pinyin')->abbr(stripslashes($arctype->typename));
            }
            $arctype->typedir = $nextdir . '/' . $arctype->typedir;
            $arctype->typedir = preg_replace("#\/{1,}#", "/", $arctype->typedir);
        }

        self::deleteCacheWithAdminAll(Auth::user());
        self::deleteCacheWithAll();
        return $arctype->save();
    }

    public function updateArctype($request)
    {
        $cross = $request->post('cross') ? $request->post('cross') : 0;
        $ispart = $request->post('ispart') ? $request->post('ispart') : 0;
        if (!$ispart) {
            $cross = 0;
        }
        $this->sortrank = $request->post('sortrank') ? $request->post('sortrank') : 50;
        $this->typename = $request->post('typename');
        if ($request->post('typedir')) {
            $this->typedir = $request->post('typedir');
        }
        $this->isdefault = $request->post('isdefault', 0) ? $request->post('isdefault') : 0;
        $this->defaultname = $request->post('defaultname') ? $request->post('defaultname') : 'index.html';
        $this->issend = $request->post('issend', 1) ? $request->post('issend') : 1;
        $this->channeltype = $request->post('channeltype', 1) ? $request->post('channeltype') : 1;
        $this->tempindex = $request->post('tempindex') ? $request->post('tempindex') : "{style}/index_" . self::NID . ".htm";
        $this->templist = $request->post('templist') ? $request->post('templist') : "{style}/list_" . self::NID . ".htm";
        $this->temparticle = $request->post('temparticle') ? $request->post('temparticle') : "{style}/article_" . self::NID . ".htm";

        $sysConfig = CfgConfig::sysConfig();
        $this->namerule = $request->post('namerule') ? $request->post('namerule') : $sysConfig->cfg_df_namerule;
        $this->namerule2 = $request->post('namerule2') ? $request->post('namerule2') : '{typedir}/list_{tid}_{page}.html';
        $this->ispart = $ispart;
        $this->corank = $request->post('corank') ? $request->post('corank') : 0;
        $this->description = $request->post('description') ? $request->post('description') : '';
        $this->keywords = $request->post('keywords') ? $request->post('keywords') : '';
        $this->seotitle = $request->post('seotitle') ? $request->post('seotitle') : '';
        $this->moresite = $request->post('moresite') ? $request->post('moresite') : 0;

        $this->siteurl = $request->post('siteurl') ? $request->post('siteurl') : '';
        $this->sitepath = $request->post('sitepath') ? $request->post('sitepath') : '';
        $this->ishidden = $request->post('ishidden') ? $request->post('ishidden') : 0;

        $this->cross = $cross;
        $this->crossid = $request->post('crossid') ? $request->post('crossid') : '';
        $this->content = $request->post('content') ? $request->post('content') : '';
        $this->smalltypes = '';
        $smalltype = $request->post('smalltype') ? $request->post('smalltype') : '';
        if (is_array($smalltype)) {
            $this->smalltypes = join(',', $smalltype);
        }
        $result = $this->save();
        if ($result) {

//            如果选择子栏目可投稿，更新顶级栏目为可投稿
            if ($this->topid && $this->issend == 1) {
                self::where('id', $this->topid)->update([
                    'issend' => $this->issend
                ]);
            }

//            寻找所有子栏目
            $idList = [];
            $result = self::getAllWithCache();
            foreach ($result as $id => $value) {
                if (strpos($value['path'] . '', $this->id . '') === 0) {
                    $idList[] = $id;
                }
            }

//            修改顶级栏目时强制修改下级的多站点支持属性
            if ($this->topid == 0 && !empty($idList)) {
                self::whereIn('id', $idList)->update([
                    'moresite' => $this->moresite,
                    'siteurl' => $this->siteurl,
                    'sitepath' => $this->sitepath,
                    'ishidden' => $this->ishidden,
                ]);
            }

//            更改子栏目属性
            if ($request->post('upnext') && !empty($idList)) {
                self::whereIn('id', $idList)->update([
                    'issend' => $this->issend,
                    'defaultname' => $this->defaultname,
                    'channeltype' => $this->channeltype,
                    'tempindex' => $this->tempindex,
                    'templist' => $this->templist,
                    'temparticle' => $this->temparticle,
                    'namerule' => $this->namerule,
                    'namerule2' => $this->namerule2,
                    'ishidden' => $this->ishidden,
                ]);
            }
        }
        return $result;
    }

    public static function getCacheKey($key = 'all')
    {
        return 'arctype_' . $key;
    }

    public static function getAllWithCache()
    {
        $key = self::getCacheKey();
        $result = Cache::get($key);
        if (empty($result)) {
            $result = self::orderBy('reid', 'asc')->get();
            $data = [];
            foreach ($result as $value) {
                $data[$value->id] = [
                    'reid' => $value->reid,
                    'path' => $value->reid ? $data[$value->reid]['path'] . '_' . $value->id : $value->id,
                    'channeltype' => $value->channeltype,
                    'issend' => $value->issend,
                    'typename' => $value->typename
                ];
            }
            if (empty($result)) {
                $data2 = '#';
            } else {
                $data2 = json_encode($data);
            }
            Cache::forever($key, $data2);
        } elseif ($result == '#') {
            $data = [];
        } else {
            $data = json_decode($result, 1);
        }
        return $data;
    }

    public static function getTopIdWithCache($tid)
    {
        $cfg_Cs = self::getAllWithCache();
        if (!isset($cfg_Cs[$tid]['reid']) || $cfg_Cs[$tid]['reid'] == 0) {
            return $tid;
        } else {
            return self::getTopIdWithCache($cfg_Cs[$tid]['reid']);
        }
    }

    public static function getChildAllWithCache($id)
    {
        $list = [];
//        self::deleteCacheWithAll();
        $all = self::getAllWithCache();
        if (!empty($all)) {
            $path = $all[$id]['path'];
            foreach ($all as $id => $v) {
                if (strpos($v['path'] . '', $path . '') === 0) {
                    $list[$id] = $v;
                }
            }
        }
        return $list;
    }

    public static function getAdminAllWithCache($user)
    {
        $key = self::getCacheKey('admin_' . $user->id);
        $result = Cache::get($key);
        if (empty($result)) {
            $result = self::getAllWithCache();
            $typeId = $user->typeid;
            $list = [];
            if (empty($typeId) || $user->usertype >= 10) {
                $lsit = $result;
            } else {
                $typeIds = explode(',', $typeId);
                foreach ($typeIds as $typeId) {
                    $list[$typeId] = $result[$typeId];
                }
            }
            $data = json_encode($lsit);
            Cache::forever($key, $data);
        } else {
            $data = json_decode($result, 1);
        }
        return $data;
    }

    public static function deleteCacheWithAll()
    {
        $key = self::getCacheKey();
        Cache::forget($key);
    }

    public static function deleteCacheWithAdminAll($user)
    {
        $key = self::getCacheKey('admin_' . $user->id);
        Cache::forget($key);
    }

    public static function deleteArctype($id)
    {
        $arctype = Arctype::where('id', $id)->first();
        $result = self::getChildAllWithCache($id);
        $addtable = $arctype->channelType->addtable;
        if ($addtable) {
            $addtable = new Expression($addtable);
        }
        foreach ($result as $cid => $value) {
            Arctype::where('id', $cid)->delete();
            Archives::where('typeid', $cid)->delete();
            Arctiny::where('typeid', $cid)->delete();
            Feedback::where('typeid', $cid)->delete();

            Spec::where('typeid', $cid)->delete();
//            Schema::hasTable(strtolower(Spec::class)) && Spec::where('typeid', $cid)->delete();

            if ($addtable) {
                DB::table($addtable)->where('typeid', $cid)->delete();
            }

            //删除目录和目录里的所有文件 ### 禁止了此功能
            //删除单独页面
//            if($myrow['ispart']==2 && $myrow['typedir']=='')
//            {
//                if( is_file($this->baseDir.'/'.$myrow['defaultname']) )
//                {
//                    @unlink($this->baseDir.'/'.$myrow['defaultname']);
//                }
//            }

            //清空缓存
            Cache::flush();
        }
        return true;
    }


}

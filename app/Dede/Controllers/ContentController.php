<?php

namespace App\Dede\Controllers;

use App\Arcatt;
use App\Archives;
use App\Arcrank;
use App\Arctype;
use App\CfgConfig;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ContentController extends Controller
{
    //
    public function index(Request $request)
    {
        $list = Archives::searchFilter($request);
        $list->withPath(route('admin.content.list'));
        $arrayOne = Arctype::listAllTypeArrayOne(Arctype::listAllTypeArray());
        return view('admin.content.index', [
            'navListArrayOne' => $arrayOne,
            'sortTypeList' => Archives::$sortTypeList,
            'ismakeTypeList' => Archives::$ismakeTypeList,
            'arcattList' => Arcatt::all(),
            'list' => $list
        ]);
    }

    public function pageList(Request $request){
        $rule = [
            'cid' => 'numeric',
            'channelid' => 'numeric',
            'keyword' => 'string',
            'arcrank' => 'string',
            'orderby' => 'string',
        ];
        $this->validate($request, $rule);
        $list = Archives::searchFilter($request);
        $list->withPath(route('admin.content.list'));
        return view('admin.content.line', [
            'list' => $list
        ]);
    }

    public function add()
    {
        $arcRankList = Arcrank::where('adminrank', '<=', Auth::user()->usertype)->get();
        $sysConfig = CfgConfig::sysConfig();
        return view('admin.content.add', [
            'action' => url()->current(),
            'navListArrayOne' => Arctype::listAllTypeArrayOne(Arctype::listAllTypeArray()),
            'arcattList' => Arcatt::all(),
            'sortArticleList' => Archives::$sortArticleList,
            'isHtmlList' => Archives::$isHtmlList,
            'arcRankList' => $arcRankList,
            'sysConfig' =>$sysConfig
        ]);
    }

    public function create(Request $request)
    {
        $sysConfig = CfgConfig::sysConfig();
        if ($request->isMethod('post')) {
            $rule = [
                'typeid' => 'required|numeric|exists:channeltype,id',
                'title' => 'required|string',
                'shorttitle' => 'string',
                'keywords' => 'string',
                'body' => 'string',
                'description' => 'string',
                'tags' => 'string',
                'picname' => 'string',
                'filename' => 'string',
                'templet' => 'string',
                'litpic' => 'string',
                'redirecturl' => 'string|url',
                'channelid' => 'required|numeric|exists:channeltype,id',
                'flags' => 'string',
                'typeid2' => 'string',
                'autokey' => 'string',
                'remote' => 'string',
                'dellink' => 'string',
                'autolitpic' => 'string',
                'sptype' => 'string',
                'click' => 'string',
                'color' => 'string',
                'pubdate' => 'string',
                'voteid' => 'numeric',
                'writer' => 'string',
                'source' => 'numeric',
                'ishtml' => 'numeric',
                'weight' => 'numeric',
                'spsize' => 'numeric',
                'sortup' => 'numeric',
                'arcrank' => 'numeric',
                'money' => 'numeric',
                'notpost' => 'numeric'
            ];
            $this->validate($request, $rule);
            $result = Archives::createNewArctype($request);
            if (!$result) {
                return \Redirect::back()->withErrors("保存目录数据时失败，请检查你的输入资料是否存在问题！");
            }
            return redirect(route('admin.catalog'));
        }
        return redirect(route('admin.catalog.create'));
    }

    public function edit(Arctype $arctype)
    {
//        dd($arctype);
        $sysConfig = CfgConfig::sysConfig();
        $parentArctype = $arctype->topid ? Arctype::where('id', $arctype->topid)->first() : Arctype::defalutArctype();
        $arctype->typedir = preg_replace("/\/{1,}/", '/', $arctype->typedir);
        $channelTypeList = ChannelType::getShowAll();
        $list = [];
        foreach ($channelTypeList as $k => $v) {
            $list[] = $v->typename . '(' . $v->nid . ')';
        }
//        dd($parentArctype);
        return view('admin.catalog.edit', [
            'action' => url()->current(),
            'channelTypeList' => $channelTypeList,
            'channelTypeListString' => implode('、', $list),
            'referpathList' => Arctype::$referpathList,
            'isdefaultList' => Arctype::$isdefaultList,
            'ispartList' => Arctype::$ispartList,
            'crossList' => Arctype::$crossList,
            'sysEnumInfoTypeList' => SysEnum::getInfoTypeAll(),
            'corankList' => Arcrank::getShowAll(),
            'arctype' => $arctype,
            'parentArctype' => $parentArctype,
            'sysConfig' => $sysConfig,
        ]);
    }

    public function update(Arctype $arctype, Request $request)
    {
        if ($request->isMethod('post')) {
            $rule = [
                'typename' => 'required|string|between:1,20',
                'typedir' => 'string|nullable|between:1,100',
                'sortrank' => 'numeric',
                'isdefault' => 'digits_between:0,1',
                'issend' => 'digits_between:0,1',
                'channeltype' => 'numeric|exists:channeltype,id',
                'corank' => 'numeric|exists:arcrank,rank',
                'moresite' => 'digits_between:0,1',
                'cross' => 'digits_between:0,2',
                'ispart' => 'digits_between:0,2',
                'ishidden' => 'digits_between:0,1'
            ];
            $this->validate($request, $rule);
            $result = $arctype->updateArctype($request);
            if (!$result) {
                return \Redirect::back()->withErrors("保存目录数据时失败，请检查你的输入资料是否存在问题！");
            }
            return redirect(route('admin.catalog'));
        }
        return redirect(route('admin.catalog.update'));
    }


}

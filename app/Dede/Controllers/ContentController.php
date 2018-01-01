<?php

namespace App\Dede\Controllers;

use App\Arcatt;
use App\Archives;
use App\Arcrank;
use App\Arctiny;
use App\Arctype;
use App\CfgConfig;
use App\ChannelType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helpers\HttpRequest;

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

    public function pageList(Request $request)
    {
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
//        dd(HttpRequest::get('http://www.dilidili.wang/uploads/allimg/171202/290_1527337801.jpg'));
        $arcRankList = Arcrank::where('adminrank', '<=', Auth::user()->usertype)->get();
        $sysConfig = CfgConfig::sysConfig();
        $archives = Archives::defalutArchives();
        return view('admin.content.add', [
            'action' => url()->current(),
            'navListArrayOne' => Arctype::listAllTypeArrayOne(Arctype::listAllTypeArray()),
            'arcattList' => Arcatt::all(),
            'sortArticleList' => Archives::$sortArticleList,
            'isHtmlList' => Archives::$isHtmlList,
            'arcRankList' => $arcRankList,
            'archives' => $archives,
            'content' => '',
            'sysConfig' => $sysConfig
        ]);
    }

    public function create(Request $request)
    {
        dd($request);
        $sysConfig = CfgConfig::sysConfig();
        if ($request->isMethod('post')) {
            $rule = [
                'title' => 'required|string',
                'shorttitle' => 'string|nullable|max:36',
                'flags[]' => 'string|nullable',
                'redirecturl' => 'string|nullable|url',
                'tags' => 'string|nullable',
                'weight' => 'numeric',

                'picname' => 'string|nullable',
                'isremote' => 'numeric|nullable',
                'source' => 'string|nullable|max:30',
                'writer' => 'string|nullable|max:30',
                'typeid' => 'required|numeric|exists:arctype,id',
                'typeid2' => 'string|nullable',

                'keywords' => 'string|nullable|max:60',

                'description' => 'string|nullable|max:' . $sysConfig->cfg_auot_description,
                'autokey' => 'string|nullable',

                'remote' => 'string|nullable',
                'dellink' => 'string|nullable',
                'autolitpic' => 'string|nullable',
                'needwatermark' => 'numeric',

                'sptype' => 'string|nullable',
                'spsize' => 'numeric',
                'body' => 'string|nullable',

                'voteid' => 'numeric|nullable',

                'notpost' => 'numeric',
                'click' => 'string|nullable|max:7',
                'sortup' => 'numeric',
                'color' => 'string|nullable',
                'arcrank' => 'numeric',
                'money' => 'numeric',
                'pubdate' => 'string|nullable',
                'ishtml' => 'numeric',

                'filename' => 'string|nullable|max:40',
                'templet' => 'string|nullable',
                'litpic' => 'string|nullable',

            ];
            $this->validate($request, $rule);
//            处理水印
            $arctiny = Arctiny::createNewArctiny($request);
            if (!$arctiny->save()) {
                return \Redirect::back()->withErrors("无法获得主键，因此无法进行后续操作！");
            }

            $request->offsetSet('id', $arctiny->id);
            $archives = Archives::createNewArctype($request);
            if (!$archives) {
                $arctiny->delete();
                return \Redirect::back()->withErrors("保存目录数据时失败，请检查你的输入资料是否存在问题！");
            }

            $channelType = ChannelType::where('id', $request->post('channelid'));
            if(empty($channelType->addtable)){
                $arctiny->delete();
                $archives->delete();
                return \Redirect::back()->withErrors("没找到当前模型的主表信息，无法完成操作！。");
            }

            $tableName = str_replace('', '', $channelType->addtable);
            $tableName = ucfirst($tableName);
            $obj = $tableName::createNew($request);
            if(empty($obj)){
                $arctiny->delete();
                $archives->delete();
                return \Redirect::back()->withErrors("把数据保存到数据库附加表。");
            }
            return redirect(route('admin.content'));
        }

        return redirect(route('admin.content.create'));
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
            'content' => '',
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
            return redirect(route('admin.content'));
        }
        return redirect(route('admin.content.update'));
    }


}

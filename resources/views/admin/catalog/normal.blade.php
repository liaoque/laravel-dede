<div class="box-body">
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">是否支持投稿：</label>
        <div class="col-sm-2 col-sm-10">
            <div class="radio">
                <label>
                    @if($arctype->issend == 0)
                        <input type="radio" name="issend" value="0" checked>不支持
                    @else
                        <input type="radio" name="issend" value="0">不支持
                    @endif
                </label>
            </div>
        </div>
        <div class="col-sm-2 col-sm-10">
            <div class="radio">
                <label>
                    @if($arctype->issend == 1)
                        <input type="radio" name="issend" value="1" checked>支持
                    @else
                        <input type="radio" name="issend" value="1">支持
                    @endif
                </label>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">是否隐藏栏目：</label>
        <div class="col-sm-2 col-sm-10">
            <div class="radio">
                <label>
                    @if($arctype->ishidden == 0)
                        <input type="radio" name="ishidden" checked="checked" value="0">显示
                    @else
                        <input type="radio" name="ishidden" value="0">显示
                    @endif
                </label>
            </div>
        </div>
        <div class="col-sm-2 col-sm-10">
            <div class="radio">
                <label>
                    @if($arctype->ishidden == 1)
                        <input type="radio" name="ishidden" checked="checked" value="1">隐藏
                    @else
                        <input type="radio" name="ishidden" value="1">隐藏
                    @endif
                </label>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">内容模型：</label>
        <div class="col-sm-10">
            <select class="form-control" name="channeltype">
                @foreach($channelTypeList as $key => $value)
                    @if($value->id == $arctype->channelType->id)
                        <option value="{{$value->id}}" selected>{{$value->typename}}|{{$value->nid}}</option>
                    @else
                        <option value="{{$value->id}}">{{$value->typename}}|{{$value->nid}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">栏目名称：</label>
        <div class="col-sm-10">
            <input type="text" name="typename" value="{{$arctype->typename}}" class="form-control" placeholder="栏目名称">
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">排列顺序：</label>
        <div class="col-sm-10">
            <input type="text" name="sortrank" class="form-control" value="{{$arctype->sortrank}}"
                   placeholder="">
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">浏览权限：</label>
        <div class="col-sm-10">
            <select class="form-control" name="corank">
                @foreach($corankList as $key => $value)
                    @if($value->rank == $arctype->corank)
                        <option value="{{$value->rank}}" selected>{{$value->membername}}</option>
                    @else
                        <option value="{{$value->rank}}">{{$value->membername}}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
    @if(!$arctype->id)
        <div class="form-group">
            <label for="" class="col-sm-2 control-label">上级目录：</label>
            <div class="col-sm-10">
                <p class="form-control">{{$parentArctype->typedir}}</p>
                <input name="nextdir" type="hidden" value="{{$parentArctype->typedir}}">
            </div>
        </div>
    @endif

    <div class="form-group">
        <label for="" class="col-sm-2 control-label">文件保存目录：</label>
        <div class="col-sm-10">
            @if(!$arctype->id)
                <div class="input-group ">
                <span class="input-group-addon">
                        <input name="upinyin" type="checkbox" id="upinyin" class="np" value="1"
                               onClick="CheckTypeDir()"/>
                        <label for="upinyin" style="font-weight: normal">拼音</label>
                </span>
                    <input name="typedir" value="{{$arctype->typedir}}" type="text" class="form-control" id="typedir"/>
                </div>
            @else
                <input name="typedir" value="{{$arctype->typedir}}" type="text" class="form-control" id="typedir"/>
            @endif
        </div>
    </div>
    @if(!$arctype->id)
        <div class="form-group">
            <label for="" class="col-sm-2 control-label">目录相对位置：</label>
            @foreach($referpathList as $key => $value)
                <div class="col-sm-2 col-sm-10">
                    <div class="radio">
                        <label>
                            @if($key == 'parent')
                                <input type="radio" name="referpath" checked="checked"
                                       value="{{$key}}">{{$value}}
                            @else
                                <input type="radio" name="referpath" value="{{$key}}">{{$value}}
                            @endif
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">栏目列表选项：</label>
        @foreach($isdefaultList as $key => $value)
            <div class="col-sm-2 col-sm-10">
                <div class="radio">
                    <label>
                        @if($arctype->isdefault == $key)
                            <input type="radio" name="isdefault" checked="checked"
                                   value="{{$key}}">{{$value}}
                        @else
                            <input type="radio" name="isdefault" value="{{$key}}">{{$value}}
                        @endif
                    </label>
                </div>
            </div>
        @endforeach
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">默认页的名称：</label>
        <div class="col-sm-10">
            <input name="defaultname" type="text" value="{{$arctype->defaultname}}" class="form-control"/>
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">排列顺序：</label>
        @foreach($ispartList as $key => $value)
            <div class="col-sm-2 col-sm-10">
                <div class="radio">
                    <label>
                        @if($arctype->ispart == $key)
                            <input type="radio" name="ispart" checked="checked"
                                   value="{{$key}}">{{$value}}
                        @else
                            <input type="radio" name="ispart" value="{{$key}}">{{$value}}
                        @endif
                    </label>
                </div>
            </div>
        @endforeach

    </div>
    <div class="form-group" id="helpvarco" style="display: none">
        <label for="" class="col-sm-2 control-label">栏目交叉说明：</label>
        <div class="col-sm-10 col-sm-10">
            <p class="help-block">
                交叉栏目是指一个大栏目与另一个非下级的子栏目出现交叉的情况，相当于系统原来的副栏目功能，不过现在改在栏目里预先设置好。<br>例如：
                网站上有大栏目——智能手机、音乐手机，另外又有栏目——诺基亚-&gt;智能手机、诺基亚-&gt;音乐手机，这样顶级的大栏目就和另一个大栏目的子栏目形成了交叉，这样只需要在大栏目中指定交叉的栏目即可。
                <br>注：会自动索引交叉栏目的内容，但不会索引交叉栏目下级栏目的内容，这种应用也适用于按地区划分资讯的站点。
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="" class="col-sm-2 control-label">栏目交叉<a class="btn btn-box-tool" onclick="ShowHide('helpvarco')"><i
                        class="fa fa-fw fa-question-circle"></i></a>：<br/>仅适用[最终列表栏目]</label>
        @foreach($crossList as $key => $value)
            <div class="col-sm-2 col-sm-10">
                <div class="radio">
                    <label>
                        @if($arctype->cross == $key)
                            <input type="radio" name="cross" checked="checked"
                                   value="{{$key}}" onclick="CheckCross(this)">{{$value}}
                        @else
                            <input type="radio" name="cross" value="{{$key}}" onclick="CheckCross(this)">{{$value}}
                        @endif
                    </label>
                </div>
            </div>
        @endforeach

        <div class="col-sm-offset-2 col-sm-10">
            @if($arctype->cross == \App\Arctype::CROSS_NAVID)
                <textarea class="form-control" name="crossid" cols="50" rows="3" id="crossid" style="display: block;"
                          class="alltxt"></textarea>
            @else
                <textarea class="form-control" name="crossid" cols="50" rows="3" id="crossid" style="display: none;"
                          class="alltxt"></textarea>
            @endif
        </div>
    </div>

    @if($arctype->channelType->id < 0)
        <div class="form-group">
            <label for="" class="col-sm-2 control-label">绑定小分类： <br/>仅适用[分类信息模型]</label>
            <div class=" col-sm-10">
                <select class="form-control" name="smalltype[]" size='5' multiple='yes'>
                    @foreach($sysEnumInfoTypeList as $key => $value)
                        @if($value->evalue%500 ==0)
                            <option value="{{$value->evalue}}">{{$value->ename}}</option>
                        @elseif(preg_match("#\.#", $value->evalue))
                            <option value="{{$value->evalue}}"> └───{{$value->ename}}</option>
                        @else
                            <option value="{{$value->evalue}}"> └───{{$value->ename}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
    @endif

</div>


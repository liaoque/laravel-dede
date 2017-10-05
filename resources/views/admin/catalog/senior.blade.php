<div class="box-body">
    @if($parentArctype->id > 0)
        <input type='hidden' name='moresite' value='{{$arctype->channelType->moresite}}'/>
        <input type='hidden' name='siteurl' value='{{$arctype->channelType->siteurl}}'/>
        <input type='hidden' name='sitepath' value='{{$arctype->channelType->sitepath}}'/>
    @elseif($parentArctype->id == 0)
        <div class="form-group">
            <label for="" class="col-sm-2 control-label">多站点支持：</label>
            <div class="col-sm-2 col-sm-10">
                <div class="radio">
                    <label>
                        @if($arctype->moresite == 0)
                            <input type="radio" name="moresite" value="0" checked>不启用
                        @else
                            <input type="radio" name="moresite" value="0">不启用
                        @endif
                    </label>
                </div>
            </div>
            <div class="col-sm-2 col-sm-10">
                <div class="radio">
                    <label>
                        @if($arctype->moresite == 1)
                            <input type="radio" name="moresite" value="1" checked>启用
                        @else
                            <input type="radio" name="moresite" value="1">启用
                        @endif
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-2 control-label">说明：</label>
            <div class="col-sm-10">
                <p class="help-block">如果需要绑定二级域名，必须在“系统参数”中设定“支持多站点”。</p>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-2 control-label">站点根目录：</label>
            <div class="col-sm-10">
                <input name="siteurl" type="text" id="siteurl" size="35" onChange="CheckPathSet();"
                       value="{{$arctype->siteurl}}" class="form-control" placeholder="(需加 http://，一级或二级域名的根网址)"/>
            </div>
        </div>
        <div class="form-group">
            <label for="" class="col-sm-2 control-label">绑定域名：</label>
            <div class="col-sm-10">
                <p class="help-block">为简化操作，站点根目录与当前栏目目录一致，请注意当前栏目文件保存目录的设置，域名需自行手工绑定到这个目录。</p>
            </div>
        </div>
    @endif


    <div class="form-group" id="helpvar1" style="display: none">
        <label for="" class="col-sm-2 control-label">支持变量：</label>
        <div class="col-sm-10">
            <p class="help-block">{cid}表示频道模型的'名字ID'（{{$channelTypeListString}}）模板文件的默认位置是放在模板目录
                "cms安装目录{{$sysConfig->cfg_templets_dir}}" 内。</p>
            <input type='hidden' value='{style}' name='dfstyle' class="pubinputs"/>
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">规则选项：</label>
        <div class="col-sm-10">
            <p class="help-block">按不同的内容类型设定相关模板及命名规则。</p>
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">封面模板<a class="btn btn-box-tool" onclick="ShowHide('helpvar1')"><i
                        class="fa fa-fw fa-question-circle"></i></a>：</label>
        <div class="col-sm-10">
            <div class="input-group ">
                <input name="tempindex" type="text" value="{{$arctype->tempindex}}" class="form-control"/>
                <span class="input-group-btn">
                    <button type="button" name="set1" class="btn btn-info">浏览..</button>
                </span>
            </div>

        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">列表模板：</label>
        <div class="col-sm-10 ">
            <div class="input-group ">
                <input name="templist" type="text" value="{{$arctype->templist}}" class="form-control"/>
              <span class="input-group-btn">
                <button type="button" name="set1" class="btn btn-info">浏览..</button>
              </span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">文章模板：</label>
        <div class="col-sm-10">
            <div class="input-group ">
                <input name="temparticle" type="text" value="{{$arctype->temparticle}}"
                       class="form-control"/>
              <span class="input-group-btn">
                <button type="button" name="set1" class="btn btn-info">浏览..</button>
              </span>
            </div>
        </div>
    </div>
    <div class="form-group" id="helpvar2" style="display: none">
        <label for="" class="col-sm-2 control-label">支持变量：</label>
        <div class="col-sm-10">
            <p class="help-block">
                {Y}、{M}、{D} 年月日<br/>
                {timestamp} INT类型的UNIX时间戳<br/>
                {aid} 文章ID<br/>
                {pinyin} 拼音+文章ID<br/>
                {py} 拼音部首+文章ID<br/>
                {typedir} 栏目目录 <br/>
                {cc} 日期+ID混编后用转换为适合的字母 <br/>
            </p>
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">文章命名规则<a class="btn btn-box-tool" onclick="ShowHide('helpvar2')"><i
                        class="fa fa-fw fa-question-circle"></i></a>：</label>
        <div class="col-sm-10">
            <input name="namerule" type="text" value="{{$sysConfig->cfg_df_namerule}}" class="form-control">
        </div>
    </div>
    <div class="form-group" id="helpvar3" style="display: none">
        <label for="" class="col-sm-2 control-label">支持变量：</label>
        <div class="col-sm-10">
            <p class="help-block">
                {page} 列表的页码
            </p>
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">列表命名规则<a class="btn btn-box-tool" onclick="ShowHide('helpvar3')"><i
                        class="fa fa-fw fa-question-circle"></i></a>：</label>
        <div class="col-sm-10">
            <input name="namerule2" type="text" value="{typedir}/list_{tid}_{page}.html" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">SEO标题：</label>
        <div class="col-sm-10">
            <input name="seotitle" type="text" value="" class="form-control"
                   placeholder="(栏目模板里用{dede:field.seotitle /}调用)">
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">关键字：</label>
        <div class="col-sm-10">
            <input name="keywords" type="text" value="" class="form-control">
        </div>
    </div>
    <div class="form-group">
        <label for="" class="col-sm-2 control-label">栏目描述：</label>
        <div class="col-sm-10">
            <textarea name="description" cols="70" rows="4" class="form-control"></textarea>
        </div>
    </div>

        @if(!$arctype->id)
            <div class="form-group">
                <label for="" class="col-sm-2 control-label">目录相对位置：</label>
                    <div class="col-sm-2 col-sm-10">
                        <div class="checkbox">
                            <label>
                                <input name="upnext" type="checkbox" id="upnext" value="1" class="np">
                                同时更改下级栏目的浏览权限、内容类型、模板风格、命名规则等通用属性
                            </label>
                        </div>
                    </div>
            </div>
        @endif


</div>
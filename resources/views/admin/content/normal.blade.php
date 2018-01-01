<div class="box-body">
    <div class="form-group">
        <label for="exampleInputEmail1title">文章标题：</label>
        <input name="title" class="form-control" value="{{$archives->title}}" id="exampleInputEmail1title"
               placeholder="文章标题">
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1shorttitle">简略标题：</label>
        <input name="shorttitle" class="form-control" value="{{$archives->shorttitle}}"
               id="exampleInputEmail1shorttitle"
               placeholder="简略标题">
    </div>

    <div class="form-group attr-flag">
        <label for="exampleInputEmail1">自定义属性：</label>
        @foreach($arcattList as $value)
            <label>
                @if(preg_match("/".$value->att."/", $archives->flag))
                    <input type="checkbox" name="flags[]" value="{{$value->att}}" checked="checked"> {{$value->attname}}
                    [{{$value->att}}]
                @else
                    <input type="checkbox" name="flags[]" value="{{$value->att}}"> {{$value->attname}}[{{$value->att}}]
                @endif
            </label>
        @endforeach
    </div>

    <div class="form-group redirecturl-row" style="display: none">
        <label for="exampleInputEmail1redirecturl">跳转网址：</label>
        <input name="redirecturl" class="form-control" value="{{$archives->redirecturl}}"
               id="exampleInputEmail1redirecturl">
    </div>


    <div class="form-group">
        <label for="exampleInputEmail1tags">TAG标签(','号分开，单个标签小于12字节)：</label>
        <input name="tags" class="form-control" id="exampleInputEmail1tags" value="{{$archives->tags}}"
               placeholder="(','号分开，单个标签小于12字节)">
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1weight">权重(越小越靠前)：</label>
        <input name="weight" class="form-control" id="exampleInputEmail1weight" value="{{$archives->weight}}"
               placeholder="权重">
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1picname">缩略图：</label>
        <div class="input-group input-group-sm">
            <input name="picname" type="text" class="form-control picname" value="{{$archives->litpic}}"/>
            <span class="input-group-btn">
              <button type="button" class="btn btn-default btn-flat btn-cropper">本地上传</button>
              <button type="button" class="btn btn-default btn-flat">站内选择(没做)</button>
              <button type="button" class="btn btn-default btn-flat btn-cropper2">裁剪</button>
            </span>
        </div>
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1source">文章来源：</label>
        <input name="source" class="form-control" id="exampleInputEmail1source" value="{{$archives->source}}"
               placeholder="文章来源">
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1writer">作者：</label>
        <input name="writer" class="form-control" id="exampleInputEmail1writer" value="{{$archives->writer}}"
               placeholder="作者">
    </div>

    <div class="form-group">
        <label>文章主栏目：</label>
        <select name="typeid" class="form-control">
            <option value="0">请选择栏目</option>
            @foreach($navListArrayOne as $value)
                @if($archives->typeid ==$value->id )
                    <option value="{{$value->id}}" selected>{{$value->_typename}}</option>
                @else
                    <option value="{{$value->id}}">{{$value->_typename}}</option>
                @endif
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1keywords">关键字：</label>
        <input name="keywords" class="form-control" id="exampleInputEmail1keywords" value="{{$archives->keywords}}"
               placeholder="关键字">
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1description">内容摘要：</label>
        <textarea name="description" type="text" class="form-control">
            {{$archives->description}}
        </textarea>
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1">附加选项：</label>
        <label>
            @if($sysConfig->cfg_rm_remote == 'Y')
                <input name="remote" type="checkbox" value="1" checked="checked"/>下载远程图片和资源
            @else
                <input name="remote" type="checkbox" value="0"/>下载远程图片和资源
            @endif
        </label>

        <label>
            @if($sysConfig->cfg_arc_dellink == 'Y')
                <input name="dellink" type="checkbox" value="1" checked="checked"/>删除非站内链接
            @else
                <input name="dellink" type="checkbox" value="0"/>删除非站内链接
            @endif
        </label>

        <label>
            @if($sysConfig->cfg_arc_autopic == 'Y')
                <input name="autolitpic" type="checkbox" value="1" checked="checked"/>提取第一个图片为缩略图
            @else
                <input name="autolitpic" type="checkbox" value="0"/>提取第一个图片为缩略图
            @endif
        </label>

        <label>
            @if($sysConfig->photo_markup == 'Y')
                <input name="needwatermark" type="checkbox" value="1" checked="checked"/>图片是否加水印
            @else
                <input name="needwatermark" type="checkbox" value="0"/>图片是否加水印
            @endif
        </label>
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1">分页方式：</label>
        <label>
            @if($sysConfig->cfg_arcautosp == 'Y')
                <input name="sptype" type="radio" value="auto"/>自动
                <input name="sptype" type="radio" value="hand" checked="checked"/>手动
            @else
                <input name="sptype" type="radio" value="auto" checked="checked"/>自动
                <input name="sptype" type="radio" value="hand"/>手动
            @endif
        </label>
    </div>

    <div class="form-group">
        <label for="exampleInputEmail1">文章内容：</label>
        @include('admin.content.uedit')
    </div>
</div>



@foreach ($listNav as $nav)
    <li class="treeview">
        <a href="#" style="font-weight:normal;">
            {{$nav->typename}}[ID:{{$nav->id}}](文档：0)
            @if ($nav->ishidden == 1)
                <span class="text-green">[隐]</span>
            @endif

            <span class="pull-right-container" style="position: static;    padding: 6px 6px;">
              <i class="fa fa-angle-left" style="position: static;"></i>
            </span>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool">
                    <i class="fa fa-eye" title="预览"></i>
                </button>
                <button type="button" class="btn btn-box-tool">
                    <i class="fa fa-book" title="内容"></i>
                </button>
                <button type="button" class="btn btn-box-tool webhaeder"
                        data-href="{{route('admin.catalog.create', ['arctype' => $nav->id])}}">
                    <i class="fa fa-plus" title="增加子类"></i>
                </button>
                <button type="button" class="btn btn-box-tool webhaeder"
                        data-href="{{route('admin.catalog.update', ['arctype' => $nav->id])}}">
                    <i class="fa fa-edit" title="更改"></i>
                </button>
                <button type="button" class="btn btn-box-tool" onclick="moveShow(event, this)"
                        data-channeltype="{{$nav->channeltype}}" data-title="{{$nav->typename}}" data-id="{{$nav->id}}"
                        data-target="#modal-moving">
                    <i class="fa fa-share" title="移动"></i>
                </button>
                <button type="button" class="btn btn-box-tool">
                    <i class="fa fa-trash-o" title="删除"></i>
                </button>
                <input type="text" class=" " value="{{$nav->sortrank}}"
                       style="width: 30px;text-align: center;" placeholder="排序">
            </div>

        </a>
        <ul class="treeview-menu">
            @include('admin.catalog.line', ['listNav' => \App\Arctype::ListAllType($nav->id)])
        </ul>

    </li>
@endforeach




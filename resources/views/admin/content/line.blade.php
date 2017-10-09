<div class="table-responsive mailbox-messages">
    <table class="table table-hover table-striped">
        <thead>
        <tr>
            <th>id</th>
            <th>标题</th>
            <th>更新时间</th>
            <th>点击</th>
            <th>HTML</th>
            <th>权限</th>
            <th>作者</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($list as $item)
            <tr>
                <td><input type="checkbox" name="arcid[]" value="{{$item->id}}">&nbsp;&nbsp;{{$item->id}}</td>
                <td>[{{$item->arctype->typename}}]&nbsp;&nbsp;{{$item->title}}</td>
                <td>{{$item->senddate}}</td>
                <td>{{$item->click}}</td>
                <td>{{\App\Archives::isMakeTypeText($item->ismake)}}</td>
                <td>{{$item->arcrank}}</td>
                <td>{{$item->member->uname}}</td>
                <td>
                    <button type="button" class="btn btn-box-tool " style="padding-top: 0;padding-bottom: 0;">
                        <i class="fa fa-eye" title="预览"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool webhaeder"
                            style="padding-top: 0;padding-bottom: 0;"
                            data-href="{{route('admin.catalog.update', ['arctype' => $item->id])}}">
                        <i class="fa fa-edit" title="更改"></i>
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <!-- /.table -->
</div>
<div class="box-footer no-padding">
    <div class="mailbox-controls">
        <!-- Check all button -->
        <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i
                    class="fa fa-square-o"></i>
        </button>
        <!-- /.btn-group -->
        <button type="button" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i></button>
        <span>
                                {{($list->currentPage() - 1)*$list->count() + 1}}-{{($list->currentPage())*$list->count
                            ()}}/{{$list->total()}}
                            </span>
        <div class="pull-right">
            <div class="btn-group btn-group-nomargin">
                {{ $list->links() }}                                </div>
            <!-- /.btn-group -->
        </div>
        <!-- /.pull-right -->
    </div>
</div>
<!-- /.mail-box-messages -->
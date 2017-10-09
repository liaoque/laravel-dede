@extends("admin.layout.main")

@section('content')

        <!-- Main content -->
<style>
    .treeview-menu > li > a {
        padding: 12px 5px 12px 15px;
        font-size: 12px;
    }

    .sidebar-menu .treeview-menu {
        padding-left: 20px;
    }
</style>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <form action="{{route('admin.catalog.sotrrank')}}">
                <div class="box-header with-border">
                    <i class="fa fa-text-width"></i>

                    <h3 class="box-title">网站栏目管理</h3>

                    <div class="box-tools pull-right">
                        <div class="has-feedback">
                            <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i
                                        class="fa fa-square-o"></i>
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm webhaeder"
                                        data-href="{{route('admin.catalog.create')}}"><i class="fa fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-default btn-sm" onclick="sortrank(event, this)"><i
                                            class="fa fa-sort"></i>
                                </button>
                                <button type="button" class="btn btn-default btn-sm"><i class="fa fa-share"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-tools -->
                </div>
                <!-- /.box-header -->
                <div class="no-padding">

                    <div class="box-body text-green">
                        <small>提示：可使用右键菜单进行操作。</small>
                    </div>


                    <div class="table-responsive mailbox-messages">
                        <ul class="sidebar-menu tree " data-widget="tree">
                            @include('admin.catalog.line', ['listNav' => $listNav])
                        </ul>
                    </div>
                    <!-- /.mail-box-messages -->
                </div>
                <!-- /.box-body -->
                </form>
            </div>
            <!-- /. box -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</section>
<!-- /.content -->

@endsection


@section('tool')
    @include('tool.modal.move')
    @include('tool.modal.confirm')
    @include('tool.modal.alert')



@endsection
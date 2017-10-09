@extends("admin.layout.main")


@section('content')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <form data-action="{{route('admin.content.list')}}">
                <div class="box">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-md-12">
                                <i class="fa fa-text-width"></i>

                                <h3 class="box-title">文档管理</h3>

                                <div class="box-tools pull-right">
                                    <div class="has-feedback">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default btn-sm webhaeder"
                                                    data-href="{{route('admin.content.create')}}"><i
                                                        class="fa fa-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-default btn-sm"><i
                                                        class="fa fa-share"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-tools -->
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>选择栏目：</label>
                                    <select class="form-control" name="cid">
                                        <option value="0">选择栏目</option>
                                        @foreach($navListArrayOne as $value)
                                            <option value="{{$value->id}}">{{$value->_typename}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <!-- /.form-group -->
                                <div class="form-group">
                                    <label>排序：</label>
                                    <select class="form-control" name="orderby">
                                        @foreach($sortTypeList as $key => $value)
                                            <option value="{{$key}}">{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>属性：</label>
                                    <select class="form-control" name="flag">
                                        <option value="0">属性</option>
                                        @foreach($arcattList as $value)
                                            <option value="{{$value->att}}">{{$value->attname}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <!-- /.form-group -->
                                <div class="form-group">
                                    <label>关键字：</label>
                                    <div class="input-group ">
                                        <input type="text" class="form-control" name="keyword" placeholder="关键字">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-default search-form-button"><i
                                                        class="fa
                                            fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->
                    </div>

                    <div class="box-body no-padding">
                        @include('admin.content.line')
                    </div>
                    <!-- /.box-body -->

                </div>
                <!-- /.box -->
                </form>
            </div>
        </div>
    </section>
    <style>
        .btn-group-nomargin .pagination {
            margin: 0;
        }
    </style>
@endsection


@section('tool')
    @include('tool.modal.confirm')
    @include('tool.modal.alert')
@endsection
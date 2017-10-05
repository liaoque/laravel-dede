@extends("admin.layout.main")

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @include('admin.layout.error')
                        <!-- Custom Tabs -->
                <form class="form-horizontal" name="form1" action="{{$action}}" method="post" onSubmit="return checkSubmit();">
                    <input type="hidden" value="{{csrf_token()}}"  name="_token"  />
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab_1" data-toggle="tab"> 常规选项 </a></li>
                            <li class=""><a href="#tab_2" data-toggle="tab"> 高级选项 </a></li>
                            <li class=""><a href="#tab_3" data-toggle="tab"> 栏目内容 </a></li>
                        </ul>

                        <div class="tab-content">
                            <input type="hidden" name="id"  value="{{$arctype->id}}"/>
                            <input type='hidden' name='topid' id='topid' value='{{$arctype->topid}}'/>
                            <div class="tab-pane active" id="tab_1">
                                @include('admin.catalog.normal')
                                        <!-- /.box-body -->
                            </div>
                            <!-- /.tab-pane -->
                            <div class="tab-pane" id="tab_2">
                                @include('admin.catalog.senior')
                            </div>
                            <!-- /.tab-pane -->
                            <div class="tab-pane " id="tab_3">
                                @include('admin.catalog.uedit')
                            </div>
                            <!-- /.tab-pane -->

                        </div>
                        <!-- /.tab-content -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-default">返回</button>
                            <button type="submit" class="btn btn-info pull-right">确定</button>
                        </div>
                        <!-- /.box-footer -->
                    </div>
                    <!-- nav-tabs-custom -->
                </form>
            </div>
            <!-- /.col -->
        </div>
    </section>
@endsection


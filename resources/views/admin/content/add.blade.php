@extends("admin.layout.main")

@section('content')
    <section class="content">
        <div class="row">
            <div class="col-md-12">
            @include('admin.layout.error')
            <!-- Custom Tabs -->
                <form role="form" name="form1" action="{{$action}}" method="post">
                    <input type="hidden" value="{{csrf_token()}}" name="_token"/>
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab_1" data-toggle="tab"> 常规选项 </a></li>
                            <li class=""><a href="#tab_2" data-toggle="tab"> 高级选项 </a></li>
                            <li class=""><a href="#tab_3" data-toggle="tab"> 投票管理 </a></li>
                        </ul>
                        <div class="tab-content">

                            <div class="tab-pane active" id="tab_1">
                            @include('admin.content.normal')
                            <!-- /.box-body -->
                            </div>
                            <!-- /.tab-pane -->
                            <div class="tab-pane" id="tab_2">
                                @include('admin.content.senior')
                            </div>
                            <!-- /.tab-pane -->
                            <div class="tab-pane " id="tab_3">
                                @include('admin.content.toupiao')
                            </div>
                            <!-- /.tab-pane -->

                        </div>
                        <!-- /.tab-content -->

                        <div class="box-footer">
                            <button type="button" class="btn btn-default">返回</button>
                            <button type="submit" class="btn btn-info pull-right">确定</button>
                        </div>
                    </div>
                    <!-- /.box-footer -->
                </form>

            </div>

        </div>
        <!-- /.col -->
        </div>
    </section>
@endsection

@section('tool')
    @include('tool.modal.alert')
    @include('tool.modal.cropper')
@endsection

@push('script2')
    <script>
        var selfForm = $('form[name="form1"]').submit(function (e) {
            e.preventDefault();
            var params = selfForm.serializeArray();

            var textArea = $('<div>').append(params[26].value);
            var items = textArea.find('p')

            for (var i = 0; i < items.length; i++){
                items.eq(i).text(123456789)
            }
            params[26].value = textArea.html()

            console.log(params)
//            serialize()
//            serializeArray()
            textArea = null;
            return false;

        });


    </script>
@endpush

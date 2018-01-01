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
            var textArea = $('<div>').append(UE.getEditor('editor').getContent());

            /**
             * 自动内容摘要
             * */
            if($('input[name="description"]').val().length){
                $('input[name="description"]').val(textArea.text().substr(0, config.cfg_auot_description));
            }

            /**
             * 自动获取缩略图
             * */
            if($('input[name="autolitpic"]').prop('checked')){
                var items = textArea.find('img')
                if(items.length){
                    $('input[name="autolitpic"]').val(items.eq(0).attr('href'));
                }
            }

            /**
             * 删除站外连接
              */
            if($('input[name="dellink"]').prop('checked')){
                var items = textArea.find('a')
                for (var i = 0; i < items.length; i++){
                    items.eq(i).attr('href', 'javascript:void(0)');
                }
                UE.getEditor('editor').setContent(textArea.html())
            }



            textArea = null;
            return false;

        });


    </script>
@endpush

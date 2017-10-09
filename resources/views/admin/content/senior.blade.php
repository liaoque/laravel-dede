<div class="box-body">

    <div class="form-group">
        <label for="" class="control-label">评论选项：</label>
        @if($sysConfig->cfg_feedback_forbid == 'Y')
            <label>
                <input name="notpost" type="radio" value="0"/>允许评论
            </label>
            <label>
                <input name="notpost" type="radio" value="1" checked="checked"/>禁止评论
            </label>
        @else
            <label>
                <input name="notpost" type="radio" value="0" checked="checked"/>允许评论
            </label>
            <label>
                <input name="notpost" type="radio" value="1"/>禁止评论
            </label>
        @endif
    </div>

    <div class="form-group">
        <label for="" class=" control-label">浏览次数：</label>
        <input name="click" type="text" value="" class="form-control"/>
        {{--<input type='text' name='click' value='echo ($cfg_arc_click=='-1' ? mt_rand(50, 200) : $cfg_arc_click); ' style='width:100px;' />--}}
    </div>

    <div class="form-group">
        <label for="" class=" control-label">文章排序：</label>
        <select name="sortup" class="form-control">
            @foreach($sortArticleList as $key=> $value)
                <option value="{{$key}}" >{{$value}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="" class=" control-label">标题颜色：</label>
        <input type="text" name="modcolor" class="form-control my-colorpicker1">
    </div>

    <div class="form-group">
        <label for="" class=" control-label">阅读权限：</label>
        <select name="arcrank" class="form-control">
            @foreach($arcRankList as $key=> $value)
                <option value="{{$value->rank}}" >{{$value->membername}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="" class=" control-label">消费金币：</label>
        <input name="click" type="text" value="" class="form-control"/>
    </div>

    <div class="form-group">
        <label for="" class=" control-label">浏览次数：</label>
        <input name="money" type="text" value="" class="form-control"/>
    </div>

    <div class="form-group">
        <label for="" class=" control-label">发布时间：</label>
        <div class="input-group date">
            <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
            </div>
            <input type="text" name="pubdate"  class="form-control pull-right" id="datepicker">
        </div>
        <!-- /.input group -->
    </div>

    <div class="form-group">
        <label for="" class=" control-label">发布选项：</label>
        @foreach($isHtmlList as $key => $value)
            <label>
                <input name="autolitpic" type="radio" value="{{$key}}" checked="checked"/>{{$value}}
            </label>
        @endforeach
    </div>

    <div class="form-group">
        <label for="" class=" control-label">自定义文件名（不包括后缀名如.html等）：</label>
        <input name="filename" type="text" value="" class="form-control"/>
    </div>

</div>

@push('scripts')
    <!-- Bootstrap Color Picker -->
    {{--<link rel="stylesheet" href="/adminlte/plugins/timepicker/bootstrap-timepicker.min.css">--}}
    {{--<link rel="stylesheet" href="/adminlte/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css">--}}
    <link rel="stylesheet" href="http://www.bootcss.com/p/bootstrap-datetimepicker/bootstrap-datetimepicker/css/datetimepicker.css">

    <script src="/adminlte/bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>

    {{--<script src="/adminlte/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>--}}
    <script src="http://www.bootcss.com/p/bootstrap-datetimepicker/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

    <script>
        $('#datepicker').datetimepicker({
            format: "yyyy-mm-dd hh:ii:ss",
            autoclose: true,
            minuteStep: 1
        })
        $('.my-colorpicker1').colorpicker()
    </script>
@endpush
